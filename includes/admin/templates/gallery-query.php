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
$wg      = Gallery::get_instance( $data['post']->ID, $data['post']->post_type );
$gallery = $wg->get_gallery();
if ( empty( $gallery['data']['post_type'] ) ) {
	$gallery_post_types = [ 'post' ];
} else {
	$gallery_post_types = wp_list_pluck( $gallery['data']['post_type'], 'name' ) ?: [ 'post' ];
}

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
				<input type="radio" id="wgd-query-type-ig" value="instagram" v-model="query_type">
				<label for="wgd-query-type-ig"><?php echo esc_html__( 'Instagram', 'woowgallery' ); ?></label>
			</div>
		</div>

		<template v-if="'wp' === query_type">
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
		</template>

		<template v-else-if="'instagram' === query_type">
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
		</template>

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

	<textarea autocomplete="off" name="post_content_filtered" id="woowgallery-data" aria-hidden="true"><?php echo esc_attr( $data['post']->post_content_filtered ); ?></textarea>

<?php
wp_nonce_field( 'ajax', '_nonce_woowgallery_ajax', false );
