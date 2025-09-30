<?php
/**
 * Frontend class.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

namespace WoowGallery;

use WoowGallery\Admin\Settings;
use WP_Post;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * Class Frontend.
 */
class Frontend {

	/**
	 * Frontend constructor.
	 */
	public function __construct() {

		add_action( 'template_redirect', [ $this, 'process_request' ], 0 );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 8 );
		add_action( 'wp_head', [ $this, 'standalone_maybe_insert_shortcode' ] );

		add_filter( 'woowgallery_pre_data', [ $this, 'filter_woowgallery_data' ], 10, 2 );
		add_filter( 'rest_woowgallery_full_post_content', [ $this, 'filter_woowgallery_content' ] );
		add_filter( 'the_preview', [ $this, 'set_preview' ] );
		if ( current_theme_supports( 'block-templates' ) ) {
			add_filter( 'render_block_core/post-content', [ $this, 'standalone_maybe_insert_shortcode_block' ], 10, 2 );
		}
	}

	/**
	 * IG proxy request check
	 *
	 * @return false|void
	 */
	public function process_request() {
		// Check if we're on the correct url.
		global $wp;
		$current_slug = add_query_arg( [], $wp->request );
		if ( 'woow-ig-proxy' !== $current_slug ) {
			return false;
		}

		// Check if it's a valid request.
		$url = woowgallery_GET( 'url' );
		if ( 'instagram' !== substr( $url, 8, 9 ) ) {
			die( 'IG link?' );
		}
		include WOOWGALLERY_PATH . '/includes/tools/proxy.php';
		die( 'Sorry :(' );
	}

	/**
	 * Enqueue main scripts and styles
	 */
	public function enqueue_scripts() {
		//wp_enqueue_style( WOOWGALLERY_SLUG . '-style' );
		wp_enqueue_script( WOOWGALLERY_SLUG . '-script' );
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
		$post_types = apply_filters( 'woowgallery_posttypes', [ Posttypes::GALLERY_POSTTYPE, Posttypes::DYNAMIC_POSTTYPE, Posttypes::ALBUM_POSTTYPE ] );
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
		$post_types = apply_filters( 'woowgallery_posttypes', [ Posttypes::GALLERY_POSTTYPE, Posttypes::DYNAMIC_POSTTYPE, Posttypes::ALBUM_POSTTYPE ] );
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
	 * Maybe inserts the WoowGallery shortcode into the content for the page being viewed.
	 *
	 * @param string $block_content
	 * @param array  $block
	 *
	 * @return string
	 */
	public function standalone_maybe_insert_shortcode_block( $block_content, $block ) {
		// Check we are on a single Post.
		if ( ! is_singular() || is_admin() ) {
			return $block_content;
		}

		$post_type  = get_query_var( 'post_type' );
		$post_types = apply_filters( 'woowgallery_posttypes', [ Posttypes::GALLERY_POSTTYPE, Posttypes::DYNAMIC_POSTTYPE, Posttypes::ALBUM_POSTTYPE ] );
		// Bail if we're on the WoowGallery Post Type screen.
		if ( ! in_array( $post_type, $post_types, true ) ) {
			return $block_content;
		}

		$standalone = Settings::get_settings( 'standalone_' . $post_type );
		if ( ! empty( $standalone ) ) {
			return $this->standalone_insert_shortcode( $block_content );
		}

		return $block_content;
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

		if( post_password_required( $post ) ) {
			return $content;
		}

		$gallery_html = woowgallery( $post->ID, $post->post_type, [], true );

		return $content . $gallery_html;
	}

	/**
	 * Filter gallery data.
	 *
	 * @param array $gallery Full Gallery data.
	 * @param int   $counter The number of gallery on the page.
	 *
	 * @return array
	 */
	public function filter_woowgallery_data( $gallery, $counter ) {
		if ( empty( $gallery['content'] ) ) {
			return $gallery;
		}

		$gallery['content'] = $this->filter_woowgallery_content( $gallery['content'] );

		return $gallery;
	}

	/**
	 * Filter gallery content.
	 *
	 * @param array $content Full Gallery content.
	 *
	 * @return array
	 */
	public function filter_woowgallery_content( $content ) {
		if ( empty( $content ) ) {
			return $content;
		}

		if ( function_exists( 'wc_get_product' ) ) {
			foreach ( $content as $i => $item ) {
				if ( 'post' !== $item['type'] || 'product' !== $item['subtype'] ) {
					continue;
				}
				$product = wc_get_product( (int) $item['id'] );
				if ( ! empty( $product ) ) {
					$btn_url = $product->add_to_cart_url();
					if ( substr( $btn_url, 0, 1 ) === '?' ) {
						$query = wp_parse_url( $btn_url, PHP_URL_QUERY );
						if ( $query ) {
							wp_parse_str( $query, $args );
							$btn_url = add_query_arg( $args, $item['src'] );
						}
					}
					$content[ $i ]['product'] = [
						'price'    => $product->get_price_html(),
						'on_sale'  => $product->is_on_sale(),
						'btn_text' => esc_html( $product->add_to_cart_text() ),
						'btn_url'  => $btn_url,
					];
				}
			}
		}

		if ( is_admin() ) {
			return $content;
		}

		$current_user_id   = get_current_user_id();
		$is_user_logged_in = is_user_logged_in();
		$filtered          = false;
		foreach ( $content as $i => $item ) {
			if ( ! $is_user_logged_in && 'publish' !== $item['status'] ) {
				unset( $content[ $i ] );
				$filtered = true;
			} else {
				if ( 'post' !== $item['type'] ) {
					continue;
				}
				$author_id = isset( $item['author']['id'] ) ? (int) $item['author']['id'] : 0;
				if (
					'future' === $item['status']
					|| ( $current_user_id !== $author_id && in_array( $item['status'], [ 'draft', 'pending' ], true ) )
				) {
					unset( $content[ $i ] );
					$filtered = true;
				}
			}
		}

		if ( $filtered ) {
			$content = array_values( $content );
		}

		return $content;
	}

	/**
	 * Sets up the post object for preview based on the post autosave.
	 *
	 * @param WP_Post $post The Post.
	 *
	 * @return WP_Post|false
	 */
	public function set_preview( $post ) {
		if ( ! is_object( $post ) ) {
			return $post;
		}

		if ( ! in_array( $post->post_type, [ Posttypes::GALLERY_POSTTYPE, Posttypes::DYNAMIC_POSTTYPE, Posttypes::ALBUM_POSTTYPE ], true ) ) {
			return $post;
		}

		$preview = wp_get_post_autosave( $post->ID );
		if ( ! is_object( $preview ) ) {
			return $post;
		}

		$preview = sanitize_post( $preview );

		$post->post_content_filtered = $preview->post_content_filtered;
		$post->preview_id            = $preview->ID;

		return $post;
	}

}
