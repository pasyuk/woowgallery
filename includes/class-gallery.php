<?php
/**
 * Gallery class.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

namespace WoowGallery;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

use WoowGallery\Admin\Edit_Woowgallery;
use WoowGallery\Admin\Notice;
use WoowGallery\Admin\Settings;

/**
 * Class Taxonomies
 */
class Gallery {

	const GALLERY_UPDATE_META_KEY          = '_woowgallery_update';
	const GALLERY_CONTENT_META_KEY         = '_woowgallery_content';
	const GALLERY_MEDIA_COUNT_META_KEY     = '_woowgallery_media_count';
	const GALLERY_SETTINGS_META_KEY        = '_woowgallery_settings';
	const GALLERY_EDITOR_SETTINGS_META_KEY = '_woowgallery_editor_settings';
	const GALLERY_SKIN_META_KEY            = '_woowgallery_skin';
	const GALLERY_SKIN_CONFIG_META_KEY     = '_woowgallery_skin_config';

	/**
	 * WoowGallery ID
	 *
	 * @var int
	 */
	private $id;

	/**
	 * WoowGallery Type
	 *
	 * @var int
	 */
	private $post_type;

	/**
	 * Gallery skin slug
	 *
	 * @var int
	 */
	private $skin_slug;

	/**
	 * Fallback skin preset
	 *
	 * @var int
	 */
	private $skin_preset;

	/**
	 * Gallery constructor.
	 *
	 * @param int|string $id Gallery ID or slug.
	 * @param null       $post_type
	 */
	public function __construct( $id, $post_type = Posttypes::GALLERY_POSTTYPE ) {
		$this->post_type = $post_type;
		$this->set_id( $id );
	}

	/**
	 * Returns all Galleries IDs created on the site.
	 *
	 * @param bool   $skip_empty   Skip empty sliders.
	 * @param bool   $ignore_cache Ignore cache.
	 * @param string $search_terms Search for specified Galleries by Title.
	 *
	 * @return array|bool Array of gallery ids.
	 */
	public static function get_galleries_ids( $skip_empty = true, $ignore_cache = false, $search_terms = '' ) {

		// Attempt to return the cache first, otherwise generate the new query to retrieve the data.
		$cache_group   = 'woowgallery_galleries_ids';
		$cache_key     = 'woowgallery_galleries_ids' . $skip_empty ? '_no_empty' : '';
		$galleries_ids = wp_cache_get( $cache_key, $cache_group );
		if ( $ignore_cache || ! empty( $search_terms ) || false === $galleries_ids ) {
			$args = [
				'post_type'      => Posttypes::GALLERY_POSTTYPE,
				'post_status'    => 'publish',
				'posts_per_page' => - 1,
				'fields'         => 'ids',
				's'              => $search_terms,
			];
			if ( $skip_empty ) {
				$args['meta_query'] = [
					[
						'key'     => self::GALLERY_MEDIA_COUNT_META_KEY,
						'value'   => 0,
						'compare' => '>',
						'type'    => 'NUMERIC',
					],
				];
			}

			$galleries_ids = get_posts( $args );

			// Cache the results if we're not performing a search.
			if ( empty( $search_terms ) ) {
				wp_cache_set( $cache_key, $galleries_ids, $cache_group );
			}
		}

		// Return the galleries ids.
		return $galleries_ids;

	}

	/**
	 * Returns a WoowGallery data.
	 *
	 * @return array|bool Array of gallery data or false if none found.
	 */
	public function get_gallery() {

		$cache_group = 'woowgallery';
		$cache_key   = 'wg' . $this->id;
		$gallery     = wp_cache_get( $cache_key, $cache_group );
		// Attempt to return the cache first, otherwise generate the new query to retrieve the data.
		if ( false === $gallery ) {
			$post    = get_post( $this->id );
			$gallery = [
				'id'          => $post->ID,
				'type'        => $post->post_type,
				'slug'        => $post->name,
				'title'       => $post->post_title,
				'description' => $post->post_content,
				'date'        => $post->post_date,
				'modified'    => $post->post_modified,
				'status'      => $post->post_status,
				'skin'        => [
					'slug'   => $this->get_skin_slug(),
					'config' => $this->get_skin_config(),
				],
				'data'        => (array) json_decode( $post->post_content_filtered ),
				'content'     => $this->get_gallery_content(),
				'count'       => (int) get_post_meta( $this->id, self::GALLERY_MEDIA_COUNT_META_KEY, true ),
			];

			wp_cache_set( $cache_key, $gallery, $cache_group );
		}

		// Return the gallery data.
		return $gallery;
	}

	/**
	 * Helper method for retrieving gallery skin slug.
	 *
	 * @return string.
	 */
	public function get_skin_slug() {

		if ( ! $this->skin_slug ) {
			// Get config.
			$gallery_skin = get_post_meta( $this->id, self::GALLERY_SKIN_META_KEY, true );

			// Check config key exists.
			if ( empty( $gallery_skin ) ) {
				$gallery_skin_default = Settings::get_settings( 'default_skin' );
				if ( ! empty( $gallery_skin_default ) ) {
					$gallery_skin_default = explode( ':', $gallery_skin_default );
					$gallery_skin         = $gallery_skin_default[0];
				}
			}

			$skin = Skins::get_instance()->get_skin( $gallery_skin );
			if ( $skin->slug !== $gallery_skin && is_admin() ) {
				Notice::add_message( sprintf( __( 'Broken or removed Skin! Please re-save gallery ID#%d with a new Skin.', 'wgtd' ), $this->id ) );
			}

			$this->skin_slug   = $skin->slug;
			$this->skin_preset = $skin->preset_name;
		}

		return $this->skin_slug;
	}

	/**
	 * Helper method for retrieving gallery skin model.
	 *
	 * @return array.
	 */
	public function get_skin_config() {

		$skin_slug = $this->get_skin_slug();

		// Get config.
		$gallery_skin_config = get_post_meta( $this->id, self::GALLERY_SKIN_CONFIG_META_KEY, true ) ?: [];

		// Check config key exists.
		if ( ! empty( $gallery_skin_config['__skin'] ) && $gallery_skin_config['__skin'] === $skin_slug ) {
			return Skins::get_instance()->get_skin_model( $skin_slug, $this->skin_preset, $gallery_skin_config );
		}

		return Skins::get_instance()->get_skin_model( $skin_slug, $this->skin_preset );
	}

	/**
	 * Returns a WoowGallery content.
	 *
	 * @return array Array of gallery content.
	 */
	public function get_gallery_content() {
		$update_required = get_post_meta( $this->id, self::GALLERY_UPDATE_META_KEY, true );
		if ( ! empty( $update_required ) && time() > (int) $update_required ) {
			$post = get_post( $this->id );
			$data = (array) json_decode( $post->post_content_filtered, true );

			return Edit_Woowgallery::set_gallery_content( $post->ID, $data );
		}

		return get_post_meta( $this->id, self::GALLERY_CONTENT_META_KEY, true ) ?: [];
	}

	/**
	 * Get gallery ID.
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Set gallery ID.
	 *
	 * @param int|string $id Gallery ID or slug.
	 */
	public function set_id( $id ) {

		if ( is_numeric( $id ) ) {
			$this->id = (int) $id;

			return;
		}

		// Attempt to return the cache first, otherwise generate the new query to retrieve the data.
		$cache_group = 'woowgallery_id';
		$cache_key   = "{$this->post_type}_{$id}";
		$gallery_id  = wp_cache_get( $cache_key, $cache_group );
		if ( false === $gallery_id ) {
			// Get Polyview Gallery CPT by slug.
			$posts = get_posts(
				[
					'post_type'      => $this->post_type,
					'name'           => $id,
					'fields'         => 'ids',
					'posts_per_page' => 1,
				]
			);
			if ( ! empty( $posts ) ) {
				$gallery_id = $posts[0];
				wp_cache_set( $cache_key, $gallery_id, $cache_group );
			}
		}

		// Return the gallery ID.
		$this->id = $gallery_id;
	}

	/**
	 * Helper method for retrieving gallery settings values.
	 *
	 * @param string      $key     The setting key to retrieve.
	 * @param bool|string $default A default value to use.
	 *
	 * @return array|string Key value on success, default value on failure.
	 */
	public function get_settings( $key, $default = false ) {

		// Get settings.
		$settings = $this->get_all_settings();

		// Check setting key exists.
		if ( isset( $settings[ $key ] ) ) {

			return $settings[ $key ];
		} else {

			return ( false !== $default ) ? $default : '';
		}

	}

	/**
	 * Helper method for retrieving all gallery settings.
	 *
	 * @return array|bool Key value on success, default value on failure.
	 */
	public function get_all_settings() {

		return get_post_meta( $this->id, self::GALLERY_SETTINGS_META_KEY, true );
	}

	/**
	 * Helper method for retrieving gallery editor settings values.
	 *
	 * @param string      $key     The editor setting key to retrieve.
	 * @param bool|string $default A default value to use.
	 *
	 * @return string Key value on success, default value on failure.
	 */
	public function get_editor_settings( $key, $default = false ) {

		// Get settings.
		$settings = get_post_meta( $this->id, self::GALLERY_EDITOR_SETTINGS_META_KEY, true );

		// Check setting key exists.
		if ( isset( $settings[ $key ] ) ) {

			return $settings[ $key ];
		} else {

			return ( false !== $default ) ? $default : '';
		}

	}

}
