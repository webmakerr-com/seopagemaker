<?php
/**
 * Outputs the Keywords screen
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<header>
	<h1>
		<?php echo esc_html( $this->base->plugin->displayName ); ?>

		<span>
			<?php esc_html_e( 'Keywords', 'page-generator-pro' ); ?>
		</span>
	</h1>
</header>

<div class="wrap">
	<div class="wrap-inner">
		<?php
		// Button Links.
		require_once 'keywords-links.php';

		// Search Subtitle.
		if ( ! empty( $keywords_table->get_search() ) ) {
			?>
			<span class="subtitle left"><?php esc_html_e( 'Search results for', 'page-generator-pro' ); ?> &#8220;<?php echo esc_html( $keywords_table->get_search() ); ?>&#8221;</span>
			<?php
		}
		?>

		<form action="admin.php" method="get" id="posts-filter">
			<input type="hidden" name="page" value="page-generator-pro-keywords" />
			<?php
			$keywords_table->search_box( esc_html__( 'Search', 'page-generator-pro' ), 'page-generator-pro' );
			$keywords_table->display();
			?>
		</form>
	</div>
</div><!-- /.wrap -->
