<?php
/**
 * Outputs a table row when viewing Logs
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

foreach ( $this->items as $count => $result ) {
	?>
	<tr class="<?php echo esc_attr( $result['result'] . ( ( $count % 2 > 0 ) ? ' alternate' : '' ) ); ?>">
		<th scope="row" class="check-column">
			<input type="checkbox" name="ids[<?php echo esc_attr( $result['id'] ); ?>]" value="<?php echo esc_attr( $result['id'] ); ?>" />
		</th>
		<td class="group_id column-group_id<?php echo esc_attr( ( in_array( 'group_id', $hidden, true ) ? ' hidden' : '' ) ); ?>">
			<a href="<?php echo esc_attr( admin_url( 'admin.php?page=' . $this->base->plugin->name . '-logs&group_id=' . $result['group_id'] ) ); ?>" title="<?php esc_attr_e( 'Filter Log by this Group', 'page-generator-pro' ); ?>"> 
				#<?php echo esc_html( $result['group_id'] ); ?><br />
				<?php echo esc_html( $result['group_name'] ); ?>
			</a>
		</td>
		<td class="group_id column-post_id<?php echo esc_attr( ( in_array( 'post_id', $hidden, true ) ? ' hidden' : '' ) ); ?>">
			<a href="<?php echo esc_attr( $result['url'] ); ?>" target="_blank" title="<?php esc_attr_e( 'View Generated Item', 'page-generator-pro' ); ?>"><?php echo esc_html( $result['url'] ); ?></a>
		</td>
		<td class="group_id column-system<?php echo esc_attr( ( in_array( 'system', $hidden, true ) ? ' hidden' : '' ) ); ?>">
			<?php echo esc_html( $result['system'] ); ?>
		</td>
		<td class="group_id column-test_mode<?php echo esc_attr( ( in_array( 'test_mode', $hidden, true ) ? ' hidden' : '' ) ); ?>">
			<?php echo esc_html( $result['test_mode'] ? esc_html__( 'Yes', 'page-generator-pro' ) : esc_html__( 'No', 'page-generator-pro' ) ); ?>
		</td>
		<td class="group_id column-generated<?php echo esc_attr( ( in_array( 'generated', $hidden, true ) ? ' hidden' : '' ) ); ?>">
			<?php echo esc_html( $result['generated'] ? esc_html__( 'Yes', 'page-generator-pro' ) : esc_html__( 'No', 'page-generator-pro' ) ); ?>
		</td>
		<td class="group_id column-keywords_terms<?php echo esc_attr( ( in_array( 'keywords_terms', $hidden, true ) ? ' hidden' : '' ) ); ?>">
			<?php
			// Keywords/Terms will be empty if an error occured because all possibile keyword term combinations have been generated.
			if ( empty( $result['keywords_terms'] ) ) {
				echo '&nbsp;';
			} else {
				$keywords_terms = json_decode( $result['keywords_terms'] );
				foreach ( $keywords_terms as $keyword => $keyword_term ) {
					echo esc_html( $keyword ) . ': ' . esc_html( $keyword_term ) . '<br />';
				}
			}
			?>
		</td>
		<td class="group_id column-result<?php echo esc_attr( ( in_array( 'result', $hidden, true ) ? ' hidden' : '' ) ); ?>">
			<?php echo esc_html( $result['result'] ); ?>
		</td>
		<td class="group_id column-message<?php echo esc_attr( ( in_array( 'message', $hidden, true ) ? ' hidden' : '' ) ); ?>">
			<?php echo esc_html( $result['message'] ); ?>
		</td>
		<td class="group_id column-duration<?php echo esc_attr( ( in_array( 'duration', $hidden, true ) ? ' hidden' : '' ) ); ?>">
			<?php echo esc_html( $result['duration'] ); ?>
		</td>
		<td class="group_id column-memory_usage<?php echo esc_attr( ( in_array( 'memory_usage', $hidden, true ) ? ' hidden' : '' ) ); ?>">
			<?php echo esc_html( $result['memory_usage'] ); ?>
		</td>
		<td class="group_id column-memory_peak_usage<?php echo esc_attr( ( in_array( 'memory_peak_usage', $hidden, true ) ? ' hidden' : '' ) ); ?>">
			<?php echo esc_html( $result['memory_peak_usage'] ); ?>
		</td>
		<td class="group_id column-generated_at<?php echo esc_attr( ( in_array( 'generated_at', $hidden, true ) ? ' hidden' : '' ) ); ?>">
			<?php echo esc_html( gmdate( 'jS F, Y H:i:s', strtotime( $result['generated_at'] ) ) ); ?>
		</td>
	</tr>
	<?php
}
