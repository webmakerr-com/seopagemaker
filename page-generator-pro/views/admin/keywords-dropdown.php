<?php
/**
 * Outputs the Keywords dropdown field
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<select size="1" class="right wpzinc-tags" data-element="#<?php echo esc_attr( $element ); ?>">
	<option value=""><?php esc_attr_e( '--- Insert Keyword ---', 'page-generator-pro' ); ?></option>
	<?php
	if ( is_array( $keywords ) && count( $keywords ) ) {
		foreach ( $keywords as $keyword ) {
			?>
			<option value="{<?php echo esc_attr( $keyword ); ?>}"><?php echo esc_attr( $keyword ); ?></option>
			<?php
		}
	}
	?>
</select>
