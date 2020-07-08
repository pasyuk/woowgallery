<?php
/**
 * Ajax class.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

namespace WoowGallery\Admin;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

use WoowGallery\Gallery;
use WoowGallery\Shortcodes;
use WoowGallery\Skins;

/**
 * Class Ajax
 */
class Ajax {

	/**
	 * Primary class constructor.
	 */
	public function __construct() {

		add_action( 'wp_ajax_woowgallery_get_media_data', [ $this, 'get_media_data' ] );
		add_action( 'wp_ajax_woowgallery_set_media_copyright', [ $this, 'set_media_copyright' ] );
		add_action( 'wp_ajax_woowgallery_set_media_tags', [ $this, 'set_media_tags' ] );
		add_action( 'wp_ajax_woowgallery_bulk_set_media_data', [ $this, 'bulk_set_media_data' ] );

		add_action( 'wp_ajax_woowgallery_dynamic_refresh_taxonomy_terms', [ $this, 'refresh_taxonomy_terms' ] );
		add_action( 'wp_ajax_woowgallery_dynamic_refresh_flagallery_source', [ $this, 'refresh_flagallery_source' ] );
		add_action( 'wp_ajax_woowgallery_dynamic_fetch_query', [ $this, 'dynamic_fetch_query' ] );
		add_action( 'wp_ajax_woowgallery_cache_clear', [ $this, 'gallery_cache_clear' ] );

		add_action( 'wp_ajax_nopriv_woowgallery_skin_assets', [ $this, 'get_skin_assets' ] );
		add_action( 'wp_ajax_woowgallery_skin_assets', [ $this, 'get_skin_assets' ] );
		add_action( 'wp_ajax_woowgallery_save_skin_data', [ $this, 'save_skin_data' ] );
		add_action( 'wp_ajax_woowgallery_delete_skin_preset', [ $this, 'delete_skin_preset' ] );

		add_filter( 'ajax_query_attachments_args', [ $this, 'ajax_query_attachments_args' ] );

	}

	/**
	 * Filters the arguments passed to WP_Query during an Ajax
	 * call for querying attachments.
	 *
	 * @param array $query An array of query variables.
	 *
	 * @return array
	 */
	public function ajax_query_attachments_args( $query ) {

		$tax_query = [];

		foreach ( get_object_taxonomies( 'attachment', 'names' ) as $taxname ) {
			if ( ! empty( $query[ $taxname ] ) ) {
				if ( is_numeric( $query[ $taxname ] ) || is_array( $query[ $taxname ] ) ) {
					$tax_query[] = [
						'taxonomy' => $taxname,
						'field'    => 'term_id',
						'terms'    => (array) $query[ $taxname ],
					];
					unset( $query[ $taxname ] );
				} elseif ( 'not_in' === $query[ $taxname ] || 'in' === $query[ $taxname ] ) {
					$terms = get_terms(
						$taxname,
						[
							'fields' => 'ids',
							'get'    => 'all',
						]
					);

					$tax_query[] = [
						'taxonomy' => $taxname,
						'field'    => 'term_id',
						'terms'    => $terms,
						'operator' => strtoupper( str_replace( '_', ' ', $query[ $taxname ] ) ),
					];
					unset( $query[ $taxname ] );
				}
			}
		}

		if ( ! empty( $tax_query ) ) {
			$tax_query['relation'] = 'AND';
			$query['tax_query']    = $tax_query;
		}

		return $query;
	}

	/**
	 * WoowGallery Media Data.
	 */
	public function get_media_data() {
		$media_post_data = json_decode( woowgallery_POST( 'media', '[]' ) );

		$media_data = [];
		foreach ( $media_post_data as $item_id ) {
			$media = get_post( $item_id );

			if ( ! $media ) {
				continue;
			}
			if ( 'attachment' === $media->post_type ) {
				$media_data[] = woowgallery_prepare_attachment_data( $media );
			} else {
				$media_data[] = woowgallery_prepare_post_data( $media );
			}
		}

		if ( $media_data ) {
			wp_send_json_success( $media_data );
		} else {
			wp_send_json_error();
		}
	}

	/**
	 * WoowGallery Media Copyright update.
	 */
	public function set_media_copyright() {
		// Bail out if we fail a security check.
		woowgallery_verify_nonce( 'ajax' );

		$media_id = (int) woowgallery_POST( 'media_id', 0 );
		if ( $media_id ) {
			$copyright_trim = wp_strip_all_tags( woowgallery_POST( 'copyright', '' ) );
			update_metadata( 'post', $media_id, '_media_copyright', $copyright_trim );

			wp_send_json_success();
		}

		wp_send_json_error();
	}

	/**
	 * WoowGallery Media Tags update.
	 */
	public function set_media_tags() {
		// Bail out if we fail a security check.
		woowgallery_verify_nonce( 'ajax' );

		$media_id = (int) woowgallery_POST( 'media_id', 0 );
		if ( $media_id ) {
			$taxonomy   = woowgallery_POST( 'taxonomy', 'post_tag' );
			$taxonomies = get_post_taxonomies( $media_id );
			if ( in_array( $taxonomy, $taxonomies, true ) ) {
				$terms  = array_map( 'trim', explode( ',', woowgallery_POST( 'tags', '' ) ) );
				$tt_ids = wp_set_object_terms( $media_id, $terms, $taxonomy );

				wp_send_json_success( $tt_ids );
			}
		}

		wp_send_json_error();
	}

	/**
	 * WoowGallery bulk Media Tags and Meta update.
	 */
	public function bulk_set_media_data() {
		// Bail out if we fail a security check.
		woowgallery_verify_nonce( 'ajax' );

		$medias = json_decode( woowgallery_POST( 'media', '[]' ) );
		if ( $medias ) {
			$terms          = array_filter( array_map( 'trim', explode( ',', woowgallery_POST( 'tags', '' ) ) ) );
			$copyright      = woowgallery_POST( 'copyright', '' );
			$copyright_trim = wp_strip_all_tags( $copyright );
			foreach ( $medias as $media ) {
				$media_id = (int) $media->id;
				if ( ! empty( $terms ) ) {
					if ( 'attachment' === $media->type ) {
						wp_set_object_terms( $media_id, $terms, 'media_tag', true );
					} elseif ( 'post' === $media->type ) {
						if ( is_object_in_term( $media_id, 'post_tag' ) ) {
							wp_set_object_terms( $media_id, $terms, 'post_tag', true );
						} elseif ( taxonomy_exists( $media->subtype . '_tag' ) && is_object_in_term( $media->id, $media->subtype . '_tag' ) ) {
							wp_set_object_terms( $media_id, $terms, $media->subtype . '_tag', true );
						}
					}
				}
				if ( 'attachment' === $media->type && ! empty( $copyright ) ) {
					update_metadata( 'post', $media_id, '_media_copyright', $copyright_trim );
				}
			}

			wp_send_json_success();
		}

		wp_send_json_error();
	}

	/**
	 * Refreshes the taxonomy terms list to show available terms for the selected post types.
	 */
	public function refresh_taxonomy_terms() {

		$post_type      = (array) woowgallery_GET( 'post_type', [] );
		$terms_ralation = woowgallery_GET( 'terms_relation', 'IN' );
		$wg_taxonomies  = woowgallery_get_taxonomy_terms( $post_type, ( 'IN' !== $terms_ralation ) );

		wp_send_json_success( $wg_taxonomies );
	}

	/**
	 * Refreshes the available Flagallery galleries.
	 */
	public function refresh_flagallery_source() {
		global $flagdb;

		$gallerylist = $flagdb->find_all_galleries( 'title', 'ASC', true );
		if ( ! empty( $gallerylist ) ) {
			$gallerylist = array_values( $gallerylist );
		}

		wp_send_json_success( $gallerylist );
	}

	/**
	 * Fetch Query for Dynamic Galeries
	 */
	public function dynamic_fetch_query() {

		$json = woowgallery_GET( 'json' );
		if ( empty( $json ) ) {
			wp_send_json_error( __( 'Empty Query', 'woowgallery' ) );
		}

		$gallery_id = (int) woowgallery_GET( 'gallery_id', 0 );
		$query      = (array) json_decode( $json, true );
		try {
			$query_content = Edit_Dynamic_Gallery::get_dynamic_query( $query );

			if ( empty( $query_content['errors'] ) && ! empty( $query_content['posts'] ) ) {
				if ( $gallery_id ) {
					// Cache fetched content.
					set_transient( 'woowgallery_fetch_' . $gallery_id, $query_content, 6 * HOUR_IN_SECONDS );
				}
				// Add edit link for posts.
				if ( 'wp' === $query['query_type'] && current_user_can( 'edit_posts' ) ) {
					foreach ( $query_content['posts'] as $i => $item ) {
						if ( current_user_can( 'edit_post', (int) $item['id'] ) ) {
							$query_content['posts'][ $i ]['edit_link'] = get_edit_post_link( (int) $item['id'], 'raw' );
						}
					}
				}
			} elseif ( $gallery_id ) {
				// Clear cache for previously fetched content.
				delete_transient( 'woowgallery_fetch_' . $gallery_id );
			}

			wp_send_json_success( $query_content );
		} catch ( \Exception $e ) {
			if ( $gallery_id ) {
				// Clear cache for previously fetched content.
				delete_transient( 'woowgallery_fetch_' . $gallery_id );
			}
			wp_send_json_error( $e->getMessage() );
		}
	}

	/**
	 * Clear cache for gallery ID
	 */
	public function gallery_cache_clear() {

		$cache_clear_id = (int) woowgallery_POST( 'id' );
		if ( ! empty( $cache_clear_id ) ) {
			if ( metadata_exists( 'post', $cache_clear_id, Gallery::GALLERY_UPDATE_META_KEY ) ) {
				update_post_meta( $cache_clear_id, Gallery::GALLERY_UPDATE_META_KEY, 1 );
			}
			wp_send_json_success();
		}

		wp_send_json_error();
	}

	/**
	 * Get Skin Assets.
	 */
	public function get_skin_assets() {

		$skin  = woowgallery_GET( 'skin' );
		$skins = Skins::get_instance()->get_skins();
		if ( ! $skin || empty( $skins[ $skin ] ) ) {
			die();
		}

		$lightbox = woowgallery_GET( 'lightbox' );
		$skin     = $skins[ $skin ];
		ob_start();
		if ( ! empty( $lightbox ) ) {
			wp_enqueue_script( $lightbox );
		}
		Shortcodes::load_skin_css( $skin->slug );
		Shortcodes::load_skin_js( $skin->slug );
		wp_print_footer_scripts();
		$output = ob_get_clean();

		wp_send_json_success( $output );
	}

	/**
	 * Save Skin Preset Data.
	 */
	public function save_skin_data() {
		// Bail out if we fail a security check.
		woowgallery_verify_nonce( 'skin_settings_save' );

		$skin          = woowgallery_POST( 'skin' );
		$preset        = trim( woowgallery_POST( 'preset', 'default' ) );
		$data          = woowgallery_POST( 'data', '{}' );
		$default_reset = woowgallery_POST( 'default_reset' );

		if ( ! $skin || ! $preset ) {
			wp_send_json_error( __( 'Something went wrong.', 'woowbox' ) );
		}

		$skins_data = get_option( Skins::PRESETS_KEY, [] );
		if ( $default_reset ) {
			unset( $skins_data[ $skin ]['default'] );
		} else {
			$skins_data[ $skin ][ $preset ] = json_decode( $data, JSON_OBJECT_AS_ARRAY );
			ksort( $skins_data[ $skin ] );
			ksort( $skins_data );
		}

		update_option( Skins::PRESETS_KEY, $skins_data );

		wp_send_json_success( sprintf( __( 'Settings saved (`%s` preset)', 'woowbox' ), $preset ) );
	}

	/**
	 * Delete Skin Preset.
	 */
	public function delete_skin_preset() {
		// Bail out if we fail a security check.
		woowgallery_verify_nonce( 'skin_settings_save' );

		$skin   = woowgallery_POST( 'skin' );
		$preset = woowgallery_POST( 'preset', 'default' );

		if ( ! $skin || 'default' === $preset ) {
			wp_send_json_error( __( 'Something went wrong.', 'woowbox' ) );
		}

		$settings_skin  = Settings::get_settings( 'default_skin' );
		$settings_skin  = explode( ':', $settings_skin, 2 );
		$default_skin   = trim( $settings_skin[0] );
		$default_preset = isset( $settings_skin[1] ) ? trim( $settings_skin[1] ) : 'default';

		if ( $skin === $default_skin && $preset === $default_preset ) {
			wp_send_json_error( __( 'You can\'t delete skin/preset chosen by default', 'woowbox' ) );
		}

		$skins_data = get_option( Skins::PRESETS_KEY, [] );
		unset( $skins_data[ $skin ][ $preset ] );

		update_option( Skins::PRESETS_KEY, $skins_data );

		wp_send_json_success( sprintf( __( '`%s` preset was deleted', 'woowbox' ), $preset ) );
	}
}
