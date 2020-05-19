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
			'common'   => [
				'label'  => __( 'Common Settings', 'wgtd' ),
				'fields' => [
					'collectionPreloaderColor' => array(
						'label' => __('Gallery Preloader Color', 'wgtd'),
						'tag' => 'input',
						'default' => 'rgba(180,180,180,1)',
						'attr' => array(
							'type' => 'color',
						),
						'options' => array(
							'showAlpha' => true
						),
					),
					'collectionThumbColumns'     => array(
						'label'   => __( 'Gallery Columns', 'wgtd' ),
						'tag'     => 'input',
						'default' => 3,
						'attr'    => array(
							'type' => 'number',
							'min'  => 1,
							'max'  => 10,
						),
					),
					'collectionThumbRecomendedWidth'     => array(
						'label'   => __( 'Thumbnail Min. Width', 'wgtd' ),
						'tag'     => 'input',
						'default' => 200,
						'attr'    => array(
							'type' => 'number',
							'min'  => 100,
							'max'  => 400,
						),
					),
					'thumbSpacing'     => array(
						'label'   => __( 'Space between thumbnails', 'wgtd' ),
						'tag'     => 'input',
						'default' => 10,
						'attr'    => array(
							'type' => 'number',
							'min'  => 0,
							'max'  => 20,
						),
					),
				],
			],
			'tagFilter' => [
				'label'  => __( 'Tag Filter', 'wgtd' ),
				'fields' => [
					'tagsFilter'         => array(
						'label'   => __( 'Album Filter enable', 'wgtd' ),
						'tag'     => 'checkbox',
						'default' => 1,
					),
					'tagCloudAll' => array(
						'label' => __('ALL - name)', 'wgtd'),
						'tag'   => 'input',
						'default' => 'All',
						'attr'    => array(
							'type' => 'text',
						)
					),
					'tagCloudTextColor' => array(
						'label' => __('Text color', 'wgtd'),
						'tag' => 'input',
						'default' => 'rgba(0,0,0,1)',
						'attr' => array(
							'type' => 'color',
						),
						'options' => array(
							'showAlpha' => true
						),
						'text' => __('Filter button', 'wgtd'),
					),
					'tagCloudBgColor' => array(
						'label' => __('Background color', 'wgtd'),
						'tag' => 'input',
						'default' => 'rgba(180,180,180,1)',
						'attr' => array(
							'type' => 'color',
						),
						'options' => array(
							'showAlpha' => true
						),
						'text' => __('Filter button', 'wgtd'),
					),
				],
			],
			'thumbnailsSettings'   => [
				'label'  => __( 'Thumbnails Settings', 'wgtd' ),
				'fields' => [
					'collectionThumbHoverColor'        => array(
						'label'   => __( 'Hover color', 'wgtd' ),
						'tag'     => 'input',
						'default' => 'rgba(0,0,0,0.5)',
						'options' => array(
							'showAlpha' => true,
						),
						'attr'    => array(
							'type' => 'color',
						),
					),
					'collectionThumbContentBGColor'        => array(
						'label'   => __( 'Description bar background color', 'wgtd' ),
						'tag'     => 'input',
						'default' => 'rgba(220,220,220,1)',
						'options' => array(
							'showAlpha' => true,
						),
						'attr'    => array(
							'type' => 'color',
						),
					),
					'collectionThumbTitleShow'         => array(
						'label'   => __( 'Title', 'wgtd' ),
						'tag'     => 'checkbox',
						'default' => 1,
					),
					'collectionThumbTitleColor' => array(
						'label'   => __( 'Title Text color', 'wgtd' ),
						'tag'     => 'input',
						'default' => 'rgba(0,0,0,1)',
						'visible' => 'collectionThumbTitleShow',
						'options' => array(
							'showAlpha' => true,
						),
						'attr'    => array(
							'type' => 'color',
						),
					),
					'collectionThumbFontSize'  => array(
						'label'   => __( 'Title font size', 'wgtd' ),
						'tag'     => 'input',
						'default' => 18,
						'visible' => 'collectionThumbTitleShow',
						'attr'    => array(
							'type' => 'number',
							'min'  => 10,
							'max'  => 36,
						),
					),
					'collectionThumbDescriptionShow'         => array(
						'label'   => __( 'Item Description', 'wgtd' ),
						'tag'     => 'checkbox',
						'default' => 1,
					),
					'collectionThumbDescriptionColor' => array(
						'label'   => __( 'Description Text color', 'wgtd' ),
						'tag'     => 'input',
						'default' => 'rgba(0,0,0,1)',
						'visible' => 'collectionThumbDescriptionShow',
						'options' => array(
							'showAlpha' => true,
						),
						'attr'    => array(
							'type' => 'color',
						),
					),
					'collectionThumbDescriptionFontSize'  => array(
						'label'   => __( 'Description font size', 'wgtd' ),
						'tag'     => 'input',
						'default' => 15,
						'visible' => 'collectionThumbDescriptionShow',
						'attr'    => array(
							'type' => 'number',
							'min'  => 10,
							'max'  => 36,
						),
					),
					'collectionReadMoreButtonLabel' => array(
						'label' => __('Read More button - Default Label Text', 'wgtd'),
						'tag'   => 'input',
						'default' => 'Read More',
						'attr'    => array(
							'type' => 'text',
						)
					),
					'collectionReadMoreButtonBGColor' => array(
						'label'   => __( 'Read More button color', 'wgtd' ),
						'tag'     => 'input',
						'default' => 'rgba(0,0,0,1)',
						'options' => array(
							'showAlpha' => true,
						),
						'attr'    => array(
							'type' => 'color',
						),
					),
					'collectionReadMoreButtonBGColorHover' => array(
						'label'   => __( 'Read More button Hover color', 'wgtd' ),
						'tag'     => 'input',
						'default' => 'rgba(180,180,180,1)',
						'options' => array(
							'showAlpha' => true,
						),
						'attr'    => array(
							'type' => 'color',
						),
					),
					'collectionReadMoreButtonLabelColor' => array(
						'label'   => __( 'Read More button Label color', 'wgtd' ),
						'tag'     => 'input',
						'default' => 'rgba(255,255,255,1)',
						'options' => array(
							'showAlpha' => true,
						),
						'attr'    => array(
							'type' => 'color',
						),
					),
					'collectionReadMoreButtonLabelColorHover' => array(
						'label'   => __( 'Read More button Label Hover color', 'wgtd' ),
						'tag'     => 'input',
						'default' => 'rgba(0,0,0,1)',
						'options' => array(
							'showAlpha' => true,
						),
						'attr'    => array(
							'type' => 'color',
						),
					),
				],
			],
			'modalSettings'   => [
				'label'  => __( 'Modal Window Settings (Item Info Bar)', 'wgtd' ),
				'fields' => [
					'modaBgColor' => array(
						'label' => __('Overlap Color', 'wgtd'),
						'tag' => 'input',
						'default' => 'rgba(0,0,0,0.9)',
						'attr' => array(
							'type' => 'color',
						),
						'options' => array(
							'showAlpha' => true
						)
					),
					'modalInfoBoxBgColor' => array(
						'label' => __('Info Bar Color', 'wgtd'),
						'tag' => 'input',
						'default' => 'rgba(255,255,255,1)',
						'attr' => array(
							'type' => 'color',
						),
						'options' => array(
							'showAlpha' => true
						)
					),
					'modalInfoBoxTitleTextColor' => array(
						'label' => __('Info Bar Title text Color', 'wgtd'),
						'tag' => 'input',
						'default' => 'rgba(0,0,0,1)',
						'attr' => array(
							'type' => 'color',
						),
						'options' => array(
							'showAlpha' => true
						)
					),
					'modalInfoBoxTextColor' => array(
						'label' => __('Info Bar Text Color', 'wgtd'),
						'tag' => 'input',
						'default' => 'rgba(90,90,90,1)',
						'attr' => array(
							'type' => 'color',
						),
						'options' => array(
							'showAlpha' => true
						)
					),
					'infoBarExifEnable' => array(
						'label' => __('Show Item EXIF Data', 'wgtd'),
						'tag' => 'checkbox',
						'default' => 1,
					),
					'infoBarDateInfoEnable' => array(
						'label' => __('Show Item Upload Date', 'wgtd'),
						'tag' => 'checkbox',
						'default' => 1,
					)
				]
			],
			'lightboxSettings'   => [
				'label'  => __( 'Lightbox Settings', 'wgtd' ),
				'fields' => [
					'lightBoxEnable' => array(
						'label' => __('Lightbox', 'wgtd'),
						'tag' => 'checkbox',
						'default' => 1,
						'text' => __('Show the item in the Lightbox by clicking on the thumbnail', 'wgtd'),
					),
					'copyR_Protection' => array(
						'label' => __('Enable Download Protection', 'wgtd'),
						'tag' => 'checkbox',
						'default' => 1,
						'text' => __('Disable right click to protect content from download', 'wgtd'),
					),
					'copyR_Alert' => array(
						'label' => __('Copyright Alert (right mouse click)', 'wgtd'),
						'tag'   => 'input',
						'default' => 'Hello, this photo is mine!',
						'text'    => __('Alert about the ban on downloading photo', 'wgtd'),
						'attr'    => array(
							'type' => 'This message is displayed when a visitor clicks the right mouse button on a photo in a lightbox',
						)
					),
					'sliderScrollNavi' => array(
						'label' => __('Scroll to navigate (mouse wheel)', 'wgtd'),
						'tag' => 'checkbox',
						'default' => 1,
						'text' => __('Using this disable mouse wheel scaling!', 'wgtd'),
					),
					'sliderNextPrevAnimation'       => array(
						'label'   => __( 'Items Transition Type', 'woowbox' ),
						'tag'     => 'select',
						'default' => 'animation',
						'options' => array(
							array(
								'name'  => 'Slipping',
								'value' => 'animation',
							),
							array(
								'name'  => 'Fading',
								'value' => 'fading',
							),
						),
					),
					'sliderBgColor' => array(
						'label' => __('Lightbox  background color', 'wgtd'),
						'tag' => 'input',
						'default' => 'rgba(0,0,0,0.9)',
						'attr' => array(
							'type' => 'color',
						),
						'options' => array(
							'showAlpha' => true
						),
						'text' => __('Set the background color for lightbox', 'wgtd'),
					),
					'sliderPreloaderColor' => array(
						'label' => __('Preloader Color', 'wgtd'),
						'tag' => 'input',
						'default' => 'rgba(255,255,255,1)',
						'attr' => array(
							'type' => 'color',
						),
						'options' => array(
							'showAlpha' => true
						),
						'text' => __('Set custom color for gallery', 'wgtd'),
					),
					'sliderHeaderFooterBgColor' => array(
						'label' => __('Lightbox Header & Footer color', 'wgtd'),
						'tag' => 'input',
						'default' => 'rgba(0,0,0,0.4)',
						'options' => array(
							'showAlpha' => true
						),
						'attr' => array(
							'type' => 'color',
						),
						'text' => __('Set the background color for header and footer (gradient)', 'wgtd'),
					),
					'sliderNavigationColor' => array(
						'label' => __('Main Controls Color', 'wgtd'),
						'tag' => 'input',
						'default' => 'rgba(0,0,0,1)',
						'attr' => array(
							'type' => 'color',
						),
						'options' => array(
							'showAlpha' => true
						),
						'text' => __('Buttons Background Color', 'wgtd'),
					),
					'sliderNavigationColorOver' => array(
						'label' => __('Main Controls Color (over)', 'wgtd'),
						'tag' => 'input',
						'default' => 'rgba(255,255,255,1)',
						'attr' => array(
							'type' => 'color',
						),
						'options' => array(
							'showAlpha' => true
						),
					),
					'sliderNavigationIconColor' => array(
						'label' => __('Main Controls Icon Color', 'wgtd'),
						'tag' => 'input',
						'default' => 'rgba(255,255,255,1)',
						'attr' => array(
							'type' => 'color',
						),
						'options' => array(
							'showAlpha' => true
						),
						'text' => __('Icon Color', 'wgtd'),
					),
					'sliderNavigationIconColorOver' => array(
						'label' => __('Main Controls Icon Color (over)', 'wgtd'),
						'tag' => 'input',
						'default' => 'rgba(0,0,0,1)',
						'attr' => array(
							'type' => 'color',
						),
						'options' => array(
							'showAlpha' => true
						),
					),
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
					'sliderItemTitleEnable' => array(
						'label' => __('Show Title', 'wgtd'),
						'tag' => 'checkbox',
						'default' => 1,
					),
					'sliderItemTitleFontSize' => array(
						'label' => __('Item Title - font size', 'wgtd'),
						'visible' => 'sliderItemTitleEnable == "1"',
						'tag' => 'input',
						'default' => 18,
						'attr' => array(
							'type' => 'number',
							'min' => 18,
							'max' => 36,
						),
					),
					'sliderItemTitleTextColor' => array(
						'label' => __('Item Title - text color', 'wgtd'),
						'visible' => 'sliderItemTitleEnable == "1"',
						'tag' => 'input',
						'default' => 'rgba(255,255,255,1)',
						'attr' => array(
							'type' => 'color',
						),
						'options' => array(
							'showAlpha' => true
						)
					),
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
					'sliderThumbBarEnable' => array(
						'label' => __('Show Thumbnails Bar', 'wgtd'),
						'tag' => 'checkbox',
						'default' => 1,
						//'premium' => 1
					),
					'sliderThumbBarHoverColor' => array(
						'label' => __('Thumbnails Border Color (select mode)', 'wgtd'),
						'visible' => 'sliderThumbBarEnable == "1"',
						'tag' => 'input',
						'default' => 'rgba(255,255,255,1)',
						//'premium' => 1,
						'attr' => array(
							'type' => 'color',
						),
						'options' => array(
							'showAlpha' => true
						)
					),
					'sliderPlayButton' => array(
						'label' => __('Show Slideshow Button', 'wgtd'),
						'tag' => 'checkbox',
						'default' => 1
					),
					'slideshowDelay' => array(
						'label' => __('Slideshows Timer', 'wgtd'),
						'visible' => 'sliderPlayButton == "1"',
						'tag' => 'input',
						'default' => 8,
						'attr' => array(
							'type' => 'number',
							'min' => 2,
							'max' => 30,
						),
					),
					'slideshowProgressBarColor' => array(
						'label' => __('Slideshow progress bar color', 'wgtd'),
						'visible' => 'sliderPlayButton == "1"',
						'tag' => 'input',
						'default' => 'rgba(255,255,255,1)',
						'attr' => array(
							'type' => 'color',
						),
						'options' => array(
							'showAlpha' => true
						)
					),
					'slideshowProgressBarBGColor' => array(
						'label' => __('Slideshow progress bar Background color', 'wgtd'),
						'visible' => 'sliderPlayButton == "1"',
						'tag' => 'input',
						'default' => 'rgba(255,255,255,0.6)',
						'attr' => array(
							'type' => 'color',
						),
						'options' => array(
							'showAlpha' => true
						)
					),
					'sliderZoomButton' => array(
						'label' => __('Enable Zooom ', 'wgtd'),
						'tag' => 'checkbox',
						'default' => 1
					),
					'sliderInfoEnable' => array(
						'label' => __('Show Info Button', 'wgtd'),
						'tag' => 'checkbox',
						'default' => 1,
						'text' => __('Enable description bar for item', 'wgtd'),
						//'premium' => 1
					),
					'sliderSocialShareEnabled' => array(
						'label' => __('Show Share Buttons', 'wgtd'),
						'tag' => 'checkbox',
						'default' => 1,
						//'premium' => 1
					),
					'sliderItemDownload' => array(
						'label' => __('Show Download Button', 'wgtd'),
						'tag' => 'checkbox',
						'default' => 1,
						'text' => __('Download original file', 'wgtd'),
						//'premium' => 1
					),
					'sliderFullScreen' => array(
						'label' => __('FullScreen Button Show', 'wgtd'),
						'tag' => 'checkbox',
						'default' => 1,
						//'premium' => 1
					),
					// 'sliderItemDiscuss' => array(
					// 	'label' => __('Show Comments Button', 'wgtd'),
					// 	'tag' => 'checkbox',
					// 	'default' => 1,
					// 	'premium' => 1
					// ),
					'sliderThumbSubMenuBackgroundColor' => array(
						'label' => __('Submenu button color', 'wgtd'),
						'tag' => 'input',
						'default' => 'rgba(0, 0, 0, 0)',
						'options' => array(
							'showAlpha' => true
						),
						'attr' => array(
							'type' => 'color',
						),
					),
					'sliderThumbSubMenuIconColor' => array(
						'label' => __('Submenu button Icon color', 'wgtd'),
						'tag' => 'input',
						'default' => 'rgba(255, 255, 255, 1)',
						'options' => array(
							'showAlpha' => true
						),
						'attr' => array(
							'type' => 'color',
						),
					),
					'sliderThumbSubMenuBackgroundColorOver' => array(
						'label' => __('Submenu button Hover color', 'wgtd'),
						'tag' => 'input',
						'default' => 'rgba(255, 255, 255, 1)',
						'options' => array(
							'showAlpha' => true
						),
						'attr' => array(
							'type' => 'color',
						),
					),
					'sliderThumbSubMenuIconHoverColor' => array(
						'label' => __('Submenu button Icon Hover color', 'wgtd'),
						'tag' => 'input',
						'default' => 'rgba(0, 0, 0, 1)',
						'options' => array(
							'showAlpha' => true
						),
						'attr' => array(
							'type' => 'color',
						),
					),
				]
			]
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
