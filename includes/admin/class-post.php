<?php
/**
 * Post class.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

namespace WoowGallery\Admin;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

use _WP_Editors;
use WoowGallery\Gallery;
use WoowGallery\Posttypes;
use WoowGallery\Taxonomies;
use WP_Post;

/**
 * Class Post
 */
class Post {

	/**
	 * WoowGallery Post Types
	 *
	 * @var array
	 */
	public static $wg_post_types;

	/**
	 * Primary class constructor.
	 */
	public function __construct() {

		self::$wg_post_types = [ Posttypes::GALLERY_POSTTYPE, Posttypes::DYNAMIC_POSTTYPE, Posttypes::ALBUM_POSTTYPE ];

		// Scripts and styles.
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );

		// Add a custom media button to the editor.
		add_action( 'media_buttons', [ $this, 'media_button' ] );

		// Associate Post with WoowGallery shortcode to the Gallery.
		add_action( 'save_post', [ $this, 'update_in_post_galleries' ], 9999, 3 );
		// Update post thumbnail in galleries.
		add_action( 'updated_postmeta', [ $this, 'updated_postmeta' ], 10, 4 );
		add_action( 'set_object_terms', [ $this, 'set_object_terms' ], 10, 6 );

		// Update galleries data on post update.
		add_action( 'post_updated', [ $this, 'post_updated' ], 10, 3 );

		// Remove post association with galleries.
		add_action( 'before_delete_post', [ $this, 'delete_post_id_in_galleries' ] );
		add_action( 'before_delete_post', [ $this, 'before_delete_post' ] );
		add_action( 'delete_attachment', [ $this, 'before_delete_post' ] );

		// Add modal template to the Edit Post page.
		add_action( 'wg_admin_footer', [ $this, 'add_modal_tpl' ] );
		add_action( 'admin_footer', [ $this, 'add_modal_tpl' ] );

		// Actions for WoowGallery CPTs.
		add_filter( 'wp_insert_post_data', [ $this, 'wp_insert_wg_post_data' ], 10, 2 );
		add_action( 'post_updated', [ $this, 'wg_post_updated' ], 10, 3 );
		add_action( 'wp_trash_post', [ $this, 'trash_wg_post' ] );
		add_action( 'untrash_post', [ $this, 'untrash_wg_post' ] );
		add_action( 'delete_post', [ $this, 'delete_wg_post' ] );

		add_action( 'wg_admin_footer', [ $this, 'wg_footer_templates' ] );
		add_action( 'admin_footer', [ $this, 'wg_footer_templates' ] );

	}

	/**
	 * Load assets
	 *
	 * @param string $hook Page hook.
	 */
	public function admin_enqueue_scripts( $hook ) {

		// Get current screen.
		$screen = get_current_screen();

		// Bail if we're not on the Edit Post screen.
		if ( 'post' !== $screen->base || ! post_type_supports( $screen->post_type, 'editor' ) ) {
			return;
		}

		// Enqueue styles.
		wp_enqueue_style( WOOWGALLERY_SLUG . '-editor-modal-style' );

		// Enqueue the script that will trigger the editor button.
		wp_enqueue_script( WOOWGALLERY_SLUG . '-editor-modal-script' );

		// Fire a hook to load custom metabox scripts.
		do_action( 'woowgallery_editor_modal_scripts' );
	}

	/**
	 * Adds a custom gallery insert button beside the media uploader button.
	 *
	 * @param string $editor_id Unique editor identifier, e.g. 'content'.
	 */
	public function media_button( $editor_id ) {

		// Get current screen.
		$screen = get_current_screen();

		$post_types = apply_filters( 'woowgallery_posttypes', [ Posttypes::GALLERY_POSTTYPE, Posttypes::DYNAMIC_POSTTYPE, Posttypes::ALBUM_POSTTYPE ] );
		// Bail if we're on the WoowGallery Post Type screen.
		if ( in_array( $screen->post_type, $post_types, true ) ) {
			return;
		}

		// Create the media button.
		echo '<a id="woowgallery-modal-button" href="#" class="button woowgallery-modal-button" data-modal="shortcode" data-posttype="' . esc_attr( Posttypes::GALLERY_POSTTYPE ) . '" title="' . esc_attr__( 'WoowGallery Galleries', 'woowgallery' ) . '" >
            <span class="woowgallery-icon"></span> ' . esc_html__( 'WoowGallery', 'woowgallery' ) . '</a>';

		add_action( 'woowgallery_media_button', $editor_id, $screen->post_type );
	}

	/**
	 * Checks for the existience of any WoowGallery shortcodes in the Post's content,
	 * storing this Gallery's ID in meta.
	 *
	 * @param int      $post_id The current post ID.
	 * @param \WP_POST $post    The current post object.
	 * @param bool     $update
	 */
	public function update_in_post_galleries( $post_id, $post, $update ) {

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

		$gallery_ids        = [];
		$gallery_ids_before = get_post_meta( $post->ID, '_woowgallery_galleries', true ) ?: [];

		// Check content for shortcodes.
		if ( strpos( $post->post_content, '[woowgallery' ) !== false ) {
			preg_match_all( '/' . get_shortcode_regex( [ Posttypes::GALLERY_POSTTYPE, Posttypes::DYNAMIC_POSTTYPE, Posttypes::ALBUM_POSTTYPE ] ) . '/', $post->post_content, $matches, PREG_SET_ORDER );
			if ( ! empty( $matches ) ) {

				// Iterate through shortcode matches, extracting the gallery ID and storing it in the meta.
				foreach ( $matches as $shortcode ) {
					$args = shortcode_parse_atts( $shortcode[3] );
					if ( isset( $args['id'] ) ) {
						$gallery_ids[] = (int) $args['id'];
					}
					if ( isset( $args['slug'] ) ) {
						$gallery = Gallery::get_instance( $args['slug'], $shortcode[2] );
						if ( $gallery->get_id() ) {
							$gallery_ids[] = $gallery->get_id();
						}
					}
				}
			}
		}

		$gallery_ids_added   = array_diff( $gallery_ids, $gallery_ids_before );
		$gallery_ids_removed = array_diff( $gallery_ids_before, $gallery_ids );

		// Update post ids in galleries.
		$this->update_gallery_post_ids( $post->ID, $gallery_ids_added, $gallery_ids_removed );

		if ( ! empty( $gallery_ids ) ) {
			update_post_meta( $post->ID, '_woowgallery_galleries', $gallery_ids );
		} elseif ( ! empty( $gallery_ids_before ) ) {
			delete_post_meta( $post->ID, '_woowgallery_galleries' );
		}
	}

	/**
	 * Checks for WoowGallery shortcodes in the given content.
	 *
	 * If found, adds or removes those shortcode IDs to the given Post ID
	 *
	 * @param int   $post_id             Post ID.
	 * @param array $gallery_ids_added   Add $post_id to galleries.
	 * @param array $gallery_ids_removed Remove $post_id from galleries.
	 */
	public function update_gallery_post_ids( $post_id, $gallery_ids_added, $gallery_ids_removed ) {

		// Iterate through each gallery.
		foreach ( $gallery_ids_added as $gallery_id ) {
			// Get Post IDs this Gallery is included in.
			$post_ids = get_post_meta( $gallery_id, '_woowgallery_posts', true ) ?: [];
			// Add the Post ID.
			$post_ids[] = $post_id;
			// Save.
			update_post_meta( $gallery_id, '_woowgallery_posts', array_values( array_unique( $post_ids ) ) );
		}

		// Iterate through each gallery.
		foreach ( $gallery_ids_removed as $gallery_id ) {
			// Get Post IDs this Gallery is included in.
			$post_ids = get_post_meta( $gallery_id, '_woowgallery_posts', true ) ?: [];
			// Remove the Post ID.
			$key = array_search( $post_id, $post_ids, true );
			if ( false !== $key ) {
				unset( $post_ids[ $key ] );
				$post_ids = array_values( $post_ids );
			}
			// Save.
			update_post_meta( $gallery_id, '_woowgallery_posts', $post_ids );
		}
	}

	/**
	 * Checks for the existience of any WoowGallery shortcodes in the Post's content,
	 * deleting this Post's ID in galleries meta.
	 *
	 * @param int $post_id Post ID.
	 */
	public function delete_post_id_in_galleries( $post_id ) {

		// Get galleries ids from Post meta.
		$gallery_ids_removed = get_post_meta( $post_id, '_woowgallery_galleries', true );

		if ( $gallery_ids_removed ) {
			// Update post ids in galleries.
			$this->update_gallery_post_ids( $post_id, [], $gallery_ids_removed );
		}

	}

	/**
	 * Update Post Thumbnail in WoowGalleries
	 *
	 * @param int    $meta_id    ID of updated metadata entry.
	 * @param int    $object_id  Post ID.
	 * @param string $meta_key   Meta key.
	 * @param mixed  $meta_value Meta value. This will be a PHP-serialized string representation of the value if
	 *                           the value is an array, an object, or itself a PHP-serialized string.
	 */
	public function updated_postmeta( $meta_id, $object_id, $meta_key, $meta_value ) {
		if ( ! in_array( $meta_key, [ '_thumbnail_id', '_media_copyright' ], true ) || empty( $meta_value ) ) {
			return;
		}
		// Get galleries ids from Post meta.
		$gallery_ids = get_post_meta( $object_id, '_woowgallery', true );
		if ( empty( $gallery_ids ) ) {
			return;
		}

		foreach ( $gallery_ids as $gallery_id ) {
			update_post_meta( $gallery_id, Gallery::GALLERY_UPDATE_META_KEY, 1 );
		}
	}

	/**
	 * Update Post terms in WoowGalleries
	 *
	 * @param int    $object_id  Object ID.
	 * @param array  $terms      An array of object terms.
	 * @param array  $tt_ids     An array of term taxonomy IDs.
	 * @param string $taxonomy   Taxonomy slug.
	 * @param bool   $append     Whether to append new terms to the old terms.
	 * @param array  $old_tt_ids Old array of term taxonomy IDs.
	 */
	public function set_object_terms( $object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids ) {
		if ( ! in_array( $taxonomy, [ 'post_tag', 'media_tag' ], true ) ) {
			$post_type = get_post_type( $object_id );
			if ( $post_type . '_tag' !== $taxonomy ) {
				return;
			}
		}
		// Get galleries ids from Post meta.
		$gallery_ids = get_post_meta( $object_id, '_woowgallery', true );
		if ( empty( $gallery_ids ) ) {
			return;
		}

		foreach ( $gallery_ids as $gallery_id ) {
			update_post_meta( $gallery_id, Gallery::GALLERY_UPDATE_META_KEY, 1 );
		}
	}

	/**
	 * Update gallery data if updated post has gallery
	 *
	 * @param int     $post_id     Post ID.
	 * @param WP_Post $post_after  Post object following the update.
	 * @param WP_Post $post_before Post object before the update.
	 */
	public function post_updated( $post_id, $post_after, $post_before ) {
		$galleries = get_post_meta( $post_id, '_woowgallery', true );

		if ( empty( $galleries ) ) {
			return;
		}

		foreach ( (array) $galleries as $gallery_id ) {
			update_post_meta( $gallery_id, Gallery::GALLERY_UPDATE_META_KEY, 1 );
		}

		if ( $post_before->post_status !== $post_after->post_status ) {
			$post_type = $post_after->post_type;
			foreach ( (array) $galleries as $gallery_id ) {
				$gallery = get_post( (int) $gallery_id );
				if ( empty( $gallery ) ) {
					continue;
				}

				$update_gallery = false;
				$gallery_data   = (array) json_decode( $gallery->post_content_filtered );
				// Update post status in gallery.
				foreach ( $gallery_data as $i => $data ) {
					if (
						(int) $data->id === $post_id
						&& ( 'post' === $data->type && $post_type === $data->subtype )
						&& $data->status !== $post_after->post_status
					) {
						$gallery_data[ $i ]->status = $post_after->post_status;
						$update_gallery             = true;
					}
				}

				if ( $update_gallery ) {
					$gallery->post_content_filtered = wp_unslash( wg_json_encode( array_values( $gallery_data ) ) );
					wp_update_post( $gallery );
				}
			}
		}
	}

	/**
	 * Deletes post data from galleries once the post being deleted.
	 *
	 * @param int $post_id The gallery ID being deleted.
	 */
	public function before_delete_post( $post_id ) {

		$galleries = get_post_meta( $post_id, '_woowgallery', true );
		// Only proceed if the post is attached to any WoowGallery galleries.
		if ( empty( $galleries ) ) {
			return;
		}

		$post_type = get_post_type( $post_id );
		foreach ( (array) $galleries as $gallery_id ) {
			$gallery = get_post( (int) $gallery_id );
			if ( empty( $gallery ) ) {
				continue;
			}

			$update_gallery = false;
			$gallery_data   = (array) json_decode( $gallery->post_content_filtered );
			// Remove post from the gallery.
			foreach ( $gallery_data as $i => $data ) {
				if ( (int) $data->id === $post_id && ( 'attachment' === $data->type || ( 'post' === $data->type && $post_type === $data->subtype ) ) ) {
					unset( $gallery_data[ $i ] );
					$update_gallery = true;
				}
			}

			if ( $update_gallery ) {
				$gallery->post_content_filtered = wp_unslash( wg_json_encode( array_values( $gallery_data ) ) );
				wp_update_post( $gallery );
			}
		}
	}

	/**
	 * Adds Modal Template
	 */
	public function add_modal_tpl() {
		if ( did_action( 'admin_footer' ) && did_action( 'wg_admin_footer' ) ) {
			return;
		}

		// Get current screen.
		$screen = get_current_screen();

		// Bail if we're not on the edit WOOW Post Type screen.
		if ( 'post' !== $screen->base ) {
			return;
		}

		Admin::load_template( 'modal-gallery' );
	}

	/**
	 * Callback for saving WoowGallery Data.
	 * Filters slashed post data just before it is inserted into the database.
	 *
	 * @param array $data    An array of slashed post data.
	 * @param array $postarr An array of sanitized, but otherwise unmodified post data.
	 *
	 * @return array
	 */
	public function wp_insert_wg_post_data( $data, $postarr ) {

		// Do nothing with $data if it's not WoowGallery CPT.
		if ( ! in_array( $data['post_type'], self::$wg_post_types, true ) ) {
			$_post_type             = woowgallery_POST( 'post_type' );
			$_post_content_filtered = isset( $_POST['post_content_filtered'] ) ? sanitize_post_field( 'post_content_filtered', $_POST['post_content_filtered'], $data['ID'], 'db' ) : '';
			if ( in_array( $_post_type, self::$wg_post_types, true ) && ! empty( $_post_content_filtered ) ) {
				$data['post_content_filtered'] = $_post_content_filtered;
			}
		}

		return $data;
	}

	/**
	 * Callback for updating WoowGallery.
	 *
	 * @param int     $post_id     Post ID.
	 * @param WP_Post $post_after  Post object following the update.
	 * @param WP_Post $post_before Post object before the update.
	 */
	public function wg_post_updated( $post_id, $post_after, $post_before ) {

		// Bail out if running an autosave, cron, revision or ajax.
		if (
			( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			|| ( defined( 'DOING_CRON' ) && DOING_CRON )
			|| 'auto-draft' === $post_after->post_status
			// Bail out if the user doesn't have the correct permissions to update the gallery.
			|| ! current_user_can( 'edit_post', $post_id )
		) {
			return;
		}

		$_post_type = woowgallery_POST( 'post_type' );
		if ( in_array( $_post_type, self::$wg_post_types, true ) ) {
			update_metadata( 'post', $post_id, '_data_before', $post_before->post_content_filtered );
			if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
				$data = Edit_Woowgallery::set_gallery_data( $post_id, $post_after, $post_before );
				if ( Posttypes::DYNAMIC_POSTTYPE !== $_post_type ) {
					update_metadata( 'post', $post_id, Gallery::GALLERY_MEDIA_COUNT_META_KEY, count( $data ) );
				}

				return;
			}
		}
	}

	/**
	 * Flush caches when the woowgallery post type is trashed.
	 *
	 * @param int $id The post ID being trashed.
	 */
	public function trash_wg_post( $id ) {

		$wgpost = get_post( $id );

		// Return early if not an WoowGallery.
		if ( ! in_array( $wgpost->post_type, self::$wg_post_types, true ) ) {
			return;
		}

		// Flush necessary gallery caches to ensure trashed galleries are not showing.
		woowgallery_flush_caches( $wgpost->ID, $wgpost->post_name );

		// Allow other addons to run routines when a Gallery is trashed.
		do_action( 'woowgallery_trash', $wgpost );
	}

	/**
	 * Flush caches when the woowgallery post type is untrashed.
	 *
	 * @param int $id The post ID being untrashed.
	 */
	public function untrash_wg_post( $id ) {

		$wgpost = get_post( $id );

		// Return early if not an WoowGallery.
		if ( ! in_array( $wgpost->post_type, self::$wg_post_types, true ) ) {
			return;
		}

		// Flush necessary gallery caches to ensure trashed galleries are not showing.
		woowgallery_flush_caches( $wgpost->ID, $wgpost->post_name );

		// Allow other addons to run routines when a Gallery is untrashed.
		do_action( 'woowgallery_untrash', $wgpost );
	}

	/**
	 * Flush caches when the woowgallery post type is deleted.
	 *
	 * @param int $postid Post ID.
	 */
	public function delete_wg_post( $postid ) {

		// Get post.
		$wgpost = get_post( $postid );

		// Return early if not an WoowGallery.
		if ( ! in_array( $wgpost->post_type, self::$wg_post_types, true ) ) {
			return;
		}

		// Delete gallery taxonomy on gallery delete.
		if ( Posttypes::GALLERY_POSTTYPE === $wgpost->post_type ) {
			$term = get_term_by( 'slug', $wgpost->post_name, Taxonomies::GALLERY_TAXONOMY_NAME );
			if ( ! empty( $term ) ) {
				wp_delete_term( $term->term_id, Taxonomies::GALLERY_TAXONOMY_NAME );
			}
		}

		// Update attachments meta.
		if ( Posttypes::DYNAMIC_POSTTYPE !== $wgpost->post_type ) {

			$data = json_decode( $wgpost->post_content_filtered );
			// Retrive attachmnet IDs from the $data.
			$att_array = array_map(
				function ( $item ) {
					return [
						'id'   => (int) $item->id,
						'type' => $item->type,
					];
				},
				array_filter(
					$data,
					function ( $item ) {
						return 'attachment' === $item->type || 'post' === $item->type;
					}
				)
			);

			$media_delete = (int) Settings::get_settings( 'media_delete' );

			foreach ( $att_array as $att ) {
				// Is attachment already in galleries?
				$has_gallery = get_post_meta( $att['id'], '_woowgallery', true ) ?: [];
				$has_gallery = array_diff( (array) $has_gallery, [ $postid ] );
				if ( count( $has_gallery ) ) {
					update_post_meta( $att['id'], '_woowgallery', $has_gallery );
				} else {
					delete_post_meta( $att['id'], '_woowgallery' );

					// Check if the media_delete setting is enabled and delete only images that aren't in another gallery.
					if ( ! empty( $media_delete ) && 'attachment' === $att['type'] ) {
						// If attachment parent is the Gallery ID we're OK to delete the image.
						$attachment = get_post( $att['id'] );
						if ( $attachment->post_parent === $wgpost->ID ) {
							wp_delete_attachment( $att['id'] );
							continue;
						}
					}
				}
				delete_post_meta( $att['id'], "_woowgallery_{$postid}" );
			}
		}

		// Flush necessary gallery caches to ensure trashed galleries are not showing.
		woowgallery_flush_caches( $wgpost->ID, $wgpost->post_name );

		// Allow other addons to run routines when a Gallery is deleted.
		do_action( 'woowgallery_delete', $wgpost );
	}

	/**
	 * Footer templates.
	 */
	public function wg_footer_templates() {
		if ( did_action( 'admin_footer' ) && did_action( 'wg_admin_footer' ) ) {
			return;
		}

		global $post;

		// Check we're on the WoowGallery CPT.
		if ( ! $post || ! in_array( $post->post_type, self::$wg_post_types, true ) ) {
			return;
		}

		if ( Posttypes::DYNAMIC_POSTTYPE !== $post->post_type ) {
			// Adds wpLink dialog for internal linking.
			if ( ! class_exists( '_WP_Editors', false ) ) {
				require_once ABSPATH . WPINC . '/class-wp-editor.php';
			}
			_WP_Editors::wp_link_dialog();

			if ( Posttypes::GALLERY_POSTTYPE === $post->post_type ) {
				Admin::load_template( 'wp-media-insert-settings' );
			}
		}

		Admin::load_template( 'modal-portal-vue' );
	}

}
