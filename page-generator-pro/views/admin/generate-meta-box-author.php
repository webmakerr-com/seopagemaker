<?php
/**
 * Outputs the Author metabox when adding/editing a Content Groups
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<div id="author" class="wpzinc-option">
	<div class="left">
		<label for="author"><?php esc_html_e( 'Author', 'page-generator-pro' ); ?></strong>
	</div>
	<div class="right">
		<select name="<?php echo esc_attr( $this->base->plugin->name ); ?>[author]" id="author" class="wpzinc-selectize-search" data-action="page_generator_pro_search_authors" data-name-field="user_login" data-value-field="id" data-method="POST" data-output-fields="user_login" data-nonce="<?php echo esc_attr( wp_create_nonce( 'search_authors' ) ); ?>">
			<?php
			if ( ! empty( $this->settings['author'] ) && $this->settings['author'] !== false ) {
				?>
				<option value="<?php echo esc_attr( $this->settings['author'] ); ?>" selected><?php echo esc_attr( $author->user_login ); ?></option>
				<?php
			}
			?>
		</select>
	</div>	
</div>

<div class="wpzinc-option">
	<div class="left">
		<label for="rotate_authors"><?php esc_html_e( 'Random?', 'page-generator-pro' ); ?></label>
	</div>
	<div class="right">
		<input type="checkbox" id="rotate_authors" name="<?php echo esc_attr( $this->base->plugin->name ); ?>[rotateAuthors]" value="1"<?php checked( $this->settings['rotateAuthors'], 1 ); ?> data-conditional="author" data-conditional-display="false" />

		<p class="description">
			<?php esc_html_e( 'If checked, will choose a WordPress User at random for each Page/Post generated.', 'page-generator-pro' ); ?>
		</p>
	</div>
</div>
