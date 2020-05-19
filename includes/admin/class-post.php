<?php
/**
 * Post class.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

namespace WoowGallery\Admin;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

use WoowGallery\Gallery;
use WoowGallery\Posttypes;
use WP_Post;

/**
 * Class Post
 */
class Post {

	/**
	 * Primary class constructor.
	 */
	public function __construct() {

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

		$post_types = apply_filters( 'woowgallery_posttypes', [ Posttypes::GALLERY_POSTTYPE, Posttypes::ALBUM_POSTTYPE, Posttypes::DYNAMIC_POSTTYPE ] );
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

		// Get post.
		$post = get_post( $post_id );
		if ( ! $post ) {
			return;
		}

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

		// Don't do anything if this is a Post Revision.
		if ( wp_is_post_revision( $post ) ) {
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
						$gallery = new Gallery( $args['slug'], $shortcode[2] );
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
		if ( '_thumbnail_id' !== $meta_key || empty( $meta_value ) ) {
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
					$gallery->post_content_filtered = wg_json_encode( array_values( $gallery_data ) );
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
				$gallery->post_content_filtered = wg_json_encode( array_values( $gallery_data ) );
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

}
