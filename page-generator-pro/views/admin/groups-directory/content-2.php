<?php
/**
 * Outputs the second step for Content Groups > Add New Directory
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<h1><?php esc_html_e( 'Done', 'page-generator-pro' ); ?></h1>

<p>
	<?php esc_html_e( 'The following has been setup.', 'page-generator-pro' ); ?>
</p>

<h2><?php esc_html_e( 'Keywords', 'page-generator-pro' ); ?></h2>
<table class="widefat striped">
	<tbody>
		<?php
		if ( ! empty( $this->configuration['service_keyword_id'] ) ) {
			?>
			<tr>
				<th><?php esc_html_e( 'Service Keyword', 'page-generator-pro' ); ?></th>
				<td>
					<a href="admin.php?page=page-generator-pro-keywords&cmd=form&id=<?php echo esc_attr( $this->configuration['service_keyword_id'] ); ?>" target="_blank">
						<?php esc_html_e( 'Edit', 'page-generator-pro' ); ?>
					</a>
				</td>
			</tr>
			<?php
		}
		?>
		<tr>
			<th><?php esc_html_e( 'Location Keyword', 'page-generator-pro' ); ?></th>
			<td>
				<a href="admin.php?page=page-generator-pro-keywords&cmd=form&id=<?php echo esc_attr( $this->configuration['location_keyword_id'] ); ?>" target="_blank">
					<?php esc_html_e( 'Edit', 'page-generator-pro' ); ?>
				</a>
			</td>
		</tr>
	</tbody>
</table>

<h2><?php esc_html_e( 'Content Groups', 'page-generator-pro' ); ?></h2>

<p>
	<?php esc_html_e( 'It is recommended that you edit each Content Group and write the content needed. The Content Groups will have a paragraph of text prefilled with the necessary Related Links shortcode for interlinking content.', 'page-generator-pro' ); ?>
</p>

<table class="widefat striped">
	<tbody>
		<?php
		foreach ( $this->configuration['content_group_ids'] as $content_group_type => $content_group_id ) {
			?>
			<tr>
				<th>
					<?php
					switch ( $content_group_type ) {
						case 'region_group_id':
							esc_html_e( 'Region Content Group', 'page-generator-pro' );
							break;

						case 'county_group_id':
							esc_html_e( 'County Content Group', 'page-generator-pro' );
							break;

						case 'city_group_id':
							esc_html_e( 'City Content Group', 'page-generator-pro' );
							break;

						case 'service_group_id':
							esc_html_e( 'Service Content Group', 'page-generator-pro' );
							break;
					}
					?>
				</th>
				<td>
					<a href="post.php?post=<?php echo esc_attr( $content_group_id ); ?>&action=edit" target="_blank">
						<?php esc_html_e( 'Edit', 'page-generator-pro' ); ?>
					</a>
				</td>
			</tr>
			<?php
		}
		?>
	</tbody>
</table>

<p>
	<?php esc_html_e( 'Click "Finish" to load the Content Groups screen.', 'page-generator-pro' ); ?>
</p>
