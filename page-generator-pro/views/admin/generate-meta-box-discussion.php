<?php
/**
 * Outputs the Discussion metabox when adding/editing a Content Groups
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<div class="wpzinc-option">
	<div class="left">
		<label for="comments"><?php esc_html_e( 'Allow comments?', 'page-generator-pro' ); ?></label>
	</div>
	<div class="right">
		<input type="checkbox" id="comments" name="<?php echo esc_attr( $this->base->plugin->name ); ?>[comments]" value="1"<?php checked( $this->settings['comments'], 1 ); ?> />

		<p class="description">
			<?php esc_html_e( 'If checked, a comments form will be displayed on every generated Page/Post.  It is your Theme\'s responsibility to honor this setting.', 'page-generator-pro' ); ?>
		</p>
	</div>
</div>
<div class="wpzinc-option">
	<div class="left">
		<label for="comments_generate"><?php esc_html_e( 'Generate Comments?', 'page-generator-pro' ); ?></label>
	</div>
	<div class="right">
		<input type="checkbox" id="comments_generate" name="<?php echo esc_attr( $this->base->plugin->name ); ?>[comments_generate][enabled]" value="1" data-conditional="comments-generate" <?php checked( $this->settings['comments_generate']['enabled'], 1 ); ?> />

		<p class="description">
			<?php esc_html_e( 'If checked, options are displayed to generate comments with every generated Page/Post.', 'page-generator-pro' ); ?>
		</p>
	</div>
</div>

<div id="comments-generate">
	<div class="wpzinc-option">
		<div class="left">
			<label for="comments_generate_limit"><?php esc_html_e( 'No. Comments', 'page-generator-pro' ); ?></label>
		</div>
		<div class="right">
			<input type="number" id="comments_generate_limit" name="<?php echo esc_attr( $this->base->plugin->name ); ?>[comments_generate][limit]" value="<?php echo esc_attr( $this->settings['comments_generate']['limit'] ); ?>" min="0" max="50" step="1" />

			<p class="description">
				<?php esc_html_e( 'The number of Comments to generate for each Page/Post generated. If zero or blank, a random number of Comments will be generated.', 'page-generator-pro' ); ?>
			</p>
		</div>
	</div>

	<div class="wpzinc-option">
		<div class="left">
			<label for="comments_generate_date_option"><?php esc_html_e( 'Date', 'page-generator-pro' ); ?></label>
		</div>
		<div class="right">
			<select name="<?php echo esc_attr( $this->base->plugin->name ); ?>[comments_generate][date_option]" id="comments_generate_date_option" size="1" class="widefat">
				<?php
				if ( is_array( $date_options ) && count( $date_options ) > 0 ) {
					foreach ( $date_options as $date_option => $label ) {
						?>
						<option value="<?php echo esc_attr( $date_option ); ?>"<?php selected( $this->settings['comments_generate']['date_option'], $date_option ); ?>>
							<?php echo esc_attr( $label ); ?>
						</option>
						<?php
					}
				}
				?>
			</select>
		</div>
	</div>

	<div class="wpzinc-option specific">
		<div class="left">
			<label for="comments_generate_date_specific"><?php esc_html_e( 'Specific Date', 'page-generator-pro' ); ?></label>
		</div>
		<div class="right">
			<input type="date" name="<?php echo esc_attr( $this->base->plugin->name ); ?>[comments_generate][date_specific]" id="comments_generate_date_specific" value="<?php echo esc_attr( $this->settings['comments_generate']['date_specific'] ); ?>" class="widefat" />

			<p class="description">
				<?php esc_html_e( 'Each generated comment will use this date as the comment date.', 'page-generator-pro' ); ?>
			</p>
		</div>
	</div>

	<div class="wpzinc-option random">
		<div class="left">
			<label for="comments_generate_date_min"><?php esc_html_e( 'Start', 'page-generator-pro' ); ?></label>
		</div>
		<div class="right">
			<input type="date" name="<?php echo esc_attr( $this->base->plugin->name ); ?>[comments_generate][date_min]" id="comments_generate_date_min" value="<?php echo esc_attr( $this->settings['comments_generate']['date_min'] ); ?>" />
		</div>
	</div>
	<div class="wpzinc-option random">
		<div class="left">
			<label for="comments_generate_date_max"><?php esc_html_e( 'End', 'page-generator-pro' ); ?></label>
		</div>
		<div class="right">
			<input type="date" name="<?php echo esc_attr( $this->base->plugin->name ); ?>[comments_generate][date_max]" id="comments_generate_date_max" value="<?php echo esc_attr( $this->settings['comments_generate']['date_max'] ); ?>" />

			<p class="description">
				<?php esc_html_e( 'Each generated comment will use a date and time between the above minimum and maximum dates.', 'page-generator-pro' ); ?>
			</p>
		</div>
	</div>

	<div class="wpzinc-option">
		<div class="left">
			<label for="comments_generate_firstname"><?php esc_html_e( 'First Name', 'page-generator-pro' ); ?></label>
		</div>
		<div class="right">
			<input type="text" id="comments_generate_firstname" name="<?php echo esc_attr( $this->base->plugin->name ); ?>[comments_generate][firstname]" value="<?php echo esc_attr( $this->settings['comments_generate']['firstname'] ); ?>" class="widefat" />

			<button class="spintax dynamic-element button" data-shortcode="spintax_firstname" title="<?php echo esc_attr__( 'Insert Spintax of First Names', 'page-generator-pro' ); ?>" data-content="{James|Mary|John|Patricia|Robert|Jennifer|Michael|Linda|William|Elizabeth|David|Barbara|Richard|Susan|Joseph|Jessica|Thomas|Sarah|Charles|Karen|Christopher|Nancy|Daniel|Lisa|Matthew|Betty|Anthony|Margaret|Mark|Sandra|Donald|Ashley|Steven|Kimberly|Paul|Emily|Andrew|Donna|Joshua|Michelle|Kenneth|Dorothy|Kevin|Carol|Brian|Amanda|George|Melissa|Edward|Deborah|Ronald|Stephanie|Timothy|Rebecca|Jason|Laura|Jeffrey|Sharon|Ryan|Cynthia|Jacob|Kathleen|Gary|Amy|Nicholas|Shirley|Eric|Angela|Jonathan|Helen|Stephen|Anna|Larry|Brenda|Justin|Pamela|Scott|Nicole|Brandon|Emma|Benjamin|Samantha|Samuel|Katherine|Gregory|Christine|Frank|Debra|Raymond|Rachel|Alexander|Catherine|Patrick|Carolyn|Jack|Janet|Dennis|Ruth|Jerry|Maria}">
				<?php echo esc_attr__( 'Insert Spintax of First Names', 'page-generator-pro' ); ?>
			</button>

			<p class="description">
				<?php esc_html_e( 'The Author\'s First Name for each Generated Comment. Supports Keywords and Spintax. We recommend using a Keyword comprising of all First Names, and using {keyword:random_different} to generate a random first name for each generated Comment.', 'page-generator-pro' ); ?>
			</p>
		</div>
	</div>

	<div class="wpzinc-option">
		<div class="left">
			<label for="comments_generate_firstname"><?php esc_html_e( 'Surname', 'page-generator-pro' ); ?></label>
		</div>
		<div class="right">
			<input type="text" id="comments_generate_surname" name="<?php echo esc_attr( $this->base->plugin->name ); ?>[comments_generate][surname]" value="<?php echo esc_attr( $this->settings['comments_generate']['surname'] ); ?>" class="widefat" />

			<button class="spintax dynamic-element button" data-shortcode="spintax_surname" title="<?php echo esc_attr__( 'Insert Spintax of Surnames', 'page-generator-pro' ); ?>" data-content="{Smith|Johnson|Williams|Brown|Jones|Garcia|Miller|Davis|Rodriguez|Martinez|Hernandez|Lopez|Gonzalez|Wilson|Anderson|Thomas|Taylor|Moore|Jackson|Martin|Lee|Perez|Thompson|White|Harris|Sanchez|Clark|Ramirez|Lewis|Robinson|Walker|Young|Allen|King|Wright|Scott|Torres|Nguyen|Hill|Flores|Green|Adams|Nelson|Baker|Hall|Rivera|Campbell|Mitchell|Carter|Roberts|Gomez|Phillips|Evans|Turner|Diaz|Parker|Cruz|Edwards|Collins|Reyes|Stewart|Morris|Morales|Murphy|Cook|Rogers|Gutierrez|Ortiz|Morgan|Cooper|Peterson|Bailey|Reed|Kelly|Howard|Ramos|Kim|Cox|Ward|Richardson|Watson|Brooks|Chavez|Wood|James|Bennett|Gray|Mendoza|Ruiz|Hughes|Price|Alvarez|Castillo|Sanders|Patel|Myers|Long|Ross|Foster|Jimenez|Powell}">
				<?php echo esc_attr__( 'Insert Spintax of Surnames', 'page-generator-pro' ); ?>
			</button>

			<p class="description">
				<?php esc_html_e( 'The Author\'s Surname for each Generated Comment. Supports Keywords and Spintax. We recommend using a Keyword comprising of all Surnames, and using {keyword:random_different} to generate a random surname for each generated Comment.', 'page-generator-pro' ); ?>
			</p>
		</div>
	</div>

	<div class="wpzinc-option">
		<div class="left">
			<label for="comments_generate_comment"><?php esc_html_e( 'Comment', 'page-generator-pro' ); ?></label>
		</div>
		<div class="right">
			<textarea id="comments_generate_comment" name="<?php echo esc_attr( $this->base->plugin->name ); ?>[comments_generate][comment]" class="widefat"><?php echo esc_attr( $this->settings['comments_generate']['comment'] ); ?></textarea>
			<?php
			// Iterate through Dynamic Elements, outputting buttons for each.
			foreach ( $shortcodes as $shortcode_name => $shortcode ) {
				?>
				<button class="<?php echo esc_attr( $shortcode_name ); ?> dynamic-element button" data-shortcode="<?php echo esc_attr( $shortcode_name ); ?>" title="<?php echo esc_attr( $shortcode['title'] ); ?>">
					<?php echo esc_html( $shortcode['title'] ); ?>
				</button>
				<?php
			}
			?>
			<p class="description">
				<?php esc_html_e( 'The Comment Text for each Generated Comment. Supports Keywords, Spintax and AI Dynamic Elements.', 'page-generator-pro' ); ?>
			</p>
		</div>
	</div>
</div>
<div class="wpzinc-option">
	<div class="left">
		<label for="trackbacks"><?php esc_html_e( 'Allow track / pingbacks?', 'page-generator-pro' ); ?></label>
	</div>
	<div class="right">
		<input type="checkbox" id="trackbacks" name="<?php echo esc_attr( $this->base->plugin->name ); ?>[trackbacks]" value="1"<?php checked( $this->settings['trackbacks'], 1 ); ?> />
	</div>
</div>
