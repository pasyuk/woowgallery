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

<div id="woowgallery-config-slug-box" class="form-group field-input">
	<label for="woowgallery-config-slug">
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
			<input id="woowgallery-config-slug" class="form-control" type="text" name="post_name" value="<?php echo esc_attr( $data['post']->post_name ); ?>"/>
		</div>
	</div>
	<div class="hint">
		<?php
		if ( Posttypes::ALBUM_POSTTYPE === $data['post']->post_type ) {
			echo wp_kses( __( '<strong>Unique</strong> album slug for identification and advanced album queries.', 'wgtd' ), 'strong' );
		} else {
			echo wp_kses( __( '<strong>Unique</strong> gallery slug for identification and advanced gallery queries.', 'wgtd' ), 'strong' );
		}
		?>
	</div>
</div>
<div id="woowgallery-config-classes-box" class="form-group field-input">
	<label for="woowgallery-config-classes">
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
			<input id="woowgallery-config-classes" class="form-control" type="text" name="_woowgallery[settings][classes]" placeholder="<?php esc_attr_e( 'Enter custom CSS classes here.', 'wgtd' ); ?>" value="<?php echo esc_attr( implode( ' ', (array) $wg->get_settings( 'classes' ) ) ); ?>"/>
		</div>
	</div>
	<div class="hint"><?php esc_html_e( 'Adds custom CSS classes. Separate classes with whitespace.', 'wgtd' ); ?></div>
</div>
<div id="woowgallery-config-description-box" class="form-group field-textarea">
	<label for="woowgallery-config-classes">
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
