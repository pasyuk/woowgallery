<?php
/**
 * Modal edit post administration panel.
 *
 * @see        /wp-admin/post.php
 *
 * @package    woowgallery
 */

wp_reset_vars( [ 'action' ] );

if ( isset( $_GET['post'] ) && isset( $_POST['post_ID'] ) && (int) $_GET['post'] !== (int) $_POST['post_ID'] ) {
	wp_die( __( 'A post ID mismatch has been detected.' ), __( 'Sorry, you are not allowed to edit this item.' ), 400 );
} elseif ( isset( $_GET['post'] ) ) {
	$post_id = $post_ID = (int) $_GET['post'];
} elseif ( isset( $_POST['post_ID'] ) ) {
	$post_id = $post_ID = (int) $_POST['post_ID'];
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

if ( isset( $_POST['post_type'] ) && $post && $post_type !== $_POST['post_type'] ) {
	wp_die( __( 'A post type mismatch has been detected.' ), __( 'Sorry, you are not allowed to edit this item.' ), 400 );
}

switch ( $action ) {
	case 'edit':
		$editing = true;

		if ( empty( $post_id ) || ! $post ) {
			wp_die( __( 'You attempted to edit an item that doesn&#8217;t exist. Perhaps it was deleted?' ) );
			exit();
		}

		if ( ! $post_type_object ) {
			wp_die( __( 'Invalid post type.' ) );
		}

		if ( ! in_array( $typenow, get_post_types( [ 'show_ui' => true ] ) ) ) {
			wp_die( __( 'Sorry, you are not allowed to edit posts in this post type.' ) );
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( __( 'Sorry, you are not allowed to edit this item.' ) );
		}

		if ( 'trash' == $post->post_status ) {
			wp_die( __( 'You can&#8217;t edit this item because it is in the Trash. Please restore it and try again.' ) );
		}

		if ( ! empty( $_GET['get-post-lock'] ) ) {
			check_admin_referer( 'lock-post_' . $post_id );
			wp_set_post_lock( $post_id );
			wp_redirect( get_edit_post_link( $post_id, 'url' ) );
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
		wp_die( __( 'Action does not exist.', 'woowgallery' ) );
		exit();
} // End switch.

do_action( 'wg_admin_footer', $hook_suffix );

iframe_footer();
