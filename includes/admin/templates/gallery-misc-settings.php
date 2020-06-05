<?php
/**
 * Outputs the Gallery Miscellaneous Settings.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

use WoowGallery\Gallery;
use WoowGallery\Posttypes;

/**
 * Template vars
 *
 * @var array $data
 */

$screen = get_current_screen();
$wg     = Gallery::get_instance( $data['post']->ID, $data['post']->post_type );
?>
<div class="woowgallery-intro">
	<h3><?php esc_html_e( 'Miscellaneous Settings', 'woowgallery' ); ?></h3>
	<p>
		<?php
		if ( Posttypes::ALBUM_POSTTYPE === $data['post']->post_type ) {
			esc_html_e( 'The settings below adjust miscellaneous options for the Album.', 'woowgallery' );
		} else {
			esc_html_e( 'The settings below adjust miscellaneous options for the Gallery.', 'woowgallery' );
		}
		?>
	</p>
</div>

<?php if ( 'add' !== $screen->action ) { ?>
	<div id="wg-config-force-update" class="form-group field-input">
		<label><?php esc_html_e( 'Force Update', 'woowgallery' ); ?></label>
		<div class="field-wrap">
			<div class="wrapper">
				<input type="hidden" name="wg_force_update" value="0"/>
				<label>
				<span class="wg-toggle">
					<input type="checkbox" id="wg-force-update" name="wg_force_update" value="1"/>
					<span class="wg-toggle__track"></span>
					<span class="wg-toggle__thumb"></span>
				</span>
				</label>
			</div>
		</div>
		<div class="hint">
			<?php esc_html_e( 'Force update gallery full media data, even if you did not change gallery items.', 'woowgallery' ); ?>
			<br/><?php esc_html_e( 'Note: this option automaticaly disables after gallery update.', 'woowgallery' ); ?>
		</div>
	</div>
<?php } ?>

<div id="wg-config-slug-box" class="form-group field-input">
	<label for="post_name">
		<?php
		if ( Posttypes::ALBUM_POSTTYPE === $data['post']->post_type ) {
			esc_html_e( 'Album Slug', 'woowgallery' );
		} else {
			esc_html_e( 'Gallery Slug', 'woowgallery' );
		}
		?>
	</label>
	<div class="field-wrap">
		<div class="wrapper">
			<input id="post_name" class="form-control" type="text" name="post_name" value="<?php echo esc_attr( $data['post']->post_name ); ?>"/>
		</div>
	</div>
	<div class="hint">
		<?php
		if ( Posttypes::ALBUM_POSTTYPE === $data['post']->post_type ) {
			echo wp_kses( __( '<strong>Unique</strong> album slug for identification and advanced album queries.', 'woowgallery' ), '' );
		} else {
			echo wp_kses( __( '<strong>Unique</strong> gallery slug for identification and advanced gallery queries.', 'woowgallery' ), '' );
		}
		?>
	</div>
</div>
<div id="wg-config-classes-box" class="form-group field-input">
	<label for="wg-config-classes">
		<?php
		if ( Posttypes::ALBUM_POSTTYPE === $data['post']->post_type ) {
			esc_html_e( 'Custom Album Classes', 'woowgallery' );
		} else {
			esc_html_e( 'Custom Gallery Classes', 'woowgallery' );
		}
		?>
	</label>
	<div class="field-wrap">
		<div class="wrapper">
			<input id="wg-config-classes" class="form-control" type="text" name="_woowgallery[settings][classes]" placeholder="<?php esc_attr_e( 'Enter custom CSS classes here.', 'woowgallery' ); ?>" value="<?php echo esc_attr( implode( ' ', (array) $wg->get_settings( 'classes' ) ) ); ?>"/>
		</div>
	</div>
	<div class="hint"><?php esc_html_e( 'Adds custom CSS classes. Separate classes with whitespace.', 'woowgallery' ); ?></div>
</div>
<div id="wg-config-custom-css-box" class="form-group field-textarea">
	<label for="wg-custom-css"><?php esc_html_e( 'Custom CSS', 'woowgallery' ); ?></label>
	<div class="field-wrap">
		<div class="wrapper" style="width: 100%; max-width: 800px;">
			<textarea name="_woowgallery[settings][custom_css]" id="wg-custom-css" class="form-control" rows="10" cols="60"><?php echo esc_textarea( stripslashes( $wg->get_settings( 'custom_css' ) ) ); ?></textarea>
			<?php woowgallery_is_premium_feature(); ?>
		</div>
	</div>
	<div class="hint"><code>.wg-id-<?php echo (int) $data['post']->ID; ?></code> - <?php echo wp_kses( __( 'use this classname or any of <strong>`Custom Gallery Classes`</strong> for each styles you added. It is the main WoowGallery wrapper.', 'woowgallery' ), '' ); ?></div>
</div>

<div id="wg-config-description-box" class="form-group field-textarea">
	<label for="wg-config-classes">
		<?php
		if ( Posttypes::ALBUM_POSTTYPE === $data['post']->post_type ) {
			esc_html_e( 'Album Description', 'woowgallery' );
		} else {
			esc_html_e( 'Gallery Description', 'woowgallery' );
		}
		?>
	</label>
	<div class="field-wrap">
		<div class="wrapper" style="width: 100%; max-width: 800px;">
			<?php
			$settings = [
				'teeny'         => true,
				'textarea_name' => 'content',
				'media_buttons' => false,
				'editor_height' => 200,
				'textarea_rows' => 10,
			];
			wp_editor( $data['post']->post_content, 'description', $settings );
			?>
		</div>
	</div>
</div>
