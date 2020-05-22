<?php
/**
 * Abstract WP_Background_Process class.
 *
 * Uses https://github.com/A5hleyRich/wp-background-processing to handle background processes.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

namespace WoowGallery\Tools;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

if ( ! class_exists( 'WP_Async_Request', false ) ) {
	include_once WOOWGALLERY_PATH . '/includes/libraries/wp-async-request.php';
}

if ( ! class_exists( 'WP_Background_Process', false ) ) {
	include_once WOOWGALLERY_PATH . '/includes/libraries/wp-background-process.php';
}

/**
 * WC_Background_Process class.
 */
abstract class Background_Process extends \WP_Background_Process {}
