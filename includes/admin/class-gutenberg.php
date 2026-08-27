<?php
/**
 * Gutenberg class.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

namespace WoowGallery\Admin;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * Class Gutenberg
 */
class Gutenberg {

	/**
	 * Primary class constructor.
	 */
	public function __construct() {

		$this->php_block_init();

	}

	/**
	 * Register our block and shortcode.
	 */
	public function php_block_init() {
		if ( ! apply_filters( 'woowgallery_gutenberg_enabled', true ) ) {
			return;
		}

		// Get out quickly if no Gutenberg.
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		$wp_version = get_bloginfo( 'version' );
		if ( version_compare( $wp_version, '6.3', '>=' ) ) {
			$block_api_version = 3;
		} elseif ( version_compare( $wp_version, '5.6', '>=' ) ) {
			$block_api_version = 2;
		} else {
			$block_api_version = 1;
		}

		// Gutenberg assets.
		wp_register_style(
			WOOWGALLERY_SLUG . '-block-style',
			plugins_url( 'assets/css/blocks.style.build.css', WOOWGALLERY_FILE ),
			[],
			WOOWGALLERY_VERSION
		);
		wp_register_script(
			WOOWGALLERY_SLUG . '-block-script',
			plugins_url( 'assets/js/blocks.build.js', WOOWGALLERY_FILE ),
			[
				WOOWGALLERY_SLUG . '-editor-modal-script',
				WOOWGALLERY_SLUG . '-script',
				'wp-api-fetch',
				'wp-block-editor',
				'wp-blocks',
				'wp-components',
				'wp-element',
				'wp-i18n',
			],
			WOOWGALLERY_VERSION,
			true
		);
		wp_add_inline_script(
			WOOWGALLERY_SLUG . '-block-script',
			'window.woowgalleryBlockSettings = ' . wp_json_encode(
				[
					'apiVersion' => $block_api_version,
					'iconUrl'    => plugins_url( 'assets/images/woowgallery-icon.svg', WOOWGALLERY_FILE ),
				]
			) . ';',
			'before'
		);

		$block_args = [
			'editor_script' => WOOWGALLERY_SLUG . '-block-script',
			'editor_style'  => WOOWGALLERY_SLUG . '-block-style',
		];
		if ( version_compare( $wp_version, '5.6', '>=' ) ) {
			$block_args['api_version'] = $block_api_version;
		}

		// Register our block, and explicitly define the attributes we accept.
		register_block_type(
			'woowplugins/woowgallery',
			$block_args
		);
	}
}
