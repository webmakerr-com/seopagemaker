<?php
/**
 * Outputs the Generation metabox when adding/editing a Content Groups
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<div class="wpzinc-option sidebar">
	<div class="left">
		<label for="method"><?php esc_html_e( 'Method', 'page-generator-pro' ); ?></label>
	</div>
	<div class="right">
		<select name="<?php echo esc_attr( $this->base->plugin->name ); ?>[method]" id="method" size="1" class="widefat">
			<?php
			if ( is_array( $methods ) && count( $methods ) > 0 ) {
				foreach ( $methods as $method => $label ) {
					?>
					<option value="<?php echo esc_attr( $method ); ?>"<?php selected( $this->settings['method'], $method ); ?>>
						<?php echo esc_attr( $label ); ?>
					</option>
					<?php
				}
			}
			?>
		</select>
	</div>
	<p class="description">
		<strong><?php esc_html_e( 'All:', 'page-generator-pro' ); ?></strong>
		<?php
		printf(
			/* translators: Post Type, Plural (e.g. Posts, Pages) */
			esc_html__( 'Generates %s for all possible combinations of terms across all keywords used.', 'page-generator-pro' ),
			esc_html( $labels['plural'] )
		);
		?>
	</p>
	<p class="description">
		<strong><?php esc_html_e( 'Sequential:', 'page-generator-pro' ); ?></strong>
		<?php esc_html_e( 'Honors the order of terms in each keyword used. Once all terms have been used in a keyword, the generator stops.', 'page-generator-pro' ); ?>
	</p>
	<p class="description">
		<strong><?php esc_html_e( 'Random:', 'page-generator-pro' ); ?></strong>
		<?php
		printf(
			/* translators: Post Type, Singular (e.g. Post, Page) */
			esc_html__( 'For each %s generated, selects a term at random from each keyword used.', 'page-generator-pro' ),
			esc_html( $labels['singular'] )
		);
		?>
	</p>
</div>

<div class="wpzinc-option sidebar">
	<div class="left">
		<label for="overwrite"><?php esc_html_e( 'Overwrite', 'page-generator-pro' ); ?></label>
	</div>
	<div class="right">
		<select name="<?php echo esc_attr( $this->base->plugin->name ); ?>[overwrite]" id="overwrite" size="1" class="widefat">
			<?php
			if ( is_array( $overwrite_methods ) && count( $overwrite_methods ) > 0 ) {
				foreach ( $overwrite_methods as $method => $label ) {
					?>
					<option value="<?php echo esc_attr( $method ); ?>"<?php selected( $this->settings['overwrite'], $method ); ?>>
						<?php echo esc_attr( $label ); ?>
					</option>
					<?php
				}
			}
			?>
		</select>
	</div>
	<p class="description">
		<?php
		printf(
			'%s %s %s',
			esc_html__( 'See the', 'page-generator-pro' ),
			'<a href="' . esc_attr( $overwrite_documentation_url ) . '" rel="noopener" target="_blank">' . esc_html__( 'Documentation', 'page-generator-pro' ) . '</a>',
			esc_html__( 'to understand each available option.', 'page-generator-pro' )
		);
		?>
	</p>
</div>

<?php
if ( isset( $overwrite_sections ) ) {
	?>
	<div class="wpzinc-option sidebar overwrite-sections overwrite overwrite_any overwrite_preserve_date overwrite_any_preserve_date">
		<div class="full">
			<label><?php esc_html_e( 'Overwrite Sections', 'page-generator-pro' ); ?></label>
		</div>
		<div class="full">		
			<ul class="checklist">                    			
				<?php
				foreach ( $overwrite_sections as $key => $label ) {
					?>
					<li>
						<label>
							<input type="checkbox" name="<?php echo esc_attr( $this->base->plugin->name ); ?>[overwrite_sections][<?php echo esc_attr( $key ); ?>]" value="1"<?php echo esc_attr( isset( $this->settings['overwrite_sections'][ $key ] ) ? ' checked' : '' ); ?> />
							<?php echo esc_html( $label ); ?>
							<br />  
						</label>
					</li>
					<?php
				}
				?>
			</ul>
		</div>
		<p class="description">
			<?php
			printf(
				/* translators: %1$s: Post Type, Singular, %2$s: Post Type, Singular */
				esc_html__( 'If generation would overwrite an existing %1$s, choose which items of the existing %2$s to overwrite.', 'page-generator-pro' ),
				esc_html( $labels['singular'] ),
				esc_html( $labels['singular'] )
			);
			?>
		</p>
	</div>
	<?php
}
?>

<div class="wpzinc-option sidebar">
	<div class="left">
		<label for="number_of_posts">
			<?php
			printf(
				/* translators: Post Type, Plural */
				esc_html__( 'No. %s', 'page-generator-pro' ),
				esc_html( $labels['plural'] )
			);
			?>
		</label>
	</div>
	<div class="right">
		<input type="number" name="<?php echo esc_attr( $this->base->plugin->name ); ?>[numberOfPosts]" id="number_of_posts" value="<?php echo esc_attr( $this->settings['numberOfPosts'] ); ?>" step="1" min="0" class="widefat" />
	</div>
	<p class="description">
		<?php
		printf(
			/* translators: %1$s: Post Type, Plural, %2$s: Post Type, Plural */
			esc_html__( 'The number of %1$s to generate. If zero or blank, all %2$s will be generated.', 'page-generator-pro' ),
			esc_html( $labels['plural'] ),
			esc_html( $labels['plural'] )
		);
		?>
	</p>
</div>

<div class="wpzinc-option sidebar">
	<div class="left">
		<label for="resume_index"><?php esc_html_e( 'Resume Index', 'page-generator-pro' ); ?></label>
	</div>
	<div class="right">
		<input type="number" name="<?php echo esc_attr( $this->base->plugin->name ); ?>[resumeIndex]" id="resume_index" value="<?php echo esc_attr( $this->settings['resumeIndex'] ); ?>" step="1" min="0" class="widefat" />
	</div>
	<div class="full">
		<a href="#" class="alignright wpzinc-populate-field-value" data-field="#resume_index" data-value="<?php echo esc_attr( $this->settings['last_index_generated'] ); ?>">
			<?php esc_html_e( 'Use Last Generated Index', 'page-generator-pro' ); ?>
		</a>
	</div>

	<p class="description">
		<?php esc_html_e( 'Optional: If generation did not fully complete (e.g. 50 / 100 only), or you specified a limit, you can set the Resume Index = 50.', 'page-generator-pro' ); ?>
	</p>
</div>
