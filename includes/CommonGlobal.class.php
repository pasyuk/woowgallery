<?php

/**
 * Common class.
 *
 * @package WoowGallery
 * @author  Sergey Pasyuk
 */
class WoowGallery_CommonGlobal {

	/**
	 * Primary class constructor.
	 */
	public function __construct() {

	}


	/**
	 * Helper method for getting default config values.
	 *
	 * @param string $key The default config key to retrieve.
	 *
	 * @return string Key value on success, false on failure.
	 */
	public function get_config_default( $key ) {

		global $id, $post;

		// Get the current post ID. If ajax, grab it from the $_POST variable.
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$post_id = absint( woowgallery_POST( 'post_id' ) );
		} else {
			$post_id = isset( $post->ID ) ? $post->ID : (int) $id;
		}

		// Prepare default values.
		$defaults = $this->get_config_defaults( $post_id );

		// Return the key specified.
		return isset( $defaults[ $key ] ) ? $defaults[ $key ] : false;

	}

	/**
	 * Retrieves the gallery config defaults.
	 *
	 * @param int $post_id The current post ID.
	 *
	 * @return array Array of slider config defaults.
	 */
	public function get_config_defaults( $post_id ) {

		// Prepare default values.
		$defaults = array(
			// Misc.
			'default_template' => 'flipgrid',
			'slug'             => '',
			'classes'          => array(),
		);

		// Allow devs to filter the defaults.
		$defaults = apply_filters( 'woowgallery_defaults', $defaults, $post_id );

		return $defaults;

	}

	/**
	 * Helper method for retrieving image sizes.
	 *
	 * @param bool   $wordpress_only             WordPress Only (excludes the default and woowgallery_random options).
	 *
	 * @return  array                       Array of image size data.
	 * @global array $_wp_additional_image_sizes Array of registered image sizes.
	 */
	public function get_image_sizes_options( $wordpress_only = false ) {

		if ( ! $wordpress_only ) {
			$sizes = array(
				array(
					'value' => 'default',
					'name'  => __( 'Default', 'woowgallery' ),
				),
			);
		}

		global $_wp_additional_image_sizes;
		$wp_sizes = get_intermediate_image_sizes();
		foreach ( (array) $wp_sizes as $size ) {
			if ( isset( $_wp_additional_image_sizes[ $size ] ) ) {
				$width  = absint( $_wp_additional_image_sizes[ $size ]['width'] );
				$height = absint( $_wp_additional_image_sizes[ $size ]['height'] );
			} else {
				$width  = absint( get_option( $size . '_size_w' ) );
				$height = absint( get_option( $size . '_size_h' ) );
			}

			if ( ! $width && ! $height ) {
				$sizes[] = array(
					'value' => $size,
					'name'  => ucwords( str_replace( array( '-', '_' ), ' ', $size ) ),
				);
			} else {
				$sizes[] = array(
					'value'  => $size,
					'name'   => ucwords( str_replace( array( '-', '_' ), ' ', $size ) ) . ' (' . $width . ' &#215; ' . $height . ')',
					'width'  => $width,
					'height' => $height,
				);
			}
		}
		// Add Option for full image.
		$sizes[] = array(
			'value' => 'full',
			'name'  => __( 'Original Image', 'woowgallery' ),
		);

		// Add Random option.
		if ( ! $wordpress_only ) {
			$sizes[] = array(
				'value' => 'woowgallery_random',
				'name'  => __( 'Random', 'woowgallery' ),
			);
		}

		return apply_filters( 'woowgallery_image_sizes', $sizes );

	}

	/**
	 * Helper method to return the max execution time for scripts.
	 *
	 * @return int
	 * @var int $time The max execution time available for PHP scripts.
	 */
	public function get_max_execution_time() {

		$time = ini_get( 'max_execution_time' );

		return ! $time || empty( $time ) ? (int) 0 : $time;

	}

	/**
	 * Helper method to return the expiration time
	 *
	 * @param string $context
	 *
	 * @return int Expiration Time (in seconds)
	 */
	public function get_expiration_time( $context = 'woowgallery' ) {

		// Define the default.
		$default = DAY_IN_SECONDS;

		// Allow devs to filter this depending on the plugin.
		$default = apply_filters( 'woowgallery_get_expiration_time', $default, $context );

		return $default;

	}


	/**
	 * Get array of image sizes function.
	 *
	 * @return array
	 */
	public function get_image_sizes() {

		global $_wp_additional_image_sizes;

		$sizes = array();
		foreach ( get_intermediate_image_sizes() as $_size ) {

			if ( in_array( $_size, array( 'thumbnail', 'medium', 'medium_large', 'large' ), true ) ) {

				if ( true === (bool) get_option( "{$_size}_crop" ) ) {

					continue;

				}
				$sizes[ $_size ]['name']   = $_size;
				$sizes[ $_size ]['width']  = get_option( "{$_size}_size_w" );
				$sizes[ $_size ]['height'] = get_option( "{$_size}_size_h" );
				$sizes[ $_size ]['crop']   = (bool) get_option( "{$_size}_crop" );

			} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {

				if ( true === $_wp_additional_image_sizes[ $_size ]['crop'] ) {

					continue;

				}

				$sizes[ $_size ] = array(
					'name'   => $_size,
					'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
					'height' => $_wp_additional_image_sizes[ $_size ]['height'],
					'crop'   => $_wp_additional_image_sizes[ $_size ]['crop'],
				);
			}
		}

		return $sizes;
	}

	/**
	 * Check if url has image extention
	 *
	 * @param string $url
	 *
	 * @return bool
	 */
	public function is_image( $url ) {

		$p = strrpos( $url, '.' );

		if ( false === $p ) {

			return false;
		}

		$extension = strtolower( trim( substr( $url, $p ) ) );

		$img_extensions = array( '.gif', '.jpg', '.jpeg', '.png', '.tiff', '.tif' );

		if ( in_array( $extension, $img_extensions, true ) ) {

			return true;
		}

		return false;
	}



}
