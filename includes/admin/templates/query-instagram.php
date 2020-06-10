<?php
/**
 * Outputs the Instagram Query Builder.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

?>
<div class="woowgallery-query-builder instagram-query-builder">
	<div class="form-group field-multiselect">
		<label for="wgd-ig-sources"><?php esc_html_e( 'Instagram Sources', 'woowgallery' ); ?></label>
		<div class="field-wrap">
			<div class="wrapper">
				<vue-multiselect
					id="wgd-ig-sources"
					v-model="instagram.sources"
					:options="[]"
					:multiple="true"
					:taggable="true"
					:show-no-results="false"
					placeholder=""
					tag-placeholder="<?php esc_attr_e( 'Press enter to add source', 'woowgallery' ); ?>"
					@tag="addSource"
				>
					<template slot="placeholder"><?php esc_attr_e( '@username, #hashtag', 'woowgallery' ); ?></template>
					<template slot="noOptions"><?php esc_attr_e( 'Type @username or #hashtag', 'woowgallery' ); ?></template>
				</vue-multiselect>
			</div>
		</div>
		<div class="hint" v-show="hints"><?php esc_html_e( 'Set any combination of Instagram @username, #hashtag. Avoid using many sources, because it will slow down the loading speed of the feed.', 'woowgallery' ); ?>
			<br/><?php esc_html_e( 'Note: videos and carousels will be skipped when you set #hashtag as a source.', 'woowgallery' ); ?></div>
	</div>

	<div class="form-group field-mixed">
		<label><?php esc_html_e( 'ordered by', 'woowgallery' ); ?></label>
		<div class="field-wrap">
			<div class="wrapper">
				<select class="form-control" v-model="instagram.sorting">
					<option value="date"><?php echo esc_html_x( 'publication date', 'ordered by:', 'woowgallery' ); ?></option>
					<option value="source"><?php echo esc_html_x( 'source list position', 'ordered by:', 'woowgallery' ); ?></option>
				</select>
			</div>
		</div>
		<div class="hint" v-show="hints"><?php esc_html_e( 'Set the display order for Instagram posts in your feed. Publication date displays them chronologically in the order they were published on Instagram. Source list position displays the posts according to the order the sources were added in.', 'woowgallery' ); ?></div>
	</div>

	<div class="form-group field-text">
		<label for="wgd-limit">
			<select class="as-label" v-model="instagram.limit_type">
				<option value="all"><?php esc_html_e( 'limit ALL sources result to', 'woowgallery' ); ?></option>
				<option value="each"><?php esc_html_e( 'limit EACH source result to', 'woowgallery' ); ?></option>
			</select>
		</label>
		<div class="field-wrap">
			<div class="wrapper">
				<input type="number" id="wgd-limit" class="form-control" min="1" max="100" placeholder="<?php esc_attr_e( 'Maximum: 100', 'woowgallery' ); ?>" v-model="instagram.limit"/>
			</div>
		</div>
		<div class="hint" v-show="hints"><?php esc_html_e( 'Set the required number to restrict the count of loaded posts. You can choose to limit result of all sources or for each source separately. Maximum: 100.', 'woowgallery' ); ?></div>
	</div>

	<hr/>
	<div class="wg-fetch">
		<span class="spinner" :class="{'is-active': loading}"></span>
		<button type="button" class="button button-primary" @click.prevent="wp_fetchQuery()"><?php esc_html_e( 'Fetch Gallery Data', 'woowgallery' ); ?></button>
	</div>

	<?php woowgallery_is_premium_feature(); ?>
</div>
