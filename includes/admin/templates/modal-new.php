<?php
/**
 * Modal new post administration screen.
 *
 * @see        /wp-admin/post-new.php
 *
 * @package    woowgallery
 */

/**
 * @global string  $post_type
 * @global object  $post_type_object
 * @global WP_Post $post
 */
global $post_type, $post_type_object, $post;
global $action, $typenow, $hook_suffix;

if ( ! isset( $_GET['post_type'] ) ) {
	$post_type = \WoowGallery\Posttypes::GALLERY_POSTTYPE;
} elseif ( in_array( $_GET['post_type'], get_post_types( [ 'show_ui' => true ] ), true ) ) {
	$post_type = $_GET['post_type'];
} else {
	wp_die( __( 'Invalid post type.' ) );
}
$post_type_object = get_post_type_object( $post_type );

$title   = $post_type_object->labels->add_new_item;
$editing = true;

if ( ! current_user_can( $post_type_object->cap->edit_posts ) || ! current_user_can( $post_type_object->cap->create_posts ) ) {
	wp_die(
		'<h1>' . __( 'You need a higher level of permission.' ) . '</h1>' .
		'<p>' . __( 'Sorry, you are not allowed to create posts as this user.' ) . '</p>',
		403
	);
}

$post    = get_default_post_to_edit( $post_type, true );
$post_ID = $post->ID;

wp_enqueue_script( 'autosave' );
require __DIR__ . '/modal-edit-form-advanced.php';

do_action( 'wg_admin_footer', $hook_suffix );

iframe_footer();
