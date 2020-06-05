<?php
/**
 * Outputs the WoowGallery Header
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

/**
 * Template vars
 *
 * @var $data array
 */
?>
<div id="woowgallery-screen-meta-block"></div>
<div id="woowgallery-header" class="woowgallery-header">
	<h1 class="woowgallery-logo" id="woowgallery-logo">
		<img src="<?php echo esc_url( $data['logo'] ); ?>" alt="<?php esc_attr_e( 'WoowGallery', 'woowgallery' ); ?>" height="26" style="width:auto;"/>
		<?php esc_html_e( 'WoowGallery', 'woowgallery' ); ?>
	</h1>
</div>
