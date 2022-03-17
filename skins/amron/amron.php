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
	const VERSION     = '1.2.2';
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
