<?php
/**
 * Outputs the Gallery Query Builder.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

use WoowGallery\Admin\Admin;
use WoowGallery\Gallery;

/**
 * Template vars
 *
 * @var array $data
 */
$wg      = new Gallery( $data['post']->ID, $data['post']->post_type );
$gallery = $wg->get_gallery();
if ( empty( $gallery['data']['post_type'] ) ) {
	$gallery_post_types = [ 'post' ];
} else {
	$gallery_post_types = wp_list_pluck( $gallery['data']['post_type'], 'name' ) ?: [ 'post' ];
}

$_post_types   = get_post_types( [ 'public' => true ], 'objects', 'and' );
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
		'orderby' => 'display_name',
		'order'   => 'ASC',
		'fields'  => [ 'ID', 'display_name' ],
		'who'     => 'authors',
	]
);
$wg_authors = [];
foreach ( $_authors as $_a ) {
	$wg_authors[] = [
		'id'   => $_a->ID,
		'name' => $_a->display_name,
	];
}

$wg_taxonomies = woowgallery_get_taxonomy_terms( $gallery_post_types );

$wg_orderby = [
	[
		'label' => _x( 'sorted by Date', 'wgtd' ),
		'value' => 'date',
	],
	[
		'label' => _x( 'sorted by ID', 'wgtd' ),
		'value' => 'ID',
	],
	[
		'label' => _x( 'sorted by Author', 'wgtd' ),
		'value' => 'author',
	],
	[
		'label' => _x( 'sorted by Title', 'wgtd' ),
		'value' => 'title',
	],
	[
		'label' => _x( 'sorted by Menu Order', 'wgtd' ),
		'value' => 'menu_order',
	],
	[
		'label' => _x( 'randomly sorted', 'wgtd' ),
		'value' => 'rand',
	],
	[
		'label' => _x( 'sorted by Comment Count', 'wgtd' ),
		'value' => 'comment_count',
	],
	[
		'label' => _x( 'sorted by Post Name', 'wgtd' ),
		'value' => 'name',
	],
	[
		'label' => _x( 'sorted by Modified Date', 'wgtd' ),
		'value' => 'modified',
	],
	[
		'label' => _x( 'sorted by Meta Value', 'wgtd' ),
		'value' => 'meta_value',
	],
	[
		'label' => _x( 'sorted by Meta Value (Numeric)', 'wgtd' ),
		'value' => 'meta_value_num',
	],
];

$wg_post_status = [
	[
		'label' => __( 'Publish', 'wgtd' ),
		'value' => 'publish',
	],
	[
		'label' => __( 'Private', 'wgtd' ),
		'value' => 'private',
	],
	[
		'label' => __( 'Scheduled', 'wgtd' ),
		'value' => 'future',
	],
];

$wg_meta_value = [
	[
		'label' => __( 'Meta value EXISTS', 'wgtd' ),
		'value' => 'EXISTS',
	],
	[
		'label' => __( 'Meta value NOT EXISTS', 'wgtd' ),
		'value' => 'NOT EXISTS',
	],
	[
		'label' => __( 'Meta value equal', 'wgtd' ),
		'value' => '=',
	],
	[
		'label' => __( 'Meta value not equal', 'wgtd' ),
		'value' => '!=',
	],
	[
		'label' => __( 'Meta value greater than', 'wgtd' ),
		'value' => '>',
	],
	[
		'label' => __( 'Meta value less than', 'wgtd' ),
		'value' => '<',
	],
	[
		'label' => __( 'Meta value LIKE', 'wgtd' ),
		'value' => 'LIKE',
	],
	[
		'label' => __( 'Meta value NOT LIKE', 'wgtd' ),
		'value' => 'NOT LIKE',
	],
];
?>
	<script type="text/javascript">
		<?php echo 'var wp_taxonomy_terms_options = "' . wp_slash( wp_json_encode( $wg_taxonomies ) ) . '";'; ?>
	</script>

	<div id="woowgallery-dynamic-query" class="woowgallery-dynamic-query">
		<!-- Title and Help -->
		<div class="woowgallery-intro">
			<h3>
				<?php esc_html_e( 'Build a Gallery that displays...', 'wgtd' ); ?>
				<span class="wg-hints dashicons dashicons-editor-help" :class="{'show-hints': hints}" @click="hints = !hints" title="<?php esc_attr_e( 'Show/Hide hints.', 'wgtd' ); ?>"></span>
			</h3>

			<div class="wg-radio-group">
				<input type="radio" id="wgd-query-type-wp" value="wp" v-model="query_type">
				<label for="wgd-query-type-wp"><?php echo esc_html__( 'WordPress', 'wgtd' ); ?></label>
				<input type="radio" id="wgd-query-type-ig" value="instagram" v-model="query_type">
				<label for="wgd-query-type-ig"><?php echo esc_html__( 'Instagram', 'wgtd' ); ?></label>
			</div>
		</div>

		<template v-if="'wp' === query_type">
			<div class="woowgallery-query-builder wordpress-query-builder">
				<div class="form-group field-multiselect">
					<label for="wgd-post_type"><?php esc_html_e( 'Post Type(s)', 'wgtd' ); ?></label>
					<div class="field-wrap">
						<div class="wrapper">
							<vue-multiselect
								id="wgd-post_type"
								v-model="wp.post_type"
								:options="<?php echo esc_js( wp_json_encode( $wg_post_types ) ); ?>"
								:multiple="true"
								:searchable="false"
								placeholder="<?php echo esc_attr_x( 'any', 'Post Type', 'wgtd' ); ?>"
								label="label"
								track-by="name"
								:preselect-first="true"
							></vue-multiselect>
						</div>
					</div>
					<div class="hint" v-show="hints"><?php esc_html_e( 'Determines the post types to query.', 'wgtd' ); ?></div>
				</div>

				<div class="form-group field-multiselect">
					<label for="wgd-author"><?php esc_html_e( 'written by', 'wgtd' ); ?></label>
					<div class="field-wrap">
						<div class="wrapper">
							<vue-multiselect
								id="wgd-author"
								v-model="wp.post_author"
								:options="<?php echo esc_js( wp_json_encode( $wg_authors ) ); ?>"
								:multiple="true"
								:searchable="true"
								placeholder="<?php esc_attr_e( 'Type to search...', 'wgtd' ); ?>"
								label="name"
								track-by="id"
							>
								<template slot="selection" slot-scope="{ values, remove }">
									<template v-for="value, index in values">
										<span class="multiselect__sep" v-if="index">or</span>
										<span class="multiselect__tag"><span>{{ value.name }}</span> <i aria-hidden="true" tabindex="1" class="multiselect__tag-icon" @mousedown.prevent="remove(value)"></i></span>
									</template>
								</template>
								<template slot="placeholder"><?php echo esc_attr_x( 'anyone', 'written by', 'wgtd' ); ?></template>
							</vue-multiselect>
						</div>
					</div>
					<div class="hint" v-show="hints"><?php esc_html_e( 'Determines authors of queried Posts.', 'wgtd' ); ?></div>
				</div>

				<div class="form-group field-mixed">
					<label>
						<select v-model="wp.order" :disabled="'rand' === wp.orderby">
							<option value="DESC"><?php esc_attr_e( 'in descending order', 'wgtd' ); ?></option>
							<option value="ASC"><?php esc_attr_e( 'in ascending order', 'wgtd' ); ?></option>
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
					<div class="hint" v-show="hints"><?php esc_html_e( 'Determines how the posts are sorted in the gallery.', 'wgtd' ); ?></div>
				</div>

				<div class="form-group field-multiselect">
					<label>
						<select class="as-label" v-model="wp.terms_relation">
							<option value="IN"><?php esc_html_e( 'with ANY selected Taxonomy Terms', 'wgtd' ); ?></option>
							<option value="AND"><?php esc_html_e( 'with ALL selected Taxonomy Terms', 'wgtd' ); ?></option>
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
								placeholder="<?php esc_attr_e( 'Type to search...', 'wgtd' ); ?>"
								label="name"
								track-by="id"
							>
								<template slot="tag" slot-scope="{ option, remove }">
									<span class="multiselect__tag" :class="{'ms_tag_missed': wp_taxtermMissed(option.id)}"><span class="ms_taxname">{{ option.taxlabel }}:</span> <span class="ms_termname">{{ option.name }}</span> <i aria-hidden="true" tabindex="1" class="multiselect__tag-icon" @mousedown.prevent="remove(option)"></i></span>
								</template>
								<template slot="placeholder"><?php esc_attr_e( 'no selected terms', 'wgtd' ); ?></template>
							</vue-multiselect>
						</div>
					</div>
					<div class="hint" v-show="hints"><?php esc_html_e( 'Determines whether all or any of chosen taxonomy terms must be present in the above Posts.', 'wgtd' ); ?></div>
				</div>

				<div class="form-group field-text">
					<label for="wgd-limit"><?php esc_html_e( 'limit result to', 'wgtd' ); ?></label>
					<div class="field-wrap">
						<div class="wrapper">
							<input type="number" id="wgd-limit" class="form-control" v-model="wp.limit"/>
						</div>
					</div>
					<div class="hint" v-show="hints"><?php esc_html_e( 'Set the required number to restrict the count of loaded posts. Leave this option `0` to show all available posts.', 'wgtd' ); ?></div>
				</div>

				<div class="form-group field-text">
					<label for="wgd-offset"><?php esc_html_e( 'with offset', 'wgtd' ); ?></label>
					<div class="field-wrap">
						<div class="wrapper">
							<input type="number" id="wgd-offset" class="form-control" v-model="wp.offset"/>
						</div>
					</div>
					<div class="hint" v-show="hints"><?php esc_html_e( 'The number of posts to offset in the query.', 'wgtd' ); ?></div>
				</div>

				<hr/>
				<h4>Other criterias to meet your needs:</h4>

				<div class="form-group field-checkbox">
					<label for="wgd-ignore_sticky"><?php esc_html_e( 'Ignore Sticky Posts', 'wgtd' ); ?></label>
					<div class="field-wrap">
						<div class="wrapper">
							<label class="wg-toggle" :class="{'is-checked': wp.ignore_sticky}">
								<input type="checkbox" id="wgd-ignore_sticky" v-model="wp.ignore_sticky"/>
								<span class="wg-toggle__track"></span>
								<span class="wg-toggle__thumb"></span>
							</label>
						</div>
					</div>
					<div class="hint" v-show="hints"><?php esc_html_e( 'If disabled, any Posts that are marked as Sticky will be at the start of the resultset.', 'wgtd' ); ?></div>
				</div>

				<div class="form-group field-text">
					<label for="wgd-post_status"><?php esc_html_e( 'Post status', 'wgtd' ); ?></label>
					<div class="field-wrap">
						<div class="wrapper">
							<vue-multiselect
								id="wgd-post_status"
								v-model="wp.post_status"
								:options="<?php echo esc_js( wp_json_encode( $wg_post_status ) ); ?>"
								:multiple="true"
								:searchable="false"
								placeholder="<?php esc_attr_e( 'Publish', 'wgtd' ); ?>"
								label="label"
								track-by="value"
							></vue-multiselect>
						</div>
					</div>
					<div class="hint" v-show="hints"><?php esc_html_e( 'Note: Private posts will be visible in gallery only for logged in users.', 'wgtd' ); ?></div>
				</div>

				<div class="form-group field-checkbox">
					<label><?php esc_html_e( 'Password Protected', 'wgtd' ); ?></label>
					<div class="field-wrap">
						<div class="wrapper">
							<div class="wg-radio-group">
								<input type="radio" id="wg-password-off" value="" v-model="wp.has_password">
								<label for="wg-password-off"><?php echo esc_html_x( 'Off', 'Password Protected:', 'wgtd' ); ?></label>
								<input type="radio" id="wg-password-no" value="0" v-model="wp.has_password">
								<label for="wg-password-no"><?php echo esc_html_x( 'No', 'Password Protected:', 'wgtd' ); ?></label>
								<input type="radio" id="wg-password-yes" value="1" v-model="wp.has_password">
								<label for="wg-password-yes"><?php echo esc_html_x( 'Yes', 'Password Protected:', 'wgtd' ); ?></label>
							</div>
						</div>
					</div>
					<div class="hint" v-show="hints"><?php esc_html_e( 'Off - query all Posts; No - query Posts without password; Yes - query Posts with password. <br />Note: Private Posts can\'t be password protected.', 'wgtd' ); ?></div>
				</div>

				<div class="form-group field-text" v-show="'1' === wp.has_password">
					<label for="wgd-post_password"><?php esc_html_e( 'Post password', 'wgtd' ); ?></label>
					<div class="field-wrap">
						<div class="wrapper">
							<input type="text" id="wgd-post_password" class="form-control" v-model="wp.post_password" placeholder="<?php esc_attr_e( 'Leave empty for any passsword', 'wgtd' ); ?>"/>
						</div>
					</div>
					<div class="hint" v-show="hints"><?php esc_html_e( 'You can specify to query Posts with particular password.', 'wgtd' ); ?></div>
				</div>

				<div class="form-group field-text">
					<label for="wgd-meta_key"><?php esc_html_e( 'Meta key', 'wgtd' ); ?></label>
					<div class="field-wrap">
						<div class="wrapper">
							<input type="text" id="wgd-meta_key" class="form-control" v-model="wp.meta_key" :required="'meta_value' === wp.orderby || 'meta_value_num' === wp.orderby"/>
						</div>
					</div>
					<div class="hint" v-show="hints"><?php esc_html_e( 'Query Posts with specific meta key. Also can be used for ordering Posts (when Sorted by Meta Value).', 'wgtd' ); ?></div>
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
					<label for="wgd-exclude"><?php esc_html_e( 'Exclude Post IDs', 'wgtd' ); ?></label>
					<div class="field-wrap">
						<div class="wrapper">
							<input type="text" id="wgd-exclude" class="form-control" v-model="wp.post__not_in"/>
						</div>
					</div>
					<div class="hint" v-show="hints"><?php esc_html_e( 'Comma separated list of Post IDs, which you need to exclude from Gallery.', 'wgtd' ); ?></div>
				</div>
			</div>
		</template>

		<template v-else-if="'instagram' === query_type">
			<div class="woowgallery-query-builder instagram-query-builder">
				<div class="form-group field-multiselect">
					<label for="wgd-ig-sources"><?php esc_html_e( 'Instagram Sources', 'wgtd' ); ?></label>
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
								tag-placeholder="<?php esc_attr_e( 'Press enter to add source', 'wgtd' ); ?>"
								@tag="addSource"
							>
								<template slot="placeholder"><?php esc_attr_e( '@username, #hashtag', 'wgtd' ); ?></template>
								<template slot="noOptions"><?php esc_attr_e( 'Type @username or #hastag', 'wgtd' ); ?></template>
							</vue-multiselect>
						</div>
					</div>
					<div class="hint" v-show="hints"><?php esc_html_e( 'Set any combination of Instagram @username, #hashtag. Avoid using many sources, because it will slow down the loading speed of the feed.', 'wgtd' ); ?></div>
				</div>

				<div class="form-group field-mixed">
					<label><?php esc_html_e( 'ordered by', 'wgtd' ); ?></label>
					<div class="field-wrap">
						<div class="wrapper">
							<select class="form-control" v-model="instagram.sorting">
								<option value="date"><?php echo esc_html_x( 'publication date', 'ordered by:', 'wgtd' ); ?></option>
								<option value="source"><?php echo esc_html_x( 'source list position', 'ordered by:', 'wgtd' ); ?></option>
							</select>
						</div>
					</div>
					<div class="hint" v-show="hints"><?php esc_html_e( 'Set the display order for Instagram posts in your feed. Publication date displays them chronologically in the order they were published on Instagram. Source list position displays the posts according to the order the sources were added in.', 'wgtd' ); ?></div>
				</div>

				<div class="form-group field-text">
					<label for="wgd-limit">
						<select class="as-label" v-model="instagram.limit_type">
							<option value="all"><?php esc_html_e( 'limit ALL sources result to', 'wgtd' ); ?></option>
							<option value="each"><?php esc_html_e( 'limit EACH source result to', 'wgtd' ); ?></option>
						</select>
					</label>
					<div class="field-wrap">
						<div class="wrapper">
							<input type="number" id="wgd-limit" class="form-control" min="1" max="100" placeholder="<?php esc_attr_e( 'Maximum: 100', 'wgtd' ); ?>" v-model="instagram.limit"/>
						</div>
					</div>
					<div class="hint" v-show="hints"><?php esc_html_e( 'Set the required number to restrict the count of loaded posts. You can choose to limit result of all sources or for each source separately. Maximum: 100.', 'wgtd' ); ?></div>
				</div>

			</div>
		</template>

		<div class="woowgallery-preview grid" v-if="query_type" v-cloak>
			<hr/>
			<div class="wg-fetch">
				<span class="spinner" :class="{'is-active': loading}"></span>
				<button type="button" class="button button-primary" @click.prevent="wp_fetchQuery()"><?php esc_html_e( 'Fetch Gallery Data', 'wgtd' ); ?></button>
			</div>
			<div class="woowgallery-error-message" v-if="error">{{ error }}</div>
			<div class="woowgallery-content-images" v-if="gallery">
				<!-- Title and Help -->
				<div class="woowgallery-intro">
					<h3>
						<?php
						esc_html_e( 'Currently in your Gallery', 'wgtd' );
						// translators: %s: number of posts.
						echo ': ' . esc_html( sprintf( __( 'found posts - %s', 'wgtd' ), '{{ gallery.post_count }}' ) );
						?>
					</h3>
				</div>

				<!-- Pagination -->
				<div class="woowgallery-simple-pager" v-if="pages > 1">
					<div class="woowgallery-label"><?php esc_html_e( 'Pages:', 'wgtd' ); ?></div>
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
									<div class="item-posttype-icon" v-if="'post' === item.type" v-html="subtypeIcon(item)"></div>
								</div>
							</div>
							<template v-if="'instagram' !== query_type">
								<div class="actions">
									<span v-if="item.has_password" class="dashicons dashicons-shield woowgallery-item-status" data-status="protected" title="<?php esc_attr_e( 'Status: Password Protected', 'wgtd' ); ?>"></span>
									<span v-else-if="'private' === item.status" class="dashicons dashicons-lock woowgallery-item-status" data-status="private" title="<?php esc_attr_e( 'Status: Only for logged in users with editor rights', 'wgtd' ); ?>"></span>
									<span v-else-if="'publish' === item.status" class="dashicons dashicons-unlock woowgallery-item-status" data-status="publish" title="<?php esc_attr_e( 'Status: Visible for all', 'wgtd' ); ?>"></span>
									<a :href="item.edit_link" target="_blank" class="dashicons dashicons-edit woowgallery-edit-media" :class="{'woowgallery-disabled': !item.edit_link}" title="<?php esc_attr_e( 'Edit Post', 'wgtd' ); ?>"></a>
									<a @click="removeItem(item.id, $event)" href="#" class="dashicons dashicons-trash woowgallery-remove-media" title="<?php esc_attr_e( 'Exclude from Query', 'wgtd' ); ?>" data-confirm="<?php esc_attr_e( 'Confirm you want to exclude this post from the query.', 'wgtd' ); ?>"></a>
									<div class="more"><?php do_action( 'woowgallery_dynamic_item_more_actions', $data['post'] ); ?></div>
								</div>
								<div class="meta">
									<div class="title" :title="item.title">#{{ item.id }}: {{ item.title }}</div>
								</div>
							</template>
						</li>
					</ul>
				</div>
			</div>
		</div>

		<?php Admin::load_template( 'gallery-media-view', $data ); ?>

	</div>

	<textarea autocomplete="off" style="width: 100%; display:block;" name="post_content_filtered" id="woowgallery-data" aria-hidden="true"><?php echo esc_attr( $data['post']->post_content_filtered ); ?></textarea>

<?php
wp_nonce_field( 'ajax', '_nonce_woowgallery_ajax', false );
