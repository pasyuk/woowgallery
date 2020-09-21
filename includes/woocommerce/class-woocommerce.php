<?php
/**
 * Woocommerce class.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

namespace WoowGallery\Woocommerce;

use WoowGallery\Admin\Settings;
use WP_Post;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * Class Woocommerce.
 */
class Woocommerce {

	/**
	 * Woocommerce constructor.
	 */
	public function __construct() {

		//add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ], 30 );
		//add_action( 'woocommerce_process_product_meta', [ $this, 'metabox_save_select_skin' ], 20, 2 );

		add_filter( 'wc_get_template', [ $this, 'woowtemplate' ], 10, 5 );
	}

	/**
	 * Add WC Meta boxes.
	 */
	public function add_meta_boxes() {
		add_meta_box( 'woowgallery-wc-product-gallery', __( 'WoowGallery Skin', 'woowgallery' ), [ $this, 'metabox_select_skin' ], 'product', 'side', 'low' );
	}

	/**
	 * Metabox HTML output
	 *
	 * @param WP_Post $post
	 */
	public function metabox_select_skin( $post ) {
		?>
		<div id="woowgallery_product_gallery_skin"></div>
		<?php
	}

	/**
	 * Metabox data save
	 *
	 * @param int     $post_id
	 * @param WP_Post $post
	 */
	public function metabox_save_select_skin( $post_id, $post ) {
		?>
		<div id="woowgallery_product_gallery_skin">

			Select Skin
		</div>
		<?php
	}

	/**
	 * Filter woocommerce templates (e.g. product gallery) passing attributes and including the file.
	 *
	 * @param string $template      Absolute path to the template.
	 * @param string $template_name Template name.
	 * @param array  $args          Arguments. (default: array).
	 * @param string $template_path Template path. (default: '').
	 * @param string $default_path  Default path. (default: '').
	 *
	 * @return string
	 */
	public function woowtemplate( $template, $template_name, $args, $template_path, $default_path ) {

		if ( 'single-product/product-image.php' === $template_name ) {
			$product_gallery_enabled = (int) Settings::get_settings( 'product_gallery' );
			if ( $product_gallery_enabled ) {
				return dirname( __FILE__ ) . '/templates/product-image.php';
			}
		}

		return $template;
	}

}
