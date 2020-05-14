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
use WoowGallery\Posttypes;
use WoowGallery\Skins;
use WP_Query;

/**
 * Class Ajax
 */
class Ajax {

	/**
	 * Primary class constructor.
	 */
	public function __construct() {

		add_action( 'wp_ajax_woowgallery_get_media_data', [ $this, 'get_media_data' ] );
		add_action( 'wp_ajax_woowgallery_set_media_tags', [ $this, 'set_media_tags' ] );
		add_action( 'wp_ajax_woowgallery_bulk_set_media_tags', [ $this, 'bulk_set_media_tags' ] );

		add_action( 'wp_ajax_woowgallery_dynamic_refresh_taxonomy_terms', [ $this, 'refresh_taxonomy_terms' ] );
		add_action( 'wp_ajax_woowgallery_dynamic_fetch_query', [ $this, 'dynamic_fetch_query' ] );
		add_action( 'wp_ajax_woowgallery_cache_clear', [ $this, 'gallery_cache_clear' ] );

		//add_action( 'wp_ajax_woowgallery_license', [ $this, 'woowgallery_license' ] ); // @TODO Freemius.

		add_action( 'wp_ajax_woowgallery_save_skin_data', [ $this, 'save_skin_data' ] );
		add_action( 'wp_ajax_woowgallery_delete_skin_preset', [ $this, 'delete_skin_preset' ] );

		add_filter( 'ajax_query_attachments_args', [ $this, 'ajax_query_attachments_args' ] );

		//add_action( 'wp_ajax_woowgallery_load_gallery', [ $this, 'load_gallery' ] );
		//add_action( 'wp_ajax_woowgallery_update_gallery_data', [ $this, 'update_gallery_data' ] );
		//add_action( 'wp_ajax_woowgallery_save_gallery_data', [ $this, 'save_gallery_data' ] );
		//add_action( 'wp_ajax_woowgallery_collection_save_bulk_meta', [ $this, 'collection_save_bulk_meta' ] );
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
	 * WoowGallery Media Tags update.
	 */
	public function set_media_tags() {
		// Bail out if we fail a security check.
		woowgallery_verify_nonce( 'ajax' );

		$media_id = (int) woowgallery_POST( 'media_id', 0 );
		$taxonomy = woowgallery_POST( 'taxonomy', 'post_tag' );
		if ( $media_id ) {
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
	 * WoowGallery bulk Media Tags update.
	 */
	public function bulk_set_media_tags() {
		// Bail out if we fail a security check.
		woowgallery_verify_nonce( 'ajax' );

		$medias = json_decode( woowgallery_POST( 'media', '[]' ) );
		if ( $medias ) {
			foreach ( $medias as $media ) {
				$terms = array_map( 'trim', explode( ',', woowgallery_POST( 'tags', '' ) ) );
				if ( 'attachment' === $media->type ) {
					$tt_ids = wp_set_object_terms( (int) $media->id, $terms, 'media_tag', true );
				} elseif ( 'post' === $media->type && is_object_in_term( $media->id, 'post_tag' ) ) {
					$tt_ids = wp_set_object_terms( (int) $media->id, $terms, 'post_tag', true );
				}
			}

			wp_send_json_success( $tt_ids );
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
	 * Fetch Query for Dynamic Galeries
	 */
	public function dynamic_fetch_query() {

		$json = woowgallery_GET( 'json' );
		if ( empty( $json ) ) {
			wp_send_json_error( __( 'Empty Query', 'wgtd' ) );
		}

		$query = (array) json_decode( $json, true );
		try {
			$query_content = Edit_Dynamic_Gallery::get_dynamic_query( $query );
			wp_send_json_success( $query_content );
		} catch ( \Exception $e ) {
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


	///**
	// * Save License.
	// */
	//public function woowgallery_license() {
	//	// Bail out if we fail a security check.
	//	woowbox_verify_nonce( 'settings_save' );
	//
	//	$license_action = woowgallery_POST( 'license_action', '' );
	//	$license        = trim( woowgallery_POST( 'license', '' ) );
	//	Settings::save_settings( $license, 'license' );
	//
	//	if ( 'check' === $license_action && ! $license ) {
	//		$message = '';
	//	} else {
	//		$message = $license ? __( 'Saved successfuly', 'woowbox' ) : '';
	//	}
	//	wp_send_json_success( $message );
	//}

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
			wp_send_json_error( __( 'Something goes wrong.', 'woowbox' ) );
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
			wp_send_json_error( __( 'Something goes wrong.', 'woowbox' ) );
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


	/**
	 * WoowGallery Get Gallery Data.
	 */
	public function load_gallery() {

		$id = (int) woowgallery_GET( 'id', 0 );
		if ( ! $id ) {
			wp_send_json_error();
		}

		$wgpost     = get_post( $id );
		$post_types = apply_filters( 'woowgallery_posttypes', [ Posttypes::GALLERY_POSTTYPE, Posttypes::ALBUM_POSTTYPE, Posttypes::DYNAMIC_POSTTYPE ] );
		if ( ! in_array( $wgpost->post_type, $post_types, true ) ) {
			wp_send_json_error();
		}

		$thumb_att_id = get_post_thumbnail_id( $wgpost->ID );
		$gallery_data = (array) json_decode( $wgpost->post_content_filtered, true );
		$result       = [
			'type'      => 'post',
			'subtype'   => $wgpost->post_type,
			'id'        => $wgpost->ID,
			'status'    => $wgpost->post_status,
			'title'     => $wgpost->post_title,
			'link'      => [
				'url'    => wp_get_shortlink( $wgpost->ID ),
				'target' => '_self',
				'text'   => '',
			],
			'author'    => [
				'id'   => $wgpost->post_author,
				'name' => get_the_author_meta( 'display_name', $wgpost->post_author ),
				'url'  => get_the_author_meta( 'url', $wgpost->post_author ),
			],
			'date'      => get_the_date( '', $wgpost->ID ),
			'thumb'     => $thumb_att_id ? wp_get_attachment_image_src( $thumb_att_id, 'medium' ) : [],
			'image'     => $thumb_att_id ? wp_get_attachment_image_src( $thumb_att_id, 'large' ) : [],
			'count'     => esc_html( sprintf( _n( '%d Media Item', '%d Media Items', count( $gallery_data ), 'wgtd' ), count( $gallery_data ) ) ),
			'tags'      => join( ',', wp_get_post_terms( $wgpost->ID, 'post_tag', [ 'fields' => 'names' ] ) ),
			'edit_link' => get_edit_post_link( $wgpost->ID, 'raw' ),
		];

		wp_send_json_success( $result );
	}


	/**
	 * WoowGallery Get Gallery Data.
	 */
	public function update_gallery_data() {
		// Bail out if we fail a security check.
		woowgallery_verify_nonce( 'ajax' );

		$media_id             = (int) woowgallery_POST( 'media_id' );
		$woowgallery_settings = (array) woowgallery_POST( 'woowgallery_settings', [] );

		$post_id       = (int) woowgallery_POST( 'post_id' );
		$post          = get_post( $post_id );
		$response_data = (array) json_decode( $post->post_content_filtered, true );

		if ( $media_id && ! empty( $woowgallery_settings ) ) {
			$update_post = false;
			foreach ( $response_data as &$attachment ) {
				if ( $attachment['id'] === $media_id ) {
					$attachment  = array_merge( $attachment, $woowgallery_settings );
					$update_post = true;
					break;
				}
			}

			if ( $update_post ) {
				$post->post_content_filtered = wp_json_encode( $response_data );
				$post_id                     = wp_update_post( $post );
				if ( ! $post_id || is_wp_error( $post_id ) ) {
					wp_send_json_error( $post_id );
				}
			}
		}

		wp_send_json_success( $response_data );
	}

	/**
	 * WoowGallery Set Gallery Data.
	 */
	public function save_gallery_data() {
		// Bail out if we fail a security check.
		woowgallery_verify_nonce( 'ajax' );

		$post_id = (int) woowgallery_POST( 'post_id' );
		$post    = get_post( $post_id );
		$data    = woowgallery_POST( 'data', '[]' );
		$do      = woowgallery_POST( 'do' );

		if ( $post->post_content_filtered !== $data ) {

			if ( 'remove' === $do ) {
				$old_data  = (array) json_decode( $post->post_content_filtered, true );
				$new_data  = (array) json_decode( $data, true );
				$diff_data = array_diff( $old_data, $new_data );
				$meta_key  = '_' . $post->post_type;
				foreach ( $diff_data as $item ) {
					$id = (int) $item['id'];
					if ( empty( $id ) || ! get_post( $id ) ) {
						continue;
					}
					delete_post_meta( $id, "{$meta_key}_{$post_id}" );
					// Is attachment already in galleries?
					$has_gallery = get_post_meta( $id, $meta_key, true );
					if ( empty( $has_gallery ) ) {
						$has_gallery = [];
					}
					$has_gallery = array_diff( $has_gallery, [ $post_id ] );
					if ( count( $has_gallery ) ) {
						update_post_meta( $id, $meta_key, $has_gallery );
					} else {
						delete_post_meta( $id, $meta_key );
					}
				}
			}

			$post->post_content_filtered = $data;
			$post_id                     = wp_update_post( $post );
			if ( ! $post_id || is_wp_error( $post_id ) ) {
				wp_send_json_error( $post_id );
			}
		}

		wp_send_json_success();
	}

	/**
	 * Save bulk attachments data.
	 */
	public function collection_save_bulk_meta() {
		// Bail out if we fail a security check.
		woowgallery_verify_nonce( 'ajax' );

		$changes = woowgallery_REQUEST( 'changes' );
		$_ids    = woowgallery_REQUEST( 'ids', [] );
		$ids     = array_map(
			'absint',
			array_filter(
				(array) $_ids,
				function ( $id ) {
					$id = (int) $id;

					return $id && current_user_can( 'edit_post', $id );
				}
			)
		);

		if ( empty( $ids ) || ! $changes ) {
			wp_send_json_error( compact( 'ids', 'changes' ) );
		}

		foreach ( $ids as $id ) {
			$post = get_post( $id, ARRAY_A );

			if ( 'attachment' !== $post['post_type'] ) {
				continue;
			}

			if ( ! empty( $changes['title'] ) ) {
				$post['post_title'] = $changes['title'];
			}

			if ( isset( $changes['caption'] ) ) {
				$post['post_excerpt'] = $changes['caption'];
			}

			if ( isset( $changes['description'] ) ) {
				$post['post_content'] = $changes['description'];
			}

			if ( MEDIA_TRASH && isset( $changes['status'] ) ) {
				$post['post_status'] = $changes['status'];
			}

			if ( isset( $changes['alt'] ) ) {
				$alt = wp_unslash( $changes['alt'] );
				if ( get_post_meta( $id, '_wp_attachment_image_alt', true ) !== $alt ) {
					$alt = wp_strip_all_tags( $alt, true );
					update_post_meta( $id, '_wp_attachment_image_alt', wp_slash( $alt ) );
				}
			}

			if ( wp_attachment_is( 'audio', $post['ID'] ) ) {
				$changed = false;
				$id3data = wp_get_attachment_metadata( $post['ID'] );
				if ( ! is_array( $id3data ) ) {
					$changed = true;
					$id3data = [];
				}
				foreach ( wp_get_attachment_id3_keys( (object) $post, 'edit' ) as $key => $label ) {
					if ( isset( $changes[ $key ] ) ) {
						$changed         = true;
						$id3data[ $key ] = sanitize_text_field( wp_unslash( $changes[ $key ] ) );
					}
				}

				if ( $changed ) {
					wp_update_attachment_metadata( $id, $id3data );
				}
			}

			if ( MEDIA_TRASH && isset( $changes['status'] ) && 'trash' === $changes['status'] ) {
				wp_delete_post( $id );
			} else {
				wp_update_post( $post );
			}
		}

		$collection_data = [];
		if ( ! empty( $changes['woowgallery_settings'] ) ) {
			$post_id         = (int) woowgallery_POST( 'post_id' );
			$post            = get_post( $post_id );
			$collection_data = (array) json_decode( $post->post_content_filtered, true );
			$ids             = array_filter( array_map( 'absint', $_ids ) );
			$update_post     = false;
			foreach ( $collection_data as &$attachment ) {
				if ( in_array( $attachment['id'], $ids, true ) ) {
					$attachment = array_merge( $attachment, $changes['woowgallery_settings'] );
					switch ( $attachment['woowgallery_link']['to'] ) {
						case 'none':
							$attachment['woowgallery_link']['url'] = '';
							break;
						case 'file':
							$attachment['woowgallery_link']['url'] = $attachment['url'];
							break;
						case 'post':
							$attachment['woowgallery_link']['url'] = $attachment['link'];
							break;
					}
					if ( '' === $attachment['woowgallery_link']['url'] ) {
						$attachment['woowgallery_link']['to'] = 'none';
					}
					$update_post = true;
				}
			}

			if ( $update_post ) {
				$post->post_content_filtered = wp_json_encode( $collection_data );
				$post_id                     = wp_update_post( $post );
				if ( ! $post_id || is_wp_error( $post_id ) ) {
					wp_send_json_error( $post_id );
				}
			}
		}

		wp_send_json_success( $collection_data );
	}

}
