<?php
/**
 * Outputs the Gallery Lightbox Config Template.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

use WoowGallery\Assets;
use WoowGallery\Gallery;
use WoowGallery\Lightbox;

/**
 * @var $data array
 */
$wg = Gallery::get_instance( $data['post']->ID, $data['post']->post_type );

$lightbox_list = Assets::lightboxes();
$lightbox      = Lightbox::get_instance();
$lb_settings   = $lightbox->get_settings();

$wg_lb_name   = $wg->get_lightbox_slug();
$wg_lb_config = $wg->get_lightbox_config();
if ( isset( $lb_settings[ $wg_lb_name ] ) ) {
	$lb_settings[ $wg_lb_name ] = $lightbox->get_lightbox( $wg_lb_name, $wg_lb_config );
}
?>
	<div id="woowgallery-lightbox">
		<div id="woowgallery-lightbox-config" v-cloak>
			<div class="woowgallery-lightbox-settings-inner">
				<!-- Title and Help -->
				<div class="woowgallery-tab-intro">
					<h3><?php esc_html_e( 'Lightbox Settings', 'woowgallery' ); ?></h3>

					<div id="wg-config-classes-box" class="form-group field-input">
						<label for="wg-config-classes"><?php esc_html_e( 'Select Lightbox', 'woowgallery' ); ?></label>
						<div class="field-wrap">
							<div class="wrapper">
								<div class="wg-radio-group">
									<input type="radio" id="wglb-none" value="" :name="'_woowgallery[lightbox]'" v-model="lightbox" />
									<label for="wglb-none"><?php esc_html_e( 'Disabled', 'woowgallery' ); ?></label>
									<?php
									foreach ( $lightbox_list as $lb ) {
										echo '<input type="radio" id="wglb-' . esc_attr( $lb['slug'] ) . '" value="' . esc_attr( $lb['slug'] ) . '" :name="\'_woowgallery[lightbox]\'" v-model="lightbox" />';
										echo '<label for="wglb-' . esc_attr( $lb['slug'] ) . '">' . esc_html( $lb['name'] ) . '</label>';
									}
									?>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="woowgallery-config-wrapper" v-if="lightbox">
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
	</div>

	<script><?php echo 'var woowgallery_lightbox = ' . wp_json_encode( $lb_settings, JSON_FORCE_OBJECT ) . ';'; ?></script>
<?php
