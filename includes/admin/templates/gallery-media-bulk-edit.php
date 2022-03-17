<?php
/**
 * The template for bulk editing items details.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

/**
 * Template vars
 *
 * @var array $data
 */
?>
	<mounting-portal mount-to="#woowgallery-portal" name="woowgallery-bulk-edit-items" v-if="bulkEdit" v-cloak append>
		<template>
			<div tabindex="0" class="upload-php media-modal wp-core-ui woowgallery-bulk-edit-modal">
				<button @click.prevent="bulkEditClose()" type="button" class="media-modal-close"><span class="media-modal-icon"><span class="screen-reader-text"><?php esc_html_e( 'Close media panel', 'woowgallery' ); ?></span></span></button>
				<div class="media-modal-content">
					<div class="edit-attachment-frame mode-select hide-menu hide-router">
						<div class="media-frame-title">
							<h1><?php esc_html_e( 'Bulk Edit Items Data', 'woowgallery' ); ?></h1>
						</div>
						<div class="media-frame-content">
							<div class="attachment-details">
								<!-- Left -->
								<div class="attachment-media-view woowgallery-preview woowgallery-selected-preview grid">
									<div class="woowgallery-attachments-wrapper">
										<ul class="attachments">
											<li class="attachment" v-for="(item, i) in bulkEditItems" :key="i + '-' + item.id">
												<div class="attachment-preview" :class="['type-' + item.type, 'subtype-' + item.subtype]">
													<div class="thumbnail">
														<img :src="item.thumb[0]" alt="" :class="{icon: item.thumb[4]}"/>
													</div>
													<div class="additional-preview-data">
														<div class="item-posttype-icon" v-html="subtypeIcon(item)"></div>
													</div>
												</div>
												<div class="actions visible">
													<a v-if="'private' === item.status" @click="setStatus(item.id, 'publish', $event)" href="#" class="dashicons dashicons-lock woowgallery-item-status" data-status="private" title="<?php esc_attr_e( 'Status: Only for logged in users', 'woowgallery' ); ?>"></a>
													<a v-else-if="'publish' === item.status" @click="setStatus(item.id, 'private', $event)" href="#" class="dashicons dashicons-unlock woowgallery-item-status" data-status="publish" title="<?php esc_attr_e( 'Status: Visible for all', 'woowgallery' ); ?>"></a>
													<a @click="removeItem(item.id, $event)" href="#" class="dashicons dashicons-trash woowgallery-remove-media" title="<?php esc_attr_e( 'Remove from Gallery', 'woowgallery' ); ?>" data-confirm="<?php esc_attr_e( 'Are you sure you want to remove this item from the gallery?', 'woowgallery' ); ?>"></a>
												</div>
												<div class="meta">
													<div class="title" :title="item.title">{{ item.title }}</div>
												</div>
											</li>
										</ul>
									</div>
								</div>

								<!-- Right -->
								<div class="attachment-info">
									<!-- Settings -->
									<div class="settings">
										<!-- Status -->
										<div class="woowgallery-setting">
											<label for="item-status"><?php esc_html_e( 'Status', 'woowgallery' ); ?></label>
											<select id="item-status" v-model="bulkEdit.status">
												<option value=""><?php esc_html_e( 'Do not change', 'woowgallery' ); ?></option>
												<option value="private"><?php esc_html_e( 'Private', 'woowgallery' ); ?></option>
												<option value="publish"><?php esc_html_e( 'Publish', 'woowgallery' ); ?></option>
											</select>
											<div class="description"><?php esc_html_e( 'Controls whether this individual item is Private or Published within the Gallery.', 'woowgallery' ); ?></div>
										</div>

										<!-- Image Title -->
										<div class="woowgallery-setting">
											<label for="item-title"><?php esc_html_e( 'Title', 'woowgallery' ); ?></label>
											<input type="text" id="item-title" v-model="bulkEdit.title" :disabled="!bulkEdit.title_src || 'custom' !== bulkEdit.title_src"/>
											<div class="wg-clearfix">
												<label class="switcher"><?php esc_html_e( 'Source:', 'woowgallery' ); ?>
													<select id="item-title-src" v-model="bulkEdit.title_src">
														<option value=""><?php esc_html_e( 'Do not change', 'woowgallery' ); ?></option>
														<option value="title" v-text="('post' === bulkEditType)? '<?php echo esc_js( __( 'Post Title', 'woowgallery' ) ); ?>' : '<?php echo esc_js( __( 'Media Title', 'woowgallery' ) ); ?>'"></option>
														<option value="custom"><?php esc_html_e( 'Custom Title', 'woowgallery' ); ?></option>
													</select>
												</label>
											</div>
										</div>

										<!-- Caption -->
										<div class="woowgallery-setting">
											<label for="item-caption"><?php esc_html_e( 'Caption', 'woowgallery' ); ?></label>
											<div id="wp-item-caption-wrap" class="wp-core-ui wp-editor-wrap html-active">
												<div id="wp-item-caption-editor-container">
													<div id="qt_item-caption_toolbar" class="quicktags-toolbar wp-editor-container" :class="{'woowgallery-disabled': (!bulkEdit.caption_src || 'custom' !== bulkEdit.caption_src)}"></div>
													<textarea class="woowgallery-caption-editor wp-editor-area" style="height: 100px" cols="40" id="item-caption" v-model="bulkEdit.caption" :disabled="!bulkEdit.caption_src || 'custom' !== bulkEdit.caption_src"></textarea>
												</div>
											</div>
											<div class="wg-clearfix">
												<label class="switcher"><?php esc_html_e( 'Source:', 'woowgallery' ); ?>
													<select id="item-title-src" v-model="bulkEdit.caption_src">
														<option value=""><?php esc_html_e( 'Do not change', 'woowgallery' ); ?></option>
														<template v-if="'attachment' === bulkEditType">
															<option value="caption"><?php esc_html_e( 'Media Caption', 'woowgallery' ); ?></option>
															<option value="description"><?php esc_html_e( 'Media Description', 'woowgallery' ); ?></option>
														</template>
														<template v-if="'post' === bulkEditType">
															<option value="excerpt"><?php esc_html_e( 'Post Excerpt', 'woowgallery' ); ?></option>
														</template>
														<option value="custom"><?php esc_html_e( 'Custom Caption', 'woowgallery' ); ?></option>
													</select>
												</label>
												<div class="description"><?php esc_html_e( 'HTML is accepted for formatting text in the Custom Caption source.', 'woowgallery' ); ?></div>
											</div>
										</div>

										<!-- Alt Text -->
										<div class="woowgallery-setting" v-if="'post' !== bulkEditType">
											<label for="item-alt"><?php esc_html_e( 'Alt Text', 'woowgallery' ); ?></label>
											<input type="text" id="item-alt" v-model="bulkEdit.alt" :disabled="!bulkEdit.alt_src || 'custom' !== bulkEdit.alt_src"/>
											<div class="wg-clearfix">
												<label class="switcher"><?php esc_html_e( 'Source:', 'woowgallery' ); ?>
													<select id="item-alt-src" v-model="bulkEdit.alt_src">
														<option value=""><?php esc_html_e( 'Do not change', 'woowgallery' ); ?></option>
														<template v-if="'attachment' === bulkEditType">
															<option value="alt"><?php esc_html_e( 'Media Alt', 'woowgallery' ); ?></option>
														</template>
														<option value="title"><?php esc_html_e( 'Same as Title', 'woowgallery' ); ?></option>
														<option value="custom"><?php esc_html_e( 'Custom Alt', 'woowgallery' ); ?></option>
													</select>
												</label>
												<div class="description"><?php esc_html_e( 'Very important for SEO, the Alt Text describes the image.', 'woowgallery' ); ?></div>
											</div>
										</div>

										<!-- Link -->
										<div class="woowgallery-setting">
											<div class="woowgallery-flex space-between">
												<label><?php esc_html_e( 'Link', 'woowgallery' ); ?></label>
												<div class="buttons textright" v-if="'attachment' === bulkEditType">
													<button v-if="bulkEdit.link.url_change" class="button button-small choose-link" data-woowgallery-custom-link="item-link-url"><?php esc_html_e( 'Choose Link', 'woowgallery' ); ?></button>
												</div>
											</div>

											<template v-if="'attachment' === bulkEditType">
												<div class="woowgallery-flex sub-setting">
													<label class="inline-label" for="item-link-url"><?php esc_html_e( 'URL:', 'woowgallery' ); ?></label>
													<input type="text" id="item-link-url" v-model="bulkEdit.link.url" :disabled="!bulkEdit.link.url_change">
													<input type="checkbox" v-model="bulkEdit.link.url_change"/>
												</div>
												<div class="description"><?php esc_html_e( 'Enter a hyperlink if you wish to link each of these items to somewhere other than its original file.', 'woowgallery' ); ?></div>
											</template>

											<div class="woowgallery-flex sub-setting">
												<label class="inline-label" for="item-link-text"><?php esc_html_e( 'Text:', 'woowgallery' ); ?></label>
												<input type="text" id="item-link-text" v-model="bulkEdit.link.text" :disabled="!bulkEdit.link.text_change">
												<input type="checkbox" v-model="bulkEdit.link.text_change"/>
											</div>
											<div class="description"><?php esc_html_e( 'Enter a text for link button (optional).', 'woowgallery' ); ?></div>

											<div class="woowgallery-flex sub-setting">
												<label class="inline-label" for="item-link-target"><?php esc_html_e( 'Target:', 'woowgallery' ); ?></label>
												<select id="item-link-target" v-model="bulkEdit.link.target">
													<option value=""><?php esc_html_e( 'Do not change', 'woowgallery' ); ?></option>
													<option value="_self"><?php esc_html_e( 'Same Window', 'woowgallery' ); ?></option>
													<option value="_blank"><?php esc_html_e( 'New Tab', 'woowgallery' ); ?></option>
												</select>
											</div>
											<div class="description"><?php esc_html_e( 'Opens link in a same browser window or in a new tab.', 'woowgallery' ); ?></div>
										</div>

										<!-- Copyright -->
										<div class="woowgallery-setting" v-if="'attachment' === bulkEditType">
											<label for="item-copyright"><?php esc_html_e( 'Copyright', 'woowgallery' ); ?></label>
											<input type="text" id="item-copyright" v-model="bulkEdit.copyright" />
											<div class="description"><?php esc_html_e( 'Leave empty for no change or white space to clear Copyright on selected media.', 'woowgallery' ); ?></div>
										</div>

										<div class="woowgallery-setting">
											<div class="tagsdiv" :id="bulkEditTagsTaxonomy()">
												<div class="jaxtag">
													<div class="nojs-tags hide-if-js">
														<label :for="'tax-input-' + bulkEditTagsTaxonomy()">Add or remove tags</label>
														<textarea v-model="bulkEdit.tags" rows="3" cols="20" class="the-tags" :id="'tax-input-' + bulkEditTagsTaxonomy()"></textarea>
													</div>
													<div class="ajaxtag hide-if-no-js">
														<label :for="'new-tag-' + bulkEditTagsTaxonomy()">Add New Tag</label>
														<div class="woowgallery-flex">
															<input :data-wp-taxonomy="bulkEditTagsTaxonomy()" type="text" :id="'new-tag-' + bulkEditTagsTaxonomy()" class="newtag form-input-tip" autocomplete="off" value=""/>
															<input type="button" class="button tagadd" value="<?php esc_attr_e( 'Add' ); ?>"/>
														</div>
													</div>
													<div class="description">
														<?php esc_html_e( 'Enter a comma separated list of Tags that you want to be added to these items.', 'woowgallery' ); ?><br/>
														<template v-if="bulkEditTagsTaxonomy(true)">
															<?php esc_html_e( 'Note: tags will be applied for these items in all galleries.', 'woowgallery' ); ?>
														</template>
													</div>
												</div>
												<ul class="tagchecklist" role="list"></ul>
											</div>
											<div class="hide-if-no-js">
												<button type="button" class="button-link tagcloud-link" :id="'link-' + bulkEditTagsTaxonomy()" aria-expanded="false">Choose from the most used tags</button>
											</div>
										</div>

										<!-- Addons can populate the UI here -->
										<div class="woowgallery-addons"></div>
									</div>
									<!-- /.settings -->

									<!-- Actions -->
									<div class="actions">
										<span class="spinner" :class="{'is-active': bulkEditSaving}"></span>
										<button @click="bulkEditSave()" class="button button-primary woowgallery-bulk-save"><?php esc_html_e( 'Save Changes', 'woowgallery' ); ?></button>
									</div>
									<!-- /.actions -->
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="media-modal-backdrop" @click="bulkEditClose()"></div>
		</template>
	</mounting-portal>

<?php
