<?php
/**
 * Admin class.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

namespace WoowGallery\Admin;

use WoowGallery\Posttypes;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * Class Admin
 */
class Admin {

	/**
	 * Primary class constructor.
	 */
	public function __construct() {

		add_action( 'admin_notices', 'WoowGallery\Admin\Notice::show_all' );
		add_action( 'admin_init', [ $this, 'add_capabilities' ] );
		add_action( 'in_admin_header', [ $this, 'admin_header' ], 100 );

		// Load admin assets.
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ] );

		new Edit_Modal();
		new Ajax();
		new Edit_Galleries();
		new Edit_Gallery();
		new Edit_Dynamic_Galleries();
		new Edit_Dynamic_Gallery();
		new Edit_Albums();
		new Edit_Album();
		new Post();
		new Media_Library();
		new Media();
		new Settings();

	}

	/**
	 * Registers WoowGallery capabilities for each Role, if they don't already exist.
	 */
	public function add_capabilities() {

		// Grab the administrator role, and if it already has an WoowGallery capability key defined, bail
		// as we only need to register our capabilities once.
		$administrator = get_role( 'administrator' );
		if ( $administrator->has_cap( 'edit_other_woowgallery_posts' ) ) {
			return;
		}

		// If here, we need to assign capabilities
		// Define the roles we want to assign capabilities to.
		$roles = [
			'administrator',
			'editor',
			'author',
			'contributor',
			'subscriber',
		];

		// Iterate through roles.
		foreach ( $roles as $role_name ) {
			// Properly get the role as WP_Role object.
			$role = get_role( $role_name );
			if ( ! is_object( $role ) ) {
				continue;
			}

			$is_administrator = ( 'administrator' === $role_name );

			// Map this Role's Post capabilities to our WoowGallery capabilities.
			$caps = [
				// Meta caps.
				'edit_woowgallery_post'              => $is_administrator || $role->has_cap( 'edit_post' ),
				'read_woowgallery_post'              => $is_administrator || $role->has_cap( 'read_post' ),
				'delete_woowgallery_post'            => $is_administrator || $role->has_cap( 'delete_post' ),

				// Primitive caps outside map_meta_cap().
				'edit_woowgallery_posts'             => $is_administrator || $role->has_cap( 'edit_posts' ),
				'edit_others_woowgallery_posts'      => $is_administrator || $role->has_cap( 'edit_others_posts' ),
				'publish_woowgallery_posts'          => $is_administrator || $role->has_cap( 'publish_posts' ),
				'read_private_woowgallery_posts'     => $is_administrator || $role->has_cap( 'read_private_posts' ),

				// Primitive caps used within map_meta_cap().
				'read_woowgallery'                   => $is_administrator || $role->has_cap( 'read' ),
				'delete_woowgallery_posts'           => $is_administrator || $role->has_cap( 'delete_posts' ),
				'delete_private_woowgallery_posts'   => $is_administrator || $role->has_cap( 'delete_private_posts' ),
				'delete_published_woowgallery_posts' => $is_administrator || $role->has_cap( 'delete_published_posts' ),
				'delete_others_woowgallery_posts'    => $is_administrator || $role->has_cap( 'delete_others_posts' ),
				'edit_private_woowgallery_posts'     => $is_administrator || $role->has_cap( 'edit_private_posts' ),
				'edit_published_woowgallery_posts'   => $is_administrator || $role->has_cap( 'edit_published_posts' ),
				'create_woowgallery_posts'           => $is_administrator || $role->has_cap( 'edit_posts' ),
			];

			// Add the above WoowGallery capabilities to this Role.
			foreach ( $caps as $woowgallery_cap => $value ) {
				$role->add_cap( $woowgallery_cap, $value );
			}
		}

	}

	/**
	 * Outputs the WoowGallery Header in the wp-admin.
	 */
	public function admin_header() {

		// Get the current screen, and check whether we're viewing the WoowGallery or WoowGallery Album Post Types.
		$screen     = get_current_screen();
		$post_types = apply_filters( 'woowgallery_posttypes', [ Posttypes::GALLERY_POSTTYPE, Posttypes::DYNAMIC_POSTTYPE, Posttypes::ALBUM_POSTTYPE ] );
		if ( ! in_array( $screen->post_type, $post_types, true ) ) {
			return;
		}

		// If here, we're on an WoowGallery or Collection screen, so output the header.
		self::load_template(
			'header',
			[
				'logo' => plugins_url( 'assets/images/woowgallery-logo.png', WOOWGALLERY_FILE ),
			]
		);

	}

	/**
	 * Loads a partial view for the Administration screen
	 *
	 * @param string $template PHP file at includes/admin/partials, excluding file extension.
	 * @param array  $data     Any data to pass to the view.
	 *
	 * @return void
	 */
	public static function load_template( $template, $data = [] ) {

		$dir      = WOOWGALLERY_PATH . '/includes/admin/templates/';
		$template = sanitize_file_name( $template );
		if ( file_exists( $dir . $template . '.php' ) ) {
			require_once $dir . $template . '.php';
		}

	}

	/**
	 * Register and Loads styles / scripts for all WOOWGALLERY-based Administration Screens.
	 */
	public function admin_scripts() {

		$admin_menu_css = '';
		$admin_menu_css .= '.menu-icon-woowgallery .wp-menu-image { opacity: 0.8; transition: opacity 0.1s; }';
		$admin_menu_css .= '.menu-icon-woowgallery:hover .wp-menu-image, .menu-icon-woowgallery.wp-menu-open .wp-menu-image { opacity: 1; }';
		wp_add_inline_style( 'admin-menu', $admin_menu_css );
		// Enqueue global assets.
		wp_enqueue_style( WOOWGALLERY_SLUG . '-admin-global' );

		// Get current screen.
		$screen     = get_current_screen();
		$post_types = apply_filters( 'woowgallery_posttypes', [ Posttypes::GALLERY_POSTTYPE, Posttypes::DYNAMIC_POSTTYPE, Posttypes::ALBUM_POSTTYPE ] );
		// Bail if we're on the WoowGallery Post Type screen.
		if ( ! in_array( $screen->post_type, $post_types, true ) ) {
			return;
		}

		// Load necessary admin scripts.
		wp_enqueue_style( WOOWGALLERY_SLUG . '-admin-style' );
		wp_enqueue_script( WOOWGALLERY_SLUG . '-admin-script' );

		// Fire a hook to load in custom admin scripts.
		do_action( 'woowgallery_admin_scripts' );

	}

}
