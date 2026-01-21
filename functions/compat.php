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
 * Uses mb_convert_encoding() if available (PHP 8.2+), otherwise falls back to utf8_encode()
 *
 * @param string $string String to encode to UTF-8.
 * @return string|false Encoded string or false on failure.
 */
function woowgallery_utf8_encode( $string ) {
	if ( function_exists( 'mb_convert_encoding' ) ) {
		return mb_convert_encoding( $string, 'UTF-8', 'ISO-8859-1' );  // PHP 8.2+ compatible
	} else {
		return utf8_encode( $string );  // Older PHP versions
	}
}

/**
 * Backward compatibility for UTF-8 validation
 * Uses wp_is_valid_utf8() if available (WP 6.9+), otherwise falls back to seems_utf8()
 *
 * @param string $string String to check.
 * @return bool
 */
function woowgallery_is_valid_utf8( $string ) {
	if ( function_exists( 'wp_is_valid_utf8' ) ) {
		return wp_is_valid_utf8( $string );  // WP 6.9+
	} else {
		return function_exists( 'seems_utf8' ) ? seems_utf8( $string ) : true;  // Older WP
	}
}

/**
 * Backward compatibility for content filtering
 * Uses wp_filter_content_tags() if available (WP 5.5+), otherwise falls back to wp_make_content_images_responsive()
 *
 * @param string $content Content to filter.
 * @return string
 */
function woowgallery_filter_content_tags( $content ) {
	if ( function_exists( 'wp_filter_content_tags' ) ) {
		return wp_filter_content_tags( $content );  // WP 5.5+
	} else {
		return function_exists( 'wp_make_content_images_responsive' ) ? wp_make_content_images_responsive( $content ) : $content;  // Older WP
	}
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
	} else {
		// Fallback to direct file operations (older WordPress)
		$output_file = fopen( $file, 'w' );
		if ( $output_file ) {
			$result = fwrite( $output_file, $content );
			fclose( $output_file );
			return $result;
		}
		return false;
	}
}
