<?php
/**
 * Outputs the Settings panel.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

/**
 * Template vars
 *
 * @var array $data
 */

$settings = $data['settings'];
$skins    = $data['skins'];
?>
<form method="post" style="padding-top: 20px;">
	<h1><?php esc_html_e( 'General Settings', 'wgtd' ); ?></h1>
	<div class="postbox">
		<div class="inside">

			<?php

			/*
			<div class="form-group field-input license-field <?php echo $settings['license'] ? 'license-active' : 'license-inactive'; ?>">
				<label for="woowgallery-license"><?php esc_html_e( 'License Key', 'wgtd' ); ?></label>
				<div class="field-wrap with-button">
					<div class="wrapper">
						<input name="settings[license]" type="text" class="form-control" id="woowgallery-license" value="<?php echo esc_attr( $settings['license'] ); ?>" <?php echo $settings['license'] ? 'readonly' : ''; ?>/>
					</div>
					<div class="field-button">
						<button type="button" class="woowgallery-license-action-button button button-primary" data-action="activate"><?php esc_html_e( 'Activate', 'wgtd' ); ?></button>
						<button type="button" class="woowgallery-license-action-button button button-danger" data-action="deactivate"><?php esc_html_e( 'Deactivate', 'wgtd' ); ?></button>
					</div>
				</div>
			</div>
			*/
			?>

			<div class="form-group field-input">
				<label for="woowgallery-default-skin"><?php esc_html_e( 'Default skin', 'wgtd' ); ?></label>
				<div class="field-wrap">
					<div class="wrapper">
						<select name="settings[default_skin]" id="woowgallery-default-skin" class="form-control">
							<!-- <option value=""<?php selected( $settings['default_skin'], '' ); ?>><?php esc_html_e( 'None', 'wgtd' ); ?></option> -->
							<?php
							// Iterate through the available skins, outputting them in a list.
							foreach ( $skins as $slug => $skin ) {
								$info = $skin->info;
								?>
								<option value="<?php echo esc_attr( $slug ); ?>"<?php selected( $settings['default_skin'], $slug ); ?>><?php echo esc_html( $info['name'] ); ?></option>
								<?php
								foreach ( $skin->model as $preset_name => $preset_data ) {
									if ( 'default' === $preset_name ) {
										continue;
									}
									$value = $slug . ': ' . $preset_name;
									?>
									<option value="<?php echo esc_attr( $value ); ?>"<?php selected( $settings['default_skin'], $value ); ?>><?php echo esc_html( $info['name'] . ': ' . $preset_name ); ?></option>
									<?php
								}
							}
							?>
						</select>
					</div>
				</div>
				<div class="hint"><?php esc_html_e( 'Select default skin for your galleries. You can also config skins/presets below for faster gallery creation.', 'wgtd' ); ?></div>
			</div>

			<div class="form-group field-select">
				<label for="woowgallery-edit-view"><?php esc_html_e( 'Default Media View', 'wgtd' ); ?></label>
				<div class="field-wrap">
					<div class="wrapper">
						<select name="settings[edit_gallery_view]" id="woowgallery-edit-view" class="form-control">
							<option value="grid" <?php selected( $settings['edit_gallery_view'], 'grid' ); ?>><?php esc_attr_e( 'Grid', 'wgtd' ); ?></option>
							<option value="list" <?php selected( $settings['edit_gallery_view'], 'list' ); ?>><?php esc_attr_e( 'List', 'wgtd' ); ?></option>
						</select>
					</div>
				</div>
				<div class="hint"><?php esc_html_e( 'Select default view for media while editing the gallery.', 'wgtd' ); ?></div>
			</div>

			<div class="form-group field-select">
				<label for="woowgallery-selection-prepend"><?php esc_html_e( 'Adding New Media', 'wgtd' ); ?></label>
				<div class="field-wrap">
					<div class="wrapper">
						<select name="settings[selection_prepend]" id="woowgallery-selection-prepend" class="form-control">
							<option value="1" <?php selected( $settings['selection_prepend'], '1' ); ?>><?php esc_attr_e( 'Prepend', 'wgtd' ); ?></option>
							<option value="0" <?php selected( $settings['selection_prepend'], '0' ); ?>><?php esc_attr_e( 'Append', 'wgtd' ); ?></option>
						</select>
					</div>
				</div>
				<div class="hint"><?php esc_html_e( 'Choose where to add new media in your gallery.', 'wgtd' ); ?></div>
			</div>

			<div class="form-group field-textarea">
				<label for="woowgallery-custom-css"><?php esc_html_e( 'Custom CSS', 'wgtd' ); ?></label>
				<div class="field-wrap">
					<div class="wrapper" style="max-width: 800px;">
						<textarea name="settings[custom_css]" id="woowgallery-custom-css" class="form-control" rows="10" cols="60"><?php echo esc_textarea( stripslashes( $settings['custom_css'] ) ); ?></textarea>
					</div>
				</div>
			</div>

			<div class="alignright">
				<button type="submit" name="woowgallery-settings-reset" class="button button-secondary" data-confirm="<?php esc_attr_e( 'This will reset plugin\'s settings and delete all skins presets.' ); ?>"><?php esc_html_e( 'Reset Plugin', 'wgtd' ); ?></button>
				&nbsp;
				<button type="submit" name="woowgallery-settings-submit" class="button button-primary"><?php esc_html_e( 'Save', 'wgtd' ); ?></button>
			</div>

		</div>
	</div>
	<?php
	wp_nonce_field( 'settings_save', '_nonce_woowgallery_settings_save', false );
	?>
</form>
