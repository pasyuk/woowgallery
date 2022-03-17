<?php
/**
 * Outputs the Gallery Cache Settings.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

use WoowGallery\Admin\Settings;
use WoowGallery\Gallery;

/**
 * Template vars
 *
 * @var array $data
 */

$wg = Gallery::get_instance( $data['post']->ID, $data['post']->post_type );
?>
<div class="woowgallery-intro">
	<h3><?php esc_html_e( 'Caching Settings', 'woowgallery' ); ?></h3>
</div>

<div id="woowgallery-config-slug-box" class="form-group field-input">
	<label for="woowgallery-cache-update"><?php esc_html_e( 'Update Cache every (hours)', 'woowgallery' ); ?></label>
	<div class="field-wrap">
		<div class="wrapper">
			<input id="woowgallery-cache-update" class="form-control" type="number" name="_woowgallery[settings][cache]" placeholder="<?php esc_attr_e( 'Set 0 to disable cache.', 'woowgallery' ); ?>" value="<?php echo absint( $wg->get_settings( 'cache', Settings::get_settings( 'cache' ) ) ); ?>"/>
		</div>
	</div>
	<div class="hint"><?php esc_html_e( 'Set cache time in hours for your Gallery. Set to `0` if you want to disable caching (not recommended).', 'woowgallery' ); ?></div>
</div>
