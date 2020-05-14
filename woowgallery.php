<?php
/**
 * Plugin Name: WoowGallery
 * Plugin URI:  http://codeasily.com
 * Description: WoowGallery is best responsive WordPress gallery plugin.
 * Author:      CodEasily.com Team
 * Author URI:  http://codeasily.com
 * Version:     1.0.0
 * Text Domain: woowgallery
 * Domain Path: languages
 * Licence: GPLv2
 *
 * WoowGallery is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * WoowGallery is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with WoowGallery. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package   woowgallery
 * @fs_ignore /assets/vendor/, /includes/libraries/
 */

namespace WoowGallery;

use WoowGallery\Admin\Admin;
use WoowGallery\Admin\Gutenberg;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

if ( function_exists( 'woow_fs' ) ) {
	woow_fs()->set_basename( true, __FILE__ );
} else {
	/**
	 * WoowGallery Constants.
	 */
	define( 'WOOWGALLERY_VERSION', '1.0.0' );
	define( 'WOOWGALLERY_SLUG', 'woowgallery' );
	define( 'WOOWGALLERY_FILE', __FILE__ );
	define( 'WOOWGALLERY_PATH', plugin_dir_path( __FILE__ ) );
	define( 'WOOWGALLERY_URL', plugin_dir_url( __FILE__ ) );
	define( 'WOOWGALLERY_DIRNAME', basename( WOOWGALLERY_PATH ) );

	// DO NOT REMOVE THIS IF, IT IS ESSENTIAL FOR THE `function_exists` CALL ABOVE TO PROPERLY WORK.
	if ( ! function_exists( 'woow_fs' ) ) {
		/**
		 * Create a helper function for easy SDK access.
		 */
		function woow_fs() {
			global $woow_fs;

			if ( ! isset( $woow_fs ) ) {
				// Include Freemius SDK.
				require_once WOOWGALLERY_PATH . 'freemius/start.php';

				$woow_fs = fs_dynamic_init(
					[
						'id'                  => '6026',
						'slug'                => 'woowgallery',
						'type'                => 'plugin',
						'public_key'          => 'pk_cc0fe81f5fd36b175cf9234630313',
						'is_premium'          => true,
						// If your plugin is a serviceware, set this option to false.
						'has_premium_version' => true,
						'has_addons'          => false,
						'has_paid_plans'      => true,
						'trial'               => [
							'days'               => 7,
							'is_require_payment' => true,
						],
						'menu'                => [
							'slug'   => 'woowgallery-settings',
							'parent' => [
								'slug' => 'edit.php?post_type=woowgallery',
							],
						],
						// Set the SDK to work in a sandbox mode (for development & testing).
						// IMPORTANT: MAKE SURE TO REMOVE SECRET KEY BEFORE DEPLOYMENT.
						'secret_key'          => 'sk_Kv-2&2a0CdQKJ<F!JbKgyda.*nXKC',
					]
				);
			}

			return $woow_fs;
		}

		// Init Freemius.
		woow_fs();
		// Signal that SDK was initiated.
		do_action( 'woow_fs_loaded' );

		/**
		 * Custom product icon
		 */
		woow_fs()->add_filter(
			'plugin_icon',
			function () {
				return WOOWGALLERY_PATH . 'assets/images/woowgallery-logo.png';
			}
		);
	}

	/**
	 * Main plugin class.
	 *
	 * @package WoowGallery
	 * @author  Sergey Pasyuk
	 */
	class WoowGallery {

		/**
		 * Primary class constructor.
		 */
		public function __construct() {

			// Init Classes Auto Load.
			require_once WOOWGALLERY_PATH . 'includes/class-autoload.php';
			Autoload::add_namespace( 'WoowGallery', WOOWGALLERY_PATH . 'includes' );

			require_once WOOWGALLERY_PATH . 'functions/utils.php';
			require_once WOOWGALLERY_PATH . 'functions/setup.php';
			require_once WOOWGALLERY_PATH . 'functions/helpers.php';

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

}
