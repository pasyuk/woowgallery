<?php
/**
 * Main plugin class
 *
 * @package WoowGallery
 * @author  Sergey Pasyuk
 */

namespace WoowGallery;

use WoowGallery\Admin\Admin;
use WoowGallery\Admin\Elementor;
use WoowGallery\Admin\Gutenberg;
use WoowGallery\Woocommerce\Woocommerce;

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

		// Load the plugin widget.
		add_action( 'widgets_init', [ $this, 'widgets' ] );

	}

	/**
	 * Loads the plugin into WordPress.
	 */
	public function init() {

		// Fire a hook before the plugin loaded.
		do_action( 'woowgallery_pre_init' );

		woowgallery_upgrade();

		new Assets();
		new Posttypes();
		new Taxonomies();
		new Skins();
		new Shortcodes();
		new Rest_Routes();

		if ( is_admin() ) {
			new Admin();
		}

		new Woocommerce();
		new Elementor();
		new Gutenberg();
		new Frontend();

		//new Setup();

		// Run hook once WoowGallery has been initialized.
		do_action( 'woowgallery_init' );

		// Add hook for when WoowGallery has loaded.
		do_action( 'woowgallery_loaded' );

	}

	/**
	 * Registers the WoowGallery widgets.
	 */
	public function widgets() {

		register_widget( 'WoowGallery\Widgets' );

	}

}

new WoowGallery();
