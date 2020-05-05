<?php
/**
 * Abstract Edit WoowGallery class.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

namespace WoowGallery\Admin;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

use _WP_Editors;
use WoowGallery\Gallery;
use WoowGallery\Posttypes;
use WoowGallery\Tools\Cropping;
use WP_Post;

/**
 * Class Edit_Gallery
 */
class Edit_Woowgallery {

	/**
	 * Holds the post_type.
	 *
	 * @var string
	 */
	public $post_type;

	/**
	 * Primary class constructor.
	 *
	 * @param string $post_type Post Type.
	 */
	public function __construct( $post_type ) {

		$this->post_type = $post_type;

		// Metaboxes.
		add_action( "add_meta_boxes_{$this->post_type}", [ $this, 'sanitize_metaboxes' ], 1 );
		add_action( "add_meta_boxes_{$this->post_type}", [ $this, 'add_meta_boxes' ], 5 );

		add_action( 'wg_admin_footer', [ $this, 'footer_templates' ] );
		add_action( 'admin_footer', [ $this, 'footer_templates' ] );

		// Remove gallery association with Albums.
		add_action( 'before_delete_post', [ $this, 'gallery_delete' ] );

		add_action( 'wp_trash_post', [ $this, 'trash_post' ] );
		add_action( 'untrash_post', [ $this, 'untrash_post' ] );
		add_action( 'delete_post', [ $this, 'delete_post' ] );
	}

	/**
	 * Set Gallery Content.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $data    Gallery data.
	 *
	 * @return array
	 */
	public static function set_gallery_content( $post_id, $data = null ) {
		$post = get_post( (int) $post_id );
		if ( empty( $post ) ) {
			return [];
		}
		if ( ! in_array( $post->post_type, [ Posttypes::GALLERY_POSTTYPE, Posttypes::ALBUM_POSTTYPE ], true ) ) {
			if ( Posttypes::DYNAMIC_POSTTYPE === $post->post_type ) {
				return Edit_Dynamic_Gallery::set_dynamic_content( $post_id, $data );
			} else {
				return [];
			}
		}

		if ( null === $data ) {
			$data = (array) json_decode( $post->post_content_filtered, true );
		}
		$content = [];
		foreach ( $data as $item ) {
			$content[] = woowgallery_full_post_data( $item, $post );
		}
		$content = array_filter( $content );
		update_post_meta( $post_id, Gallery::GALLERY_CONTENT_META_KEY, $content );
		update_post_meta( $post_id, Gallery::GALLERY_UPDATE_META_KEY, 0 );

		return $content;
	}

	/**
	 * Footer templates.
	 */
	public function footer_templates() {
		if ( did_action( 'admin_footer' ) && did_action( 'wg_admin_footer' ) ) {
			return;
		}

		global $post;

		// Check we're on the WoowGallery CPT.
		if ( ! $post || $this->post_type !== $post->post_type ) {
			return;
		}

		if ( Posttypes::DYNAMIC_POSTTYPE !== $post->post_type ) {
			// Adds wpLink dialog for internal linking.
			if ( ! class_exists( '_WP_Editors', false ) ) {
				require_once( ABSPATH . WPINC . '/class-wp-editor.php' );
			}
			_WP_Editors::wp_link_dialog();

			if ( Posttypes::GALLERY_POSTTYPE === $post->post_type ) {
				Admin::load_template( 'wp-media-insert-settings' );
			}
		}

		Admin::load_template( 'modal-portal-vue' );
	}

	/**
	 * Creates metaboxes for handling and managing galleries.
	 *
	 * @param WP_Post $post The Post.
	 */
	public function add_meta_boxes( $post ) {

		// Add WoowGallery metaboxes.
		// Displays the media in the WoowGallery.
		add_action( 'edit_form_after_editor', [ $this, 'meta_box_gallery' ], 1 );

		// Load all tabs.
		add_action( 'woowgallery_tab_gallery', [ $this, 'tab_gallery' ] );
		add_action( 'woowgallery_tab_config', [ $this, 'tab_config' ] );
		add_action( 'woowgallery_tab_misc', [ $this, 'tab_misc' ] );

		// Display the Gallery Code metabox if we're editing an existing Gallery.
		if ( 'auto-draft' !== $post->post_status ) {
			add_meta_box( 'woowgallery-tips', __( 'WoowGallery Tips', 'woowgallery' ), [ $this, 'meta_box_gallery_tips' ], $this->post_type, 'side', 'default' );
			add_meta_box( 'woowgallery-code', __( 'WoowGallery Code', 'woowgallery' ), [ $this, 'meta_box_gallery_code' ], $this->post_type, 'side', 'default' );
		}

		add_filter( 'teeny_mce_plugins', [ $this, 'mce_plugins' ] );
		add_filter( 'tiny_mce_plugins', [ $this, 'mce_plugins' ] );
		add_filter( 'mce_css', '__return_empty_string' );
	}

	/**
	 * Filter plugins for wp_editor.
	 *
	 * @param array $plugins Array of MCE plugins.
	 *
	 * @return array
	 */
	public function mce_plugins( $plugins ) {
		return array_filter(
			$plugins,
			function ( $plugin ) {
				return 'fullscreen' !== $plugin;
			}
		);
	}

	/**
	 * Removes all the third party metaboxes on the WoowGallery CPT.
	 *
	 * @global array $wp_meta_boxes Array of registered metaboxes.
	 */
	public function sanitize_metaboxes() {

		global $wp_meta_boxes;

		// This is the post type you want to target. Adjust it to match yours.
		$post_type = $this->post_type;

		// These are the metabox IDs you want to pass over. They don't have to match exactly. preg_match will be run on them.
		$pass_over_defaults = [ 'submitdiv', 'postimagediv', 'woowgallery' ];

		//if ( Settings::get_setting( 'standalone_gallery' ) ) {
		//	$pass_over_defaults[] = 'slugdiv';
		//	$pass_over_defaults[] = 'authordiv';
		//	$pass_over_defaults[] = 'wpseo_meta';
		//}

		$pass_over = apply_filters( 'woowgallery_metabox_ids', $pass_over_defaults, $post_type );

		// All the metabox contexts you want to check.
		$contexts_defaults = [ 'normal', 'advanced', 'side' ];
		$contexts          = apply_filters( 'woowgallery_metabox_contexts', $contexts_defaults, $post_type );

		// All the priorities you want to check.
		$priorities_defaults = [ 'high', 'core', 'default', 'low' ];
		$priorities          = apply_filters( 'woowgallery_metabox_priorities', $priorities_defaults, $post_type );

		// Loop through and target each context.
		foreach ( $contexts as $context ) {
			// Now loop through each priority and start the purging process.
			foreach ( $priorities as $priority ) {
				if ( isset( $wp_meta_boxes[ $post_type ][ $context ][ $priority ] ) ) {
					foreach ( (array) $wp_meta_boxes[ $post_type ][ $context ][ $priority ] as $id => $metabox_data ) {
						// If the metabox ID to pass over matches the ID given, remove it from the array and continue.
						if ( in_array( $id, $pass_over, true ) ) {
							unset( $pass_over[ $id ] );
							continue;
						}

						// Otherwise, loop through the pass_over IDs and if we have a match, continue.
						foreach ( $pass_over as $to_pass ) {
							if ( preg_match( '#^' . $id . '#i', $to_pass ) ) {
								continue;
							}
						}

						// If we reach this point, remove the metabox completely.
						unset( $wp_meta_boxes[ $post_type ][ $context ][ $priority ][ $id ] );
					}
				}
			}
		}
	}

	/**
	 * Displays the Gallery main metabox.
	 *
	 * @param WP_Post $post The current post object.
	 */
	public function meta_box_gallery( $post ) {

		$wg       = new Gallery( $post->ID, $post->post_type );
		$gallery  = $wg->get_gallery();
		$tabs     = $this->get_gallery_editor_tabs_nav();
		$settings = Settings::get_settings();

		// Load view.
		Admin::load_template(
			'gallery-edit-page',
			compact( 'post', 'gallery', 'tabs', 'settings' )
		);

	}

	/**
	 * Returns the tabs to be displayed in the gallery metabox.
	 *
	 * @return array Array of tab navigation.
	 */
	public function get_gallery_editor_tabs_nav() {

		$tabs = [
			'gallery' => [
				'label' => __( 'Gallery', 'woowgallery' ),
				'icon'  => 'dashicons-screenoptions',
			],
			'config'  => [
				'label' => __( 'Config', 'woowgallery' ),
				'icon'  => 'dashicons-admin-generic',
			],
		];
		$tabs = apply_filters( 'woowgallery_editor_tabs_nav', $tabs, $this->post_type );

		// "Misc" tab is required.
		$tabs['misc'] = [
			'label' => __( 'Misc', 'woowgallery' ),
			'icon'  => 'dashicons-admin-tools',
		];

		return $tabs;

	}

	/**
	 * Callback for displaying the Gallery Tips metabox.
	 *
	 * @param WP_Post $post The current post object.
	 */
	public function meta_box_gallery_tips( $post ) {
		// Load view.
		Admin::load_template( 'gallery-metabox-tips', compact( 'post' ) );
	}

	/**
	 * Callback for displaying the Gallery Code metabox.
	 *
	 * @param WP_Post $post The current post object.
	 */
	public function meta_box_gallery_code( $post ) {
		// Load view.
		Admin::load_template( 'gallery-metabox-shortcodes', compact( 'post' ) );
	}

	/**
	 * Callback for displaying the gallery preview tab.
	 *
	 * @param WP_Post $post The current post object.
	 */
	public function tab_gallery( $post ) {
		// Load view.
		Admin::load_template( 'gallery-media', compact( 'post' ) );
	}

	/**
	 * Callback for displaying the template config tab.
	 *
	 * @param WP_Post $post The current post object.
	 */
	public function tab_config( $post ) {
		// Load view.
		Admin::load_template( 'gallery-skin-config', compact( 'post' ) );
	}

	/**
	 * Callback for displaying the settings UI for the Misc tab.
	 *
	 * @param WP_Post $post The current post object.
	 */
	public function tab_misc( $post ) {
		// Load view.
		Admin::load_template( 'gallery-misc-settings', compact( 'post' ) );
	}

	/**
	 * Deletes gallery data from albums once the gallery being deleted.
	 *
	 * @param int $post_id The gallery ID being deleted.
	 */
	public function gallery_delete( $post_id ) {
		if ( ! in_array( $this->post_type, [ Posttypes::GALLERY_POSTTYPE, Posttypes::DYNAMIC_POSTTYPE ], true ) || get_post_type( $post_id ) !== $this->post_type ) {
			return;
		}

		$albums = get_post_meta( $post_id, '_woowgallery', true );
		// Only proceed if the gallery is attached to any WoowGallery albums.
		if ( empty( $albums ) ) {
			return;
		}

		foreach ( (array) $albums as $album_id ) {
			$album = get_post( (int) $album_id );
			if ( empty( $album ) || Posttypes::ALBUM_POSTTYPE !== $album->post_type ) {
				continue;
			}

			$update_album = false;
			$album_data   = (array) json_decode( $album->post_content_filtered );
			// Remove the gallery association.
			foreach ( $album_data as $i => $data ) {
				if ( (int) $data->id === $post_id && Posttypes::GALLERY_POSTTYPE === $data->subtype ) {
					unset( $album_data[ $i ] );
					$update_album = true;
				}
			}

			if ( $update_album ) {
				$album->post_content_filtered = wg_json_encode( array_values( $album_data ) );
				wp_update_post( $album );
			}
		}
	}

	/**
	 * Flush caches when the woowgallery post type is trashed.
	 *
	 * @param int $id The post ID being trashed.
	 */
	public function trash_post( $id ) {

		$wgpost = get_post( $id );

		// Return early if not an WoowGallery.
		if ( $this->post_type !== $wgpost->post_type ) {
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
	public function untrash_post( $id ) {

		$wgpost = get_post( $id );

		// Return early if not an WoowGallery.
		if ( $this->post_type !== $wgpost->post_type ) {
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
	public function delete_post( $postid ) {

		// Get post.
		$wgpost = get_post( $postid );

		// Return early if not an WoowGallery.
		if ( $this->post_type !== $wgpost->post_type ) {
			return;
		}

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

		// Flush necessary gallery caches to ensure trashed galleries are not showing.
		woowgallery_flush_caches( $wgpost->ID, $wgpost->post_name );

		// Allow other addons to run routines when a Gallery is deleted.
		do_action( 'woowgallery_delete', $wgpost );
	}

	/**
	 * Callback for saving attached posts meta
	 *
	 * @param int|array $att_ids   Posts IDs.
	 * @param int       $wgpost_id The Gallery ID.
	 */
	public function update_attachments_woowgallery_meta( $att_ids, $wgpost_id ) {
		global $wpdb;

		if ( ! is_array( $att_ids ) ) {
			$att_ids = [ $att_ids ];
		}

		$found_ids = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s", "_woowgallery_{$wgpost_id}" ) );

		$att_ids   = array_map( 'absint', $att_ids );
		$found_ids = array_map( 'absint', $found_ids );

		$add_ids = array_diff( $att_ids, $found_ids );
		foreach ( $add_ids as $id ) {
			// Is attachment already in galleries?
			if ( get_post_meta( $id, "_woowgallery_{$wgpost_id}", true ) ) {
				continue;
			}
			$has_gallery = get_post_meta( $id, '_woowgallery', true ) ?: [];
			array_push( $has_gallery, $wgpost_id );
			update_post_meta( $id, '_woowgallery', array_values( array_unique( $has_gallery ) ) );
			update_post_meta( $id, "_woowgallery_{$wgpost_id}", time() );
		}

		$remove_ids = array_diff( $found_ids, $att_ids );
		foreach ( $remove_ids as $id ) {
			delete_post_meta( $id, "_woowgallery_{$wgpost_id}" );
			// Is attachment in few galleries?
			$has_gallery = get_post_meta( $id, '_woowgallery', true ) ?: [];
			$has_gallery = array_diff( $has_gallery, [ $wgpost_id ] );
			if ( count( $has_gallery ) ) {
				update_post_meta( $id, '_woowgallery', $has_gallery );
			} else {
				delete_post_meta( $id, '_woowgallery' );
			}
		}
	}

	/**
	 * Set Gallery Cover if it is not already set.
	 *
	 * @param WP_Post $post    The current post object.
	 * @param array   $content Gallery data.
	 */
	public function set_gallery_cover_from_content( $post, $content ) {
		if ( has_post_thumbnail( $post ) || ! count( $content ) ) {
			return;
		}

		$thumbnail_id = 0;
		foreach ( $content as $item ) {
			if ( empty( $item['image_id'] ) ) {
				continue;
			}
			$thumbnail_id = (int) $item['image_id'];
			break;
		}

		if ( $thumbnail_id ) {
			$dims = woowgallery_get_resize_dimensions( $post );

			Cropping::resize_image(
				$thumbnail_id,
				$dims['thumb']
			);
			Cropping::resize_image(
				$thumbnail_id,
				$dims['image']
			);

			set_post_thumbnail( $post, $thumbnail_id );
		}
	}
}
