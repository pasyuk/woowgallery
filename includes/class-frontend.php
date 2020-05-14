<?php
/**
 * Frontend class.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

namespace WoowGallery;

use WoowGallery\Admin\Settings;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * Class Frontend.
 */
class Frontend {

	/**
	 * Frontend constructor.
	 */
	public function __construct() {

		add_action( 'wp_head', [ $this, 'standalone_maybe_insert_shortcode' ] );

	}

	/**
	 * Standalone pre_get_posts hook
	 *
	 * @param object $query The query object passed by reference.
	 */
	public function standalone_pre_get_posts( $query ) {

		// Return early if in the admin, not the main query or not a single post.
		if ( is_admin() || ! $query->is_main_query() || ! $query->is_single() ) {
			return;
		}

		$post_type  = get_query_var( 'post_type' );
		$post_types = apply_filters( 'woowgallery_posttypes', [ Posttypes::GALLERY_POSTTYPE, Posttypes::ALBUM_POSTTYPE, Posttypes::DYNAMIC_POSTTYPE ] );
		// Bail if we're on the WoowGallery Post Type screen.
		if ( ! in_array( $post_type, $post_types, true ) ) {
			return;
		}

		$standalone = Settings::get_settings( 'standalone_' . $post_type );
		if ( ! empty( $standalone ) ) {
			do_action( 'woowgallery_standalone_pre_get_posts', $query );
		}
	}

	/**
	 * Maybe inserts the WoowGallery shortcode into the content for the page being viewed.
	 */
	public function standalone_maybe_insert_shortcode() {
		// Check we are on a single Post.
		if ( ! is_singular() ) {
			return;
		}

		$post_type  = get_query_var( 'post_type' );
		$post_types = apply_filters( 'woowgallery_posttypes', [ Posttypes::GALLERY_POSTTYPE, Posttypes::ALBUM_POSTTYPE, Posttypes::DYNAMIC_POSTTYPE ] );
		// Bail if we're on the WoowGallery Post Type screen.
		if ( ! in_array( $post_type, $post_types, true ) ) {
			return;
		}

		$standalone = Settings::get_settings( 'standalone_' . $post_type );
		if ( ! empty( $standalone ) ) {
			add_filter( 'the_content', [ $this, 'standalone_insert_shortcode' ] );
		}
	}

	/**
	 * Inserts the WoowGallery shortcode into the content for the page being viewed.
	 *
	 * @param string $content The content to be filtered.
	 *
	 * @return string Post content with gallery appended.
	 */
	public function standalone_insert_shortcode( $content ) {
		global $post;

		$gallery_html = woowgallery( $post->ID, $post->post_type, [], true );

		return $content . $gallery_html;
	}

}
