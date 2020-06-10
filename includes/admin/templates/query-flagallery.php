<?php
/**
 * Outputs the WP Query Builder.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

$wg_orderby = [
	[
		'label' => __( 'sorted by default', 'woowgallery' ),
		'value' => 'sortorder',
	],
	[
		'label' => __( 'sorted by Date', 'woowgallery' ),
		'value' => 'imagedate',
	],
	[
		'label' => __( 'sorted by ID', 'woowgallery' ),
		'value' => 'pid',
	],
	[
		'label' => __( 'sorted by Title', 'woowgallery' ),
		'value' => 'alttext',
	],
	[
		'label' => __( 'sorted by filename', 'woowgallery' ),
		'value' => 'filename',
	],
	[
		'label' => __( 'randomly sorted', 'woowgallery' ),
		'value' => 'rand()',
	],
];
?>
<div class="woowgallery-query-builder flagallery-query-builder">
	<div class="form-group field-multiselect">
		<label for="wgd-flagallery"><?php esc_html_e( 'Gallery', 'woowgallery' ); ?></label>
		<div class="field-wrap">
			<div class="wrapper">
				<vue-multiselect
					id="wgd-flagallery"
					:value="flagallery.source"
					@select="flagallerySourceSelect"
					:options="flagallery_source_options.data"
					:loading="flagallery_source_options.loading"
					:disabled="!flagallery_source_options.data.length"
					:multiple="false"
					:searchable="true"
					placeholder="<?php esc_attr_e( 'Type to search...', 'woowgallery' ); ?>"
					deselect-label="<?php esc_attr_e( 'Selected', 'woowgallery' ); ?>"
					label="title"
					track-by="gid"
				>
					<template slot="placeholder"><?php esc_attr_e( 'no selected gallery', 'woowgallery' ); ?></template>
					<template slot="option" slot-scope="{ option }">{{ option.title }} <span class="badge">{{ option.counter }}</span></template>
				</vue-multiselect>
			</div>
		</div>
		<div class="hint" v-show="hints"><?php esc_html_e( 'Choose gallery folder from Flagallery plugin.', 'woowgallery' ); ?></div>
	</div>

	<div class="form-group field-mixed">
		<label>
			<select v-model="flagallery.order" :disabled="'rand()' === flagallery.orderby">
				<option value="ASC"><?php esc_attr_e( 'in ascending order', 'woowgallery' ); ?></option>
				<option value="DESC"><?php esc_attr_e( 'in descending order', 'woowgallery' ); ?></option>
			</select>
		</label>
		<div class="field-wrap">
			<div class="wrapper">
				<select class="form-control" v-model="flagallery.orderby">
					<?php
					foreach ( $wg_orderby as $ob ) {
						echo '<option value="' . esc_attr( $ob['value'] ) . '">' . esc_html( $ob['label'] ) . '</option>';
					}
					?>
				</select>
			</div>
		</div>
		<div class="hint" v-show="hints"><?php esc_html_e( 'Determines how the images are sorted in the gallery.', 'woowgallery' ); ?></div>
	</div>

	<hr/>
	<div class="wg-fetch">
		<span class="spinner" :class="{'is-active': loading}"></span>
		<button type="button" class="button button-primary" @click.prevent="wp_fetchQuery()"><?php esc_html_e( 'Fetch Gallery Data', 'woowgallery' ); ?></button>
	</div>
</div>
