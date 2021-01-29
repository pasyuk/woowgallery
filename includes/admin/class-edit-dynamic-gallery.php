<?php
/**
 * Edit Dynamic Gallery class.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

namespace WoowGallery\Admin;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

use WoowGallery\Gallery;
use WoowGallery\Posttypes;
use WP_Post;
use WP_Query;

/**
 * Class Edit_Gallery
 */
class Edit_Dynamic_Gallery extends Edit_Woowgallery {

	/**
	 * Primary class constructor.
	 */
	public function __construct() {
		parent::__construct( Posttypes::DYNAMIC_POSTTYPE );

		// Scripts and styles.
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );

		add_action( "save_post_{$this->post_type}", [ $this, 'save_gallery' ], 10, 3 );

		add_filter( 'woowgallery_editor_tabs_nav', [ $this, 'editor_cache_tab_nav' ], 8, 2 );
		add_action( 'woowgallery_tab_cache', [ $this, 'tab_cache' ] );
	}

	/**
	 * Set Gallery Content.
	 *
	 * @param WP_Post $post  Post object.
	 * @param array   $query Gallery Query.
	 *
	 * @return array
	 */
	public static function set_dynamic_content( $post, $query = null ) {
		$post      = get_post( $post );
		$post_id   = $post->ID;
		$post_type = $post->post_type;
		if ( wp_is_post_autosave( $post ) ) {
			$post_id   = $post->post_parent;
			$post_type = get_post_type( $post_id );
		}
		if ( empty( $post ) || Posttypes::DYNAMIC_POSTTYPE !== $post_type ) {
			return [];
		}

		if ( null === $query ) {
			$query = (array) json_decode( $post->post_content_filtered, true );
		}

		$query_content = get_transient( 'woowgallery_fetch_' . $post_id );
		if ( empty( $query_content ) || empty( $query_content['query']['query_type'] ) || $query['query_type'] !== $query_content['query']['query_type'] ) {
			try {
				$query_content = self::get_dynamic_query( $query );
			} catch ( \Exception $e ) {
				$query_content = [];
			}
		} else {
			delete_transient( 'woowgallery_fetch_' . $post_id );
		}

		if ( ! empty( $query_content['posts'] ) ) {
			$wg    = Gallery::get_instance( $post_id, $post_type );
			$cache = absint( $wg->get_settings( 'cache', Settings::get_settings( 'cache' ) ) );

			if ( $cache ) {
				update_post_meta( $post->ID, Gallery::GALLERY_CONTENT_META_KEY, $query_content['posts'] );
				$update_value = time() + $cache * HOUR_IN_SECONDS;
			} else {
				$update_value = 1;
			}
			update_metadata( 'post', $post->ID, Gallery::GALLERY_UPDATE_META_KEY, $update_value );
			update_metadata( 'post', $post->ID, Gallery::GALLERY_MEDIA_COUNT_META_KEY, $query_content['post_count'] );

			return $query_content['posts'];
		}

		return [];
	}

	/**
	 * Get Dynamic Gallery Content.
	 *
	 * @param array $query Gallery Query.
	 *
	 * @return array
	 */
	public static function get_dynamic_query( $query ) {
		$query_content = [];
		if ( 'wp' === $query['query_type'] ) {
			$query_content = self::get_dynamic_wp_query( $query );
		} elseif ( 'instagram' === $query['query_type'] ) {
			$query_content = self::get_dynamic_instagram_query( $query );
		} elseif ( 'flagallery' === $query['query_type'] ) {
			$query_content = self::get_dynamic_flagallery_query( $query );
		}

		return $query_content;
	}

	/**
	 * Get Dynamic WP Gallery Content.
	 *
	 * @param array $query Gallery Query.
	 *
	 * @return array
	 */
	public static function get_dynamic_wp_query( $query ) {
		$query_backup = $query;

		$query['post_type']   = wp_list_pluck( (array) $query['post_type'], 'name' ) ?: 'any';
		$query['post_author'] = wp_list_pluck( (array) $query['post_author'], 'id' );
		if ( ! empty( $query['taxonomy_terms'] ) ) {
			$post_type = ( 'any' === $query['post_type'] ) ? [] : $query['post_type'];
			$shared    = ( 'IN' !== $query['terms_relation'] );
			if ( $shared || empty( $post_type ) ) {
				$_taxonomies = woowgallery_get_shared_object_taxonomies( $post_type, 'names' );
			} else {
				$_taxonomies = get_object_taxonomies( $post_type, 'names' );
			}

			$tax_terms = [];
			foreach ( (array) $query['taxonomy_terms'] as $term ) {
				if ( ! in_array( $term['taxname'], $_taxonomies, true ) ) {
					continue;
				}
				$tax_terms[ $term['taxname'] ][] = $term;
			}

			if ( ! empty( $tax_terms ) ) {
				$query['tax_query'] = [
					'relation' => ( 'IN' === $query['terms_relation'] ) ? 'OR' : 'AND',
				];
				foreach ( $tax_terms as $taxname => $terms ) {
					$query['tax_query'][] = [
						'taxonomy' => $taxname,
						'field'    => 'slug',
						'terms'    => wp_list_pluck( $terms, 'slug' ),
						'operator' => $query['terms_relation'],
					];
				}
			}
		}

		if ( ! empty( $query['limit'] ) ) {
			$query['posts_per_page'] = (int) $query['limit'];
		} else {
			$query['nopaging'] = true;
		}

		$query['offset']      = (int) $query['offset'];
		$query['post_status'] = wp_list_pluck( $query['post_status'], 'value' ) ?: [ 'publish' ];
		if ( 'any' === $query['post_type'] || in_array( 'attachment', (array) $query['post_type'], true ) ) {
			$query['post_status'][] = 'inherit';
		}
		if ( isset( $query['post_parent'] ) ) {
			$query['post_parent'] = '' !== trim( $query['post_parent'] ) ? (int) $query['post_parent'] : '';
		}
		$query['post__not_in'] = array_map(
			function ( $id ) {
				return (int) trim( $id );
			},
			explode( ',', $query['post__not_in'] )
		);

		if ( '' !== trim( $query['meta_key'] ) ) {
			$query['meta_query']   = [
				'relation' => 'AND',
			];
			$query['meta_query'][] = [
				'key'     => $query['meta_key'],
				'value'   => $query['meta_value'],
				'compare' => $query['meta_value'],
			];
		}
		unset( $query['taxonomy_terms'], $query['terms_relation'], $query['limit'], $query['meta_key'], $query['meta_value'], $query['meta_compare'] );

		if ( empty( $query['has_password'] ) ) {
			unset( $query['post_password'] );
		}

		$query = array_filter( $query );
		if ( '' !== $query_backup['has_password'] ) {
			$query['has_password'] = (int) $query_backup['has_password'];
		}

		$query['cache_results']          = false;
		$query['update_post_meta_cache'] = false;
		$query['update_post_term_cache'] = false;

		$data = [];

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
			'query'      => $wg_query->query,
			'posts'      => $data,
			'errors'     => [],
		];
	}

	/**
	 * Get Dynamic Instagram Gallery Content.
	 *
	 * @param array $query Gallery Query.
	 *
	 * @return array
	 */
	public static function get_dynamic_instagram_query( $query ) {

		$content = [];
		$errors  = [];

		if ( woow_fs()->can_use_premium_code__premium_only() ) {
			$limit      = (int) $query['limit'] ?: 50;
			$limit      = $limit > 100 ? 100 : $limit;
			$sources    = (array) $query['sources'];
			$source_tag = false;
			$true_limit = ( 'each' === $query['limit_type'] ) ? $limit * count( $sources ) : $limit;
			$data       = [];
			$url        = 'https://api.instacloud.io/v1';
			// media_path: '/media/shortcode/B5sJCwup9Ln'.
			foreach ( $sources as $source ) {
				$source_type = '@' === substr( $source, 0, 1 ) ? 'users' : 'tags';
				if ( 'tags' === $source_type ) {
					$source_tag = true;
				}
				$source_term = substr( $source, 1 );
				$url         = add_query_arg(
					[
						'count' => $limit,
						'path'  => "/{$source_type}/{$source_term}/media/recent/",
					],
					$url
				);

				$remote_get = wp_remote_get(
					$url,
					[
						'timeout'     => 30,
						'redirection' => 2,
						'httpversion' => '1.1',
						'user-agent'  => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.139 Safari/537.36',
						'blocking'    => true,
						'headers'     => [
							'Content-Type'   => 'application/json',
							'Sec-Fetch-Mode' => 'cors',
							'Sec-Fetch-Site' => 'cross-site',
						],
						'cookies'     => [],
						'body'        => null,
						'sslverify'   => false,
					]
				);
				if ( is_wp_error( $remote_get ) ) {
					$errors[] = $remote_get->get_error_message();
					continue;
				}

				$response = wp_remote_retrieve_body( $remote_get );
				if ( ! empty( $response ) ) {
					$response = (array) json_decode( $response, true );
					if ( ! empty( $response['data'] ) ) {
						$data = array_merge( $data, $response['data'] );
					}
				}
				if ( 'source' === $query['sorting'] && count( $data ) >= $true_limit ) {
					break;
				}
			}

			if ( ! empty( $data ) ) {
				foreach ( $data as $media ) {
					$content[ $media['id'] ] = woowgallery_full_instagram_data( $media, $source_tag );
				}
			}

			if ( 'date' === $query['sorting'] ) {
				$content = wp_list_sort( $content, 'date', 'DESC' );
			}
			if ( count( $content ) > $true_limit ) {
				$content = array_slice( $content, 0, $true_limit );
			} else {
				$content = array_values( $content );
			}
		}

		return [
			'post_count' => count( $content ),
			'query'      => wp_json_encode( $query ),
			'posts'      => $content,
			'errors'     => $errors,
		];
	}

	/**
	 * Get Dynamic Flagallery Content.
	 *
	 * @param array $query Gallery Query.
	 *
	 * @return array
	 */
	public static function get_dynamic_flagallery_query( $query ) {
		$post_count = 0;
		$data       = [];
		$errors     = [];
		if ( is_plugin_active( 'flash-album-gallery/flag.php' ) ) {
			global $flagdb;
			$id      = (int) $query['source']['gid'];
			$orderby = $query['orderby'];
			$order   = $query['order'];
			$gallery = $flagdb->get_gallery( $id, $orderby, $order );
			if ( ! empty( $gallery ) ) {
				foreach ( $gallery as $media ) {
					$data[] = woowgallery_full_flagallery_data( $media );
				}
				$post_count = count( $gallery );
			}
		} else {
			$errors[] = __( 'Flgallery plugin not activated.', 'woowgallery' );
		}

		return [
			'post_count' => $post_count,
			'query'      => $query,
			'posts'      => $data,
			'errors'     => [],
		];
	}

	/**
	 * Load assets
	 *
	 * @param string $hook Page Hook.
	 */
	public function admin_enqueue_scripts( $hook ) {

		// Get current screen.
		$screen = get_current_screen();

		// Bail if we're not on the edit WoowGallery Post Type screen.
		if ( 'post' !== $screen->base || $this->post_type !== $screen->post_type ) {
			return;
		}

		add_filter( 'woowgallery_admin_scripts_l10n', [ $this, 'l10n' ] );

		// Load necessary assets.
		wp_enqueue_style( 'vue-multiselect' );
		wp_enqueue_script( 'vue-multiselect' );
		wp_enqueue_style( 'swiper' );
		wp_enqueue_script( 'swiper' );
		wp_enqueue_style( WOOWGALLERY_SLUG . '-edit-woowgallery-style' );
		wp_enqueue_script( WOOWGALLERY_SLUG . '-edit-dynamic-gallery-script' );

		Settings::enqueue_code_editor();

		// Fire a hook to load custom metabox scripts.
		do_action( 'woowgallery_edit_gallery_scripts' );

	}

	/**
	 * Adds localization for admin scripts.
	 *
	 * @param array $l10n Localization Data.
	 *
	 * @return array Updated $data
	 */
	public function l10n( $l10n ) {
		global $post, $pagenow;

		$settings = Settings::get_settings();
		$wg       = Gallery::get_instance( $post->ID, $post->post_type );

		$js_data = [
			'siteurl'          => site_url(),
			'per_page'         => (int) $wg->get_editor_settings( 'per_page', $settings['edit_dynamic_per_page'] ),
			'icons_url'        => plugins_url( 'assets/images/icons', WOOWGALLERY_FILE ),
			'default_skin'     => $settings['default_skin'],
			'default_lightbox' => $settings['default_lightbox'],
		];

		if ( 'post-new.php' !== $pagenow ) {
			$js_data['selected_skin']     = $wg->get_skin_slug() . ': _custom';
			$js_data['selected_lightbox'] = $wg->get_lightbox_slug();
		}

		return array_merge( $l10n, $js_data );
	}

	/**
	 * Callback for saving WoowGallery.
	 *
	 * @param int     $post_id The current post ID.
	 * @param WP_POST $post    The current post object.
	 * @param bool    $update  Is Post update?.
	 */
	public function save_gallery( $post_id, $post, $update ) {
		// Bail out if running an autosave, cron, revision or ajax.
		if (
			( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			|| ( defined( 'DOING_CRON' ) && DOING_CRON )
			|| wp_is_post_revision( $post_id )
			|| wp_is_post_autosave( $post_id )
			|| 'auto-draft' === $post->post_status
			// Bail out if the user doesn't have the correct permissions to update the slider.
			|| ! current_user_can( 'edit_post', $post_id )
		) {
			return;
		}

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			woowgallery_flush_caches( $post_id, $post->post_name );

			return;
		}

		parent::set_gallery_data( $post_id, $post, $update );

		// Fire a hook for addons.
		do_action( 'woowgallery_saved', $post_id, $post );

		// Finally, flush all gallery caches to ensure everything is up to date.
		woowgallery_flush_caches( $post_id, $post->post_name );
	}

	/**
	 * Callback for displaying the gallery preview tab.
	 *
	 * @param WP_Post $post The current post object.
	 */
	public function tab_gallery( $post ) {
		$wg      = Gallery::get_instance( $post->ID, $post->post_type );
		$gallery = $wg->get_gallery();
		// Load view.
		Admin::load_template( 'gallery-query', compact( 'post', 'gallery' ) );
	}

	/**
	 * Callback for displaying the gallery cache tab.
	 *
	 * @param WP_Post $post The current post object.
	 */
	public function tab_cache( $post ) {
		// Load view.
		Admin::load_template( 'gallery-cache-settings', compact( 'post' ) );
	}

	/**
	 * Add Caching tab for edit gallery.
	 *
	 * @param array  $tabs      Editor tabs.
	 * @param string $post_type Post Type.
	 *
	 * @return array
	 */
	public function editor_cache_tab_nav( $tabs, $post_type ) {
		if ( $this->post_type === $post_type ) {
			$tabs['cache'] = [
				'label' => __( 'Caching', 'woowgallery' ),
				'icon'  => 'dashicons-performance',
			];
		}

		return $tabs;
	}
}

