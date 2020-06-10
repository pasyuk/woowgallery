<?php
/**
 * The template for edit item details.
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
	<mounting-portal mount-to="#woowgallery-portal" name="woowgallery-view-item" v-if="viewItem" v-cloak append>
		<template>
			<div tabindex="0" class="upload-php media-modal wp-core-ui">
				<button @click.prevent="viewItemClose()" type="button" class="media-modal-close"><span class="media-modal-icon"><span class="screen-reader-text"><?php esc_html_e( 'Close media panel', 'woowgallery' ); ?></span></span></button>
				<div class="media-modal-content">
					<div class="edit-attachment-frame mode-select hide-menu hide-router">
						<div class="edit-media-header">
							<button @click.prevent="viewItemSet(viewItemPrev)" class="left dashicons" :class="{'woowgallery-disabled': !viewItemPrev}"><span class="screen-reader-text"><?php esc_html_e( 'Previous media item', 'woowgallery' ); ?></span></button>
							<button @click.prevent="viewItemSet(viewItemNext)" class="right dashicons" :class="{'woowgallery-disabled': !viewItemNext}"><span class="screen-reader-text"><?php esc_html_e( 'Next media item', 'woowgallery' ); ?></span></button>
						</div>
						<div class="media-frame-title">
							<h1><?php esc_html_e( 'View Item Data', 'woowgallery' ); ?></h1>
						</div>
						<div class="media-frame-content">
							<div class="attachment-details">
								<!-- Left -->
								<div class="attachment-media-view">
									<div class="preview" :class="['type-' + viewItem.type, 'subtype-' + viewItem.subtype]">
										<video v-if="'video' === viewItem.subtype" class="details-video" :src="itemOriginalSrc(viewItem)" controls :poster="itemImage(viewItem)[0]"></video>
										<div v-else-if="'audio' === viewItem.subtype" class="details-audio">
											<img class="wg-audio-cover" :src="itemImage(viewItem)[0]" draggable="false"/>
											<audio class="wg-audio-player" :src="itemOriginalSrc(viewItem)" controls></audio>
										</div>
										<div v-else-if="'carousel' === viewItem.subtype" class="details-carousel">
											<img class="details-image" :src="itemImage(viewItem)[0]" />
											<div :id="'carousel-' + viewItem.id" class="swiper-container">
												<div class="swiper-wrapper">
													<div class="swiper-slide" v-for="slide in viewItem.carousel" :key="slide.id">
														<img v-if="'image' === slide.type" :src="slide.sources[0][0]"/>
														<video v-else-if="'video' === slide.type" :src="slide.src" loop controls :poster="slide.sources[0][0]"></video>
													</div>
												</div>
												<div class="swiper-pagination"></div>
												<div class="swiper-button-prev"></div>
												<div class="swiper-button-next"></div>
											</div>
										</div>
										<img v-else class="details-image" :src="itemImage(viewItem)[0]" />
									</div>
									<div class="additional-preview-data">
										<div class="item-posttype-icon" v-html="subtypeIcon(viewItem)"></div>
									</div>
								</div>

								<!-- Right -->
								<div class="attachment-info">
									<div class="attachment-view-details">
										<h3 class="item-title">{{ viewItem.title }} &nbsp;</h3>
										<div class="item-counters">
											<div class="item-counter likes-count" v-if="viewItem.likes && viewItem.likes.count">
												<span class="dashicons dashicons-heart"></span> {{ viewItem.likes.count }}
											</div>
											<div class="item-counter likes-count" v-if="viewItem.comments && viewItem.comments.count">
												<span class="dashicons dashicons-admin-comments"></span> {{ viewItem.comments.count }}
											</div>
										</div>
										<p class="item-general-type">
											<b><?php esc_html_e( 'Type:', 'woowgallery' ); ?></b> {{ viewItem.type.toUpperCase() }}
										</p>
										<p class="item-location" v-if="viewItem.location && viewItem.location.name">
											<b><?php esc_html_e( 'Location:', 'woowgallery' ); ?></b> {{ viewItem.location.name }}
										</p>
										<p class="item-link" v-if="viewItem.link.url">
											<b><?php esc_html_e( 'Link:', 'woowgallery' ); ?></b>
											<a :href="viewItem.link.url" target="_blank">{{ viewItem.link.url }}</a>
										</p>
										<p class="item-tags" v-if="viewItem.tags && viewItem.tags.length">
											<b><?php esc_html_e( 'Tags:', 'woowgallery' ); ?></b> {{ viewItem.tags | formatTags }}
										</p>
										<div class="item-caption" v-html="captionHashtags(viewItem.caption)"></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="media-modal-backdrop" @click="viewItemClose()"></div>
		</template>
	</mounting-portal>

<?php
