<?php
/**
 * Taxonomies class.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

namespace WoowGallery;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * Class Taxonomies
 */
class Taxonomies {

	const GALLERY_TAXONOMY_NAME   = 'woowgallery';
	const MEDIA_TAG_TAXONOMY_NAME = 'media_tag';

	/**
	 * Primary class constructor.
	 */
	public function __construct() {

		$this->register_woowgallery_taxonomy();
		$this->register_media_tag_taxonomy();

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

}
