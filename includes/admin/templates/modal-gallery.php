<?php
/**
 * The template for Woowgallery modal.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

use WoowGallery\Posttypes;

/**
 * Template vars
 *
 * @global WP_Post $post
 */
global $post;

$hide_menu = ( Posttypes::ALBUM_POSTTYPE === $post->post_type ) ? 'hide-menu' : '';
?>
	<div id="woowgallery-modal">
		<template v-if="content" v-cloak>
			<div tabindex="0" class="upload-php media-modal wp-core-ui">
				<div class="media-modal-content">
					<div class="media-frame mode-select hide-router <?php echo esc_attr( $hide_menu ); ?>" :class="{'modal-menu-slide-in': menu_toggle, 'hide-menu': modalIframeSrc}">
						<div class="media-frame-menu">
							<?php if ( empty( $hide_menu ) ) { ?>
								<div class="media-menu">
									<?php if ( Posttypes::GALLERY_POSTTYPE !== $post->post_type ) { ?>
										<a href="#" class="media-menu-item" :class="{active: ('woowgallery' === get_post_type)}" @click.prevent="get_post_type = 'woowgallery'; frame_title = '<?php echo esc_js( __( 'WoowGallery Galleries', 'woowgallery' ) ); ?>'"><?php esc_html_e( 'Woow Galleries', 'woowgallery' ); ?></a>
										<a href="#" class="media-menu-item" :class="{active: ('woowgallery-dynamic' === get_post_type)}" @click.prevent="get_post_type = 'woowgallery-dynamic'; frame_title = '<?php echo esc_js( __( 'WoowGallery Dynamic Galleries', 'woowgallery' ) ); ?>'"><?php esc_html_e( 'Woow Dynamic Galleries', 'woowgallery' ); ?></a>
										<a href="#" class="media-menu-item" :class="{active: ('woowgallery-album' === get_post_type)}" @click.prevent="get_post_type = 'woowgallery-album'; frame_title = '<?php echo esc_js( __( 'WoowGallery Albums', 'woowgallery' ) ); ?>'"><?php esc_html_e( 'Woow Albums', 'woowgallery' ); ?></a>
									<?php } else { ?>
										<?php
										$_post_types = woowgallery_get_post_types( [ 'show_in_rest' => true ] );
										foreach ( $_post_types as $_post_type ) {
											if ( ! post_type_supports( $_post_type->name, 'thumbnail' ) || in_array( $_post_type->name, [ Posttypes::ALBUM_POSTTYPE, Posttypes::GALLERY_POSTTYPE, Posttypes::DYNAMIC_POSTTYPE ], true ) ) {
												continue;
											}

											$rest_base = $_post_type->rest_base ?: $_post_type->name;
											echo '<a href="#" class="media-menu-item" :class="{active: (\'' . esc_attr( $_post_type->name ) . '\' === get_post_type)}" @click.prevent="get_post_type = \'' . esc_attr( $_post_type->name ) . '\'; frame_title = \'' . esc_attr( $_post_type->label ) . '\'">';
											echo wg_posttype_icon( $_post_type ); // @codingStandardsIgnoreLine
											echo esc_html( $_post_type->label );
											echo '</a>';
										}
										?>
									<?php } ?>
								</div>
								<div class="menu-backdrop" @click="menu_toggle = false;"></div>
							<?php } ?>
						</div>
						<div class="media-frame-title">
							<span id="wg-modal-menu-toggle" v-if="!modalIframeSrc" @click="menu_toggle = !menu_toggle;"><span class="dashicons dashicons-menu-alt"></span></span>
							<h1 v-text="frame_title"></h1>
							<button v-if="previous_screen" @click.prevent="loadPreviousScreen()" type="button" class="media-modal-close"><span class="dashicons dashicons-arrow-left-alt"><span class="screen-reader-text"><?php esc_html_e( 'Go Back', 'woowgallery' ); ?></span></span></button>
							<button @click.prevent="modalClose()" type="button" class="media-modal-close"><span class="media-modal-icon"><span class="screen-reader-text"><?php esc_html_e( 'Close media panel', 'woowgallery' ); ?></span></span></button>
						</div>
						<div class="media-frame-content" :class="{'show-iframe': modalIframeSrc}">
							<iframe v-if="modalIframeSrc" class="edit-woowgallery-modal" :src="modalIframeSrc" referrerpolicy="same-origin" name="woowgallery-edit-iframe" width="100%" height="100%" allowtransparency></iframe>
							<div v-else class="attachments-browser">
								<!-- Left -->
								<div class="attachment-media-view">
									<div class="media-toolbar">
										<div class="media-toolbar-secondary">
											<!-- Pagination -->
											<div class="woowgallery-pager" v-if="pages > 1">
												<div class="woowgallery-btn-group">
													<a class="woowgallery-btn" :class="{disabled: (page < 2)}" @click.prevent="page = 1" href="#"><span class="fast-backward">«</span></a>
													<a class="woowgallery-btn" :class="{disabled: (page < 2)}" @click.prevent="page--" href="#"><span class="step-backward">‹</span></a>
												</div>
												<div class="woowgallery-page-of woowgallery-btn-group">
													<span class="woowgallery-btn-addon"><?php esc_html_e( 'Page', 'woowgallery' ); ?></span>
													<input class="woowgallery-pager-current-page" type="number" v-model="page" min="1" :max="pages" step="1">
													<span class="woowgallery-btn-addon"><?php echo esc_html_x( 'of', 'paging: (Page 1 of 100)' ); ?> {{ pages }}</span>
												</div>
												<div class="woowgallery-btn-group">
													<a class="woowgallery-btn" :class="{disabled: (page > (pages - 1))}" @click.prevent="page++" href="#"><span class="step-forward">›</span></a>
													<a class="woowgallery-btn" :class="{disabled: (page > (pages - 1))}" @click.prevent="page = pages" href="#"><span class="fast-forward">»</span></a>
												</div>
											</div>

											<div class="woowgallery-select-switchers" v-if="showSelectSwitchers()">
												<label class="wg-add-media-toggle woowgallery-selection-prepend">
													<input type="checkbox" v-model="prepend_mode"/>
													<span class="prepend-mode" title="<?php esc_attr_e( 'Prepend selected items to the gallery', 'woowgallery' ); ?>"><object type="image/svg+xml" data="<?php echo esc_url( plugins_url( 'assets/images/add-items.svg', WOOWGALLERY_FILE ) ); ?>" width="32" height="32"></object></span>
													<span class="append-mode" title="<?php esc_attr_e( 'Append selected items to the gallery', 'woowgallery' ); ?>"><object type="image/svg+xml" data="<?php echo esc_url( plugins_url( 'assets/images/add-items.svg', WOOWGALLERY_FILE ) ); ?>" width="32" height="32"></object></span>
												</label>
												<label class="wg-add-media-toggle woowgallery-selection-display">
													<input type="checkbox" v-model="hide_selected"/>
													<span class="selected-show" title="<?php esc_attr_e( 'Show items that are already added to the gallery', 'woowgallery' ); ?>"><object type="image/svg+xml" data="<?php echo esc_url( plugins_url( 'assets/images/selected-show.svg', WOOWGALLERY_FILE ) ); ?>" width="32" height="32"></object></span>
													<span class="selected-hide" title="<?php esc_attr_e( 'Hide items that are already added to the gallery', 'woowgallery' ); ?>"><object type="image/svg+xml" data="<?php echo esc_url( plugins_url( 'assets/images/selected-hide.svg', WOOWGALLERY_FILE ) ); ?>" width="32" height="32"></object></span>
												</label>
											</div>
										</div>
										<div class="media-toolbar-primary search-form">
											<span class="spinner" :class="{'is-active': loading}"></span>
											<input type="search" placeholder="<?php esc_attr_e( 'Search...', 'woowgallery' ); ?>" id="woowgallery-media-search-input" class="form-control search" v-model="search_term">
										</div>
									</div>
									<div class="woowgallery-attachments-wrapper">
										<ul class="attachments" v-if="get_post_type && content.length">
											<li class="attachment" v-for="(item, i) in content" :key="i + '-' + item.id" :class="selectedItemClasses(item.id)" :data-id="item.id">
												<div class="woowgallery-checkbox">
													<button type="button" class="check" tabindex="-1" @click="toggleSelectItem(item.id, $event)">
														<span class="media-modal-icon"></span>
														<span class="screen-reader-text"><?php esc_html_e( 'Deselect', 'woowgallery' ); ?></span>
													</button>
												</div>
												<div class="attachment-preview-wrapper" @mouseup.left.exact="selectItem(item.id, $event)" @mouseup.left.shift.exact="selectItemsTo(item.id, $event)">
													<div class="attachment-preview" :class="['type-' + item.type, 'subtype-' + item.subtype]">
														<div class="thumbnail" v-if="item.thumb[0]">
															<img :src="item.thumb[0]" alt=""/>
														</div>
														<div class="no-thumbnail" v-else><?php esc_html_e( 'No Featured Image', 'woowgallery' ); ?></div>
													</div>
													<div class="meta">
														<div class="title">{{ mediaTitle(item) }}</div>
														<div class="wg-count" v-if="item.count">{{ mediaCount(item) }}</div>
													</div>
												</div>
											</li>
										</ul>
										<div class="no-entries-found" :class="{'still-loading': loading}" v-else-if="get_post_type">
											<h3><?php esc_html_e( 'There is nothing to show :(', 'gmtd' ); ?></h3>
										</div>
										<div class="no-entries-found" v-else>
											<h3><?php esc_html_e( 'Select Post Type in the menu.', 'gmtd' ); ?></h3>
										</div>
									</div>
								</div>

								<!-- Right -->
								<div class="attachment-info">
									<!-- Settings -->
									<div class="settings">
										<template v-if="selected_last">
											<div class="attachment-details">
												<div class="thumbnail" v-if="selected_last_item.thumb[0]">
													<img :src="selected_last_item.thumb[0]" alt=""/>
												</div>
												<div class="meta">
													<div class="title">{{ mediaTitle(selected_last_item) }}</div>
													<div class="date">{{ selected_last_item.date|mediaDate }}</div>
													<div class="wg-count" v-if="selected_last_item.count">{{ mediaCount(selected_last_item) }}</div>
													<div class="wg-tags" v-if="selected_last_item.tags.length">{{ selected_last_item.tags|mediaTags }}</div>
													<div class="wg-actions">
														<a v-if="'woowgallery' === get_post_type || 'woowgallery-dynamic' === get_post_type || 'woowgallery-album' === get_post_type"
															:href="selected_last_item.edit_link"
															@click.prevent="createModal(selected_last_item.subtype, selected_last_item.id, 'WoowGallery', true)"
															class="button button-secondary button-small"
															target="_blank"
														><?php esc_html_e( 'Edit', 'woowgallery' ); ?></a>
														<a v-else :href="selected_last_item.edit_link" target="_blank"><?php esc_html_e( 'Edit', 'woowgallery' ); ?></a>
													</div>
												</div>
											</div>

											<template v-if="'shortcode' === modal_type">
												<h2><?php esc_html_e( 'Shortcode Settings', 'woowgallery' ); ?></h2>
												<div class="woowgallery-setting">
													<label for="wg_width"><?php esc_html_e( 'Width', 'woowgallery' ); ?></label>
													<div class="multi-input">
														<input type="number" id="wg_width" class="form-control" v-model="wg_width"/>
														<select id="wg_align" class="form-control" v-model="wg_width_unit">
															<option value="%">%</option>
															<option value="px">px</option>
															<option value="vw">vw</option>
														</select>
													</div>
												</div>

												<div class="woowgallery-setting">
													<label for="wg_align"><?php esc_html_e( 'Align', 'woowgallery' ); ?></label>
													<select id="wg_align" class="form-control" v-model="wg_align">
														<option value="left"><?php esc_attr_e( 'Left', 'woowgallery' ); ?></option>
														<option value="center"><?php esc_attr_e( 'Center', 'woowgallery' ); ?></option>
														<option value="right"><?php esc_attr_e( 'Right', 'woowgallery' ); ?></option>
													</select>
												</div>
											</template>
										</template>

										<!-- Addons can populate the UI here -->
										<div class="woowgallery-addons"></div>
									</div>
									<!-- /.settings -->

									<!-- Actions -->
									<div class="actions">
										<span class="spinner" :class="{'is-active': inserting}"></span>
										<button @click="insert()" class="button button-primary woowgallery-modal-insert" :disabled="!selected_last"><?php esc_html_e( 'Insert', 'woowgallery' ); ?></button>
									</div>
									<!-- /.actions -->
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="media-modal-backdrop" @click="modalClose()"></div>
		</template>
	</div>

<?php
/*

<script type="text/html" id="tmpl-woowgallery-bulk-edit-items">
    <# var model = data.toJSON(); #>
    <div class="attachment-media-view woowgallery-collection-preview grid">
        <ul class="attachments woowgallery-collection-media-output"></ul>
    </div>
    <div class="attachment-info">
        <div class="settings">
            <h2><?php _e( 'Edit Meta', 'woowgallery' ); ?></h2>
            <label class="setting">
                <span class="name"><?php _e('Title', 'woowgallery' ); ?></span>
                <input type="text" name="title" data-setting="title" value="{{ model.title }}" />
            </label>

            <div class="display-settings"></div>
        </div>

        <div class="actions">
            <span class="settings-save-status">
                <span class="spinner"></span>
                <span class="saved"><?php esc_html_e('Saved', 'woowgallery' ); ?></span>
            </span>
            <button type="button" class="button-primary woowgallery-collection-bulk-save"><?php _e( 'Save', 'woowgallery' ); ?></button>
        </div>

    </div>
</script>

*/
