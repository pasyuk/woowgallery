<?php
/**
 * Outputs the Gallery Query Builder.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

use WoowGallery\Admin\Admin;
use WoowGallery\Admin\Notice;
use WoowGallery\Gallery;

/**
 * Template vars
 *
 * @var array $data
 */
$wg      = Gallery::get_instance( $data['post']->ID, $data['post']->post_type );
$gallery = $wg->get_gallery();
if ( empty( $gallery['data']['post_type'] ) ) {
	$gallery_post_types = [ 'post' ];
} else {
	$gallery_post_types = wp_list_pluck( $gallery['data']['post_type'], 'name' ) ?: [ 'post' ];
}
$wg_taxonomies = woowgallery_get_taxonomy_terms( $gallery_post_types );

$wg_plugin_source = [
	'flagallery' => is_plugin_active( 'flash-album-gallery/flag.php' ),
];
$wg_query_type    = ! empty( $gallery['data']['query_type'] ) ? $gallery['data']['query_type'] : 'wp';
?>
	<script type="text/javascript">
		<?php echo 'var wp_taxonomy_terms_options = "' . wp_slash( wp_json_encode( $wg_taxonomies ) ) . '";'; ?>
	</script>

	<div id="woowgallery-dynamic-query" class="woowgallery-dynamic-query">
		<!-- Title and Help -->
		<div class="woowgallery-intro">
			<h3>
				<?php esc_html_e( 'Build a Gallery that displays...', 'woowgallery' ); ?>
				<span class="wg-hints dashicons dashicons-editor-help" :class="{'show-hints': hints}" @click="hints = !hints" title="<?php esc_attr_e( 'Show/Hide hints.', 'woowgallery' ); ?>"></span>
			</h3>

			<div class="wg-radio-group">
				<input type="radio" id="wgd-query-type-wp" value="wp" v-model="query_type">
				<label for="wgd-query-type-wp"><?php echo esc_html__( 'WordPress', 'woowgallery' ); ?></label>
				<!--<input type="radio" id="wgd-query-type-ig" value="instagram" v-model="query_type">
				<label for="wgd-query-type-ig"><?php //echo esc_html__( 'Instagram', 'woowgallery' ); ?></label>-->
				<?php if ( $wg_plugin_source['flagallery'] || 'flagallery' === $wg_query_type ) { ?>
					<input type="radio" id="wgd-query-type-flagallery" value="flagallery" v-model="query_type">
					<label for="wgd-query-type-flagallery"><?php echo esc_html__( 'Flagallery', 'woowgallery' ); ?></label>
				<?php } ?>
			</div>
		</div>

		<template v-if="'wp' === query_type">
			<?php Admin::load_template( 'query-wp' ); ?>
		</template>

		<!--<template v-else-if="'instagram' === query_type">
			<?php //Admin::load_template( 'query-instagram' ); ?>
		</template>-->

		<?php if ( $wg_plugin_source['flagallery'] || 'flagallery' === $wg_query_type ) { ?>
			<template v-else-if="'flagallery' === query_type">
				<?php
				if ( $wg_plugin_source['flagallery'] ) {
					Admin::load_template( 'query-flagallery' );
				} else {
					$notice = __( 'Flagallery plugin was deactivated and it is not valid anymore as a mediafiles source :(', 'woowgallery' );
					$notice .= "\n" . __( 'Please, switch to another source for Dynamic Gallery.', 'woowgallery' );
					Notice::display_notice( nl2br( $notice ) );
					echo '<p class="error-message">' . nl2br( esc_html( $notice ) ) . '</p>';
				}
				?>
			</template>
		<?php } ?>

		<div class="woowgallery-preview grid" v-if="query_type" v-cloak>
			<div class="woowgallery-error-message" v-if="error">{{ error }}</div>
			<div class="woowgallery-content-images" v-if="gallery">
				<!-- Title and Help -->
				<div class="woowgallery-intro">
					<h3>
						<?php
						esc_html_e( 'Currently in your Gallery', 'woowgallery' );
						// translators: %s: number of posts.
						echo ': ' . esc_html( sprintf( __( 'found posts - %s', 'woowgallery' ), '{{ gallery.post_count }}' ) );
						?>
					</h3>
				</div>

				<!-- Pagination -->
				<div class="woowgallery-simple-pager" v-if="pages > 1">
					<div class="woowgallery-label"><?php esc_html_e( 'Pages:', 'woowgallery' ); ?></div>
					<div class="woowgallery-btn-group">
						<span class="woowgallery-btn-page" v-for="n in pages" :class="{current: (page === n)}" @click="page = n">{{ n }}</span>
					</div>
				</div>

				<div class="woowgallery-attachments-wrapper">
					<ul class="woowgallery-media-output attachments">
						<li class="attachment" v-for="item in gallery_paged" :key="item.id">
							<div class="attachment-preview" :class="['type-' + item.type, 'subtype-' + item.subtype]">
								<div class="thumbnail" @click="viewItemSet(item, $event)">
									<img :src="itemThumbnail(item)" :alt="item.alt" :class="{icon: item.thumb[4]}"/>
								</div>
								<div class="additional-preview-data">
									<div class="item-posttype-icon" v-html="subtypeIcon(item)"></div>
								</div>
							</div>
							<template v-if="'wp' === query_type">
								<div class="actions">
									<span v-if="'future' === item.status" class="dashicons dashicons-clock woowgallery-item-status" data-status="future" title="<?php esc_attr_e( 'Status: Scheduled', 'woowgallery' ); ?>"></span>
									<span v-else-if="item.has_password" class="dashicons dashicons-shield woowgallery-item-status" data-status="protected" title="<?php esc_attr_e( 'Status: Password Protected', 'woowgallery' ); ?>"></span>
									<span v-else-if="'publish' === item.status" class="dashicons dashicons-unlock woowgallery-item-status" data-status="publish" title="<?php esc_attr_e( 'Status: Visible for all', 'woowgallery' ); ?>"></span>
									<span v-else-if="'private' === item.status" class="dashicons dashicons-lock woowgallery-item-status" data-status="private" title="<?php esc_attr_e( 'Status: Only for logged in users with editor rights', 'woowgallery' ); ?>"></span>
									<a :href="item.edit_link" target="_blank" class="dashicons dashicons-edit woowgallery-edit-media" :class="{'woowgallery-disabled': !item.edit_link}" title="<?php esc_attr_e( 'Edit Post', 'woowgallery' ); ?>"></a>
									<a @click="removeItem(item.id, $event)" href="#" class="dashicons dashicons-trash woowgallery-remove-media" title="<?php esc_attr_e( 'Exclude from Query', 'woowgallery' ); ?>" data-confirm="<?php esc_attr_e( 'Confirm you want to exclude this post from the query.', 'woowgallery' ); ?>"></a>
									<div class="more"><?php do_action( 'woowgallery_dynamic_wp_item_more_actions', $data['post'] ); ?></div>
								</div>
								<div class="meta">
									<div class="title" :title="item.title">#{{ item.id }}: {{ item.title || '-' }}</div>
								</div>
							</template>
							<template v-else-if="'instagram' !== query_type">
								<div class="actions">
									<span v-if="'private' === item.status" class="dashicons dashicons-lock woowgallery-item-status" data-status="private" title="<?php esc_attr_e( 'Status: Only for logged in users with editor rights', 'woowgallery' ); ?>"></span>
								</div>
								<div class="meta">
									<div class="title" :title="item.title">{{ item.title }} &nbsp;</div>
								</div>
							</template>
						</li>
					</ul>
				</div>
			</div>
		</div>

		<?php Admin::load_template( 'gallery-media-view', $data ); ?>

	</div>

	<textarea autocomplete="off" name="post_content_filtered" id="woowgallery-data" aria-hidden="true"><?php echo esc_attr( $data['post']->post_content_filtered ); ?></textarea>

<?php
wp_nonce_field( 'ajax', '_nonce_woowgallery_ajax', false );
