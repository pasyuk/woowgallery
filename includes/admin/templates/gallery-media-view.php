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
	<mounting-portal mount-to="#woowgallery-portal" name="woowgallery-view-item" v-if="viewItem" v-cloak append>
		<template>
			<div tabindex="0" class="upload-php media-modal wp-core-ui">
				<button @click.prevent="viewItemClose()" type="button" class="media-modal-close"><span class="media-modal-icon"><span class="screen-reader-text"><?php esc_html_e( 'Close media panel', 'wgtd' ); ?></span></span></button>
				<div class="media-modal-content">
					<div class="edit-attachment-frame mode-select hide-menu hide-router">
						<div class="edit-media-header">
							<button @click.prevent="viewItemSet(viewItemPrev)" class="left dashicons" :class="{disabled: !viewItemPrev}"><span class="screen-reader-text"><?php esc_html_e( 'Edit previous media item', 'wgtd' ); ?></span></button>
							<button @click.prevent="viewItemSet(viewItemNext)" class="right dashicons" :class="{disabled: !viewItemNext}"><span class="screen-reader-text"><?php esc_html_e( 'Edit next media item', 'wgtd' ); ?></span></button>
						</div>
						<div class="media-frame-title">
							<h1><?php esc_html_e( 'View Item Data', 'wgtd' ); ?></h1>
						</div>
						<div class="media-frame-content">
							<div class="attachment-details">
								<!-- Left -->
								<div class="attachment-media-view">
									<div class="preview" :class="['type-' + viewItem.type, 'subtype-' + viewItem.subtype]">
										<img v-if="'image' === viewItem.subtype" class="details-image" :src="itemImage(viewItem)[0]" />
										<video v-else-if="'video' === viewItem.subtype" class="details-video" :src="itemSrc(viewItem)" controls :poster="itemImage(viewItem)[0]"></video>
										<audio v-else-if="'audio' === viewItem.subtype" class="details-audio" :src="itemSrc(viewItem)" controls></audio>
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
									</div>
								</div>

								<!-- Right -->
								<div class="attachment-info">
									<div class="attachment-view-details">
										<h3 class="item-title" v-show="viewItem.title">{{ viewItem.title }}</h3>
										<div class="item-counters">
											<div class="item-counter likes-count" v-if="viewItem.likes && viewItem.likes.count">
												<span class="dashicons dashicons-heart"></span> {{ viewItem.likes.count }}
											</div>
											<div class="item-counter likes-count" v-if="viewItem.comments && viewItem.comments.count">
												<span class="dashicons dashicons-admin-comments"></span> {{ viewItem.comments.count }}
											</div>
										</div>
										<p class="item-location" v-if="viewItem.location && viewItem.location.name">{{ viewItem.location.name }}</p>
										<p class="item-link"><a :href="viewItem.link.url" target="_blank">{{ viewItem.link.url }}</a></p>
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
