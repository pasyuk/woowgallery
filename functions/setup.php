<?php
/**
 * WoowGallery Setup functions.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

use WoowGallery\Admin\Notice;

// Fire a hook for plugin activation.
register_activation_hook( WOOWGALLERY_FILE, 'woowgallery_activation_hook' );

// Fire a hook for plugin deactivation.
register_deactivation_hook( WOOWGALLERY_FILE, 'woowgallery_deactivation_hook' );

// Fire a hook for plugin uninstall.
register_uninstall_hook( __FILE__, 'woowgallery_uninstall_hook' );

// Check setup.
add_action( 'admin_init', 'woowgallery_check_environment', 0 );


/**
 * Fired when the plugin is activated.
 *
 * @param boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false otherwise.
 *
 * @global int    $wp_version   The version of WordPress for this install.
 * @global object $wpdb         The WordPress database object.
 */
function woowgallery_activation_hook( $network_wide ) {

	global $wp_version;
	if ( version_compare( $wp_version, '4.6.0', '<' ) ) {
		deactivate_plugins( plugin_basename( WOOWGALLERY_FILE ) );
		// Translators: %s: URL.
		wp_die( sprintf( __( 'Sorry, but your version of WordPress does not meet WoowGallery\'s required version of <strong>4.0.0</strong> or higher to run properly. The plugin has been deactivated. <a href="%s">Click here to return to the Dashboard</a>.', 'woowgallery' ), get_admin_url() ) ); // @codingStandardsIgnoreLine
	}

	if ( is_multisite() && $network_wide ) {
		$site_list = get_sites();
		foreach ( (array) $site_list as $site ) {
			switch_to_blog( $site['blog_id'] );

			// Set default options.
			woowgallery_setup();

			restore_current_blog();
		}
	} else {
		// Set default options.
		woowgallery_setup();
	}

}

/**
 * Fired when the plugin is deactivated to clear flushed permalinks flag and flush the permalinks.
 *
 * @param boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false otherwise.
 */
function woowgallery_deactivation_hook( $network_wide ) {

	if ( is_multisite() && $network_wide ) {
		$site_list = get_sites();
		foreach ( (array) $site_list as $site ) {
			switch_to_blog( $site['blog_id'] );

			// Flush rewrite rules.
			flush_rewrite_rules();

			restore_current_blog();
		}
	} else {
		// Flush rewrite rules.
		flush_rewrite_rules();
	}

}

/**
 * Fired when the plugin is uninstalled.
 *
 * @global int    $wp_version The version of WordPress for this install.
 * @global object $wpdb       The WordPress database object.
 */
function woowgallery_uninstall_hook() {

	if ( is_multisite() ) {
		$site_list = get_sites();
		foreach ( (array) $site_list as $site ) {
			switch_to_blog( $site['blog_id'] );

			// Delete options.
			woowgallery_delete();

			restore_current_blog();
		}
	} else {
		// Delete options.
		woowgallery_delete();
	}

}

/**
 * Setup the plugin settings in DB
 */
function woowgallery_setup() {

	$version = get_option( 'woowgallery_version' );
	// Set default options.
	if ( empty( $version ) ) {
		update_option( 'woowgallery_version', WOOWGALLERY_VERSION );
		update_option( 'woowgallery_install_date', time() );
		update_option( 'woowgallery_options', woowgallery_settings_default() );
	} else {
		woowgallery_upgrade();
	}

}

/**
 * Delete the plugin settings from DB
 */
function woowgallery_delete() {

	delete_option( 'woowgallery_version' );
	delete_option( 'woowgallery_install_date' );
	delete_option( 'woowgallery_options' );

	delete_option( 'woowgallery_notices' );

}

/**
 * Display a nag notice if the server's configuration doesn't match requirements
 */
function woowgallery_check_environment() {

	// Output a notice if PHP version less than 5.4.
	if ( (float) phpversion() < 5.4 ) {
		Notice::add_message( __( 'WoowGallery requires PHP 5.3 or greater for some specific functionality. Please have your web host resolve this.', 'woowgallery' ) );
	}

	// Output a notice if missing cropping extensions because WoowGallery needs them.
	if ( ! ( extension_loaded( 'gd' ) && function_exists( 'gd_info' ) ) && ! extension_loaded( 'imagick' ) ) {
		Notice::add_message( __( 'The GD or Imagick libraries are not installed on your server. WoowGallery requires at least one (preferably Imagick) in order to crop images and may not work properly without it. Please contact your webhost and ask them to compile GD or Imagick for your PHP install.', 'woowgallery' ) );
	}

}

/**
 * Handles any necessary upgrades.
 */
function woowgallery_upgrade() {

	$install_date = get_option( 'woowgallery_install_date' );
	if ( empty( $install_date ) ) {
		update_option( 'woowgallery_install_date', time() );
	}

	$version = get_option( 'woowgallery_version' );
	if ( $version && version_compare( WOOWGALLERY_VERSION, $version, '>' ) ) {
		$default_options = woowgallery_settings_default();
		$options         = get_option( 'woowgallery_options' );
		$new_options     = woowgallery_array_diff_key_recursive( $default_options, $options );
		if ( ! empty( $new_options ) ) {
			$options = array_replace_recursive( $options, $new_options );
			update_option( 'woowgallery_options', $options );
		}

		do_action( 'woowgallery_upgrade' );

		update_option( 'woowgallery_version', WOOWGALLERY_VERSION );
	}

}

/**
 * Default WoowGallery Settings.
 *
 * @return array
 */
function woowgallery_settings_default() {
	$settings = [
		'default_skin'           => 'amron',
		'product_gallery'        => '0',
		'product_gallery_skin'   => 'amron',
		'default_lightbox'       => 'woowlightbox',
		'cache'                  => '12',
		'custom_css'             => '',
		'edit_gallery_view'      => 'grid',
		'edit_gallery_per_page'  => '40',
		'edit_dynamic_per_page'  => '20',
		'edit_album_per_page'    => '20',
		'selection_prepend'      => '0',
		'thumb_width'            => '400',
		'thumb_height'           => '600',
		'thumb_quality'          => '82',
		'image_width'            => '2200',
		'image_height'           => '2200',
		'image_quality'          => '82',
		'media_delete'           => '0',
		'woowgallery_categories' => '1',
		'woowgallery_tags'       => '1',
	];

	return apply_filters( 'woowgallery_settings_default', $settings );
}
