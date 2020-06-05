<?php
/**
 * Outputs the Gallery Code Metabox Content.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

use WoowGallery\Posttypes;

/**
 * Template vars
 *
 * @var array $data
 */
?>
<p><?php echo wp_kses( __( 'Use <strong>one</strong> of the shortcodes below to place this gallery anywhere into your posts, pages, custom post types or widgets:', 'woowgallery' ), '' ); ?></p>
<div class="woowgallery-code">
	<code id="woowgallery_shortcode_id_<?php echo absint( $data['post']->ID ); ?>"><?php echo '[' . esc_html( $data['post']->post_type ) . ' id="' . absint( $data['post']->ID ) . '"]'; ?></code>
	<a href="#" title="<?php esc_attr_e( 'Copy Shortcode to Clipboard', 'woowgallery' ); ?>" data-clipboard-target="#woowgallery_shortcode_id_<?php echo absint( $data['post']->ID ); ?>" class="dashicons dashicons-clipboard woowgallery-clipboard">
		<span><?php esc_html_e( 'Copy to Clipboard', 'woowgallery' ); ?></span>
	</a>
</div>

<div class="woowgallery-code">
	<code id="woowgallery_shortcode_slug_<?php echo absint( $data['post']->ID ); ?>"><?php echo '[' . esc_html( $data['post']->post_type ) . ' id="' . esc_html( $data['post']->post_name ) . '"]'; ?></code>
	<a href="#" title="<?php esc_attr_e( 'Copy Shortcode to Clipboard', 'woowgallery' ); ?>" data-clipboard-target="#woowgallery_shortcode_slug_<?php echo absint( $data['post']->ID ); ?>" class="dashicons dashicons-clipboard woowgallery-clipboard">
		<span><?php esc_html_e( 'Copy to Clipboard', 'woowgallery' ); ?></span>
	</a>
</div>

<p><?php echo wp_kses( __( 'You can also place this gallery into your template files by using <strong>one</strong> of the template tag(s) below:', 'woowgallery' ), '' ); ?></p>
<div class="woowgallery-code">
	<code id="woowgallery_template_tag_id_<?php echo absint( $data['post']->ID ); ?>"><?php echo 'if ( function_exists( \'woowgallery\' ) ) { woowgallery( \'' . absint( $data['post']->ID ) . '\', \'' . esc_html( $data['post']->post_type ) . '\' ); }'; ?></code>
	<a href="#" title="<?php esc_attr_e( 'Copy Template Tag to Clipboard', 'woowgallery' ); ?>" data-clipboard-target="#woowgallery_template_tag_id_<?php echo absint( $data['post']->ID ); ?>" class="dashicons dashicons-clipboard woowgallery-clipboard">
		<span><?php esc_html_e( 'Copy to Clipboard', 'woowgallery' ); ?></span>
	</a>
</div>

<div class="woowgallery-code">
	<code id="woowgallery_template_tag_slug_<?php echo absint( $data['post']->ID ); ?>"><?php echo 'if ( function_exists( \'woowgallery\' ) ) { woowgallery( \'' . esc_html( $data['post']->post_name ) . '\', \'' . esc_html( $data['post']->post_type ) . '\' ); }'; ?></code>
	<a href="#" title="<?php esc_attr_e( 'Copy Template Tag to Clipboard', 'woowgallery' ); ?>" data-clipboard-target="#woowgallery_template_tag_slug_<?php echo absint( $data['post']->ID ); ?>" class="dashicons dashicons-clipboard woowgallery-clipboard">
		<span><?php esc_html_e( 'Copy to Clipboard', 'woowgallery' ); ?></span>
	</a>
</div>
