<?php
/**
 * Main plugin class
 *
 * @package WoowGallery
 * @author  Sergey Pasyuk
 */

namespace WoowGallery;

use WoowGallery\Admin\Admin;
use WoowGallery\Admin\Gutenberg;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * Class WoowGallery
 */
class WoowGallery {

	/**
	 * Primary class constructor.
	 */
	public function __construct() {

		// Init Classes Auto Load.
		require_once WOOWGALLERY_PATH . '/includes/class-autoload.php';
		Autoload::add_namespace( 'WoowGallery', WOOWGALLERY_PATH . '/includes' );

		require_once WOOWGALLERY_PATH . '/functions/utils.php';
		require_once WOOWGALLERY_PATH . '/functions/setup.php';
		require_once WOOWGALLERY_PATH . '/functions/helpers.php';

		// Load the plugin.
		add_action( 'init', [ $this, 'init' ] );

	}

	/**
	 * Returns the license key for WoowGallery.
	 *
	 * @return array $key The user's license key for WoowGallery.
	 */
	public static function get_license() {
		//$license = Settings::get_settings( 'license' );
		//
		//if ( empty( $license ) && defined( 'WOOWGALLERY_LICENSE' ) ) {
		//	$license = WOOWGALLERY_LICENSE;
		//}

		$license = woow_fs()->can_use_premium_code();

		return apply_filters( 'woowgallery_license', $license );
	}

	/**
	 * Loads the plugin into WordPress.
	 */
	public function init() {

		// Fire a hook before the plugin loaded.
		do_action( 'woowgallery_pre_init' );

		new Assets();
		new Posttypes();
		new Taxonomies();
		new Skins();
		new Shortcodes();
		new Widgets();
		new Rest_Routes();

		if ( is_admin() ) {
			new Admin();
		}

		new Gutenberg();
		new Frontend();

		// Run hook once WoowGallery has been initialized.
		do_action( 'woowgallery_init' );

		// Add hook for when WoowGallery has loaded.
		do_action( 'woowgallery_loaded' );

	}
}

new WoowGallery();
