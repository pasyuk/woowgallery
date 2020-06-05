<?php
/**
 * Amron Skin
 *
 * @package woowgallery
 * @author  GalleryCreator
 */

namespace WoowGallery\Skins;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );
if ( ! class_exists( 'WoowGallery\Skins\Amron' ) ) {
	return;
}

/**
 * Class Amron
 */
class Amron {

	const NAME        = 'Amron';
	const SLUG        = 'amron';
	const VERSION     = '1.0.0';
	const DESCRIPTION = '';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'woowgallery_skins', [ $this, 'add_skin' ] );
	}

	/**
	 * Skin Info
	 *
	 * @return array
	 */
	public static function info() {
		$info = [
			'name'        => self::NAME,
			'slug'        => self::SLUG,
			'version'     => self::VERSION,
			'description' => self::DESCRIPTION,
			'screenshots' => [ plugins_url( 'screenshot.png', __FILE__ ) ],
			'styles'      => [],
			'scripts'     => [ plugins_url( 'assets/amron.js', __FILE__ ) ],
			'dependecies' => [],
		];

		return apply_filters( 'woowgallery_skin_info', $info );
	}

	/**
	 * Render skin HTML
	 *
	 * @param array $gallery Gallery data.
	 *
	 * @return string
	 */
	public static function render( $gallery ) {
		ob_start();
		?>
		<div class='woowgallery-amron'>
			<script type="application/json" class="wg-json-content"><?php echo wp_json_encode( $gallery['content'] ); ?></script>
			<script type="application/json" class="wg-json-settings"><?php echo wp_json_encode( $gallery['skin']['config'] ); ?></script>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Skin Settings Schema
	 *
	 * @return array
	 */
	public static function settings() {
		$schema = [
			'common'             => [
				'label'  => __( 'Common Settings', 'wgtd' ),
				'fields' => [
					'collectionPreloaderColor'       => [
						'label'   => __( 'Gallery Preloader Color', 'wgtd' ),
						'tag'     => 'input',
						'default' => 'rgba(180,180,180,1)',
						'attr'    => [
							'type' => 'color',
						],
						'options' => [
							'showAlpha' => true,
						],
					],
					'collectionBgColor'              => [
						'label'   => __( 'Gallery Background Color', 'wgtd' ),
						'tag'     => 'input',
						'default' => 'rgba(255,255,255,0)',
						'attr'    => [
							'type' => 'color',
						],
						'options' => [
							'showAlpha' => true,
						],
					],
					'collectionThumbColumns'         => [
						'label'   => __( 'Gallery Columns', 'wgtd' ),
						'tag'     => 'input',
						'default' => 3,
						'attr'    => [
							'type' => 'number',
							'min'  => 1,
							'max'  => 10,
						],
					],
					'collectionThumbRecomendedWidth' => [
						'label'   => __( 'Thumbnail Min. Width', 'wgtd' ),
						'tag'     => 'input',
						'default' => 200,
						'attr'    => [
							'type' => 'number',
							'min'  => 100,
							'max'  => 400,
						],
					],
					'thumbSpacing'                   => [
						'label'   => __( 'Space Between Thumbnails', 'wgtd' ),
						'tag'     => 'input',
						'default' => 10,
						'attr'    => [
							'type' => 'number',
							'min'  => 0,
							'max'  => 20,
						],
					],
				],
			],
			'tagFilter'          => [
				'label'  => __( 'Tags Filter', 'wgtd' ),
				'fields' => [
					'tagsFilter'        => [
						'label'   => __( 'Enable Tags Filter', 'wgtd' ),
						'tag'     => 'checkbox',
						'default' => 1,
					],
					'tagCloudAll'       => [
						'label'   => __( 'Text for filter button "All"', 'wgtd' ),
						'tag'     => 'input',
						'default' => __( 'All', 'wgtd' ),
						'attr'    => [
							'type' => 'text',
						],
					],
					'tagCloudTextColor' => [
						'label'   => __( 'Tags Text Color', 'wgtd' ),
						'tag'     => 'input',
						'default' => 'rgba(0,0,0,1)',
						'attr'    => [
							'type' => 'color',
						],
						'options' => [
							'showAlpha' => true,
						],
					],
					'tagCloudBgColor'   => [
						'label'   => __( 'Tags Background Color', 'wgtd' ),
						'tag'     => 'input',
						'default' => 'rgba(180,180,180,1)',
						'attr'    => [
							'type' => 'color',
						],
						'options' => [
							'showAlpha' => true,
						],
					],
				],
			],
			'thumbnailsSettings' => [
				'label'  => __( 'Thumbnails Settings', 'wgtd' ),
				'fields' => [
					'collectionThumbHoverColor'               => [
						'label'   => __( 'Overlay Color on Hover', 'wgtd' ),
						'tag'     => 'input',
						'default' => 'rgba(0,0,0,0.5)',
						'options' => [
							'showAlpha' => true,
						],
						'attr'    => [
							'type' => 'color',
						],
					],
					'collectionThumbTitleShow'                => [
						'label'   => __( 'Show Title', 'wgtd' ),
						'tag'     => 'checkbox',
						'default' => 1,
					],
					'collectionThumbTitleColor'               => [
						'label'   => __( 'Title Text Color', 'wgtd' ),
						'tag'     => 'input',
						'default' => 'rgba(0,0,0,1)',
						'visible' => 'collectionThumbTitleShow',
						'options' => [
							'showAlpha' => true,
						],
						'attr'    => [
							'type' => 'color',
						],
					],
					'collectionThumbFontSize'                 => [
						'label'   => __( 'Title Font Size', 'wgtd' ),
						'tag'     => 'input',
						'default' => 18,
						'visible' => 'collectionThumbTitleShow',
						'attr'    => [
							'type' => 'number',
							'min'  => 10,
							'max'  => 36,
						],
					],
					'collectionThumbDescriptionShow'          => [
						'label'   => __( 'Show Description', 'wgtd' ),
						'tag'     => 'checkbox',
						'default' => 1,
					],
					'collectionThumbContentBGColor'           => [
						'label'   => __( 'Description Background Color', 'wgtd' ),
						'tag'     => 'input',
						'default' => 'rgba(220,220,220,1)',
						'options' => [
							'showAlpha' => true,
						],
						'attr'    => [
							'type' => 'color',
						],
					],
					'collectionThumbDescriptionColor'         => [
						'label'   => __( 'Description Text Color', 'wgtd' ),
						'tag'     => 'input',
						'default' => 'rgba(0,0,0,1)',
						'visible' => 'collectionThumbDescriptionShow',
						'options' => [
							'showAlpha' => true,
						],
						'attr'    => [
							'type' => 'color',
						],
					],
					'collectionThumbDescriptionFontSize'      => [
						'label'   => __( 'Description Font Size', 'wgtd' ),
						'tag'     => 'input',
						'default' => 15,
						'visible' => 'collectionThumbDescriptionShow',
						'attr'    => [
							'type' => 'number',
							'min'  => 10,
							'max'  => 36,
						],
					],
					'collectionReadMoreButtonLabel'           => [
						'label'   => __( 'Link Button - Default Label Text', 'wgtd' ),
						'tag'     => 'input',
						'default' => __( 'Read More', 'wgtd' ),
						'attr'    => [
							'type' => 'text',
						],
					],
					'collectionReadMoreButtonFontSize'        => [
						'label'   => __( 'Link Button - Font Size', 'wgtd' ),
						'tag'     => 'input',
						'default' => 12,
						'attr'    => [
							'type' => 'number',
							'min'  => 10,
							'max'  => 36,
						],
					],
					'collectionReadMoreButtonBGColor'         => [
						'label'   => __( 'Link Button - BG Color', 'wgtd' ),
						'tag'     => 'input',
						'default' => 'rgba(0,0,0,1)',
						'options' => [
							'showAlpha' => true,
						],
						'attr'    => [
							'type' => 'color',
						],
					],
					'collectionReadMoreButtonBGColorHover'    => [
						'label'   => __( 'Link Button - Hover BG Color', 'wgtd' ),
						'tag'     => 'input',
						'default' => 'rgba(180,180,180,1)',
						'options' => [
							'showAlpha' => true,
						],
						'attr'    => [
							'type' => 'color',
						],
					],
					'collectionReadMoreButtonLabelColor'      => [
						'label'   => __( 'Link Button - Text Color', 'wgtd' ),
						'tag'     => 'input',
						'default' => 'rgba(255,255,255,1)',
						'options' => [
							'showAlpha' => true,
						],
						'attr'    => [
							'type' => 'color',
						],
					],
					'collectionReadMoreButtonLabelColorHover' => [
						'label'   => __( 'Link Button - Hover Text Color', 'wgtd' ),
						'tag'     => 'input',
						'default' => 'rgba(0,0,0,1)',
						'options' => [
							'showAlpha' => true,
						],
						'attr'    => [
							'type' => 'color',
						],
					],
				],
			],
			'modalSettings'      => [
				'label'  => __( 'Social Share Settings', 'wgtd' ),
				'fields' => [
					'shareBarBgColor'   => [
						'label'   => __( 'Overlay BG Color', 'wgtd' ),
						'tag'     => 'input',
						'default' => 'rgba(0,0,0,0.9)',
						'attr'    => [
							'type' => 'color',
						],
						'options' => [
							'showAlpha' => true,
						],
					],
					'shareBarIconColor' => [
						'label'   => __( 'Icon Color', 'wgtd' ),
						'tag'     => 'input',
						'default' => 'rgba(255,255,255,1)',
						'attr'    => [
							'type' => 'color',
						],
						'options' => [
							'showAlpha' => true,
						],
					],
					'shareBarFacebook'  => [
						'label'   => __( 'Enable Facebook', 'wgtd' ),
						'tag'     => 'checkbox',
						'default' => 1,
					],
					'shareBarTwitter'   => [
						'label'   => __( 'Enable Twitter', 'wgtd' ),
						'tag'     => 'checkbox',
						'default' => 1,
					],
					'shareBarPinterest' => [
						'label'   => __( 'Enable Pinterest', 'wgtd' ),
						'tag'     => 'checkbox',
						'default' => 1,
					],
					'shareBarDownload'  => [
						'label'   => __( 'Enable Download', 'wgtd' ),
						'tag'     => 'checkbox',
						'default' => 1,
					],
				],
			],
			'lightboxSettings'   => [
				'label'  => __( 'Lightbox Settings', 'wgtd' ),
				'fields' => [
					'lightBoxEnable'                        => [
						'label'   => __( 'Enable Lightbox', 'wgtd' ),
						'tag'     => 'checkbox',
						'default' => 1,
						'text'    => __( 'Show item in the Lightbox by clicking on a thumbnail', 'wgtd' ),
					],
					'copyR_Protection'                      => [
						'label'   => __( 'Enable Image Protection', 'wgtd' ),
						'tag'     => 'checkbox',
						'default' => 1,
						'text'    => __( 'Disable right mouse click for images', 'wgtd' ),
					],
					'copyR_Alert'                           => [
						'label'   => __( 'Copyright Alert (right mouse click)', 'wgtd' ),
						'tag'     => 'input',
						'default' => __( 'Hello, this photo is mine!', 'wgtd ' ),
						'text'    => __( 'Show this message when visitor clicks the right mouse button on a photo', 'wgtd' ),
					],
					'sliderScrollNavi'                      => [
						'label'   => __( 'Use Mouse Wheel for Navigation', 'wgtd' ),
						'tag'     => 'checkbox',
						'default' => 1,
						'text'    => __( 'Note: This option disable scaling with mouse wheel!', 'wgtd' ),
					],
					'sliderNextPrevAnimation'               => [
						'label'   => __( 'Transition Type Between Items', 'wgtd' ),
						'tag'     => 'select',
						'default' => 'animation',
						'options' => [
							[
								'name'  => __( 'Slipping', 'wgtd' ),
								'value' => 'animation',
							],
							[
								'name'  => __( 'Fading', 'wgtd' ),
								'value' => 'fading',
							],
						],
					],
					'sliderBgColor'                         => [
						'label'   => __( 'Lightbox BG Color', 'wgtd' ),
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
						'label'   => __( 'Preloader Color', 'wgtd' ),
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
						'label'   => __( 'Lightbox Header & Footer Color', 'wgtd' ),
						'tag'     => 'input',
						'default' => 'rgba(0,0,0,0.4)',
						'options' => [
							'showAlpha' => true,
						],
						'attr'    => [
							'type' => 'color',
						],
						'text'    => __( 'Set the background color for header and footer (with fading to transparent)', 'wgtd' ),
					],
					'sliderNavigationColor'                 => [
						'label'   => __( 'Main Controls - BG Color', 'wgtd' ),
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
						'label'   => __( 'Main Controls - Hover BG Color', 'wgtd' ),
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
						'label'   => __( 'Main Controls - Icon Color', 'wgtd' ),
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
						'label'   => __( 'Main Controls - Icon Hover Color', 'wgtd' ),
						'tag'     => 'input',
						'default' => 'rgba(0,0,0,1)',
						'attr'    => [
							'type' => 'color',
						],
						'options' => [
							'showAlpha' => true,
						],
					],
					// 'itemCounterColor' => array(
					// 	'label' => __('Items Counter Color', 'wgtd'),
					// 	'tag' => 'input',
					// 	'default' => 'rgba(255,255,255,1)',
					// 	'attr' => array(
					// 		'type' => 'color',
					// 	),
					// 	'options' => array(
					// 		'showAlpha' => true
					// 	)
					// ),
					// 'sliderDescriptionShow' => array(
					// 	'label' => __('Show Description Bar (Title + Description)', 'wgtd'),
					// 	'tag' => 'checkbox',
					// 	'default' => 1,
					// ),
					'sliderItemTitleEnable'                 => [
						'label'   => __( 'Show Title', 'wgtd' ),
						'tag'     => 'checkbox',
						'default' => 1,
					],
					'sliderItemTitleFontSize'               => [
						'label'   => __( 'Title - Font Size', 'wgtd' ),
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
						'label'   => __( 'Title - Text Color', 'wgtd' ),
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
						'label'   => __( 'Show Description', 'wgtd' ),
						'tag'     => 'checkbox',
						'default' => 1,
					],
					'sliderItemDescriptionFontSize'         => [
						'label'   => __( 'Description - Font Size', 'wgtd' ),
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
						'label'   => __( 'Description - Text Color', 'wgtd' ),
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
						'label'   => __( 'Show Image EXIF Data', 'wgtd' ),
						'tag'     => 'checkbox',
						'default' => 1,
					],
					'sliderThumbBarEnable'                  => [
						'label'   => __( 'Show Thumbnails Bar', 'wgtd' ),
						'tag'     => 'checkbox',
						'default' => 1,
					],
					'sliderThumbBarHoverColor'              => [
						'label'   => __( 'Active Thumbnail Border Color', 'wgtd' ),
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
						'label'   => __( 'Show Slideshow Button', 'wgtd' ),
						'tag'     => 'checkbox',
						'default' => 1,
					],
					'slideshowDelay'                        => [
						'label'   => __( 'Slideshow - Timer', 'wgtd' ),
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
						'label'   => __( 'Slideshow - Progress Bar Color', 'wgtd' ),
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
						'label'   => __( 'Slideshow - Progress Bar BG Color', 'wgtd' ),
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
						'label'   => __( 'Enable Zooming', 'wgtd' ),
						'tag'     => 'checkbox',
						'default' => 1,
					],
					'sliderSocialShareEnabled'              => [
						'label'   => __( 'Show "Share" Button', 'wgtd' ),
						'tag'     => 'checkbox',
						'default' => 1,
					],
					'sliderFullScreen'                      => [
						'label'   => __( 'Show "Fullscreen" Button', 'wgtd' ),
						'tag'     => 'checkbox',
						'default' => 1,
					],
					// 'sliderItemDiscuss' => array(
					// 	'label' => __('Show "Comments" Button', 'wgtd'),
					// 	'tag' => 'checkbox',
					// 	'default' => 1,
					// 	'premium' => 1
					// ),
					'sliderThumbSubMenuBackgroundColor'     => [
						'label'   => __( 'Buttons BG Color', 'wgtd' ),
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
						'label'   => __( 'Buttons Icon Color', 'wgtd' ),
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
						'label'   => __( 'Buttons Hover BG Color', 'wgtd' ),
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
						'label'   => __( 'Buttons Icon Hover Color', 'wgtd' ),
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
		];

		return apply_filters( 'woowgallery_skin_settings', $schema, self::SLUG );
	}

	/**
	 * Add Skin to WoowGallery Skins
	 *
	 * @param array $skins Array of Skins Objects.
	 *
	 * @return array
	 */
	public function add_skin( $skins ) {

		$skins[ self::SLUG ] = $this;

		return $skins;
	}
}

new Amron();
