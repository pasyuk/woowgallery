<?php
/**
 * WP List Class.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

namespace WoowGallery\Admin;

use WoowGallery\Gallery;
use WoowGallery\Posttypes;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * Class Edit_Tablelist
 */
abstract class Edit_Tablelist {

	/**
	 * Current Post type
	 *
	 * @var string
	 */
	public $post_type;

	/**
	 * Constructor.
	 *
	 * @param string $post_type Post Type.
	 */
	public function __construct( $post_type ) {
		$this->post_type = $post_type;

		// Expand search with IDs in addition to WordPress default of post/gallery titles.
		add_action( 'posts_where', [ $this, 'enable_search_by_id' ], 10 );

		// Append data to various admin columns.
		add_filter( 'manage_edit-' . $this->post_type . '_columns', [ $this, 'table_columns' ] );
		add_action( 'manage_' . $this->post_type . '_posts_custom_column', [ $this, 'custom_columns_data' ], 10, 2 );

	}

	/**
	 * Enables search by ID for galleries in table overview screen
	 *
	 * @param string $where MYSQL.
	 *
	 * @return string Revised Where.
	 */
	public function enable_search_by_id( $where ) {

		// Bail if we are not in the admin area or not doing a search.
		if ( ! is_admin() || ! is_search() ) {
			return $where;
		}

		$post_type     = woowgallery_GET( 'post_type' );
		$wg_post_types = apply_filters( 'woowgallery_posttypes', [ Posttypes::GALLERY_POSTTYPE, Posttypes::DYNAMIC_POSTTYPE, Posttypes::ALBUM_POSTTYPE ] );
		// Bail if we're on the WoowGallery Post Type screen.
		if ( ! in_array( $post_type, $wg_post_types, true ) ) {
			return $where;
		}

		global $wpdb;

		// Get the value that is being searched.
		$search_string = get_query_var( 's' );
		if ( is_numeric( $search_string ) ) {
			$where = str_replace( '(' . $wpdb->posts . '.post_title LIKE', '(' . $wpdb->posts . '.ID = ' . $search_string . ') OR (' . $wpdb->posts . '.post_title LIKE', $where );
		} elseif ( preg_match( '/^(\d+)(,\s*\d+)*$/', $search_string ) ) { // string of post IDs.
			$where = str_replace( '(' . $wpdb->posts . '.post_title LIKE', '(' . $wpdb->posts . '.ID in (' . $search_string . ')) OR (' . $wpdb->posts . '.post_title LIKE', $where );
		}

		return $where;
	}

	/**
	 * Customize the post columns for the Woowgallery post type.
	 *
	 * @param array $columns The default columns.
	 *
	 * @return array $columns Amended columns.
	 */
	public function table_columns( $columns ) {

		// Add additional columns we want to display.
		$wg_columns = [
			'cb'        => '<input type="checkbox" />',
			'image'     => __( 'Cover', 'woowgallery' ),
			'title'     => __( 'Title', 'woowgallery' ),
			'shortcode' => __( 'Shortcode', 'woowgallery' ),
			'posts'     => __( 'Posts', 'woowgallery' ),
			'modified'  => __( 'Last Modified', 'woowgallery' ),
			'date'      => __( 'Date', 'woowgallery' ),
		];

		// Allow filtering of columns.
		$wg_columns = apply_filters( 'woowgallery_table_columns', $wg_columns, $columns, $this->post_type );

		// Return merged column set.  This allows plugins to output their columns (e.g. Yoast SEO),
		// and column management plugins, such as Admin Columns, should play nicely.
		return array_merge( $wg_columns, $columns );

	}

	/**
	 * Add data to the custom columns added to the Woowgallery post type.
	 *
	 * @param string $column  The name of the custom column.
	 * @param int    $post_id The current post ID.
	 */
	public function custom_columns_data( $column, $post_id ) {
		$post = get_post( $post_id );

		switch ( $column ) {
			/**
			 * Image
			 */
			case 'image':
				// Get Gallery Images.
				if ( has_post_thumbnail( $post ) ) {
					$src = get_the_post_thumbnail_url( $post, 'thumbnail' );
				} else {
					$src = plugins_url( 'assets/images/icons/image.png', WOOWGALLERY_FILE );
				}
				// Display the cover.
				echo '<img src="' . esc_url( $src ) . '" width="75" />'; // @codingStandardsIgnoreLine
				echo '<span>';
				$count_items = (int) get_post_meta( $post->ID, Gallery::GALLERY_MEDIA_COUNT_META_KEY, true );
				if ( Posttypes::GALLERY_POSTTYPE === $this->post_type || Posttypes::DYNAMIC_POSTTYPE === $this->post_type ) {
					// translators: number of media items.
					echo esc_html( sprintf( _n( '%d Media Item', '%d Media Items', $count_items, 'woowgallery' ), $count_items ) );
				} elseif ( Posttypes::ALBUM_POSTTYPE === $this->post_type ) {
					// translators: number of galleries in album.
					echo esc_html( sprintf( _n( '%d Gallery', '%d Galleries', $count_items, 'woowgallery' ), $count_items ) );
					$total_items  = 0;
					$gallery_data = (array) json_decode( $post->post_content_filtered );
					foreach ( $gallery_data as $gallery_item ) {
						$count_items = (int) get_post_meta( (int) $gallery_item->id, Gallery::GALLERY_MEDIA_COUNT_META_KEY, true ) ?: 0;
						$total_items = $total_items + $count_items;
					}
					// translators: number of media items.
					echo '<br />' . esc_html( sprintf( _n( '%d Media Item', '%d Media Items', $total_items, 'woowgallery' ), $total_items ) );
				}
				echo '</span>';
				break;

			/**
			 * Shortcode
			 */
			case 'shortcode':
				echo '
<div class="woowgallery-code">
	<code id="woowgallery_shortcode_id_' . absint( $post->ID ) . '">[' . esc_html( $this->post_type ) . ' id="' . absint( $post->ID ) . '"]</code>
	<a href="#" title="' . esc_attr__( 'Copy Shortcode to Clipboard', 'woowgallery' ) . '" data-clipboard-target="#woowgallery_shortcode_id_' . absint( $post->ID ) . '" class="dashicons dashicons-clipboard woowgallery-clipboard">
		<span>' . esc_html__( 'Copy to Clipboard', 'woowgallery' ) . '</span>
	</a>
</div>';
				break;

			/**
			 * Posts
			 */
			case 'posts':
				$posts = get_post_meta( $post->ID, '_woowgallery_posts', true );
				$arr   = [];
				if ( is_array( $posts ) ) {
					foreach ( $posts as $in_post_id ) {
						$arr[] = '<a href="' . esc_url( get_permalink( $in_post_id ) ) . '" target="_blank">' . get_the_title( $in_post_id ) . '</a>';
					}
					echo implode( ', ', $arr ); // @codingStandardsIgnoreLine
				}
				break;

			/**
			 * Last Modified
			 */
			case 'modified':
				global $mode;

				if ( '0000-00-00 00:00:00' === $post->post_modified ) {
					$t_time = __( 'Unpublished', 'woowgallery' );
					$h_time = $t_time;
				} else {
					$t_time = get_the_modified_time( __( 'Y/m/d g:i:s a' ) );
					$m_time = $post->post_modified;
					$time   = get_post_modified_time( 'G', true, $post );

					$time_diff = time() - $time;

					if ( $time_diff > 0 && $time_diff < DAY_IN_SECONDS ) {
						// translators: time ago.
						$h_time = sprintf( __( '%s ago', 'woowgallery' ), human_time_diff( $time ) );
					} else {
						$h_time = mysql2date( __( 'Y/m/d' ), $m_time );
					}
				}
				echo '<abbr title="' . esc_attr( $t_time ) . '">' . esc_html( apply_filters( 'post_date_column_time', $h_time, $post, 'date', $mode ) ) . '</abbr>';

				if ( Posttypes::DYNAMIC_POSTTYPE === $post->post_type ) {
					$wg      = Gallery::get_instance( $post->ID, $post->post_type );
					$cache_h = absint( $wg->get_settings( 'cache', Settings::get_settings( 'cache' ) ) );
					if ( $cache_h ) {
						// translators: time in hours.
						echo '<p><span class="cache-time">' . esc_html( sprintf( _n( 'Cache time: %d hour', 'Cache time: %d hours', $cache_h, 'woowgallery' ), $cache_h ) ) . '</span></p>';

						$cache_expire = (int) get_post_meta( $post->ID, Gallery::GALLERY_UPDATE_META_KEY, true );
						if ( $cache_expire && 1 < $cache_expire ) {
							$updated   = $cache_expire - $cache_h * HOUR_IN_SECONDS;
							$time_diff = time() - $updated;
							if ( $time_diff > $cache_h * HOUR_IN_SECONDS ) {
								echo '<span class="cache-updated">' . esc_html__( 'Cache expired', 'woowgallery' ) . '</span>';
							} else {
								// translators: time ago.
								echo '<span class="cache-updated">' . esc_html( sprintf( __( 'Cached: %s ago', 'woowgallery' ), human_time_diff( $updated ) ) ) . '</span>';
								echo '<br /><button name="wg_cache_clear" value="' . absint( $post->ID ) . '" class="button button-small button-primary cache-clear">' . esc_attr__( 'Clear cache', 'woowgallery' ) . '</button>';
							}
						} else {
							echo '<span class="cache-updated">' . esc_html__( 'Not cached yet', 'woowgallery' ) . '</span>';
						}
					} else {
						echo '<span class="cache-time">' . esc_html__( 'Cache disabled', 'woowgallery' ) . '</span>';
					}
				}

				break;
		}
	}
}
