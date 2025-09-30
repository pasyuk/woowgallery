<?php
/**
 * This class assumes that you have stored your Instagram client ID, client secret, and redirect URI as properties of the class, and that you have a database connection that you can use to store and retrieve the long-lived access token.
 * To use this class, you can first create an instance of it with your Instagram client credentials, like this:
 *
 * $instagram_controller = new InstagramAuthController($client_id, $client_secret, $redirect_uri);
 *
 * Then, when you receive an authorization code from Instagram (after the user has authorized your app), you can call the `authenticate()` method to exchange the code for a long-lived access token:
 *
 * $success = $instagram_controller->authenticate($code);
 * if (!$success) {
 *   // Handle authentication error.
 * }
 *
 * After this, you can call the get_access_token() method to retrieve the long-lived access token:
 *
 * $access_token = $instagram_controller->get_access_token();
 *
 * If you need to refresh the access token (because it has expired), you can call the needs_token_refresh() method to check if a refresh is necessary, and the refresh_token() method to perform the refresh:
 *
 * if ($instagram_controller->needs_token_refresh()) {
 *  $success = $instagram_controller->refresh_token();
 *  if (!$success) {
 *      // Handle token refresh error.
 *  }
 * }
 */

class InstagramAuthController {
	private $client_id;
	private $client_secret;
	private $redirect_uri;
	private $access_token;

	public function __construct( $client_id, $client_secret, $redirect_uri ) {
		$this->client_id     = $client_id;
		$this->client_secret = $client_secret;
		$this->redirect_uri  = $redirect_uri;
	}

	public function authenticate( $code ) {
		// Step 1: Exchange the authorization code for a short-lived access token.
		$url      = "https://api.instagram.com/oauth/access_token";
		$data     = array(
			'client_id'     => $this->client_id,
			'client_secret' => $this->client_secret,
			'grant_type'    => 'authorization_code',
			'redirect_uri'  => $this->redirect_uri,
			'code'          => $code
		);
		$response = $this->send_post_request( $url, $data );
		if ( ! $response || ! isset( $response->access_token ) ) {
			return false;
		}
		$short_lived_token = $response->access_token;

		// Step 2: Exchange the short-lived access token for a long-lived access token.
		$url      = "https://graph.instagram.com/access_token";
		$data     = array(
			'grant_type'    => 'ig_exchange_token',
			'client_secret' => $this->client_secret,
			'access_token'  => $short_lived_token
		);
		$response = $this->send_get_request( $url, $data );
		if ( ! $response || ! isset( $response->access_token ) ) {
			return false;
		}
		$long_lived_token = $response->access_token;

		// Step 3: Store the long-lived access token in the database.
		$this->store_token( $long_lived_token );

		$this->access_token = $long_lived_token;

		return true;
	}

	public function get_access_token() {
		if ( ! $this->access_token ) {
			$this->access_token = $this->retrieve_token();
		}

		return $this->access_token;
	}

	private function send_post_request( $url, $data ) {
		$options  = array(
			'http' => array(
				'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
				'method'  => 'POST',
				'content' => http_build_query( $data )
			)
		);
		$context  = stream_context_create( $options );
		$response = file_get_contents( $url, false, $context );

		return json_decode( $response );
	}

	private function send_get_request( $url, $data ) {
		$url      .= '?' . http_build_query( $data );
		$response = file_get_contents( $url );

		return json_decode( $response );
	}

	private function store_token( $token ) {
		// Store the token in your database.
		// Here's an example using WordPress functions:
		update_option( 'instagram_access_token', $token );
		update_option( 'instagram_token_updated', time() );
	}

	private function retrieve_token() {
		// Retrieve the token from your database.
		// Here's an example using WordPress functions:
		$token = get_option( 'instagram_access_token' );

		return $token;
	}

	public function needs_token_refresh() {
		// Check if the token needs to be refreshed.
		// Here's an example using WordPress functions:
		$last_updated = get_option( 'instagram_token_updated' );
		if ( ! $last_updated ) {
			return true;
		}
		$days_since_update = ( time() - $last_updated ) / ( 60 * 60 * 24 );
		if ( $days_since_update >= 60 ) {
			return true;
		}

		return false;
	}

	public function refresh_token() {
		// Refresh the access token.
		// Here's an example using WordPress functions:
		$token    = $this->get_access_token();
		$url      = "https://graph.instagram.com/refresh_access_token";
		$data     = array(
			'grant_type'   => 'ig_refresh_token',
			'access_token' => $token
		);
		$response = $this->send_get_request( $url, $data );
		if ( ! $response || ! isset( $response->access_token ) ) {
			return false;
		}
		$new_token = $response->access_token;
		$this->store_token( $new_token );
		$this->access_token = $new_token;

		return true;
	}
}