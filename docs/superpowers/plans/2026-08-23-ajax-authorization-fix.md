# AJAX Authorization Fix (CWE-862) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Close the WP.org-reported missing-authorization vulnerability in `woowgallery_dynamic_fetch_query` and harden all sibling admin AJAX handlers with nonce + capability checks, plus server-side query restrictions.

**Architecture:** Authorization enforced at two layers: (1) every admin AJAX handler in `includes/admin/class-ajax.php` verifies the existing plugin `'ajax'` nonce and a capability; (2) the shared query builder `Edit_Dynamic_Gallery::get_dynamic_wp_query()` allowlists caller-supplied `post_status`/`post_type`, adds `perm => readable`, and gates every non-public post behind `current_user_can( 'read_post' )`. Password-protected posts get their caption blanked in the shared `woowgallery_full_post_data()` helper (fixes both AJAX and frontend display). JS callers gain the `_nonce_woowgallery_ajax` parameter following the existing pattern in `assets/js/src/tags-box.js:27`.

**Tech Stack:** WordPress plugin PHP (WP-style, tabs), jQuery admin JS. No PHPUnit — verification via `php -l` and a `wp-dev eval-file` script against the local WP 7.0 site. Minified JS normally built by CodeKit; interim rebuild via `npx esbuild --minify`.

**Out of scope (release steps, human-driven):** version bump, readme.txt "Tested up to", SVN commit, Freemius release, reply to plugins@wordpress.org.

---

### Task 1: Harden `dynamic_fetch_query` handler

**Files:**
- Modify: `includes/admin/class-ajax.php:225-233`

- [ ] **Step 1: Write the failing verification script**

Create `/private/tmp/claude-501/-Users-simka--LOCAL--wp-dev-app-public-wp-content-plugins-woowgallery/1403bee3-ace8-4be2-a411-2674b575e4a0/scratchpad/verify-query-auth.php`:

```php
<?php
/**
 * Verification: get_dynamic_wp_query must not disclose protected posts
 * to a Subscriber. Run: wp-dev eval-file <this file>
 */

use WoowGallery\Admin\Edit_Dynamic_Gallery;

$admin_id = 1;

// Fixture posts (created as admin).
wp_set_current_user( $admin_id );
$fixtures = [];
foreach ( [ 'draft', 'pending', 'private' ] as $status ) {
	$fixtures[ $status ] = wp_insert_post( [
		'post_title'   => "WG-SEC-TEST {$status}",
		'post_excerpt' => "SECRET-{$status}-EXCERPT",
		'post_status'  => $status,
		'post_type'    => 'post',
		'post_author'  => $admin_id,
	] );
}
$fixtures['password'] = wp_insert_post( [
	'post_title'    => 'WG-SEC-TEST password',
	'post_excerpt'  => 'SECRET-password-EXCERPT',
	'post_status'   => 'publish',
	'post_password' => 'pw123',
	'post_type'     => 'post',
	'post_author'   => $admin_id,
] );
$fixtures['public'] = wp_insert_post( [
	'post_title'   => 'WG-SEC-TEST public control',
	'post_excerpt' => 'PUBLIC-CONTROL-EXCERPT',
	'post_status'  => 'publish',
	'post_type'    => 'post',
	'post_author'  => $admin_id,
] );

$subscriber = wp_insert_user( [
	'user_login' => 'wg_sec_test_subscriber_' . wp_rand(),
	'user_pass'  => wp_generate_password(),
	'role'       => 'subscriber',
] );

// The exact query shape the AJAX endpoint receives.
$attack_query = [
	'query_type'     => 'wp',
	'post_type'      => [ [ 'name' => 'post' ] ],
	'post_status'    => [
		[ 'value' => 'publish' ],
		[ 'value' => 'draft' ],
		[ 'value' => 'pending' ],
		[ 'value' => 'private' ],
	],
	'post_author'    => [],
	'taxonomy_terms' => [],
	'terms_relation' => 'IN',
	'limit'          => 100,
	'offset'         => 0,
	'post__not_in'   => '',
	'meta_key'       => '',
	'meta_value'     => '',
	'meta_compare'   => '',
	'has_password'   => '',
	'orderby'        => 'date',
	'order'          => 'DESC',
];

$failures = [];

// 1. Subscriber must not receive protected posts.
wp_set_current_user( $subscriber );
$result = Edit_Dynamic_Gallery::get_dynamic_wp_query( $attack_query );
$blob   = wp_json_encode( $result['posts'] );
foreach ( [ 'draft', 'pending', 'private', 'password' ] as $status ) {
	if ( false !== strpos( $blob, "SECRET-{$status}-EXCERPT" ) ) {
		$failures[] = "subscriber sees {$status} excerpt";
	}
	if ( 'password' !== $status && false !== strpos( $blob, "WG-SEC-TEST {$status}" ) ) {
		$failures[] = "subscriber sees {$status} title";
	}
}
if ( false === strpos( $blob, 'PUBLIC-CONTROL-EXCERPT' ) ) {
	$failures[] = 'subscriber does NOT see public control (over-blocking)';
}

// 2. Password-protected excerpt must be blank for everyone without the password.
if ( false !== strpos( $blob, 'SECRET-password-EXCERPT' ) ) {
	$failures[] = 'password-protected excerpt disclosed';
}

// 3. Admin must still see everything.
wp_set_current_user( $admin_id );
$result = Edit_Dynamic_Gallery::get_dynamic_wp_query( $attack_query );
$blob   = wp_json_encode( $result['posts'] );
foreach ( [ 'draft', 'pending', 'private' ] as $status ) {
	if ( false === strpos( $blob, "WG-SEC-TEST {$status}" ) ) {
		$failures[] = "admin lost access to {$status}";
	}
}

// 4. Unregistered status must be stripped server-side (no crash, no disclosure).
$bogus                = $attack_query;
$bogus['post_status'] = [ [ 'value' => 'draft' ], [ 'value' => 'bogus_status' ] ];
wp_set_current_user( $subscriber );
$result = Edit_Dynamic_Gallery::get_dynamic_wp_query( $bogus );
$blob   = wp_json_encode( $result['posts'] );
if ( false !== strpos( $blob, 'SECRET-draft-EXCERPT' ) ) {
	$failures[] = 'bogus status list still discloses drafts';
}

// Cleanup.
foreach ( $fixtures as $id ) {
	wp_delete_post( $id, true );
}
wp_delete_user( $subscriber );

if ( $failures ) {
	WP_CLI::error( 'FAIL: ' . implode( '; ', $failures ) );
}
WP_CLI::success( 'All authorization checks pass.' );
```

- [ ] **Step 2: Run it to confirm it FAILS on current code**

Run: `wp-dev eval-file <scratchpad>/verify-query-auth.php`
Expected: `Error: FAIL: subscriber sees draft excerpt; subscriber sees pending excerpt; ...`

- [ ] **Step 3: Add nonce + capability to the handler**

In `includes/admin/class-ajax.php`, `dynamic_fetch_query()` — replace the method opening:

```php
	public function dynamic_fetch_query() {
		// Bail out if we fail a security check.
		woowgallery_verify_nonce( 'ajax' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( -1, 403 );
		}

		$json = woowgallery_GET( 'json' );
		if ( empty( $json ) ) {
			wp_send_json_error( __( 'Empty Query', 'woowgallery' ) );
		}

		$gallery_id = (int) woowgallery_GET( 'gallery_id', 0 );
		if ( $gallery_id && ! current_user_can( 'edit_post', $gallery_id ) ) {
			$gallery_id = 0;
		}
```

(The rest of the method is unchanged.)

- [ ] **Step 4: Lint**

Run: `php -l includes/admin/class-ajax.php`
Expected: `No syntax errors detected`

- [ ] **Step 5: Commit**

```bash
git add includes/admin/class-ajax.php
git commit -m "Security: require nonce and edit_posts capability in dynamic_fetch_query AJAX"
```

---

### Task 2: Server-side query restrictions in `get_dynamic_wp_query`

**Files:**
- Modify: `includes/admin/class-edit-dynamic-gallery.php:119-223`
- Modify: `functions/helpers.php:192-196`

- [ ] **Step 1: Allowlist post_type**

In `includes/admin/class-edit-dynamic-gallery.php`, `get_dynamic_wp_query()`, replace:

```php
		$query['post_type']   = wp_list_pluck( (array) $query['post_type'], 'name' ) ?: 'any';
```

with:

```php
		$query['post_type'] = wp_list_pluck( (array) $query['post_type'], 'name' ) ?: 'any';
		if ( 'any' !== $query['post_type'] ) {
			$query['post_type'] = array_values( array_intersect( (array) $query['post_type'], get_post_types() ) ) ?: 'any';
		}
```

- [ ] **Step 2: Allowlist post_status and require readable perm**

Replace:

```php
		$query['post_status'] = wp_list_pluck( $query['post_status'], 'value' ) ?: [ 'publish' ];
```

with:

```php
		$query['post_status'] = wp_list_pluck( $query['post_status'], 'value' ) ?: [ 'publish' ];
		$query['post_status'] = array_values( array_intersect( $query['post_status'], get_post_stati() ) ) ?: [ 'publish' ];
```

Then, after the existing lines:

```php
		$query['cache_results']          = false;
		$query['update_post_meta_cache'] = false;
		$query['update_post_term_cache'] = false;
```

add:

```php
		$query['perm'] = 'readable';
```

- [ ] **Step 3: Per-object read gate in the loop**

Replace the query loop:

```php
		global $post;
		$wg_query = new WP_Query( $query );
		if ( $wg_query->have_posts() ) {
			while ( $wg_query->have_posts() ) {
				$wg_query->the_post();
				$attachment_data      = woowgallery_prepare_post_data( $post );
				$attachment_full_data = woowgallery_full_post_data( $attachment_data );

				$data[] = $attachment_full_data;
			}
		}
		wp_reset_postdata();

		return [
			'post_count' => $wg_query->post_count,
```

with:

```php
		global $post;
		$wg_query = new WP_Query( $query );
		if ( $wg_query->have_posts() ) {
			while ( $wg_query->have_posts() ) {
				$wg_query->the_post();
				// Non-public statuses require per-post read capability.
				if ( ! in_array( $post->post_status, [ 'publish', 'inherit' ], true ) && ! current_user_can( 'read_post', $post->ID ) ) {
					continue;
				}
				$attachment_data      = woowgallery_prepare_post_data( $post );
				$attachment_full_data = woowgallery_full_post_data( $attachment_data );

				$data[] = $attachment_full_data;
			}
		}
		wp_reset_postdata();

		return [
			'post_count' => count( $data ),
```

- [ ] **Step 4: Preserve password protection in shared data helper**

In `functions/helpers.php`, `woowgallery_full_post_data()`, replace:

```php
		if ( 'excerpt' === $attachment['caption_src'] ) {
			$attachment['caption'] = Posttypes::GALLERY_POSTTYPE === $post->post_type ? $post->post_content : $post->post_excerpt;
		} elseif ( 'content' === $attachment['caption_src'] ) {
			$attachment['caption'] = $post->post_content;
		}
```

with:

```php
		if ( post_password_required( $post ) ) {
			$attachment['caption'] = '';
		} elseif ( 'excerpt' === $attachment['caption_src'] ) {
			$attachment['caption'] = Posttypes::GALLERY_POSTTYPE === $post->post_type ? $post->post_content : $post->post_excerpt;
		} elseif ( 'content' === $attachment['caption_src'] ) {
			$attachment['caption'] = $post->post_content;
		}
```

- [ ] **Step 5: Lint both files**

Run: `php -l includes/admin/class-edit-dynamic-gallery.php && php -l functions/helpers.php`
Expected: `No syntax errors detected` twice.

- [ ] **Step 6: Run the verification script — must PASS now**

Run: `wp-dev eval-file <scratchpad>/verify-query-auth.php`
Expected: `Success: All authorization checks pass.`

- [ ] **Step 7: Commit**

```bash
git add includes/admin/class-edit-dynamic-gallery.php functions/helpers.php
git commit -m "Security: allowlist status/type, readable perm, per-post read checks, password-protected caption stripping"
```

---

### Task 3: Harden remaining unprotected AJAX handlers

**Files:**
- Modify: `includes/admin/class-ajax.php` (`get_media_data`, `refresh_taxonomy_terms`, `refresh_flagallery_source`, `gallery_cache_clear`)
- Modify: `includes/admin/class-edit-dynamic-galleries.php:37-48`

- [ ] **Step 1: get_media_data** — add at the top of the method body:

```php
	public function get_media_data() {
		// Bail out if we fail a security check.
		woowgallery_verify_nonce( 'ajax' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( -1, 403 );
		}

		$media_post_data = json_decode( woowgallery_POST( 'media', '[]' ) );
```

- [ ] **Step 2: refresh_taxonomy_terms** — same guard:

```php
	public function refresh_taxonomy_terms() {
		// Bail out if we fail a security check.
		woowgallery_verify_nonce( 'ajax' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( -1, 403 );
		}

		$post_type      = (array) woowgallery_GET( 'post_type', [] );
```

- [ ] **Step 3: refresh_flagallery_source** — same guard:

```php
	public function refresh_flagallery_source() {
		// Bail out if we fail a security check.
		woowgallery_verify_nonce( 'ajax' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( -1, 403 );
		}

		global $flagdb;
```

- [ ] **Step 4: gallery_cache_clear** — nonce + per-post capability:

```php
	public function gallery_cache_clear() {
		// Bail out if we fail a security check.
		woowgallery_verify_nonce( 'ajax' );

		$cache_clear_id = (int) woowgallery_POST( 'id' );
		if ( ! empty( $cache_clear_id ) && current_user_can( 'edit_post', $cache_clear_id ) ) {
			if ( metadata_exists( 'post', $cache_clear_id, Gallery::GALLERY_UPDATE_META_KEY ) ) {
				update_post_meta( $cache_clear_id, Gallery::GALLERY_UPDATE_META_KEY, 1 );
			}
			wp_send_json_success();
		}

		wp_send_json_error();
	}
```

- [ ] **Step 5: no-JS cache clear via GET** — in `includes/admin/class-edit-dynamic-galleries.php`, `current_screen()`, replace:

```php
			$cache_clear_id = (int) woowgallery_GET( 'wg_cache_clear' );
			if ( ! empty( $cache_clear_id ) ) {
```

with:

```php
			$cache_clear_id = (int) woowgallery_GET( 'wg_cache_clear' );
			if ( ! empty( $cache_clear_id ) && current_user_can( 'edit_post', $cache_clear_id ) ) {
```

- [ ] **Step 6: Lint**

Run: `php -l includes/admin/class-ajax.php && php -l includes/admin/class-edit-dynamic-galleries.php`
Expected: `No syntax errors detected` twice.

- [ ] **Step 7: Commit**

```bash
git add includes/admin/class-ajax.php includes/admin/class-edit-dynamic-galleries.php
git commit -m "Security: nonce and capability checks on remaining admin AJAX handlers"
```

---

### Task 4: JS callers send the nonce

**Files:**
- Modify: `assets/js/edit-dynamic-gallery.js:203-260` (+ regen `.min.js`)
- Modify: `assets/js/edit-album.js:~105`, `assets/js/edit-gallery.js:~114` (+ regen `.min.js`)
- Modify: `assets/js/admin.js:~47` (+ regen `.min.js`)
- Modify: `includes/class-assets.php:296-303`

Pattern reference: `assets/js/src/tags-box.js:27` — `_nonce_woowgallery_ajax: $('#_nonce_woowgallery_ajax').val()`. The nonce hidden field is already rendered on gallery editor pages by `includes/admin/templates/gallery-query.php:149` and `gallery-media.php:175`.

- [ ] **Step 1: edit-dynamic-gallery.js** — add the nonce param to all three AJAX data objects:

`wp_refreshTaxonomyTerms`:
```js
          {
            action: 'woowgallery_dynamic_refresh_taxonomy_terms',
            _nonce_woowgallery_ajax: $('#_nonce_woowgallery_ajax').val(),
            post_type: _.pluck(this.wp.post_type, 'name'),
            terms_relation: this.wp.terms_relation
          },
```

`wp_fetchQuery`:
```js
          data: {
            action: 'woowgallery_dynamic_fetch_query',
            _nonce_woowgallery_ajax: $('#_nonce_woowgallery_ajax').val(),
            gallery_id: post_id,
            json: json
          }
```

`flagallery_refreshSource`:
```js
          {
            action: 'woowgallery_dynamic_refresh_flagallery_source',
            _nonce_woowgallery_ajax: $('#_nonce_woowgallery_ajax').val()
          },
```

- [ ] **Step 2: edit-album.js and edit-gallery.js** — in both `woowgallery_get_media_data` calls:

```js
          {
            action: 'woowgallery_get_media_data',
            _nonce_woowgallery_ajax: $('#_nonce_woowgallery_ajax').val(),
            // make this a JSON string so we can send larger amounts of data (images), otherwise max is around 20 by default for most server configs
            media: JSON.stringify(media)
          },
```

- [ ] **Step 3: localize nonce for admin.js** — in `includes/class-assets.php`, `admin_scripts_l10n()`:

```php
		$script_localize = [
			'l10n'         => apply_filters( 'woowgallery_admin_scripts_l10n', [] ),
			'wpApiRoot'    => esc_url_raw( rest_url() ),
			'wpApiNonce'   => wp_create_nonce( 'wp_rest' ),
			'ajaxNonce'    => wp_create_nonce( 'ajax' ),
			'createNew'    => esc_url( admin_url( 'post-new.php?post_type=' ) ),
			'editModalSrc' => esc_url( admin_url( 'admin.php?page=woowgallery-edit' ) ),
			'post_types'   => $post_types,
		];
```

- [ ] **Step 4: admin.js cache-clear call**:

```js
      {
        action: 'woowgallery_cache_clear',
        _nonce_woowgallery_ajax: WoowGalleryAdmin.ajaxNonce,
        id: id
      }
```

- [ ] **Step 5: Regenerate minified files** (CodeKit not scriptable; interim esbuild):

```bash
for f in edit-dynamic-gallery edit-album edit-gallery admin; do
  npx --yes esbuild "assets/js/$f.js" --minify --outfile="assets/js/$f.min.js" --allow-overwrite
done
```

Expected: four `.min.js` files rewritten. Note in PR: rebuild via CodeKit before release for source maps.

- [ ] **Step 6: Lint PHP**

Run: `php -l includes/class-assets.php`
Expected: `No syntax errors detected`

- [ ] **Step 7: Commit**

```bash
git add assets/js/edit-dynamic-gallery.js assets/js/edit-dynamic-gallery.min.js assets/js/edit-album.js assets/js/edit-album.min.js assets/js/edit-gallery.js assets/js/edit-gallery.min.js assets/js/admin.js assets/js/admin.min.js includes/class-assets.php
git commit -m "Security: send plugin ajax nonce from all admin AJAX callers"
```

---

### Task 5: Full verification sweep

- [ ] **Step 1: Whole-plugin lint**

Run: `find . -name '*.php' -not -path './vendor/*' -print0 | xargs -0 -n1 php -l | grep -v 'No syntax errors' || echo ALL-CLEAN`
Expected: `ALL-CLEAN`

- [ ] **Step 2: Re-run authorization verification**

Run: `wp-dev eval-file <scratchpad>/verify-query-auth.php`
Expected: `Success: All authorization checks pass.`

- [ ] **Step 3: Plugin Check (WP.org requirement)**

```bash
wp-dev plugin install plugin-check --activate
wp-dev plugin check woowgallery
```

Expected: report; fix any ERROR-level findings in follow-up commits (separate scope decision if large).

- [ ] **Step 4: Manual editor smoke test (user)**

In wp-admin: open a Dynamic Gallery, confirm query fetch, taxonomy refresh, media insert on Gallery/Album, and cache-clear button all still work as admin.

---

### Deferred (release, human-driven — protected actions)

- Version bump to 1.2.5 in `woowgallery.php` + `readme.txt` stable tag / changelog / "Tested up to".
- CodeKit rebuild of minified assets with source maps.
- SVN commit to WP.org, Freemius release, reply to plugins@wordpress.org requesting re-review.
