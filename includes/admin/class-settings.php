<?php
/**
 * Settings class.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

namespace WoowGallery\Admin;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

use WoowGallery\Lightbox;
use WoowGallery\Posttypes;
use WoowGallery\Skins;
use WoowGallery\Taxonomies;

/**
 * Class Settings
 */
class Settings {

	/**
	 * Settings key.
	 */
	const SETTINGS_KEY = 'woowgallery_settings';

	/**
	 * Settings Page slug.
	 */
	const MENU_SLUG = 'woowgallery-settings';
	/**
	 * Holds the settings array.
	 *
	 * @var string
	 */
	private static $settings;
	/**
	 * Holds the submenu pagehook.
	 *
	 * @var string
	 */
	public $hook;

	/**
	 * WoowGallery_Settings constructor.
	 */
	public function __construct() {

		// Add custom settings submenu.
		add_action( 'admin_menu', [ $this, 'admin_menu' ], 11 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_freemius_assets' ] );

		// Add the settings menu item to the Plugins table.
		add_filter( 'plugin_action_links_' . plugin_basename( WOOWGALLERY_FILE ), [ $this, 'settings_link' ] );

	}

	/**
	 * Register the Settings submenu item for WoowGallery.
	 */
	public function admin_menu() {

		// Register the submenu.
		$this->hook = add_submenu_page(
			'edit.php?post_type=' . Posttypes::GALLERY_POSTTYPE,
			__( 'WoowGallery Settings', 'woowgallery' ),
			__( 'Settings', 'woowgallery' ),
			apply_filters( 'woowgallery_menu_cap', 'manage_options' ),
			self::MENU_SLUG,
			[ $this, 'settings_page' ]
		);

		// If successful, load admin assets only on that page and check for addons refresh.
		if ( ! $this->hook ) {
			return;
		}

		add_action( 'load-' . $this->hook, [ $this, 'settings_page_assets' ] );
		add_action( 'load-' . $this->hook, [ $this, 'saving_settings' ] );

	}

	/**
	 * Callback to output the WoowGallery settings page.
	 */
	public function settings_page() {
		do_action( 'woowgallery_head' );

		// Get the settings data.
		$settings = self::get_settings();
		// Get Skins data.
		$skins = Skins::get_instance()->get_skins();

		?>
		<div class="wrap woowgallery-wrap">
			<h1 class="wp-heading-inline">
				<?php esc_html_e( 'Settings', 'woowgallery' ); ?>
			</h1>
			<?php
			do_action( 'woowgallery_settings_notice' );

			// Load view.
			Admin::load_template(
				'settings',
				compact( 'settings', 'skins' )
			);

			// Load view.
			Admin::load_template(
				'skins',
				compact( 'settings', 'skins' )
			);
			?>
		</div>
		<?php
	}

	/**
	 * Helper method for getting a setting's value. Falls back to the default
	 * setting value if none exists in the options table.
	 *
	 * @param string $key     The setting key to retrieve.
	 * @param mixed  $default The default setting key on failure.
	 *
	 * @return mixed settings value on success, null on failure.
	 */
	public static function get_settings( $key = null, $default = null ) {

		if ( empty( self::$settings ) ) {
			// Get the settings.
			$default_settings = (array) self::get_settings_default();
			$stored_settings  = (array) get_option( self::SETTINGS_KEY, [] );
			$settings         = array_merge( $default_settings, $stored_settings );

			// Allow devs to filter.
			self::$settings = apply_filters( 'woowgallery_settings', $settings, $key );
		}

		if ( ! empty( $key ) ) {
			return isset( self::$settings[ $key ] ) ? self::$settings[ $key ] : $default;
		}

		return self::$settings;
	}

	/**
	 * Retrieves the default settings
	 *
	 * @param string $key The default setting key to retrieve.
	 *
	 * @return array Array of default settings.
	 */
	public static function get_settings_default( $key = null ) {

		// Get default settings.
		$settings = woowgallery_settings_default();

		// Allow devs to filter the defaults.
		$settings = apply_filters( 'woowgallery_settings_default', $settings, $key );

		if ( ! empty( $key ) ) {
			return isset( $settings[ $key ] ) ? $settings[ $key ] : null;
		}

		return $settings;
	}

	/**
	 * Loads assets for the settings page.
	 */
	public function settings_page_assets() {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
		add_filter( 'woowgallery_admin_scripts_l10n', [ $this, 'l10n' ] );
	}

	/**
	 * Register and enqueue settings page specific CSS/JS.
	 */
	public function enqueue_freemius_assets() {
		$screen = get_current_screen();
		if ( strpos( $screen->base, $this->hook ) === 0 ) {
			wp_enqueue_style( WOOWGALLERY_SLUG . '-freemius-style' );
		}
	}

	/**
	 * Register and enqueue settings page specific CSS/JS.
	 */
	public function enqueue_admin_assets() {
		wp_enqueue_style( WOOWGALLERY_SLUG . '-settings-style' );
		wp_enqueue_script( WOOWGALLERY_SLUG . '-settings-script' );

		self::enqueue_code_editor();

		// Run a hook to load in custom scripts.
		do_action( 'woowgallery_settings_assets' );
	}

	/**
	 * Register and enqueue settings page specific CSS/JS.
	 */
	public static function enqueue_code_editor() {
		// Code Editor.
		if ( ! defined( 'IFRAME_REQUEST' ) && function_exists( 'wp_enqueue_code_editor' ) ) {
			$settings_css = wp_enqueue_code_editor( [ 'type' => 'text/css' ] );

			// do nothing if CodeMirror disabled.
			if ( false !== $settings_css ) {
				// initialization.
				wp_add_inline_script(
					'code-editor',
					sprintf( 'jQuery(function($){ if(document.getElementById("wg-custom-css")){ window.wgCodeEditor_custom_css = wp.codeEditor.initialize("wg-custom-css", %s); } });', wp_json_encode( $settings_css ) )
				);
			}
		}
	}

	/**
	 * Saves Settings:
	 */
	public function saving_settings() {
		// Check if user pressed the 'Update' button and nonce is valid.
		if ( ! ( isset( $_POST['woowgallery-settings-submit'] ) || isset( $_POST['woowgallery-settings-reset'] ) ) ) {
			return;
		}
		if ( ! woowgallery_verify_nonce( 'settings_save', false ) ) {
			return;
		}
		if ( isset( $_POST['woowgallery-settings-submit'] ) ) {
			// Update settings.
			self::save_settings( woowgallery_POST( 'settings', [] ) );

			// Output an admin notice so the user knows what happened.
			Notice::add_message( __( 'Settings saved successfully.', 'woowgallery' ), Notice::TYPE_SUCCESS );
		}

		if ( isset( $_POST['woowgallery-settings-reset'] ) ) {
			// Update settings.
			self::save_settings( [] );
			delete_option( Skins::PRESETS_KEY );
			delete_option( Lightbox::OPTIONS_KEY );

			// Delete gallery taxonomies that must not exist.
			$terms = (array) get_terms( Taxonomies::GALLERY_TAXONOMY_NAME, [ 'get' => 'all' ] );
			foreach ( $terms as $term ) {
				$gallery_id   = get_term_meta( $term->term_id, '_woowgallery_id', true );
				$gallery_post = get_post( $gallery_id );
				if ( empty( $gallery_post ) ) {
					wp_delete_term( $term->term_id, Taxonomies::GALLERY_TAXONOMY_NAME );
				}
			}

			// Output an admin notice so the user knows what happened.
			Notice::add_message( __( 'Settings reset successfully.', 'woowgallery' ), Notice::TYPE_SUCCESS );
		}
	}

	/**
	 * Helper method for updating a setting's value.
	 *
	 * @param mixed  $new_settings The value to set for the key.
	 * @param string $key          Key to update.
	 */
	public static function save_settings( $new_settings, $key = null ) {

		// Allow devs to filter.
		$new_settings = apply_filters( 'woowgallery_settings_save', $new_settings, $key );
		$old_settings = self::get_settings();

		if ( $key ) {
			$settings         = $old_settings;
			$settings[ $key ] = $new_settings;
		} else {
			$settings = $new_settings;

			if ( ! empty( $settings['default_lightbox'] ) ) {
				$lb                 = $settings['default_lightbox'];
				$lightbox           = Lightbox::get_instance();
				$lb_settings        = get_option( Lightbox::OPTIONS_KEY, [] );
				$lb_settings[ $lb ] = $lightbox->format_settings( woowgallery_POST( '_woowgallery_lightbox', [] ), $lb );

				// reset cached variable.
				$lightbox->lightbox = [];

				update_option( Lightbox::OPTIONS_KEY, $lb_settings );
			}
		}

		// Set flag to flush rewrite rules.
		$flush_rewrite_rules = false;
		$permalink_base      = [];

		// Check if permalink base changed for WoowGallery post types.
		$posttypes = [ Posttypes::GALLERY_POSTTYPE, Posttypes::DYNAMIC_POSTTYPE, Posttypes::ALBUM_POSTTYPE ];
		foreach ( $posttypes as $pt ) {
			$_key              = 'permalink_base_' . $pt;
			$settings[ $_key ] = isset( $settings[ $_key ] ) ? trim( $settings[ $_key ] ) : '';
			if ( empty( $settings[ $_key ] ) ) {
				$settings[ $_key ] = $pt;
			}
			if ( empty( $old_settings[ $_key ] ) || $settings[ $_key ] !== $old_settings[ $_key ] ) {
				$flush_rewrite_rules = true;
			}
			$permalink_base[] = $settings[ $_key ];
		}

		// Check if permalink base unique for each WoowGallery post type.
		$permalink_base = array_unique( $permalink_base );
		if ( count( $permalink_base ) < count( $posttypes ) ) {
			foreach ( $posttypes as $pt ) {
				$settings[ 'permalink_base_' . $pt ] = $pt;
			}

			// Output an admin notice so the user knows what happened.
			Notice::add_message( __( 'Permalink base slugs must be unique for each post type.', 'woowgallery' ) );
		}

		if ( ! $flush_rewrite_rules ) {
			// Check if standalone option changed/enabled for WoowGallery post types.
			foreach ( $posttypes as $pt ) {
				$_key = 'standalone_' . $pt;
				if ( ! empty( $settings[ $_key ] ) && empty( $old_settings[ $_key ] ) ) {
					$flush_rewrite_rules = true;
					break;
				}
			}
		}

		// Clear cached variable.
		self::$settings = null;
		// Update option.
		update_option( self::SETTINGS_KEY, $settings );

		// Flush rewrite rules.
		if ( $flush_rewrite_rules ) {
			// flush_rewrite_rules( false ); - Flush rewrite rules not working here, so we just clear the rules in DataBase.
			update_option( 'rewrite_rules', '' );
		}
	}

	/**
	 * Callback for l10n filter.
	 *
	 * @param array $l10n Localization.
	 *
	 * @return array
	 */
	public function l10n( $l10n ) {
		$settings = self::get_settings();

		return array_merge(
			$l10n,
			[
				'siteurl'                               => site_url(),
				'_nonce_woowgallery_skin_settings_save' => wp_create_nonce( 'skin_settings_save' ),
				'fill_preset_name'                      => __( 'Fill the Preset Name', 'woowgallery' ),
				'delete_default_preset_error'           => __( 'You can\'t delete default skin preset.', 'woowgallery' ),
				'default_skin'                          => $settings['default_skin'],
				'default_lightbox'                      => $settings['default_lightbox'],
				'selected_skin'                         => '',
			]
		);
	}

	/**
	 * Add Settings page link to plugin action links in the Plugins table.
	 *
	 * @param array $links Default plugin action links.
	 *
	 * @return array $links Amended plugin action links.
	 */
	public function settings_link( $links ) {

		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url(
				add_query_arg(
					[
						'post_type' => Posttypes::GALLERY_POSTTYPE,
						'page'      => self::MENU_SLUG,
					],
					admin_url( 'edit.php' )
				)
			),
			__( 'Settings', 'woowgallery' )
		);
		array_unshift( $links, $settings_link );

		return $links;

	}

}


