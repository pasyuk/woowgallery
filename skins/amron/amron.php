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
						'label'   => __( 'Space between thumbnails', 'wgtd' ),
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
						'label'   => __( 'Tags Filter', 'wgtd' ),
						'tag'     => 'checkbox',
						'default' => 1,
					],
					'tagCloudAll'       => [
						'label'   => __( 'ALL - name', 'wgtd' ),
						'tag'     => 'input',
						'default' => 'All',
						'attr'    => [
							'type' => 'text',
						],
					],
					'tagCloudTextColor' => [
						'label'   => __( 'Text color', 'wgtd' ),
						'tag'     => 'input',
						'default' => 'rgba(0,0,0,1)',
						'attr'    => [
							'type' => 'color',
						],
						'options' => [
							'showAlpha' => true,
						],
						'text'    => __( 'Filter button', 'wgtd' ),
					],
					'tagCloudBgColor'   => [
						'label'   => __( 'Background color', 'wgtd' ),
						'tag'     => 'input',
						'default' => 'rgba(180,180,180,1)',
						'attr'    => [
							'type' => 'color',
						],
						'options' => [
							'showAlpha' => true,
						],
						'text'    => __( 'Filter button', 'wgtd' ),
					],
				],
			],
			'thumbnailsSettings' => [
				'label'  => __( 'Thumbnails Settings', 'wgtd' ),
				'fields' => [
					'collectionThumbHoverColor'               => [
						'label'   => __( 'Hover color', 'wgtd' ),
						'tag'     => 'input',
						'default' => 'rgba(0,0,0,0.5)',
						'options' => [
							'showAlpha' => true,
						],
						'attr'    => [
							'type' => 'color',
						],
					],
					'collectionThumbContentBGColor'           => [
						'label'   => __( 'Description bar background color', 'wgtd' ),
						'tag'     => 'input',
						'default' => 'rgba(220,220,220,1)',
						'options' => [
							'showAlpha' => true,
						],
						'attr'    => [
							'type' => 'color',
						],
					],
					'collectionThumbTitleShow'                => [
						'label'   => __( 'Title', 'wgtd' ),
						'tag'     => 'checkbox',
						'default' => 1,
					],
					'collectionThumbTitleColor'               => [
						'label'   => __( 'Title Text color', 'wgtd' ),
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
						'label'   => __( 'Title font size', 'wgtd' ),
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
						'label'   => __( 'Item Description', 'wgtd' ),
						'tag'     => 'checkbox',
						'default' => 1,
					],
					'collectionThumbDescriptionColor'         => [
						'label'   => __( 'Description Text color', 'wgtd' ),
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
						'label'   => __( 'Description font size', 'wgtd' ),
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
						'label'   => __( 'Read More button - Default Label Text', 'wgtd' ),
						'tag'     => 'input',
						'default' => 'Read More',
						'attr'    => [
							'type' => 'text',
						],
					],
					'collectionReadMoreButtonBGColor'         => [
						'label'   => __( 'Read More button color', 'wgtd' ),
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
						'label'   => __( 'Read More button Hover color', 'wgtd' ),
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
						'label'   => __( 'Read More button Label color', 'wgtd' ),
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
						'label'   => __( 'Read More button Label Hover color', 'wgtd' ),
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
				'label'  => __( 'Modal Window Settings (Item Info Bar)', 'wgtd' ),
				'fields' => [
					'modaBgColor'                => [
						'label'   => __( 'Overlap Color', 'wgtd' ),
						'tag'     => 'input',
						'default' => 'rgba(0,0,0,0.9)',
						'attr'    => [
							'type' => 'color',
						],
						'options' => [
							'showAlpha' => true,
						],
					],
					'modalInfoBoxBgColor'        => [
						'label'   => __( 'Info Bar Color', 'wgtd' ),
						'tag'     => 'input',
						'default' => 'rgba(255,255,255,1)',
						'attr'    => [
							'type' => 'color',
						],
						'options' => [
							'showAlpha' => true,
						],
					],
					'modalInfoBoxTitleTextColor' => [
						'label'   => __( 'Info Bar Title text Color', 'wgtd' ),
						'tag'     => 'input',
						'default' => 'rgba(0,0,0,1)',
						'attr'    => [
							'type' => 'color',
						],
						'options' => [
							'showAlpha' => true,
						],
					],
					'modalInfoBoxTextColor'      => [
						'label'   => __( 'Info Bar Text Color', 'wgtd' ),
						'tag'     => 'input',
						'default' => 'rgba(90,90,90,1)',
						'attr'    => [
							'type' => 'color',
						],
						'options' => [
							'showAlpha' => true,
						],
					],
					'infoBarExifEnable'          => [
						'label'   => __( 'Show Item EXIF Data', 'wgtd' ),
						'tag'     => 'checkbox',
						'default' => 1,
					],
					'infoBarDateInfoEnable'      => [
						'label'   => __( 'Show Item Upload Date', 'wgtd' ),
						'tag'     => 'checkbox',
						'default' => 1,
					],
				],
			],
			'lightboxSettings'   => [
				'label'  => __( 'Lightbox Settings', 'wgtd' ),
				'fields' => [
					'lightBoxEnable'                        => [
						'label'   => __( 'Lightbox', 'wgtd' ),
						'tag'     => 'checkbox',
						'default' => 1,
						'text'    => __( 'Show the item in the Lightbox by clicking on the thumbnail', 'wgtd' ),
					],
					'copyR_Protection'                      => [
						'label'   => __( 'Enable Download Protection', 'wgtd' ),
						'tag'     => 'checkbox',
						'default' => 1,
						'text'    => __( 'Disable right click to protect content from download', 'wgtd' ),
					],
					'copyR_Alert'                           => [
						'label'   => __( 'Copyright Alert (right mouse click)', 'wgtd' ),
						'tag'     => 'input',
						'default' => 'Hello, this photo is mine!',
						'text'    => __( 'Alert about the ban on downloading photo', 'wgtd' ),
						'attr'    => [
							'type' => 'This message is displayed when a visitor clicks the right mouse button on a photo in a lightbox',
						],
					],
					'sliderScrollNavi'                      => [
						'label'   => __( 'Scroll to navigate (mouse wheel)', 'wgtd' ),
						'tag'     => 'checkbox',
						'default' => 1,
						'text'    => __( 'Using this disable mouse wheel scaling!', 'wgtd' ),
					],
					'sliderNextPrevAnimation'               => [
						'label'   => __( 'Items Transition Type', 'woowbox' ),
						'tag'     => 'select',
						'default' => 'animation',
						'options' => [
							[
								'name'  => 'Slipping',
								'value' => 'animation',
							],
							[
								'name'  => 'Fading',
								'value' => 'fading',
							],
						],
					],
					'sliderBgColor'                         => [
						'label'   => __( 'Lightbox  background color', 'wgtd' ),
						'tag'     => 'input',
						'default' => 'rgba(0,0,0,0.9)',
						'attr'    => [
							'type' => 'color',
						],
						'options' => [
							'showAlpha' => true,
						],
						'text'    => __( 'Set the background color for lightbox', 'wgtd' ),
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
						'text'    => __( 'Set custom color for gallery', 'wgtd' ),
					],
					'sliderHeaderFooterBgColor'             => [
						'label'   => __( 'Lightbox Header & Footer color', 'wgtd' ),
						'tag'     => 'input',
						'default' => 'rgba(0,0,0,0.4)',
						'options' => [
							'showAlpha' => true,
						],
						'attr'    => [
							'type' => 'color',
						],
						'text'    => __( 'Set the background color for header and footer (gradient)', 'wgtd' ),
					],
					'sliderNavigationColor'                 => [
						'label'   => __( 'Main Controls Color', 'wgtd' ),
						'tag'     => 'input',
						'default' => 'rgba(0,0,0,1)',
						'attr'    => [
							'type' => 'color',
						],
						'options' => [
							'showAlpha' => true,
						],
						'text'    => __( 'Buttons Background Color', 'wgtd' ),
					],
					'sliderNavigationColorOver'             => [
						'label'   => __( 'Main Controls Color (over)', 'wgtd' ),
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
						'label'   => __( 'Main Controls Icon Color', 'wgtd' ),
						'tag'     => 'input',
						'default' => 'rgba(255,255,255,1)',
						'attr'    => [
							'type' => 'color',
						],
						'options' => [
							'showAlpha' => true,
						],
						'text'    => __( 'Icon Color', 'wgtd' ),
					],
					'sliderNavigationIconColorOver'         => [
						'label'   => __( 'Main Controls Icon Color (over)', 'wgtd' ),
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
						'label'   => __( 'Item Title - font size', 'wgtd' ),
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
						'label'   => __( 'Item Title - text color', 'wgtd' ),
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
					// 'sliderItemDescriptionEnable' => array(
					// 	'label' => __('Show Description', 'wgtd'),
					// 	'tag' => 'checkbox',
					// 	'visible' => 'sliderDescriptionShow=="1"',
					// 	'default' => 1,
					// ),
					// 'sliderItemDescriptionFontSize' => array(
					// 	'label' => __('Item Description - font size', 'wgtd'),
					// 	'visible' => 'sliderItemDescriptionEnable == "1" and sliderDescriptionShow=="1"',
					// 	'tag' => 'input',
					// 	'default' => 16,
					// 	'attr' => array(
					// 		'type' => 'number',
					// 		'min' => 12,
					// 		'max' => 36,
					// 	),
					// ),
					// 'sliderItemDescriptionTextColor' => array(
					// 	'label' => __('Item Description - text color', 'wgtd'),
					// 	'visible' => 'sliderItemDescriptionEnable == "1" and sliderDescriptionShow=="1"',
					// 	'tag' => 'input',
					// 	'default' => 'rgba(255,255,255,0.8)',
					// 	'attr' => array(
					// 		'type' => 'color',
					// 	),
					// 	'options' => array(
					// 		'showAlpha' => true
					// 	)
					// ),
					'sliderThumbBarEnable'                  => [
						'label'   => __( 'Show Thumbnails Bar', 'wgtd' ),
						'tag'     => 'checkbox',
						'default' => 1,
						//'premium' => 1
					],
					'sliderThumbBarHoverColor'              => [
						'label'   => __( 'Thumbnails Border Color (select mode)', 'wgtd' ),
						'visible' => 'sliderThumbBarEnable == "1"',
						'tag'     => 'input',
						'default' => 'rgba(255,255,255,1)',
						//'premium' => 1,
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
						'label'   => __( 'Slideshows Timer', 'wgtd' ),
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
						'label'   => __( 'Slideshow progress bar color', 'wgtd' ),
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
						'label'   => __( 'Slideshow progress bar Background color', 'wgtd' ),
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
						'label'   => __( 'Enable Zooom ', 'wgtd' ),
						'tag'     => 'checkbox',
						'default' => 1,
					],
					'sliderInfoEnable'                      => [
						'label'   => __( 'Show Info Button', 'wgtd' ),
						'tag'     => 'checkbox',
						'default' => 1,
						'text'    => __( 'Enable description bar for item', 'wgtd' ),
						//'premium' => 1
					],
					'sliderSocialShareEnabled'              => [
						'label'   => __( 'Show Share Buttons', 'wgtd' ),
						'tag'     => 'checkbox',
						'default' => 1,
						//'premium' => 1
					],
					'sliderItemDownload'                    => [
						'label'   => __( 'Show Download Button', 'wgtd' ),
						'tag'     => 'checkbox',
						'default' => 1,
						'text'    => __( 'Download original file', 'wgtd' ),
						//'premium' => 1
					],
					'sliderFullScreen'                      => [
						'label'   => __( 'FullScreen Button Show', 'wgtd' ),
						'tag'     => 'checkbox',
						'default' => 1,
						//'premium' => 1
					],
					// 'sliderItemDiscuss' => array(
					// 	'label' => __('Show Comments Button', 'wgtd'),
					// 	'tag' => 'checkbox',
					// 	'default' => 1,
					// 	'premium' => 1
					// ),
					'sliderThumbSubMenuBackgroundColor'     => [
						'label'   => __( 'Submenu button color', 'wgtd' ),
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
						'label'   => __( 'Submenu button Icon color', 'wgtd' ),
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
						'label'   => __( 'Submenu button Hover color', 'wgtd' ),
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
						'label'   => __( 'Submenu button Icon Hover color', 'wgtd' ),
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
