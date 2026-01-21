<?php
/**
 * Plugin Name: WoowGallery
 * Plugin URI:  http://woowgallery.com/
 * Description: WoowGallery is the fastest, easiest to use WordPress multifunctional image gallery plugin. Create Featured Posts Gallery and Dynamic Content Gallery with a few click.
 * Author:      Serhii Pasyuk
 * Author URI:  https://profiles.wordpress.org/pasyuk/
 * Version:     1.2.3
 * Text Domain: woowgallery
 * License: GPLv2 or later
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
 * @package         woowgallery
 * @fs_ignore       /assets/vendor/, /includes/libraries/
 * @fs_premium_only /skins/multigrid/assets/, /skins/parallax/assets/
 */

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

if ( function_exists( 'woow_fs' ) ) {
	woow_fs()->set_basename( true, __FILE__ );
} else {
	/**
	 * WoowGallery Constants.
	 */
	define( 'WOOWGALLERY_VERSION', '1.2.3' );
	define( 'WOOWGALLERY_SLUG', 'woowgallery' );
	define( 'WOOWGALLERY_FILE', __FILE__ );
	define( 'WOOWGALLERY_PATH', __DIR__ );
	define( 'WOOWGALLERY_URL', plugin_dir_url( __FILE__ ) );
	define( 'WOOWGALLERY_DIRNAME', basename( WOOWGALLERY_PATH ) );

	// DO NOT REMOVE THIS IF, IT IS ESSENTIAL FOR THE `function_exists` CALL ABOVE TO PROPERLY WORK.
	if ( ! function_exists( 'woow_fs' ) ) {
		/**
		 * Create a helper function for easy SDK access.
		 *
		 * @return Freemius
		 * @noinspection PhpDocMissingThrowsInspection
		 */
		function woow_fs() {
			global $woow_fs;

			if ( ! isset( $woow_fs ) ) {
				// Include Freemius SDK.
				require_once dirname(__FILE__) . '/vendor/freemius/start.php';

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
						// Automatically removed in the free version. If you're not using the
						// auto-generated free version, delete this line before uploading to wp.org.
						'wp_org_gatekeeper'   => 'OA7#BoRiBNqdf52FvzEf!!074aRLPs8fspif$7K1#4u4Csys1fQlCecVcUTOs2mcpeVHi#C2j9d09fOTvbC0HloPT7fFee5WdS3G',
						'trial'               => [
							'days'               => 7,
							'is_require_payment' => true,
						],
						'has_affiliation'     => 'all',
						'menu'                => [
							'slug'           => 'woowgallery-settings',
							'parent'         => [
								'slug' => 'edit.php?post_type=woowgallery',
							],
						],
					]
				);
			}

			return $woow_fs;
		}

		// Init Freemius.
		woow_fs();
		// Signal that SDK was initiated.
		do_action( 'woow_fs_loaded' );

		// Hook uninstall cleanup to Freemius after_uninstall action.
		woow_fs()->add_action( 'after_uninstall', 'woowgallery_uninstall_hook' );

		/**
		 * Custom product icon
		 */
		woow_fs()->add_filter(
			'plugin_icon',
			function () {
				return WOOWGALLERY_PATH . '/assets/images/woowgallery-logo.png';
			}
		);
	}

	require_once WOOWGALLERY_PATH . '/class-woowgallery.php';
}
