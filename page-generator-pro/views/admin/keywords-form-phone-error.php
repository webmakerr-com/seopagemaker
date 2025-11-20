<?php
/**
 * Outputs an error message at Keywords > Generate Phone Area Codes
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<header>
	<h1>
		<?php echo esc_html( $this->base->plugin->displayName ); ?>

		<span>
			<?php esc_html_e( 'Generate Phone Area Codes', 'page-generator-pro' ); ?>
		</span>
	</h1>
</header>

<div class="wrap">
	<div class="wrap-inner">
		<?php
		// Button Links.
		require_once 'keywords-links.php';

		// Output Success and/or Error Notices, if any exist.
		$this->base->get_class( 'notices' )->output_notices();
		?>
	</div>
</div>
