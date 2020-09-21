<?php
/**
 * Single Product Image
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/product-image.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.5.1
 */

use WoowGallery\Gallery;
use WoowGallery\Shortcodes;

defined( 'ABSPATH' ) || exit;

// Note: `wc_get_gallery_image_html` was added in WC 3.3.2 and did not exist prior. This check protects against theme overrides being used on older versions of WC.
if ( ! function_exists( 'wc_get_gallery_image_html' ) ) {
	return;
}

/**
 * @var WC_Product $product
 */
global $product;

$post_thumbnail_id = $product->get_image_id();
if ( $post_thumbnail_id ) {

	$gallery_img_ids = [ $post_thumbnail_id ];
	$attachment_ids  = $product->get_gallery_image_ids();
	if ( $attachment_ids ) {
		$gallery_img_ids = array_merge( $gallery_img_ids, $attachment_ids );
	}

	$gallery_data    = [];
	$gallery_content = [];
	foreach ( $gallery_img_ids as $attachment_id ) {
		$attachment_data      = woowgallery_prepare_post_data( $attachment_id );
		$attachment_full_data = woowgallery_full_post_data( $attachment_data );
		if ( ! empty( $attachment_full_data ) ) {
			$gallery_data[]    = $attachment_data;
			$gallery_content[] = $attachment_full_data;
		}
	}

	$product_posttype = get_post_type( $product->get_id() );
	$wg               = Gallery::get_instance( $product->get_id(), $product_posttype );
	$gallery          = $wg->get_gallery_artificiall( $gallery_data, $gallery_content );

	$wg_sc = Shortcodes::get_instance();

	$wrapper_classes   = apply_filters(
		'woocommerce_single_product_image_gallery_classes',
		[
			'woocommerce-product-gallery',
			'woocommerce-product-gallery--with-images',
			'images',
		]
	);
	$wrapper_classes[] = 'wg-images';
	?>

	<div class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $wrapper_classes ) ) ); ?>">
		<?php echo $wg_sc->shortcode( [ 'gallery' => $gallery ], $product_posttype ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped ?>
	</div>

	<?php
	// Else return default WC template.
} else {

	$columns         = apply_filters( 'woocommerce_product_thumbnails_columns', 4 );
	$wrapper_classes = apply_filters(
		'woocommerce_single_product_image_gallery_classes',
		[
			'woocommerce-product-gallery',
			'woocommerce-product-gallery--' . ( $product->get_image_id() ? 'with-images' : 'without-images' ),
			'woocommerce-product-gallery--columns-' . absint( $columns ),
			'images',
		]
	);
	?>
	<div class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $wrapper_classes ) ) ); ?>" data-columns="<?php echo esc_attr( $columns ); ?>" style="opacity: 0; transition: opacity .25s ease-in-out;">
		<figure class="woocommerce-product-gallery__wrapper">
			<?php
			if ( $product->get_image_id() ) {
				$html = wc_get_gallery_image_html( $post_thumbnail_id, true );
			} else {
				$html = '<div class="woocommerce-product-gallery__image--placeholder">';
				$html .= sprintf( '<img src="%s" alt="%s" class="wp-post-image" />', esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ), esc_html__( 'Awaiting product image', 'woocommerce' ) );
				$html .= '</div>';
			}

			echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', $html, $post_thumbnail_id ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped

			do_action( 'woocommerce_product_thumbnails' );
			?>
		</figure>
	</div>
	<?php
}
