<?php
/**
 * WordPress Backward Compatibility Functions
 *
 * This file contains compatibility functions to support older WordPress versions
 * while using modern functions when available.
 *
 * @package woowgallery
 */

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * Backward compatibility for UTF-8 encoding
 * Uses mb_convert_encoding() if available, otherwise a manual ISO-8859-1 to UTF-8 conversion.
 *
 * @param string $string String to encode to UTF-8.
 * @return string|false Encoded string or false on failure.
 */
function woowgallery_utf8_encode( $string ) {
	if ( function_exists( 'mb_convert_encoding' ) ) {
		return mb_convert_encoding( $string, 'UTF-8', 'ISO-8859-1' );
	}

	// Manual ISO-8859-1 to UTF-8 conversion when ext/mbstring is unavailable.
	$out = '';
	for ( $i = 0, $len = strlen( $string ); $i < $len; $i++ ) {
		$byte = ord( $string[ $i ] );
		$out .= $byte < 0x80 ? $string[ $i ] : chr( 0xC0 | ( $byte >> 6 ) ) . chr( 0x80 | ( $byte & 0x3F ) );
	}

	return $out;
}

/**
 * Backward compatibility for UTF-8 validation
 * Uses wp_is_valid_utf8() if available (WP 6.9+), otherwise falls back to seems_utf8()
 *
 * @param string $string String to check.
 * @return bool
 */
function woowgallery_is_valid_utf8( $string ) {
	// Variable dispatch: wp_is_valid_utf8() only exists on WP 6.9+, the guard below keeps older versions working.
	$utf8_check = 'wp_is_valid_utf8';
	if ( function_exists( $utf8_check ) ) {
		return $utf8_check( $string );
	}

	// phpcs:ignore WordPress.WP.DeprecatedFunctions.seems_utf8Found -- fallback for WP < 6.9 where wp_is_valid_utf8() does not exist.
	return function_exists( 'seems_utf8' ) ? seems_utf8( $string ) : true;
}

/**
 * Content filtering wrapper.
 * wp_filter_content_tags() exists since WP 5.5, the plugin's minimum supported version.
 *
 * @param string $content Content to filter.
 * @return string
 */
function woowgallery_filter_content_tags( $content ) {
	return wp_filter_content_tags( $content );
}

/**
 * Safe file write with WP_Filesystem fallback
 * Uses WP_Filesystem when available, falls back to direct file operations
 *
 * @param string $file    Path to the file.
 * @param string $content Content to write.
 * @return bool|int Number of bytes written or false on failure.
 */
function woowgallery_put_contents( $file, $content ) {
	global $wp_filesystem;
	
	// Initialize WP_Filesystem if not already available
	if ( ! $wp_filesystem ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
	}

	// Try WP_Filesystem first (modern approach)
	if ( $wp_filesystem && method_exists( $wp_filesystem, 'put_contents' ) ) {
		return $wp_filesystem->put_contents( $file, $content );
	}

	// Fallback when WP_Filesystem could not initialize (e.g. non-direct transport without credentials).
	// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_fopen, WordPress.WP.AlternativeFunctions.file_system_operations_fwrite, WordPress.WP.AlternativeFunctions.file_system_operations_fclose
	$output_file = fopen( $file, 'w' );
	if ( $output_file ) {
		$result = fwrite( $output_file, $content );
		fclose( $output_file );
		return $result;
	}
	// phpcs:enable

	return false;
}
