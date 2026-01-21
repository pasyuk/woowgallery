<?php
/**
 * Proxy file for IG
 *
 * @var $url string
 */

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

// Use WordPress timezone instead of setting timezone directly
$current_date = gmdate( 'H:i:s - d/m/Y' );
$mimetypes    = [ 'image/png', 'image/jpg', 'image/jpeg', 'image/gif', 'video/mp4' ];

$request = wp_remote_get( $url );
//echo '<pre>' . print_r($url, true) . '</pre>';
$mime    = wp_remote_retrieve_header( $request, 'content-type' );

$expires_offset = 2628000; // 1 month.

// if a valid MIME type exists, display the image.
// by sending appropriate headers and streaming the file.
foreach ( $mimetypes as $mimetype ) {
	if ( $mime === $mimetype ) {
		status_header( 200, 'OK' );
		header( 'Content-type: ' . $mime . ';' );
		header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + $expires_offset ) . ' GMT' );
		header( "Cache-Control: public, max-age=$expires_offset" );
		if ( isset( $url ) ) {
			$response = wp_remote_retrieve_body( $request );
			// Output the response directly as it's binary/image data
			echo $response; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			die();
		}
	}
}
