# Pre-release Review Fixes Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Close the authorization gaps found while reviewing merged PRs #2, #5, #6, #7, and #8 before releasing WoowGallery 1.2.5.

**Architecture:** Keep the existing nonce actions and add authorization at each mutation/read boundary. Object-scoped operations use WordPress meta capabilities on the exact post ID; global skin-preset operations reuse the settings menu capability filter; the list-table fallback reuses WordPress's existing `bulk-posts` nonce.

**Tech Stack:** WordPress 7.x, PHP 7.4+, WP-CLI, real HTTP requests to Local WordPress.

**Spec:** `docs/superpowers/plans/2026-08-23-ajax-authorization-fix.md`

## Global Constraints

- Do not change plugin version or `readme.txt` stable tag.
- Do not touch `vendor/` or `assets/vendor/`.
- Do not commit, push, merge, publish, or release without explicit user approval.
- Preserve the existing `ajax` and `skin_settings_save` nonce actions.
- Run the required plugin-wide PHP lint before completion.

---

### Task 1: Add a failing Local HTTP authorization regression test

**Files:**
- Create temporarily: `/tmp/woowgallery-pre-release-security-test.php`
- Test: `includes/admin/class-ajax.php`
- Test: `includes/admin/class-edit-dynamic-galleries.php`

**Interfaces:**
- Consumes: active Local site at `https://wp-dev.loc`, `wp-dev eval-file`, existing administrator and subscriber users.
- Produces: assertions proving that a valid subscriber nonce cannot read another user's private post or mutate post metadata, terms, or skin options; and that a cache-clear GET without the list-table nonce is rejected.

- [x] **Step 1: Create isolated fixtures and authenticated HTTP requests**

Use a temporary administrator-owned private post and temporary option values. Generate a session-token-bound cookie and nonce for each test user, send real requests to `wp-admin/admin-ajax.php`, and restore/delete every fixture in a `finally` block.

- [x] **Step 2: Run the regression test against current `master`**

Run: `wp-dev eval-file /tmp/woowgallery-pre-release-security-test.php`

Expected: FAIL because subscriber requests currently reach nonce-only handlers, `get_media_data` can return an unreadable post, and the nonce-less list-table fallback clears cache metadata.

### Task 2: Enforce object and settings capabilities in AJAX handlers

**Files:**
- Modify: `includes/admin/class-ajax.php`
- Test: `/tmp/woowgallery-pre-release-security-test.php`

**Interfaces:**
- Consumes: post IDs from `media_id` and decoded `media` payloads; `apply_filters( 'woowgallery_menu_cap', 'manage_options' )` for settings access.
- Produces: unauthorized requests return WordPress JSON errors/403 without reading or changing protected data; authorized requests retain existing response shapes.

- [x] **Step 1: Protect reads in `get_media_data()`**

After loading each post, skip it unless `current_user_can( 'read_post', $media->ID )` is true.

- [x] **Step 2: Protect single-object media mutations**

Require `current_user_can( 'edit_post', $media_id )` before `update_metadata()` or `wp_set_object_terms()` in `set_media_copyright()` and `set_media_tags()`.

- [x] **Step 3: Protect every object in `bulk_set_media_data()`**

Before any per-item mutation, skip IDs for which `current_user_can( 'edit_post', $media_id )` is false; report success only when at least one authorized item was processed.

- [x] **Step 4: Protect global skin preset mutations**

In both `save_skin_data()` and `delete_skin_preset()`, reject callers who lack `apply_filters( 'woowgallery_menu_cap', 'manage_options' )`.

- [x] **Step 5: Re-run the focused HTTP test**

Expected: all subscriber-denial and administrator-success assertions pass, with fixture state restored.

### Task 3: Protect list-table cache-clearing paths

**Files:**
- Modify: `includes/admin/class-edit-dynamic-galleries.php`
- Test: `/tmp/woowgallery-pre-release-security-test.php`

**Interfaces:**
- Consumes: WordPress list-table `_wpnonce` for action `bulk-posts` and exact post IDs.
- Produces: no-JS cache clearing requires the list-table nonce; bulk clearing changes only posts the current user may edit and reports the number actually changed.

- [x] **Step 1: Verify the no-JS request nonce**

When `wg_cache_clear` is present, call `check_admin_referer( 'bulk-posts' )` before changing metadata.

- [x] **Step 2: Gate each bulk-selected post**

Skip any `$post_id` for which `current_user_can( 'edit_post', $post_id )` is false, count successful updates, and use that count in the notice.

- [x] **Step 3: Re-run focused authorization tests**

Expected: nonce-less and unauthorized requests leave cache metadata unchanged; authorized nonce-bearing requests still clear it.

### Task 4: Re-audit the remaining merged release diffs

**Files:**
- Verify: `assets/css/global.scss`, `assets/css/global.css`
- Verify: `assets/js/*.min.js`, `assets/js/*.map`
- Verify: `.distignore`, `woowgallery.php`, `readme.txt`

**Interfaces:**
- Consumes: merged diffs for PRs #5-#8 and `assets/.config.codekit3`.
- Produces: evidence that CSS overrides remain plugin-scoped, source maps parse, prepend-built bundles retain their dependencies, and the distribution archive excludes development-only files.

- [x] **Step 1: Validate generated assets**

Parse every committed source map as JSON, verify each referenced source exists, and assert the four rebuilt admin bundles contain symbols from their declared prepend dependencies.

- [x] **Step 2: Build and inspect a temporary distribution archive**

Run: `wp-dev dist-archive . /tmp/woowgallery-review/woowgallery.zip`

Expected: archive builds; `.git`, agent files, `graphify-out`, `docs/superpowers`, CodeKit config, and `.distignore` are absent.

- [x] **Step 3: Run final verification**

Run: `find . -name '*.php' -not -path './vendor/*' -print0 | xargs -0 -n1 php -l`

Expected: every plugin-owned PHP file reports no syntax errors.

- [x] **Step 4: Leave changes uncommitted for user review**

Show the exact diff and verification evidence; do not commit or release.
