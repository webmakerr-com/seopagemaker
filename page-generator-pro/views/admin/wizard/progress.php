<?php
/**
 * Outputs the progress bar for Content Groups > Add New Directory
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<div id="wpzinc-onboarding-progress">
	<ol>
		<li<?php echo ( $this->step >= 1 ? ' class="done"' : '' ); ?>><?php esc_html_e( 'Setup', 'page-generator-pro' ); ?></li>
		<li<?php echo ( $this->step >= 2 ? ' class="done"' : '' ); ?>><?php esc_html_e( 'Done', 'page-generator-pro' ); ?></li>
	</ol>
</div>
