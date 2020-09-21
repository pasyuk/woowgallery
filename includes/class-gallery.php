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
 * Class Gallery
 */
class Gallery {

	const GALLERY_UPDATE_META_KEY          = '_woowgallery_update';
	const GALLERY_CONTENT_META_KEY         = '_woowgallery_content';
	const GALLERY_MEDIA_COUNT_META_KEY     = '_woowgallery_media_count';
	const GALLERY_SETTINGS_META_KEY        = '_woowgallery_settings';
	const GALLERY_EDITOR_SETTINGS_META_KEY = '_woowgallery_editor_settings';
	const GALLERY_SKIN_META_KEY            = '_woowgallery_skin';
	const GALLERY_SKIN_CONFIG_META_KEY     = '_woowgallery_skin_config';
	const GALLERY_LIGHTBOX_META_KEY        = '_woowgallery_lightbox';
	const GALLERY_LIGHTBOX_CONFIG_META_KEY = '_woowgallery_lightbox_config';

	/**
	 * Holds the class object.
	 *
	 * @var Gallery object
	 */
	public static $instance;

	/**
	 * WoowGallery post ID
	 *
	 * @var int
	 */
	private $post_id;

	/**
	 * WoowGallery real ID
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
	 * Gallery skin slug
	 *
	 * @var int
	 */
	private $lightbox_slug;

	/**
	 * Fallback skin preset
	 *
	 * @var int
	 */
	private $skin_preset;

	/**
	 * Gallery constructor.
	 *
	 * @param int|string $post_id   Gallery ID or slug.
	 * @param string     $post_type Post type.
	 */
	public function __construct( $post_id, $post_type = Posttypes::GALLERY_POSTTYPE ) {
		$this->post_type = $post_type;
		$this->set_id( $post_id );
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @param int|string $post_id   Gallery ID or slug.
	 * @param string     $post_type Post type.
	 *
	 * @return Gallery object
	 */
	public static function get_instance( $post_id, $post_type = Posttypes::GALLERY_POSTTYPE ) {
		if ( ! ( isset( self::$instance ) && self::$instance instanceof Gallery ) || self::$instance->get_id() !== (int) $post_id ) {
			self::$instance = new Gallery( $post_id, $post_type );
		}

		return self::$instance;
	}

	/**
	 * Get gallery ID.
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->post_id;
	}

	/**
	 * Set gallery ID.
	 *
	 * @param int|string $post_id Gallery ID or slug.
	 */
	public function set_id( $post_id ) {

		if ( is_numeric( $post_id ) ) {
			$this->post_id = (int) $post_id;
			$this->set_real_id();

			return;
		}

		// Attempt to return the cache first, otherwise generate the new query to retrieve the data.
		$cache_group = 'woowgallery_id';
		$cache_key   = "{$this->post_type}_{$post_id}";
		$gallery_id  = wp_cache_get( $cache_key, $cache_group );
		if ( false === $gallery_id ) {
			// Get WoowGallery CPT by slug.
			$posts = get_posts(
				[
					'post_type'      => $this->post_type,
					'name'           => $post_id,
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
		$this->post_id = $gallery_id;
		$this->set_real_id();
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
			$post    = get_post( $this->post_id );
			$data    = ( $this->id === $this->post_id ) ? $post->post_content_filtered : get_post_field( 'post_content_filtered', $this->id, 'raw' );
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
				'lightbox'    => [
					'slug'   => $this->get_lightbox_slug(),
					'config' => $this->get_lightbox_config(),
				],
				'data'        => (array) json_decode( $data ),
				'content'     => $this->get_gallery_content(),
				'count'       => (int) get_metadata( 'post', $this->id, self::GALLERY_MEDIA_COUNT_META_KEY, true ),
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
			$gallery_skin = get_metadata( 'post', $this->id, self::GALLERY_SKIN_META_KEY, true );

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
				// translators: Gallery ID.
				Notice::add_message( sprintf( __( 'Broken or removed Skin! Please re-save gallery ID#%d with a new Skin.', 'woowgallery' ), $this->post_id ), Notice::TYPE_ERROR, '', 'broken_skin' );
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
		$gallery_skin_config = get_metadata( 'post', $this->id, self::GALLERY_SKIN_CONFIG_META_KEY, true ) ?: [];

		// Check config key exists.
		if ( ! empty( $gallery_skin_config['__skin'] ) && $gallery_skin_config['__skin'] === $skin_slug ) {
			return Skins::get_instance()->get_skin_model( $skin_slug, $this->skin_preset, $gallery_skin_config );
		}

		return Skins::get_instance()->get_skin_model( $skin_slug, $this->skin_preset );
	}

	/**
	 * Helper method for retrieving gallery lightbox slug.
	 *
	 * @return string.
	 */
	public function get_lightbox_slug() {

		if ( ! $this->lightbox_slug ) {
			if ( metadata_exists( 'post', $this->id, self::GALLERY_LIGHTBOX_META_KEY ) ) {
				$this->lightbox_slug = get_metadata( 'post', $this->id, self::GALLERY_LIGHTBOX_META_KEY, true );
			} else {
				$this->lightbox_slug = Settings::get_settings_default( 'default_lightbox' );
			}
		}

		return $this->lightbox_slug;
	}

	/**
	 * Helper method for retrieving gallery lightbox model.
	 *
	 * @return array|null.
	 */
	public function get_lightbox_config() {

		$lightbox_slug = $this->get_lightbox_slug();

		// Get config.
		$gallery_lightbox_config = get_metadata( 'post', $this->id, self::GALLERY_LIGHTBOX_CONFIG_META_KEY, true ) ?: [];

		// Check config key exists.
		if ( ! empty( $lightbox_slug ) ) {
			return Lightbox::get_instance()->get_lightbox_model( $lightbox_slug, $gallery_lightbox_config );
		}

		return null;
	}

	/**
	 * Returns a WoowGallery content.
	 *
	 * @return array Array of gallery content.
	 */
	public function get_gallery_content() {
		$update_required = (int) get_metadata( 'post', $this->id, self::GALLERY_UPDATE_META_KEY, true );
		if ( $this->id !== $this->post_id || $update_required && time() > $update_required ) {
			$post    = get_post( $this->id );
			$data    = (array) json_decode( $post->post_content_filtered, true );
			$content = Edit_Woowgallery::set_gallery_content( $this->id, $data );
		} else {
			$content = get_metadata( 'post', $this->id, self::GALLERY_CONTENT_META_KEY, true ) ?: [];
		}

		if ( current_user_can( 'edit_posts' ) ) {
			foreach ( $content as $i => $item ) {
				if ( ( 'post' === $item['type'] || 'attachment' === $item['type'] ) && current_user_can( 'edit_post', (int) $item['id'] ) ) {
					$content[ $i ]['edit_link'] = get_edit_post_link( (int) $item['id'], 'raw' );
				}
			}
		}

		return $content;
	}

	/**
	 * Returns an artificiall WoowGallery data.
	 *
	 * @param array $data    Gallery Data.
	 * @param array $content Gallery Content.
	 *
	 * @return array|bool Array of gallery data or false if none found.
	 */
	public function get_gallery_artificiall( $data = [], $content = [] ) {

		$cache_group = 'woo_gallery';
		$cache_key   = 'wg' . $this->id;
		$gallery     = wp_cache_get( $cache_key, $cache_group );
		// Attempt to return the cache first, otherwise generate the new query to retrieve the data.
		if ( false === $gallery ) {
			$post = get_post( $this->post_id );

			$skin              = Skins::get_instance()->get_skin( Settings::get_settings( 'product_gallery_skin' ) );
			$this->skin_slug   = $skin->slug;
			$this->skin_preset = $skin->preset_name;

			$lightbox_slug   = Settings::get_settings_default( 'default_lightbox' );
			$lightbox_config = ! empty( $lightbox_slug ) ? Lightbox::get_instance()->get_lightbox_model( $lightbox_slug ) : [];

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
					'slug'   => $skin->slug,
					'config' => $skin->model[ $skin->preset_name ],
				],
				'lightbox'    => [
					'slug'   => $lightbox_slug,
					'config' => $lightbox_config,
				],
				'data'        => $data,
				'content'     => $content,
				'count'       => count( $content ),
			];

			wp_cache_set( $cache_key, $gallery, $cache_group );
		}

		// Return the gallery data.
		return $gallery;
	}

	/**
	 * Get gallery real ID.
	 *
	 * @return int
	 */
	public function get_real_id() {
		return $this->id;
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

		return get_metadata( 'post', $this->id, self::GALLERY_SETTINGS_META_KEY, true );
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
		$settings = get_post_meta( $this->post_id, self::GALLERY_EDITOR_SETTINGS_META_KEY, true );

		// Check setting key exists.
		if ( isset( $settings[ $key ] ) ) {

			return $settings[ $key ];
		} else {

			return ( false !== $default ) ? $default : '';
		}

	}

	/**
	 * Set gallery real ID.
	 */
	private function set_real_id() {
		if ( is_preview() && current_user_can( 'edit_post', $this->post_id ) ) {
			$preview = wp_get_post_autosave( $this->post_id );
			if ( is_object( $preview ) ) {
				$preview  = sanitize_post( $preview );
				$this->id = $preview->ID;

				return;
			}
		}

		$this->id = $this->post_id;
	}

}
