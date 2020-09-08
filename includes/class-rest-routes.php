<?php
/**
 * WoowGallery Rest Routes Class.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

namespace WoowGallery;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

use WoowGallery\Tools\Cropping;
use WP_HTTP_Response;
use WP_REST_Request;
use WP_REST_Server;

/**
 * Class Rest_Routes
 */
class Rest_Routes {

	/**
	 * API Namespace
	 *
	 * (default value: 'woowgallery-route')
	 *
	 * @var string
	 */
	public $domain = 'woowgallery-route';

	/**
	 * API Version
	 *
	 * (default value: 'v1')
	 *
	 * @var string
	 */
	public $version = 'v1';

	/**
	 * Holds API Request
	 *
	 * (default value: null)
	 *
	 * @var mixed
	 */
	public $request = null;

	/**
	 * Class Constructor.
	 */
	public function __construct() {

		// Actions.
		add_action( 'rest_api_init', [ $this, 'custom_rest_fields' ] );
		add_action( 'rest_api_init', [ $this, 'register_api_routes' ] );

		// Filters.
		//add_filter( 'rest_pre_serve_request', [ $this, 'multiformat_rest_pre_serve_request' ], 10, 4 );

	}

	/**
	 * Register Custom Rest fields.
	 *
	 * @param WP_REST_Server $wp_rest_server Server object.
	 */
	public function custom_rest_fields( $wp_rest_server ) {
		$_fields = woowgallery_GET( '_fields', '' );
		$_fields = is_array( $_fields ) ? implode( ',', $_fields ) : $_fields;
		if ( empty( $_fields ) ) {
			return;
		}

		$_post_type = woowgallery_GET( '_post_type', '' );
		if ( empty( $_post_type ) || ! post_type_exists( $_post_type ) ) {
			return;
		}

		if ( strpos( $_fields, 'wg_data' ) !== false ) {
			$this->register_rest_field_data( $_post_type );
		}
		if ( strpos( $_fields, 'wg_content' ) !== false ) {
			$this->register_rest_field_content( $_post_type );
		}
		if ( strpos( $_fields, 'wg_shortcode' ) !== false ) {
			$this->register_rest_field_shortcode( $_post_type );
		}
	}

	/**
	 * Register Custom Rest Field for gallery.
	 *
	 * @param string $post_type Post Type.
	 */
	public function register_rest_field_data( $post_type ) {
		register_rest_field(
			$post_type,
			'wg_data',
			[
				'get_callback'    => function ( $post_arr, $attr, WP_REST_Request $request, $object_type ) {
					$wgpost = get_post( $post_arr['id'] );

					$attachment_data      = woowgallery_prepare_post_data( $wgpost );
					$attachment_full_data = woowgallery_full_post_data( $attachment_data );
					if ( ! empty( $attachment_full_data ) ) {
						$attachment_full_data['excerpt'] = $wgpost->post_excerpt;
						if ( current_user_can( 'edit_post', $wgpost->ID ) ) {
							$attachment_full_data['edit_link'] = get_edit_post_link( $wgpost->ID, 'raw' );
						}

						$wg        = Gallery::get_instance( $wgpost->ID, $wgpost->post_type );
						$skin_slug = $wg->get_skin_slug();
						$skin      = Skins::get_instance()->get_skin( $skin_slug );

						$attachment_full_data['skin'] = [
							'slug' => $skin_slug,
							'info' => $skin->info,
						];

						$lightbox_list = Assets::lightboxes();
						$lightbox_slug = $wg->get_lightbox_slug();
						if ( ! empty( $lightbox_slug ) ) {
							$attachment_full_data['lightbox'] = $lightbox_list[ $lightbox_slug ];
							if ( $lightbox_list[ $lightbox_slug ]['script'] ) {
								$attachment_full_data['skin']['info']['scripts'] = array_merge( $attachment_full_data['skin']['info']['scripts'], [ $lightbox_list[ $lightbox_slug ]['script'] ] );
							}
							if ( $lightbox_list[ $lightbox_slug ]['style'] ) {
								$attachment_full_data['skin']['info']['styles'] = array_merge( $attachment_full_data['skin']['info']['styles'], [ $lightbox_list[ $lightbox_slug ]['style'] ] );
							}
						}
					}

					return apply_filters( 'rest_woowgallery_full_post_data', $attachment_full_data );
				},
				'update_callback' => null,
				'schema'          => [
					'description' => __( 'WoowGallery Gallery data.' ),
					'type'        => 'array',
				],
			]
		);
	}

	/**
	 * Register Custom Rest Field for gallery.
	 *
	 * @param string $post_type Post Type.
	 */
	public function register_rest_field_content( $post_type ) {
		register_rest_field(
			$post_type,
			'wg_content',
			[
				'get_callback'    => function ( $post_arr, $attr, WP_REST_Request $request, $object_type ) {
					$wgpost       = get_post( $post_arr['id'] );
					$wg           = Gallery::get_instance( $wgpost->ID, $wgpost->post_type );
					$gallery_data = $wg->get_gallery_content();

					return [
						'post' => [
							'ID'             => $wgpost->ID,
							'post_type'      => $wgpost->post_type,
							'post_title'     => $wgpost->post_title,
							'post_name'      => $wgpost->post_name,
							'post_status'    => $wgpost->post_status,
							'post_content'   => $wgpost->post_content,
							'post_date'      => $wgpost->post_date,
							'post_modified'  => $wgpost->post_modified,
							'comment_count'  => $wgpost->comment_count,
							'comment_status' => $wgpost->comment_status,
							'has_password'   => ! empty( $wgpost->post_password ),
						],
						'data' => apply_filters( 'rest_woowgallery_full_post_content', $gallery_data ),
					];
				},
				'update_callback' => null,
				'schema'          => [
					'description' => __( 'WoowGallery Gallery content.' ),
					'type'        => 'array',
				],
			]
		);
	}

	/**
	 * Register Custom Rest Field for gallery.
	 *
	 * @param string $post_type Post Type.
	 */
	public function register_rest_field_shortcode( $post_type ) {
		register_rest_field(
			$post_type,
			'wg_shortcode',
			[
				'get_callback'    => function ( $post_arr, $attr, WP_REST_Request $request, $object_type ) {
					$wgpost = get_post( $post_arr['id'] );

					return woowgallery( $wgpost->ID, $wgpost->post_type, [], true );
				},
				'update_callback' => null,
				'schema'          => [
					'description' => __( 'WoowGallery Gallery shortcode HTML.' ),
					'type'        => 'array',
				],
			]
		);
	}

	/**
	 * Register API Routes.
	 *
	 * @param WP_REST_Server $wp_rest_server Server object.
	 */
	public function register_api_routes( $wp_rest_server ) {

		$namespace = $this->domain . '/' . $this->version;

		register_rest_route(
			$namespace,
			'/crop-images',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'crop_images' ],
				'permission_callback' => '__return_true',
			]
		);

		register_rest_route(
			$namespace,
			'/resize-image',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'resize' ],
				'permission_callback' => '__return_true',
			]
		);

		do_action( 'woowgallery_routes', $this->domain, $this->version );

	}

	/**
	 * Crop images via background request.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return void
	 */
	public function crop_images( WP_REST_Request $request ) {

		// Validate the request.
		$valid = $this->validate_request( $request );

		// Return if request not valid.
		if ( ! $valid ) {

			return;
		}

		// Set the request.
		$this->request = $request;

		// Get the body.
		$body = $request->get_body_params();

		$data         = $body['data'];
		$gallery_id   = (int) $data['id'];
		$gallery_post = get_post( $gallery_id );
		$wg           = Gallery::get_instance( $gallery_post->ID, $gallery_post->post_type );
		$gallery_data = $wg->get_gallery_content();
		$attachments  = array_filter(
			$gallery_data,
			function ( $item ) {
				return ( ! empty( $item['image_id'] ) && empty( $item['resized'] ) );
			}
		);
		if ( ! $attachments ) {
			return;
		}

		$dims = woowgallery_get_resize_dimensions( $gallery_post );
		// Loop through the images and crop them.
		foreach ( $attachments as $att ) {
			$crop_data = [
				'args'       => $dims['thumb'],
				'gallery_id' => $gallery_id,
				'image_id'   => $att['id'],
			];
			// Generate the cropped image.
			$this->background_request( $crop_data, 'resize-image' );

			$crop_data = [
				'args'       => $dims['image'],
				'gallery_id' => $gallery_id,
				'image_id'   => $att['id'],
			];
			// Generate the cropped image.
			$this->background_request( $crop_data, 'resize-image' );
		}
	}

	/**
	 * Validates the API request.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return bool
	 */
	public function validate_request( $request ) {

		$body = $request->get_body_params();
		if ( ! is_array( $body ) ) {

			return false;
		}

		// Verify the request is comming from the site.
		$site_url = site_url();

		if ( strpos( $body['site'], $site_url ) === false ) {

			return false;
		}

		$token = get_option( 'woowgallery_rest_token' );

		if ( $token !== $request->get_header( 'X-WoowGallery-Token' ) ) {

			return false;
		}

		do_action( 'woowgallery_validate_route_request' );

		// All checks passed.
		return true;
	}

	/**
	 * Helper function to call background requests.
	 *
	 * @param array  $data Request Data.
	 * @param string $type Request Type.
	 */
	public function background_request( $data, $type ) {

		// Bail if nothing set.
		if ( ! is_array( $data ) || ! isset( $type ) ) {
			return;
		}

		$namespace = $this->domain . '/' . $this->version;

		$rest_url = get_rest_url();
		$nonce    = wp_create_nonce( 'wp_rest' );
		$token    = get_option( 'woowgallery_rest_token' );

		if ( ! $token ) {

			$token = $this->generate_token();
		}

		$defaults = [
			'data'  => $data,
			'site'  => get_home_url(),
			'nonce' => $nonce,
		];

		$body = wp_parse_args( $data, $defaults );

		$headers = [
			'X-WoowGallery-Token' => $token,
		];

		// Generate the background request url.
		$url = trailingslashit( $rest_url ) . $namespace . '/' . $type;

		$args = [
			'headers'    => $headers,
			'body'       => $body,
			'user-agent' => 'WoowGallery/' . WOOWGALLERY_VERSION,
			'timeout'    => 0.5,
			'blocking'   => false,
			'sslverify'  => apply_filters( 'woowgallery_background_ssl_verify', false ),
		];

		wp_remote_post( $url, $args );

	}

	/**
	 * Self generated token to validate background requests.
	 *
	 * @return string
	 */
	public function generate_token() {

		$rest_token = wp_generate_password( 45, false, false );

		$hash = hash( 'sha256', $rest_token );

		update_option( 'woowgallery_rest_token', $hash );

		return $hash;
	}

	/**
	 * Helper Request to resize images in the background.
	 *
	 * @param WP_REST_Request $request Request.
	 */
	public function resize( WP_REST_Request $request ) {

		// Validate the request.
		$valid = $this->validate_request( $request );

		// Return if request not valid.
		if ( ! $valid ) {

			wp_send_json_error();
		}

		// Set the request.
		$this->request = $request;

		// Get the body.
		$body = $request->get_body_params();

		$cropped_image = Cropping::resize_image( (int) $body['data']['image_id'], $body['data']['args'] );

		if ( $cropped_image ) {
			update_post_meta( (int) $body['data']['gallery_id'], Gallery::GALLERY_UPDATE_META_KEY, 1 );
			wp_send_json_success( $cropped_image );
		} else {
			wp_send_json_error( $cropped_image );
		}
	}

	/**
	 * Non-JSON results from the WP-API
	 *
	 * @param bool             $served  Whether the request has already been served. Default false.
	 * @param WP_HTTP_Response $result  Result to send to the client. Usually a WP_REST_Response.
	 * @param WP_REST_Request  $request Request used to generate the response.
	 * @param WP_REST_Server   $server  Server instance.
	 *
	 * @return bool
	 */
	public function multiformat_rest_pre_serve_request( $served, $result, $request, $server ) {
		// assumes 'format' was passed into the intial API route
		// example: https://example.com/wp-json/lorem/ipsum?format=text
		// the default JSON response will be handled automatically by WP-API.
		switch ( $request['format'] ) {
			case 'text':
				header( 'Content-Type: text/plain; charset=' . get_option( 'blog_charset' ) );

				echo esc_html( $result->data->my_text_data );

				$served = true; // tells the WP-API that we sent the response already.
				break;
		}

		return $served;
	}

}
