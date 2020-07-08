<?php
/**
 * Elementor class.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

namespace WoowGallery\Admin;

use WoowGallery\Skins;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * Class Elementor
 */
class Elementor {

	/**
	 * Primary class constructor.
	 */
	public function __construct() {

		add_action( 'elementor/preview/enqueue_styles', [ $this, 'elementor_before_enqueue_styles' ] );
		add_action( 'elementor/preview/enqueue_scripts', [ $this, 'elementor_before_enqueue_scripts' ] );

		add_action(
			'elementor/widget/before_render_content',
			function ( $widget ) {
				if ( \Elementor\Plugin::$instance->editor->is_edit_mode() && 'wp-widget-woowgallery' === $widget->get_name() ) {
					$settings = $widget->get_settings();

					if ( ! empty( $settings['wp']['woowgallery'] ) ) {
						add_filter( 'woowgallery_shortcode', [ $this, 'load_skin_assets' ], 10, 2 );
					}
				}
			}
		);

		add_filter(
			'elementor/document/config',
			function ( $add_config ) {
				$add_config['widgets']['wp-widget-woowgallery']['icon']          = 'eicon-gallery-masonry';
				$add_config['widgets']['wp-widget-woowgallery']['categories'][0] = 'general';

				return $add_config;
			}
		);
	}

	/**
	 * Enqueue styles
	 */
	public function elementor_before_enqueue_styles() {
		wp_enqueue_style( WOOWGALLERY_SLUG . '-style' );
	}

	/**
	 * Enqueue scripts
	 */
	public function elementor_before_enqueue_scripts() {
		wp_enqueue_script( WOOWGALLERY_SLUG . '-elementor' );
		wp_enqueue_script( WOOWGALLERY_SLUG . '-script' );
	}

	/**
	 * Load skin assets
	 *
	 * @param string $shortcode_html Shortcode HTML.
	 * @param array  $gallery        Gallery data.
	 *
	 * @return string
	 */
	public function load_skin_assets( $shortcode_html, $gallery ) {
		$skin          = Skins::get_instance()->get_skin( $gallery['skin']['slug'] );
		$id            = sanitize_html_class( $gallery['uid'] );
		$skin_slug     = esc_attr( $skin->slug );
		$lightbox_slug = esc_attr( $gallery['lightbox']['slug'] );

		$init_script = '<s' . 'cript>';
		$init_script .= 'jQuery(function($){';
		$init_script .= "initWoowGallerySkin('{$id}','{$skin_slug}','{$lightbox_slug}');";
		$init_script .= '});';
		$init_script .= '</s' . 'cript>';

		return $shortcode_html . $init_script;
	}
}
