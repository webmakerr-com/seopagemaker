<?php
/**
 * Outputs the Keywords dropdown field for the Content Group's Title field
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<!-- Keywords -->
<div id="keywords-title">
	<?php $this->base->get_class( 'keywords' )->output_dropdown( $this->keywords, 'title' ); ?>
</div>
