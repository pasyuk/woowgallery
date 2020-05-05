<?php
/**
 * WP List Albums Class.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

namespace WoowGallery\Admin;

use WoowGallery\Posttypes;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

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
