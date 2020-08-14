<?php
/**
 * Outputs the Gallery Preview.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

use WoowGallery\Admin\Admin;
use WoowGallery\Admin\Settings;
use WoowGallery\Posttypes;

/**
 * Template vars
 *
 * @var array $data
 */
?>
	<div id="woowgallery-preview" class="woowgallery-preview" :class="view">
		<div class="woowgallery-empty-gallery" v-if="!gallery.length" v-cloak>
			<h2><?php esc_html_e( 'WoowGallery', 'woowgallery' ); ?></h2>
			<h3 v-if="'gallery' == woowgallery_type"><?php esc_html_e( 'Create Gallery by adding media files.', 'woowgallery' ); ?></h3>
			<h3 v-else-if="'album' == woowgallery_type"><?php esc_html_e( 'Create Albums Gallery by adding Galleries.', 'woowgallery' ); ?></h3>
			<div class="wg-media-buttons">
				<?php do_action( 'woowgallery_media_buttons', $data['post'] ); ?>
			</div>
		</div>
		<div class="woowgallery-content-images" :class="{'sorting': sorting}" v-if="gallery.length" v-cloak>
			<!-- Title and Help -->
			<div class="woowgallery-intro">
				<h3>
					<?php
					if ( Posttypes::ALBUM_POSTTYPE === $data['post']->post_type ) {
						esc_html_e( 'Currently in your Album', 'woowgallery' );
					} else {
						esc_html_e( 'Currently in your Gallery', 'woowgallery' );
					}
					?>
				</h3>

				<div class="grid-actions fc-wa">
					<div class="woowgallery-sort-options">
						<select id="gallery-sortby" class="form-control gallery-sortby" name="_woowgallery[settings][sortby]" v-model="sortby">
							<option value="custom"><?php esc_html_e( 'Custom Sorting', 'woowgallery' ); ?></option>
							<option value="title"><?php esc_html_e( 'Sort by Title', 'woowgallery' ); ?></option>
							<option value="caption"><?php esc_html_e( 'Sort by Caption', 'woowgallery' ); ?></option>
							<?php
							if ( Posttypes::ALBUM_POSTTYPE !== $data['post']->post_type ) {
								echo '<option value="alt">' . esc_html__( 'Sort by Alt', 'woowgallery' ) . '</option>';
							}
							if ( Posttypes::ALBUM_POSTTYPE !== $data['post']->post_type
								|| ( woow_fs()->can_use_premium_code() && (int) Settings::get_settings( 'woowgallery_tags' ) ) ) {
								echo '<option value="tags">' . esc_html__( 'Sort by Tags', 'woowgallery' ) . '</option>';
							}
							?>
							<option value="date"><?php esc_html_e( 'Sort by Date', 'woowgallery' ); ?></option>
							<option value="slug"><?php esc_html_e( 'Sort by Slug', 'woowgallery' ); ?></option>
						</select>

						<select id="gallery-sortorder" class="form-control gallery-sortorder" name="_woowgallery[settings][sortorder]" v-model="sortorder" :disabled="'custom' === sortby">
							<option value="asc"><?php esc_html_e( 'Ascending (A-Z)', 'woowgallery' ); ?></option>
							<option value="desc"><?php esc_html_e( 'Descending (Z-A)', 'woowgallery' ); ?></option>
						</select>
					</div>

					<input class="form-control gallery-filter" type="text" v-model="filter" placeholder="<?php esc_attr_e( 'Filter', 'woowgallery' ); ?>" autocomplete="off"/>

					<!-- List / Grid View -->
					<div class="woowgallery-view-switch view-switch">
						<a @click.prevent="view = 'grid'" href="#" class="view-grid" :class="{current: (view === 'grid')}"><span class="screen-reader-text"><?php esc_html_e( 'Grid View', 'woowgallery' ); ?></span></a>
						<a @click.prevent="view = 'list'" href="#" class="view-list" :class="{current: (view === 'list')}"><span class="screen-reader-text"><?php esc_html_e( 'List View', 'woowgallery' ); ?></span></a>
						<input type="hidden" name="_woowgallery[editor][view]" :value="view"/>
					</div>
				</div>
			</div>

			<nav class="woowgallery-options">
				<label class="woowgallery-select-all">
					<input type="checkbox" :checked="selected.length" @click="toggleSelectAll()"/>
					<template v-if="!selected.length">
						<?php esc_html_e( 'Select All', 'woowgallery' ); ?> (<span class="woowgallery-count">{{ gallery.length }}</span>)
					</template>
					<template v-else>
						<?php esc_html_e( 'Selected', 'woowgallery' ); ?> (<span class="woowgallery-count">{{ selected.length }} / {{ gallery.length }}</span>)
					</template>
				</label>

				<!-- Bulk Edit / Delete Buttons -->
				<div class="woowgallery-select-options" v-if="selected.length">
					<div class="woowgallery-label"><?php esc_html_e( 'Select Action:', 'woowgallery' ); ?>&nbsp;</div>
					<?php do_action( 'woowgallery_bulk_actions', $data['post'] ); ?>
					<a @click.prevent="bulkEditSet()" v-if="'gallery' == woowgallery_type" href="#" class="button woowgallery-media-bulk-edit" :class="{disabled: (selected_types.length > 1)}" :title="(selected_types.length > 1)? '<?php echo esc_js( __( 'Can\'t bulk edit multiple types of media', 'wgdt' ) ); ?>' : null"><?php esc_html_e( 'Bulk Edit', 'woowgallery' ); ?></a>
					<a @click="removeSelectedItems($event)" href="#" class="button button-danger woowgallery-media-delete" data-confirm="<?php esc_attr_e( 'Are you sure you want to remove selected items from the gallery?', 'woowgallery' ); ?>"><?php esc_html_e( 'Delete from Gallery', 'woowgallery' ); ?></a>
				</div>
			</nav>

			<!-- Pagination -->
			<div class="woowgallery-simple-pager" v-if="pages > 1">
				<div class="woowgallery-label"><?php esc_html_e( 'Pages:', 'woowgallery' ); ?></div>
				<div class="woowgallery-btn-group">
					<span class="woowgallery-btn-page" v-for="n in pages" :class="{current: (page === n)}" @click="page = n" @mouseenter="sortableMoveToPage(n)">{{ n }}</span>
				</div>

			</div>

			<div class="woowgallery-attachments-wrapper">
				<ul id="woowgallery-sortable-list" class="woowgallery-media-output attachments" :key="'sortable' + sortingHash" :data-view="view" :class="{'has-selected': selected.length, 'sorting-allowed': !sortableDisabled}" v-sortable="sortable()">
					<li class="attachment" v-for="(item, i) in gallery_paged" :key="i + '-' + item.id" :class="{selected: isSelected(item.id), 'selected-last': (item.id === selected_last)}" :data-id="item.id" v-if="!(sortingPageChanged && item.id == sortingElementID)">
						<div class="woowgallery-checkbox">
							<button type="button" class="check" @click="toggleSelectItem(item.id, $event)" tabindex="-1">
								<span class="media-modal-icon"></span>
								<span class="screen-reader-text"><?php esc_html_e( 'Deselect', 'woowgallery' ); ?></span>
							</button>
						</div>
						<template>
							<div class="attachment-preview sort-handle" :class="['type-' + item.type, 'subtype-' + item.subtype, 'status-' + item.status]">
								<div class="thumbnail" @mouseup.left.exact="toggleSelectItem(item.id, $event)" @mouseup.left.shift.exact="selectItemsTo(item.id, $event)">
									<img :src="item.thumb[0]" :alt="item.alt" :class="{icon: item.thumb[4]}"/>
								</div>
								<div class="additional-preview-data">
									<div class="item-gallery-count" v-if="'<?php echo esc_js( Posttypes::GALLERY_POSTTYPE ); ?>' === item.subtype">{{ item.count }}</div>
									<div class="item-posttype-icon" v-else v-html="subtypeIcon(item)"></div>
								</div>
							</div>
							<div class="actions">
								<template v-if="item.has_password">
									<a v-if="'private' === item.status" @click="setStatus(item.id, 'publish', $event)" href="#" class="dashicons dashicons-shield-alt woowgallery-item-status" data-status="private" title="<?php esc_attr_e( 'Status: Only for logged in users', 'woowgallery' ); ?>"></a>
									<a v-else-if="'publish' === item.status" @click="setStatus(item.id, 'private', $event)" href="#" class="dashicons dashicons-shield woowgallery-item-status" data-status="publish" title="<?php esc_attr_e( 'Status: Visible for all', 'woowgallery' ); ?>"></a>
								</template>
								<template v-else>
									<a v-if="'private' === item.status" @click="setStatus(item.id, 'publish', $event)" href="#" class="dashicons dashicons-lock woowgallery-item-status" data-status="private" title="<?php esc_attr_e( 'Status: Only for logged in users', 'woowgallery' ); ?>"></a>
									<a v-else-if="'publish' === item.status" @click="setStatus(item.id, 'private', $event)" href="#" class="dashicons dashicons-unlock woowgallery-item-status" data-status="publish" title="<?php esc_attr_e( 'Status: Visible for all', 'woowgallery' ); ?>"></a>
								</template>
								<template v-if="'post' === item.type">
									<span v-if="'future' === item.status" class="dashicons dashicons-clock woowgallery-item-status" data-status="future" title="<?php esc_attr_e( 'Status: Scheduled', 'woowgallery' ); ?>"></span>
									<span v-else-if="'draft' === item.status || 'pending' === item.status" class="dashicons dashicons-sos woowgallery-item-status" :data-status="item.status" title="<?php esc_attr_e( 'Status: Draft/Pending', 'woowgallery' ); ?>"></span>
								</template>
								<a @click="editItemSet(item, $event)" href="#" class="dashicons dashicons-edit woowgallery-edit-media" title="<?php esc_attr_e( 'Edit Data', 'woowgallery' ); ?>"></a>
								<a @click="removeItem(item.id, $event)" href="#" class="dashicons dashicons-trash woowgallery-remove-media" title="<?php esc_attr_e( 'Remove from Gallery', 'woowgallery' ); ?>" data-confirm="<?php esc_attr_e( 'Are you sure you want to remove this item from the gallery?', 'woowgallery' ); ?>"></a>
								<div class="more"><?php do_action( 'woowgallery_item_more_actions', $data['post'] ); ?></div>
							</div>
							<div class="meta">
								<div class="title" :title="item.title">{{ item.title }} &nbsp;</div>
								<div class="more" v-if="view === 'list'">
									<div class="caption">{{ item.caption }}</div>
									<div class="meta-link" v-if="item.link.url">
										<?php esc_html_e( 'Link:', 'woowgallery' ); ?> <span v-if="item.link.text">[{{ item.link.text }}]</span> <a target="_blank" :class="['target-' + item.link.target]" :href="item.link.url">{{ item.link.url }}</a>
									</div>
									<div class="tags" v-if="item.tags">
										<?php esc_html_e( 'Tags:', 'woowgallery' ); ?> {{ item.tags | formatTags }}
									</div>
									<div class="addons"><?php do_action( 'woowgallery_item_more_meta', $data['post'] ); ?></div>
								</div>
							</div>
						</template>
					</li>
				</ul>
			</div>

		</div>

		<?php
		Admin::load_template( 'gallery-media-edit', $data );
		if ( Posttypes::GALLERY_POSTTYPE === $data['post']->post_type ) {
			Admin::load_template( 'gallery-media-bulk-edit', $data );
		}
		?>
	</div>

	<textarea autocomplete="off" name="post_content_filtered" id="woowgallery-data" aria-hidden="true"><?php echo esc_attr( $data['post']->post_content_filtered ); ?></textarea>

<?php
wp_nonce_field( 'ajax', '_nonce_woowgallery_ajax', false );
