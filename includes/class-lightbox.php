<?php
/**
 * WoowGallery Lightbox class
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

namespace WoowGallery;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * Skins class.
 */
class Lightbox {

	const OPTIONS_KEY = 'woowgallery_lightbox';

	/**
	 * Holds the class object.
	 *
	 * @var Lightbox object
	 */
	public static $instance;

	/**
	 * Holds the lightbox object.
	 *
	 * @var array
	 */
	public $lightbox = [];

	/**
	 * Primary class constructor.
	 */
	public function __construct() {
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @return Lightbox object
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Lightbox ) ) {
			self::$instance = new Lightbox();
		}

		return self::$instance;
	}

	/**
	 * Get lightbox model
	 *
	 * @param string $lightbox_slug Lightbox slug.
	 * @param array  $overwrites    Model Settings overwrites.
	 *
	 * @return array Lightbox model
	 */
	public function get_lightbox_model( $lightbox_slug, $overwrites = [] ) {
		$lightbox = $this->get_lightbox( $lightbox_slug, $overwrites );

		return $lightbox['model'];
	}

	/**
	 * Get lightbox
	 *
	 * @param string $lightbox_slug Lightbox name.
	 * @param array  $overwrites    Model Settings overwrites.
	 *
	 * @return object
	 */
	public function get_lightbox( $lightbox_slug = 'woowlightbox', $overwrites = [] ) {
		$lightboxes = $this->get_settings();

		if ( empty( $lightbox_slug ) || ! isset( $lightboxes[ $lightbox_slug ] ) ) {
			$lightbox_slug = 'woowlightbox';
		}

		$lightbox = $lightboxes[ $lightbox_slug ];

		if ( ! empty( $overwrites ) ) {
			$overwrites        = $this->format_settings( $overwrites, $lightbox_slug );
			$lightbox['model'] = array_merge( $lightbox['model'], (array) $overwrites );
		}

		return $lightbox;
	}

	/**
	 * Get Lightbox settings
	 *
	 * @param bool $cache Cache settings or get fresh data and clear cache.
	 *
	 * @return array
	 */
	public function get_settings( $cache = true ) {

		if ( $this->lightbox && $cache ) {
			return $this->lightbox;
		}

		$preset   = get_option( self::OPTIONS_KEY, [] );
		$schema   = $this->get_schema();
		$lightbox = [];
		foreach ( $schema as $name => $lb_schema ) {
			$defaults          = $this->get_schema_defaluts( $lb_schema );
			$preset            = isset( $preset[ $name ] ) ? $preset[ $name ] : [];
			$model             = array_merge( $defaults, $preset );
			$lightbox[ $name ] = [
				'name'   => $name,
				'model'  => $model,
				'schema' => $schema[ $name ],
			];
		}

		if ( $cache ) {
			$this->lightbox = $lightbox;
		} else {
			$this->lightbox = [];
		}

		return $lightbox;
	}

	/**
	 * Settings Schema
	 *
	 * @return array
	 */
	private function get_schema() {
		return [
			'woowlightbox' => [
				'common' => [
					'label'  => __( 'WoowLightbox Settings', 'woowgallery' ),
					'fields' => [
						'copyR_Protection'                      => [
							'label'   => __( 'Enable Image Protection', 'woowgallery' ),
							'tag'     => 'checkbox',
							'default' => 1,
							'text'    => __( 'Disable right mouse click for images', 'woowgallery' ),
						],
						'copyR_Alert'                           => [
							'label'   => __( 'Copyright Alert (right mouse click)', 'woowgallery' ),
							'tag'     => 'input',
							'default' => __( 'Hello, this photo is mine!', 'woowgallery' ),
							'text'    => __( 'Show this message when visitor clicks the right mouse button on a photo', 'woowgallery' ),
						],
						'sliderScrollNavi'                      => [
							'label'   => __( 'Use Mouse Wheel for Navigation', 'woowgallery' ),
							'tag'     => 'checkbox',
							'default' => 1,
							'text'    => __( 'Note: This option disable scaling with mouse wheel!', 'woowgallery' ),
						],
						'sliderNextPrevAnimation'               => [
							'label'   => __( 'Transition Type Between Items', 'woowgallery' ),
							'tag'     => 'select',
							'default' => 'animation',
							'options' => [
								[
									'name'  => __( 'Slipping', 'woowgallery' ),
									'value' => 'animation',
								],
								[
									'name'  => __( 'Fading', 'woowgallery' ),
									'value' => 'fading',
								],
							],
						],
						'sliderBgColor'                         => [
							'label'   => __( 'Lightbox BG Color', 'woowgallery' ),
							'tag'     => 'input',
							'default' => 'rgba(0,0,0,0.9)',
							'attr'    => [
								'type' => 'color',
							],
							'options' => [
								'showAlpha' => true,
							],
						],
						'sliderPreloaderColor'                  => [
							'label'   => __( 'Preloader Color', 'woowgallery' ),
							'tag'     => 'input',
							'default' => 'rgba(255,255,255,1)',
							'attr'    => [
								'type' => 'color',
							],
							'options' => [
								'showAlpha' => true,
							],
						],
						'sliderHeaderFooterBgColor'             => [
							'label'   => __( 'Lightbox Header & Footer Color', 'woowgallery' ),
							'tag'     => 'input',
							'default' => 'rgba(0,0,0,0.4)',
							'options' => [
								'showAlpha' => true,
							],
							'attr'    => [
								'type' => 'color',
							],
							'text'    => __( 'Set the background color for header and footer (with fading to transparent)', 'woowgallery' ),
						],
						'sliderNavigationColor'                 => [
							'label'   => __( 'Main Controls - BG Color', 'woowgallery' ),
							'tag'     => 'input',
							'default' => 'rgba(0,0,0,1)',
							'attr'    => [
								'type' => 'color',
							],
							'options' => [
								'showAlpha' => true,
							],
						],
						'sliderNavigationColorOver'             => [
							'label'   => __( 'Main Controls - Hover BG Color', 'woowgallery' ),
							'tag'     => 'input',
							'default' => 'rgba(255,255,255,1)',
							'attr'    => [
								'type' => 'color',
							],
							'options' => [
								'showAlpha' => true,
							],
						],
						'sliderNavigationIconColor'             => [
							'label'   => __( 'Main Controls - Icon Color', 'woowgallery' ),
							'tag'     => 'input',
							'default' => 'rgba(255,255,255,1)',
							'attr'    => [
								'type' => 'color',
							],
							'options' => [
								'showAlpha' => true,
							],
						],
						'sliderNavigationIconColorOver'         => [
							'label'   => __( 'Main Controls - Icon Hover Color', 'woowgallery' ),
							'tag'     => 'input',
							'default' => 'rgba(0,0,0,1)',
							'attr'    => [
								'type' => 'color',
							],
							'options' => [
								'showAlpha' => true,
							],
						],
						'sliderItemTitleEnable'                 => [
							'label'   => __( 'Show Title', 'woowgallery' ),
							'tag'     => 'checkbox',
							'default' => 1,
						],
						'sliderItemTitleFontSize'               => [
							'label'   => __( 'Title - Font Size', 'woowgallery' ),
							'visible' => 'sliderItemTitleEnable == "1"',
							'tag'     => 'input',
							'default' => 18,
							'attr'    => [
								'type' => 'number',
								'min'  => 18,
								'max'  => 36,
							],
						],
						'sliderItemTitleTextColor'              => [
							'label'   => __( 'Title - Text Color', 'woowgallery' ),
							'visible' => 'sliderItemTitleEnable == "1"',
							'tag'     => 'input',
							'default' => 'rgba(255,255,255,1)',
							'attr'    => [
								'type' => 'color',
							],
							'options' => [
								'showAlpha' => true,
							],
						],
						'sliderItemDescriptionEnable'           => [
							'label'   => __( 'Show Description', 'woowgallery' ),
							'tag'     => 'checkbox',
							'default' => 1,
						],
						'sliderItemDescriptionFontSize'         => [
							'label'   => __( 'Description - Font Size', 'woowgallery' ),
							'visible' => 'sliderItemDescriptionEnable == "1"',
							'tag'     => 'input',
							'default' => 16,
							'attr'    => [
								'type' => 'number',
								'min'  => 12,
								'max'  => 36,
							],
						],
						'sliderItemDescriptionTextColor'        => [
							'label'   => __( 'Description - Text Color', 'woowgallery' ),
							'visible' => 'sliderItemDescriptionEnable == "1"',
							'tag'     => 'input',
							'default' => 'rgba(255,255,255,0.8)',
							'attr'    => [
								'type' => 'color',
							],
							'options' => [
								'showAlpha' => true,
							],
						],
						'infoBarExifEnable'                     => [
							'label'   => __( 'Show Image EXIF Data', 'woowgallery' ),
							'tag'     => 'checkbox',
							'default' => 1,
						],
						'sliderThumbBarEnable'                  => [
							'label'   => __( 'Show Thumbnails Bar', 'woowgallery' ),
							'tag'     => 'checkbox',
							'default' => 1,
						],
						'sliderThumbBarHoverColor'              => [
							'label'   => __( 'Active Thumbnail Border Color', 'woowgallery' ),
							'visible' => 'sliderThumbBarEnable == "1"',
							'tag'     => 'input',
							'default' => 'rgba(255,255,255,1)',
							'attr'    => [
								'type' => 'color',
							],
							'options' => [
								'showAlpha' => true,
							],
						],
						'sliderPlayButton'                      => [
							'label'   => __( 'Show Slideshow Button', 'woowgallery' ),
							'tag'     => 'checkbox',
							'default' => 1,
						],
						'slideshowDelay'                        => [
							'label'   => __( 'Slideshow - Timer', 'woowgallery' ),
							'visible' => 'sliderPlayButton == "1"',
							'tag'     => 'input',
							'default' => 8,
							'attr'    => [
								'type' => 'number',
								'min'  => 2,
								'max'  => 30,
							],
						],
						'slideshowProgressBarColor'             => [
							'label'   => __( 'Slideshow - Progress Bar Color', 'woowgallery' ),
							'visible' => 'sliderPlayButton == "1"',
							'tag'     => 'input',
							'default' => 'rgba(255,255,255,1)',
							'attr'    => [
								'type' => 'color',
							],
							'options' => [
								'showAlpha' => true,
							],
						],
						'slideshowProgressBarBGColor'           => [
							'label'   => __( 'Slideshow - Progress Bar BG Color', 'woowgallery' ),
							'visible' => 'sliderPlayButton == "1"',
							'tag'     => 'input',
							'default' => 'rgba(255,255,255,0.6)',
							'attr'    => [
								'type' => 'color',
							],
							'options' => [
								'showAlpha' => true,
							],
						],
						'sliderZoomButton'                      => [
							'label'   => __( 'Enable Zooming', 'woowgallery' ),
							'tag'     => 'checkbox',
							'default' => 1,
						],
						'sliderSocialShareEnabled'              => [
							'label'   => __( 'Show "Share" Button', 'woowgallery' ),
							'tag'     => 'checkbox',
							'default' => 1,
						],
						'sliderFullScreen'                      => [
							'label'   => __( 'Show "Fullscreen" Button', 'woowgallery' ),
							'tag'     => 'checkbox',
							'default' => 1,
						],
						'sliderThumbSubMenuBackgroundColor'     => [
							'label'   => __( 'Buttons BG Color', 'woowgallery' ),
							'tag'     => 'input',
							'default' => 'rgba(0, 0, 0, 0)',
							'options' => [
								'showAlpha' => true,
							],
							'attr'    => [
								'type' => 'color',
							],
						],
						'sliderThumbSubMenuIconColor'           => [
							'label'   => __( 'Buttons Icon Color', 'woowgallery' ),
							'tag'     => 'input',
							'default' => 'rgba(255, 255, 255, 1)',
							'options' => [
								'showAlpha' => true,
							],
							'attr'    => [
								'type' => 'color',
							],
						],
						'sliderThumbSubMenuBackgroundColorOver' => [
							'label'   => __( 'Buttons Hover BG Color', 'woowgallery' ),
							'tag'     => 'input',
							'default' => 'rgba(255, 255, 255, 1)',
							'options' => [
								'showAlpha' => true,
							],
							'attr'    => [
								'type' => 'color',
							],
						],
						'sliderThumbSubMenuIconHoverColor'      => [
							'label'   => __( 'Buttons Icon Hover Color', 'woowgallery' ),
							'tag'     => 'input',
							'default' => 'rgba(0, 0, 0, 1)',
							'options' => [
								'showAlpha' => true,
							],
							'attr'    => [
								'type' => 'color',
							],
						],
					],
				],
			],
		];
	}

	/**
	 * Recursive function to get default values from settings schema
	 *
	 * @param array $schema   Settings Schema.
	 * @param array $defaults Defaults.
	 *
	 * @return array Schema default settings
	 */
	private function get_schema_defaluts( $schema, $defaults = [] ) {
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
	 * Format settings
	 *
	 * @param array  $settings      Settings.
	 * @param string $lightbox_slug Lightbox name.
	 *
	 * @return array
	 */
	public function format_settings( $settings, $lightbox_slug ) {
		$lightbox        = $this->get_lightbox( $lightbox_slug );
		$schema_defaults = $this->get_schema_defaluts( $lightbox['schema'] );

		foreach ( $settings as $key => &$value ) {
			if ( ! isset( $schema_defaults[ $key ] ) ) {
				continue;
			}
			$type = gettype( $schema_defaults[ $key ] );
			if ( in_array( $type, [ 'integer', 'double', 'boolean' ], true ) ) {
				settype( $value, $type );
			}
		}

		return $settings;
	}
}
