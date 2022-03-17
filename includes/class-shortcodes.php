<?php
/**
 * Shortcodes class.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

namespace WoowGallery;

use WoowGallery\Admin\Settings;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * Class Shortcodes
 */
class Shortcodes {

	/**
	 * Holds the class object.
	 *
	 * @var Shortcodes object
	 */
	public static $instance;
	/**
	 * Is mobile
	 *
	 * @var mixed
	 * @access public
	 */
	public $is_mobile;
	/**
	 * Iterator for shortcodes on the page.
	 *
	 * @var int
	 */
	protected $counter = 1;
	/**
	 * Array of galleries with data
	 *
	 * @var array
	 */
	protected $galleries = [];
	/**
	 * Gallery output HTML
	 *
	 * @var mixed
	 * @access public
	 */
	protected $gallery_markup;

	/**
	 * Primary class constructor.
	 */
	public function __construct() {

		$this->is_mobile = woowgallery_is_mobile();

		add_shortcode( 'woowgallery', [ $this, 'woowgallery' ] );
		add_shortcode( 'woowgallery-dynamic', [ $this, 'woowgallery_dynamic' ] );
		add_shortcode( 'woowgallery-album', [ $this, 'woowgallery_album' ] );

		// Load hooks and filters.
		add_filter( 'widget_text', 'do_shortcode' );
		add_filter( 'style_loader_tag', [ $this, 'add_stylesheet_property_attribute' ] );
		if ( woow_fs()->can_use_premium_code__premium_only() ) {
			add_filter( 'woowgallery_shortcode_start', [ $this, 'inline_styles' ], 0, 2 );
		}

		/* Yoast SEO */
		add_filter( 'wpseo_sitemap_urlimages', [ $this, 'woowgallery_filter_wpseo_sitemap_urlimages' ], 10, 2 );

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @return Shortcodes object
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Shortcodes ) ) {
			self::$instance = new Shortcodes();
		}

		return self::$instance;
	}

	/**
	 * Add inline custom styles to the footer
	 *
	 * @param string $gallery_markup Gallery HTML.
	 * @param array  $gallery        Gallery data.
	 *
	 * @return string
	 */
	public function inline_styles( $gallery_markup, $gallery ) {
		if ( woow_fs()->can_use_premium_code__premium_only() ) {
			$style = '';
			if ( 1 === $this->counter ) {
				$style .= $this->get_global_inline_styles();
			}

			$wg         = Gallery::get_instance( $gallery['id'], $gallery['type'] );
			$custom_css = $wg->get_settings( 'custom_css', '' );
			if ( ! empty( $custom_css ) ) {
				// Build out the custom CSS.
				$style .= '<style type="text/css" id="woowgallery-' . sanitize_html_class( $gallery['uid'] ) . '-custom-styles">' . woowgallery_minify( html_entity_decode( stripslashes( $custom_css ), ENT_QUOTES ), false ) . '</style>';
			}

			if ( ! empty( $style ) ) {
				$style = wp_kses(
					$style,
					[
						'style' => [
							'type' => [],
							'id'   => [],
						],
					]
				);

				return $style . $gallery_markup;
			}
		}

		return $gallery_markup;
	}

	/**
	 * Add global inline custom styles to the footer
	 *
	 * @return string
	 */
	public function get_global_inline_styles() {
		if ( woow_fs()->can_use_premium_code__premium_only() ) {
			$custom_css = Settings::get_settings( 'custom_css' );
			if ( ! empty( $custom_css ) ) {
				// Build out the custom CSS.
				$style = '<style type="text/css" id="woowgallery-global-custom-styles">' . woowgallery_minify( html_entity_decode( stripslashes( $custom_css ), ENT_QUOTES ), false ) . '</style>';

				return wp_kses(
					$style,
					[
						'style' => [
							'type' => [],
							'id'   => [],
						],
					]
				);
			}
		}

		return '';
	}

	/**
	 * Creates the woowgallery shortcode for the plugin.
	 *
	 * @param array  $atts    Array of shortcode attributes.
	 * @param string $content Enclosed content.
	 *
	 * @return string The gallery output.
	 */
	public function woowgallery( $atts, $content = '' ) {
		return $this->shortcode( $atts, Posttypes::GALLERY_POSTTYPE, $content );
	}

	/**
	 * Creates the shortcode for the plugin.
	 *
	 * @param array  $atts    Array of shortcode attributes.
	 * @param string $type    WoowGallery Type.
	 * @param string $content Enclosed content.
	 *
	 * @return string The gallery output.
	 */
	public function shortcode( $atts, $type = Posttypes::GALLERY_POSTTYPE, $content = '' ) {

		global $post;

		$gallery_id = 0;
		// If no attributes have been passed, the gallery should be pulled from the current post.
		if ( ! empty( $atts['id'] ) ) {
			$gallery_id = (int) $atts['id'];
		} elseif ( $type === $post->post_type ) {
			$gallery_id = $post->ID;
		}


		// If empty $gallery_id return early.
		if ( ! $gallery_id ) {
			return '';
		}

		if ( isset( $atts['gallery'] ) ) {
			$gallery = $atts['gallery'];
		} else {
			$wg      = Gallery::get_instance( $gallery_id, $type );
			$gallery = $wg->get_gallery();
		}

		// Limit the number of images returned, if specified
		// [woowgallery id="123" limit="10"] would only display 10 images.
		$offset = ! empty( $atts['offset'] ) && is_numeric( $atts['offset'] ) ? intval( $atts['offset'], 10 ) : 0;
		$limit  = ! empty( $atts['limit'] ) && is_numeric( $atts['limit'] ) ? intval( $atts['limit'], 10 ) : null;
		if ( ! empty( $offset ) || ! empty( $limit ) ) {
			$items              = array_slice( $gallery['content'], $offset, $limit, true );
			$gallery['content'] = $items;
		}

		// Overwrite gallery skin, if specified
		// [woowgallery id="123" skin="custom-skin"].
		if ( ! empty( $atts['skin'] ) ) {
			$overwrite_skin = Skins::get_instance()->get_skin( $atts['skin'] );

			$gallery['skin']['slug']   = $overwrite_skin->slug;
			$gallery['skin']['config'] = $overwrite_skin->model[ $overwrite_skin->preset_name ];
		}

		// Allow the gallery data to be filtered before it is used to create the gallery output.
		$gallery = apply_filters( 'woowgallery_pre_data', $gallery, $this->counter );

		// If there is no data to output, do nothing.
		if ( empty( $gallery['content'] ) ) {
			return '';
		}

		// If the gallery is not published and current user can't view the post, do nothing.
		if ( 'publish' !== $gallery['status'] && ! is_preview() ) {
			if ( ! current_user_can( 'edit_post', $gallery_id ) ) {
				return '';
			}
		}

		// Lets check if this gallery has already been output on the page.
		if ( empty( $this->galleries[ $gallery['id'] ] ) ) {
			$this->galleries[ $gallery['id'] ] = $gallery;
		}
		$gallery['uid'] = 'wg_' . sanitize_key( $gallery['id'] . '_' . $this->starttime() );

		// Prepare variables.
		$this->gallery_markup = '';

		// If this is a feed view, customize the output and return early.
		if ( is_feed() ) {
			return $this->do_feed_output( $gallery );
		}

		// Load main scripts and styles.
		wp_enqueue_style( WOOWGALLERY_SLUG . '-style' );
		wp_enqueue_script( WOOWGALLERY_SLUG . '-script' );
		if ( ! empty( $gallery['lightbox']['slug'] ) ) {
			wp_enqueue_script( $gallery['lightbox']['slug'] );
		}
		self::load_skin_css( $gallery['skin']['slug'] );
		self::load_skin_js( $gallery['skin']['slug'] );

		// Run a skin specific hook after scripts and inits have been set.
		do_action( 'woowgallery_' . $gallery['skin']['slug'] . '_skin', $gallery );

		// Run a hook before the gallery output begins but after scripts and inits have been set.
		do_action( 'woowgallery_before_shortcode', $gallery );

		$js_callback = '';
		$cb          = isset( $atts['callback'] ) ? preg_replace( '/[^a-zA-Z_\-]/', '', $atts['callback'] ) : '';
		if ( ! empty( $cb ) ) {
			$js_callback .= "<script type='text/javascript'>(typeof window.{$cb} === 'function') && window.{$cb}('{$gallery['uid']}');</script>";
		}

		// Apply a filter before starting the gallery HTML.
		$this->gallery_markup = apply_filters( 'woowgallery_shortcode_start', $this->gallery_markup, $gallery, $content );

		// Schema.org microdata ( Itemscope, etc. ) interferes with Google+ Sharing... so we are adding this via filter rather than hardcoding.
		$schema_microdata = apply_filters( 'woowgallery_shortcode_schema_microdata', 'itemscope itemtype="http://schema.org/ImageGallery"', $gallery );

		// Build out the gallery HTML.
		$this->gallery_markup .= '<div id="' . sanitize_html_class( $gallery['uid'] ) . '" class="' . $this->get_gallery_classes( $gallery['id'], $type ) . '" ' . $schema_microdata . '>';

		$this->gallery_markup = apply_filters( 'woowgallery_shortcode_before_container', $this->gallery_markup, $gallery, $content );

		$this->gallery_markup .= Skins::get_instance()->render_skin( $gallery );

		$this->gallery_markup = apply_filters( 'woowgallery_shortcode_after_container', $this->gallery_markup, $gallery, $content );

		$this->gallery_markup .= apply_filters( 'woowgallery_shortcode_js_callback', $js_callback, $gallery, $content );

		$this->gallery_markup .= '</div>';

		$this->gallery_markup = apply_filters( 'woowgallery_shortcode_end', $this->gallery_markup, $gallery, $content );

		// Increment the counter.
		$this->counter ++;

		// Add no JS fallback support.
		$no_js                = '<noscript>';
		$no_js                .= $this->get_indexable_content( $gallery );
		$no_js                .= '</noscript>';
		$this->gallery_markup .= apply_filters( 'woowgallery_shortcode_noscript', $no_js, $gallery );

		// Return the gallery HTML.
		return apply_filters( 'woowgallery_shortcode', $this->gallery_markup, $gallery );
	}

	public function starttime() {
		$r = explode( ' ', microtime() );
		$r = $r[1] + $r[0];

		return $r;
	}

	/**
	 * Outputs gallery cover or the first image of the gallery inside a regular <div> tag to avoid styling issues with
	 * feeds.
	 *
	 * @param array $gallery Array of gallery data.
	 *
	 * @return string $gallery Custom gallery output for feeds.
	 */
	public function do_feed_output( $gallery ) {

		$output   = '<div class="woowgallery-feed-output">';
		$imagesrc = get_the_post_thumbnail_url( $gallery['id'], 'large' );

		$output .= '<img class="woowgallery-feed-image" src="' . esc_url( $imagesrc ) . '" title="' . trim( esc_attr( $gallery['title'] ) ) . '" alt="' . trim( esc_attr( $gallery['title'] ) ) . '" />';
		$output .= '</div>';

		return apply_filters( 'woowgallery_feed_output', $output, $gallery );

	}

	/**
	 * Load skin assets
	 *
	 * @param string $skin_slug Skin slug.
	 */
	public static function load_skin_css( $skin_slug ) {
		$skin = Skins::get_instance()->get_skin( $skin_slug );
		$deps = ! empty( $skin->info['dependecies'] ) ? (array) $skin->info['dependecies'] : [];

		if ( ! empty( $skin->info['styles'] ) ) {
			foreach ( (array) $skin->info['styles'] as $style ) {
				$handle = 'wgskin_' . $skin->info['slug'];
				wp_register_style( $handle, $style, $deps, $skin->info['version'] );
				wp_enqueue_style( $handle );
			}
		}
	}

	/**
	 * Load skin assets
	 *
	 * @param string $skin_slug Skin slug.
	 */
	public static function load_skin_js( $skin_slug ) {
		$skin = Skins::get_instance()->get_skin( $skin_slug );
		$deps = ! empty( $skin->info['dependecies'] ) ? (array) $skin->info['dependecies'] : [];

		//$request_type = ! empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( wp_unslash( $_SERVER['HTTP_X_REQUESTED_WITH'] ) ) === 'xmlhttprequest' ? 'ajax' : 'direct';

		if ( ! empty( $skin->info['scripts'] ) ) {
			foreach ( (array) $skin->info['scripts'] as $script ) {
				$handle = 'wgskin_' . $skin->info['slug'];
				wp_register_script( $handle, $script, $deps, $skin->info['version'], true );
				wp_enqueue_script( $handle );
				//if ( 'ajax' === $request_type ) {
				//	wp_register_script( $handle, $script, $deps, $skin->info['version'], false );
				//	wp_print_scripts( $handle );
				//} else {
				//	wp_enqueue_script( $handle, $script, $deps, $skin->info['version'], true );
				//}
			}
		}
	}

	/**
	 * Helper method for adding custom gallery classes.
	 *
	 * @param int    $gallery_id The gallery ID to use for retrieval.
	 * @param string $type       WoowGallery Type.
	 *
	 * @return string String of space separated gallery classes.
	 */
	public function get_gallery_classes( $gallery_id, $type = Posttypes::GALLERY_POSTTYPE ) {

		// Set default class.
		$wg        = Gallery::get_instance( $gallery_id, $type );
		$classes   = $wg->get_settings( 'classes', [] );
		$classes[] = 'woowgallery-wrapper';
		$classes[] = 'wg-id-' . $gallery_id;
		$classes[] = 'type-' . $type;

		// Allow filtering of classes and then return what's left.
		$classes = apply_filters( 'woowgallery_shortcode_classes', $classes, $gallery_id );

		return trim( implode( ' ', array_unique( array_map( 'sanitize_html_class', $classes ) ) ) );
	}

	/**
	 * Returns a set of indexable image links to allow SEO indexing for preloaded images.
	 *
	 * @param mixed $gallery Gallery Data.
	 *
	 * @return string String of indexable content HTML.
	 */
	public function get_indexable_content( $gallery ) {

		$content = apply_filters( 'woowgallery_indexable_data', $gallery['content'], $gallery );

		// If there are no images, don't do anything.
		$output = '';
		foreach ( $content as $item ) {
			// Skip over items that are not attachments or not published.
			if ( 'publish' !== $item['status'] ) {
				continue;
			}

			if ( 'attachment' === $item['type'] || 'post' === $item['type'] ) {
				$imagesrc = apply_filters( 'woowgallery_default_image_src', $item['image'], $item, $gallery, $this->is_mobile );
				if ( ! empty( $imagesrc ) ) {
					if ( 'attachment' === $item['type'] ) {
						if ( $item['link']['url'] ) {
							$output .= '<a href="' . esc_url( $item['link']['url'] ) . '" target="' . esc_attr( $item['link']['target'] ) . '">';
						} else {
							$output .= '<a href="' . esc_url( $item['original'] ) . '">';
						}
					} else {
						$output .= '<a href="' . esc_url( $item['src'] ) . '">';
					}

					$output .= '<img class="skip-lazy no-lazyload" data-lazy-src="" src ="' . esc_url( $imagesrc[0] ) . '" width="' . absint( $imagesrc[1] ) . '" height="' . absint( $imagesrc[2] ) . '" title="' . trim( esc_attr( $item['title'] ) ) . '" alt="' . trim( esc_attr( $item['alt'] ) ) . '" />';

					$output .= '</a>';
				}
			}
		}

		return $output;
	}

	/**
	 * Creates the woowgallery-dynamic shortcode for the plugin.
	 *
	 * @param array  $atts    Array of shortcode attributes.
	 * @param string $content Enclosed content.
	 *
	 * @return string The gallery output.
	 */
	public function woowgallery_dynamic( $atts, $content = '' ) {
		return $this->shortcode( $atts, Posttypes::DYNAMIC_POSTTYPE, $content );
	}

	/**
	 * Creates the woowgallery-album shortcode for the plugin.
	 *
	 * @param array  $atts    Array of shortcode attributes.
	 * @param string $content Enclosed content.
	 *
	 * @return string The gallery output.
	 */
	public function woowgallery_album( $atts, $content = '' ) {
		return $this->shortcode( $atts, Posttypes::ALBUM_POSTTYPE, $content );
	}

	/**
	 * Builds HTML for the Gallery Description
	 *
	 * @param string $markup  Gallery HTML.
	 * @param array  $gallery Data.
	 *
	 * @return string HTML
	 */
	public function description( $markup, $gallery ) {

		$gallery_post = get_post( $gallery['id'] );
		// Get description.
		$description = $gallery_post->post_excerpt;

		// If the WP_Embed class is available, use that to parse the content using registered oEmbed providers.
		if ( isset( $GLOBALS['wp_embed'] ) ) {
			$description = $GLOBALS['wp_embed']->autoembed( $description );
		}

		// Get the description and apply most of the filters that apply_filters( 'the_content' ) would use
		// We don't use apply_filters( 'the_content' ) as this would result in a nested loop and a failure.
		$description = wptexturize( $description );
		$description = convert_smilies( $description );
		$description = wpautop( $description );
		$description = prepend_attachment( $description );
		$description = wp_make_content_images_responsive( $description );

		// Filter the gallery description.
		$description = apply_filters( 'woowgallery_description', $description, $gallery );

		// Append the description to the gallery output.
		$markup .= '<div class="woowgallery-description">';
		$markup .= $description;
		$markup .= '</div>';

		return $markup;
	}

	/**
	 * Add the 'property' tag to stylesheets enqueued in the body
	 *
	 * @param string $tag '<link>' tag.
	 *
	 * @return string
	 */
	public function add_stylesheet_property_attribute( $tag ) {

		// If the <link> stylesheet is any WOOWGALLERY-based stylesheet, add the property attribute.
		if ( strpos( $tag, "id='woowgallery-" ) !== false ) {
			$tag = str_replace( '/>', 'property="stylesheet" />', $tag );
		}

		return $tag;
	}

	/**
	 * Inserts images into Yoast SEO Sitexml.
	 *
	 * @param array $yoast_images Current incoming array of images.
	 * @param int   $post_id      Post ID.
	 *
	 * @return array Updated Yoast Array.
	 */
	public function woowgallery_filter_wpseo_sitemap_urlimages( $yoast_images, $post_id ) {

		// make filter magic happen here... if the post_id is an WoowGallery or album, great. if not, go back.
		$post_type     = get_post_type( $post_id );
		$wg_post_types = apply_filters( 'woowgallery_posttypes', [ Posttypes::GALLERY_POSTTYPE, Posttypes::DYNAMIC_POSTTYPE, Posttypes::ALBUM_POSTTYPE ] );
		if ( in_array( $post_type, $wg_post_types, true ) ) {
			$galleries_ids = [ $post_id ];
		} else {
			$galleries_ids = get_post_meta( $post_id, '_woowgallery_galleries', true );
		}

		if ( empty( $galleries_ids ) ) {
			return $yoast_images;
		}

		foreach ( $galleries_ids as $gallery_id ) {
			$post_type = get_post_type( $gallery_id );
			$wg        = Gallery::get_instance( $post_id, $post_type );
			$content   = $wg->get_gallery_content();
			if ( empty( $content ) ) {
				continue;
			}
			if ( Posttypes::GALLERY_POSTTYPE === $post_type ) {
				foreach ( $content as $item ) {
					// Skip over items that are not attachments or not published.
					if ( 'image' !== $item['subtype'] || 'attachment' !== $item['type'] || 'publish' !== $item['status'] ) {
						continue;
					}
					$yoast_images[] = [ 'src' => $item['src'] ];
				}
			} elseif ( Posttypes::ALBUM_POSTTYPE === $post_type ) {
				foreach ( $content as $item ) {
					if ( 'publish' !== $item['status'] ) {
						continue;
					}
					$wg          = Gallery::get_instance( (int) $item['id'], $item['subtype'] );
					$sub_content = $wg->get_gallery_content();
					foreach ( $sub_content as $sub_item ) {
						// Skip over items that are not attachments or not published.
						if ( 'image' !== $sub_item['subtype'] || 'attachment' !== $sub_item['type'] || 'publish' !== $sub_item['status'] ) {
							continue;
						}
						$yoast_images[] = [ 'src' => $item['src'] ];
					}
				}
			}
		}

		return $yoast_images;
	}

	public function endtime( $starttime ) {
		$r = explode( ' ', microtime() );
		$r = $r[1] + $r[0];
		$r = round( $r - $starttime, 4 );

		return '<strong>Execution Time</strong>: ' . $r . ' seconds<br />';
	}

	/**
	 * Get on page galleries array data.
	 *
	 * @return array
	 */
	public function get_galleries() {
		return $this->galleries;
	}
}
