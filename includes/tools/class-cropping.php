<?php
/**
 * WoowGallery Cropping tool.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

namespace WoowGallery\Tools;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * Class Cropping
 */
class Cropping {

	/**
	 * API method for cropping images by id. Resize images dynamically.
	 *
	 * @param string       $attachment_id Media ID.
	 * @param string|array $size          Intermediate size or array [size,width,height,quality,crop,retina].
	 *
	 * @return array|bool
	 * @var int            [retina]       Returns cropped image url and dimentions for chosen retina coefficient.
	 *                                    If `1` also cretes and returns retina url @2x.
	 *                                    If `0` skip creating retina image.
	 */
	public static function resize_image( $attachment_id, $size = '' ) {

		if ( ! $attachment_id ) {
			return false;
		}

		// Array of valid crop locations.
		$crop_locations = [
			''   => false,
			'c'  => [ 'center', 'center' ],
			't'  => [ 'center', 'top' ],
			'b'  => [ 'center', 'bottom' ],
			'l'  => [ 'left', 'center' ],
			'r'  => [ 'right', 'center' ],
			'lt' => [ 'left', 'top' ],
			'lb' => [ 'left', 'bottom' ],
			'rt' => [ 'right', 'top' ],
			'rb' => [ 'right', 'bottom' ],
		];

		// Filter size args.
		$size = apply_filters( 'woowgallery_resize_image_args', $size, $attachment_id );

		// Size is an array.
		if ( is_array( $size ) ) {
			$intermediate_size = isset( $size['size'] ) ? $size['size'] : '';
			$width             = isset( $size['width'] ) ? (int) $size['width'] : 0;
			$height            = isset( $size['height'] ) ? (int) $size['height'] : 0;
			$quality           = isset( $size['quality'] ) ? (int) $size['quality'] : null;
			$retina            = isset( $size['retina'] ) ? (int) $size['retina'] : 0;
			$crop              = empty( $size['crop'] ) ? '' : $size['crop'];
			if ( $crop ) {
				$crop = is_bool( $crop ) || ! isset( $crop_locations[ $crop ] ) ? 'c' : $crop;
			}
		} else {
			$intermediate_size = $size;
			$width             = 0;
			$height            = 0;
			$quality           = null;
			$retina            = 0;
			$crop              = '';
		}

		// Get attachment data.
		$path = get_attached_file( $attachment_id );
		if ( empty( $path ) ) {
			return false;
		}

		$src = wp_get_attachment_image_src( $attachment_id, 'full' );
		if ( empty( $size ) ) {

			return [
				'url'    => $src[0],
				'width'  => $src[1],
				'height' => $src[2],
			];
		}

		// Calculate output dimensions after resize.
		$cropped_dims = image_resize_dimensions( $src[1], $src[2], $width, $height, $crop_locations[ $crop ] );

		// Target image size dims.
		$dest_w = isset( $cropped_dims[4] ) ? $cropped_dims[4] : 0;
		$dest_h = isset( $cropped_dims[5] ) ? $cropped_dims[5] : 0;

		// If current image size is smaller or equal to target size return full image.
		if (
			empty( $cropped_dims )
			|| $dest_w > $src[1]
			|| $dest_h > $src[2]
			|| ( $dest_w === $src[1] && $dest_h === $src[2] )
		) {
			// don't return original for retina images.
			if ( 1 < $retina ) {
				return false;
			}

			return [
				'url'    => $src[0],
				'width'  => $src[1],
				'height' => $src[2],
			];
		}

		// Retina can't be cropped to exactly @nx.
		if ( $crop && ( 1 < $retina ) && ( $dest_w !== $width || $dest_h !== $height ) ) {
			return false;
		}

		// Define crop suffix if custom crop is set and valid.
		$crop_suffix = $crop ? '_' . $crop : '';

		// Define suffix.
		if ( 1 < $retina ) {
			$suffix    = $dest_w / $retina . 'x' . $dest_h / $retina . $crop_suffix . '@2x';
			$im_suffix = $width / $retina . 'x' . $height / $retina . $crop_suffix . '@2x';
		} else {
			$suffix    = $dest_w . 'x' . $dest_h . $crop_suffix;
			$im_suffix = $width . 'x' . $height . $crop_suffix;
		}

		// Define custom intermediate_size based on suffix.
		$intermediate_size = $intermediate_size ? $intermediate_size : 'wg' . $im_suffix;

		// Get attachment details.
		$info        = pathinfo( $path );
		$extension   = '.' . $info['extension'];
		$path_no_ext = $info['dirname'] . '/' . $info['filename'];

		// Get cropped path.
		$cropped_path     = $path_no_ext . '-' . $suffix . $extension;
		$is_image_cropped = false;
		// Return changed image
		// and try to generate retina if not created already.
		if ( file_exists( $cropped_path ) ) {
			$is_image_cropped = true;
		} else {
			// Crop image.
			$editor = wp_get_image_editor( $path );
			if ( ! is_wp_error( $editor ) && ! is_wp_error( $editor->resize( $width, $height, $crop_locations[ $crop ] ) ) ) {
				// Set the image editor quality.
				$editor->set_quality( $quality );

				// Get resized file.
				$cropped_path = $editor->generate_filename( $suffix );
				$editor       = $editor->save( $cropped_path );
				if ( ! is_wp_error( $editor ) ) {
					$is_image_cropped = true;
				}
			}
		}

		// Set new image url from resized image.
		if ( $is_image_cropped ) {
			// Cropped image.
			$cropped_img = str_replace( basename( $src[0] ), basename( $cropped_path ), $src[0] );

			// Generate retina version.
			if ( 1 === $retina ) {
				$retina      = 2;
				$retina_dims = [
					'width'  => $dest_w * $retina,
					'height' => $dest_h * $retina,
					'retina' => $retina,
					'crop'   => $crop,
				];
				$retina_src  = self::resize_image( $attachment_id, $retina_dims );
			}

			// Get thumbnail meta.
			$meta = wp_get_attachment_metadata( $attachment_id );
			if ( is_array( $meta ) ) {
				$meta['sizes'] = isset( $meta['sizes'] ) ? $meta['sizes'] : [];
				if (
					! array_key_exists( $intermediate_size, $meta['sizes'] )
					|| ( $dest_w !== $meta['sizes'][ $intermediate_size ]['width'] || $dest_h !== $meta['sizes'][ $intermediate_size ]['height'] )
				) {
					// Check correct mime type.
					$mime_type = wp_check_filetype( $cropped_img );
					$mime_type = isset( $mime_type['type'] ) ? $mime_type['type'] : '';

					// Cropped image file name.
					$dest_filename = $info['filename'] . '-' . $suffix . $extension;

					// Add cropped image to image meta.
					$meta['sizes'][ $intermediate_size ] = [
						'file'      => $dest_filename,
						'width'     => $dest_w,
						'height'    => $dest_h,
						'mime-type' => $mime_type,
					];

					// Update meta.
					wp_update_attachment_metadata( $attachment_id, $meta );
				}
			}

			// Return cropped image.
			return [
				'url'               => $cropped_img,
				'width'             => $dest_w,
				'height'            => $dest_h,
				'retina'            => ! empty( $retina_src['url'] ) ? $retina_src['url'] : '',
				'intermediate_size' => $intermediate_size,
			];
		}

		// Couldn't dynamically create image so return original.
		return [
			'url'    => $src[0],
			'width'  => $src[1],
			'height' => $src[2],
		];
	}

	/**
	 * API method for cropping images by url.
	 *
	 * @param string       $url             The URL of the image to resize.
	 * @param string|array $size            Array [size,width,height,quality,crop,retina].
	 * @param bool         $force_overwrite Forces an overwrite even if the thumbnail already exists (useful for applying watermarks).
	 *
	 * @return array|bool
	 * @var int            [retina]       Returns cropped image url and dimentions for chosen retina coefficient.
	 *                                    If `1` also cretes and returns retina url @2x.
	 *                                    If `0` skip creating retina image.
	 */
	public static function resize_image_url( $url, $size = '', $force_overwrite = false ) {

		if ( ! $url ) {
			return false;
		}

		// Array of valid crop locations.
		$crop_locations = [
			''   => false,
			'c'  => [ 'center', 'center' ],
			't'  => [ 'center', 'top' ],
			'b'  => [ 'center', 'bottom' ],
			'l'  => [ 'left', 'center' ],
			'r'  => [ 'right', 'center' ],
			'lt' => [ 'left', 'top' ],
			'lb' => [ 'left', 'bottom' ],
			'rt' => [ 'right', 'top' ],
			'rb' => [ 'right', 'bottom' ],
		];

		$url = set_url_scheme( $url );
		// Filter size args.
		$size = apply_filters( 'woowgallery_resize_image_url_args', $size, $url );

		// Size is an array.
		if ( is_array( $size ) ) {
			$width   = isset( $size['width'] ) ? (int) $size['width'] : 0;
			$height  = isset( $size['height'] ) ? (int) $size['height'] : 0;
			$quality = isset( $size['quality'] ) ? (int) $size['quality'] : null;
			$crop    = empty( $size['crop'] ) ? '' : $size['crop'];
			if ( $crop ) {
				$crop = is_bool( $crop ) || ! isset( $crop_locations[ $crop ] ) ? 'c' : $crop;
			}
		} else {
			$width   = 0;
			$height  = 0;
			$quality = null;
			$crop    = '';
		}

		// Don't resize images that don't belong to this site's URL.
		// Strip ?lang=de from blog's URL - WPML adds this on.
		// and means our next statement fails.
		if ( is_multisite() ) {
			$blog_id = get_current_blog_id();
			// doesn't use network_site_url because this will be incorrect for remapped domains.
			if ( is_main_site( $blog_id ) ) {
				$site_url = preg_replace( '/\?.*/', '', network_site_url() );
			} else {
				$site_url = preg_replace( '/\?.*/', '', site_url() );
			}
		} else {
			$site_url = preg_replace( '/\?.*/', '', get_bloginfo( 'url' ) );
		}

		// WPML check - if there is a /fr or any domain in the url, then remove that from the $site_url.
		if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
			if ( strpos( $site_url, '/' . ICL_LANGUAGE_CODE ) !== false ) {
				$site_url = str_replace( '/' . ICL_LANGUAGE_CODE, '', $site_url );
			}
		}

		if ( function_exists( 'qtrans_getLanguage' ) ) {
			$lang = qtrans_getLanguage();
			if ( ! empty( $lang ) ) {
				if ( strpos( $site_url, '/' . $lang ) !== false ) {
					$site_url = str_replace( '/' . $lang, '', $site_url );
				}
			}
		}

		$site_url = set_url_scheme( $site_url );
		if ( strpos( $url, $site_url ) !== 0 ) {
			// Get original image size.
			$url_size = @getimagesize( $url );
			// If no size data obtained, return an error.
			if ( ! is_array( $url_size ) ) {
				return [ 'url' => $url ];
			}

			return [
				'url'    => $url,
				'width'  => $url_size[0],
				'height' => $url_size[1],
			];
		}

		$url_rel_path   = str_replace( $site_url, '', $url );
		$site_home_path = get_home_path();
		$path           = $site_home_path . $url_rel_path;

		// Attempt to stream and import the image if it does not exist based on URL provided.
		if ( ! file_exists( $path ) ) {
			return false;
		}

		// Get original image size.
		$file_size = getimagesize( $path );
		// If no size data obtained, return an error.
		if ( ! is_array( $file_size ) ) {
			return [ 'url' => $url ];
		}

		// Calculate output dimensions after resize.
		$cropped_dims = image_resize_dimensions( $file_size[0], $file_size[1], $width, $height, $crop_locations[ $crop ] );

		// Target image size dims.
		$dest_w = isset( $cropped_dims[4] ) ? $cropped_dims[4] : 0;
		$dest_h = isset( $cropped_dims[5] ) ? $cropped_dims[5] : 0;

		// If current image size is smaller or equal to target size return full image.
		if (
			empty( $cropped_dims )
			|| $dest_w > $file_size[0]
			|| $dest_h > $file_size[1]
			|| ( $dest_w === $file_size[0] && $dest_h === $file_size[1] )
		) {

			return [
				'url'    => $url,
				'width'  => $file_size[0],
				'height' => $file_size[1],
			];
		}

		// Define crop suffix if custom crop is set and valid.
		$crop_suffix = $crop ? '_' . $crop : '';
		$suffix      = $dest_w . 'x' . $dest_h . $crop_suffix;

		// Get attachment details.
		$info        = pathinfo( $path );
		$extension   = '.' . $info['extension'];
		$path_no_ext = $info['dirname'] . '/' . $info['filename'];

		// Get cropped path.
		$cropped_path     = $path_no_ext . '-' . $suffix . $extension;
		$is_image_cropped = false;
		// Return changed image.
		if ( file_exists( $cropped_path ) && ! $force_overwrite ) {
			$is_image_cropped = true;
		} else {
			// Crop image.
			$editor = wp_get_image_editor( $path );
			if ( ! is_wp_error( $editor ) && ! is_wp_error( $editor->resize( $width, $height, $crop_locations[ $crop ] ) ) ) {
				// Set the image editor quality.
				$editor->set_quality( $quality );

				// Get resized file.
				$cropped_path = $editor->generate_filename( $suffix );
				$editor       = $editor->save( $cropped_path );
				if ( ! is_wp_error( $editor ) ) {
					$is_image_cropped = true;
				}
			}
		}

		// Set new image url from resized image.
		if ( $is_image_cropped ) {
			// Cropped image.
			$cropped_img = str_replace( basename( $path ), basename( $cropped_path ), $path );

			// Return cropped image.
			return [
				'url'    => $cropped_img,
				'width'  => $dest_w,
				'height' => $dest_h,
			];
		}

		return [
			'url'    => $url,
			'width'  => $file_size[0],
			'height' => $file_size[1],
		];
	}
}
