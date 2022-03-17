<?php
/**
 * Modal post advanced form for inclusion in the administration panels.
 *
 * @see        /wp-admin/edit-form-advanced.php
 *
 * @package    woowgallery
 */

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * @global string       $post_type
 * @global WP_Post_Type $post_type_object
 * @global WP_Post      $post
 */
global $post_type, $post_type_object, $post;

// Flag that we're not loading the block editor.
$current_screen = get_current_screen();
$current_screen->is_block_editor( false );

if ( is_multisite() ) {
	add_action( 'admin_footer', '_admin_notice_post_locked' );
} else {
	$check_users = get_users(
		[
			'fields' => 'ID',
			'number' => 2,
		]
	);

	if ( count( $check_users ) > 1 ) {
		add_action( 'admin_footer', '_admin_notice_post_locked' );
	}

	unset( $check_users );
}

wp_enqueue_script( 'post' );

if ( wp_is_mobile() ) {
	wp_enqueue_script( 'jquery-touch-punch' );
}

/**
 * Post ID global
 *
 * @name $post_ID
 * @var int
 */
$post_ID = isset( $post_ID ) ? (int) $post_ID : 0;
$user_ID = isset( $user_ID ) ? (int) $user_ID : 0;
$action  = isset( $action ) ? $action : '';

add_thickbox();
wp_enqueue_media( [ 'post' => $post_ID ] );

// Add the local autosave notice HTML.
add_action( 'admin_footer', '_local_storage_notice' );

$messages = [];

$scheduled_date   = sprintf(
/* translators: Publish box date string. 1: Date, 2: Time. */
	__( '%1$s at %2$s' ),
	/* translators: Publish box date format, see https://www.php.net/date */
	date_i18n( _x( 'M j, Y', 'publish box date format' ), strtotime( $post->post_date ) ),
	/* translators: Publish box time format, see https://www.php.net/date */
	date_i18n( _x( 'H:i', 'publish box time format' ), strtotime( $post->post_date ) )
);
$messages['post'] = [
	0  => '', // Unused. Messages start at index 1.
	1  => __( 'Post updated.' ),
	4  => __( 'Post updated.' ),
	6  => __( 'Post published.' ),
	7  => __( 'Post saved.' ),
	8  => __( 'Post submitted.' ),
	9  => sprintf( __( 'Post scheduled for: %s.' ), '<strong>' . $scheduled_date . '</strong>' ),
	10 => __( 'Post draft updated.' ),
];

/**
 * Filters the post updated messages.
 *
 * @param array[] $messages Post updated messages. For defaults see `$messages` declarations above.
 */
$messages = apply_filters( 'post_updated_messages', $messages );

$message = false;
if ( isset( $_GET['message'] ) ) {
	$_GET['message'] = absint( $_GET['message'] );
	if ( isset( $messages[ $post_type ][ $_GET['message'] ] ) ) {
		$message = $messages[ $post_type ][ $_GET['message'] ];
	} elseif ( ! isset( $messages[ $post_type ] ) && isset( $messages['post'][ $_GET['message'] ] ) ) {
		$message = $messages['post'][ $_GET['message'] ];
	}
}

$notice     = false;
$form_extra = '';
if ( 'auto-draft' === $post->post_status ) {
	if ( 'edit' === $action ) {
		$post->post_title = '';
	}
	$autosave   = false;
	$form_extra .= "<input type='hidden' id='auto_draft' name='auto_draft' value='1' />";
} else {
	$autosave = wp_get_post_autosave( $post_ID );
}

$form_action  = 'editpost';
$nonce_action = 'update-post_' . $post_ID;
$form_extra   .= "<input type='hidden' id='post_ID' name='post_ID' value='" . esc_attr( $post_ID ) . "' />";

// Detect if there exists an autosave newer than the post and if that autosave is different than the post.
if ( $autosave && mysql2date( 'U', $autosave->post_modified_gmt, false ) > mysql2date( 'U', $post->post_modified_gmt, false ) ) {
	wp_delete_post_revision( $autosave->ID );
}

$post_type_object = get_post_type_object( $post_type );

// All meta boxes should be defined and added before the first do_meta_boxes() call (or potentially during the do_meta_boxes action).
require_once ABSPATH . 'wp-admin/includes/meta-boxes.php';

register_and_do_post_meta_boxes( $post );

wp_enqueue_style( WOOWGALLERY_SLUG . '-edit-woowgallery-modal-style', plugins_url( 'assets/css/edit-woowgallery-modal.css', WOOWGALLERY_FILE ), [], WOOWGALLERY_VERSION );
iframe_header( 'WoowGallery' );
?>

<div id="wpbody-content">
	<div class="wrap">
		<h1 class="wp-heading-inline">
			<?php
			/**
			 * @var $title string
			 */
			echo esc_html( $title );
			?>
		</h1>

		<hr class="wp-header-end">

		<?php if ( $message ) : ?>
			<div id="message" class="updated notice notice-success is-dismissible"><p><?php echo $message; ?></p></div>
		<?php endif; ?>
		<div id="lost-connection-notice" class="error hidden">
			<p><span class="spinner"></span> <?php _e( '<strong>Connection lost.</strong> Saving has been disabled until you&#8217;re reconnected.' ); ?>
				<span class="hide-if-no-sessionstorage"><?php _e( 'We&#8217;re backing up this post in your browser, just in case.' ); ?></span>
			</p>
		</div>
		<form name="post" action="post.php" method="post" id="post"
			<?php
			/**
			 * Fires inside the post editor form tag.
			 *
			 * @param WP_Post $post Post object.
			 */
			do_action( 'post_edit_form_tag', $post );

			$referer = wp_get_referer();
			?>
		>
			<?php wp_nonce_field( $nonce_action ); ?>
			<input type="hidden" id="user-id" name="user_ID" value="<?php echo (int) $user_ID; ?>"/>
			<input type="hidden" id="hiddenaction" name="action" value="<?php echo esc_attr( $form_action ); ?>"/>
			<input type="hidden" id="originalaction" name="originalaction" value="<?php echo esc_attr( $form_action ); ?>"/>
			<input type="hidden" id="post_author" name="post_author" value="<?php echo esc_attr( $post->post_author ); ?>"/>
			<input type="hidden" id="post_type" name="post_type" value="<?php echo esc_attr( $post_type ); ?>"/>
			<input type="hidden" id="original_post_status" name="original_post_status" value="<?php echo esc_attr( $post->post_status ); ?>"/>
			<input type="hidden" id="referredby" name="referredby" value="<?php echo $referer ? esc_url( $referer ) : ''; ?>"/>
			<input type="hidden" id="woowgallery_modal_flag" name="woowgallery_modal_flag" value="1"/>
			<?php if ( ! empty( $active_post_lock ) ) { ?>
				<input type="hidden" id="active_post_lock" value="<?php echo esc_attr( implode( ':', $active_post_lock ) ); ?>"/>
				<?php
			}
			if ( 'draft' !== get_post_status( $post ) ) {
				wp_original_referer_field( true, 'previous' );
			}

			echo $form_extra;

			wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
			wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
			?>

			<?php
			/**
			 * Fires at the beginning of the edit form.
			 *
			 * At this point, the required hidden fields and nonces have already been output.
			 *
			 * @param WP_Post $post Post object.
			 */
			do_action( 'edit_form_top', $post );
			?>

			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">

						<?php if ( post_type_supports( $post_type, 'title' ) ) { ?>
							<div id="titlediv">
								<div id="titlewrap">
									<?php
									/**
									 * Filters the title field placeholder text.
									 *
									 * @param string  $text Placeholder text. Default 'Add title'.
									 * @param WP_Post $post Post object.
									 */
									$title_placeholder = apply_filters( 'enter_title_here', __( 'Add title' ), $post );
									?>
									<label class="screen-reader-text" id="title-prompt-text" for="title"><?php echo $title_placeholder; ?></label>
									<input type="text" name="post_title" size="30" value="<?php echo esc_attr( $post->post_title ); ?>" id="title" spellcheck="true" autocomplete="off"/>
								</div>
								<?php
								/**
								 * Fires before the permalink field in the edit form.
								 *
								 * @param WP_Post $post Post object.
								 */
								do_action( 'edit_form_before_permalink', $post );

								wp_nonce_field( 'samplepermalink', 'samplepermalinknonce', false );
								?>
							</div><!-- /titlediv -->
							<?php
						}
						/**
						 * Fires after the title field.
						 *
						 * @param WP_Post $post Post object.
						 */
						do_action( 'edit_form_after_title', $post );

						/**
						 * Fires after the content editor.
						 *
						 * @param WP_Post $post Post object.
						 */
						do_action( 'edit_form_after_editor', $post );
						?>
					</div><!-- /post-body-content -->

					<div id="postbox-container-1" class="postbox-container">
						<?php
						/**
						 * Fires before meta boxes with 'side' context are output for all post types other than 'page'.
						 *
						 * The submitpost box is a meta box with 'side' context, so this hook fires just before it is output.
						 *
						 * @param WP_Post $post Post object.
						 */
						do_action( 'submitpost_box', $post );

						do_meta_boxes( $post_type, 'side', $post );
						?>
					</div>
					<div id="postbox-container-2" class="postbox-container">
						<?php
						do_meta_boxes( null, 'normal', $post );

						/**
						 * Fires after 'normal' context meta boxes have been output for all post types other than 'page'.
						 *
						 * @param WP_Post $post Post object.
						 */
						do_action( 'edit_form_advanced', $post );

						do_meta_boxes( null, 'advanced', $post );
						?>
					</div>
					<?php
					/**
					 * Fires after all meta box sections have been output, before the closing #post-body div.
					 *
					 * @param WP_Post $post Post object.
					 */
					do_action( 'dbx_post_sidebar', $post );
					?>
				</div><!-- /post-body -->
				<br class="clear"/>
			</div><!-- /poststuff -->
		</form>
	</div>
</div>

<?php if ( ! wp_is_mobile() && post_type_supports( $post_type, 'title' ) && '' === $post->post_title ) : ?>
	<script type="text/javascript">
		try {document.post.title.focus();} catch (e) {}
	</script>
<?php endif; ?>
