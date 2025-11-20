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
		<tr>
			<th><?php esc_html_e( 'Service Keyword', 'page-generator-pro' ); ?></th>
			<td>
				<a href="admin.php?page=page-generator-pro-keywords&cmd=form&id=<?php echo esc_attr( $this->configuration['service_keyword_id'] ); ?>" target="_blank">
					<?php esc_html_e( 'Edit', 'page-generator-pro' ); ?>
				</a>
			</td>
		</tr>
	</tbody>
</table>

<h2><?php esc_html_e( 'Content Groups', 'page-generator-pro' ); ?></h2>

<p>
	<?php esc_html_e( 'It is recommended that you edit the Content Group, proof read the AI generated content and adjust as necessary.', 'page-generator-pro' ); ?>
</p>

<table class="widefat striped">
	<tbody>
		<?php
		foreach ( $this->configuration['content_group_ids'] as $content_group_type => $content_group_id ) {
			?>
			<tr>
				<th>
					<?php echo esc_html( $this->configuration['service'] ); ?>
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
