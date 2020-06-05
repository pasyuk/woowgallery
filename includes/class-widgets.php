<?php
/**
 * Widget class.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

namespace WoowGallery;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

use WP_Widget;

/**
 * Class Widgets
 */
class Widgets extends WP_Widget {

	/**
	 * Constructor. Sets up and creates the widget with appropriate settings.
	 */
	public function __construct() {

		// Widget Name.
		$widget_name = __( 'WoowGallery', 'woowgallery' );
		$widget_name = apply_filters( 'woowgallery_widget_name', $widget_name );

		// Options.
		$widget_options = [
			'classname'   => 'woowgallery',
			'description' => __( 'Place a WoowGallery into a widgetized area.', 'woowgallery' ),
		];
		$widget_options = apply_filters( 'woowgallery_widget_options', $widget_options );

		// Control Options.
		$control_options = [
			'id_base' => 'woowgallery',
			'height'  => 350,
			'width'   => 225,
		];
		$control_options = apply_filters( 'woowgallery_widget_control_options', $control_options );

		// Init.
		parent::__construct( 'woowgallery', $widget_name, $widget_options, $control_options );

	}

	/**
	 * Outputs the widget form where the user can specify settings.
	 *
	 * @param array $instance The input settings for the current widget instance.
	 *
	 * @return void
	 */
	public function form( $instance ) {

		// Get all avilable publish galleries and widget properties.
		$all_galleries = get_posts(
			[
				'post_type'      => [ Posttypes::GALLERY_POSTTYPE, Posttypes::DYNAMIC_POSTTYPE, Posttypes::ALBUM_POSTTYPE ],
				'post_status'    => 'publish',
				'posts_per_page' => - 1,
			]
		);
		$galleries     = [];
		foreach ( $all_galleries as $_gall ) {
			$galleries[ $_gall->post_type ][] = $_gall;
		}

		$title    = isset( $instance['title'] ) ? $instance['title'] : '';
		$_gallery = isset( $instance['woowgallery'] ) ? $instance['woowgallery'] : '';

		do_action( 'woowgallery_widget_before_form', $instance );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'woowgallery' ); ?></label>
			<input id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" style="width: 100%;"/>
		</p>
		<?php do_action( 'woowgallery_widget_middle_form', $instance ); ?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'woowgallery' ) ); ?>"><?php esc_html_e( 'WoowGallery', 'woowgallery' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'woowgallery' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'woowgallery' ) ); ?>" style="width: 100%;">
				<option value=""<?php selected( '', $_gallery ); ?>><?php esc_attr_e( 'Choose WoowGallery', 'woowgallery' ); ?></option>
				<?php
				if ( ! empty( $galleries[ Posttypes::GALLERY_POSTTYPE ] ) ) {
					echo '<optgroup label="' . esc_attr__( 'Galleries', 'woowgallery' ) . '">';
					foreach ( $galleries[ Posttypes::GALLERY_POSTTYPE ] as $gallery ) {
						$value = $gallery->ID . ':' . $gallery->post_type;
						?>
						<option value="<?php echo esc_attr( $value ); ?>"<?php selected( $value, $_gallery ); ?>><?php echo esc_attr( $gallery->post_title ); ?></option>
						<?php
					}
					echo '</optgroup>';
				}
				if ( ! empty( $galleries[ Posttypes::DYNAMIC_POSTTYPE ] ) ) {
					echo '<optgroup label="' . esc_attr__( 'Dynamic Galleries', 'woowgallery' ) . '">';
					foreach ( $galleries[ Posttypes::DYNAMIC_POSTTYPE ] as $gallery ) {
						$value = $gallery->ID . ':' . $gallery->post_type;
						?>
						<option value="<?php echo esc_attr( $value ); ?>"<?php selected( $value, $_gallery ); ?>><?php echo esc_attr( $gallery->post_title ); ?></option>
						<?php
					}
					echo '</optgroup>';
				}
				if ( ! empty( $galleries[ Posttypes::ALBUM_POSTTYPE ] ) ) {
					echo '<optgroup label="' . esc_attr__( 'Albums', 'woowgallery' ) . '">';
					foreach ( $galleries[ Posttypes::ALBUM_POSTTYPE ] as $gallery ) {
						$value = $gallery->ID . ':' . $gallery->post_type;
						?>
						<option value="<?php echo esc_attr( $value ); ?>"<?php selected( $value, $_gallery ); ?>><?php echo esc_attr( $gallery->post_title ); ?></option>
						<?php
					}
					echo '</optgroup>';
				}
				?>
			</select>
		</p>
		<?php
		do_action( 'woowgallery_widget_after_form', $instance );

	}

	/**
	 * Sanitizes and updates the widget.
	 *
	 * @param array $new_instance The new input settings for the current widget instance.
	 * @param array $old_instance The old input settings for the current widget instance.
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {

		// Set $instance to the old instance in case no new settings have been updated for a particular field.
		$instance = $old_instance;

		// Sanitize user inputs.
		$instance['title']       = trim( $new_instance['title'] );
		$instance['woowgallery'] = $new_instance['woowgallery'];

		return apply_filters( 'woowgallery_widget_update_instance', $instance, $new_instance );

	}

	/**
	 * Outputs the widget within the widgetized area.
	 *
	 * @param array $args     The default widget arguments.
	 * @param array $instance The input settings for the current widget instance.
	 */
	public function widget( $args, $instance ) {

		/**
		 * Extract arguments into variables.
		 *
		 * @var $before_widget
		 * @var $after_widget
		 * @var $before_title
		 * @var $after_title
		 */
		extract( $args ); // @codingStandardsIgnoreLine

		$title        = false;
		$gallery_id   = false;
		$gallery_type = false;

		if ( isset( $instance['title'] ) ) {
			$title = apply_filters( 'widget_title', $instance['title'] );
		}
		if ( isset( $instance['woowgallery'] ) ) {
			$id_type      = explode( ':', $instance['woowgallery'] );
			$gallery_id   = (int) $id_type[0];
			$gallery_type = isset( $id_type[1] ) ? $id_type[1] : Posttypes::GALLERY_POSTTYPE;
		}

		if ( ! $gallery_id ) {
			return;
		}

		do_action( 'woowgallery_widget_before_output', $args, $instance );

		echo $before_widget; // @codingStandardsIgnoreLine

		do_action( 'woowgallery_widget_before_title', $args, $instance );

		// If a title exists, output it.
		if ( $title ) {
			echo $before_title . $title . $after_title; // @codingStandardsIgnoreLine
		}

		do_action( 'woowgallery_widget_before_gallery', $args, $instance );

		// If a gallery has been selected, output it.
		if ( $gallery_id ) {
			woowgallery( $gallery_id, $gallery_type );
		}

		do_action( 'woowgallery_widget_after_gallery', $args, $instance );

		echo $after_widget; // @codingStandardsIgnoreLine

		do_action( 'woowgallery_widget_after_output', $args, $instance );

	}
}
