<?php
/**
 * Cron class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Schedules and unschedules cron events.
 *
 * @package   Page_Generator_Pro
 * @author    WP Zinc
 * @version   2.6.1
 */
class Page_Generator_Pro_Cron {

	/**
	 * Holds the base class object.
	 *
	 * @since   2.6.1
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   2.6.1
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

	}

	/**
	 * Schedules the regeneration event in the WordPress CRON on a daily basis
	 *
	 * @since   2.7.9
	 */
	public function schedule_regeneration_event() {

		// Bail if the scheduled event already exists.
		$scheduled_event = $this->get_regeneration_event();
		if ( $scheduled_event !== false ) {
			return;
		}

		// Schedule event.
		$scheduled_date_time = gmdate( 'Y-m-d H', strtotime( '+1 hour' ) ) . ':00:00';
		wp_schedule_event( strtotime( $scheduled_date_time ), 'hourly', 'page_generator_pro_regeneration_cron' );

	}

	/**
	 * Unschedules the regeneration event in the WordPress CRON.
	 *
	 * @since   2.7.9
	 */
	public function unschedule_regeneration_event() {

		wp_clear_scheduled_hook( 'page_generator_pro_regeneration_cron' );

	}

	/**
	 * Reschedules the regeneration event in the WordPress CRON, by unscheduling
	 * and scheduling it.
	 *
	 * @since   2.7.9
	 */
	public function reschedule_regeneration_event() {

		$this->unschedule_regeneration_event();
		$this->schedule_regeneration_event();

	}

	/**
	 * Returns the scheduled regeneration event, if it exists
	 *
	 * @since   2.7.9
	 */
	public function get_regeneration_event() {

		return wp_get_schedule( 'page_generator_pro_regeneration_cron' );

	}

	/**
	 * Returns the scheduled regeneration event's next date and time to run, if it exists
	 *
	 * @since   2.7.9
	 *
	 * @param   mixed $format     Format Timestamp (false | php date() compat. string).
	 */
	public function get_regeneration_event_next_scheduled( $format = false ) {

		// Get timestamp for when the event will next run.
		$scheduled = wp_next_scheduled( 'page_generator_pro_regeneration_cron' );

		// If no timestamp or we're not formatting the result, return it now.
		if ( ! $scheduled || ! $format ) {
			return $scheduled;
		}

		// Return formatted date/time.
		return gmdate( $format, $scheduled );

	}

	/**
	 * Runs the generate CRON event.
	 *
	 * @since   2.6.1
	 *
	 * @param   int    $group_id   Group ID.
	 * @param   string $type       Content Type.
	 */
	public function generate( $group_id, $type = 'content' ) {

		$this->base->get_class( 'generate' )->generate(
			$group_id,
			$type,
			0,
			0,
			false,
			'cron'
		);

	}

	/**
	 * Schedules the log cleanup event in the WordPress CRON on a daily basis
	 *
	 * @since   2.6.1
	 */
	public function schedule_log_cleanup_event() {

		// Bail if logging is disabled.
		if ( ! $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-generate', 'log_enabled', '0' ) ) {
			return;
		}

		// Bail if the preserve logs settings is indefinite.
		if ( ! $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-generate', 'log_preserve_days', '7' ) ) {
			return;
		}

		// Bail if the scheduled event already exists.
		$scheduled_event = $this->get_log_cleanup_event();
		if ( $scheduled_event !== false ) {
			return;
		}

		// Schedule event.
		$scheduled_date_time = gmdate( 'Y-m-d', strtotime( '+1 day' ) ) . ' 00:00:00';
		wp_schedule_event( strtotime( $scheduled_date_time ), 'daily', 'page_generator_pro_log_cleanup_cron' );

	}

	/**
	 * Unschedules the log cleanup event in the WordPress CRON.
	 *
	 * @since   2.6.1
	 */
	public function unschedule_log_cleanup_event() {

		wp_clear_scheduled_hook( 'page_generator_pro_log_cleanup_cron' );

	}

	/**
	 * Reschedules the log cleanup event in the WordPress CRON, by unscheduling
	 * and scheduling it.
	 *
	 * @since   2.6.1
	 */
	public function reschedule_log_cleanup_event() {

		$this->unschedule_log_cleanup_event();
		$this->schedule_log_cleanup_event();

	}

	/**
	 * Returns the scheduled log cleanup event, if it exists
	 *
	 * @since   2.6.1
	 */
	public function get_log_cleanup_event() {

		return wp_get_schedule( 'page_generator_pro_log_cleanup_cron' );

	}

	/**
	 * Returns the scheduled log cleanup event's next date and time to run, if it exists
	 *
	 * @since   2.6.1
	 *
	 * @param   bool|string $format     Format Timestamp (false | php date() compat. string).
	 */
	public function get_log_cleanup_event_next_scheduled( $format = false ) {

		// Get timestamp for when the event will next run.
		$scheduled = wp_next_scheduled( 'page_generator_pro_log_cleanup_cron' );

		// If no timestamp or we're not formatting the result, return it now.
		if ( ! $scheduled || ! $format ) {
			return $scheduled;
		}

		// Return formatted date/time.
		return gmdate( $format, $scheduled );

	}

	/**
	 * Runs the log cleanup CRON event
	 *
	 * @since   2.6.1
	 */
	public function log_cleanup() {

		// Bail if logging is disabled.
		if ( ! $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-generate', 'log_enabled', '0' ) ) {
			return;
		}

		// Bail if the preserve logs settings is indefinite.
		$preserve_days = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-generate', 'log_preserve_days', '7' );
		if ( ! $preserve_days ) {
			return;
		}

		// Define the date cutoff.
		$date_time = gmdate( 'Y-m-d H:i:s', strtotime( '-' . $preserve_days . ' days' ) );

		// Delete log entries older than the date.
		$this->base->get_class( 'log' )->delete_by_generated_at_cutoff( $date_time );

	}

}
