<?php
/**
 * The template for Append / Prepend Selection Button.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

// @formatter:off
?>
<script type="text/html" id="tmpl-woowgallery-select-switchers">
	<#
		var wg_selection_prepend = window.WoowGalleryAdmin.l10n.selection_prepend && Number(window.WoowGalleryAdmin.l10n.selection_prepend)? 'checked' : '';
		var wg_selection_hide_selected = window.WoowGalleryAdmin.l10n.selection_hide_selected && Number(window.WoowGalleryAdmin.l10n.selection_hide_selected)? 'checked' : '';
	#>
	<label class="wg-add-media-toggle woowgallery-selection-prepend">
		<input type="checkbox" {{ wg_selection_prepend }} />
		<span class="prepend-mode" title="<?php esc_attr_e( 'Prepend selected items to the gallery', 'woowgallery' ); ?>"><img src="<?php echo esc_url( plugins_url( 'assets/images/add-items.svg', WOOWGALLERY_FILE ) ); ?>" width="32" height="32" /></span>
		<span class="append-mode" title="<?php esc_attr_e( 'Append selected items to the gallery', 'woowgallery' ); ?>"><img src="<?php echo esc_url( plugins_url( 'assets/images/add-items.svg', WOOWGALLERY_FILE ) ); ?>" width="32" height="32" /></span>
	</label>
	<label class="wg-add-media-toggle woowgallery-selection-display">
		<input type="checkbox" {{ wg_selection_hide_selected }} />
		<span class="selected-show" title="<?php esc_attr_e( 'Show items that are already added to the gallery', 'woowgallery' ); ?>"><img src="<?php echo esc_url( plugins_url( 'assets/images/selected-show.svg', WOOWGALLERY_FILE ) ); ?>" width="32" height="32" /></span>
		<span class="selected-hide" title="<?php esc_attr_e( 'Hide items that are already added to the gallery', 'woowgallery' ); ?>"><img src="<?php echo esc_url( plugins_url( 'assets/images/selected-hide.svg', WOOWGALLERY_FILE ) ); ?>" width="32" height="32" /></span>
	</label>
</script>
