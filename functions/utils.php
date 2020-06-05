<?php
/**
 * Utilites.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

if ( ! function_exists( 'woowgallery_is_mobile' ) ) {

	/**
	 * Helper Method to check if is mobile.
	 *
	 * @return bool
	 */
	function woowgallery_is_mobile() {

		if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {

			return preg_match( '/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i', sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) );

		}

		return false;
	}
}

if ( ! function_exists( 'woowgallery_verify_nonce' ) ) {

	/**
	 * Verify that correct nonce was used with time limit.
	 *
	 * The user is given an amount of time to use the token, so therefore, since the
	 * UID and $action remain the same, the independent variable is the time.
	 *
	 * @param string $action          Action nonce.
	 * @param bool   $die             Optional. Whether to die early when the nonce cannot be verified.
	 *                                Default true.
	 *
	 * @return false|int False if the nonce is invalid, 1 if the nonce is valid and generated between
	 *                   0-12 hours ago, 2 if the nonce is valid and generated between 12-24 hours ago.
	 */
	function woowgallery_verify_nonce( $action, $die = true ) {
		// Key to check for the nonce in `$_REQUEST`.
		$key   = "_nonce_woowgallery_{$action}";
		$nonce = woowgallery_REQUEST( $key );

		if ( ! $nonce ) {
			if ( $die ) {
				if ( wp_doing_ajax() ) {
					wp_die( - 1, 403 );
				} else {
					die( '-1' );
				}
			}

			return false;
		}

		$result = wp_verify_nonce( $nonce, $action );

		/**
		 * Fires once the request has been validated or not.
		 *
		 * @param string    $action The nonce action.
		 * @param false|int $result False if the nonce is invalid, 1 if the nonce is valid and generated between
		 *                          0-12 hours ago, 2 if the nonce is valid and generated between 12-24 hours ago.
		 */
		do_action( 'woowgallery_verify_nonce', $action, $result );

		if ( $die && false === $result ) {
			if ( wp_doing_ajax() ) {
				wp_die( - 1, 403 );
			} else {
				die( '-1' );
			}
		}

		return $result;
	}
}

if ( ! function_exists( 'woowgallery_GET' ) ) {

	/**
	 * Check GET data
	 *
	 * @param string $key
	 * @param mixed  $default
	 * @param bool   $empty_is_false
	 *
	 * @return mixed
	 */
	function woowgallery_GET( $key, $default = false, $empty_is_false = false ) {
		return isset( $_GET[ $key ] ) ? ( ( $empty_is_false && woowgallery_empty( $_GET[ $key ] ) ) ? false : stripslashes_from_strings_only( $_GET[ $key ] ) ) : $default;
	}
}

if ( ! function_exists( 'woowgallery_empty' ) ) {

	/**
	 * Check if variable has empty value
	 *
	 * @param string $var
	 *
	 * @return bool
	 */
	function woowgallery_empty( $var ) {
		return ! ( ! empty( $var ) && ! in_array( strtolower( $var ), [ 'null', 'false' ], true ) );
	}
}

if ( ! function_exists( 'woowgallery_POST' ) ) {

	/**
	 * Check POST data
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	function woowgallery_POST( $key, $default = false ) {
		return isset( $_POST[ $key ] ) ? stripslashes_from_strings_only( $_POST[ $key ] ) : $default;
	}
}

if ( ! function_exists( 'woowgallery_REQUEST' ) ) {

	/**
	 * Check REQUEST data
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	function woowgallery_REQUEST( $key, $default = false ) {
		if ( isset( $_POST[ $key ] ) ) {
			return woowgallery_POST( $key, $default );
		}

		return woowgallery_GET( $key, $default );
	}
}

if ( ! function_exists( 'woowgallery_minify' ) ) {
	/**
	 * Helper method to minify a string of data.
	 *
	 * @param string $string                      String of data to minify.
	 * @param bool   $strip_double_forwardslashes Strip double forward slashes.
	 *
	 * @return string $string Minified string of data.
	 */
	function woowgallery_minify( $string, $strip_double_forwardslashes = true ) {

		// Added a switch for stripping double forwardslashes
		// This can be disabled when using URLs in JS, to ensure http:// doesn't get removed
		// All other comment removal and minification will take place.
		$strip_double_forwardslashes = apply_filters( 'woowgallery_minify_strip_double_forward_slashes', $strip_double_forwardslashes );

		if ( $strip_double_forwardslashes ) {
			$clean = preg_replace( '/((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/', '', $string );
		} else {
			// Use less aggressive method.
			$clean = preg_replace( '!/\*.*?\*/!s', '', $string );
			$clean = preg_replace( '/\n\s*\n/', "\n", $clean );
		}

		$clean = str_replace( [ "\r\n", "\r", "\t", "\n", '  ', '	  ', '	   ' ], '', $clean );

		return apply_filters( 'woowgallery_minified_string', $clean, $string );

	}
}

if ( ! function_exists( 'woowgallery_array_diff_key_recursive' ) ) {
	/**
	 * Helper method to return difference between two multidimentional arrays
	 *
	 * @param array $arr1
	 * @param array $arr2
	 *
	 * @return array
	 *
	 * @author  Gajus Kuizinas <g.kuizinas@anuary.com>
	 */
	function woowgallery_array_diff_key_recursive( array $arr1, array $arr2 ) {
		$diff      = array_diff_key( $arr1, $arr2 );
		$intersect = array_intersect_key( $arr1, $arr2 );

		foreach ( $intersect as $k => $v ) {
			if ( is_array( $arr1[ $k ] ) && is_array( $arr2[ $k ] ) ) {
				$d = woowgallery_array_diff_key_recursive( $arr1[ $k ], $arr2[ $k ] );

				if ( ! empty( $d ) ) {
					$diff[ $k ] = $d;
				}
			}
		}

		return $diff;
	}
}

if ( ! function_exists( 'woowgallery_get_object_taxonomy_terms' ) ) {
	/**
	 * Get array of taxonomy terms of current object.
	 *
	 * @param WP_Post $post The current post object.
	 *
	 * @return array
	 */
	function woowgallery_get_object_taxonomy_terms( $post ) {
		$_taxonomies   = get_object_taxonomies( $post, 'objects' );
		$wg_taxonomies = [];

		foreach ( $_taxonomies as $_tax ) {
			if ( empty( $_tax->public ) ) {
				continue;
			}

			$wg_taxonomies[ $_tax->name ] = [
				'taxonomy' => $_tax->label,
				'terms'    => wp_get_object_terms(
					$post->ID,
					$_tax->name,
					[
						'orderby' => 'name',
						'order'   => 'ASC',
						'fields'  => 'names',
					]
				),
			];
		}

		return $wg_taxonomies;
	}
}

if ( ! function_exists( 'woowgallery_get_post_types' ) ) {
	/**
	 * Get array of supported Post Type(s).
	 *
	 * @param array $args Arguments.
	 *
	 * @return array
	 */
	function woowgallery_get_post_types( $args = [] ) {
		$args       = array_merge( $args, [ 'public' => true ] );
		$post_types = get_post_types(
			$args,
			'objects',
			'and'
		);

		if ( ! woow_fs()->can_use_premium_code() ) {
			$post_types = array_filter(
				$post_types,
				function ( $pt ) {
					return ! in_array( $pt->name, [ 'product', 'download' ], true );
				}
			);
		}

		return apply_filters( 'woowgallery_supported_posttypes', $post_types );
	}
}

if ( ! function_exists( 'woowgallery_get_taxonomy_terms' ) ) {
	/**
	 * Get array of taxonomy terms by Post Type(s).
	 *
	 * @param array|string|WP_Post $post_type The current post object.
	 * @param bool                 $shared    Shared taxonomy.
	 *
	 * @return array
	 */
	function woowgallery_get_taxonomy_terms( $post_type, $shared = false ) {
		if ( $shared || empty( $post_type ) ) {
			$taxonomies = woowgallery_get_shared_object_taxonomies( $post_type, 'objects' );
		} else {
			$taxonomies = get_object_taxonomies( $post_type, 'objects' );
		}

		if ( ! woow_fs()->can_use_premium_code() ) {
			$taxonomies = array_filter(
				$taxonomies,
				function ( $tax ) {
					return ! in_array( $tax->name, [ 'product_cat', 'product_tag', 'download_category', 'download_tag' ], true );
				}
			);
		}

		$taxonomies    = apply_filters( 'woowgallery_supported_taxonomies', $taxonomies );
		$wg_taxonomies = [];

		foreach ( $taxonomies as $tax ) {
			if ( empty( $tax->public ) || empty( $tax->show_ui ) ) {
				continue;
			}

			$terms       = get_terms( $tax->name );
			$wg_taxonomy = [
				'taxonomy' => $tax->label,
				'terms'    => [],
			];
			foreach ( $terms as $tt ) {
				$wg_taxonomy['terms'][] = [
					'id'       => $tt->term_id,
					'slug'     => $tt->slug,
					'name'     => $tt->name,
					'taxname'  => $tax->name,
					'taxlabel' => $tax->labels->singular_name,
				];
			}
			$wg_taxonomies[] = $wg_taxonomy;
		}

		return $wg_taxonomies;
	}
}

if ( ! function_exists( 'woowgallery_get_shared_object_taxonomies' ) ) {
	/**
	 * Return the names or objects of the shared taxonomies which are registered for the requested post types.
	 *
	 * @param array|string|WP_Post $object        Name of the type of taxonomy object, or an object (row from posts).
	 * @param string               $output        Optional. The type of output to return in the array. Accepts either
	 *                                            taxonomy 'names' or 'objects'. Default 'names'.
	 *
	 * @return array The names of all taxonomy of $object_type.
	 */
	function woowgallery_get_shared_object_taxonomies( $object, $output = 'names' ) {
		global $wp_taxonomies;

		if ( empty( $object ) ) {
			return get_taxonomies( [ 'public' => true ], $output );
		}

		if ( is_object( $object ) ) {
			if ( 'attachment' === $object->post_type ) {
				return get_attachment_taxonomies( $object, $output );
			}
			$object = $object->post_type;
		}

		$object = (array) $object;

		$taxonomies = [];
		foreach ( (array) $wp_taxonomies as $tax_name => $tax_obj ) {
			if ( ! array_diff( $object, (array) $tax_obj->object_type ) ) {
				if ( 'names' === $output ) {
					$taxonomies[] = $tax_name;
				} else {
					$taxonomies[ $tax_name ] = $tax_obj;
				}
			}
		}

		return $taxonomies;
	}
}

if ( ! function_exists( 'woowgallery_convert_encoding' ) ) {
	/**
	 * Convert a string to UTF-8, so that it can be safely encoded to JSON.
	 *
	 * @param string $string The string which is to be converted.
	 *
	 * @return string The checked string.
	 */
	function woowgallery_convert_encoding( $string ) {
		static $use_mb = null;
		if ( is_null( $use_mb ) ) {
			$use_mb = function_exists( 'mb_convert_encoding' );
		}

		if ( $use_mb ) {
			$content = mb_convert_encoding( $string, 'UTF-8', 'auto' );
		} else {
			$content = wp_check_invalid_utf8( $string, true );
		}

		return wp_encode_emoji( $content );
	}
}

if ( ! function_exists( 'woowgallery_is_premium_feature' ) ) {
	/**
	 * Print `Get Premium` message.
	 *
	 * @param string $msg The content of message.
	 */
	function woowgallery_is_premium_feature( $msg = '' ) {
		if ( woow_fs()->can_use_premium_code() ) {
			return;
		}
		?>
		<div class="woowgallery-pro-feature wg-transparent">
			<?php if ( $msg ) { ?>
				<h6><?php echo wp_kses( $msg, '' ); ?></h6>
			<?php } ?>
			<a class="button button-primary" href="<?php echo esc_url( woow_fs()->get_upgrade_url() ); ?>" target="_blank"><span class="dashicons dashicons-cart"></span> <?php esc_html_e( 'Get WoowGallery Premium', 'woowgallery' ); ?></a>
		</div>
		<?php
	}
}
