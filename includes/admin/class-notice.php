<?php
/**
 * Notices admin class.
 *
 * Handles retrieving whether a particular notice has been dismissed or not,
 * as well as marking a notice as dismissed.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

namespace WoowGallery\Admin;

/**
 * Class Notice
 */
class Notice {

	const META_KEY = 'woowgallery_notices';

	const TYPE_ERROR   = 'error';
	const TYPE_INFO    = 'info';
	const TYPE_SUCCESS = 'success';
	const TYPE_WARNING = 'warning';

	/**
	 * Add Message.
	 *
	 * @param string $message     Message to show.
	 * @param string $type        Notice type.
	 * @param string $title       Title.
	 * @param string $uid         Message unique ID.
	 * @param string $button_text Button Text (optional).
	 * @param string $button_url  Button URL (optional).
	 */
	public static function add_message( $message, $type = self::TYPE_ERROR, $title = '', $uid = '', $button_text = '', $button_url = '' ) {

		// Nothing to do.
		if ( empty( $message ) ) {
			return;
		}

		// Check if there are already any messages in a queue.
		$messages = get_option( self::META_KEY, [] );

		// Add the new value.
		if ( $uid ) {
			if ( isset( $messages['queue'][ $uid ] ) ) {
				return;
			}
			$messages['queue'][ $uid ] = compact( 'message', 'type', 'title', 'button_text', 'button_url' );
		} else {
			$messages['queue'][] = compact( 'message', 'type', 'title', 'button_text', 'button_url' );
		}

		// Save message queue.
		update_option( self::META_KEY, $messages );
	}

	/**
	 * Display notices.
	 */
	public static function show_all() {

		// Check if there are any messages to display.
		$messages = get_option( self::META_KEY, [] );

		if ( empty( $messages['queue'] ) ) {
			return;
		}

		// Display messages.
		foreach ( $messages['queue'] as $notice ) {
			self::display_notice( $notice['message'], $notice['type'], $notice['title'], '', $notice['button_text'], $notice['button_url'] );
		}

		$messages['queue'] = [];

		// Save cleared message queue.
		update_option( self::META_KEY, $messages );
	}

	/**
	 * Displays an inline notice with some WoowGallery styling.
	 *
	 * @param string $message     Message.
	 * @param string $type        Message Type (updated|warning|error) - green, yellow/orange and red respectively.
	 * @param string $title       Title.
	 * @param string $notice_name Programmatic Notice Name.
	 * @param string $button_text Button Text (optional).
	 * @param string $button_url  Button URL (optional).
	 */
	public static function display_notice( $message, $type = self::TYPE_ERROR, $title = '', $notice_name = '', $button_text = '', $button_url = '' ) {

		if ( empty( $message ) ) {
			return;
		}

		// Check if the notice is dismissible, and if so has been dismissed.
		if ( $notice_name && self::is_dismissed( $notice_name ) ) {
			// Nothing to show here, return!
			return;
		}

		// Detect css class.
		switch ( $type ) {
			case self::TYPE_ERROR:
			case self::TYPE_SUCCESS:
			case self::TYPE_WARNING:
				$class = $type;
				break;
			default:
				$class = self::TYPE_INFO;
		}
		$class = 'notice notice-' . $class;

		if ( $notice_name ) {
			$class .= ' is-dismissable';
		}

		// Display inline notice.
		?>
		<div class="woowgallery-notice <?php echo esc_attr( $class ); ?>" data-notice="<?php echo esc_attr( $notice_name ); ?>">
			<?php
			// Title.
			if ( ! empty( $title ) ) {
				?>
				<p class="woowgallery-intro"><?php echo esc_html( $title ); ?></p>
				<?php
			}

			// Message.
			if ( ! empty( $message ) ) {
				?>
				<p><?php echo wp_kses_post( $message ); ?></p>
				<?php
			}

			// Button.
			if ( ! empty( $button_text ) && ! empty( $button_url ) ) {
				?>
				<a href="<?php echo esc_url( $button_url ); ?>" target="_blank" class="button button-primary"><?php echo esc_html( $button_text ); ?></a>
				<?php
			}

			// Dismiss Button.
			if ( $notice_name ) {
				?>
				<button type="button" class="notice-dismiss">
					<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice', 'woowgallery' ); ?></span>
				</button>
				<?php
			}
			?>
		</div>
		<?php
	}

	/**
	 * Checks if a given notice has been dismissed or not
	 *
	 * @param string $notice_name Programmatic Notice Name.
	 *
	 * @return bool Notice Dismissed
	 */
	protected static function is_dismissed( $notice_name ) {

		$messages = get_option( self::META_KEY, [] );
		if ( ! isset( $messages['dismissed'][ $notice_name ] ) ) {
			return false;
		}

		return true;

	}

	/**
	 * Marks the given notice as dismissed
	 *
	 * @param string $notice_name Programmatic Notice Name.
	 */
	public static function dismiss( $notice_name ) {

		$messages = get_option( self::META_KEY, [] );

		$messages['dismissed'][ $notice_name ] = time();
		update_option( 'woowgallery_notices', $messages );

	}

	/**
	 * Marks a notice as not dismissed
	 *
	 * @param string $notice_name Programmatic Notice Name.
	 */
	public static function undismiss( $notice_name ) {

		$messages = get_option( self::META_KEY, [] );
		if ( empty( $messages['dismissed'] ) ) {
			return;
		}
		unset( $messages['dismissed'][ $notice_name ] );
		update_option( 'woowgallery_notices', $messages );

	}

}
