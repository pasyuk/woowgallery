<?php
/**
 * Taxonomies class.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

namespace WoowGallery;

use WoowGallery\Admin\Settings;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * Class Taxonomies
 */
class Taxonomies {

	const GALLERY_TAXONOMY_NAME             = 'media_woowgallery';
	const MEDIA_TAG_TAXONOMY_NAME           = 'media_tag';
	const STANDALONE_CATEGORY_TAXONOMY_NAME = 'woowgallery_category';
	const STANDALONE_TAG_TAXONOMY_NAME      = 'woowgallery_tag';

	/**
	 * Primary class constructor.
	 */
	public function __construct() {

		$this->register_woowgallery_taxonomy();
		$this->register_media_tag_taxonomy();

		if ( woow_fs()->can_use_premium_code__premium_only() ) {
			$this->register_standalone_category_taxonomy();
			$this->register_standalone_tag_taxonomy();
		}

	}

	/**
	 * Register WoowGallery Taxonomy.
	 */
	public function register_woowgallery_taxonomy() {

		// Build the labels for the taxonomy.
		$labels = [
			'name'          => __( 'WoowGallery', 'wgtd' ),
			'singular_name' => __( 'WoowGallery', 'wgtd' ),
		];

		// Build out the taxonomy arguments.
		$args = [
			'labels'                => $labels,
			'hierarchical'          => false,
			'public'                => false,
			'publicly_queryable'    => false,
			'show_ui'               => false,
			'show_in_menu'          => false,
			'show_in_nav_menus'     => false,
			'show_in_rest'          => false,
			'show_tagcloud'         => false,
			'show_in_quick_edit'    => false,
			'show_admin_column'     => true,
			'rewrite'               => false,
			'update_count_callback' => '_update_generic_term_count',
			'capabilities'          => [
				'manage_terms' => 'edit_woowgallery_galleries',
				'edit_terms'   => 'edit_woowgallery_galleries',
				'assign_terms' => 'edit_woowgallery_galleries',
				'delete_terms' => 'delete_woowgallery_galleries',
			],
		];

		// Filter arguments.
		$args = apply_filters( 'woowgallery_taxonomy_args', $args, self::GALLERY_TAXONOMY_NAME );

		// Register the post type with WordPress.
		register_taxonomy( self::GALLERY_TAXONOMY_NAME, [ 'attachment' ], $args );
	}

	/**
	 * Register WoowGallery Media Tag Taxonomy.
	 */
	public function register_media_tag_taxonomy() {

		if ( taxonomy_exists( self::MEDIA_TAG_TAXONOMY_NAME ) ) {
			return;
		}

		// Build the labels for the taxonomy.
		$labels = [
			'name'          => __( 'Media Tags', 'wgtd' ),
			'singular_name' => __( 'Media Tag', 'wgtd' ),
		];

		// Build out the taxonomy arguments.
		$args = [
			'labels'                => $labels,
			'hierarchical'          => false,
			'public'                => true,
			'show_in_quick_edit'    => true,
			'show_admin_column'     => true,
			'show_in_rest'          => true,
			'update_count_callback' => '_update_generic_term_count',
		];

		// Filter arguments.
		$args = apply_filters( 'woowgallery_taxonomy_args', $args, self::MEDIA_TAG_TAXONOMY_NAME );

		// Register the post type with WordPress.
		register_taxonomy( self::MEDIA_TAG_TAXONOMY_NAME, [ 'attachment' ], $args );
	}

	/**
	 * Register WoowGallery Standalone Category taxonomy.
	 */
	public function register_standalone_category_taxonomy() {
		if ( woow_fs()->can_use_premium_code__premium_only() ) {
			$categories_enabled = (int) Settings::get_settings( 'woowgallery_categories' );
			if ( ! $categories_enabled || taxonomy_exists( self::STANDALONE_CATEGORY_TAXONOMY_NAME ) ) {
				return;
			}

			// Build the labels for the taxonomy.
			$labels = [
				'name'          => __( 'Categories', 'wgtd' ),
				'singular_name' => __( 'WoowGallery Category', 'wgtd' ),
			];

			// Build out the taxonomy arguments.
			$args = [
				'labels'                => $labels,
				'hierarchical'          => true,
				'public'                => false,
				'show_ui'               => true,
				'show_in_quick_edit'    => true,
				'show_admin_column'     => true,
				'show_in_rest'          => true,
				'update_count_callback' => '_update_post_term_count',
			];

			// Filter arguments.
			$args = apply_filters( 'woowgallery_taxonomy_args', $args, self::STANDALONE_CATEGORY_TAXONOMY_NAME );

			$wg_post_types = [ Posttypes::GALLERY_POSTTYPE, Posttypes::DYNAMIC_POSTTYPE, Posttypes::ALBUM_POSTTYPE ];
			foreach ( $wg_post_types as $posttype ) {
				$standalone = Settings::get_settings( 'standalone_' . $posttype );
				if ( ! empty( $standalone ) ) {
					$args['public'] = true;
				}
			}

			// Register the post type with WordPress.
			register_taxonomy( self::STANDALONE_CATEGORY_TAXONOMY_NAME, $wg_post_types, $args );
		}
	}

	/**
	 * Register WoowGallery Standalone Tag taxonomy.
	 */
	public function register_standalone_tag_taxonomy() {
		if ( woow_fs()->can_use_premium_code__premium_only() ) {
			$tags_enabled = (int) Settings::get_settings( 'woowgallery_tags' );
			if ( ! $tags_enabled || taxonomy_exists( self::STANDALONE_TAG_TAXONOMY_NAME ) ) {
				return;
			}

			// Build the labels for the taxonomy.
			$labels = [
				'name'          => __( 'Tags', 'wgtd' ),
				'singular_name' => __( 'WoowGallery Tag', 'wgtd' ),
			];

			// Build out the taxonomy arguments.
			$args = [
				'labels'                => $labels,
				'hierarchical'          => true,
				'public'                => false,
				'show_ui'               => true,
				'show_in_quick_edit'    => true,
				'show_admin_column'     => true,
				'show_in_rest'          => true,
				'update_count_callback' => '_update_post_term_count',
			];

			// Filter arguments.
			$args = apply_filters( 'woowgallery_taxonomy_args', $args, self::STANDALONE_TAG_TAXONOMY_NAME );

			$wg_post_types = [ Posttypes::GALLERY_POSTTYPE, Posttypes::DYNAMIC_POSTTYPE, Posttypes::ALBUM_POSTTYPE ];
			foreach ( $wg_post_types as $posttype ) {
				$standalone = Settings::get_settings( 'standalone_' . $posttype );
				if ( ! empty( $standalone ) ) {
					$args['public'] = true;
				}
			}

			// Register the post type with WordPress.
			register_taxonomy( self::STANDALONE_TAG_TAXONOMY_NAME, $wg_post_types, $args );
		}
	}

}
