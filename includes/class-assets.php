<?php
/**
 * Assets class.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

namespace WoowGallery;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * Class Admin
 */
class Assets {

	/**
	 * Primary class constructor.
	 */
	public function __construct() {

		// Register assets.
		$this->global_scripts();
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ], 2 );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts_l10n' ], 999 );
	}

	/**
	 * Register global scripts.
	 */
	public function global_scripts() {

		$suffix = SCRIPT_DEBUG ? '' : '.min';

		// Vendor scripts.
		wp_register_script( 'vuejs', plugins_url( "assets/vendor/vue{$suffix}.js", WOOWGALLERY_FILE ), [], '2.4.2', true );

		wp_register_style( 'swiper', plugins_url( "assets/vendor/swiper/swiper{$suffix}.css", WOOWGALLERY_FILE ), [], '5.2.1' );
		wp_register_script( 'swiper', plugins_url( "assets/vendor/swiper/swiper{$suffix}.js", WOOWGALLERY_FILE ), [], '5.2.1', true );

		// Register frontend scripts.
		wp_register_script( WOOWGALLERY_SLUG . '-elementor', plugins_url( "assets/js/elementor{$suffix}.js", WOOWGALLERY_FILE ), [ 'jquery' ], WOOWGALLERY_VERSION, true );

		wp_register_style( WOOWGALLERY_SLUG . '-style', plugins_url( 'assets/css/woowgallery.css', WOOWGALLERY_FILE ), [], WOOWGALLERY_VERSION );
		wp_register_script( WOOWGALLERY_SLUG . '-script', plugins_url( 'assets/js/woowgallery.min.js', WOOWGALLERY_FILE ), [], WOOWGALLERY_VERSION, false );
		$script_localize = [
			'ajaxurl'   => admin_url( 'admin-ajax.php' ),
			'wpApiRoot' => esc_url_raw( rest_url() ),
			'g11n'      => apply_filters( 'woowgallery_frontend_scripts_g11n', [] ),
			'skins'     => null,
		];
		if ( woow_fs()->is__premium_only() ) {
			if ( woow_fs()->has_active_valid_license() ) {
				$script_localize['status'] = 'premium';
			} elseif ( woow_fs()->is_trial() ) {
				$script_localize['status'] = 'trial';
			} else {
				$script_localize['status'] = 'pending';
			}
		}
		wp_localize_script( WOOWGALLERY_SLUG . '-script', 'WoowGallery', $script_localize );
	}

	/**
	 * Register and Loads styles / scripts for all WOOWGALLERY-based Administration Screens.
	 */
	public function admin_scripts() {

		$suffix = SCRIPT_DEBUG ? '' : '.min';

		// Vendor scripts.
		wp_register_script( 'portal-vue', plugins_url( "assets/vendor/portal-vue.umd{$suffix}.js", WOOWGALLERY_FILE ), [ 'vuejs' ], '2.1.5', true );

		wp_register_style( 'vue-toasted', plugins_url( 'assets/vendor/vue-toasted/vue-toasted.min.css', WOOWGALLERY_FILE ), [], '1.1.24' );
		wp_register_script( 'vue-toasted', plugins_url( "assets/vendor/vue-toasted/vue-toasted{$suffix}.js", WOOWGALLERY_FILE ), [ 'vuejs' ], '1.1.24', true );

		wp_register_style( 'vue-multiselect', plugins_url( 'assets/vendor/vue-multiselect/vue-multiselect.min.css', WOOWGALLERY_FILE ), [], '2.1.6' );
		wp_register_script( 'vue-multiselect', plugins_url( 'assets/vendor/vue-multiselect/vue-multiselect.min.js', WOOWGALLERY_FILE ), [ 'vuejs' ], '2.1.6', true );

		wp_register_script( 'Sortable', plugins_url( "assets/vendor/Sortable{$suffix}.js", WOOWGALLERY_FILE ), [], '1.10.1', true );

		wp_register_script( 'clipboard', plugins_url( "assets/vendor/clipboard{$suffix}.js", WOOWGALLERY_FILE ), [], '1.6.0', true );

		wp_register_script( 'filtrex', plugins_url( 'assets/vendor/filtrex.js', WOOWGALLERY_FILE ), [], '20150306', true );

		wp_register_style( 'spectrum', plugins_url( "assets/vendor/spectrum/spectrum{$suffix}.css", WOOWGALLERY_FILE ), [], '1.8.0' );
		wp_register_script( 'spectrum', plugins_url( "assets/vendor/spectrum/spectrum{$suffix}.js", WOOWGALLERY_FILE ), [ 'jquery' ], '1.8.0', true );

		// Admin scripts.
		wp_register_style( WOOWGALLERY_SLUG . '-admin-global', plugins_url( 'assets/css/global.css', WOOWGALLERY_FILE ), [], WOOWGALLERY_VERSION );
		wp_register_style( WOOWGALLERY_SLUG . '-admin-style', plugins_url( 'assets/css/admin.css', WOOWGALLERY_FILE ), [], WOOWGALLERY_VERSION );
		wp_register_script(
			WOOWGALLERY_SLUG . '-admin-script',
			plugins_url( 'assets/js/admin.min.js', WOOWGALLERY_FILE ),
			[
				'jquery',
				'underscore',
				'clipboard',
				'wp-i18n',
			],
			WOOWGALLERY_VERSION,
			true
		);

		// Settins page assets.
		wp_register_style( WOOWGALLERY_SLUG . '-freemius-style', plugins_url( 'assets/css/freemius.css', WOOWGALLERY_FILE ), [], WOOWGALLERY_VERSION );
		wp_register_style( WOOWGALLERY_SLUG . '-settings-style', plugins_url( 'assets/css/settings.css', WOOWGALLERY_FILE ), [ 'vue-toasted', 'spectrum', WOOWGALLERY_SLUG . '-freemius-style' ], WOOWGALLERY_VERSION );
		wp_register_script(
			WOOWGALLERY_SLUG . '-settings-script',
			plugins_url( 'assets/js/settings.min.js', WOOWGALLERY_FILE ),
			[
				WOOWGALLERY_SLUG . '-admin-script',
				'vuejs',
				'vue-toasted',
				'backbone',
				'filtrex',
				'spectrum',
			],
			WOOWGALLERY_VERSION,
			true
		);

		// Editor Modal assets.
		wp_register_style( WOOWGALLERY_SLUG . '-editor-modal-style', plugins_url( 'assets/css/editor-modal.css', WOOWGALLERY_FILE ), [], WOOWGALLERY_VERSION );
		wp_register_script(
			WOOWGALLERY_SLUG . '-editor-modal-script',
			//plugins_url( 'assets/js/editor-modal.min.js', WOOWGALLERY_FILE ),
			plugins_url( 'assets/js/editor-modal.js', WOOWGALLERY_FILE ),
			[
				WOOWGALLERY_SLUG . '-admin-script',
				'vuejs',
				'backbone',
			],
			WOOWGALLERY_VERSION,
			true
		);

		// Edit Gallery assets.
		wp_register_style(
			WOOWGALLERY_SLUG . '-edit-woowgallery-style',
			plugins_url( 'assets/css/edit-woowgallery.css', WOOWGALLERY_FILE ),
			[
				'vue-toasted',
				'spectrum',
			],
			WOOWGALLERY_VERSION
		);
		wp_register_script(
			WOOWGALLERY_SLUG . '-edit-gallery-script',
			plugins_url( 'assets/js/edit-gallery.min.js', WOOWGALLERY_FILE ),
			[
				WOOWGALLERY_SLUG . '-admin-script',
				'wp-api',
				'jquery',
				'plupload-handlers',
				'quicktags',
				'jquery-ui-sortable',
				'vuejs',
				'portal-vue',
				'vue-toasted',
				'Sortable',
				'backbone',
				'media-views',
				'media-grid',
				'filtrex',
				'spectrum',
			],
			WOOWGALLERY_VERSION,
			true
		);
		wp_register_script(
			WOOWGALLERY_SLUG . '-edit-dynamic-gallery-script',
			plugins_url( 'assets/js/edit-dynamic-gallery.min.js', WOOWGALLERY_FILE ),
			[
				WOOWGALLERY_SLUG . '-admin-script',
				'wp-api',
				'jquery',
				'vuejs',
				'portal-vue',
				'vue-toasted',
				'backbone',
				'filtrex',
				'spectrum',
			],
			WOOWGALLERY_VERSION,
			true
		);
		wp_register_script(
			WOOWGALLERY_SLUG . '-edit-album-script',
			plugins_url( 'assets/js/edit-album.min.js', WOOWGALLERY_FILE ),
			[
				WOOWGALLERY_SLUG . '-admin-script',
				'wp-api',
				'jquery',
				'quicktags',
				'jquery-ui-sortable',
				'vuejs',
				'portal-vue',
				'vue-toasted',
				'Sortable',
				'backbone',
				'filtrex',
				'spectrum',
			],
			WOOWGALLERY_VERSION,
			true
		);

	}

	/**
	 * Global Scripts Localization.
	 */
	public function admin_scripts_l10n() {
		$post_types     = [];
		$post_types_obj = get_post_types(
			[
				'public'       => true,
				'show_in_rest' => true,
			],
			'objects',
			'and'
		);
		$wg_posttypes   = [ Posttypes::ALBUM_POSTTYPE, Posttypes::GALLERY_POSTTYPE, Posttypes::DYNAMIC_POSTTYPE ];
		foreach ( $wg_posttypes as $posttype ) {
			$_post_type = get_post_type_object( $posttype );
			if ( ! $_post_type ) {
				continue;
			}
			$post_types[ $_post_type->name ] = [
				'name'      => $_post_type->name,
				'base'      => $_post_type->rest_base ?: $_post_type->name,
				'icon_html' => wg_posttype_icon( $_post_type ),
			];
		}
		foreach ( $post_types_obj as $_post_type ) {
			if ( in_array( $_post_type->name, $wg_posttypes, true ) ) {
				continue;
			}
			$post_types[ $_post_type->name ] = [
				'name'      => $_post_type->name,
				'base'      => $_post_type->rest_base ?: $_post_type->name,
				'icon_html' => wg_posttype_icon( $_post_type ),
			];
		}
		$script_localize = [
			'l10n'         => apply_filters( 'woowgallery_admin_scripts_l10n', [] ),
			'wpApiRoot'    => esc_url_raw( rest_url() ),
			'wpApiNonce'   => wp_create_nonce( 'wp_rest' ),
			'createNew'    => esc_url( admin_url( 'post-new.php?post_type=' ) ),
			'editModalSrc' => esc_url( admin_url( 'admin.php?page=woowgallery-edit' ) ),
			'post_types'   => $post_types,
		];
		if ( woow_fs()->is__premium_only() ) {
			if ( woow_fs()->has_active_valid_license() ) {
				$script_localize['status'] = 'premium';
			} elseif ( woow_fs()->is_trial() ) {
				$script_localize['status'] = 'trial';
			} else {
				$script_localize['status'] = 'pending';
			}
		}
		wp_localize_script( WOOWGALLERY_SLUG . '-admin-script', 'WoowGalleryAdmin', $script_localize );
	}

}
