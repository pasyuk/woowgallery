<?php
/**
 * Edit Album class.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

namespace WoowGallery\Admin;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

use WoowGallery\Gallery;
use WoowGallery\Posttypes;
use WoowGallery\Rest_Routes;
use WP_Post;

/**
 * Class Edit_Album
 */
class Edit_Album extends Edit_Woowgallery {

	/**
	 * Primary class constructor.
	 */
	public function __construct() {
		parent::__construct( Posttypes::ALBUM_POSTTYPE );

		// Scripts and styles.
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );

		add_filter( 'wp_insert_post_data', [ $this, 'wp_insert_album_data' ], 10, 2 );
		add_action( "save_post_{$this->post_type}", [ $this, 'save_album' ], 10, 3 );

		add_action( 'woowgallery_media_buttons', [ $this, 'media_buttons' ] );
		add_filter( 'woowgallery_editor_tabs_nav', [ $this, 'editor_tabs_nav' ], 10, 2 );
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
		wp_enqueue_style( WOOWGALLERY_SLUG . '-edit-woowgallery-style' );
		wp_enqueue_script( WOOWGALLERY_SLUG . '-edit-album-script' );

		// Link Search.
		wp_enqueue_style( 'editor-buttons' );
		wp_enqueue_script( 'wplink' );

		wp_enqueue_style( WOOWGALLERY_SLUG . '-editor-modal-style' );
		wp_enqueue_script( WOOWGALLERY_SLUG . '-editor-modal-script' );

		Settings::enqueue_code_editor();

		// Fire a hook to load custom metabox scripts.
		do_action( 'woowgallery_edit_album_scripts' );

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
		$wg       = Gallery::get_instance( $post->ID, $this->post_type );

		$js_data = [
			'siteurl'           => site_url(),
			'view_mode'         => $wg->get_editor_settings( 'view', $settings['edit_gallery_view'] ),
			'sortby'            => $wg->get_settings( 'sortby', 'custom' ),
			'sortorder'         => $wg->get_settings( 'sortorder', 'asc' ),
			'per_page'          => (int) $wg->get_editor_settings( 'per_page', $settings['edit_album_per_page'] ),
			'icons_url'         => plugins_url( 'assets/images/icons', WOOWGALLERY_FILE ),
			'selection_prepend' => (int) $settings['selection_prepend'],
			'bulkEdit'          => [
				'status'      => '',
				'title'       => '',
				'title_src'   => '',
				'caption'     => '',
				'caption_src' => '',
				'alt'         => '',
				'alt_src'     => '',
				'link'        => [
					'url'         => '',
					'text'        => '',
					'target'      => '',
					'url_change'  => false,
					'text_change' => false,
				],
				'tags'        => '',
			],
			'default_skin'      => $settings['default_skin'],
			'default_lightbox'  => $settings['default_lightbox'],
		];

		if ( 'post-new.php' !== $pagenow ) {
			$js_data['selected_skin']     = $wg->get_skin_slug() . ': _custom';
			$js_data['selected_lightbox'] = $wg->get_lightbox_slug();
		}

		return array_merge( $l10n, $js_data );
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
	public function wp_insert_album_data( $data, $postarr ) {

		// Do nothing with $data if it's not WoowGallery CPT.
		if ( $this->post_type !== $data['post_type'] ) {
			return $data;
		}

		$gallery_data = (array) json_decode( wp_unslash( $postarr['post_content_filtered'] ) );
		$data_changed = false;

		// Check for existing Attachments.
		$insert_ids = array_map(
			function ( $item ) {
				return (int) $item->id;
			},
			array_filter(
				$gallery_data,
				function ( $item ) {
					return Posttypes::GALLERY_POSTTYPE === $item->subtype;
				}
			)
		);

		if ( ! empty( $insert_ids ) ) {
			$media_ids = (array) get_posts(
				[
					'post_type'      => Posttypes::GALLERY_POSTTYPE,
					'post_status'    => [ 'future', 'publish', 'private' ],
					'post__in'       => $insert_ids,
					'posts_per_page' => - 1,
					'fields'         => 'ids',
				]
			);
			if ( count( $insert_ids ) > count( $media_ids ) ) {
				foreach ( $gallery_data as $i => $item ) {
					if ( ! in_array( (int) $item->id, $media_ids, true ) ) {
						unset( $gallery_data[ $i ] );
					}
				}
				$data_changed = true;
			}
		}

		if ( $data_changed ) {
			$data['post_content_filtered'] = wp_slash( wp_json_encode( array_values( $gallery_data ) ) );
		}

		return $data;
	}

	/**
	 * Callback for saving WoowGallery.
	 *
	 * @param int     $post_id The current post ID.
	 * @param WP_POST $post    The current post object.
	 * @param bool    $update  Is Post update?.
	 */
	public function save_album( $post_id, $post, $update ) {
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

			// Check if this is a Quick Edit request.
			if ( isset( $_POST['_inline_edit'] ) ) {
				// Here we can update specific fields in the Quick Edit screen.
				return;
			}

			$data = (array) json_decode( $post->post_content_filtered, true );
			update_post_meta( $post_id, Gallery::GALLERY_MEDIA_COUNT_META_KEY, count( $data ) );
			update_metadata( 'post', $post_id, Gallery::GALLERY_UPDATE_META_KEY, 1 );

			return;
		}

		$data = parent::set_gallery_data( $post_id, $post, $update );
		update_post_meta( $post_id, Gallery::GALLERY_MEDIA_COUNT_META_KEY, count( $data ) );

		// Retrive attachmnet IDs from the $data.
		$att_ids = array_map(
			function ( $item ) {
				return (int) $item['id'];
			},
			array_filter(
				$data,
				function ( $item ) {
					return Posttypes::GALLERY_POSTTYPE === $item['subtype'];
				}
			)
		);

		// Update attachments meta.
		if ( ! empty( $att_ids ) ) {
			$this->update_attachments_woowgallery_meta( $att_ids, $post_id );
		}

		// Fire a hook for addons.
		do_action( 'woowgallery_saved', $post_id, $post );

		$background = new Rest_Routes();
		$background->background_request( [ 'id' => $post_id ], 'crop-images' );

		// Finally, flush all gallery caches to ensure everything is up to date.
		woowgallery_flush_caches( $post_id, $post->post_name );
	}

	/**
	 * Callback for displaying the Gallery Media Buttons.
	 *
	 * @param WP_Post $post The current post object.
	 */
	public function media_buttons( $post ) {

		if ( $post->post_type !== $this->post_type ) {
			return;
		}
		?>
		<button type="button" class="button button-primary woowgallery-add-media woowgallery-modal-button" data-editor="woowgallery-preview" data-modal="woowgallery" data-posttype="<?php echo esc_attr( Posttypes::GALLERY_POSTTYPE ); ?>" title="<?php esc_attr_e( 'Select Galleries', 'woowgallery' ); ?>">
			<span class="dashicons dashicons-format-gallery"></span><span class="btn-label"><?php esc_html_e( 'Add Galleries to Album', 'woowgallery' ); ?></span>
		</button>
		<?php
	}

	/**
	 * Filter the tabs to be displayed in the gallery metabox.
	 *
	 * @param array  $tabs      Tabs.
	 * @param string $post_type Post Type.
	 *
	 * @return array Array of tab navigation.
	 */
	public function editor_tabs_nav( $tabs, $post_type ) {
		if ( $post_type === $this->post_type ) {
			$tabs['gallery']['label'] = __( 'Album', 'woowgallery' );
		}

		return $tabs;
	}

}
