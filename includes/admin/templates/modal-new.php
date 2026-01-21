<?php
/**
 * Modal new post administration screen.
 *
 * @see        /wp-admin/post-new.php
 *
 * @package    woowgallery
 */

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * @global string  $post_type
 * @global object  $post_type_object
 * @global WP_Post $post
 */
global $post_type, $post_type_object, $post;
global $action, $typenow, $hook_suffix;

$_post_type = woowgallery_GET( 'post_type' );
if ( ! $_post_type ) {
	$post_type = \WoowGallery\Posttypes::GALLERY_POSTTYPE;
} elseif ( in_array( $_post_type, get_post_types( [ 'show_ui' => true ] ), true ) ) {
	$post_type = $_post_type;
} else {
	wp_die( esc_html__( 'Invalid post type.', 'woowgallery' ) );
}
$post_type_object = get_post_type_object( $post_type );
if ( ! $post_type_object ) {
	wp_die( esc_html__( 'Invalid post type.', 'woowgallery' ) );
}

$title   = $post_type_object->labels->add_new_item;
$editing = true;

if ( ! current_user_can( $post_type_object->cap->edit_posts ) || ! current_user_can( $post_type_object->cap->create_posts ) ) {
	wp_die(
		'<h1>' . esc_html__( 'You need a higher level of permission.', 'woowgallery' ) . '</h1>' .
		'<p>' . esc_html__( 'Sorry, you are not allowed to create posts as this user.', 'woowgallery' ) . '</p>',
		403
	);
}

$post    = get_default_post_to_edit( $post_type, true );
$post_ID = $post->ID;

wp_enqueue_script( 'autosave' );
require __DIR__ . '/modal-edit-form-advanced.php';

do_action( 'wg_admin_footer', $hook_suffix );

iframe_footer();
