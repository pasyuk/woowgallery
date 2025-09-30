<?php
/**
 * Edit Modal class.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

namespace WoowGallery\Admin;

use WoowGallery\Posttypes;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * Class Edit_Album
 */
class Edit_Modal {

	/**
	 * Primary class constructor.
	 */
	public function __construct() {
		global $pagenow;

		add_action( 'admin_menu', [ $this, 'admin_menu' ] );

		$in_modal = isset( $_GET['page'] ) && 'woowgallery-edit' === $_GET['page'];
		if ( 'admin.php' === $pagenow && $in_modal ) {
			add_action( 'admin_init', [ $this, 'edit_modal_frame' ] );
		}

		add_filter( 'get_edit_post_link', [ $this, 'edit_post_link' ], 10, 3 );
		add_filter( 'redirect_post_location', [ $this, 'redirect_location' ], 10, 2 );
	}

	/**
	 * Register hidden menu
	 */
	public function admin_menu() {
		add_submenu_page(
			'',
			__( 'WoowGallery', 'woowgallery' ),
			__( 'WG Edit', 'woowgallery' ),
			apply_filters( 'woowgallery_menu_cap', 'edit_woowgallery_posts' ),
			'woowgallery-edit'
		);
	}

	/**
	 * Load edit pages in wpless interface
	 */
	public function edit_modal_frame() {
		define( 'IFRAME_REQUEST', true );

		$wg_post_id = woowgallery_GET( 'post' );
		if ( ! empty( $wg_post_id ) ) {
			$post_type = get_post_type( $wg_post_id );
			set_current_screen( $post_type );

			require __DIR__ . '/templates/modal-edit.php';
		} else {
			$post_type = isset( $_GET['post_type'] ) && in_array( $_GET['post_type'], [ Posttypes::GALLERY_POSTTYPE, Posttypes::DYNAMIC_POSTTYPE, Posttypes::ALBUM_POSTTYPE ], true ) ? $_GET['post_type'] : '';
			if ( empty( $post_type ) ) {
				wp_die( __( 'Sorry, you are not allowed to edit posts in this post type.' ) );
			}

			global $current_screen;
			set_current_screen( $post_type );
			$current_screen->action = 'add';

			require __DIR__ . '/templates/modal-new.php';
		}

		exit;
	}

	/**
	 * Fix redirect location
	 *
	 * @param string $location The destination URL.
	 * @param int    $post_id  The post ID.
	 *
	 * @return string
	 */
	public function redirect_location( $location, $post_id ) {
		if ( isset( $_POST['woowgallery_modal_flag'] ) ) {
			$location = str_replace( 'post.php', 'admin.php', $location );
			$location = add_query_arg( 'page', 'woowgallery-edit', $location );
		}

		return $location;
	}

	/**
	 * Fix edit post link
	 *
	 * @param string $link    The edit link.
	 * @param int    $post_id Post ID.
	 * @param string $context The link context. If set to 'display' then ampersands
	 *                        are encoded.
	 *
	 * @return string
	 */
	public function edit_post_link( $link, $post_id, $context ) {
		if ( isset( $_POST['woowgallery_modal_flag'] ) || ( isset( $_GET['page'] ) && 'woowgallery-edit' === $_GET['page'] ) ) {
			$link = str_replace( 'post.php', 'admin.php', $link );
			$link = add_query_arg( 'page', 'woowgallery-edit', $link );
		}

		return $link;
	}

}
