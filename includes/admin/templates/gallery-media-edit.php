<?php
/**
 * The template for edit item details.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

use WoowGallery\Posttypes;

/**
 * Template vars
 *
 * @var array $data
 */
?>
	<mounting-portal mount-to="#woowgallery-portal" name="woowgallery-edit-item" v-if="editItem" v-cloak append>
		<template>
			<div tabindex="0" class="upload-php media-modal wp-core-ui">
				<button @click.prevent="editItemClose()" type="button" class="media-modal-close"><span class="media-modal-icon"><span class="screen-reader-text"><?php esc_html_e( 'Close media panel', 'woowgallery' ); ?></span></span></button>
				<div class="media-modal-content">
					<div class="edit-attachment-frame mode-select hide-menu hide-router">
						<div class="edit-media-header">
							<button @click.prevent="editItemSet(editItemPrev)" class="left dashicons" :class="{'woowgallery-disabled': !editItemPrev}"><span class="screen-reader-text"><?php esc_html_e( 'Edit previous media item', 'woowgallery' ); ?></span></button>
							<button @click.prevent="editItemSet(editItemNext)" class="right dashicons" :class="{'woowgallery-disabled': !editItemNext}"><span class="screen-reader-text"><?php esc_html_e( 'Edit next media item', 'woowgallery' ); ?></span></button>
						</div>
						<div class="media-frame-title">
							<h1>
								<?php
								if ( Posttypes::ALBUM_POSTTYPE === $data['post']->post_type ) {
									esc_html_e( 'Edit Gallery Data', 'woowgallery' );
								} else {
									esc_html_e( 'Edit Item Data', 'woowgallery' );
								}
								?>
							</h1>
						</div>
						<div class="media-frame-content">
							<div class="attachment-details">
								<!-- Left -->
								<div class="attachment-media-view">
									<div class="preview">
										<video v-if="'video' === editItem.subtype" class="details-video" :src="itemOriginalSrc(editItem)" controls :poster="itemImage(editItem)[0]"></video>
										<div v-else-if="'audio' === editItem.subtype" class="details-audio">
											<img class="wg-audio-cover" :src="itemImage(editItem)[0]" draggable="false"/>
											<audio class="wg-audio-player" :src="itemOriginalSrc(editItem)" controls></audio>
										</div>
										<img v-else class="details-image" :src="itemImage(editItem)[0]" draggable="false"/>
									</div>
									<div class="additional-preview-data">
										<div class="item-gallery-count" v-if="'<?php echo esc_js( Posttypes::GALLERY_POSTTYPE ); ?>' === editItem.subtype">{{ itemGalleryCount(editItem) }}</div>
										<div class="item-posttype-icon" v-else v-html="subtypeIcon(editItem)"></div>
									</div>
								</div>

								<!-- Right -->
								<div class="attachment-info">
									<!-- Settings -->
									<div class="settings">
										<!-- Status -->
										<div class="woowgallery-setting">
											<label for="item-status"><?php esc_html_e( 'Status', 'woowgallery' ); ?></label>
											<span v-if="editItem.has_password" class="item-has-password"><?php esc_html_e( '(password protected)', 'woowgallery' ); ?></span>
											<template v-if="'publish' === editItem.status || 'private' === editItem.status">
												<select id="item-status" v-model="editItem.status">
													<option value="private"><?php esc_attr_e( 'Private', 'woowgallery' ); ?></option>
													<option value="publish"><?php esc_attr_e( 'Publish', 'woowgallery' ); ?></option>
												</select>
												<div class="description">
													<?php
													if ( Posttypes::ALBUM_POSTTYPE === $data['post']->post_type ) {
														esc_html_e( 'Controls whether this individual gallery is Private or Published within the Album.', 'woowgallery' );
													} else {
														esc_html_e( 'Controls whether this individual item is Private or Published within the Gallery.', 'woowgallery' );
													}
													?>
												</div>
											</template>
											<template v-else>
												<input v-if="'future' === editItem.status" type="text" value="<?php esc_attr_e( 'Scheduled', 'woowgallery' ); ?>" readonly/>
												<input v-else-if="'draft' === editItem.status || 'pending' === editItem.status" type="text" value="<?php esc_attr_e( 'Draft / Pending', 'woowgallery' ); ?>" readonly/>
											</template>
										</div>

										<!-- Image Title -->
										<div class="woowgallery-setting">
											<label for="item-title"><?php esc_html_e( 'Title', 'woowgallery' ); ?></label>
											<input v-if="'custom' !== editItem.title_src" :key="editItem.title_src" type="text" id="item-title" :value="itemTitle(editItem)" readonly/>
											<input v-else :key="editItem.title_src" type="text" id="item-title" v-model="editItem.title"/>
											<div class="wg-clearfix">
												<label class="switcher"><?php esc_html_e( 'Source:', 'woowgallery' ); ?>
													<select id="item-title-src" v-model="editItem.title_src">
														<?php if ( Posttypes::ALBUM_POSTTYPE === $data['post']->post_type ) { ?>
															<option value="title"><?php esc_attr_e( 'Gallery Title', 'woowgallery' ); ?></option>
														<?php } else { ?>
															<option value="title" v-text="('post' === editItem.type)? '<?php echo esc_js( __( 'Post Title', 'woowgallery' ) ); ?>' : '<?php echo esc_js( __( 'Media Title', 'woowgallery' ) ); ?>'"></option>
														<?php } ?>
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
													<div id="qt_item-caption_toolbar" class="quicktags-toolbar wp-editor-container" :class="{'woowgallery-disabled': ('custom' !== editItem.caption_src)}"></div>
													<textarea v-if="'custom' !== editItem.caption_src" :key="editItem.caption_src" class="woowgallery-caption-editor wp-editor-area" style="height: 100px" cols="40" id="item-caption" readonly>{{ itemCaption(editItem) }}</textarea>
													<textarea v-else :key="editItem.caption_src" class="woowgallery-caption-editor wp-editor-area" style="height: 100px" cols="40" id="item-caption" v-model="editItem.caption"></textarea>
												</div>
											</div>
											<div class="wg-clearfix">
												<label class="switcher"><?php esc_html_e( 'Source:', 'woowgallery' ); ?>
													<select id="item-title-src" v-model="editItem.caption_src">
														<template v-if="'attachment' === editItem.type">
															<option value="caption"><?php esc_html_e( 'Media Caption', 'woowgallery' ); ?></option>
															<option value="description"><?php esc_html_e( 'Media Description', 'woowgallery' ); ?></option>
														</template>
														<template v-else-if="'post' === editItem.type">
															<?php if ( Posttypes::ALBUM_POSTTYPE === $data['post']->post_type ) { ?>
																<option value="excerpt"><?php esc_html_e( 'Gallery Description', 'woowgallery' ); ?></option>
															<?php } else { ?>
																<option value="excerpt"><?php esc_html_e( 'Post Excerpt', 'woowgallery' ); ?></option>
															<?php } ?>
														</template>
														<option value="custom"><?php esc_html_e( 'Custom Caption', 'woowgallery' ); ?></option>
													</select>
												</label>
												<div class="description"><?php esc_html_e( 'HTML is accepted for formatting text in the Custom Caption source.', 'woowgallery' ); ?></div>
											</div>
										</div>

										<?php if ( Posttypes::ALBUM_POSTTYPE !== $data['post']->post_type ) { ?>
											<!-- Alt Text -->
											<div class="woowgallery-setting" v-if="'post' !== editItem.type">
												<label for="item-alt"><?php esc_html_e( 'Alt Text', 'woowgallery' ); ?></label>
												<input v-if="'custom' !== editItem.alt_src" :key="editItem.alt_src" type="text" id="item-alt" :value="itemAlt(editItem)" readonly/>
												<input v-else :key="editItem.alt_src" type="text" id="item-alt" v-model="editItem.alt"/>
												<div class="wg-clearfix">
													<label class="switcher"><?php esc_html_e( 'Source:', 'woowgallery' ); ?>
														<select id="item-alt-src" v-model="editItem.alt_src">
															<template v-if="'attachment' === editItem.type">
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
													<div class="buttons textright" v-if="'attachment' === editItem.type">
														<button @click.prevent="editItem.link.url = editItemPageLink()" class="button button-small attachment-page"><?php esc_html_e( 'Attachment Page URL', 'woowgallery' ); ?></button>
														<button class="button button-small choose-link" data-woowgallery-custom-link="item-link-url"><?php esc_html_e( 'Choose Link', 'woowgallery' ); ?></button>
													</div>
												</div>

												<template v-if="'attachment' === editItem.type">
													<div class="woowgallery-flex sub-setting">
														<label class="inline-label" for="item-link-url"><?php esc_html_e( 'URL:', 'woowgallery' ); ?></label>
														<input type="text" id="item-link-url" v-model="editItem.link.url">
													</div>
													<div class="description"><?php esc_html_e( 'Enter a hyperlink if you wish to link this item to somewhere other than its original file', 'woowgallery' ); ?></div>
												</template>
												<template v-else-if="!!editItemPageLink()">
													<div class="woowgallery-flex sub-setting">
														<label class="inline-label" for="item-link-url"><?php esc_html_e( 'URL:', 'woowgallery' ); ?></label>
														<a class="wg-input-like" :href="editItemPageLink()" target="_blank">{{ editItemPageLink() }}</a>
													</div>
												</template>

												<template v-if="editItem.link.url || ('attachment' !== editItem.type && !!editItemPageLink())">
													<div class="woowgallery-flex sub-setting">
														<label class="inline-label" for="item-link-text"><?php esc_html_e( 'Text:', 'woowgallery' ); ?></label>
														<input type="text" id="item-link-text" v-model="editItem.link.text">
													</div>
													<div class="description"><?php esc_html_e( 'Enter a text for link button (optional).', 'woowgallery' ); ?></div>

													<div class="woowgallery-flex sub-setting">
														<label class="inline-label" for="item-link-target"><?php esc_html_e( 'Target:', 'woowgallery' ); ?></label>
														<select id="item-link-target" v-model="editItem.link.target">
															<option value="_self"><?php esc_html_e( 'Same Window', 'woowgallery' ); ?></option>
															<option value="_blank"><?php esc_html_e( 'New Tab', 'woowgallery' ); ?></option>
														</select>
													</div>
													<div class="description"><?php esc_html_e( 'Opens link in a same browser window or in a new tab.', 'woowgallery' ); ?></div>
												</template>
											</div>

											<!-- Copyright -->
											<div class="woowgallery-setting" v-if="'attachment' === editItem.type">
												<label for="item-copyright"><?php esc_html_e( 'Copyright', 'woowgallery' ); ?></label>
												<input type="text" id="item-copyright" :value="itemCopyright(editItem)" @change="editItemSetCopyright" />
												<div class="description"><?php esc_html_e( 'Can be used as protection alert message on right mouse click.', 'woowgallery' ); ?></div>
											</div>

											<!-- Tags -->
											<div class="woowgallery-setting">
												<div class="tagsdiv" :id="editItemTagsTaxonomy()">
													<div class="jaxtag">
														<div class="nojs-tags hide-if-js">
															<label :for="'tax-input-' + editItemTagsTaxonomy()">Add or remove tags</label>
															<textarea v-model="editItem.tags" rows="3" cols="20" class="the-tags" :id="'tax-input-' + editItemTagsTaxonomy()" :data-media-id="editItem.id" :data-taxonomy="editItemTagsTaxonomy(true)"></textarea>
														</div>
														<div class="ajaxtag hide-if-no-js">
															<label :for="'new-tag-' + editItemTagsTaxonomy()">Add New Tag</label>
															<div class="woowgallery-flex">
																<input :data-wp-taxonomy="editItemTagsTaxonomy()" type="text" :id="'new-tag-' + editItemTagsTaxonomy()" class="newtag form-input-tip" autocomplete="off" value=""/>
																<input type="button" class="button tagadd" value="<?php esc_attr_e( 'Add' ); ?>"/>
															</div>
														</div>
														<div class="description">
															<?php esc_html_e( 'Enter a comma separated list of Tags to apply to this item.', 'woowgallery' ); ?><br/>
															<template v-if="editItemTagsTaxonomy(true)">
																<?php esc_html_e( 'Note: tags will be applied for this item in all galleries.', 'woowgallery' ); ?>
															</template>
														</div>
													</div>
													<ul class="tagchecklist" role="list"></ul>
												</div>
												<div class="hide-if-no-js">
													<button type="button" class="button-link tagcloud-link" :id="'link-' + editItemTagsTaxonomy()" aria-expanded="false">Choose from the most used tags</button>
												</div>
											</div>
										<?php } ?>

										<!-- Addons can populate the UI here -->
										<div class="woowgallery-addons"></div>
									</div>
									<!-- /.settings -->

									<!-- Actions -->
									<div class="actions">
										<?php if ( Posttypes::ALBUM_POSTTYPE === $data['post']->post_type ) { ?>
											<span v-if="!!editItemPageLink()"><a class="view-attachment" :href="editItemPageLink()" target="_blank"><?php esc_html_e( 'View Gallery page', 'woowgallery' ); ?></a> |</span>
											<span v-if="!!itemEditLink(editItem)"><a :href="itemEditLink(editItem)" target="_blank"><?php esc_html_e( 'Edit Gallery', 'woowgallery' ); ?></a> |</span>
											<button @click="removeItem(editItem.id, $event)" class="button-link delete-attachment" data-confirm="<?php esc_attr_e( 'Are you sure you want to remove this gallery from the album?', 'woowgallery' ); ?>"><?php esc_html_e( 'Remove from Album', 'woowgallery' ); ?></button>
										<?php } else { ?>
											<template v-if="'attachment' === editItem.type">
												<a class="view-attachment" :href="editItemPageLink()" target="_blank"><?php esc_html_e( 'View attachment page', 'woowgallery' ); ?></a> |
												<a :href="'post.php?post=' + editItem.id + '&action=edit'" target="_blank"><?php esc_html_e( 'Edit more details', 'woowgallery' ); ?></a> |
											</template>
											<template v-if="'post' === editItem.type">
												<span v-if="!!editItemPageLink()"><a class="view-attachment" :href="editItemPageLink()" target="_blank"><?php esc_html_e( 'View Post page', 'woowgallery' ); ?></a> |</span>
												<span v-if="!!itemEditLink(editItem)"><a :href="itemEditLink(editItem)" target="_blank"><?php esc_html_e( 'Edit more details', 'woowgallery' ); ?></a> |</span>
											</template>
											<button @click="removeItem(editItem.id, $event)" class="button-link delete-attachment" data-confirm="<?php esc_attr_e( 'Are you sure you want to remove this item from the gallery?', 'woowgallery' ); ?>"><?php esc_html_e( 'Remove from Gallery', 'woowgallery' ); ?></button>
										<?php } ?>
									</div>
									<!-- /.actions -->
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="media-modal-backdrop" @click="editItemClose()"></div>
		</template>
	</mounting-portal>

<?php
/*
<script type="text/html" id="tmpl-woowgallery-attachment-details-two-column">
	<div class="attachment-media-view {{ data.orientation }}">
		<div class="thumbnail thumbnail-{{ data.type }}">
			<# if ( data.sizes && data.sizes.large ) { #>
			<img class="details-image" src="{{ data.sizes.large.url }}" draggable="false" alt=""/>
			<# } else if ( data.sizes && data.sizes.full ) { #>
			<img class="details-image" src="{{ data.sizes.full.url }}" draggable="false" alt=""/>
			<# } else if ( -1 === jQuery.inArray( data.type, [ 'audio', 'video' ] ) ) { #>
			<img class="details-image icon" src="{{ data.icon }}" draggable="false" alt=""/>
			<# } #>

			<# if ( 'audio' === data.type ) { #>
			<div class="wp-media-wrapper">
				<audio style="visibility: hidden" controls class="wp-audio-shortcode" width="100%" preload="none">
					<source type="{{ data.mime }}" src="{{ data.url }}"/>
				</audio>
			</div>
			<# } else if ( 'video' === data.type ) {
			var w_rule = '';
			if ( data.width ) {
			w_rule = 'width: ' + data.width + 'px;';
			} else if ( wp.media.view.settings.contentWidth ) {
			w_rule = 'width: ' + wp.media.view.settings.contentWidth + 'px;';
			}
			#>
			<div style="{{ w_rule }}" class="wp-media-wrapper wp-video">
				<video controls="controls" class="wp-video-shortcode" preload="metadata"
				<# if ( data.width ) { #>width="{{ data.width }}"<# } #>
				<# if ( data.height ) { #>height="{{ data.height }}"<# } #>
				<# if ( data.image && data.image.src !== data.icon ) { #>poster="{{ data.image.src }}"<# } #>>
				<source type="{{ data.mime }}" src="{{ data.url }}"/>
				</video>
			</div>
			<# } #>

			<div class="attachment-actions">
				<# if ( 'image' === data.type && ! data.uploading && data.sizes && data.can.save ) { #>
				<button type="button" class="button edit-attachment"><?php _e( 'Edit Image', 'woowgallery' ); ?></button>
				<# } else if ( 'pdf' === data.subtype && data.sizes ) { #>
				<?php _e( 'Document Preview', 'woowgallery' ); ?>
				<# } #>
			</div>
		</div>
	</div>
	<div class="attachment-info">
		<div class="settings">
            <span class="settings-save-status">
                <span class="spinner"></span>
                <span class="saved"><?php esc_html_e( 'Saved', 'woowgallery' ); ?></span>
            </span>
			<h2><?php _e( 'Attachment Edit Meta', 'woowgallery' ); ?></h2>

			<label class="setting" data-setting="url">
				<span class="name"><?php _e( 'URL', 'woowgallery' ); ?></span>
				<input type="text" value="{{ data.url }}" readonly/>
			</label>
			<# var maybeReadOnly = data.can.save || data.allowLocalEdits ? '' : 'readonly'; #>
			<?php if ( post_type_supports( 'attachment', 'title' ) ) : ?>
				<label class="setting" data-setting="title">
					<span class="name"><?php _e( 'Title', 'woowgallery' ); ?></span>
					<input type="text" value="{{ data.title }}" {{ maybeReadOnly }}/>
				</label>
			<?php endif; ?>
			<# if ( 'audio' === data.type ) { #>
			<?php foreach (
				array(
					'artist' => __( 'Artist', 'woowgallery' ),
					'album'  => __( 'Album', 'woowgallery' ),
				) as $key => $label
			) : ?>
				<label class="setting" data-setting="<?php echo esc_attr( $key ) ?>">
					<span class="name"><?php echo $label ?></span>
					<input type="text" value="{{ data.<?php echo $key ?> || data.meta.<?php echo $key ?> || '' }}"/>
				</label>
			<?php endforeach; ?>
			<# } #>
			<label class="setting" data-setting="caption">
				<span class="name"><?php _e( 'Caption', 'woowgallery' ); ?></span>
				<textarea {{ maybeReadOnly }}>{{ data.caption }}</textarea>
			</label>
			<# if ( 'image' === data.type ) { #>
			<label class="setting" data-setting="alt">
				<span class="name"><?php _e( 'Alt Text', 'woowgallery' ); ?></span>
				<input type="text" value="{{ data.alt }}" {{ maybeReadOnly }}/>
			</label>
			<# } #>
			<label class="setting" data-setting="description">
				<span class="name"><?php _e( 'Description', 'woowgallery' ); ?></span>
				<textarea {{ maybeReadOnly }}>{{ data.description }}</textarea>
			</label>

			<div class="display-settings"></div>
		</div>

		<div class="actions">
			<a class="view-attachment" href="{{ data.link }}" target="_blank"><?php _e( 'View attachment page', 'woowgallery' ); ?></a>
			<# if ( data.can.save ) { #> |
			<a href="post.php?post={{ data.id }}&action=edit" target="_blank"><?php _e( 'Edit more details', 'woowgallery' ); ?></a>
			<# } #> |
			<button type="button" class="button-link button-link-delete woowgallery-collection-media-delete"><?php _e( 'Delete from Collection', 'woowgallery' ); ?></button>
		</div>

	</div>
</script>
*/
