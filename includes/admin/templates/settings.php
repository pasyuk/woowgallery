<?php
/**
 * Outputs the Settings panel.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

use WoowGallery\Admin\Settings;
use WoowGallery\Assets;
use WoowGallery\Lightbox;
use WoowGallery\Posttypes;

/**
 * Template vars
 *
 * @var array $data
 */

$settings = $data['settings'];
$skins    = $data['skins'];
?>
<form method="post" style="padding-top: 20px;">
	<h1><?php esc_html_e( 'General Settings', 'woowgallery' ); ?></h1>
	<div class="postbox">
		<div class="woowgallery-flex align-top">
			<!-- Tabs -->
			<ul id="woowgallery-tabs-nav" class="woowgallery-tabs-nav" data-container="#woowgallery-tabs">
				<li class="woowgallery-tab-nav-media">
					<a href="#woowgallery-tab-media" class="woowgallery-active">
						<span class="dashicons dashicons-admin-media"></span>
						<span class="tab-label"><?php esc_html_e( 'Media', 'woowgallery' ); ?></span>
					</a>
				</li>
				<li class="woowgallery-tab-nav-editor">
					<a href="#woowgallery-tab-editor">
						<span class="dashicons dashicons-edit-large"></span>
						<span class="tab-label"><?php esc_html_e( 'Editor', 'woowgallery' ); ?></span>
					</a>
				</li>
				<li class="woowgallery-tab-nav-standalone">
					<a href="#woowgallery-tab-standalone">
						<span class="dashicons dashicons-marker"></span>
						<span class="tab-label"><?php esc_html_e( 'Standalone', 'woowgallery' ); ?></span>
					</a>
				</li>
				<li class="woowgallery-tab-nav-lightbox">
					<a href="#woowgallery-tab-lightbox">
						<span class="dashicons dashicons-editor-expand"></span>
						<span class="tab-label"><?php esc_html_e( 'Lightbox', 'woowgallery' ); ?></span>
					</a>
				</li>
				<li class="woowgallery-tab-nav-misc">
					<a href="#woowgallery-tab-misc">
						<span class="dashicons dashicons-admin-tools"></span>
						<span class="tab-label"><?php esc_html_e( 'Misc', 'woowgallery' ); ?></span>
					</a>
				</li>
			</ul>

			<!-- Settings -->
			<div id="woowgallery-tabs" data-navigation="#woowgallery-tabs-nav">

				<div id="woowgallery-tab-media" class="woowgallery-tab inside woowgallery-active">
					<div class="form-group field-flexbox">
						<label for="wg-thumb-size"><?php esc_html_e( 'Thumbnail Size', 'woowgallery' ); ?></label>
						<div class="field-wrap">
							<div class="wg-flexbox">
								<div class="inline-field">
									<label for="wg-thumb-width"><?php esc_html_e( 'Max Width', 'woowgallery' ); ?></label>
									<div class="wrapper">
										<input id="wg-thumb-width" name="settings[thumb_width]" type="number" min="80" class="form-control" value="<?php echo (int) $settings['thumb_width']; ?>"/>
									</div>
								</div>
								<div class="inline-field">
									<label for="wg-thumb-height"><?php esc_html_e( 'Max Height', 'woowgallery' ); ?></label>
									<div class="wrapper">
										<input id="wg-thumb-height" name="settings[thumb_height]" type="number" min="80" class="form-control" value="<?php echo (int) $settings['thumb_height']; ?>"/>
									</div>
								</div>
								<div class="inline-field">
									<label for="wg-thumb-quality"><?php esc_html_e( 'Quality', 'woowgallery' ); ?></label>
									<div class="wrapper">
										<input id="wg-thumb-quality" name="settings[thumb_quality]" type="number" min="1" max="100" class="form-control" value="<?php echo (int) $settings['thumb_quality']; ?>"/>
									</div>
								</div>
							</div>
						</div>
						<div class="hint">
							<?php
							// translators: image size.
							echo wp_kses( sprintf( __( 'Recommended thumbnail maximum dimensions is %s. It is a most suitable size for all WoowGallery skins.', 'woowgallery' ), '<code>400x400</code>' ), '' );
							// translators: image quality.
							echo '<br />' . wp_kses( sprintf( __( '<strong>Note:</strong> high quality lead to bigger image file size and slower loading. Recommended quality range: %s.', 'woowgallery' ), '<code>80-85</code>' ), '' );
							?>
						</div>
					</div>

					<div class="form-group field-flexbox">
						<label for="wg-image-size"><?php esc_html_e( 'Image Size', 'woowgallery' ); ?></label>
						<div class="field-wrap">
							<div class="wg-flexbox">
								<div class="inline-field">
									<label for="wg-image-width"><?php esc_html_e( 'Max Width', 'woowgallery' ); ?></label>
									<div class="wrapper">
										<input id="wg-image-width" name="settings[image_width]" type="number" min="400" class="form-control" value="<?php echo (int) $settings['image_width']; ?>"/>
									</div>
								</div>
								<div class="inline-field">
									<label for="wg-image-height"><?php esc_html_e( 'Max Height', 'woowgallery' ); ?></label>
									<div class="wrapper">
										<input id="wg-image-height" name="settings[image_height]" type="number" min="400" class="form-control" value="<?php echo (int) $settings['image_height']; ?>"/>
									</div>
								</div>
								<div class="inline-field">
									<label for="wg-image-quality"><?php esc_html_e( 'Quality', 'woowgallery' ); ?></label>
									<div class="wrapper">
										<input id="wg-image-quality" name="settings[image_quality]" type="number" min="1" max="100" class="form-control" value="<?php echo (int) $settings['image_quality']; ?>"/>
									</div>
								</div>
							</div>
						</div>
						<div class="hint">
							<?php
							esc_html_e( 'Determine the maximum dimensions in pixels to use when adding an image to the WoowGallery.', 'woowgallery' );
							// translators: link to WP Media Settings.
							echo '<br />' . wp_kses( sprintf( __( '<strong>Note:</strong> To decrease usage of server\'s disk space you can set the same image size for `Large size` at <a href="%s">WordPress Media Settings</a>.', 'woowgallery' ), admin_url( 'options-media.php' ) ), '' );
							echo '<br />' . wp_kses( __( '<strong>Important:</strong> If you change `Thumbnail Size` or `Image Size` settings you need to re-save galleries to crop new image sizes.', 'woowgallery' ), '' );
							?>
						</div>
					</div>

					<div class="form-group field-select">
						<label for="wg-media-delete"><?php esc_html_e( 'Delete Media on Gallery Deletion', 'woowgallery' ); ?></label>
						<div class="field-wrap">
							<div class="wrapper">
								<select name="settings[media_delete]" id="wg-media-delete" class="form-control">
									<option value="grid" <?php selected( $settings['media_delete'], '0' ); ?>><?php esc_attr_e( 'No', 'woowgallery' ); ?></option>
									<option value="list" <?php selected( $settings['media_delete'], '1' ); ?>><?php esc_attr_e( 'Yes', 'woowgallery' ); ?></option>
								</select>
							</div>
						</div>
						<div class="hint">
							<?php
							esc_html_e( 'When deleting a Gallery, choose whether to delete all media associated with the gallery.', 'woowgallery' );
							echo '<br />' . wp_kses( __( '<strong>Note:</strong> If media files are attached to other Posts (in the Media Library) or present in other Galleries, they will not be deleted.', 'woowgallery' ), '' );
							?>
						</div>
					</div>
				</div>

				<div id="woowgallery-tab-editor" class="woowgallery-tab inside">
					<div class="form-group field-checkbox">
						<label for="wg-selection-prepend"><?php esc_html_e( 'Add New Media', 'woowgallery' ); ?></label>
						<div class="field-wrap">
							<div class="wrapper">
								<input type="hidden" name="settings[selection_prepend]" value="0"/>
								<label id="wg-selection-prepend" class="wg-add-media-toggle">
									<input type="checkbox" name="settings[selection_prepend]" value="1" <?php checked( $settings['selection_prepend'], '1' ); ?> />
									<span class="wg-checked prepend-mode"><img src="<?php echo esc_url( plugins_url( 'assets/images/add-items.svg', WOOWGALLERY_FILE ) ); ?>" width="32" height="32" alt="prepend icon"/> <?php esc_html_e( 'Before Existing Media', 'woowgallery' ); ?></span>
									<span class="wg-unchecked append-mode"><img src="<?php echo esc_url( plugins_url( 'assets/images/add-items.svg', WOOWGALLERY_FILE ) ); ?>" width="32" height="32" alt="append icon"/> <?php esc_html_e( 'After Existing Media', 'woowgallery' ); ?></span>
								</label>
							</div>
						</div>
						<div class="hint"><?php esc_html_e( 'When adding media to a Gallery, choose whether to add this media before or after any existing images. This can be changed in Media Browser popup window before you insert attachments to the gallery.', 'woowgallery' ); ?></div>
					</div>

					<div class="form-group field-select">
						<label for="wg-edit-view"><?php esc_html_e( 'Default Media View for Edit Gallery', 'woowgallery' ); ?></label>
						<div class="field-wrap">
							<div class="wrapper">
								<select name="settings[edit_gallery_view]" id="wg-edit-view" class="form-control">
									<option value="grid" <?php selected( $settings['edit_gallery_view'], 'grid' ); ?>><?php esc_attr_e( 'Grid', 'woowgallery' ); ?></option>
									<option value="list" <?php selected( $settings['edit_gallery_view'], 'list' ); ?>><?php esc_attr_e( 'List', 'woowgallery' ); ?></option>
								</select>
							</div>
						</div>
						<div class="hint"><?php esc_html_e( 'Select default view for media on edit gallery page.', 'woowgallery' ); ?></div>
					</div>
				</div>

				<div id="woowgallery-tab-standalone" class="woowgallery-tab inside">
					<div>
						<h3><?php esc_html_e( 'With Standalone Galleries you\'ll be able to:', 'woowgallery' ); ?></h3>
						<ol>
							<li><?php esc_html_e( 'Create galleries as a separate pages.', 'woowgallery' ); ?></li>
							<li><?php esc_html_e( 'Apply theme\'s templates for galleries.', 'woowgallery' ); ?></li>
							<li><?php esc_html_e( 'Add categories and tags for galleries.', 'woowgallery' ); ?></li>
							<li><?php esc_html_e( 'Add WoowGallery Galleries and WoowGallery Albums to Dynamic Galleries.', 'woowgallery' ); ?></li>
							<li><?php esc_html_e( 'Easily add Standalone Galleries to WordPress Menus.', 'woowgallery' ); ?></li>
						</ol>
					</div>
					<div class="form-group field-checkbox">
						<label for="wg-standalone"><?php esc_html_e( 'Enable Standalone', 'woowgallery' ); ?></label>
						<div class="field-wrap">
							<div class="wrapper">
								<p>
									<?php $_key = 'standalone_' . Posttypes::GALLERY_POSTTYPE; ?>
									<input type="hidden" name="settings[<?php echo esc_attr( $_key ); ?>]" value="0"/>
									<label>
										<span class="wg-toggle">
											<input type="checkbox" id="wg-standalone-gallery" name="settings[<?php echo esc_attr( $_key ); ?>]" value="1" <?php checked( Settings::get_settings( $_key ), '1' ); ?>/>
											<span class="wg-toggle__track"></span>
											<span class="wg-toggle__thumb"></span>
										</span>
										<?php esc_html_e( 'Galleries', 'woowgallery' ); ?>
									</label>
								</p>
								<p>
									<?php $_key = 'standalone_' . Posttypes::DYNAMIC_POSTTYPE; ?>
									<input type="hidden" name="settings[<?php echo esc_attr( $_key ); ?>]" value="0"/>
									<label>
										<span class="wg-toggle">
											<input type="checkbox" id="wg-standalone-dynamic" name="settings[<?php echo esc_attr( $_key ); ?>]" value="1" <?php checked( Settings::get_settings( $_key ), '1' ); ?>/>
											<span class="wg-toggle__track"></span>
											<span class="wg-toggle__thumb"></span>
										</span>
										<?php esc_html_e( 'Dynamic Galleries', 'woowgallery' ); ?>
									</label>
								</p>
								<p>
									<?php $_key = 'standalone_' . Posttypes::ALBUM_POSTTYPE; ?>
									<input type="hidden" name="settings[<?php echo esc_attr( $_key ); ?>]" value="0"/>
									<label>
										<span class="wg-toggle">
											<input type="checkbox" id="wg-standalone-album" name="settings[<?php echo esc_attr( $_key ); ?>]" value="1" <?php checked( Settings::get_settings( $_key ), '1' ); ?>/>
											<span class="wg-toggle__track"></span>
											<span class="wg-toggle__thumb"></span>
										</span>
										<?php esc_html_e( 'Albums', 'woowgallery' ); ?>
									</label>
								</p>
								<?php woowgallery_is_premium_feature(); ?>
							</div>
						</div>
						<div class="hint"><?php esc_html_e( 'The standalone option allows you to access galleries created through the WoowGallery post type with unique URLs. Your galleries can have dedicated gallery pages!', 'woowgallery' ); ?></div>
					</div>

					<div class="form-group field-input">
						<label for="wg-taxonomies"><?php esc_html_e( 'Taxonomies', 'woowgallery' ); ?></label>
						<div class="field-wrap">
							<div class="wrapper">
								<p>
									<input type="hidden" name="settings[woowgallery_categories]" value="0"/>
									<label>
										<span class="wg-toggle">
											<input type="checkbox" id="wg-categories" name="settings[woowgallery_categories]" value="1" <?php checked( Settings::get_settings( 'woowgallery_categories' ), '1' ); ?>/>
											<span class="wg-toggle__track"></span>
											<span class="wg-toggle__thumb"></span>
										</span>
										<?php esc_html_e( 'Categories', 'woowgallery' ); ?>
									</label>
								</p>
								<p>
									<input type="hidden" name="settings[woowgallery_tags]" value="0"/>
									<label>
										<span class="wg-toggle">
											<input type="checkbox" id="wg-tags" name="settings[woowgallery_tags]" value="1" <?php checked( Settings::get_settings( 'woowgallery_tags' ), '1' ); ?>/>
											<span class="wg-toggle__track"></span>
											<span class="wg-toggle__thumb"></span>
										</span>
										<?php esc_html_e( 'Tags', 'woowgallery' ); ?>
									</label>
								</p>
								<?php woowgallery_is_premium_feature(); ?>
							</div>
						</div>
						<div class="hint"><?php esc_html_e( 'Enable or Disable taxonomies for WoowGallery Post Types.', 'woowgallery' ); ?></div>
					</div>

					<div class="form-group field-input">
						<label for="wg-gallery-slug"><?php esc_html_e( 'Gallery Slug Base', 'woowgallery' ); ?></label>
						<div class="field-wrap">
							<div class="wrapper">
								<?php $_key = 'permalink_base_' . Posttypes::GALLERY_POSTTYPE; ?>
								<input type="text" name="settings[<?php echo esc_attr( $_key ); ?>]" id="wg-gallery-slug" class="form-control" value="<?php echo esc_attr( Settings::get_settings( $_key, Posttypes::GALLERY_POSTTYPE ) ); ?>"/>
								<?php woowgallery_is_premium_feature(); ?>
							</div>
						</div>
						<div class="hint"><?php esc_html_e( 'The slug to prefix all WoowGallery Galleries.', 'woowgallery' ); ?></div>
					</div>

					<div class="form-group field-input">
						<label for="wg-dynamic-slug"><?php esc_html_e( 'Dynamic Gallery Slug Base', 'woowgallery' ); ?></label>
						<div class="field-wrap">
							<div class="wrapper">
								<?php $_key = 'permalink_base_' . Posttypes::DYNAMIC_POSTTYPE; ?>
								<input type="text" name="settings[<?php echo esc_attr( $_key ); ?>]" id="wg-dynamic-slug" class="form-control" value="<?php echo esc_attr( Settings::get_settings( $_key, Posttypes::DYNAMIC_POSTTYPE ) ); ?>"/>
								<?php woowgallery_is_premium_feature(); ?>
							</div>
						</div>
						<div class="hint"><?php esc_html_e( 'The slug to prefix all WoowGallery Dynamic Galleries.', 'woowgallery' ); ?></div>
					</div>

					<div class="form-group field-input">
						<label for="wg-album-slug"><?php esc_html_e( 'Album Slug Base', 'woowgallery' ); ?></label>
						<div class="field-wrap">
							<div class="wrapper">
								<?php $_key = 'permalink_base_' . Posttypes::ALBUM_POSTTYPE; ?>
								<input type="text" name="settings[<?php echo esc_attr( $_key ); ?>]" id="wg-album-slug" class="form-control" value="<?php echo esc_attr( Settings::get_settings( $_key, Posttypes::ALBUM_POSTTYPE ) ); ?>"/>
								<?php woowgallery_is_premium_feature(); ?>
							</div>
						</div>
						<div class="hint"><?php esc_html_e( 'The slug to prefix all WoowGallery Albums.', 'woowgallery' ); ?></div>
					</div>
				</div>

				<div id="woowgallery-tab-lightbox" class="woowgallery-tab inside">
					<?php if ( woow_fs()->can_use_premium_code__premium_only() ) { ?>
						<?php
						$lightbox_list = Assets::lightboxes();
						$lightbox      = Lightbox::get_instance();
						$lb_settings   = $lightbox->get_settings();
						?>
						<div id="woowgallery-lightbox-config">
							<!-- Title and Help -->
							<div class="woowgallery-tab-intro">
								<h3><?php esc_html_e( 'Default Settings for Lightbox', 'woowgallery' ); ?></h3>

								<div id="wg-config-classes-box" class="form-group field-input">
									<label for="wg-config-classes"><?php esc_html_e( 'Default Lightbox', 'woowgallery' ); ?></label>
									<div class="field-wrap">
										<div class="wrapper">
											<div class="wg-radio-group">
												<input type="radio" id="wglb-none" value="" :name="'settings[default_lightbox]'" v-model="lightbox"/>
												<label for="wglb-none"><?php esc_html_e( 'Disabled', 'woowgallery' ); ?></label>
												<?php
												foreach ( $lightbox_list as $lb ) {
													echo '<input type="radio" id="wglb-' . esc_attr( $lb['slug'] ) . '" value="' . esc_attr( $lb['slug'] ) . '" :name="\'settings[default_lightbox]\'" v-model="lightbox" />';
													echo '<label for="wglb-' . esc_attr( $lb['slug'] ) . '">' . esc_html( $lb['name'] ) . '</label>';
												}
												?>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="woowgallery-lightbox-settings-inner">
								<div class="woowgallery-config-wrapper">
									<div class="woowgallery-config-tabs">
										<div class="vue-form-generator" v-if="schema != null">
											<fieldset v-for="(group, tab_id) in schema" :id="'lb-fieldset-' + tab_id" class="woowgallery-fieldset-block" :key="lightbox + '_' + tab_id">
												<h4>{{ group.label }}</h4>
												<div class="form-group" v-for="(field, key) in group.fields" v-if="fieldVisible(field)" :class="getFieldRowClasses(field)" :style="getFieldRowStyles(field)" :key="lightbox + '_' + key">
													<label v-if="fieldTypeHasLabel(field)" :for="key">{{ field.label }}
														<div class="help" v-if="field.help"><i class="icon"></i>
															<div class="helpText" v-html="field.help"></div>
														</div>
													</label>
													<div class="field-wrap">
														<div v-if="field.premium && !premium" class="woowgallery-pro-feature">
															<h6><?php esc_html_e( 'This feature is available only in the WoowGallery Premium', 'woowgallery' ); ?></h6>
															<a class="button button-primary" href="<?php echo esc_url( woow_fs()->get_upgrade_url() ); ?>" target="_blank"><span class="dashicons dashicons-cart"></span> <?php esc_html_e( 'Get WoowGallery Premium', 'woowgallery' ); ?></a>
														</div>
														<div v-else-if="'flexbox' === field.tag && field.fields" class="wg-flexbox">
															<div class="inline-field" v-for="(subfield, subkey) in field.fields" v-if="fieldVisible(subfield)" :style="getFieldRowStyles(subfield)" :key="lightbox + '_' + subkey">
																<label v-if="fieldTypeHasLabel(subfield)" :for="subkey">{{ subfield.label }}</label>
																<component :is="getFieldTagType(subfield)" :skin="lightbox" :schema="subfield" :id="subkey" :options="options" :disabled="fieldDisabled(subfield)"></component>
															</div>
														</div>
														<component v-else :is="getFieldTagType(field)" :skin="lightbox" :schema="field" :id="key" :options="options" :disabled="fieldDisabled(field)"></component>
													</div>
													<div class="hint" v-if="field.text" v-html="field.text"></div>
												</div>
											</fieldset>
										</div>
									</div>
								</div>
							</div>
						</div>
						<script><?php echo 'var woowgallery_lightbox = ' . wp_json_encode( $lb_settings, JSON_FORCE_OBJECT ) . ';'; ?></script>
					<?php } else { ?>
						<h3><?php esc_html_e( 'Default Settings for Lightbox', 'woowgallery' ); ?></h3>
						<p><?php esc_html_e( 'WoowGallery Premium required to set default settings for lightboxes', 'woowgallery' ); ?></p>
						<div class="form-group">
							<div style="position: relative; height: 100px; width: 100%; margin: 10px 0;">
								<?php woowgallery_is_premium_feature(); ?>
							</div>
						</div>
					<?php } ?>
				</div>

				<div id="woowgallery-tab-misc" class="woowgallery-tab inside">
					<div class="form-group field-textarea">
						<label for="wg-custom-css"><?php esc_html_e( 'Global Custom CSS', 'woowgallery' ); ?></label>
						<div class="field-wrap">
							<div class="wrapper" style="width: 800px;">
								<textarea name="settings[custom_css]" id="wg-custom-css" class="form-control" rows="10" cols="60"><?php echo esc_textarea( stripslashes( $settings['custom_css'] ) ); ?></textarea>
								<?php woowgallery_is_premium_feature(); ?>
							</div>
						</div>
						<div class="hint">
							<?php esc_html_e( 'These styles will be applied for all WoowGalleries.', 'woowgallery' ); ?>
							<br/><code>.woowgallery-wrapper</code> - <?php esc_html_e( 'you can use this classname for styles you added. It is the main WoowGallery wrapper for any gallery.', 'woowgallery' ); ?>
						</div>
					</div>
				</div>

			</div>
		</div>

		<div class="settings-actions inside">

			<div class="alignright">
				<button type="submit" name="woowgallery-settings-reset" class="button button-secondary" data-confirm="<?php esc_attr_e( 'This will reset plugin\'s settings and delete all skins presets.' ); ?>"><?php esc_html_e( 'Reset Plugin', 'woowgallery' ); ?></button>
				&nbsp;
				<button type="submit" name="woowgallery-settings-submit" class="button button-primary"><?php esc_html_e( 'Save', 'woowgallery' ); ?></button>
			</div>

		</div>
	</div>
	<?php
	wp_nonce_field( 'settings_save', '_nonce_woowgallery_settings_save', false );
	?>
</form>
