<?php
/**
 * Outputs the Publish metabox when adding/editing a Content Groups
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<div class="wpzinc-option sidebar">
	<div class="left">
		<label for="type"><?php esc_html_e( 'Post Type', 'page-generator-pro' ); ?></label>
	</div>
	<div class="right">
		<select name="<?php echo esc_attr( $this->base->plugin->name ); ?>[type]" id="type" size="1" class="widefat">
			<?php
			if ( is_array( $post_types ) && count( $post_types ) > 0 ) {
				foreach ( $post_types as $group_post_type => $group_post_type_object ) {
					?>
					<option value="<?php echo esc_attr( $group_post_type ); ?>"<?php selected( $this->settings['type'], $group_post_type ); ?>>
						<?php echo esc_attr( $group_post_type_object->labels->singular_name ); ?>
					</option>
					<?php
				}
			}
			?>
		</select>
	</div>
	<p class="description">
		<?php esc_html_e( 'The Post Type to create when generating content, such as a Page or Post', 'page-generator-pro' ); ?>
	</p>
</div>

<div class="wpzinc-option sidebar">
	<div class="left">
		<label for="status"><?php esc_html_e( 'Status', 'page-generator-pro' ); ?></label>
	</div>
	<div class="right">
		<select name="<?php echo esc_attr( $this->base->plugin->name ); ?>[status]" id="status" size="1" class="widefat">
			<?php
			if ( is_array( $statuses ) && count( $statuses ) > 0 ) {
				foreach ( $statuses as $group_status => $label ) {
					?>
					<option value="<?php echo esc_attr( $group_status ); ?>"<?php selected( $this->settings['status'], $group_status ); ?>>
						<?php echo esc_attr( $label ); ?>
					</option>
					<?php
				}
			}
			?>
		</select>
	</div>
</div>

<div class="wpzinc-option sidebar">
	<div class="left">
		<label for="date_option"><?php esc_html_e( 'Date', 'page-generator-pro' ); ?></label>
	</div>
	<div class="right">
		<select name="<?php echo esc_attr( $this->base->plugin->name ); ?>[date_option]" id="date_option" size="1" class="widefat">
			<?php
			if ( is_array( $date_options ) && count( $date_options ) > 0 ) {
				foreach ( $date_options as $date_option => $label ) {
					?>
					<option value="<?php echo esc_attr( $date_option ); ?>"<?php selected( $this->settings['date_option'], $date_option ); ?>>
						<?php echo esc_attr( $label ); ?>
					</option>
					<?php
				}
			}
			?>
		</select>
	</div>
</div>

<div class="wpzinc-option sidebar specific">
	<div class="full">
		<label for="date_specific"><?php esc_html_e( 'Specific Date', 'page-generator-pro' ); ?></label>
	</div>
	<div class="full">
		<input type="datetime-local" name="<?php echo esc_attr( $this->base->plugin->name ); ?>[date_specific]" id="date_specific" value="<?php echo esc_attr( $this->settings['date_specific'] ); ?>" class="widefat" />
	</div>
</div>

<div class="wpzinc-option sidebar specific_keyword">
	<div class="full">
		<label for="date_specific_keyword"><?php esc_html_e( 'Specific Date from Keyword', 'page-generator-pro' ); ?></label>
	</div>
	<div class="full">
		<input type="text" name="<?php echo esc_attr( $this->base->plugin->name ); ?>[date_specific_keyword]" id="date_specific_keyword" value="<?php echo esc_attr( $this->settings['date_specific_keyword'] ); ?>" class="widefat" />
	</div>
	<p class="description">
		<?php esc_html_e( 'The Keyword that contains a date. Supported formats are:', 'page-generator-pro' ); ?>
		<br />
		<code>2024-01-01</code>
		<br />
		<code>2024-01-01 00:00:00</code>
		<br />
		<code>Timestamps: 1706191655</code>
	</p>
</div>

<div class="wpzinc-option sidebar random">
	<div class="full">
		<label for="date_min"><?php esc_html_e( 'Start', 'page-generator-pro' ); ?></label>
	</div>
	<div class="full">
		<input type="date" name="<?php echo esc_attr( $this->base->plugin->name ); ?>[date_min]" id="date_min" value="<?php echo esc_attr( $this->settings['date_min'] ); ?>" />
	</div>

	<div class="full">
		<label for="date_max"><?php esc_html_e( 'End', 'page-generator-pro' ); ?></label>
	</div>
	<div class="full">
		<input type="date" name="<?php echo esc_attr( $this->base->plugin->name ); ?>[date_max]" id="date_max" value="<?php echo esc_attr( $this->settings['date_max'] ); ?>" />
	</div>

	<p class="description">
		<?php esc_html_e( 'Each generated page will use a date and time between the above minimum and maximum dates.', 'page-generator-pro' ); ?>
	</p>
</div>

<!-- Schedule Options -->
<div class="wpzinc-option sidebar future">
	<div class="full">
		<label for="schedule"><?php esc_html_e( 'Schedule Increment', 'page-generator-pro' ); ?></label>
	</div>
	<div class="full">
		<input type="number" class="small-text" name="<?php echo esc_attr( $this->base->plugin->name ); ?>[schedule]" id="schedule" value="<?php echo esc_attr( $this->settings['schedule'] ); ?>" step="1" min="1" />
		<select name="<?php echo esc_attr( $this->base->plugin->name ); ?>[scheduleUnit]" size="1">
			<?php
			if ( is_array( $schedule_units ) && count( $schedule_units ) > 0 ) {
				foreach ( $schedule_units as $unit => $label ) {
					?>
					<option value="<?php echo esc_attr( $unit ); ?>"<?php selected( $this->settings['scheduleUnit'], $unit ); ?>>
						<?php echo esc_attr( $label ); ?>
					</option>
					<?php
				}
			}
			?>
		</select>
	</div>

	<p class="description">
		<?php esc_html_e( 'The first generated Page’s date and time will be based on the Date setting (i.e. now or a specified date and time), plus the increment.', 'page-generator-pro' ); ?>
		<br />
		<?php esc_html_e( 'Second and subsequent Pages’ date and time will be based on the previous generated Page’s date and time, plus the increment.', 'page-generator-pro' ); ?>
	</p>
</div>
