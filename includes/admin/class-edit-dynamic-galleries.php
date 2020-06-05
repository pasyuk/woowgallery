<?php
/**
 * WP List Dynamic Galleries Class.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

namespace WoowGallery\Admin;

use WoowGallery\Gallery;
use WoowGallery\Posttypes;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * Class Edit_Galleries
 */
class Edit_Dynamic_Galleries extends Edit_Tablelist {

	/**
	 * Edit_Galleries constructor.
	 */
	public function __construct() {
		parent::__construct( Posttypes::DYNAMIC_POSTTYPE );

		add_action( 'current_screen', [ $this, 'current_screen' ] );
		add_filter( 'bulk_actions-edit-' . Posttypes::DYNAMIC_POSTTYPE, [ $this, 'bulk_actions' ] );
		add_filter( 'handle_bulk_actions-edit-' . Posttypes::DYNAMIC_POSTTYPE, [ $this, 'handle_bulk_actions' ], 10, 3 );
	}

	/**
	 * Do some actions on current screen.
	 *
	 * @param \WP_Screen $current_screen Current WP_Screen object.
	 */
	public function current_screen( $current_screen ) {
		if ( $this->post_type === $current_screen->post_type && 'edit' === $current_screen->base ) {
			// Clear cache for gallery ID.
			$cache_clear_id = (int) woowgallery_GET( 'wg_cache_clear' );
			if ( ! empty( $cache_clear_id ) ) {
				update_post_meta( $cache_clear_id, Gallery::GALLERY_UPDATE_META_KEY, 1 );
				// translators: gallery ID.
				Notice::add_message( sprintf( __( 'Cache cleared for gallery with ID #%d', 'woowgallery' ), $cache_clear_id ), Notice::TYPE_SUCCESS );
				wp_safe_redirect( remove_query_arg( 'wg_cache_clear', wp_get_referer() ) );
			}
		}
	}

	/**
	 * Add bulk actions.
	 *
	 * @param array $bulk_array Bulk actions array.
	 *
	 * @return array
	 */
	public function bulk_actions( $bulk_array ) {
		$bulk_array['wg_bulk_cache_clear'] = __( 'Clear cache', 'woowgallery' );

		return $bulk_array;
	}

	/**
	 * Handle bulk actions.
	 *
	 * @param string $redirect   Redirect URL.
	 * @param string $doaction   Action name.
	 * @param array  $object_ids Array of post IDs.
	 *
	 * @return string
	 */
	public function handle_bulk_actions( $redirect, $doaction, $object_ids ) {
		// let's remove query args first.
		$redirect = remove_query_arg( [ 'wg_bulk_cache_clear' ], $redirect );

		if ( 'wg_bulk_cache_clear' === $doaction ) {
			foreach ( $object_ids as $post_id ) {
				update_post_meta( (int) $post_id, Gallery::GALLERY_UPDATE_META_KEY, 1 );
			}
			// translators: number of galleries.
			Notice::add_message( sprintf( _n( 'Cache cleared for %d gallery', 'Cache cleared for %d galleries', count( $object_ids ), 'woowgallery' ), count( $object_ids ) ), Notice::TYPE_SUCCESS );
		}

		return $redirect;
	}

}
