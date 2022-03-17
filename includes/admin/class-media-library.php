<?php
/**
 * Media Library class.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

namespace WoowGallery\Admin;

use Walker;
use Walker_CategoryDropdown;
use WoowGallery\Taxonomies;
use WP_Query;
use WP_Tax_Query;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * Class Media_Library
 */
class Media_Library {

	/**
	 * Holds the media taxonomies.
	 *
	 * @var array
	 */
	public $taxonomies;

	/**
	 * Primary class constructor.
	 */
	public function __construct() {
		$this->taxonomies = apply_filters( 'wg_media_library_taxonomies', [ Taxonomies::GALLERY_TAXONOMY_NAME, Taxonomies::MEDIA_TAG_TAXONOMY_NAME ] );

		add_action( 'wp_enqueue_media', [ $this, 'wp_enqueue_media' ] );
		add_action( 'restrict_manage_posts', [ $this, 'restrict_manage_posts' ] );
		add_action( 'parse_tax_query', [ $this, 'parse_tax_query' ] );

		add_filter( 'wp_dropdown_cats', [ $this, 'dropdown_cats' ], 10, 2 );
	}

	/**
	 *  Adds taxonomy filters to Media Library List View
	 */
	public function restrict_manage_posts() {
		global $current_screen, $wp_query;

		$media_library_mode = get_user_option( 'media_library_mode' ) ?: 'grid';

		if ( ! isset( $current_screen ) || ! ( 'upload' === $current_screen->base && 'list' === $media_library_mode ) ) {
			return;
		}

		foreach ( $this->taxonomies as $taxname ) {
			$taxonomy = get_taxonomy( $taxname );

			/* translators: Taxonomy Name */
			echo '<label for="' . esc_attr( $taxonomy->name ) . '" class="screen-reader-text">' . esc_html( sprintf( __( 'Filter by %s', 'woowgallery' ), $taxonomy->labels->singular_name ) ) . '</label>';

			$selected = isset( $wp_query->query[ $taxonomy->name ] ) ? $wp_query->query[ $taxonomy->name ] : 0;

			$args = [
				/* translators: Taxonomy Name */
				'show_option_all'    => sprintf( __( 'Filter by %s', 'woowgallery' ), $taxonomy->labels->singular_name ),
				/* translators: Taxonomy Name */
				'show_option_in'     => sprintf( '== ' . __( 'All %s', 'woowgallery' ) . ' ==', $taxonomy->labels->name ),
				/* translators: Taxonomy Name */
				'show_option_not_in' => sprintf( '== ' . __( 'Not in %s', 'woowgallery' ) . ' ==', $taxonomy->labels->singular_name ),
				'taxonomy'           => $taxonomy->name,
				'name'               => $taxonomy->query_var,
				'orderby'            => 'name',
				'selected'           => $selected,
				'hierarchical'       => true,
				'show_count'         => true,
				'hide_empty'         => false,
				'hide_if_empty'      => true,
				'id'                 => "media-attachment-{$taxonomy->name}-filters",
				'class'              => "attachment-filters wg-taxonomy-filters attachment-{$taxonomy->name}-filter",
				'walker'             => new Walker_WG_CategoryDropdown(),
			];
			wp_dropdown_categories( $args );
		}
	}

	/**
	 *  Modifies taxonomy filters in Media Library List View
	 *
	 * @param string $output HTML output.
	 * @param array  $r      Arguments used to build the drop-down.
	 *
	 * @return string
	 */
	public function dropdown_cats( $output, $r ) {

		if ( ! is_admin() || empty( $output ) || ! ( isset( $r['show_option_in'] ) || isset( $r['show_option_not_in'] ) ) ) {
			return $output;
		}

		$select_explode = explode( '</option>', $output, 2 );
		if ( count( $select_explode ) < 2 ) {
			return $output;
		}
		$select_explode[0] .= "</option>\n";

		if ( ! empty( $r['show_option_in'] ) ) {

			$show_option_in = $r['show_option_in'];
			$selected       = ( 'in' === strval( $r['selected'] ) ) ? " selected='selected'" : '';

			$select_explode[0] .= "\t<option value='in'{$selected}>{$show_option_in}</option>\n";
		}

		if ( ! empty( $r['show_option_not_in'] ) ) {

			$show_option_not_in = $r['show_option_not_in'];
			$selected           = ( 'not_in' === strval( $r['selected'] ) ) ? " selected='selected'" : '';

			$select_explode[0] .= "\t<option value='not_in'{$selected}>{$show_option_not_in}</option>\n";
		}

		$output = implode( '', $select_explode );

		return $output;
	}

	/**
	 * Taxonomy related args parsed
	 *
	 * @param WP_Query $query The WP_Query instance.
	 */
	public function parse_tax_query( $query ) {

		if ( ! is_admin() || 'attachment' !== $query->get( 'post_type' ) ) {
			return;
		}

		$media_library_mode = get_user_option( 'media_library_mode' ) ?: 'grid';
		if ( 'list' !== $media_library_mode ) {
			return;
		}

		if ( isset( $query->query_vars['taxonomy'] ) && isset( $query->query_vars['term'] ) ) {

			$tt   = $query->query_vars['taxonomy'];
			$term = get_term_by( 'slug', $query->query_vars['term'], $tt );

			if ( $term ) {
				$query->query_vars[ $tt ] = $term->term_id;
				$query->query[ $tt ]      = $term->term_id;

				unset( $query->query_vars['taxonomy'] );
				unset( $query->query_vars['term'] );

				unset( $query->query['taxonomy'] );
				unset( $query->query['term'] );
			}
		}

		foreach ( $this->taxonomies as $taxname ) {
			$_tax_query = false;

			$term_slug = woowgallery_REQUEST( $taxname );
			if ( ! woowgallery_REQUEST( 'filter_action' ) && ! empty( $term_slug ) ) {
				$term = get_term_by( 'slug', $term_slug, $taxname );
				if ( $term ) {
					$_tax_query = [
						'taxonomy' => $taxname,
						'field'    => 'term_id',
						'terms'    => [ $term->term_id ],
					];

					$query->query_vars[ $taxname ] = $term->term_id;
					$query->query[ $taxname ]      = $term->term_id;
				}
			} else {
				if ( ! empty( $query->query[ $taxname ] ) ) {
					if ( is_numeric( $query->query[ $taxname ] ) || is_array( $query->query[ $taxname ] ) ) {
						$_tax_query = [
							'taxonomy' => $taxname,
							'field'    => 'term_id',
							'terms'    => (array) $query->query[ $taxname ],
						];
					} elseif ( 'not_in' === $query->query[ $taxname ] || 'in' === $query->query[ $taxname ] ) {
						$terms = get_terms(
							$taxname,
							[
								'fields' => 'ids',
								'get'    => 'all',
							]
						);

						$_tax_query = [
							'taxonomy' => $taxname,
							'field'    => 'term_id',
							'terms'    => $terms,
							'operator' => strtoupper( str_replace( '_', ' ', $query->query[ $taxname ] ) ),
						];
					}
				}
			}

			if ( $_tax_query ) {
				$tax_query[] = $_tax_query;
			}
		}

		if ( ! empty( $tax_query ) ) {
			$query->tax_query = new WP_Tax_Query( $tax_query );
		}
	}

	/**
	 * WP enqueue media scripts
	 */
	public function wp_enqueue_media() {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_media_scripts' ] );
	}

	/**
	 * Enqueue media scripts in admin
	 */
	public function admin_enqueue_media_scripts() {
		wp_enqueue_script( 'wg-media-library-script', plugins_url( 'assets/js/media-library.min.js', WOOWGALLERY_FILE ), [ 'jquery', 'backbone', 'wp-i18n' ], WOOWGALLERY_VERSION, true );
		wp_localize_script( 'wg-media-library-script', 'woowgallery_l10n_taxterms', $this->l10n_taxterms() );
	}

	/**
	 * Prepare Taxonomies for Media Libaray JS
	 *
	 * @return array
	 */
	public function l10n_taxterms() {
		$media_taxonomies_ready_for_script = [];
		foreach ( $this->taxonomies as $taxname ) {
			$taxonomy       = get_taxonomy( $taxname );
			$_terms         = (array) get_terms( $taxonomy->name, [ 'get' => 'all' ] );
			$walker         = new Walker_WG_Taxonomy_Uploader_Filter();
			$taxonomy_terms = call_user_func_array( [ $walker, 'walk' ], [ $_terms, 0 ] );

			$media_taxonomies_ready_for_script[ $taxonomy->name ] = [
				'singular_name' => $taxonomy->labels->singular_name,
				'plural_name'   => $taxonomy->labels->name,
				'term_list'     => $taxonomy_terms,
			];
		}

		return [
			'taxonomies' => $media_taxonomies_ready_for_script,
		];
	}

}

/**
 *  Class Walker_WG_Taxonomy_Uploader_Filter
 *
 *  Based on /wp-includes/category-template.php
 */
if ( ! class_exists( 'Walker_WG_CategoryDropdown' ) ) {
	class Walker_WG_CategoryDropdown extends Walker_CategoryDropdown {
		public function start_el( &$output, $category, $depth = 0, $args = [], $id = 0 ) {
			$pad = str_repeat( '&dash;&dash;&nbsp;', $depth );

			/** This filter is documented in wp-includes/category-template.php */
			$cat_name = apply_filters( 'list_cats', $category->name, $category );

			if ( isset( $args['value_field'] ) && isset( $category->{$args['value_field']} ) ) {
				$value_field = $args['value_field'];
			} else {
				$value_field = 'term_id';
			}

			$output .= "\t<option class=\"level-$depth\" value=\"" . esc_attr( $category->{$value_field} ) . "\"";

			// Type-juggling causes false matches, so we force everything to a string.
			if ( (string) $category->{$value_field} === (string) $args['selected'] ) {
				$output .= ' selected="selected"';
			}
			$output .= '>';
			$output .= $pad . $cat_name;
			if ( $args['show_count'] ) {
				$output .= '&nbsp;&nbsp;(' . number_format_i18n( $category->count ) . ')';
			}
			$output .= "</option>\n";
		}
	}
}

/**
 *  Walker_WG_Taxonomy_Uploader_Filter
 *
 *  Based on /wp-includes/category-template.php
 */
if ( ! class_exists( 'Walker_WG_Taxonomy_Uploader_Filter' ) ) {

	class Walker_WG_Taxonomy_Uploader_Filter extends Walker {
		public $tree_type = 'category';
		public $db_fields = [ 'parent' => 'parent', 'id' => 'term_id' ];

		public function start_lvl( &$output, $depth = 0, $args = [] ) {
		}

		public function end_lvl( &$output, $depth = 0, $args = [] ) {
		}

		public function start_el( &$output, $category, $depth = 0, $args = [], $id = 0 ) {
			if ( ! is_array( $output ) ) {
				$output = [];
			}
			$output[] = [
				'taxonomy' => $category->taxonomy,
				'parent'   => $category->parent,
				'depth'    => $depth,
				'term_id'  => $category->term_id,
				'name'     => esc_html( apply_filters( 'the_category', $category->name ) ),
				'count'    => number_format_i18n( $category->count ),
			];
		}

		public function end_el( &$output, $category, $depth = 0, $args = [] ) {
		}
	}
}
