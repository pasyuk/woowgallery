<?php
/**
 * PeGrid Skin
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

namespace WoowGallery\Skins;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );
if ( ! class_exists( 'WoowGallery\Skins\PeGrid' ) ) {
	return;
}

/**
 * Class PeGrid
 */
class PeGrid {

	const NAME        = 'Proximity Effect Grid';
	const SLUG        = 'pe-grid';
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
			'scripts'     => [ plugins_url( '../dev/pe-grid/dist/woowgallery-pe-grid.js', __FILE__ ) ],
			//'scripts'     => [ plugins_url( 'assets/woowgallery-pe-grid.js', __FILE__ ) ],
			'dependecies' => [ 'vuejs', 'jquery' ],
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
		<woowgallery-pe-grid appid="<?php echo esc_attr( $gallery['uid'] ); ?>" class="woowgallery-block">
			<script type="application/json" class="wg-json-content"><?php echo wp_json_encode( $gallery['content'] ); ?></script>
			<script type="application/json" class="wg-json-settings"><?php echo wp_json_encode( $gallery['skin']['config'] ); ?></script>
		</woowgallery-pe-grid>
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
			'common' => [

				'label'  => __( 'Layout Settings', 'wgtd' ),
				'fields' => [
					'thumb_size'          => [
						'label'  => __( 'Thumbnail Size', 'wgtd' ),
						'tag'    => 'flexbox',
						'fields' => [
							'thumb_width'  => [
								'label'   => __( 'Width', 'wgtd' ),
								'tag'     => 'input',
								'default' => 200,
								'attr'    => [
									'type' => 'number',
									'min'  => 40,
								],
							],
							'thumb_height' => [
								'label'   => __( 'Height', 'wgtd' ),
								'tag'     => 'input',
								'default' => 200,
								'attr'    => [
									'type' => 'number',
									'min'  => 40,
								],
							],
						],
					],
					'scale'               => [
						'label'  => __( 'Thumbnail Scale', 'wgtd' ),
						'tag'    => 'flexbox',
						'fields' => [
							'min_scale' => [
								'label'   => __( 'Initial Scale', 'wgtd' ),
								'tag'     => 'input',
								'default' => 0.8,
								'attr'    => [
									'type' => 'number',
									'min'  => 0,
									'max'  => 2,
									'step' => 0.1,
								],
							],
							'max_scale' => [
								'label'   => __( 'Hover Scale', 'wgtd' ),
								'tag'     => 'input',
								'default' => 1,
								'attr'    => [
									'type' => 'number',
									'min'  => 0,
									'max'  => 2,
									'step' => 0.1,
								],
							],
						],
					],
					'opacity'             => [
						'label'  => __( 'Thumbnail Opacity', 'wgtd' ),
						'tag'    => 'flexbox',
						'fields' => [
							'min_opacity' => [
								'label'   => __( 'Initial Opacity', 'wgtd' ),
								'tag'     => 'input',
								'default' => 0.8,
								'attr'    => [
									'type' => 'number',
									'min'  => 0,
									'max'  => 1,
									'step' => 0.05,
								],
							],
							'max_opacity' => [
								'label'   => __( 'Hover Opacity', 'wgtd' ),
								'tag'     => 'input',
								'default' => 1,
								'attr'    => [
									'type' => 'number',
									'min'  => 0,
									'max'  => 1,
									'step' => 0.05,
								],
							],
						],
					],
					'thumb_border_radius' => [
						'label'  => __( 'Thumbnail Border Radius', 'wgtd' ),
						'tag'    => 'flexbox',
						'fields' => [
							'border_radius'       => [
								'label'   => __( 'Initial Border Radius', 'wgtd' ),
								'tag'     => 'input',
								'default' => 0,
								'attr'    => [
									'type' => 'number',
									'min'  => 0,
								],
							],
							'border_radius_hover' => [
								'label'   => __( 'Hover Border Radius', 'wgtd' ),
								'tag'     => 'input',
								'default' => 0,
								'attr'    => [
									'type' => 'number',
									'min'  => 0,
								],
							],
						],
					],
					'grid_gap'            => [
						'label'   => __( 'Grid Gap', 'wgtd' ),
						'tag'     => 'input',
						'default' => 10,
						'attr'    => [
							'type' => 'number',
						],
					],
					'proximity_distance'  => [
						'label'   => __( 'Proximity Distance ', 'wgtd' ),
						'tag'     => 'input',
						'default' => 200,
						'attr'    => [
							'type' => 'number',
							'min'  => 0,
						],
					],
					'show_info'           => [
						'label'   => __( 'Show Information on Hover', 'wgtd' ),
						'tag'     => 'checkbox',
						'default' => 1,
					],
					'show_title'          => [
						'label'   => __( 'Show Title', 'wgtd' ),
						'tag'     => 'checkbox',
						'default' => 1,
					],
					'show_caption'        => [
						'label'   => __( 'Show Caption', 'wgtd' ),
						'tag'     => 'checkbox',
						'default' => 1,
					],
				],
			],
			'colors' => [
				'label'  => __( 'Colors', 'wgtd' ),
				'fields' => [
					'grid_bg'       => [
						'label'   => __( 'Grid Background', 'wgtd' ),
						'tag'     => 'input',
						'default' => 'rgba(255,255,255,0)',
						'attr'    => [
							'type' => 'color',
						],
					],
					'grid_bg_img'   => [
						'label' => __( 'Grid Background Image Advanced', 'wgtd' ),
						'tag'   => 'input',
						'attr'  => [
							'type'        => 'text',
							'placeholder' => 'url(\'https://my.site/image-url.jpg\')',
						],
						'text'  => __( 'Background CSS in short format: <strong>url(\'https://my.site/image-url.jpg\') center center / cover no-repeat</strong>', 'wgtd' ),
					],
					'info_bg'       => [
						'label'   => __( 'Info Background', 'wgtd' ),
						'tag'     => 'input',
						'default' => 'rgba(0,0,0,0.8)',
						'attr'    => [
							'type' => 'color',
						],
					],
					'title_color'   => [
						'label'   => __( 'Title', 'wgtd' ),
						'tag'     => 'input',
						'default' => '#ffffff',
						'options' => [
							'showAlpha' => false,
						],
						'attr'    => [
							'type' => 'color',
						],
					],
					'caption_color' => [
						'label'   => __( 'Caption', 'wgtd' ),
						'tag'     => 'input',
						'default' => '#ffffff',
						'options' => [
							'showAlpha' => false,
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

new PeGrid();
