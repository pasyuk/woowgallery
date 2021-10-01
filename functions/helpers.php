<?php
/**
 * Helper functions.
 *
 * @package woowgallery
 */

use WoowGallery\Admin\Settings;
use WoowGallery\Gallery;
use WoowGallery\Posttypes;

if ( ! function_exists( 'woowgallery' ) ) {
	/**
	 * Primary template tag for outputting WoowGallery galleries in templates.
	 * Conditionally load the template tag.
	 *
	 * @param int    $id     The ID of the gallery to load.
	 * @param string $type   Post Type.
	 * @param array  $args   Associative array of args to be passed.
	 * @param bool   $return Flag to echo or return the gallery HTML.
	 *
	 * @return string|void Shortcode content
	 */
	function woowgallery( $id, $type = null, $args = [], $return = false ) {

		// If we have args, build them into a shortcode format.
		$args_string = '';
		if ( ! empty( $args ) ) {
			foreach ( (array) $args as $key => $value ) {
				if ( ! ( is_int( $value ) || is_string( $value ) ) || in_array( $key, [ 'id', 'posttype' ], true ) ) {
					continue;
				}
				$args_string .= ' ' . sanitize_key( $key ) . '="' . esc_attr( $value ) . '"';
			}
		}
		if ( ! $type || ! in_array( $type, [ Posttypes::GALLERY_POSTTYPE, Posttypes::DYNAMIC_POSTTYPE, Posttypes::ALBUM_POSTTYPE ], true ) ) {
			$type = Posttypes::GALLERY_POSTTYPE;
		}
		$wg = Gallery::get_instance( $id, $type );

		// Build the shortcode.
		$shortcode = '[' . $type . ' id="' . $wg->get_id() . '"' . $args_string . ']';

		// Return or echo the shortcode output.
		if ( $return ) {
			return do_shortcode( $shortcode );
		} else {
			echo do_shortcode( $shortcode );
		}

	}
}

if ( ! function_exists( 'woowgallery_prepare_post_data' ) ) {

	/**
	 * Prepare Attachment Data Model
	 *
	 * @param WP_Post|int $post Post ID or object.
	 *
	 * @return array|void Array of attachment details.
	 */
	function woowgallery_prepare_post_data( $post ) {
		$post = get_post( $post );
		if ( ! $post ) {
			return;
		}

		if ( 'attachment' === $post->post_type ) {
			return woowgallery_prepare_attachment_data( $post );
		}

		$response = [
			'type'        => 'post',
			'subtype'     => $post->post_type,
			'status'      => $post->post_status,
			'id'          => (string) $post->ID,
			'title_src'   => 'title',
			'alt_src'     => 'title',
			'caption_src' => 'excerpt',
			'title'       => '',
			'alt'         => '',
			'caption'     => '',
			'link'        => [
				'text'   => '',
				'url'    => '',
				'target' => apply_filters( 'woowgallery_default_post_link_target', '_self', $post ),
			],
			'tags'        => '',
		];

		/**
		 * Filters the attachment data prepared.
		 *
		 * @param array      $response Array of prepared attachment data.
		 * @param int|object $post     Attachment ID or object.
		 * @param array      $meta     Array of attachment meta data.
		 */
		return apply_filters( 'woowgallery_prepare_attachment_data', $response, $post );
	}
}

if ( ! function_exists( 'woowgallery_prepare_attachment_data' ) ) {

	/**
	 * Prepare Attachment Data Model
	 *
	 * @param WP_Post|int $attachment Attachment ID or object.
	 *
	 * @return array|void Array of attachment details.
	 */
	function woowgallery_prepare_attachment_data( $attachment ) {
		$attachment = get_post( $attachment );
		if ( ! $attachment ) {
			return;
		}

		if ( 'attachment' !== $attachment->post_type ) {
			return;
		}

		$type = explode( '/', $attachment->post_mime_type );

		$response = [
			'type'        => $attachment->post_type,
			'subtype'     => $type[0],
			'status'      => apply_filters( 'woowgallery_default_media_status', 'publish', $attachment ),
			'id'          => (string) $attachment->ID,
			'title_src'   => 'title',
			'alt_src'     => 'alt',
			'caption_src' => 'caption',
			'title'       => '',
			'alt'         => '',
			'caption'     => '',
			'link'        => [
				'text'   => '',
				'url'    => '',
				'target' => apply_filters( 'woowgallery_default_media_link_target', '_self', $attachment ),
			],
			'tags'        => '',
		];

		/**
		 * Filters the attachment data prepared.
		 *
		 * @param array      $response   Array of prepared attachment data.
		 * @param int|object $attachment Attachment ID or object.
		 * @param array      $meta       Array of attachment meta data.
		 */
		return apply_filters( 'woowgallery_prepare_attachment_data', $response, $attachment );
	}
}

if ( ! function_exists( 'woowgallery_full_post_data' ) ) {

	/**
	 * Get Full Post Data Model
	 *
	 * @param array        $attachment WoowGallery Attachment data.
	 * @param WP_Post|null $gallery    Gallery Post.
	 *
	 * @return array|null Array of attachment details.
	 */
	function woowgallery_full_post_data( $attachment, $gallery = null ) {
		if ( 'attachment' === $attachment['type'] ) {
			return woowgallery_full_attachment_data( $attachment, $gallery );
		}

		if ( empty( $attachment['id'] ) || 'post' !== $attachment['type'] ) {
			return null;
		}

		$post = get_post( $attachment['id'] );
		if ( empty( $post ) || $attachment['subtype'] !== $post->post_type ) {
			return null;
		}

		if ( $attachment['status'] !== $post->post_status && in_array( $post->post_status, [ 'future', 'pending', 'draft' ], true ) ) {
			$attachment['status'] = $post->post_status;
		}

		if ( 'title' === $attachment['title_src'] ) {
			$attachment['title'] = $post->post_title;
		}

		if ( 'title' === $attachment['alt_src'] ) {
			$attachment['alt'] = $attachment['title'];
		}

		if ( 'excerpt' === $attachment['caption_src'] ) {
			$attachment['caption'] = Posttypes::GALLERY_POSTTYPE === $post->post_type ? $post->post_content : $post->post_excerpt;
		} elseif ( 'content' === $attachment['caption_src'] ) {
			$attachment['caption'] = $post->post_content;
		}

		$author = woowgallery_get_attachment_author( $post );
		if ( $author ) {
			$attachment['author'] = $author;
		}

		$attachment['slug']     = $post->post_name;
		$attachment['date']     = mysql_to_rfc3339( $post->post_date );
		$attachment['src']      = wp_get_shortlink( $post->ID );
		$attachment['comments'] = [
			'status' => $post->comment_status,
			'count'  => $post->comment_count,
		];

		$image_id = get_post_thumbnail_id( $post->ID );
		if ( empty( $image_id ) ) {
			$attached_media               = get_attached_media( 'image', $post->ID );
			$attachment['attached_media'] = $attached_media;
			if ( ! empty( $attached_media ) ) {
				$cover    = reset( $attached_media );
				$image_id = $cover->ID;
			}
		}

		$att_images = woowgallery_get_attachment_images( $image_id, $gallery );
		$attachment = $attachment + $att_images;

		$attachment['has_password'] = ! empty( $post->post_password );
		$attachment['taxonomies']   = woowgallery_get_object_taxonomy_terms( $post );

		if ( isset( $attachment['taxonomies']['post_tag'] ) ) {
			$attachment['tags_taxonomy'] = 'post_tag';
			$tags                        = $attachment['taxonomies']['post_tag']['terms'];
			unset( $attachment['taxonomies']['post_tag'] );
		} elseif ( isset( $attachment['taxonomies'][ $post->post_type . '_tag' ] ) ) {
			$attachment['tags_taxonomy'] = $post->post_type . '_tag';
			$tags                        = $attachment['taxonomies'][ $post->post_type . '_tag' ]['terms'];
			unset( $attachment['taxonomies'][ $post->post_type . '_tag' ] );
		} else {
			$attachment['tags_taxonomy'] = '';
			$tags                        = [];
		}

		if ( ! empty( $attachment['tags'] ) ) {
			$attachment['tags'] = array_values(
				array_unique(
					array_merge(
						array_filter(
							array_map(
								'trim',
								explode( ',', $attachment['tags'] )
							)
						),
						$tags
					)
				)
			);
		} else {
			$attachment['tags'] = $tags;
		}

		if ( in_array( $post->post_type, [ Posttypes::GALLERY_POSTTYPE, Posttypes::ALBUM_POSTTYPE ], true ) ) {
			$attachment['count'] = get_post_meta( $post->ID, Gallery::GALLERY_MEDIA_COUNT_META_KEY, true );
		}

		/**
		 * Filters the attachment data prepared.
		 *
		 * @param array $attachment Array of prepared attachment data.
		 * @param array $meta       Array of attachment meta data.
		 */
		return apply_filters( 'woowgallery_prepare_full_post_data', $attachment );
	}
}

if ( ! function_exists( 'woowgallery_full_attachment_data' ) ) {

	/**
	 * Get Full Attachment Data Model
	 *
	 * @param array        $attachment WoowGallery Attachment data.
	 * @param WP_Post|null $gallery    Gallery Post.
	 *
	 * @return array|void Array of attachment details.
	 */
	function woowgallery_full_attachment_data( $attachment, $gallery = null ) {
		if ( empty( $attachment['id'] ) || 'attachment' !== $attachment['type'] ) {
			return null;
		}

		$post = get_post( $attachment['id'] );
		if ( empty( $post ) || 'attachment' !== $post->post_type ) {
			return null;
		}

		if ( 'title' === $attachment['title_src'] ) {
			$attachment['title'] = $post->post_title;
		}

		switch ( $attachment['alt_src'] ) {
			case 'title':
				$attachment['alt'] = $post->post_title;
				break;
			case 'alt':
				$attachment['alt'] = get_post_meta( $post->ID, '_wp_attachment_image_alt', true );
				break;
		}

		switch ( $attachment['caption_src'] ) {
			case 'caption':
				$attachment['caption'] = $post->post_excerpt;
				break;
			case 'description':
				$attachment['caption'] = $post->post_content;
				break;
		}

		$author = woowgallery_get_attachment_author( $post );
		if ( $author ) {
			$attachment['author'] = $author;
		}

		$attachment['slug'] = $post->post_name;
		$attachment['date'] = mysql_to_rfc3339( $post->post_date );
		$attachment['src']  = wp_get_shortlink( $post->ID );

		$attachment['original'] = wp_get_attachment_url( $post->ID );

		$attachment['comments'] = [
			'status' => $post->comment_status,
			'count'  => $post->comment_count,
		];

		$attachment['copyright'] = get_post_meta( $post->ID, '_media_copyright', true );

		$att_images = woowgallery_get_attachment_images( $post->ID, $gallery );
		$attachment = $attachment + $att_images;

		$meta          = wp_get_attachment_metadata( $post->ID );
		$attached_file = $att_images['file'];

		if ( $meta && 'image' === $attachment['subtype'] ) {
			if ( ! function_exists( 'wp_read_image_metadata' ) ) {
				include_once ABSPATH . 'wp-admin/includes/image.php';
			}
			$attachment['meta'] = wp_read_image_metadata( $attached_file );
			unset( $attachment['meta']['title'], $attachment['meta']['caption'], $attachment['meta']['orientation'] );
			$attachment['meta'] = array_filter(
				$attachment['meta'],
				function ( $value ) {
					return ! empty( $value );
				}
			);
		}

		if ( $meta && 'image' !== $attachment['subtype'] ) {
			$bytes = '';
			if ( isset( $meta['filesize'] ) ) {
				$bytes = $meta['filesize'];
			} elseif ( file_exists( $attached_file ) ) {
				$bytes = filesize( $attached_file );
			}
			$attachment['meta']['filesize'] = $bytes;
		}

		if ( $meta && 'video' === $attachment['subtype'] ) {
			if ( isset( $meta['width'] ) ) {
				$attachment['meta']['width'] = (int) $meta['width'];
			}
			if ( isset( $meta['height'] ) ) {
				$attachment['meta']['height'] = (int) $meta['height'];
			}
		}

		if ( $meta && ( 'audio' === $attachment['subtype'] || 'video' === $attachment['subtype'] ) ) {
			if ( isset( $meta['length_formatted'] ) ) {
				$attachment['meta']['length'] = $meta['length_formatted'];
			}

			if ( ! function_exists( 'wp_get_attachment_id3_keys' ) ) {
				include_once ABSPATH . 'wp-admin/includes/media.php';
			}
			$attachment['meta'] = [];
			$id3_keys           = wp_get_attachment_id3_keys( $post, 'display' ) + wp_get_attachment_id3_keys( $post, 'js' );
			foreach ( $id3_keys as $key => $label ) {
				$attachment['meta'][ $key ] = false;

				if ( ! empty( $meta[ $key ] ) ) {
					$attachment['meta'][ $key ] = $meta[ $key ];
				}
			}

			$image_id = get_post_thumbnail_id( $post->ID );
			if ( ! empty( $image_id ) ) {
				$att_images = woowgallery_get_attachment_images( $image_id, $gallery );
				$attachment = array_merge( $attachment, $att_images );
			}
		}

		$attachment['tags_taxonomy'] = 'media_tag';

		$tags = wp_get_object_terms(
			$post->ID,
			'media_tag',
			[
				'orderby' => 'name',
				'order'   => 'ASC',
				'fields'  => 'names',
			]
		);

		if ( ! empty( $attachment['tags'] ) ) {
			$attachment['tags'] = array_values(
				array_unique(
					array_merge(
						array_filter(
							array_map(
								'trim',
								explode( ',', $attachment['tags'] )
							)
						),
						$tags
					)
				)
			);
		} else {
			$attachment['tags'] = $tags;
		}

		/**
		 * Filters the attachment data prepared.
		 *
		 * @param array $attachment Array of prepared attachment data.
		 * @param array $meta       Array of attachment meta data.
		 */
		return apply_filters( 'woowgallery_prepare_full_attachment_data', $attachment, $meta );
	}
}

if ( ! function_exists( 'woowgallery_full_instagram_data' ) ) {

	/**
	 * Get Full Instagram Data Model
	 *
	 * @param array $media      Instagram media object.
	 * @param bool  $source_tag Source has tags.
	 *
	 * @return array|void Array of attachment details.
	 */
	function woowgallery_full_instagram_data( $media, $source_tag = false ) {
		if ( empty( $media ) || ( $source_tag && in_array( $media['type'], [ 'video', 'carousel' ], true ) ) ) {
			return null;
		}

		$proxy = home_url( '/woow-ig-proxy' );

		$attachment = [
			'type'       => 'instagram',
			'subtype'    => $media['type'],
			'status'     => 'publish',
			'id'         => $media['id'],
			'title'      => '',
			'alt'        => '',
			'caption'    => ! empty( $media['caption']['text'] ) ? woowgallery_convert_encoding( $media['caption']['text'] ) : '',
			'link'       => [
				'text'   => '',
				'url'    => $media['link'],
				'target' => apply_filters( 'woowgallery_default_instagram_link_target', '_self', $media ),
			],
			'tags'       => $media['tags'],
			'author'     => [
				'id'       => $media['user']['id'],
				'name'     => ! empty( $media['user']['full_name'] ) ? woowgallery_convert_encoding( $media['user']['full_name'] ) : '',
				'username' => ! empty( $media['user']['username'] ) ? $media['user']['username'] : '',
				'avatar'   => ! empty( $media['user']['profile_picture'] ) ? $media['user']['profile_picture'] : '',
			],
			'slug'       => $media['code'],
			'date'       => mysql_to_rfc3339( date( 'Y-m-d H:i:s', $media['created_time'] ) ),
			'src'        => add_query_arg( [ 'url' => rawurlencode( $media['video_url'] ) ], $proxy ) ?: add_query_arg( [ 'url' => rawurlencode( $media['images']['__original']['url'] ) ], $proxy ),
			'thumb'      => [
				add_query_arg( [ 'url' => rawurlencode( $media['images']['low_resolution']['url'] ) ], $proxy ),
				$media['images']['low_resolution']['width'],
				$media['images']['low_resolution']['height'],
			],
			'image'      => [
				add_query_arg( [ 'url' => rawurlencode( $media['images']['standard_resolution']['url'] ) ], $proxy ),
				$media['images']['standard_resolution']['width'],
				$media['images']['standard_resolution']['height'],
			],
			'_thumbnail' => [
				add_query_arg( [ 'url' => rawurlencode( $media['images']['thumbnail']['url'] ) ], $proxy ),
				$media['images']['thumbnail']['width'],
				$media['images']['thumbnail']['height'],
			],
			'_original'  => [
				add_query_arg( [ 'url' => rawurlencode( $media['images']['__original']['url'] ) ], $proxy ),
				$media['images']['__original']['width'],
				$media['images']['__original']['height'],
			],
			'likes'      => $media['likes'],
			'comments'   => $media['comments'],
			'location'   => $media['location'],
		];

		if ( ! empty( $media['carousel'] ) ) {
			$attachment['carousel'] = [];
			foreach ( $media['carousel'] as $item ) {
				$carousel_item = [
					'id'      => $item['id'],
					'type'    => $item['is_video'] ? 'video' : 'image',
					'sources' => [],
				];
				foreach ( $item['display_resources'] as $src ) {
					$carousel_item['sources'][] = [
						add_query_arg( [ 'url' => rawurlencode( $src['src'] ) ], $proxy ),
						$src['config_width'],
						$src['config_height'],
					];
				}

				if ( $item['is_video'] ) {
					$carousel_item['src']              = add_query_arg( [ 'url' => rawurlencode( $item['video_url'] ) ], $proxy );
					$carousel_item['video_view_count'] = $item['video_view_count'];
				} else {
					$last_source          = end( $item['display_resources'] );
					$carousel_item['src'] = add_query_arg( [ 'url' => rawurlencode( $last_source['src'] ) ], $proxy );
				}

				$attachment['carousel'][] = $carousel_item;
			}
		}

		/**
		 * Filters the attachment data prepared.
		 *
		 * @param array $attachment Array of prepared attachment data.
		 * @param array $meta       Array of attachment meta data.
		 */
		return apply_filters( 'woowgallery_prepare_full_instagram_data', $attachment, $media );
	}
}

if ( ! function_exists( 'woowgallery_full_flagallery_data' ) ) {

	/**
	 * Get Full Flagallery Data Model
	 *
	 * @param array $media Flagallery media object.
	 *
	 * @return array|void Array of attachment details.
	 */
	function woowgallery_full_flagallery_data( $media ) {
		if ( empty( $media ) ) {
			return null;
		}

		$alttext    = wp_unslash( $media->alttext );
		$attachment = [
			'type'      => 'flagallery',
			'subtype'   => 'image',
			'status'    => empty( $media->exclude ) ? 'publish' : 'private',
			'id'        => (int) $media->pid,
			'title'     => $alttext,
			'alt'       => $alttext,
			'caption'   => wp_unslash( $media->description ),
			'link'      => [
				'text'   => '',
				'url'    => ! empty( $media->link ) ? esc_url_raw( $media->link ) : '',
				'target' => apply_filters( 'woowgallery_default_link_target', '_self', $media, 'flagallery' ),
			],
			'author'    => [],
			'slug'      => $media->filename,
			'date'      => mysql_to_rfc3339( $media->imagedate ),
			'src'       => $media->imageURL,
			'thumb'     => [
				$media->thumbURL,
				isset( $media->meta_data['thumbnail']['width'] ) ? (int) $media->meta_data['thumbnail']['width'] : 0,
				isset( $media->meta_data['thumbnail']['height'] ) ? (int) $media->meta_data['thumbnail']['height'] : 0,
			],
			'image'     => [
				$media->webimageURL,
				isset( $media->meta_data['webview']['width'] ) ? (int) $media->meta_data['webview']['width'] : 0,
				isset( $media->meta_data['webview']['height'] ) ? (int) $media->meta_data['webview']['height'] : 0,
			],
			'_original' => [
				$media->imageURL,
				isset( $media->meta_data['width'] ) ? (int) $media->meta_data['width'] : 0,
				isset( $media->meta_data['height'] ) ? (int) $media->meta_data['height'] : 0,
			],
			'likes'     => [
				'count' => (int) $media->total_votes,
			],
		];

		$meta = $media->meta_data;

		$tags               = ! empty( $meta['keywords'] ) ? $meta['keywords'] : [];
		$attachment['tags'] = is_array( $tags ) ? $tags : array_map( 'trim', explode( ',', $tags ) );

		$attachment['copyright'] = ! empty( $meta['copyright'] ) ? $meta['copyright'] : '';

		unset( $meta['thumbnail'], $meta['webview'], $meta['width'], $meta['height'], $meta['keywords'], $meta['copyright'], $meta['caption'], $meta['title'] );
		$attachment['meta'] = $meta;

		/**
		 * Filters the attachment data prepared.
		 *
		 * @param array $attachment Array of prepared attachment data.
		 * @param array $meta       Array of attachment meta data.
		 */
		return apply_filters( 'woowgallery_prepare_full_flagallery_data', $attachment, $media );
	}
}

if ( ! function_exists( 'woowgallery_get_attachment_author' ) ) {
	/**
	 * Helper method to get attachment author data.
	 *
	 * @param WP_Post $post Post object.
	 *
	 * @return array|null
	 */
	function woowgallery_get_attachment_author( $post ) {
		$author = new WP_User( $post->post_author );
		if ( $author->exists() ) {
			return [
				'id'   => $post->post_author,
				'name' => html_entity_decode( $author->display_name, ENT_QUOTES, get_bloginfo( 'charset' ) ),
				'url'  => get_the_author_meta( 'url', $author->ID ),
			];
		}

		return null;
	}
}

if ( ! function_exists( 'woowgallery_get_attachment_images' ) ) {
	/**
	 * Helper method to get attachment images (thumb and image).
	 *
	 * @param int          $image_id Attachment ID.
	 * @param WP_Post|null $gallery  Gallery Post object.
	 *
	 * @return array
	 */
	function woowgallery_get_attachment_images( $image_id, $gallery = null ) {
		if ( empty( $image_id ) ) {
			return [
				'thumb'    => [
					plugins_url( 'assets/images/icons/default.png', WOOWGALLERY_FILE ),
					300,
					300,
					true,
				],
				'image'    => [
					plugins_url( 'assets/images/icons/default.png', WOOWGALLERY_FILE ),
					300,
					300,
					true,
				],
				'image_id' => 0,
				'file'     => '',
			];
		}

		$out  = [];
		$src  = wp_get_attachment_image_src( $image_id, 'full', true );
		$dims = woowgallery_get_resize_dimensions( $gallery );

		// Calculate output dimensions after resize for thumbnail.
		$cropped_thumb_dims = wp_constrain_dimensions( $src[1], $src[2], $dims['thumb']['width'], $dims['thumb']['height'] );
		$out['thumb']       = wp_get_attachment_image_src( $image_id, $cropped_thumb_dims, false ) ?: [];

		// Calculate output dimensions after resize for image.
		$cropped_image_dims = wp_constrain_dimensions( $src[1], $src[2], $dims['image']['width'], $dims['image']['height'] );
		$out['image']       = wp_get_attachment_image_src( $image_id, $cropped_image_dims, false ) ?: [];

		if ( empty( $out['thumb'] ) ) {
			$icon_name = basename( $src[0] );
			$icon_path = WOOWGALLERY_PATH . '/assets/images/icons/' . $icon_name;
			if ( is_file( $icon_path ) ) {
				$out['thumb'] = [
					plugins_url( 'assets/images/icons/' . $icon_name, WOOWGALLERY_FILE ),
					300,
					300,
					true,
				];
			} else {
				$out['thumb'] = [
					plugins_url( 'assets/images/icons/default.png', WOOWGALLERY_FILE ),
					300,
					300,
					true,
				];
			}
		}

		if ( empty( $out['image'] ) && ! empty( $out['thumb'] ) ) {
			$out['image'] = $out['thumb'];
		}

		$file     = get_attached_file( $image_id );
		$is_image = wp_attachment_is_image( $image_id );
		if ( $is_image ) {
			$out['image_id'] = absint( $image_id );

			$meta = wp_get_attachment_metadata( $image_id );
			if ( $meta ) {
				$out['resized'] = ( ( $src[1] <= $cropped_image_dims[0] && $src[2] <= $cropped_image_dims[1] ) || _wp_get_image_size_from_meta( "wg{$dims['image']['width']}x{$dims['image']['height']}", $meta ) )
					&& ( ( $src[1] <= $cropped_thumb_dims[0] && $src[2] <= $cropped_thumb_dims[1] ) || _wp_get_image_size_from_meta( "wg{$dims['thumb']['width']}x{$dims['thumb']['height']}", $meta ) );

				if ( $out['resized'] ) {
					$thumbfile = str_replace( wp_basename( $file ), wp_basename( $out['thumb'][0] ), $file );
					if ( ! is_file( $thumbfile ) ) {
						$out['thumb']   = wp_get_attachment_image_src( $image_id, 'medium', false );
						$out['resized'] = false;
					}
					$largefile = str_replace( wp_basename( $file ), wp_basename( $out['image'][0] ), $file );
					if ( ! is_file( $largefile ) ) {
						$out['image']   = wp_get_attachment_image_src( $image_id, 'large', false );
						$out['resized'] = false;
					}
				}
			}
		} else {
			$out['image_id'] = 0;
		}

		$out['file'] = $file;

		return $out;
	}
}

if ( ! function_exists( 'woowgallery_get_resize_dimensions' ) ) {
	/**
	 * Helper method to get resize dimensions.
	 *
	 * @param WP_Post|null $gallery Gallery Post object.
	 *
	 * @return array
	 */
	function woowgallery_get_resize_dimensions( $gallery = null ) {
		if ( is_object( $gallery ) ) {
			$wg   = Gallery::get_instance( $gallery->ID, $gallery->post_type );
			$dims = [
				'thumb' => [
					'width'  => (int) $wg->get_settings( 'thumb_width' ) ?: Settings::get_settings( 'thumb_width', 9999 ),
					'height' => (int) $wg->get_settings( 'thumb_height' ) ?: Settings::get_settings( 'thumb_height', 9999 ),
				],
				'image' => [
					'width'  => (int) $wg->get_settings( 'image_width' ) ?: Settings::get_settings( 'image_width', 9999 ),
					'height' => (int) $wg->get_settings( 'image_height' ) ?: Settings::get_settings( 'image_height', 9999 ),
				],
			];
		} else {
			$dims = [
				'thumb' => [
					'width'  => (int) Settings::get_settings( 'thumb_width', 9999 ),
					'height' => (int) Settings::get_settings( 'thumb_height', 9999 ),
				],
				'image' => [
					'width'  => (int) Settings::get_settings( 'image_width', 9999 ),
					'height' => (int) Settings::get_settings( 'image_height', 9999 ),
				],
			];
		}
		$dims['thumb']['quality'] = (int) Settings::get_settings( 'thumb_quality', 85 );
		$dims['image']['quality'] = (int) Settings::get_settings( 'image_quality', 85 );

		return $dims;
	}
}

if ( ! function_exists( 'woowgallery_flush_caches' ) ) {
	/**
	 * Helper method to flush gallery caches once a gallery is updated.
	 *
	 * @param int    $post_id The current post ID.
	 * @param string $slug    The unique gallery slug.
	 */
	function woowgallery_flush_caches( $post_id, $slug = '' ) {

		// Delete known gallery caches.
		wp_cache_delete( $post_id, 'woowgallery' );
		wp_cache_delete( 'woowgallery_galleries' );

		// Possibly delete slug gallery cache if available.
		if ( ! empty( $slug ) ) {
			wp_cache_delete( $slug, 'woowgallery' );
		}

		// Run a hook for Addons to access.
		do_action( 'woowgallery_flush_caches', $post_id, $slug );

	}
}

if ( ! function_exists( 'wg_json_encode' ) ) {
	/**
	 * Encode a variable into JSON, with some sanity checks.
	 *
	 * @param mixed $data    Variable (usually an array or object) to encode as JSON.
	 * @param int   $options Optional. Options to be passed to json_encode(). Default 0.
	 *
	 * @return string|false The JSON encoded string, or false if it cannot be encoded.
	 */
	function wg_json_encode( $data, $options = 0 ) {
		$options = JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | $options;

		return str_replace( '\u0022', '\\\"', wp_json_encode( $data, $options ) );
	}
}

if ( ! function_exists( 'wg_posttype_icon' ) ) {
	/**
	 * Get Post type icon.
	 *
	 * @param WP_Post_Type $post_type Post type object.
	 *
	 * @return string.
	 */
	function wg_posttype_icon( $post_type ) {
		$menu_icon = $post_type->menu_icon;
		if ( empty( $menu_icon ) || 'none' === $menu_icon || 'div' === $menu_icon ) {
			$menu_icon = 'dashicons-pressthis';
		}
		switch ( $post_type->name ) {
			case 'post':
				$menu_icon = 'dashicons-admin-post';
				break;
			case 'page':
				$menu_icon = 'dashicons-admin-page';
				break;
			case 'product':
				$menu_icon = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/PjxzdmcgaGVpZ2h0PSIxNTNweCIgcHJlc2VydmVBc3BlY3RSYXRpbz0ieE1pZFlNaWQiIHZlcnNpb249IjEuMSIgdmlld0JveD0iMCAwIDI1NiAxNTMiIHdpZHRoPSIyNTZweCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+PGc+PHBhdGggZD0iTTIzLjc1ODY2NDQsMCBMMjMyLjEzNzQzOCwwIEMyNDUuMzI0NjQzLDAgMjU2LDEwLjY3NTM1NjYgMjU2LDIzLjg2MjU2MTcgTDI1NiwxMDMuNDA0NDM0IEMyNTYsMTE2LjU5MTYzOSAyNDUuMzI0NjQzLDEyNy4yNjY5OTYgMjMyLjEzNzQzOCwxMjcuMjY2OTk2IEwxNTcuNDA5OTQyLDEyNy4yNjY5OTYgTDE2Ny42NjY2NTcsMTUyLjM4NTQ4MiBMMTIyLjU1ODA0MywxMjcuMjY2OTk2IEwyMy44NjMzMjQ4LDEyNy4yNjY5OTYgQzEwLjY3NjExOTYsMTI3LjI2Njk5NiAwLjAwMDc2MzAzODQ1OCwxMTYuNTkxNjM5IDAuMDAwNzYzMDM4NDU4LDEwMy40MDQ0MzQgTDAuMDAwNzYzMDM4NDU4LDIzLjg2MjU2MTcgQy0wLjEwMzg5NzMyLDEwLjc4MDAxNjkgMTAuNTcxNDU5MiwwIDIzLjc1ODY2NDQsMCBMMjMuNzU4NjY0NCwwIFoiIGZpbGw9IiM5QjVDOEYiLz48cGF0aCBkPSJNMTQuNTc4MTk5NCwyMS43NDk1OTM1IEMxNi4wMzUxMDk5LDE5Ljc3MjM1NzcgMTguMjIwNDc1OCwxOC43MzE3MDczIDIxLjEzNDI5NjksMTguNTIzNTc3MiBDMjYuNDQxNjE0LDE4LjEwNzMxNzEgMjkuNDU5NTAwMiwyMC42MDQ4NzggMzAuMTg3OTU1NSwyNi4wMTYyNjAyIEMzMy40MTM5NzE3LDQ3Ljc2NTg1MzcgMzYuOTUyMTgzMSw2Ni4xODUzNjU5IDQwLjY5ODUyNDYsODEuMjc0Nzk2NyBMNjMuNDg4NzY4NSwzNy44Nzk2NzQ4IEM2NS41NzAwNjkzLDMzLjkyNTIwMzMgNjguMTcxNjk1MywzMS44NDM5MDI0IDcxLjI5MzY0NjUsMzEuNjM1NzcyNCBDNzUuODcyNTA4MywzMS4zMjM1NzcyIDc4LjY4MjI2NDQsMzQuMjM3Mzk4NCA3OS44MjY5Nzk4LDQwLjM3NzIzNTggQzgyLjQyODYwNTksNTQuMjE3ODg2MiA4NS43NTg2ODcyLDY1Ljk3NzIzNTggODkuNzEzMTU4Nyw3NS45Njc0Nzk3IEM5Mi40MTg4NDk4LDQ5LjUzNDk1OTMgOTYuOTk3NzExNiwzMC40OTEwNTY5IDEwMy40NDk3NDQsMTguNzMxNzA3MyBDMTA1LjAxMDcyLDE1LjgxNzg4NjIgMTA3LjMwMDE1MSwxNC4zNjA5NzU2IDExMC4zMTgwMzcsMTQuMTUyODQ1NSBDMTEyLjcxMTUzMywxMy45NDQ3MTU0IDExNC44OTY4OTksMTQuNjczMTcwNyAxMTYuODc0MTM0LDE2LjIzNDE0NjMgQzExOC44NTEzNywxNy43OTUxMjIgMTE5Ljg5MjAyLDE5Ljc3MjM1NzcgMTIwLjEwMDE1MSwyMi4xNjU4NTM3IEMxMjAuMjA0MjE2LDI0LjAzOTAyNDQgMTE5Ljg5MjAyLDI1LjYgMTE5LjA1OTUsMjcuMTYwOTc1NiBDMTE1LjAwMDk2NCwzNC42NTM2NTg1IDExMS42NzA4ODIsNDcuMjQ1NTI4NSAxMDguOTY1MTkxLDY0LjcyODQ1NTMgQzEwNi4zNjM1NjUsODEuNjkxMDU2OSAxMDUuNDI2OTgsOTQuOTA3MzE3MSAxMDYuMDUxMzcsMTA0LjM3NzIzNiBDMTA2LjI1OTUsMTA2Ljk3ODg2MiAxMDUuODQzMjQsMTA5LjI2ODI5MyAxMDQuODAyNTksMTExLjI0NTUyOCBDMTAzLjU1MzgwOSwxMTMuNTM0OTU5IDEwMS42ODA2MzgsMTE0Ljc4Mzc0IDk5LjI4NzE0MjQsMTE0Ljk5MTg3IEM5Ni41ODE0NTE0LDExNS4yIDkzLjc3MTY5NTMsMTEzLjk1MTIyIDkxLjA2NjAwNDIsMTExLjE0MTQ2MyBDODEuMzg3OTU1NSwxMDEuMjU1Mjg1IDczLjY4NzE0MjQsODYuNDc4MDQ4OCA2OC4wNjc2MzAzLDY2LjgwOTc1NjEgQzYxLjMwMzQwMjYsODAuMTMwMDgxMyA1Ni4zMDgyODA3LDkwLjEyMDMyNTIgNTMuMDgyMjY0NCw5Ni43ODA0ODc4IEM0Ni45NDI0MjcsMTA4LjUzOTgzNyA0MS43MzkxNzUsMTE0LjU3NTYxIDM3LjM2ODQ0MzMsMTE0Ljg4NzgwNSBDMzQuNTU4Njg3MiwxMTUuMDk1OTM1IDMyLjE2NTE5MTIsMTEyLjcwMjQzOSAzMC4wODM4OTA0LDEwNy43MDczMTcgQzI0Ljc3NjU3MzMsOTQuMDc0Nzk2NyAxOS4wNTI5OTYxLDY3Ljc0NjM0MTUgMTIuOTEzMTU4NywyOC43MjE5NTEyIEMxMi40OTY4OTg1LDI2LjAxNjI2MDIgMTMuMTIxMjg4OCwyMy42MjI3NjQyIDE0LjU3ODE5OTQsMjEuNzQ5NTkzNSBaIE0yMzguMjEzOTcyLDM4LjA4NzgwNDkgQzIzNC40Njc2MywzMS41MzE3MDczIDIyOC45NTIxODMsMjcuNTc3MjM1OCAyMjEuNTYzNTY1LDI2LjAxNjI2MDIgQzIxOS41ODYzMjksMjUuNiAyMTcuNzEzMTU5LDI1LjM5MTg2OTkgMjE1Ljk0NDA1MywyNS4zOTE4Njk5IEMyMDUuOTUzODA5LDI1LjM5MTg2OTkgMTk3LjgzNjczNiwzMC41OTUxMjIgMTkxLjQ4ODc2OCw0MS4wMDE2MjYgQzE4Ni4wNzczODYsNDkuODQ3MTU0NSAxODMuMzcxNjk1LDU5LjYyOTI2ODMgMTgzLjM3MTY5NSw3MC4zNDc5Njc1IEMxODMuMzcxNjk1LDc4LjM2MDk3NTYgMTg1LjAzNjczNiw4NS4yMjkyNjgzIDE4OC4zNjY4MTcsOTAuOTUyODQ1NSBDMTkyLjExMzE1OSw5Ny41MDg5NDMxIDE5Ny42Mjg2MDYsMTAxLjQ2MzQxNSAyMDUuMDE3MjI0LDEwMy4wMjQzOSBDMjA2Ljk5NDQ2LDEwMy40NDA2NSAyMDguODY3NjMsMTAzLjY0ODc4IDIxMC42MzY3MzYsMTAzLjY0ODc4IEMyMjAuNzMxMDQ1LDEwMy42NDg3OCAyMjguODQ4MTE4LDk4LjQ0NTUyODUgMjM1LjA5MjAyLDg4LjAzOTAyNDQgQzI0MC41MDM0MDMsNzkuMDg5NDMwOSAyNDMuMjA5MDk0LDY5LjMwNzMxNzEgMjQzLjIwOTA5NCw1OC41ODg2MTc5IEMyNDMuMzEzMTU5LDUwLjQ3MTU0NDcgMjQxLjU0NDA1Myw0My43MDczMTcxIDIzOC4yMTM5NzIsMzguMDg3ODA0OSBaIE0yMjUuMTAxNzc3LDY2LjkxMzgyMTEgQzIyMy42NDQ4NjYsNzMuNzgyMTEzOCAyMjEuMDQzMjQsNzguODgxMzAwOCAyMTcuMTkyODM0LDgyLjMxNTQ0NzIgQzIxNC4xNzQ5NDcsODUuMDIxMTM4MiAyMTEuMzY1MTkxLDg2LjE2NTg1MzcgMjA4Ljc2MzU2NSw4NS42NDU1Mjg1IEMyMDYuMjY2MDA0LDg1LjEyNTIwMzMgMjA0LjE4NDcwMyw4Mi45Mzk4Mzc0IDIwMi42MjM3MjgsNzguODgxMzAwOCBDMjAxLjM3NDk0Nyw3NS42NTUyODQ2IDIwMC43NTA1NTcsNzIuNDI5MjY4MyAyMDAuNzUwNTU3LDY5LjQxMTM4MjEgQzIwMC43NTA1NTcsNjYuODA5NzU2MSAyMDAuOTU4Njg3LDY0LjIwODEzMDEgMjAxLjQ3OTAxMiw2MS44MTQ2MzQxIEMyMDIuNDE1NTk4LDU3LjU0Nzk2NzUgMjA0LjE4NDcwMyw1My4zODUzNjU5IDIwNi45OTQ0Niw0OS40MzA4OTQzIEMyMTAuNDI4NjA2LDQ0LjMzMTcwNzMgMjE0LjA3MDg4Miw0Mi4yNTA0MDY1IDIxNy44MTcyMjQsNDIuOTc4ODYxOCBDMjIwLjMxNDc4NSw0My40OTkxODcgMjIyLjM5NjA4Niw0NS42ODQ1NTI4IDIyMy45NTcwNjEsNDkuNzQzMDg5NCBDMjI1LjIwNTg0Miw1Mi45NjkxMDU3IDIyNS44MzAyMzIsNTYuMTk1MTIyIDIyNS44MzAyMzIsNTkuMjEzMDA4MSBDMjI1LjgzMDIzMiw2MS45MTg2OTkyIDIyNS42MjIxMDIsNjQuNTIwMzI1MiAyMjUuMTAxNzc3LDY2LjkxMzgyMTEgWiBNMTczLjA2OTI1NiwzOC4wODc4MDQ5IEMxNjkuMzIyOTE1LDMxLjUzMTcwNzMgMTYzLjcwMzQwMywyNy41NzcyMzU4IDE1Ni40MTg4NSwyNi4wMTYyNjAyIEMxNTQuNDQxNjE0LDI1LjYgMTUyLjU2ODQ0MywyNS4zOTE4Njk5IDE1MC43OTkzMzgsMjUuMzkxODY5OSBDMTQwLjgwOTA5NCwyNS4zOTE4Njk5IDEzMi42OTIwMiwzMC41OTUxMjIgMTI2LjM0NDA1Myw0MS4wMDE2MjYgQzEyMC45MzI2NzEsNDkuODQ3MTU0NSAxMTguMjI2OTgsNTkuNjI5MjY4MyAxMTguMjI2OTgsNzAuMzQ3OTY3NSBDMTE4LjIyNjk4LDc4LjM2MDk3NTYgMTE5Ljg5MjAyLDg1LjIyOTI2ODMgMTIzLjIyMjEwMiw5MC45NTI4NDU1IEMxMjYuOTY4NDQzLDk3LjUwODk0MzEgMTMyLjQ4Mzg5LDEwMS40NjM0MTUgMTM5Ljg3MjUwOCwxMDMuMDI0MzkgQzE0MS44NDk3NDQsMTAzLjQ0MDY1IDE0My43MjI5MTUsMTAzLjY0ODc4IDE0NS40OTIwMiwxMDMuNjQ4NzggQzE1NS41ODYzMjksMTAzLjY0ODc4IDE2My43MDM0MDMsOTguNDQ1NTI4NSAxNjkuOTQ3MzA1LDg4LjAzOTAyNDQgQzE3NS4zNTg2ODcsNzkuMDg5NDMwOSAxNzguMDY0Mzc4LDY5LjMwNzMxNzEgMTc4LjA2NDM3OCw1OC41ODg2MTc5IEMxNzguMDY0Mzc4LDUwLjQ3MTU0NDcgMTc2LjM5OTMzOCw0My43MDczMTcxIDE3My4wNjkyNTYsMzguMDg3ODA0OSBaIE0xNTkuODUyOTk2LDY2LjkxMzgyMTEgQzE1OC4zOTYwODYsNzMuNzgyMTEzOCAxNTUuNzk0NDYsNzguODgxMzAwOCAxNTEuOTQ0MDUzLDgyLjMxNTQ0NzIgQzE0OC45MjYxNjcsODUuMDIxMTM4MiAxNDYuMTE2NDExLDg2LjE2NTg1MzcgMTQzLjUxNDc4NSw4NS42NDU1Mjg1IEMxNDEuMDE3MjI0LDg1LjEyNTIwMzMgMTM4LjkzNTkyMyw4Mi45Mzk4Mzc0IDEzNy4zNzQ5NDcsNzguODgxMzAwOCBDMTM2LjEyNjE2Nyw3NS42NTUyODQ2IDEzNS41MDE3NzcsNzIuNDI5MjY4MyAxMzUuNTAxNzc3LDY5LjQxMTM4MjEgQzEzNS41MDE3NzcsNjYuODA5NzU2MSAxMzUuNzA5OTA3LDY0LjIwODEzMDEgMTM2LjIzMDIzMiw2MS44MTQ2MzQxIEMxMzcuMTY2ODE3LDU3LjU0Nzk2NzUgMTM4LjkzNTkyMyw1My4zODUzNjU5IDE0MS43NDU2NzksNDkuNDMwODk0MyBDMTQ1LjE3OTgyNSw0NC4zMzE3MDczIDE0OC44MjIxMDIsNDIuMjUwNDA2NSAxNTIuNTY4NDQzLDQyLjk3ODg2MTggQzE1NS4wNjYwMDQsNDMuNDk5MTg3IDE1Ny4xNDczMDUsNDUuNjg0NTUyOCAxNTguNzA4MjgxLDQ5Ljc0MzA4OTQgQzE1OS45NTcwNjEsNTIuOTY5MTA1NyAxNjAuNTgxNDUxLDU2LjE5NTEyMiAxNjAuNTgxNDUxLDU5LjIxMzAwODEgQzE2MC42ODU1MTYsNjEuOTE4Njk5MiAxNjAuMzczMzIxLDY0LjUyMDMyNTIgMTU5Ljg1Mjk5Niw2Ni45MTM4MjExIEwxNTkuODUyOTk2LDY2LjkxMzgyMTEgTDE1OS44NTI5OTYsNjYuOTEzODIxMSBaIiBmaWxsPSIjRkZGRkZGIi8+PC9nPjwvc3ZnPg==';
				break;
		}
		$img_class = 'wg-posttype-icon';
		$img_style = ' style="background-image:url(\'' . esc_attr( $menu_icon ) . '\')"';
		if ( 0 === strpos( $menu_icon, 'data:image/svg+xml;base64,' ) ) {
			$img_class .= ' svg';
		} elseif ( 0 === strpos( $menu_icon, 'dashicons-' ) ) {
			$img_class .= ' dashicons ' . sanitize_html_class( $menu_icon );
			$img_style = '';
		}

		return "<span class='{$img_class}'{$img_style}></span>";
	}
}

if ( ! function_exists( 'woowgallery_get_image_sizes_options' ) ) {
	/**
	 * Helper method for retrieving image sizes.
	 *
	 * @param bool   $wordpress_only             WordPress Only (excludes the default and woowgallery_random options).
	 *
	 * @return array Array of image size data.
	 * @global array $_wp_additional_image_sizes Array of registered image sizes.
	 */
	function woowgallery_get_image_sizes_options( $wordpress_only = false ) {
		global $_wp_additional_image_sizes;

		if ( ! $wordpress_only ) {
			$sizes = [
				[
					'value' => 'default',
					'name'  => __( 'Default', 'woowgallery' ),
				],
			];
		}

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
				$sizes[] = [
					'value' => $size,
					'name'  => ucwords( str_replace( [ '-', '_' ], ' ', $size ) ),
				];
			} else {
				$sizes[] = [
					'value'  => $size,
					'name'   => ucwords( str_replace( [ '-', '_' ], ' ', $size ) ) . ' (' . $width . ' &#215; ' . $height . ')',
					'width'  => $width,
					'height' => $height,
				];
			}
		}
		// Add Option for full image.
		$sizes[] = [
			'value' => 'full',
			'name'  => __( 'Original Image', 'woowgallery' ),
		];

		// Add Random option.
		if ( ! $wordpress_only ) {
			$sizes[] = [
				'value' => 'woowgallery_random',
				'name'  => __( 'Random', 'woowgallery' ),
			];
		}

		return apply_filters( 'woowgallery_image_sizes', $sizes );

	}
}
