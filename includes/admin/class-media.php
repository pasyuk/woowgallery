<?php
/**
 * Media class.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

namespace WoowGallery\Admin;

use WoowGallery\Taxonomies;
use WP_Post;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * Class Media
 */
class Media {

	/**
	 * Primary class constructor.
	 */
	public function __construct() {

		add_filter( 'wp_prepare_attachment_for_js', [ $this, 'wpmedia_add_woowgallery_data' ], 10, 3 );
		add_filter( 'wp_handle_upload', [ $this, 'fix_image_orientation' ] );
		add_filter( 'wp_generate_attachment_metadata', [ $this, 'add_keywords_as_media_tags' ], 10, 3 );

		add_filter( 'attachment_fields_to_edit', [ $this, 'attachment_fields_to_edit' ], - 1, 2 );
		add_filter( 'attachment_fields_to_save', [ $this, 'attachment_fields_to_save' ], 10, 2 );

	}

	/**
	 * Add woowgallery data to attachment data
	 *
	 * @param array      $response   Array of prepared attachment data.
	 * @param int|object $attachment Attachment ID or object.
	 * @param array      $meta       Array of attachment meta data.
	 *
	 * @return array     $response
	 */
	public function wpmedia_add_woowgallery_data( $response, $attachment, $meta ) {
		$attachment                   = get_post( $attachment );
		$response['copyright']        = get_post_meta( $attachment->ID, '_media_copyright', true );
		$response['tags_taxonomy']    = Taxonomies::MEDIA_TAG_TAXONOMY_NAME;
		$response['woowgallery_tags'] = wp_get_object_terms(
			$attachment->ID,
			'media_tag',
			[
				'orderby' => 'name',
				'order'   => 'ASC',
				'fields'  => 'names',
			]
		);

		return $response;
	}

	/**
	 * Add keywords to media tag taxonomy
	 *
	 * @param array  $metadata      An array of attachment meta data.
	 * @param int    $attachment_id Current attachment ID.
	 * @param string $context       Additional context. Can be 'create' when metadata was initially created for new attachment.
	 *
	 * @return array
	 */
	public function add_keywords_as_media_tags( $metadata, $attachment_id, $context ) {

		// Check if the image is just uploaded and have keywords.
		if ( 'create' === $context && ! empty( $metadata['image_meta']['keywords'] ) ) {
			$terms = array_map( 'trim', $metadata['image_meta']['keywords'] );
			wp_set_object_terms( $attachment_id, $terms, Taxonomies::MEDIA_TAG_TAXONOMY_NAME );
		}

		// Finally, return the data that's expected.
		return $metadata;

	}

	/**
	 * Check if the EXIF orientation flag matches one of the values we're looking for
	 * http://www.impulseadventure.com/photo/exif-orientation.html
	 *
	 * If it does, this means we need to rotate the image based on the orientation flag and then remove the flag.
	 * This will ensure the image has the correct orientation, regardless of where it's displayed.
	 *
	 * Whilst most browsers and applications will read this flag to perform the rotation on displaying just the image,
	 * it's not possible to do this in some situations e.g. displaying an image within a lightbox, or when the image is
	 * within HTML markup.
	 *
	 * Orientation flags we're looking for:
	 * 8: We need to rotate the image 90 degrees counter-clockwise
	 * 3: We need to rotate the image 180 degrees
	 * 6: We need to rotate the image 90 degrees clockwise (270 degrees counter-clockwise)
	 *
	 * @param array $file Uploaded File.
	 *
	 * @return array         Uploaded File
	 */
	public function fix_image_orientation( $file ) {

		// Check we have a file.
		if ( ! file_exists( $file['file'] ) ) {
			return $file;
		}

		// Check we have a JPEG.
		if ( 'image/jpg' !== $file['type'] && 'image/jpeg' !== $file['type'] ) {
			return $file;
		}

		// Attempt to read EXIF data from the image.
		$exif_data = wp_read_image_metadata( $file['file'] );
		if ( ! $exif_data ) {
			return $file;
		}

		// Check if an orientation flag exists.
		if ( ! isset( $exif_data['orientation'] ) ) {
			return $file;
		}

		// Check if the orientation flag matches one we're looking for.
		$required_orientations = [ 8, 3, 6 ];
		if ( ! in_array( $exif_data['orientation'], $required_orientations, true ) ) {
			return $file;
		}

		// If here, the orientation flag matches one we're looking for
		// Load the WordPress Image Editor class.
		$image = wp_get_image_editor( $file['file'] );
		if ( is_wp_error( $image ) ) {
			// Something went wrong - abort.
			return $file;
		}

		// Store the source image EXIF and IPTC data in a variable, which we'll write
		// back to the image once its orientation has changed
		// This is required because when we save an image, it'll lose its metadata.
		$source_size = getimagesize( $file['file'], $image_info );

		// Depending on the orientation flag, rotate the image.
		switch ( $exif_data['orientation'] ) {

			/**
			 * Rotate 90 degrees counter-clockwise
			 */
			case 8:
				$image->rotate( 90 );
				break;

			/**
			 * Rotate 180 degrees
			 */
			case 3:
				$image->rotate( 180 );
				break;

			/**
			 * Rotate 270 degrees counter-clockwise ($image->rotate always works counter-clockwise)
			 */
			case 6:
				$image->rotate( 270 );
				break;

		}

		// Save the image, overwriting the existing image.
		// This will discard the EXIF and IPTC data.
		$image->save( $file['file'] );

		// Drop the EXIF orientation flag, otherwise applications will try to rotate the image
		// before display it, and we don't need that to happen as we've corrected the orientation.

		// Write the EXIF and IPTC metadata to the revised image.
		$result = $this->transfer_iptc_exif_to_image( $image_info, $file['file'], $exif_data['orientation'] );
		if ( ! $result ) {
			return $file;
		}

		// Read the image again to see if the EXIF data was preserved.
		$exif_data = wp_read_image_metadata( $file['file'] );

		// Finally, return the data that's expected.
		return $file;

	}

	/**
	 * Transfers IPTC and EXIF data from a source image which contains either/both,
	 * and saves it into a destination image's headers that might not have this IPTC
	 * or EXIF data
	 *
	 * Useful for when you edit an image through PHP and need to preserve IPTC and EXIF
	 * data
	 *
	 * @param string $image_info             EXIF and IPTC image information from the source image, using getimagesize().
	 * @param string $destination_image      Path and File of Destination Image, which needs IPTC and EXIF data.
	 * @param int    $original_orientation   The image's original orientation, before we changed it.
	 *                                       Used when we replace this orientation in the EXIF data.
	 *
	 * @return bool                          Success.
	 *
	 * @source http://php.net/iptcembed - ebashkoff at gmail dot com
	 */
	private function transfer_iptc_exif_to_image( $image_info, $destination_image, $original_orientation ) {

		// Check destination exists.
		if ( ! file_exists( $destination_image ) ) {
			return false;
		}

		// Get EXIF data from the image info, and create the IPTC segment.
		$exif_data = ( ( is_array( $image_info ) && key_exists( 'APP1', $image_info ) ) ? $image_info['APP1'] : null );
		if ( $exif_data ) {
			// Find the image's original orientation flag, and change it to 1
			// This prevents applications and browsers re-rotating the image, when we've already performed that function.
			$exif_data = str_replace( chr( dechex( $original_orientation ) ), chr( 0x1 ), $exif_data );

			$exif_length = strlen( $exif_data ) + 2;
			if ( $exif_length > 0xFFFF ) {
				return false;
			}

			// Construct EXIF segment.
			$exif_data = chr( 0xFF ) . chr( 0xE1 ) . chr( ( $exif_length >> 8 ) & 0xFF ) . chr( $exif_length & 0xFF ) . $exif_data;
		}

		// Get IPTC data from the source image, and create the IPTC segment.
		$iptc_data = ( ( is_array( $image_info ) && key_exists( 'APP13', $image_info ) ) ? $image_info['APP13'] : null );
		if ( $iptc_data ) {
			$iptc_length = strlen( $iptc_data ) + 2;
			if ( $iptc_length > 0xFFFF ) {
				return false;
			}

			// Construct IPTC segment.
			$iptc_data = chr( 0xFF ) . chr( 0xED ) . chr( ( $iptc_length >> 8 ) & 0xFF ) . chr( $iptc_length & 0xFF ) . $iptc_data;
		}

		// Get the contents of the destination image.
		$destination_image_contents = file_get_contents( $destination_image );
		if ( ! $destination_image_contents ) {
			return false;
		}
		if ( strlen( $destination_image_contents ) === 0 ) {
			return false;
		}

		// Build the EXIF and IPTC data headers.
		$destination_image_contents = substr( $destination_image_contents, 2 );
		$portion_to_add             = chr( 0xFF ) . chr( 0xD8 ); // Variable accumulates new & original IPTC application segments.
		$exif_added                 = ! $exif_data;
		$iptc_added                 = ! $iptc_data;

		while ( ( substr( $destination_image_contents, 0, 2 ) & 0xFFF0 ) === 0xFFE0 ) {
			$segment_length      = ( substr( $destination_image_contents, 2, 2 ) & 0xFFFF );
			$iptc_segment_number = ( substr( $destination_image_contents, 1, 1 ) & 0x0F );   // Last 4 bits of second byte is IPTC segment #.
			if ( $segment_length <= 2 ) {
				return false;
			}

			$thisexistingsegment = substr( $destination_image_contents, 0, $segment_length + 2 );
			if ( ( 1 <= $iptc_segment_number ) && ( ! $exif_added ) ) {
				$portion_to_add .= $exif_data;
				$exif_added     = true;
				if ( 1 === $iptc_segment_number ) {
					$thisexistingsegment = '';
				}
			}

			if ( ( 13 <= $iptc_segment_number ) && ( ! $iptc_added ) ) {
				$portion_to_add .= $iptc_data;
				$iptc_added     = true;
				if ( 13 === $iptc_segment_number ) {
					$thisexistingsegment = '';
				}
			}

			$portion_to_add             .= $thisexistingsegment;
			$destination_image_contents = substr( $destination_image_contents, $segment_length + 2 );
		}

		// Write the EXIF and IPTC data to the new file.
		if ( ! $exif_added ) {
			$portion_to_add .= $exif_data;
		}
		if ( ! $iptc_added ) {
			$portion_to_add .= $iptc_data;
		}

		$output_file = fopen( $destination_image, 'w' );
		if ( $output_file ) {
			return fwrite( $output_file, $portion_to_add . $destination_image_contents );
		}

		return false;
	}

	/**
	 * Filters the attachment fields to edit.
	 *
	 * @param array   $form_fields An array of attachment form fields.
	 * @param WP_Post $post        The WP_Post attachment object.
	 *
	 * @return array
	 */
	public function attachment_fields_to_edit( $form_fields, $post ) {

		$form_fields['wg_media_copyright'] = [
			'label' => __( 'Copyright', 'woowgallery' ),
			'input' => 'text',
			'value' => get_post_meta( $post->ID, '_media_copyright', true ),
		];

		return $form_fields;
	}

	/**
	 * Filters the attachment fields to be saved.
	 *
	 * @param array $post       An array of post data.
	 * @param array $attachment An array of attachment metadata.
	 *
	 * @return array
	 */
	public function attachment_fields_to_save( $post, $attachment ) {
		if ( ! empty( $attachment['wg_media_copyright'] ) ) {
			update_metadata( 'post', $post['ID'], '_media_copyright', wp_strip_all_tags( $attachment['wg_media_copyright'] ) );
		} else {
			update_metadata( 'post', $post['ID'], '_media_copyright', '' );
		}

		return $post;
	}

	/**
	 * Get extended image metadata, exif or iptc as available.
	 * Retrieves the EXIF metadata aperture, credit, camera, caption, copyright, iso
	 * created_timestamp, focal_length, shutter_speed, and title.
	 * The IPTC metadata that is retrieved is APP13, credit, byline, created date
	 * and time, caption, copyright, and title. Also includes FNumber, Model,
	 * DateTimeDigitized, FocalLength, ISOSpeedRatings, and ExposureTime.
	 *
	 * @param string $file Path to the file.
	 *
	 * @return bool|array False on failure. Image metadata array on success.
	 */
	public function wp_read_image_metadata( $file ) {
		if ( ! is_file( $file ) ) {
			return false;
		}

		list( , , $source_image_type ) = getimagesize( $file );

		$meta = [];

		// Read IPTC first, since it might contain data not available in exif such as caption, description etc.
		if ( is_callable( 'iptcparse' ) ) {
			getimagesize( $file, $info );

			if ( ! empty( $info['APP13'] ) ) {
				$iptc = iptcparse( $info['APP13'] );

				// Headline, "A brief synopsis of the caption".
				if ( ! empty( $iptc['2#105'][0] ) ) {
					$meta['title'] = trim( $iptc['2#105'][0] );

					// Title, "Many use the Title field to store the filename of the image, though the field may be used in many ways".
				} elseif ( ! empty( $iptc['2#005'][0] ) ) {
					$meta['title'] = trim( $iptc['2#005'][0] );
				}

				// Description / legacy caption.
				if ( ! empty( $iptc['2#120'][0] ) ) {
					$caption = trim( $iptc['2#120'][0] );
					if ( empty( $meta['title'] ) ) {
						mbstring_binary_safe_encoding();
						$caption_length = strlen( $caption );
						reset_mbstring_encoding();

						// Assume the title is stored in 2:120 if it's short.
						if ( $caption_length < 80 ) {
							$meta['title'] = $caption;
						} else {
							$meta['caption'] = $caption;
						}
					} elseif ( $caption !== $meta['title'] ) {
						$meta['caption'] = $caption;
					}
				}

				// Credit.
				if ( ! empty( $iptc['2#110'][0] ) ) {
					$meta['credit'] = trim( $iptc['2#110'][0] );
					// Creator / legacy byline.
				} elseif ( ! empty( $iptc['2#080'][0] ) ) {
					$meta['credit'] = trim( $iptc['2#080'][0] );
				}

				// Created date and time.
				if ( ! empty( $iptc['2#055'][0] ) && ! empty( $iptc['2#060'][0] ) ) {
					$meta['created_timestamp'] = strtotime( $iptc['2#055'][0] . ' ' . $iptc['2#060'][0] );
				}

				// Copyright.
				if ( ! empty( $iptc['2#116'][0] ) ) {
					$meta['copyright'] = trim( $iptc['2#116'][0] );
				}

				// Keywords.
				if ( ! empty( $iptc['2#025'] ) ) {
					$meta['keywords'] = $iptc['2#025'];
				}
			}
		}

		/**
		 * Filter the image types to check for exif data.
		 *
		 * @param array $image_types Image types to check for exif data.
		 */
		if (
			is_callable( 'exif_read_data' )
			&& in_array(
				$source_image_type,
				apply_filters(
					'woow_read_image_metadata_types',
					[
						IMAGETYPE_JPEG,
						IMAGETYPE_TIFF_II,
						IMAGETYPE_TIFF_MM,
					]
				),
				true
			)
		) {
			$exif = @exif_read_data( $file );
			unset( $exif['MakerNote'] );

			// Title.
			if ( empty( $meta['title'] ) && ! empty( $exif['Title'] ) ) {
				$meta['title'] = trim( $exif['Title'] );
			}
			// Description.
			if ( ! empty( $exif['ImageDescription'] ) ) {
				mbstring_binary_safe_encoding();
				$description_length = strlen( $exif['ImageDescription'] );
				reset_mbstring_encoding();

				if ( empty( $meta['title'] ) && $description_length < 80 ) {
					// Assume the title is stored in ImageDescription.
					$meta['title'] = trim( $exif['ImageDescription'] );
					if ( empty( $meta['caption'] ) && ! empty( $exif['COMPUTED']['UserComment'] ) && trim( $exif['COMPUTED']['UserComment'] ) !== $meta['title'] ) {
						$meta['caption'] = trim( $exif['COMPUTED']['UserComment'] );
					}
				} elseif ( empty( $meta['caption'] ) && trim( $exif['ImageDescription'] ) !== $meta['title'] ) {
					$meta['caption'] = trim( $exif['ImageDescription'] );
				}
			} elseif ( empty( $meta['caption'] ) && ! empty( $exif['Comments'] ) && trim( $exif['Comments'] ) !== $meta['title'] ) {
				$meta['caption'] = trim( $exif['Comments'] );
			}
			// Credit.
			if ( empty( $meta['credit'] ) ) {
				if ( ! empty( $exif['Artist'] ) ) {
					$meta['credit'] = trim( $exif['Artist'] );
				} elseif ( ! empty( $exif['Author'] ) ) {
					$meta['credit'] = trim( $exif['Author'] );
				}
			}
			// Copyright.
			if ( empty( $meta['copyright'] ) && ! empty( $exif['Copyright'] ) ) {
				$meta['copyright'] = trim( $exif['Copyright'] );
			}
			// Camera Make.
			if ( ! empty( $exif['Make'] ) ) {
				$meta['make'] = $exif['Make'];
			}
			// Camera Model.
			if ( ! empty( $exif['Model'] ) ) {
				$meta['model'] = trim( $exif['Model'] );
			}
			// Exposure Time (shutter speed).
			if ( ! empty( $exif['ExposureTime'] ) ) {
				$meta['exposure']      = $exif['ExposureTime'] . 's';
				$meta['shutter_speed'] = (string) wp_exif_frac2dec( $exif['ExposureTime'] ) . 's';
			}
			// Aperture.
			if ( ! empty( $exif['COMPUTED']['ApertureFNumber'] ) ) {
				$meta['aperture'] = $exif['COMPUTED']['ApertureFNumber'];
			} elseif ( ! empty( $exif['FNumber'] ) ) {
				$meta['aperture'] = 'f/' . (string) round( wp_exif_frac2dec( $exif['FNumber'] ), 2 );
			}
			// ISO.
			if ( ! empty( $exif['ISOSpeedRatings'] ) ) {
				$meta['iso'] = is_array( $exif['ISOSpeedRatings'] ) ? reset( $exif['ISOSpeedRatings'] ) : $exif['ISOSpeedRatings'];
				$meta['iso'] = trim( $meta['iso'] );
			}
			// Date.
			if ( ! empty( $exif['DateTime'] ) ) {
				$meta['date'] = $exif['DateTime'];
			}
			// Created TimeStamp.
			if ( empty( $meta['created_timestamp'] ) && ! empty( $exif['DateTimeDigitized'] ) ) {
				$meta['created_timestamp'] = wp_exif_date2ts( $exif['DateTimeDigitized'] );
			}
			// Lens.
			if ( ! empty( $exif['UndefinedTag:0xA434'] ) ) {
				$meta['lens'] = $exif['UndefinedTag:0xA434'];
			}
			// Focus Distance.
			if ( ! empty( $exif['COMPUTED']['FocusDistance'] ) ) {
				$meta['distance'] = $exif['COMPUTED']['FocusDistance'];
			}
			// Focal Length.
			if ( ! empty( $exif['FocalLength'] ) ) {
				$meta['focallength'] = (string) round( wp_exif_frac2dec( $exif['FocalLength'] ) ) . 'mm';
			}
			// Focal Length 35mm.
			if ( ! empty( $exif['FocalLengthIn35mmFilm'] ) ) {
				$meta['focallength35'] = $exif['FocalLengthIn35mmFilm'] . 'mm';
			}
			// Lens Make.
			if ( ! empty( $exif['UndefinedTag:0xA433'] ) ) {
				$meta['lensmake'] = $exif['UndefinedTag:0xA433'];
			}
			// Software.
			if ( ! empty( $exif['Software'] ) ) {
				$meta['software'] = $exif['Software'];
			}
			// Orientation.
			if ( ! empty( $exif['Orientation'] ) ) {
				$meta['orientation'] = $exif['Orientation'];
			}

			$exif_sections = @exif_read_data( $file, null, true );
			if ( isset( $exif_sections['GPS'] ) ) {
				$meta['GPS'] = $this->getGPSfromExif( $exif_sections['GPS'] );
			}
			unset( $exif_sections );
			//$meta['exif'] = $exif;
		}

		foreach ( [ 'title', 'caption', 'credit', 'copyright', 'model', 'iso', 'software' ] as $key ) {
			if ( ! empty( $meta[ $key ] ) && ! seems_utf8( $meta[ $key ] ) ) {
				$meta[ $key ] = utf8_encode( $meta[ $key ] );
			}
		}
		if ( ! empty( $meta['keywords'] ) ) {
			foreach ( $meta['keywords'] as $i => $key ) {
				if ( ! seems_utf8( $key ) ) {
					$meta['keywords'][ $i ] = utf8_encode( $key );
				}
			}
		}

		foreach ( $meta as &$value ) {
			if ( is_string( $value ) ) {
				$value = wp_kses_post( $value );
			}
		}

		/**
		 * Filter the array of meta data read from an image's exif data.
		 *
		 * @param array  $meta              Image meta data.
		 * @param string $file              Path to image file.
		 * @param int    $source_image_type Type of image.
		 */
		return apply_filters( 'woow_read_image_metadata', $meta, $file, $source_image_type );
	}

}
