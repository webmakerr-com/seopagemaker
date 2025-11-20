<?php
/**
 * Outputs the Menu metabox when adding/editing a Content Groups
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<div class="wpzinc-option sidebar">
	<div class="left">
		<label for="menu"><?php esc_html_e( 'Menu', 'page-generator-pro' ); ?></label>
	</div>
	<div class="right">
		<select name="<?php echo esc_attr( $this->base->plugin->name ); ?>[menu]" id="menu" size="1" class="widefat">
			<option value="0"<?php selected( $this->settings['menu'], 0 ); ?>>
				<?php esc_attr_e( '(none)', 'page-generator-pro' ); ?>
			</option>
			<?php
			if ( is_array( $menus ) && count( $menus ) > 0 ) {
				foreach ( $menus as $group_menu ) {
					?>
					<option value="<?php echo esc_attr( $group_menu->term_id ); ?>"<?php selected( $this->settings['menu'], $group_menu->term_id ); ?>>
						<?php echo esc_attr( $group_menu->name ); ?>
					</option>
					<?php
				}
			}
			?>
		</select>
	</div>
	<p class="description">
		<?php esc_html_e( 'If defined, generated Pages will be added to this WordPress Menu.', 'page-generator-pro' ); ?>
		<br />
		<?php
		printf(
			'%s %s',
			esc_html__( 'To display a Menu in your Theme, see', 'page-generator-pro' ),
			'<a href="nav-menus.php">' . esc_html__( 'Appearance > Menus', 'page-generator-pro' ) . '</a>'
		);
		?>
		<br />
		<?php esc_html_e( 'In Test Mode, the generated Page will not be assigned to this Menu.', 'page-generator-pro' ); ?>
	</p>
</div>

<div class="wpzinc-option sidebar">
	<div class="left">
		<label for="menu_title"><?php esc_html_e( 'Menu Title', 'page-generator-pro' ); ?></label>
	</div>
	<div class="right">
		<input type="text" name="<?php echo esc_attr( $this->base->plugin->name ); ?>[menu_title]" id="menu_title" value="<?php echo esc_attr( $this->settings['menu_title'] ); ?>" class="widefat" />
	</div>
	<p class="description">
		<?php esc_html_e( 'If defined, generated Pages will have the above title set in the Menu.', 'page-generator-pro' ); ?>
		<br />
		<?php esc_html_e( 'If empty, the generated Page title will be used.', 'page-generator-pro' ); ?>
		<br />
		<?php esc_html_e( 'Keywords and Spintax are supported.', 'page-generator-pro' ); ?>
	</p>
</div>

<div class="wpzinc-option sidebar">
	<div class="left">
		<label for="menu_parent"><?php esc_html_e( 'Menu Parent', 'page-generator-pro' ); ?></label>
	</div>
	<div class="right">
		<input type="text" name="<?php echo esc_attr( $this->base->plugin->name ); ?>[menu_parent]" id="menu_parent" value="<?php echo esc_attr( $this->settings['menu_parent'] ); ?>" class="widefat" />
	</div>
	<p class="description">
		<?php esc_html_e( 'To make generated Menu items the child of an existing Menu item, enter the parent Menu Item Title or ID here.', 'page-generator-pro' ); ?>
		<br />
		<a href="<?php echo esc_attr( $this->base->plugin->documentation_url ); ?>/generate-content/#fields--menu" rel="noopener" target="_blank">
			<?php esc_html_e( 'How to find the Parent Menu ID', 'page-generator-pro' ); ?>
		</a>
	</p>
</div>
