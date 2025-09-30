<?php
/**
 * Outputs the WP Query Builder.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

$_post_types   = woowgallery_get_post_types();
$wg_post_types = [];
foreach ( $_post_types as $_pt ) {
	$wg_post_types[] = [
		'name'         => $_pt->name,
		'label'        => $_pt->label,
		'hierarchical' => $_pt->hierarchical,
	];
}

$_authors   = get_users(
	[
		'orderby'    => 'display_name',
		'order'      => 'ASC',
		'fields'     => [ 'ID', 'display_name' ],
		'capability' => 'edit_posts',
	]
);
$wg_authors = [];
foreach ( $_authors as $_a ) {
	$wg_authors[] = [
		'id'   => $_a->ID,
		'name' => $_a->display_name,
	];
}

$wg_orderby = [
	[
		'label' => __( 'sorted by Date', 'woowgallery' ),
		'value' => 'date',
	],
	[
		'label' => __( 'sorted by ID', 'woowgallery' ),
		'value' => 'ID',
	],
	[
		'label' => __( 'sorted by Author', 'woowgallery' ),
		'value' => 'author',
	],
	[
		'label' => __( 'sorted by Title', 'woowgallery' ),
		'value' => 'title',
	],
	[
		'label' => __( 'sorted by Menu Order', 'woowgallery' ),
		'value' => 'menu_order',
	],
	[
		'label' => __( 'randomly sorted', 'woowgallery' ),
		'value' => 'rand',
	],
	[
		'label' => __( 'sorted by Comment Count', 'woowgallery' ),
		'value' => 'comment_count',
	],
	[
		'label' => __( 'sorted by Post Name', 'woowgallery' ),
		'value' => 'name',
	],
	[
		'label' => __( 'sorted by Modified Date', 'woowgallery' ),
		'value' => 'modified',
	],
	[
		'label' => __( 'sorted by Meta Value', 'woowgallery' ),
		'value' => 'meta_value',
	],
	[
		'label' => __( 'sorted by Meta Value (Numeric)', 'woowgallery' ),
		'value' => 'meta_value_num',
	],
];

$wg_post_status = [
	[
		'label' => __( 'Publish', 'woowgallery' ),
		'value' => 'publish',
	],
	[
		'label' => __( 'Private', 'woowgallery' ),
		'value' => 'private',
	],
	[
		'label' => __( 'Scheduled', 'woowgallery' ),
		'value' => 'future',
	],
];

$wg_meta_value = [
	[
		'label' => __( 'Meta value EXISTS', 'woowgallery' ),
		'value' => 'EXISTS',
	],
	[
		'label' => __( 'Meta value NOT EXISTS', 'woowgallery' ),
		'value' => 'NOT EXISTS',
	],
	[
		'label' => __( 'Meta value equal', 'woowgallery' ),
		'value' => '=',
	],
	[
		'label' => __( 'Meta value not equal', 'woowgallery' ),
		'value' => '!=',
	],
	[
		'label' => __( 'Meta value greater than', 'woowgallery' ),
		'value' => '>',
	],
	[
		'label' => __( 'Meta value less than', 'woowgallery' ),
		'value' => '<',
	],
	[
		'label' => __( 'Meta value LIKE', 'woowgallery' ),
		'value' => 'LIKE',
	],
	[
		'label' => __( 'Meta value NOT LIKE', 'woowgallery' ),
		'value' => 'NOT LIKE',
	],
];
?>
<div class="woowgallery-query-builder wordpress-query-builder">
	<div class="form-group field-multiselect">
		<label for="wgd-post_type"><?php esc_html_e( 'Post Type(s)', 'woowgallery' ); ?></label>
		<div class="field-wrap">
			<div class="wrapper">
				<vue-multiselect
					id="wgd-post_type"
					v-model="wp.post_type"
					:options="<?php echo esc_js( wp_json_encode( $wg_post_types ) ); ?>"
					:multiple="true"
					:searchable="false"
					placeholder="<?php echo esc_attr_x( 'any', 'Post Types', 'woowgallery' ); ?>"
					label="label"
					track-by="name"
					:preselect-first="true"
				></vue-multiselect>
			</div>
		</div>
		<div class="hint" v-show="hints"><?php esc_html_e( 'Determines the post types to query.', 'woowgallery' ); ?></div>
	</div>

	<div class="form-group field-multiselect">
		<label for="wgd-author"><?php esc_html_e( 'written by', 'woowgallery' ); ?></label>
		<div class="field-wrap">
			<div class="wrapper">
				<vue-multiselect
					id="wgd-author"
					v-model="wp.post_author"
					:options="<?php echo esc_js( wp_json_encode( $wg_authors ) ); ?>"
					:multiple="true"
					:searchable="true"
					placeholder="<?php esc_attr_e( 'Type to search...', 'woowgallery' ); ?>"
					label="name"
					track-by="id"
				>
					<template slot="selection" slot-scope="{ values, remove }">
						<template v-for="value, index in values">
							<span class="multiselect__sep" v-if="index">or</span>
							<span class="multiselect__tag"><span>{{ value.name }}</span> <i aria-hidden="true" tabindex="1" class="multiselect__tag-icon" @mousedown.prevent="remove(value)"></i></span>
						</template>
					</template>
					<template slot="placeholder"><?php echo esc_attr_x( 'anyone', 'written by', 'woowgallery' ); ?></template>
				</vue-multiselect>
			</div>
		</div>
		<div class="hint" v-show="hints"><?php esc_html_e( 'Determines authors of queried Posts.', 'woowgallery' ); ?></div>
	</div>

	<div class="form-group field-mixed">
		<label>
			<select v-model="wp.order" :disabled="'rand' === wp.orderby">
				<option value="DESC"><?php esc_attr_e( 'in descending order', 'woowgallery' ); ?></option>
				<option value="ASC"><?php esc_attr_e( 'in ascending order', 'woowgallery' ); ?></option>
			</select>
		</label>
		<div class="field-wrap">
			<div class="wrapper">
				<select class="form-control" v-model="wp.orderby">
					<?php
					foreach ( $wg_orderby as $ob ) {
						echo '<option value="' . esc_attr( $ob['value'] ) . '">' . esc_html( $ob['label'] ) . '</option>';
					}
					?>
				</select>
			</div>
		</div>
		<div class="hint" v-show="hints"><?php esc_html_e( 'Determines how the posts are sorted in the gallery.', 'woowgallery' ); ?></div>
	</div>

	<div class="form-group field-multiselect">
		<label>
			<select class="as-label" v-model="wp.terms_relation">
				<option value="IN"><?php esc_html_e( 'with ANY selected Taxonomy Terms', 'woowgallery' ); ?></option>
				<option value="AND"><?php esc_html_e( 'with ALL selected Taxonomy Terms', 'woowgallery' ); ?></option>
			</select>
		</label>
		<div class="field-wrap">
			<div class="wrapper">
				<vue-multiselect
					id="wgd-taxterms"
					v-model="wp.taxonomy_terms"
					:options="wp_taxonomy_terms_options.data"
					:loading="wp_taxonomy_terms_options.loading"
					:disabled="!wp_taxonomy_terms_options.data.length"
					:multiple="true"
					:searchable="true"
					group-label="taxonomy"
					group-values="terms"
					:group-select="true"
					placeholder="<?php esc_attr_e( 'Type to search...', 'woowgallery' ); ?>"
					label="name"
					track-by="id"
				>
					<template slot="tag" slot-scope="{ option, remove }">
						<span class="multiselect__tag" :class="{'ms_tag_missed': wp_taxtermMissed(option.id)}"><span class="ms_taxname">{{ option.taxlabel }}:</span> <span class="ms_termname">{{ option.name }}</span> <i aria-hidden="true" tabindex="1" class="multiselect__tag-icon" @mousedown.prevent="remove(option)"></i></span>
					</template>
					<template slot="placeholder"><?php esc_attr_e( 'no selected terms', 'woowgallery' ); ?></template>
				</vue-multiselect>
			</div>
		</div>
		<div class="hint" v-show="hints"><?php esc_html_e( 'Determines whether all or any of chosen taxonomy terms must be present in the above Posts.', 'woowgallery' ); ?></div>
	</div>

	<div class="form-group field-text">
		<label for="wgd-limit"><?php esc_html_e( 'limit result to', 'woowgallery' ); ?></label>
		<div class="field-wrap">
			<div class="wrapper">
				<input type="number" id="wgd-limit" class="form-control" min="0" v-model="wp.limit"/>
			</div>
		</div>
		<div class="hint" v-show="hints"><?php esc_html_e( 'Set the required number to restrict the count of loaded posts. Leave this option `0` to show all available posts.', 'woowgallery' ); ?></div>
	</div>

	<div class="form-group field-text">
		<label for="wgd-offset"><?php esc_html_e( 'with offset', 'woowgallery' ); ?></label>
		<div class="field-wrap">
			<div class="wrapper">
				<input type="number" id="wgd-offset" class="form-control" min="0" v-model="wp.offset"/>
			</div>
		</div>
		<div class="hint" v-show="hints"><?php esc_html_e( 'The number of posts to offset in the query.', 'woowgallery' ); ?></div>
	</div>

	<hr/>
	<h4>Other criterias to meet your needs:</h4>

	<div class="form-group field-checkbox">
		<label for="wgd-ignore_sticky"><?php esc_html_e( 'Ignore Sticky Posts', 'woowgallery' ); ?></label>
		<div class="field-wrap">
			<div class="wrapper">
				<label class="wg-toggle" :class="{'is-checked': wp.ignore_sticky}">
					<input type="checkbox" id="wgd-ignore_sticky" v-model="wp.ignore_sticky"/>
					<span class="wg-toggle__track"></span>
					<span class="wg-toggle__thumb"></span>
				</label>
			</div>
		</div>
		<div class="hint" v-show="hints"><?php esc_html_e( 'If disabled, any Posts that are marked as Sticky will be at the start of the resultset.', 'woowgallery' ); ?></div>
	</div>

	<div class="form-group field-text">
		<label for="wgd-post_status"><?php esc_html_e( 'Post status', 'woowgallery' ); ?></label>
		<div class="field-wrap">
			<div class="wrapper">
				<vue-multiselect
					id="wgd-post_status"
					v-model="wp.post_status"
					:options="<?php echo esc_js( wp_json_encode( $wg_post_status ) ); ?>"
					:multiple="true"
					:searchable="false"
					placeholder="<?php esc_attr_e( 'Publish', 'woowgallery' ); ?>"
					label="label"
					track-by="value"
				></vue-multiselect>
			</div>
		</div>
		<div class="hint" v-show="hints"><?php esc_html_e( 'Note: Private posts will be visible in gallery only for logged in users.', 'woowgallery' ); ?></div>
	</div>

	<div class="form-group field-checkbox">
		<label><?php esc_html_e( 'Password Protected', 'woowgallery' ); ?></label>
		<div class="field-wrap">
			<div class="wrapper">
				<div class="wg-radio-group">
					<input type="radio" id="wg-password-off" value="" v-model="wp.has_password">
					<label for="wg-password-off"><?php echo esc_html_x( 'Off', 'Password Protected:', 'woowgallery' ); ?></label>
					<input type="radio" id="wg-password-no" value="0" v-model="wp.has_password">
					<label for="wg-password-no"><?php echo esc_html_x( 'No', 'Password Protected:', 'woowgallery' ); ?></label>
					<input type="radio" id="wg-password-yes" value="1" v-model="wp.has_password">
					<label for="wg-password-yes"><?php echo esc_html_x( 'Yes', 'Password Protected:', 'woowgallery' ); ?></label>
				</div>
			</div>
		</div>
		<div class="hint" v-show="hints"><?php esc_html_e( 'Off - query all Posts; No - query Posts without password; Yes - query Posts with password.', 'woowgallery' ); ?></div>
	</div>

	<div class="form-group field-text" v-show="'1' === wp.has_password">
		<label for="wgd-post_password"><?php esc_html_e( 'Post password', 'woowgallery' ); ?></label>
		<div class="field-wrap">
			<div class="wrapper">
				<input type="text" id="wgd-post_password" class="form-control" v-model="wp.post_password" placeholder="<?php esc_attr_e( 'Leave empty for any password', 'woowgallery' ); ?>"/>
			</div>
		</div>
		<div class="hint" v-show="hints"><?php esc_html_e( 'You can specify to query Posts with particular password.', 'woowgallery' ); ?></div>
	</div>

	<div class="form-group field-text">
		<label for="wgd-meta_key"><?php esc_html_e( 'Meta key', 'woowgallery' ); ?></label>
		<div class="field-wrap">
			<div class="wrapper">
				<input type="text" id="wgd-meta_key" class="form-control" v-model="wp.meta_key" :required="'meta_value' === wp.orderby || 'meta_value_num' === wp.orderby"/>
			</div>
		</div>
		<div class="hint" v-show="hints"><?php esc_html_e( 'Query Posts with specific meta key. Also can be used for ordering Posts (when Sorted by Meta Value).', 'woowgallery' ); ?></div>
	</div>

	<div class="form-group field-text" v-show="'' !== wp.meta_key">
		<label>
			<select v-model="wp.meta_compare">
				<?php
				foreach ( $wg_meta_value as $mv ) {
					echo '<option value="' . esc_attr( $mv['value'] ) . '">' . esc_html( $mv['label'] ) . '</option>';
				}
				?>
			</select>
		</label>
		<div class="field-wrap">
			<div class="wrapper">
				<input :type="'meta_value_num' === wp.orderby ? 'number' : 'text'" id="wgd-meta_value" class="form-control" v-model="wp.meta_value" :disabled="'EXISTS' === wp.meta_compare || 'NOT EXISTS' === wp.meta_compare"/>
			</div>
		</div>
	</div>

	<div class="form-group field-text">
		<label for="wgd-post_parent"><?php esc_html_e( 'Parent Page ID', 'woowgallery' ); ?></label>
		<div class="field-wrap">
			<div class="wrapper">
				<input type="text" id="wgd-post_parent" class="form-control" v-model="wp.post_parent"/>
			</div>
		</div>
		<div class="hint" v-show="hints"><?php echo wp_kses( __( 'Use page id to return only child pages. Set to <code>0</code> to return only top-level entries.', 'woowgallery' ), '' ); ?></div>
	</div>

	<div class="form-group field-text">
		<label for="wgd-exclude"><?php esc_html_e( 'Exclude Post IDs', 'woowgallery' ); ?></label>
		<div class="field-wrap">
			<div class="wrapper">
				<input type="text" id="wgd-exclude" class="form-control" v-model="wp.post__not_in"/>
			</div>
		</div>
		<div class="hint" v-show="hints"><?php esc_html_e( 'Comma separated list of Post IDs, which you need to exclude from Gallery.', 'woowgallery' ); ?></div>
	</div>

	<hr/>
	<div class="wg-fetch">
		<span class="spinner" :class="{'is-active': loading}"></span>
		<button type="button" class="button button-primary" @click.prevent="wp_fetchQuery()"><?php esc_html_e( 'Fetch Gallery Data', 'woowgallery' ); ?></button>
	</div>
</div>
