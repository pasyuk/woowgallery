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
	const VERSION     = '1.1.1';
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
		if ( ! empty( $gallery['lightbox']['slug'] ) ) {
			$gallery['skin']['config']['lightBoxEnable'] = 1;
			$gallery['skin']['config']                   = array_merge( (array) $gallery['lightbox']['config'], $gallery['skin']['config'] );
		} else {
			$gallery['skin']['config']['lightBoxEnable'] = 0;
		}
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
				'label'  => __( 'Common Settings', 'woowgallery' ),
				'fields' => [
					'collectionPreloaderColor'       => [
						'label'   => __( 'Gallery Preloader Color', 'woowgallery' ),
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
						'label'   => __( 'Gallery Background Color', 'woowgallery' ),
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
						'label'   => __( 'Gallery Columns', 'woowgallery' ),
						'tag'     => 'input',
						'default' => 3,
						'attr'    => [
							'type' => 'number',
							'min'  => 1,
							'max'  => 10,
						],
					],
					'collectionThumbRecomendedWidth' => [
						'label'   => __( 'Thumbnail Min. Width', 'woowgallery' ),
						'tag'     => 'input',
						'default' => 200,
						'attr'    => [
							'type' => 'number',
							'min'  => 100,
							'max'  => 400,
						],
					],
					'thumbSpacing'                   => [
						'label'   => __( 'Space Between Thumbnails', 'woowgallery' ),
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
				'label'  => __( 'Tags Filter', 'woowgallery' ),
				'fields' => [
					'tagsFilter'        => [
						'label'   => __( 'Enable Tags Filter', 'woowgallery' ),
						'tag'     => 'checkbox',
						'default' => 1,
					],
					'tagCloudAll'       => [
						'label'   => __( 'Text for filter button "All"', 'woowgallery' ),
						'tag'     => 'input',
						'default' => __( 'All', 'woowgallery' ),
						'attr'    => [
							'type' => 'text',
						],
					],
					'tagCloudTextColor' => [
						'label'   => __( 'Tags Text Color', 'woowgallery' ),
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
						'label'   => __( 'Tags Background Color', 'woowgallery' ),
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
				'label'  => __( 'Thumbnails Settings', 'woowgallery' ),
				'fields' => [
					'collectionThumbHoverColor'               => [
						'label'   => __( 'Overlay Color on Hover', 'woowgallery' ),
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
						'label'   => __( 'Show Title', 'woowgallery' ),
						'tag'     => 'checkbox',
						'default' => 1,
					],
					'collectionThumbTitleColor'               => [
						'label'   => __( 'Title Text Color', 'woowgallery' ),
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
						'label'   => __( 'Title Font Size', 'woowgallery' ),
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
						'label'   => __( 'Show Description', 'woowgallery' ),
						'tag'     => 'checkbox',
						'default' => 1,
					],
					'collectionThumbContentBGColor'           => [
						'label'   => __( 'Description Background Color', 'woowgallery' ),
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
						'label'   => __( 'Description Text Color', 'woowgallery' ),
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
						'label'   => __( 'Description Font Size', 'woowgallery' ),
						'tag'     => 'input',
						'default' => 15,
						'visible' => 'collectionThumbDescriptionShow',
						'attr'    => [
							'type' => 'number',
							'min'  => 10,
							'max'  => 36,
						],
					],
					'collectionReadMoreButtonShow'            => [
						'label'   => __( 'Show Link Button', 'woowgallery' ),
						'tag'     => 'checkbox',
						'default' => 1,
					],
					'collectionReadMoreButtonLabel'           => [
						'label'   => __( 'Link Button - Default Label Text', 'woowgallery' ),
						'tag'     => 'input',
						'default' => __( 'Read More', 'woowgallery' ),
						'visible' => 'collectionReadMoreButtonShow',
						'attr'    => [
							'type' => 'text',
						],
					],
					'collectionReadMoreButtonFontSize'        => [
						'label'   => __( 'Link Button - Font Size', 'woowgallery' ),
						'tag'     => 'input',
						'default' => 12,
						'visible' => 'collectionReadMoreButtonShow',
						'attr'    => [
							'type' => 'number',
							'min'  => 10,
							'max'  => 36,
						],
					],
					'collectionReadMoreButtonBGColor'         => [
						'label'   => __( 'Link Button - BG Color', 'woowgallery' ),
						'tag'     => 'input',
						'default' => 'rgba(0,0,0,1)',
						'visible' => 'collectionReadMoreButtonShow',
						'options' => [
							'showAlpha' => true,
						],
						'attr'    => [
							'type' => 'color',
						],
					],
					'collectionReadMoreButtonBGColorHover'    => [
						'label'   => __( 'Link Button - Hover BG Color', 'woowgallery' ),
						'tag'     => 'input',
						'default' => 'rgba(180,180,180,1)',
						'visible' => 'collectionReadMoreButtonShow',
						'options' => [
							'showAlpha' => true,
						],
						'attr'    => [
							'type' => 'color',
						],
					],
					'collectionReadMoreButtonLabelColor'      => [
						'label'   => __( 'Link Button - Text Color', 'woowgallery' ),
						'tag'     => 'input',
						'default' => 'rgba(255,255,255,1)',
						'visible' => 'collectionReadMoreButtonShow',
						'options' => [
							'showAlpha' => true,
						],
						'attr'    => [
							'type' => 'color',
						],
					],
					'collectionReadMoreButtonLabelColorHover' => [
						'label'   => __( 'Link Button - Hover Text Color', 'woowgallery' ),
						'tag'     => 'input',
						'default' => 'rgba(0,0,0,1)',
						'visible' => 'collectionReadMoreButtonShow',
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
				'label'  => __( 'Social Share Settings', 'woowgallery' ),
				'fields' => [
					'shareBarBgColor'   => [
						'label'   => __( 'Overlay BG Color', 'woowgallery' ),
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
						'label'   => __( 'Icon Color', 'woowgallery' ),
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
						'label'   => __( 'Enable Facebook', 'woowgallery' ),
						'tag'     => 'checkbox',
						'default' => 1,
					],
					'shareBarTwitter'   => [
						'label'   => __( 'Enable Twitter', 'woowgallery' ),
						'tag'     => 'checkbox',
						'default' => 1,
					],
					'shareBarPinterest' => [
						'label'   => __( 'Enable Pinterest', 'woowgallery' ),
						'tag'     => 'checkbox',
						'default' => 1,
					],
					'shareBarDownload'  => [
						'label'   => __( 'Enable Download', 'woowgallery' ),
						'tag'     => 'checkbox',
						'default' => 1,
					],
				],
			],
			//'lightboxSettings'   => [
			//	'label'  => __( 'Lightbox Settings', 'woowgallery' ),
			//	'fields' => [
			//		'lightBoxEnable'                        => [
			//			'label'   => __( 'Enable Lightbox', 'woowgallery' ),
			//			'tag'     => 'checkbox',
			//			'default' => 1,
			//			'text'    => __( 'Show item in the Lightbox by clicking on a thumbnail', 'woowgallery' ),
			//		],
			//		'copyR_Protection'                      => [
			//			'label'   => __( 'Enable Image Protection', 'woowgallery' ),
			//			'tag'     => 'checkbox',
			//			'default' => 1,
			//			'text'    => __( 'Disable right mouse click for images', 'woowgallery' ),
			//		],
			//		'copyR_Alert'                           => [
			//			'label'   => __( 'Copyright Alert (right mouse click)', 'woowgallery' ),
			//			'tag'     => 'input',
			//			'default' => __( 'Hello, this photo is mine!', 'woowgallery' ),
			//			'text'    => __( 'Show this message when visitor clicks the right mouse button on a photo', 'woowgallery' ),
			//		],
			//		'sliderScrollNavi'                      => [
			//			'label'   => __( 'Use Mouse Wheel for Navigation', 'woowgallery' ),
			//			'tag'     => 'checkbox',
			//			'default' => 1,
			//			'text'    => __( 'Note: This option disable scaling with mouse wheel!', 'woowgallery' ),
			//		],
			//		'sliderNextPrevAnimation'               => [
			//			'label'   => __( 'Transition Type Between Items', 'woowgallery' ),
			//			'tag'     => 'select',
			//			'default' => 'animation',
			//			'options' => [
			//				[
			//					'name'  => __( 'Slipping', 'woowgallery' ),
			//					'value' => 'animation',
			//				],
			//				[
			//					'name'  => __( 'Fading', 'woowgallery' ),
			//					'value' => 'fading',
			//				],
			//			],
			//		],
			//		'sliderBgColor'                         => [
			//			'label'   => __( 'Lightbox BG Color', 'woowgallery' ),
			//			'tag'     => 'input',
			//			'default' => 'rgba(0,0,0,0.9)',
			//			'attr'    => [
			//				'type' => 'color',
			//			],
			//			'options' => [
			//				'showAlpha' => true,
			//			],
			//		],
			//		'sliderPreloaderColor'                  => [
			//			'label'   => __( 'Preloader Color', 'woowgallery' ),
			//			'tag'     => 'input',
			//			'default' => 'rgba(255,255,255,1)',
			//			'attr'    => [
			//				'type' => 'color',
			//			],
			//			'options' => [
			//				'showAlpha' => true,
			//			],
			//		],
			//		'sliderHeaderFooterBgColor'             => [
			//			'label'   => __( 'Lightbox Header & Footer Color', 'woowgallery' ),
			//			'tag'     => 'input',
			//			'default' => 'rgba(0,0,0,0.4)',
			//			'options' => [
			//				'showAlpha' => true,
			//			],
			//			'attr'    => [
			//				'type' => 'color',
			//			],
			//			'text'    => __( 'Set the background color for header and footer (with fading to transparent)', 'woowgallery' ),
			//		],
			//		'sliderNavigationColor'                 => [
			//			'label'   => __( 'Main Controls - BG Color', 'woowgallery' ),
			//			'tag'     => 'input',
			//			'default' => 'rgba(0,0,0,1)',
			//			'attr'    => [
			//				'type' => 'color',
			//			],
			//			'options' => [
			//				'showAlpha' => true,
			//			],
			//		],
			//		'sliderNavigationColorOver'             => [
			//			'label'   => __( 'Main Controls - Hover BG Color', 'woowgallery' ),
			//			'tag'     => 'input',
			//			'default' => 'rgba(255,255,255,1)',
			//			'attr'    => [
			//				'type' => 'color',
			//			],
			//			'options' => [
			//				'showAlpha' => true,
			//			],
			//		],
			//		'sliderNavigationIconColor'             => [
			//			'label'   => __( 'Main Controls - Icon Color', 'woowgallery' ),
			//			'tag'     => 'input',
			//			'default' => 'rgba(255,255,255,1)',
			//			'attr'    => [
			//				'type' => 'color',
			//			],
			//			'options' => [
			//				'showAlpha' => true,
			//			],
			//		],
			//		'sliderNavigationIconColorOver'         => [
			//			'label'   => __( 'Main Controls - Icon Hover Color', 'woowgallery' ),
			//			'tag'     => 'input',
			//			'default' => 'rgba(0,0,0,1)',
			//			'attr'    => [
			//				'type' => 'color',
			//			],
			//			'options' => [
			//				'showAlpha' => true,
			//			],
			//		],
			//		// 'itemCounterColor' => array(
			//		// 	'label' => __('Items Counter Color', 'woowgallery'),
			//		// 	'tag' => 'input',
			//		// 	'default' => 'rgba(255,255,255,1)',
			//		// 	'attr' => array(
			//		// 		'type' => 'color',
			//		// 	),
			//		// 	'options' => array(
			//		// 		'showAlpha' => true
			//		// 	)
			//		// ),
			//		// 'sliderDescriptionShow' => array(
			//		// 	'label' => __('Show Description Bar (Title + Description)', 'woowgallery'),
			//		// 	'tag' => 'checkbox',
			//		// 	'default' => 1,
			//		// ),
			//		'sliderItemTitleEnable'                 => [
			//			'label'   => __( 'Show Title', 'woowgallery' ),
			//			'tag'     => 'checkbox',
			//			'default' => 1,
			//		],
			//		'sliderItemTitleFontSize'               => [
			//			'label'   => __( 'Title - Font Size', 'woowgallery' ),
			//			'visible' => 'sliderItemTitleEnable == "1"',
			//			'tag'     => 'input',
			//			'default' => 18,
			//			'attr'    => [
			//				'type' => 'number',
			//				'min'  => 18,
			//				'max'  => 36,
			//			],
			//		],
			//		'sliderItemTitleTextColor'              => [
			//			'label'   => __( 'Title - Text Color', 'woowgallery' ),
			//			'visible' => 'sliderItemTitleEnable == "1"',
			//			'tag'     => 'input',
			//			'default' => 'rgba(255,255,255,1)',
			//			'attr'    => [
			//				'type' => 'color',
			//			],
			//			'options' => [
			//				'showAlpha' => true,
			//			],
			//		],
			//		'sliderItemDescriptionEnable'           => [
			//			'label'   => __( 'Show Description', 'woowgallery' ),
			//			'tag'     => 'checkbox',
			//			'default' => 1,
			//		],
			//		'sliderItemDescriptionFontSize'         => [
			//			'label'   => __( 'Description - Font Size', 'woowgallery' ),
			//			'visible' => 'sliderItemDescriptionEnable == "1"',
			//			'tag'     => 'input',
			//			'default' => 16,
			//			'attr'    => [
			//				'type' => 'number',
			//				'min'  => 12,
			//				'max'  => 36,
			//			],
			//		],
			//		'sliderItemDescriptionTextColor'        => [
			//			'label'   => __( 'Description - Text Color', 'woowgallery' ),
			//			'visible' => 'sliderItemDescriptionEnable == "1"',
			//			'tag'     => 'input',
			//			'default' => 'rgba(255,255,255,0.8)',
			//			'attr'    => [
			//				'type' => 'color',
			//			],
			//			'options' => [
			//				'showAlpha' => true,
			//			],
			//		],
			//		'infoBarExifEnable'                     => [
			//			'label'   => __( 'Show Image EXIF Data', 'woowgallery' ),
			//			'tag'     => 'checkbox',
			//			'default' => 1,
			//		],
			//		'sliderThumbBarEnable'                  => [
			//			'label'   => __( 'Show Thumbnails Bar', 'woowgallery' ),
			//			'tag'     => 'checkbox',
			//			'default' => 1,
			//		],
			//		'sliderThumbBarHoverColor'              => [
			//			'label'   => __( 'Active Thumbnail Border Color', 'woowgallery' ),
			//			'visible' => 'sliderThumbBarEnable == "1"',
			//			'tag'     => 'input',
			//			'default' => 'rgba(255,255,255,1)',
			//			'attr'    => [
			//				'type' => 'color',
			//			],
			//			'options' => [
			//				'showAlpha' => true,
			//			],
			//		],
			//		'sliderPlayButton'                      => [
			//			'label'   => __( 'Show Slideshow Button', 'woowgallery' ),
			//			'tag'     => 'checkbox',
			//			'default' => 1,
			//		],
			//		'slideshowDelay'                        => [
			//			'label'   => __( 'Slideshow - Timer', 'woowgallery' ),
			//			'visible' => 'sliderPlayButton == "1"',
			//			'tag'     => 'input',
			//			'default' => 8,
			//			'attr'    => [
			//				'type' => 'number',
			//				'min'  => 2,
			//				'max'  => 30,
			//			],
			//		],
			//		'slideshowProgressBarColor'             => [
			//			'label'   => __( 'Slideshow - Progress Bar Color', 'woowgallery' ),
			//			'visible' => 'sliderPlayButton == "1"',
			//			'tag'     => 'input',
			//			'default' => 'rgba(255,255,255,1)',
			//			'attr'    => [
			//				'type' => 'color',
			//			],
			//			'options' => [
			//				'showAlpha' => true,
			//			],
			//		],
			//		'slideshowProgressBarBGColor'           => [
			//			'label'   => __( 'Slideshow - Progress Bar BG Color', 'woowgallery' ),
			//			'visible' => 'sliderPlayButton == "1"',
			//			'tag'     => 'input',
			//			'default' => 'rgba(255,255,255,0.6)',
			//			'attr'    => [
			//				'type' => 'color',
			//			],
			//			'options' => [
			//				'showAlpha' => true,
			//			],
			//		],
			//		'sliderZoomButton'                      => [
			//			'label'   => __( 'Enable Zooming', 'woowgallery' ),
			//			'tag'     => 'checkbox',
			//			'default' => 1,
			//		],
			//		'sliderSocialShareEnabled'              => [
			//			'label'   => __( 'Show "Share" Button', 'woowgallery' ),
			//			'tag'     => 'checkbox',
			//			'default' => 1,
			//		],
			//		'sliderFullScreen'                      => [
			//			'label'   => __( 'Show "Fullscreen" Button', 'woowgallery' ),
			//			'tag'     => 'checkbox',
			//			'default' => 1,
			//		],
			//		// 'sliderItemDiscuss' => array(
			//		// 	'label' => __('Show "Comments" Button', 'woowgallery'),
			//		// 	'tag' => 'checkbox',
			//		// 	'default' => 1,
			//		// 	'premium' => 1
			//		// ),
			//		'sliderThumbSubMenuBackgroundColor'     => [
			//			'label'   => __( 'Buttons BG Color', 'woowgallery' ),
			//			'tag'     => 'input',
			//			'default' => 'rgba(0, 0, 0, 0)',
			//			'options' => [
			//				'showAlpha' => true,
			//			],
			//			'attr'    => [
			//				'type' => 'color',
			//			],
			//		],
			//		'sliderThumbSubMenuIconColor'           => [
			//			'label'   => __( 'Buttons Icon Color', 'woowgallery' ),
			//			'tag'     => 'input',
			//			'default' => 'rgba(255, 255, 255, 1)',
			//			'options' => [
			//				'showAlpha' => true,
			//			],
			//			'attr'    => [
			//				'type' => 'color',
			//			],
			//		],
			//		'sliderThumbSubMenuBackgroundColorOver' => [
			//			'label'   => __( 'Buttons Hover BG Color', 'woowgallery' ),
			//			'tag'     => 'input',
			//			'default' => 'rgba(255, 255, 255, 1)',
			//			'options' => [
			//				'showAlpha' => true,
			//			],
			//			'attr'    => [
			//				'type' => 'color',
			//			],
			//		],
			//		'sliderThumbSubMenuIconHoverColor'      => [
			//			'label'   => __( 'Buttons Icon Hover Color', 'woowgallery' ),
			//			'tag'     => 'input',
			//			'default' => 'rgba(0, 0, 0, 1)',
			//			'options' => [
			//				'showAlpha' => true,
			//			],
			//			'attr'    => [
			//				'type' => 'color',
			//			],
			//		],
			//	],
			//],
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
