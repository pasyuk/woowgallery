<?php
/**
 * SplitSlider Skin
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

namespace WoowGallery\Skins;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );
if ( ! class_exists( 'WoowGallery\Skins\SplitSlider' ) ) {
	return;
}

/**
 * Class SplitSlider
 */
class SplitSlider {

	const NAME        = 'Split Slider';
	const SLUG        = 'splitslider';
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
			'scripts'     => [ plugins_url( 'assets/woowgallery-splitslider.js', __FILE__ ) ],
			'dependecies' => [ 'vuejs' ],
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
		<woowgallery-splitslider appid="<?php echo esc_attr( $gallery['uid'] ); ?>" class="woowgallery-block">
			<script type="application/json" class="wg-json-content"><?php echo wp_json_encode( $gallery['content'] ); ?></script>
			<script type="application/json" class="wg-json-settings"><?php echo wp_json_encode( $gallery['skin']['config'] ); ?></script>
		</woowgallery-splitslider>
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

				'label'  => __( 'Layout Settings', 'woowgallery' ),
				'fields' => [
					'ratio_wrap'        => [
						'label'  => __( 'Ratio (width / height)', 'woowgallery' ),
						'tag'    => 'flexbox',
						'fields' => [
							'base_width'  => [
								'label'   => __( 'Ratio Width', 'woowgallery' ),
								'tag'     => 'input',
								'default' => 2,
								'attr'    => [
									'type' => 'number',
									'min'  => 1,
								],
							],
							'base_height' => [
								'label'   => __( 'Ratio Height', 'woowgallery' ),
								'tag'     => 'input',
								'default' => 1,
								'attr'    => [
									'type' => 'number',
									'min'  => 1,
								],
							],
						],
						'help'  => __( 'Background CSS in short format', 'woowgallery' ),
						'text'  => __( 'Background CSS in short format: <strong>url(\'https://my.site/image-url.jpg\') center center / cover no-repeat</strong>', 'woowgallery' ),
					],
					'min_height_wrap'   => [
						'label'  => __( 'Minimum Height', 'woowgallery' ),
						'tag'    => 'flexbox',
						'fields' => [
							'min_height'           => [
								'label'   => '',
								'tag'     => 'input',
								'default' => 250,
								'attr'    => [
									'type' => 'number',
								],
							],
							'min_height_dimension' => [
								'label'   => '',
								'tag'     => 'select',
								'default' => 'px',
								'styles'  => [
									'flex' => 0,
								],
								'options' => [
									[
										'name'  => 'px',
										'value' => 'px',
									],
									[
										'name'  => 'em',
										'value' => 'em',
									],
									[
										'name'  => '%',
										'value' => '%',
									],
									[
										'name'  => 'vh',
										'value' => 'vh',
									],
								],
							],
						],
						'text'  => __( 'Background CSS in short format: <strong>url(\'https://my.site/image-url.jpg\') center center / cover no-repeat</strong>', 'woowgallery' ),
					],
					'show_title'        => [
						'label'   => __( 'Show Title', 'woowgallery' ),
						'tag'     => 'checkbox',
						'default' => 1,
					],
					'show_caption'      => [
						'label'   => __( 'Show Caption', 'woowgallery' ),
						'tag'     => 'checkbox',
						'default' => 1,
					],
					'show_link'         => [
						'label'   => __( 'Show Link Button', 'woowgallery' ),
						'tag'     => 'checkbox',
						'default' => 1,
					],
					'default_link_text' => [
						'label'   => __( 'Default Link Button Label', 'woowgallery' ),
						'tag'     => 'input',
						'default' => 'MORE',
						'visible' => 'autoplay',
						'premium' => 1,
						'text'    => __( 'Show above label if `Link Text` field is empty on image', 'woowgallery' ),
						'attr'    => [
							'type' => 'text',
						],
					],
				],
			],
			'autoplay' => [
				'label'  => __( 'Autoplay Settings', 'woowgallery' ),
				'fields' => [
					'autoplay'       => [
						'label'   => __( 'Autoplay', 'woowgallery' ),
						'tag'     => 'checkbox',
						'default' => 0,
					],
					'autoplay_delay' => [
						'label'   => __( 'Autoplay Delay', 'woowgallery' ),
						'tag'     => 'input',
						'default' => 5000,
						'visible' => 'autoplay',
						'attr'    => [
							'type' => 'number',
							'min'  => 1000,
							'step' => 100,
						],
					],
					'speed'          => [
						'label'   => __( 'Autoplay Speed', 'woowgallery' ),
						'tag'     => 'input',
						'default' => 1000,
						'visible' => 'autoplay',
						'attr'    => [
							'type' => 'number',
							'min'  => 100,
							'step' => 50,
						],
					],
				],
			],
			'colors'   => [
				'label'  => __( 'Colors', 'woowgallery' ),
				'fields' => [
					'title_color'        => [
						'label'   => __( 'Title', 'woowgallery' ),
						'tag'     => 'input',
						'default' => '#ffffff',
						'options' => [
							'showAlpha' => false,
						],
						'attr'    => [
							'type' => 'color',
						],
					],
					'caption_color'      => [
						'label'   => __( 'Caption', 'woowgallery' ),
						'tag'     => 'input',
						'default' => '#ffffff',
						'options' => [
							'showAlpha' => false,
						],
						'attr'    => [
							'type' => 'color',
						],
					],
					'button_bg'          => [
						'label'   => __( 'Button Background', 'woowgallery' ),
						'tag'     => 'input',
						'default' => '#ffffff',
						'attr'    => [
							'type' => 'color',
						],
					],
					'button_bg_hover'    => [
						'label'   => __( 'Button Hover Background', 'woowgallery' ),
						'tag'     => 'input',
						'default' => 'rgba(255,255,255,0)',
						'attr'    => [
							'type' => 'color',
						],
					],
					'button_color'       => [
						'label'   => __( 'Button', 'woowgallery' ),
						'tag'     => 'input',
						'default' => '#000000',
						'options' => [
							'showAlpha' => false,
						],
						'attr'    => [
							'type' => 'color',
						],
					],
					'button_color_hover' => [
						'label'   => __( 'Button Hover', 'woowgallery' ),
						'tag'     => 'input',
						'default' => '#ffffff',
						'options' => [
							'showAlpha' => false,
						],
						'attr'    => [
							'type' => 'color',
						],
					],
					'preloader_color'    => [
						'label'   => __( 'Preloader Color', 'woowgallery' ),
						'tag'     => 'input',
						'default' => '#000000',
						'attr'    => [
							'type' => 'color',
						],
					],
					'pagination_color'   => [
						'label'   => __( 'Pagination Color', 'woowgallery' ),
						'tag'     => 'input',
						'default' => '#ffffff',
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

new SplitSlider();
