<?php
/**
 * Modal edit post administration panel.
 *
 * @see        /wp-admin/post.php
 *
 * @package    woowgallery
 */

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

wp_reset_vars( [ 'action' ] );

$_get_post       = woowgallery_GET( 'post' );
$_post_post_ID   = woowgallery_POST( 'post_ID' );
$_post_post_type = woowgallery_POST( 'post_type' );
if ( ! empty( $_get_post ) && ! empty( $_post_post_ID ) && (int) $_get_post !== (int) $_post_post_ID ) {
	wp_die( esc_html__( 'A post ID mismatch has been detected.', 'woowgallery' ), esc_html__( 'Sorry, you are not allowed to edit this item.', 'woowgallery' ), 400 );
} elseif ( ! empty( $_get_post ) ) {
	$post_id = $post_ID = (int) $_get_post;
} elseif ( ! empty( $_post_post_ID ) ) {
	$post_id = $post_ID = (int) $_post_post_ID;
} else {
	$post_id = $post_ID = 0;
}

/**
 * @global string  $post_type
 * @global object  $post_type_object
 * @global WP_Post $post
 */
global $post_type, $post_type_object, $post;
global $action, $typenow, $hook_suffix;

if ( $post_id ) {
	$post = get_post( $post_id );
}

if ( $post ) {
	$post_type        = $post->post_type;
	$post_type_object = get_post_type_object( $post_type );
}

if ( ! empty( $_post_post_type ) && $post && $post_type !== $_post_post_type ) {
	wp_die( esc_html__( 'A post type mismatch has been detected.', 'woowgallery' ), esc_html__( 'Sorry, you are not allowed to edit this item.', 'woowgallery' ), 400 );
}

switch ( $action ) {
	case 'edit':
		$editing = true;

		if ( empty( $post_id ) || ! $post ) {
			wp_die( esc_html__( 'You attempted to edit an item that doesn&#8217;t exist. Perhaps it was deleted?', 'woowgallery' ) );
			exit();
		}

		if ( ! $post_type_object ) {
			wp_die( esc_html__( 'Invalid post type.', 'woowgallery' ) );
		}

		if ( ! in_array( $typenow, get_post_types( [ 'show_ui' => true ] ) ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to edit posts in this post type.', 'woowgallery' ) );
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to edit this item.', 'woowgallery' ) );
		}

		if ( 'trash' == $post->post_status ) {
			wp_die( esc_html__( 'You can&#8217;t edit this item because it is in the Trash. Please restore it and try again.', 'woowgallery' ) );
		}

		if ( ! empty( $_GET['get-post-lock'] ) ) {
			check_admin_referer( 'lock-post_' . $post_id );
			wp_set_post_lock( $post_id );
			wp_safe_redirect( get_edit_post_link( $post_id, 'url' ) );
			exit();
		}

		$post_type = $post->post_type;
		$title = $post_type_object->labels->edit_item;

		if ( ! wp_check_post_lock( $post->ID ) ) {
			$active_post_lock = wp_set_post_lock( $post->ID );

			wp_enqueue_script( 'autosave' );
		}

		$post = get_post( $post_id, OBJECT, 'edit' );

		require __DIR__ . '/modal-edit-form-advanced.php';

		break;

	// Intentional fall-through to trigger the edit_post() call.
	case 'editpost':
		check_admin_referer( 'update-post_' . $post_id );

		$post_id = edit_post();

		// Session cookie flag that the post was saved.
		if ( isset( $_COOKIE['wp-saving-post'] ) && $_COOKIE['wp-saving-post'] === $post_id . '-check' ) {
			setcookie( 'wp-saving-post', $post_id . '-saved', time() + DAY_IN_SECONDS, ADMIN_COOKIE_PATH, COOKIE_DOMAIN, is_ssl() );
		}

		redirect_post( $post_id ); // Send user on their way while we keep working.

		exit();

	default:
		wp_die( esc_html__( 'Action does not exist.', 'woowgallery' ) );
		exit();
} // End switch.

do_action( 'wg_admin_footer', $hook_suffix );

iframe_footer();
