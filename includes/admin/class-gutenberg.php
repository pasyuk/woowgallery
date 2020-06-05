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
				'wp-blocks',
				'wp-components',
				'wp-element',
				'wp-i18n',
				'wp-editor',
			],
			WOOWGALLERY_VERSION,
			true
		);

		// Register our block, and explicitly define the attributes we accept.
		register_block_type(
			'woowplugins/woowgallery',
			[
				'editor_script' => WOOWGALLERY_SLUG . '-block-script',
				'editor_style'  => WOOWGALLERY_SLUG . '-block-style',
			]
		);
	}
}
