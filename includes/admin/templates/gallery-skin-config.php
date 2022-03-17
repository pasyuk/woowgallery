<?php
/**
 * Outputs the Gallery Template Config.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

use WoowGallery\Admin\Admin;

?>
	<div id="woowgallery-skin-config">
		<!-- Skin Settings -->
		<?php Admin::load_template( 'skin-settings' ); ?>
	</div>

<?php
