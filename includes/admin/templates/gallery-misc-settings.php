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

$wg = new Gallery( $data['post']->ID, $data['post']->post_type );
?>
<div class="woowgallery-intro">
	<h3><?php esc_html_e( 'Miscellaneous Settings', 'wgtd' ); ?></h3>
	<p>
		<?php
		if ( Posttypes::ALBUM_POSTTYPE === $data['post']->post_type ) {
			esc_html_e( 'The settings below adjust miscellaneous options for the Album.', 'wgtd' );
		} else {
			esc_html_e( 'The settings below adjust miscellaneous options for the Gallery.', 'wgtd' );
		}
		?>
	</p>
</div>

<div id="wg-config-slug-box" class="form-group field-input">
	<label for="post_name">
		<?php
		if ( Posttypes::ALBUM_POSTTYPE === $data['post']->post_type ) {
			esc_html_e( 'Album Slug', 'wgtd' );
		} else {
			esc_html_e( 'Gallery Slug', 'wgtd' );
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
			echo wp_kses( __( '<strong>Unique</strong> album slug for identification and advanced album queries.', 'wgtd' ), '' );
		} else {
			echo wp_kses( __( '<strong>Unique</strong> gallery slug for identification and advanced gallery queries.', 'wgtd' ), '' );
		}
		?>
	</div>
</div>
<div id="wg-config-classes-box" class="form-group field-input">
	<label for="wg-config-classes">
		<?php
		if ( Posttypes::ALBUM_POSTTYPE === $data['post']->post_type ) {
			esc_html_e( 'Custom Album Classes', 'wgtd' );
		} else {
			esc_html_e( 'Custom Gallery Classes', 'wgtd' );
		}
		?>
	</label>
	<div class="field-wrap">
		<div class="wrapper">
			<input id="wg-config-classes" class="form-control" type="text" name="_woowgallery[settings][classes]" placeholder="<?php esc_attr_e( 'Enter custom CSS classes here.', 'wgtd' ); ?>" value="<?php echo esc_attr( implode( ' ', (array) $wg->get_settings( 'classes' ) ) ); ?>"/>
		</div>
	</div>
	<div class="hint"><?php esc_html_e( 'Adds custom CSS classes. Separate classes with whitespace.', 'wgtd' ); ?></div>
</div>
<div id="wg-config-custom-css-box" class="form-group field-textarea">
	<label for="wg-custom-css"><?php esc_html_e( 'Custom CSS', 'wgtd' ); ?></label>
	<div class="field-wrap">
		<div class="wrapper" style="max-width: 800px;">
			<textarea name="_woowgallery[settings][custom_css]" id="wg-custom-css" class="form-control" rows="10" cols="60"><?php echo esc_textarea( stripslashes( $wg->get_settings( 'custom_css' ) ) ); ?></textarea>
		</div>
	</div>
	<div class="hint"><code>.wg-id-<?php echo (int) $data['post']->ID; ?></code> - <?php echo wp_kses( __( 'use this classname or any of <strong>`Custom Gallery Classes`</strong> for each styles you added. It is the main WoowGallery wrapper.', 'wgtd' ), '' ); ?></div>
</div>

<div id="wg-config-description-box" class="form-group field-textarea">
	<label for="wg-config-classes">
		<?php
		if ( Posttypes::ALBUM_POSTTYPE === $data['post']->post_type ) {
			esc_html_e( 'Album Description', 'wgtd' );
		} else {
			esc_html_e( 'Gallery Description', 'wgtd' );
		}
		?>
	</label>
	<div class="field-wrap">
		<div class="wrapper" style="max-width: 800px">
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
