<?php
/**
 * Posttypes class.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

namespace WoowGallery;

use WoowGallery\Admin\Settings;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * Class Posttypes
 */
class Posttypes {

	const GALLERY_POSTTYPE = 'woowgallery';
	const ALBUM_POSTTYPE   = 'woowgallery-album';
	const DYNAMIC_POSTTYPE = 'woowgallery-dynamic';

	/**
	 * Primary class constructor.
	 */
	public function __construct() {

		if ( woow_fs()->can_use_premium_code__premium_only() ) {
			add_filter( 'woowgallery_posttype_args', [ $this, 'standalone' ], 1, 2 );
		}

		$this->register_woowgallery_posttype();
		$this->register_woowgallery_dynamic_posttype();
		$this->register_woowgallery_album_posttype();

		do_action( 'register_woowgallery_posttypes' );

		if ( is_admin() ) {
			// Update post type messages.
			add_filter( 'post_updated_messages', [ $this, 'messages' ] );

			add_filter( 'custom_menu_order', [ $this, 'custom_menu_order' ] );
		}
	}

	/**
	 * Register WoowGallery CPT.
	 */
	public function register_woowgallery_posttype() {

		// Build the labels for the post type.
		$labels = [
			'name'                  => __( 'WoowGallery', 'woowgallery' ),
			'singular_name'         => __( 'WoowGallery', 'woowgallery' ),
			'menu_name'             => __( 'WoowGallery', 'woowgallery' ),
			'name_admin_bar'        => __( 'WoowGallery', 'woowgallery' ),
			'all_items'             => __( 'Galleries', 'woowgallery' ),
			'add_new_item'          => __( 'Add New Gallery', 'woowgallery' ),
			'add_new'               => __( 'Add New Gallery', 'woowgallery' ),
			'new_item'              => __( 'New Gallery', 'woowgallery' ),
			'edit_item'             => __( 'Edit WoowGallery', 'woowgallery' ),
			'update_item'           => __( 'Update Gallery', 'woowgallery' ),
			'view_item'             => __( 'View Gallery', 'woowgallery' ),
			'view_items'            => __( 'View Galleries', 'woowgallery' ),
			'search_items'          => __( 'Search Galleries', 'woowgallery' ),
			'not_found'             => __( 'No Galleries found.', 'woowgallery' ),
			'not_found_in_trash'    => __( 'No Galleries found in trash.', 'woowgallery' ),
			'featured_image'        => __( 'Cover Image', 'woowgallery' ),
			'set_featured_image'    => __( 'Set Cover Image', 'woowgallery' ),
			'remove_featured_image' => __( 'Remove Cover Image', 'woowgallery' ),
			'use_featured_image'    => __( 'Use as Cover Image', 'woowgallery' ),
			'insert_into_item'      => __( 'Insert into Gallery', 'woowgallery' ),
			'uploaded_to_this_item' => __( 'Uploaded to this Gallery', 'woowgallery' ),
			'items_list'            => __( 'Galleries list', 'woowgallery' ),
			'items_list_navigation' => __( 'Galleries list navigation', 'woowgallery' ),
			'filter_items_list'     => __( 'Filter Galleries list', 'woowgallery' ),
		];

		// Build out the post type arguments.
		$args = [
			'label'               => __( 'WoowGallery', 'woowgallery' ),
			'labels'              => $labels,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_icon'           => 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0Ij48aW1hZ2Ugd2lkdGg9IjI0IiBoZWlnaHQ9IjI0IiBocmVmPSJkYXRhOmltYWdlL3BuZztiYXNlNjQsaVZCT1J3MEtHZ29BQUFBTlNVaEVVZ0FBQUNBQUFBQWdDQVlBQUFCemVucjBBQUFBQkdkQlRVRUFBTEdQQy94aEJRQUFBQ0JqU0ZKTiBBQUI2SmdBQWdJUUFBUG9BQUFDQTZBQUFkVEFBQU9wZ0FBQTZtQUFBRjNDY3VsRThBQUFBQm1KTFIwUUEvd0QvQVArZ3ZhZVRBQUFBIENYQklXWE1BQUFzVEFBQUxFd0VBbXB3WUFBQUhmVWxFUVZSWXc2MldhNHhWNVJXR24vWHQyemx6aFFIR2NXQkFFUkJ2VVNsZ3JOUmIgck5yV0dORkcxTFpXcWo5cUZHMGFiSnZXUmh2VDFsaWpFZHVtb2x3c3NiYVdVQyt0VFZRRUVhTWlSbFRLYllBQmg3bUNjNTg1NSt6TCB0L3BqWm1ET25EUEVZdGZQdmIvOXJuZTk3MXByZjhJSnhLcDNaNThpSHIveFUrYm1NR3YvcVk3OGVQSDVPK3RQQkV2K2w4TXJ0NTR4IHg4Q2RxbHp2T0ZURmtlSjZnazIwUjVDWHJNYVBMWjVmdiszL1RtRDFPek5PSS9DV292cDlMekRwS0dleGRnU0lBYzgzeEpIMm82elcgV0I5ZGZPR3VBMSthd0xMM1psU1VHZk1qWTJTSjV6a1R3NXhGOVRoZ0J2ekFFT2JpTm90OTJQUk4rZFBpeXpabVQ0akFNKytmOFMzSCBzYjh1TDVsNGJod3F2WmxXalBHUTQzQldGR3NqS2tvbUV3UUJYWDFONzJHVCsyK2J2MmY5Rnlhd2FzdFpOWkE4TElaYmpSRkpZa05OICtUd0N0NUxHcm8yRWNRK09DUXFBRXB2RGR5dVlOdjRLNG1TQVE5MmJNVjZFalVsQVZsc05mL1dEK2ZzYWowdGd4ZWJUNXpvcHM5cjMgNWF4Y2RsaHV4V3BDZGRsNTFJMjdqTVA5SC9OWjU1c0FHSEd4R2dOd1N0V1ZWSmVkejhIT04yanIzWW9SRnhCRXdFOFpvbEFiRTJ1WCAzajUvOXd0RkNTeC9aL1lOZnNCeXg1R3FLQ3cwT3RHUXdLbmtuTm83S1BOcitiRHhjYnF5KzZoTW5jcTh1dnNZaUE2enJmbVA1T0l1IEhQRUx2bmRjd1JnbFNmajViWE4zL2Zib2M0QXRlMnZySE05OUxyYWxFN05oNklnVXVtUEVJZEdRUTExdmszTEhjK0VwdjZROE5aVjUgVTVkeW9QTTFQanIwSkVveVZIbGhkNkFLV2pyUWNEQTM1OVFMM00zYlh1cy9CR0FBc2lyWG5GUTVNTDJ1UEJlZlhENGw2NWkwRGt1YiBMNWZCTlNsMnRLMWh6K0cxbkZ2N1F4cTczdUxUNXFkeGpJOE13bzFLbmVDNXZwVzRMclA5UDZFMEgrNnVkZ0l1TzFyWVVLMExvMWdRIDZTeXBTaDN3WjFSTjFhcjBUS3dtcU5vQ1VNK1VzTC9qMzRSSkx3YzdYc2NZdjBBeHhhTEVWS1NtMjB6WHJHVExKdzNCa1o3VzlKQVkgbCtjUlFLbFVxNmdhWXBzeHFoK1lhZU1zczZ1dkluQW5FTnZzb0l5ajFBREJHRy9VS3lXeFdkSmVKYlZsVit2QmhrRGUzYjdaeTRZOSB4b2d6ZUZZb0h6N3RBb2lvZDZ3Q1FWWG96MzFFeW12aksxT3VvNjF2Z0lhT1Y0bVRBUnd6MkdBaUJpTk9udWVKUnJqR1kxYjE5V1Q3IFRtTDlCMnVsdFdzdm51T1BxQlZROWZNVVVQQkg5NzJJUnpadTVmUGVwNmd0RDdsaTVtUFVWaTRndGhsVUxRWUh3Y0hnb0NURWRvQ2EgOHZPNGRQcVRORGVWOGNLbUoyanYzamVVZkhRdkhSc1RkNUFRQVVWV3JPQ2dRRXYzbjZsSWZjcUYweDZnc2V0aVBtcGFScUlSSWdhciBNWTRKbURQbFRpcmRCVHkvNFNGMkhOcUE3Nlp4akVleDBORUtpRWdBWXk5NUkybDZzcCt3cytWV2FzcEx1UGJzZGN5dVhnUUkweWRjIHczVm5yNFBzbVR6MjRpM3NhbnFMd0MwWmMyV3JLb29FZVFSQWZTTWxpTGdveVJna2ZCTE5zYXYxSGpLNTladzMrUzZNT015Y2REME4gTFFmNS9hdmZveS9iZ2Vla2luNXYxU0lpcEx3eWpKR0NIZ2lzUnZoT0RTbTNibWlFQ29rSUJoR1AvVWNlb2lmN0FRQWR2VTJzMmJpVSB4Q1k0cG5BSnFWb1NHek91OUNTcUswOGRHdTBrWHdFRHZ0V0lYTnlJaU12NDlNVUViaTFXYzRBdElKRm9QeTNkendMd2Z2MC9PTkxUIFdDUzVFc1ZaU2xQak9XdnFwVlNrSjlMZWZZQmNtSUVSRnJpRDhwQWU5aXdUN2lkS09waFFlalZPYVFudHZXdUprZzZNcEVhUWNBamogTmdCNit0c0wvSTZURU5meFdYRG1MWlNuSjdCMTd5dDgzdHVJWXp4RUJFV1BnZzF2d2s0WndoQnhTV3dQYlQzUE14RFdNNjNxSjFTViBmaDNWRUMyeW5rZm10cG9ReGhtbTE4emh4Z1VQb2xoZS8zZzVIYjFOUnlkaUtFOXZuZ0lpOHJycnlhSmpmMEdEaUtGellDTjl1ZTNVIGpiK2I2dkp2ODFuSG8vVG5kaU5pR0o0YWF5MmdoSEdHeXBKcXJyM2dQa3FEY2J5ODVWR2FPM2JqdTJuRU9NZjRHaEFybS9JVXNLSXYgT2s1aGNVWUNZdHZOL2lNUDBEbXdnVE5xVmpCbC9KMElMb2tkQUNCS3NpUTJZZjZzaGR5M2NCMTltUTVXdkxHRXRxNTkrRzY2RU5NUiB4TXJhUEFYY3hMeWR6ZWhoMTJOU0hCWHIvQlN0M1graFA3ZWQyVFZQVVYxK0E1MERHd0JsNnFSeldMcHdMVE5xNXJGcS9iMXMzZnNLIHZsdFNkQWtaQTNHa3ZlcXl1Y0RCdDNiWExnb0NXWUZJYWJFTHlhREhPWHlubWpOUFhrbEYrb0tqenp2N1duajg1WnM0MEw2dGFOV0QgbFFOQ29vbmMrOHpkclgvSXN3RGdrdE9iLzVZTHpUZVRSUGVrU3dSamlvQklRSmdjWmtmTFl2cHkyd2U3S1hPRVphOThaK3prQXE0diBvTFRGa2R3ME1ua2VBWUJMWmpWdTZ1dU52NWJMOG9pSTlLVFNNdHkxSTBqNGhIRWJEVWNlQkN6LzJ2b0U5UzFiaWlaM1BFR0V4Q2FzIEladGN2T3FlMXJXanp4U3NycXZPYldzSGZ2ck8zcm8xWWM3K3doaHVkRnd4WWU2WUxTSSttYWdCSmVUUTV6dHhuSHkvalROSTNGcDkgRXlzUFBiMmtkU05qaEJucnhVVXpHcmRmZEZyVHpWR2kzNGhqM2V3SGd1c2VrME1HcjVNWXlSOHgxeGV3dWlPMjlydmRFOXV1Zk9ZNCB5WTlMNEtndE01dGZDenBxTGc4amU3dXExcWZTd3VpUmxXR2ZvZFZHL0V6NndxK3V2S3Y5dWIvZk9NYWY3WGdXRkl1NWN6K01nSlZ2IDdKejhVcUFzTWVnZG5pK1RBY3lnS3IwMjFtZE43UHh1K2IzTm4zMFJ6QzhWbStyTEpuM1NzdWh1YTdQMnI1dnVYM25MSThITUU4WDYgTDY1UFVKdlJ4VGhlQUFBQUpYUkZXSFJrWVhSbE9tTnlaV0YwWlFBeU1ERTVMVEV4TFRBMVZEQTNPalExT2pNd0xUQTNPakF3OGt3WiAzZ0FBQUNWMFJWaDBaR0YwWlRwdGIyUnBabmtBTWpBeE9TMHhNUzB3TlZRd056bzBOVG96TUMwd056b3dNSU1Sb1dJQUFBQUFTVVZPIFJLNUNZSUk9Ii8+PC9zdmc+',
			'menu_position'       => 10,
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => false,
			'can_export'          => true,
			'supports'            => [ 'title', 'excerpt', 'thumbnail', 'author', 'custom-fields' ],
			'hierarchical'        => false,
			'public'              => false,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'query_var'           => false,
			'rewrite'             => false,
			'show_in_rest'        => true,
		];

		// Define custom capaibilities.
		$args['capabilities'] = $this->capabilities();

		// Filter arguments.
		$args = apply_filters( 'woowgallery_posttype_args', $args, self::GALLERY_POSTTYPE );

		// Register the post type with WordPress.
		register_post_type( self::GALLERY_POSTTYPE, $args );
	}

	/**
	 * Custom capabilities for WoowGallery CPT.
	 *
	 * @return array.
	 */
	private function capabilities() {
		return [
			// Meta caps.
			'edit_post'              => 'edit_woowgallery_post',
			'read_post'              => 'read_woowgallery_post',
			'delete_post'            => 'delete_woowgallery_post',

			// Primitive caps outside map_meta_cap().
			'edit_posts'             => 'edit_woowgallery_posts',
			'edit_others_posts'      => 'edit_others_woowgallery_posts',
			'publish_posts'          => 'publish_woowgallery_posts',
			'read_private_posts'     => 'read_private_woowgallery_posts',

			// Primitive caps used within map_meta_cap().
			'read'                   => 'read_woowgallery',
			'delete_posts'           => 'delete_woowgallery_posts',
			'delete_private_posts'   => 'delete_private_woowgallery_posts',
			'delete_published_posts' => 'delete_published_woowgallery_posts',
			'delete_others_posts'    => 'delete_others_woowgallery_posts',
			'edit_private_posts'     => 'edit_private_woowgallery_posts',
			'edit_published_posts'   => 'edit_published_woowgallery_posts',
			'create_posts'           => 'create_woowgallery_posts',
		];
	}

	/**
	 * Register WoowGallery CPT.
	 */
	public function register_woowgallery_dynamic_posttype() {

		// Build the labels for the post type.
		$labels = [
			'name'                  => __( 'WoowGallery Dynamic', 'woowgallery' ),
			'singular_name'         => __( 'WoowGallery Dynamic', 'woowgallery' ),
			'menu_name'             => __( 'WoowGallery Dynamic', 'woowgallery' ),
			'name_admin_bar'        => __( 'WoowGallery Dynamic', 'woowgallery' ),
			'all_items'             => __( 'Dynamic Galleries', 'woowgallery' ),
			'add_new_item'          => __( 'Add New Dynamic Gallery', 'woowgallery' ),
			'add_new'               => __( 'Add New Dynamic Gallery', 'woowgallery' ),
			'new_item'              => __( 'New Dynamic Gallery', 'woowgallery' ),
			'edit_item'             => __( 'Edit WoowGallery', 'woowgallery' ),
			'update_item'           => __( 'Update Dynamic Gallery', 'woowgallery' ),
			'view_item'             => __( 'View Dynamic Gallery', 'woowgallery' ),
			'view_items'            => __( 'View Dynamic Galleries', 'woowgallery' ),
			'search_items'          => __( 'Search Dynamic Galleries', 'woowgallery' ),
			'not_found'             => __( 'No Dynamic Galleries found.', 'woowgallery' ),
			'not_found_in_trash'    => __( 'No Dynamic Galleries found in trash.', 'woowgallery' ),
			'featured_image'        => __( 'Cover Image', 'woowgallery' ),
			'set_featured_image'    => __( 'Set Cover Image', 'woowgallery' ),
			'remove_featured_image' => __( 'Remove Cover Image', 'woowgallery' ),
			'use_featured_image'    => __( 'Use as Cover Image', 'woowgallery' ),
			'items_list'            => __( 'Dynamic Galleries list', 'woowgallery' ),
			'items_list_navigation' => __( 'Dynamic Galleries list navigation', 'woowgallery' ),
			'filter_items_list'     => __( 'Filter Dynamic Galleries list', 'woowgallery' ),
		];

		// Build out the post type arguments.
		$args = [
			'label'               => __( 'WoowGallery Dynamic', 'woowgallery' ),
			'labels'              => $labels,
			'show_ui'             => true,
			'show_in_menu'        => 'edit.php?post_type=' . self::GALLERY_POSTTYPE,
			'menu_icon'           => 'none',
			'menu_position'       => 10,
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => false,
			'can_export'          => true,
			'supports'            => [ 'title', 'excerpt', 'thumbnail', 'author', 'custom-fields' ],
			'hierarchical'        => false,
			'public'              => false,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'query_var'           => false,
			'rewrite'             => false,
			'show_in_rest'        => true,
		];

		// Define custom capaibilities.
		$args['capabilities'] = $this->capabilities();

		// Filter arguments.
		$args = apply_filters( 'woowgallery_posttype_args', $args, self::DYNAMIC_POSTTYPE );

		// Register the post type with WordPress.
		register_post_type( self::DYNAMIC_POSTTYPE, $args );

		// "Add New" submenu.
		add_action( 'admin_menu', [ $this, 'dynamic_new_submenu' ], 11 );
	}

	/**
	 * Register WoowGallery Album CPT.
	 */
	public function register_woowgallery_album_posttype() {

		// Build the labels for the post type.
		$labels = [
			'name'                  => __( 'WoowGallery Album', 'woowgallery' ),
			'singular_name'         => __( 'WoowGallery Album', 'woowgallery' ),
			'menu_name'             => __( 'WoowGallery Album', 'woowgallery' ),
			'name_admin_bar'        => __( 'WoowGallery Album', 'woowgallery' ),
			'all_items'             => __( 'Albums', 'woowgallery' ),
			'add_new_item'          => __( 'Add New Album', 'woowgallery' ),
			'add_new'               => __( 'Add New Album', 'woowgallery' ),
			'new_item'              => __( 'New Album', 'woowgallery' ),
			'edit_item'             => __( 'Edit WoowGallery Album', 'woowgallery' ),
			'update_item'           => __( 'Update Album', 'woowgallery' ),
			'view_item'             => __( 'View Album', 'woowgallery' ),
			'view_items'            => __( 'View Albums', 'woowgallery' ),
			'search_items'          => __( 'Search Albums', 'woowgallery' ),
			'not_found'             => __( 'No Albums found.', 'woowgallery' ),
			'not_found_in_trash'    => __( 'No Albums found in trash.', 'woowgallery' ),
			'featured_image'        => __( 'Cover Image', 'woowgallery' ),
			'set_featured_image'    => __( 'Set Cover Image', 'woowgallery' ),
			'remove_featured_image' => __( 'Remove Cover Image', 'woowgallery' ),
			'use_featured_image'    => __( 'Use as Cover Image', 'woowgallery' ),
			'insert_into_item'      => __( 'Insert into Album', 'woowgallery' ),
			'uploaded_to_this_item' => __( 'Uploaded to this Album', 'woowgallery' ),
			'items_list'            => __( 'Albums list', 'woowgallery' ),
			'items_list_navigation' => __( 'Albums list navigation', 'woowgallery' ),
			'filter_items_list'     => __( 'Filter Albums list', 'woowgallery' ),
		];

		// Build out the post type arguments.
		$args = [
			'label'               => __( 'WoowGallery Album', 'woowgallery' ),
			'labels'              => $labels,
			'show_ui'             => true,
			'show_in_menu'        => 'edit.php?post_type=' . self::GALLERY_POSTTYPE,
			'menu_icon'           => 'none',
			'menu_position'       => 10,
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => false,
			'can_export'          => true,
			'supports'            => [ 'title', 'excerpt', 'thumbnail', 'author', 'custom-fields' ],
			'hierarchical'        => false,
			'public'              => false,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'query_var'           => false,
			'rewrite'             => false,
			'show_in_rest'        => true,
		];

		// Define custom capaibilities.
		$args['capabilities'] = $this->capabilities();

		// Filter arguments.
		$args = apply_filters( 'woowgallery_posttype_args', $args, self::ALBUM_POSTTYPE );

		// Register the post type with WordPress.
		register_post_type( self::ALBUM_POSTTYPE, $args );

		// "Add New" submenu.
		add_action( 'admin_menu', [ $this, 'album_new_submenu' ], 11 );
	}

	/**
	 * Add "Add New Album" submenu.
	 */
	public function dynamic_new_submenu() {
		$obj = get_post_type_object( self::DYNAMIC_POSTTYPE );
		if ( ! $obj ) {
			return;
		}
		add_submenu_page( $obj->show_in_menu, $obj->labels->add_new_item, $obj->labels->add_new, $obj->cap->edit_post, 'post-new.php?post_type=' . self::DYNAMIC_POSTTYPE );
	}

	/**
	 * Add "Add New Album" submenu.
	 */
	public function album_new_submenu() {
		$obj = get_post_type_object( self::ALBUM_POSTTYPE );
		if ( ! $obj ) {
			return;
		}
		add_submenu_page( $obj->show_in_menu, $obj->labels->add_new_item, $obj->labels->add_new, $obj->cap->edit_post, 'post-new.php?post_type=' . self::ALBUM_POSTTYPE );
	}

	/**
	 * Contextualizes the post updated messages.
	 *
	 * @param array $messages Array of default post updated messages.
	 *
	 * @return array $messages Amended array of post updated messages.
	 */
	public function messages( $messages ) {

		global $post;

		$revision = woowgallery_GET( 'revision' );
		// Contextualize the messages for WoowGallery Galleries.
		$woowgallery_messages = [
			0  => '',
			1  => __( 'Gallery updated.', 'woowgallery' ),
			2  => __( 'Gallery custom field updated.', 'woowgallery' ),
			3  => __( 'Gallery custom field deleted.', 'woowgallery' ),
			4  => __( 'Gallery updated.', 'woowgallery' ),
			/* translators: %s: revision title */
			5  => $revision ? sprintf( __( 'Gallery restored to revision from %s.', 'woowgallery' ), wp_post_revision_title( (int) $revision, false ) ) : false,
			6  => __( 'Gallery published.', 'woowgallery' ),
			7  => __( 'Gallery saved.', 'woowgallery' ),
			8  => __( 'Gallery submitted.', 'woowgallery' ),
			/* translators: %s: scheduled date */
			9  => sprintf( __( 'WoowGallery scheduled for: <strong>%1$s</strong>.', 'woowgallery' ), date_i18n( __( 'M j, Y @ G:i', 'woowgallery' ), strtotime( $post->post_date ) ) ),
			10 => __( 'Gallery draft updated.', 'woowgallery' ),
		];

		$messages[ self::GALLERY_POSTTYPE ] = $woowgallery_messages;
		$messages[ self::DYNAMIC_POSTTYPE ] = $woowgallery_messages;
		$messages[ self::ALBUM_POSTTYPE ]   = $woowgallery_messages;

		return $messages;
	}

	/**
	 * Reorder WoowGallery submenus.
	 *
	 * @param bool $custom Whether custom ordering is enabled.
	 *
	 * @return bool
	 */
	public function custom_menu_order( $custom ) {
		// Get submenu key location based on slug.
		global $submenu;

		if ( empty( $submenu[ 'edit.php?post_type=' . self::GALLERY_POSTTYPE ] ) ) {
			return $custom;
		}

		$wg_submenu    = $submenu[ 'edit.php?post_type=' . self::GALLERY_POSTTYPE ];
		$wg_menu_order = [];
		$wg_menu_last  = [];
		foreach ( $wg_submenu as $details ) {
			$url = (array) explode( '?', $details[2], 2 );
			if ( 'edit.php' === $url[0] ) {
				$details[0] = '<span style="margin-right: 10px;">' . $details[0] . '</span></a></li>';
				$details[0] .= '<li style="position:absolute; right:0; transform: translateY(-100%);" class="wg-cpt-add-new"><a style="padding-left:5px; padding-right:3px;" href="post-new.php?' . $url[1] . '" title="' . esc_attr__( 'Add New', 'woowgallery' ) . '"><span style="transform: translateY(2px);" class="dashicons dashicons-plus"></span>';

				$wg_menu_order[ $url[1] ][] = $details;
			} elseif ( 'post-new.php' !== $url[0] ) {
				$wg_menu_last[] = $details;
			}
		}
		$wg_menu_order['last'] = $wg_menu_last;
		$wg_menu_order         = call_user_func_array( 'array_merge', array_values( $wg_menu_order ) );

		$submenu[ 'edit.php?post_type=' . self::GALLERY_POSTTYPE ] = apply_filters( 'woowgallery_submenu_order', $wg_menu_order );

		// Return the new submenu order.
		return $custom;
	}

	/**
	 * Filter post type arguments.
	 *
	 * @param array  $args     Post type arguments.
	 * @param string $posttype Post type name.
	 *
	 * @return array
	 */
	public function standalone( $args, $posttype ) {
		if ( woow_fs()->can_use_premium_code__premium_only() ) {
			// Check if standalone is enabled.
			$standalone = Settings::get_settings( 'standalone_' . $posttype );
			if ( ! empty( $standalone ) ) {
				// Get slug.
				$slug = Settings::get_settings( 'permalink_base_' . $posttype, $posttype );

				// Change the default post type args so that it can be publicly accessible.
				$args['rewrite']             = [
					'with_front' => false,
					'slug'       => $slug,
				];
				$args['query_var']           = true;
				$args['publicly_queryable']  = true;
				$args['exclude_from_search'] = false;
				$args['has_archive']         = true;
				$args['public']              = true;
				$args['show_in_nav_menus']   = true;
				$args['supports'][]          = 'slug';
				$args['supports'][]          = 'page-attributes';
			}
		}

		return $args;
	}
}
