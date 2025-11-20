<?php
/**
 * Outputs the header template for Content Groups > Add New Directory
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta name="viewport" content="width=device-width"/>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<title><?php echo esc_html( $this->base->plugin->displayName ); ?> &lsaquo; <?php bloginfo( 'name' ); ?>  &#8212; WordPress</title>
		<?php
		do_action( 'admin_print_scripts' );
		do_action( 'admin_print_styles' );
		do_action( 'admin_head' );
		?>
	</head>
	<body class="wp-admin wp-core-ui wpzinc <?php echo esc_attr( $this->base->plugin->name ); ?>">
		<div id="wpzinc-onboarding">
			<div class="wrap">
				<?php
				// Output Progress Bar / Bullets.
				require 'progress.php';

				// Output Success and/or Error Notices, if any exist.
				$this->base->get_class( 'notices' )->output_notices();
				?>

				<div class="js-notices"></div>
			</div>
