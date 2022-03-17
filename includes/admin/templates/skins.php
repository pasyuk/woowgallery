<?php
/**
 * Outputs the Skins panel.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

use WoowGallery\Admin\Admin;

/**
 * Template vars
 *
 * @var array $data
 */

if ( woow_fs()->can_use_premium_code__premium_only() ) {

	$settings = $data['settings'];
	$skins    = $data['skins'];
	?>
	<form method="post" id="woowgallery-skin-config" style="padding-top: 20px;">
		<h1><?php esc_html_e( 'Create Skins Presets', 'woowgallery' ); ?></h1>
		<h4><?php esc_html_e( 'Select skin to config it default settings and create presets.', 'woowgallery' ); ?></h4>
		<div class="postbox woowgallery-postbox">
			<div class="inside">

				<!-- Skins -->
				<div id="woowgallery-skin-select">
					<div class="woowgallery-skins">
						<?php
						// Iterate through the available skins, outputting them in a list.
						foreach ( $skins as $slug => $skin ) {
							$info = $skin->info;
							?>
							<div class="woowgallery-skin woowgallery_skin_<?php echo esc_attr( $slug ); ?>">
								<label for="woowgallery_skin_<?php echo esc_attr( $slug ); ?>">
									<input type="radio" id="woowgallery_skin_<?php echo esc_attr( $slug ); ?>" v-model="skin" name="_woowgallery_skin[skin]" value="<?php echo esc_attr( $slug ); ?>"<?php checked( $settings['default_skin'], $slug ); ?> />
									<img src="<?php echo esc_url( $info['screenshots'][0] ); ?>" alt="<?php echo esc_attr( $info['name'] ); ?>"/>
									<span class="skin-info"><span class="skin-title"><?php echo esc_html( $info['name'] ); ?></span> v<?php echo esc_html( $info['version'] ); ?></span>
								</label>
							</div>
							<?php
						}
						?>
					</div>
				</div>

				<template v-if="skin" v-cloak>
					<!-- Top Header -->
					<div class="woowgallery-top-buttons">
						<div class="woowgallery-skin-preset-selector">
							<h2><?php printf( esc_html_x( '%s Settings', 'SKIN_NAME Settings', 'woowgallery' ), '{{ skin_info.name }}' ); ?></h2>

							<div class="woowgallery-skin-preset">
								<template v-if="!new_preset">
									<label>
										<span class="label"><?php esc_html_e( 'Choose Preset', 'woowgallery' ); ?></span>
										<select name="woowskin_preset" id="woowskin_preset" class="form-control" v-model="preset">
											<option value="default"><?php esc_html_e( 'default', 'woowgallery' ); ?></option>
											<option v-for="preset in presets" v-if="preset !== 'default'" :value="preset">{{ preset }}</option>
										</select>
									</label>
									<button type="button" class="button button-danger button-small" @click.prevent="deletePreset" :disabled="preset === 'default'"><?php esc_html_e( 'Delete', 'woowgallery' ); ?></button>
									<button type="button" class="button button-secondary button-small" @click.prevent="new_preset = true"><?php esc_html_e( 'Add New', 'woowgallery' ); ?></button>
								</template>
								<template v-else>
									<label>
										<span class="label"><?php esc_html_e( 'New Preset', 'woowgallery' ); ?></span>
										<input type="text" class="form-control" name="woowskin_preset" id="woowskin_preset" v-model="new_preset_name" placeholder="<?php esc_attr_e( 'Preset Name', 'woowgallery' ); ?>">
									</label>
									<button type="button" class="button button-secondary button-small" @click.prevent="new_preset = false"><?php esc_html_e( 'Cancel', 'woowgallery' ); ?></button>
								</template>
							</div>
						</div>
						<div id="activity" class="woowgallery-action-buttons" :class="{'activity': activity}">
							<button type="button" class="button button-secondary reset-changes-action" @click.prevent="resetSkinSettingsChanges" :disabled="!isSettingsChanged"><?php esc_html_e( 'Reset Changes', 'woowgallery' ); ?></button>
							<button type="button" class="button button-secondary reset-to-defaults-action" @click.prevent="resetSkinSettings" :disabled="isSettingsDefault"><?php esc_html_e( 'Reset to Defaults', 'woowgallery' ); ?></button>
							<button type="button" class="button button-primary save-action" @click.prevent="saveSkinSettings" v-if="new_preset" :disabled="new_preset_name === ''"><?php printf( esc_html__( 'Save `%s` Preset', 'woowgallery' ), '{{ new_preset_name || \'???\' }}' ); ?></button>
							<button type="button" class="button button-primary save-action" @click.prevent="saveSkinSettings" v-else><?php printf( esc_html__( 'Save `%s` Preset', 'woowgallery' ), '{{ preset }}' ); ?></button>
						</div>
					</div>

					<!-- Skin Settings -->
					<?php Admin::load_template( 'skin-settings' ); ?>
				</template>

			</div>
		</div>

		<?php
		wp_nonce_field( 'skin_settings_save', '_nonce_woowgallery_skin_settings_save', false );
		?>

	</form>
	<?php
	echo '<script>var woowgallery_skin = ' . wp_json_encode( $skins, JSON_FORCE_OBJECT ) . ';</script>';
}
?>
