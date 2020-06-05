<?php
/**
 * WoowGallery Skins class
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

namespace WoowGallery;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

use WoowGallery\Admin\Settings;

/**
 * Skins class.
 */
class Skins {

	const PRESETS_KEY = 'woowgallery_skins_presets';

	/**
	 * Holds the class object.
	 *
	 * @var Skins object
	 */
	public static $instance;

	/**
	 * Holds the skins objects.
	 *
	 * @var array
	 */
	private $skins = [];

	/**
	 * Primary class constructor.
	 */
	public function __construct() {

		$skins_folders = glob( WOOWGALLERY_PATH . '/skins/*', GLOB_ONLYDIR | GLOB_NOSORT );
		foreach ( $skins_folders as $path ) {
			$this->load_file( $path . '/' . basename( $path ) . '.php' );
		}

		add_filter( 'woowgallery_save_skin_config', [ $this, 'format_skin_config' ], 4, 2 );
	}

	/**
	 * Include a class file.
	 *
	 * @param string $path File path.
	 */
	private function load_file( $path ) {
		if ( $path && is_readable( $path ) ) {
			include_once $path;
		}
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @return Skins object
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Skins ) ) {
			self::$instance = new Skins();
		}

		return self::$instance;
	}

	/**
	 * Get skin model
	 *
	 * @param string $skin_slug   Skin slug.
	 * @param string $preset_name Skin preset.
	 * @param array  $overwrites  Settings overwrites.
	 *
	 * @return array Skin model
	 */
	public function get_skin_model( $skin_slug, $preset_name = 'default', $overwrites = [] ) {
		$skin = $this->get_skin( $skin_slug );
		if ( ! $preset_name || ! isset( $skin->model[ $preset_name ] ) ) {
			$preset_name = 'default';
		}

		if ( ! empty( $overwrites ) ) {
			$overwrites = $this->format_skin_config( $overwrites, $skin_slug );
		}

		return array_merge( $skin->model[ $preset_name ], (array) $overwrites );
	}

	/**
	 * Get skin
	 *
	 * @param string $skin_slug   Skin slug.
	 * @param string $preset_name Skin preset.
	 *
	 * @return object|bool Skin
	 */
	public function get_skin( $skin_slug = '', $preset_name = '' ) {
		$skins = $this->get_skins();

		$slug_preset = explode( ':', $skin_slug );
		$skin_slug   = $slug_preset[0];
		if ( empty( $preset_name ) && ! empty( $slug_preset[1] ) ) {
			$preset_name = trim( $slug_preset[1] ) ?: $preset_name;
		}
		$preset_name = $preset_name ?: 'default';

		if ( empty( $skin_slug ) || ! isset( $skins[ $skin_slug ] ) ) {
			$settings = Settings::get_settings();
			if ( ! empty( $settings['default_skin'] ) && $settings['default_skin'] !== $skin_slug ) {
				return $this->get_skin( $settings['default_skin'] );
			}

			$default_settings = Settings::get_settings_default();
			if ( ! empty( $default_settings['default_skin'] ) && $default_settings['default_skin'] !== $skin_slug ) {
				return $this->get_skin( $settings['default_skin'] );
			}

			return new \WP_Error( 'skin_missed', __( 'Missed default skin. Set default skin on Settings page.', 'woowgallery' ) );
		}

		if ( 'default' !== $preset_name && ! isset( $skins[ $skin_slug ]->model[ $preset_name ] ) ) {
			$preset_name = 'default';
		}

		$skins[ $skin_slug ]->preset_name = $preset_name;

		return $skins[ $skin_slug ];
	}

	/**
	 * Get available skins
	 *
	 * @param bool $force Skip cached value.
	 *
	 * @return array
	 */
	public function get_skins( $force = false ) {

		if ( $this->skins && ! $force ) {
			return $this->skins;
		}

		$all_presets = get_option( self::PRESETS_KEY, [] );

		$skins = apply_filters( 'woowgallery_skins', [] );
		ksort( $skins );

		foreach ( $skins as $slug => &$skin ) {
			$schema           = $skin::settings();
			$defaults         = $this->get_schema_defaluts( $schema );
			$presets          = isset( $all_presets[ $slug ] ) ? $all_presets[ $slug ] : [];
			$model['default'] = array_merge( $defaults, ( isset( $presets['default'] ) ? $presets['default'] : [] ) );
			unset( $presets['default'] );
			foreach ( $presets as $name => $preset ) {
				$model[ $name ] = array_merge( $defaults, $preset );
			}

			$skin->slug   = $slug;
			$skin->info   = $skin::info();
			$skin->model  = $model;
			$skin->schema = $schema;
		}

		$this->skins = $skins;

		return $skins;
	}

	/**
	 * Recursive function to get default values from skin schema
	 *
	 * @param array $schema   Skin Settings Schema.
	 * @param array $defaults Skin defaults.
	 *
	 * @return array Schema default settings
	 */
	private static function get_schema_defaluts( $schema, $defaults = [] ) {
		foreach ( $schema as $key => $val ) {
			if ( isset( $val['default'] ) ) {
				$defaults[ $key ] = $val['default'];
			} elseif ( is_array( $val ) ) {
				$defaults = self::get_schema_defaluts( $val, $defaults );
			}
		}

		return $defaults;
	}

	/**
	 * Format skin settings
	 *
	 * @param array  $config    Skin Settings.
	 * @param string $skin_slug Skin slug.
	 *
	 * @return array
	 */
	public function format_skin_config( $config, $skin_slug ) {
		$skin_defaults = $this->get_skin_defaults( $skin_slug );

		foreach ( $config as $key => &$value ) {
			if ( ! isset( $skin_defaults[ $key ] ) ) {
				continue;
			}
			$type = gettype( $skin_defaults[ $key ] );
			if ( in_array( $type, [ 'integer', 'double', 'boolean' ], true ) ) {
				settype( $value, $type );
			}
		}

		return $config;
	}

	/**
	 * Get skin defaults
	 *
	 * @param string $skin_slug Skin slug.
	 *
	 * @return array Skin default settings
	 */
	public function get_skin_defaults( $skin_slug ) {
		$skin = $this->get_skin( $skin_slug );

		return $this->get_schema_defaluts( $skin->schema );
	}

	/**
	 * Render gallery with chosen skin.
	 *
	 * @param array $gallery Gallery data.
	 *
	 * @return string Skin HTML.
	 */
	public function render_skin( $gallery ) {
		$skin = $this->get_skin( $gallery['skin']['slug'] );

		return $skin::render( $gallery );
	}
}
