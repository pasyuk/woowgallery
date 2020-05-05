<?php
/**
 * WP List Galleries Class.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

namespace WoowGallery\Admin;

use WoowGallery\Posttypes;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * Class Edit_Galleries
 */
class Edit_Galleries extends Edit_Tablelist {

	/**
	 * Edit_Galleries constructor.
	 */
	public function __construct() {
		parent::__construct( Posttypes::GALLERY_POSTTYPE );
	}

}
