<?php
/**
 * Outputs a Group dropdown <select> field.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<select name="page_generator_pro_group_id" size="1">
	<option value=""<?php selected( $current_group_id, '' ); ?>><?php esc_html_e( 'Any Group', 'page-generator-pro' ); ?></option>
	<option value="-1"<?php selected( $current_group_id, '-1' ); ?>><?php esc_html_e( '(Manually Created)', 'page-generator-pro' ); ?></option>
	<?php
	foreach ( $groups as $group_id => $group_name ) {
		?>
		<option value="<?php echo esc_attr( $group_id ); ?>"<?php selected( $current_group_id, $group_id ); ?>><?php echo esc_attr( $group_name ); ?></option>
		<?php
	}
	?>
</select>
