<?php
/**
 * Outputs the Skin Settings.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */
?>
	<div id="woowgallery-skin-settings" class="woowgallery-skin-settings">
		<div class="woowgallery-no-skin" v-if="!skin" v-cloak>
			<h3><?php esc_html_e( 'Select skin to config it settings, please.', 'woowgallery' ); ?></h3>
		</div>
		<div v-else class="woowgallery-skin-settings-inner">
			<!-- Skin Screenshot -->
			<div class="woowgallery-skin-sreenshot" v-if="skin_info.screenshots && skin_info.screenshots[0]">
				<img :src="skin_info.screenshots[0]" :alt="skin_info.name">
			</div>
			<!-- Title and Help -->
			<div class="woowgallery-skin-intro">
				<h3><?php esc_html_e( 'Skin Settings', 'woowgallery' ); ?></h3>
				<div class="skin-description" v-html="skin_info.description"></div>
				<div class="skin-info" v-if="!premium" v-html="skin_info.info"></div>
				<select class="woowgallery-load-preset form-control" :class="{'activity': activity}" @change="loadPreset($event)">
					<option value="_custom" selected><?php esc_attr_e( 'Load Preset', 'woowgallery' ); ?></option>
					<option value="default"><?php esc_attr_e( 'Default', 'woowgallery' ); ?></option>
					<option v-for="preset in presets" v-if="(preset !== '_custom') && (preset !== 'default')" :value="preset">{{ preset }}</option>
				</select>
				<p><?php esc_html_e( 'The settings below adjust the basic configuration options for the gallery.', 'woowgallery' ); ?></p>
			</div>

			<div class="woowgallery-config-wrapper" :data-preset="preset">
				<nav class="woowgallery-config-tabs-nav">
					<div v-for="(group, tab_id) in schema" :key="skin + '_tab_' + tab_id">
						<a :href="'#fieldset-' + tab_id" :class="{'woowgallery-active': activeTab === tab_id}" @click.prevent="switchTab(tab_id)">{{ group.label }}</a>
					</div>
				</nav>

				<div class="woowgallery-config-tabs">
					<div class="vue-form-generator" v-if="schema != null">
						<fieldset v-for="(group, tab_id) in schema" :id="'fieldset-' + tab_id" :class="{'woowgallery-active': activeTab === tab_id}" :key="skin + '_' + tab_id">
							<h4>{{ group.label }}</h4>
							<div class="form-group" v-for="(field, key) in group.fields" v-if="fieldVisible(field)" :class="getFieldRowClasses(field)" :style="getFieldRowStyles(field)" :key="skin + '_' + key">
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
									<div v-else-if="'html' === field.tag && field.html" class="woowgallery-settings-html">
										<div class="wg-settings-html-wrapper" v-html="field.html"></div>
									</div>
									<div v-else-if="'flexbox' === field.tag && field.fields" class="wg-flexbox">
										<div class="inline-field" v-for="(subfield, subkey) in field.fields" v-if="fieldVisible(subfield)" :style="getFieldRowStyles(subfield)" :key="skin + '_' + subkey">
											<label v-if="fieldTypeHasLabel(subfield)" :for="subkey">{{ subfield.label }}</label>
											<component :is="getFieldTagType(subfield)" :skin="skin" :schema="subfield" :id="subkey" :options="options" :disabled="fieldDisabled(subfield)"></component>
										</div>
									</div>
									<component v-else :is="getFieldTagType(field)" :skin="skin" :preset="preset" :schema="field" :id="key" :options="options" :disabled="fieldDisabled(field)"></component>
								</div>
								<div class="hint" v-if="field.text" v-html="field.text"></div>
							</div>
						</fieldset>
					</div>
				</div>
			</div>

		</div>
	</div>
<?php
/* @todo vue template for checklist.
 * ?>
 * <template v-if="schema.tag === 'checklist'">
 * <div class="wrapper">
 * <div class="listbox form-control" v-if="schema.listBox" :disabled="disabled">
 * <div class="list-row" v-for="item in items" :class="{'is-checked': isItemChecked(item)}">
 * <label>
 * <input type="checkbox" :checked="isItemChecked(item)" :disabled="disabled" :name="getInputName(item)">
 * {{ getItemName(item) }}
 * </label>
 * </div>
 * </div>
 * <div class="combobox form-control" v-if="!schema.listBox" :disabled="disabled">
 * <div class="mainRow" @click="onExpandCombo" :class="{ expanded: comboExpanded }">
 * <div class="info">{{ selectedCount }} selected</div>
 * <div class="arrow"></div>
 * </div>
 * <div class="dropList">
 * <div class="list-row" v-if="comboExpanded" v-for="item in items" :class="{'is-checked': isItemChecked(item)}">
 * <label>
 * <input type="checkbox" :checked="isItemChecked(item)" :disabled="disabled" :name="getInputName(item)">
 * {{ getItemName(item) }}
 * </label>
 * </div>
 * </div>
 * </div>
 * </div>
 * </template>
 * <?php
 */
