<?php
/**
 * Gutenberg class.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

namespace WoowGallery\Admin;

use WoowGallery\Posttypes;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * Class Post
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
			plugins_url( 'assets/css/block.css', WOOWGALLERY_FILE ),
			[ 'wp-edit-blocks' ],
			WOOWGALLERY_VERSION
		);
		wp_register_script(
			WOOWGALLERY_SLUG . '-block-script',
			plugins_url( 'assets/js/block.js', WOOWGALLERY_FILE ),
			[
				WOOWGALLERY_SLUG . '-script',
				WOOWGALLERY_SLUG . '-admin-script',
				WOOWGALLERY_SLUG . '-editor-modal-script',
				'wp-blocks',
				'wp-components',
				'wp-element',
				'wp-i18n',
				'wp-editor',
				'jquery',
				'vuejs',
			],
			WOOWGALLERY_VERSION,
			true
		);

		// Register our block, and explicitly define the attributes we accept.
		register_block_type(
			'woowplugins/woowgallery',
			[
				'editor_script'   => WOOWGALLERY_SLUG . '-block-script',
				'editor_style'    => WOOWGALLERY_SLUG . '-block-style',
				'attributes'      => [
					'id'       => [
						'type'    => 'number',
						'default' => 0,
					],
					'posttype' => [
						'type'    => 'string',
						'default' => Posttypes::GALLERY_POSTTYPE,
					],
					'width'    => [
						'type'    => 'object',
						'default' => (object) [
							'value' => 100,
							'unit'  => '%',
						],
					],
					'align'    => [
						'type'    => 'string',
						'default' => 'center',
					],
				],
				'render_callback' => [ $this, 'render_block' ],
			]
		);
	}

	/**
	 * Render the contents of the block
	 *
	 * @param array $args Attributes.
	 *
	 * @return string
	 */
	public function render_block( $args ) {
		$id   = $args['id'];
		$type = $args['posttype'];

		$args['callback'] = 'wgSkinInit';

		return woowgallery( $id, $type, $args, true );
	}

}
