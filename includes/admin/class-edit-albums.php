<?php
/**
 * WP List Albums Class.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

namespace WoowGallery\Admin;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

use WoowGallery\Posttypes;

/**
 * Class Edit_Albums
 */
class Edit_Albums extends Edit_Tablelist {

	/**
	 * Edit_Galleries constructor.
	 */
	public function __construct() {
		parent::__construct( Posttypes::ALBUM_POSTTYPE );
	}

}
