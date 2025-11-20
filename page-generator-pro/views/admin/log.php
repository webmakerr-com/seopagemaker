<?php
/**
 * Outputs the Logs screen
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<header>
	<h1>
		<?php echo esc_html( $this->base->plugin->displayName ); ?>

		<span>
			<?php esc_html_e( 'Logs', 'page-generator-pro' ); ?>
		</span>
	</h1>
</header>

<hr class="wp-header-end" />

<div class="wrap">
	<div class="wrap-inner">
		<?php
		// Search Subtitle.
		if ( $table->is_search() ) {
			?>
			<span class="subtitle left"><?php esc_html_e( 'Search results for', 'page-generator-pro' ); ?> &#8220;<?php echo esc_html( $table->get_search() ); ?>&#8221;</span>
			<?php
		}
		?>

		<form action="admin.php?page=page-generator-pro-logs" method="post" id="posts-filter">
			<?php
			// Output Search Box.
			$table->search_box( esc_html__( 'Search', 'page-generator-pro' ), 'page-generator-pro' );

			// Output Table.
			$table->display();
			?>
		</form>
	</div>
</div><!-- /.wrap -->
