<?php
/**
 * Content Group Generation Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Handles generating content from Content Groups (Pages, Posts
 * and Custom Post Types)
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 1.0.0
 */
class Page_Generator_Pro_Generate {

	/**
	 * Holds the base object.
	 *
	 * @since   1.9.8
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Holds an array comprising of every keyword detected in the Group.
	 * Each Keyword holds an array comprising of every single Term for that Keyword.
	 *
	 * @since   1.9.8
	 *
	 * @var     array
	 */
	public $keywords = array();

	/**
	 * Holds an array comprising of every keyword detected in the Group.
	 * Each Keyword holds the nth Term that will be used to replace the Keyword.
	 *
	 * @since   1.9.8
	 *
	 * @var     array
	 */
	public $keywords_terms = array();

	/**
	 * Holds an array comprising of every required keyword detected in the Group.
	 *
	 * @since   4.0.4
	 *
	 * @var     array
	 */
	public $required_keywords = array();

	/**
	 * Holds an array comprising of every required keyword detected in the Group,
	 * including columns and modifiers.
	 *
	 * @since   4.0.4
	 *
	 * @var     array
	 */
	public $required_keywords_full = array();

	/**
	 * Holds the array of keywords to replace e.g. {city}
	 *
	 * @since   1.3.1
	 *
	 * @var     array
	 */
	public $searches = array();

	/**
	 * Holds the array of keyword values to replace e.g. Birmingham
	 *
	 * @since   1.3.1
	 *
	 * @var     array
	 */
	public $replacements = array();

	/**
	 * Holds a flag to denote if one or more $replacements are an array
	 * If they're an array, it's because the :random_different transformation
	 * is used, and so we have to perform a slower search/replace method.
	 *
	 * @since   2.7.2
	 *
	 * @var     bool
	 */
	public $replacements_contain_array = false;

	/**
	 * Holds a flag to denote whether Page Generator Pro shortcodes
	 * should be processed on the main Post Content
	 *
	 * @since   1.9.5
	 *
	 * @var     bool
	 */
	public $process_shortcodes_on_post_content = false;

	/**
	 * Holds errors from a Dynamic Elements e.g.
	 * if AI returned an error, such as a rate limit.
	 *
	 * @since   4.9.0
	 *
	 * @var     bool|WP_Error
	 */
	public $dynamic_element_errors = false;

	/**
	 * Constructor.
	 *
	 * @since   1.9.3
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Delete Geodata on Post Deletion.
		add_action( 'delete_post', array( $this, 'delete_latitude_longitude_by_post_id' ) );

	}

	/**
	 * Runs the main generate process for the given Content or Term Group across
	 * CLI or CRON.
	 *
	 * The browser method (i.e. AJAX) uses the generate_content() function directly.
	 *
	 * @since   3.1.3
	 *
	 * @param   int    $group_id           Group ID.
	 * @param   string $type               Group Type (content|term).
	 * @param   int    $resume_index       Resume Index to start at.
	 * @param   int    $number_of_posts    Number of Posts/Terms to Generate.
	 * @param   bool   $test_mode          Test Mode.
	 * @param   string $system             System (browser|server|cli).
	 * @return  WP_Error|bool
	 */
	public function generate( $group_id, $type = 'content', $resume_index = 0, $number_of_posts = 0, $test_mode = false, $system = 'browser' ) {

		$this->add_to_debug_log(
			sprintf(
				/* translators: Group ID */
				__( 'Group ID #%s: Started', 'page-generator-pro' ),
				$group_id
			),
			$system
		);

		// Get Groups or Groups Term Instance.
		$groups = ( ( $type === 'term' ) ? $this->base->get_class( 'groups_terms' ) : $this->base->get_class( 'groups' ) );

		// If this Group has a request to cancel generation, silently clear the status, system and cancel
		// flags before performing further checks on whether we should generate.
		if ( $groups->cancel_generation_requested( $group_id ) ) {
			$this->add_to_debug_log(
				sprintf(
					/* translators: Group ID */
					__( 'Group ID #%s: Generation cancelled by User', 'page-generator-pro' ),
					$group_id
				),
				$system
			);
			$groups->stop_generation( $group_id );
		}

		// If the group is already generating, bail.
		if ( $groups->is_generating( $group_id ) ) {
			$error = new WP_Error(
				'page_generator_pro_generate',
				sprintf(
					/* translators: %1$s: Group ID, %2$s: System (browser, cron or cli) */
					__( 'Group ID #%1$s: Generation is already running via %2$s', 'page-generator-pro' ),
					$group_id,
					$groups->get_system( $group_id )
				)
			);

			$this->add_to_debug_log( $error->get_error_message(), $system, true );
			$groups->stop_generation( $group_id );
			return $error;
		}

		// Get group.
		$group = $groups->get_settings( $group_id, false, true );

		// Bail if an error occured.
		if ( is_wp_error( $group ) ) {
			$this->add_to_debug_log( $group->get_error_message(), $system, true );
			$groups->stop_generation( $group_id );
			return $group;
		}

		if ( $type === 'term' ) {
			/**
			 * Runs any actions before Generate Content has started.
			 *
			 * @since   3.0.7
			 *
			 * @param   int     $group_id   Group ID.
			 * @param   bool    $test_mode  Test Mode.
			 * @param   string  $system     System.
			 */
			do_action( 'page_generator_pro_generate_terms_before', $group_id, $test_mode, $system );
		} else {
			/**
			 * Runs any actions before Generate Content has started.
			 *
			 * @since   3.0.7
			 *
			 * @param   int     $group_id   Group ID.
			 * @param   bool    $test_mode  Test Mode.
			 * @param   string  $system     System.
			 */
			do_action( 'page_generator_pro_generate_content_before', $group_id, $test_mode, $system );
		}

		// Replace the Group's Number of Posts and Resume Index now, if specified in the call to this function.
		// If they're not specified, the Group's existing Number of Posts and Resume Index settings will be used.
		if ( $number_of_posts > 0 ) {
			$group['numberOfPosts'] = absint( $number_of_posts );
		}
		if ( $resume_index > 0 ) {
			$group['resumeIndex'] = absint( $resume_index );
		}

		// Calculate how many pages could be generated.
		$number_of_pages_to_generate = $this->get_max_number_of_pages( $group );
		if ( is_wp_error( $number_of_pages_to_generate ) ) {
			$error = new WP_Error(
				'page_generator_pro_generate',
				sprintf(
					/* translators: %1$s: Group ID, %2$s: Return message */
					__( 'Group ID #%1$s: %2$s', 'page-generator-pro' ),
					$group_id,
					$number_of_pages_to_generate->get_error_message()
				)
			);

			$this->add_to_debug_log( $error->get_error_message(), $system, true );
			$groups->stop_generation( $group_id );
			return $error;
		}

		// If no limit specified, set one now.
		if ( empty( $group['numberOfPosts'] ) ) {
			if ( $group['method'] === 'random' ) {
				$group['numberOfPosts'] = 10;
			} else {
				$group['numberOfPosts'] = $number_of_pages_to_generate;
			}

			$this->add_to_debug_log(
				sprintf(
					/* translators: %1$s: Group ID, %2$s: Number of Posts */
					__( 'Group ID #%1$s: Setting Number of Posts = %2$s, as no limit specified in Group.', 'page-generator-pro' ),
					$group_id,
					$group['numberOfPosts']
				),
				$system
			);
		}

		// If the requested Number of Posts exceeds the Number of Pages that could be generated,
		// set Number of Posts to match the Number of Pages that could be generated.
		if ( $group['numberOfPosts'] > $number_of_pages_to_generate ) {
			$group['numberOfPosts'] = $number_of_pages_to_generate;

			$this->add_to_debug_log(
				sprintf(
					/* translators: %1$s: Group ID, %2$s: Number of Posts */
					__( 'Group ID #%1$s: Restricting Number of Posts = %2$s, as limit specified in Group exceeded the number of possible Pages that could be generated.', 'page-generator-pro' ),
					$group_id,
					$number_of_pages_to_generate
				),
				$system
			);
		}

		// Add Plugin Settings.
		$group['stop_on_error']       = (int) $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-generate', 'stop_on_error', 0 );
		$group['stop_on_error_pause'] = (int) $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-generate', 'stop_on_error_pause', 5 );

		// Set last generated post date and time based on the Group's settings (i.e. date/time of now,
		// specific date/time or a random date/time).
		$last_generated_post_date_time = $this->post_date( $group );

		// Set a flag to denote that this Group is generating content.
		$this->add_to_debug_log(
			sprintf(
				/* translators: %1$s: Group ID, %2$s: System (browser,cron,cli) */
				__( 'Group ID #%1$s: Setting Group Status = Generating via %2$s.', 'page-generator-pro' ),
				$group_id,
				$system
			),
			$system
		);
		$groups->start_generation( $group_id, 'generating', $system );

		// Get first admin user.
		$users   = get_users(
			array(
				'role' => 'administrator',
			)
		);
		$user_id = $users[0]->ID;

		// Run a loop to generate each page.
		for ( $i = $group['resumeIndex']; $i < ( $group['numberOfPosts'] + $group['resumeIndex'] ); $i++ ) {

			// Set a sensible timeout to minimise timeouts for this generation iteration.
			// This is higher since the OpenAI Dynamic Element was introduced in 4.1.3,
			// as a Content Group might have multiple OpenAI Dynamic Elements specified,
			// each taking a minute or two.
			set_time_limit( MINUTE_IN_SECONDS * 5 );

			// Clear cache every ~ 100 Pages/Terms.
			if ( $i % 100 === 0 ) {
				wp_cache_flush();
			}

			// If cancel generation was requested, exit now.
			if ( $groups->cancel_generation_requested( $group_id ) ) {
				$groups->stop_generation( $group_id );
				$error = new WP_Error( 'page_generator_pro_generate', 'Group ID #' . $group_id . ': Generation cancelled by User' );
				$this->add_to_debug_log( $error->get_error_message(), $system, true );

				return $error;
			}

			// For Generate via Server, set current User to an Admin, so that the unfiltered_html capability is enabled,
			// allowing generation to perform the same way as if run through the browser or CLI
			// (i.e. iframes are permitted).
			if ( $system === 'cron' ) {
				if ( ! function_exists( 'wp_set_current_user' ) ) {
					include_once ABSPATH . 'wp-includes/pluggable.php';
				}
				wp_set_current_user( $user_id );
			}

			// Run.
			switch ( $type ) {
				case 'term':
					$result = $this->generate_term( $group_id, $i, $test_mode, $system );
					break;

				default:
					$result = $this->generate_content( $group_id, $i, $test_mode, $system, $last_generated_post_date_time );
					break;
			}

			// For Generate via Server, set current User to nothing, so that the unfiltered_html capability is disabled.
			if ( $system === 'cron' ) {
				wp_set_current_user( 0 );
			}

			// If an error occured, output it and then stop or continue, depending on the Stop on Error setting.
			if ( is_wp_error( $result ) ) {
				// Build error message.
				$message = sprintf(
					/* translators: %1$s: Group ID, %2$s: Index Number, %3$s: Total Number, %4$s: Result message */
					__( 'Group ID #%1$s: %2$s/%3$s: %4$s', 'page-generator-pro' ),
					$group_id,
					( $i + 1 ),
					$group['numberOfPosts'],
					implode( "\n", $result->get_error_messages() )
				);

				switch ( $group['stop_on_error'] ) {
					// Stop.
					case 1:
						// Log error.
						$this->add_to_debug_log( $message, $system, true );

						// Stop Generation.
						$groups->stop_generation( $group_id );

						// Exit.
						return new WP_Error( 'page_generator_pro_generate', $message );

					// Continue, attempting to regenerate the Term again.
					case 0:
						// Decrement index so we regenerate this Term again.
						--$i;

						// Append error message.
						$message .= sprintf(
							/* translators: Number of seconds */
							__( '. Waiting %s seconds before attempting to regenerate this item.', 'page-generator-pro' ),
							$group['stop_on_error_pause']
						);
						break;

					// Continue, skipping the failed Term.
					case -1:
						// Append error message.
						$message .= sprintf(
							/* translators: Number of seconds */
							__( '. Waiting %s seconds before generating the next item.', 'page-generator-pro' ),
							$group['stop_on_error_pause']
						);
						break;
				}

				// Log error.
				$this->add_to_debug_log( $message, $system, true );

				// Pause for the required number of seconds before resuming.
				sleep( $group['stop_on_error_pause'] );

				// Continue execution.
				continue;
			}

			// Update last generated post date and time, which will be used on the next iteration.
			$last_generated_post_date_time = $result['last_generated_post_date_time'];

			// Build message and output.
			$message = array(
				sprintf(
					/* translators: %1$s: Group ID, %2$s: Index Number, %3$s: Total Number, %4$s: Result message, %5$s: Permalink / URL, %6$s: Time taken to generate, in seconds, %7$s: Memory Usage, in MB, %8$s: Peak Memory Usage, in MB */
					__( 'Group ID #%1$s: %2$s/%3$s: %4$s. Permalink: %5$s. Time: %6$s seconds. Memory Usage / Peak: %7$s/%8$sMB', 'page-generator-pro' ),
					$group_id,
					( $i + 1 ),
					( $group['numberOfPosts'] + $group['resumeIndex'] ),
					$result['message'],
					$result['url'],
					$result['duration'],
					$result['memory_usage'],
					$result['memory_peak_usage']
				),
			);
			foreach ( $result['keywords_terms'] as $keyword => $term ) {
				// Strip HTML tags for log output.
				$term = wp_strip_all_tags( $term );

				$message[] = '{' . $keyword . '}: ' . ( strlen( $term ) > 50 ? substr( $term, 0, 50 ) . '...' : $term );
			}
			$message[] = '--';

			// Output log.
			$this->add_to_debug_log( implode( "\n", $message ), $system );
		}

		// Stop generation.
		$groups->stop_generation( $group_id );

		if ( $type === 'term' ) {
			/**
			 * Runs any actions after Generate Terms has finished.
			 *
			 * @since   3.0.7
			 *
			 * @param   int     $group_id   Group ID.
			 * @param   bool    $test_mode  Test Mode.
			 * @param   string  $system     System.
			 */
			do_action( 'page_generator_pro_generate_terms_after', $group_id, $test_mode, $system );
		} else {
			/**
			 * Runs any actions after Generate Content has finished.
			 *
			 * @since   3.0.7
			 *
			 * @param   int     $group_id   Group ID.
			 * @param   bool    $test_mode  Test Mode.
			 * @param   string  $system     System.
			 */
			do_action( 'page_generator_pro_generate_content_after', $group_id, $test_mode, $system );
		}

		$this->add_to_debug_log(
			sprintf(
				/* translators: Group ID */
				__( 'Group ID #%s: Finished', 'page-generator-pro' ),
				$group_id
			),
			$system
		);

		return true;

	}

	/**
	 * Calculates the maximum number of items that will be generated based
	 * on the settings.
	 *
	 * @since   1.1.5
	 *
	 * @param   array $settings   Group Settings (either a Content or Term Group).
	 * @return  WP_Error|int
	 */
	public function get_max_number_of_pages( $settings ) {

		// Remove some settings that we don't want to be spun/have keywords replaced on,
		// as they're for a subsection i.e. Comment Generation.
		unset( $settings['comments_generate'] );

		// Build a class array of required keywords that need replacing with data.
		$required_keywords = $this->find_keywords_in_settings( $settings );

		// Bail if no keywords were found.
		if ( count( $required_keywords['required_keywords'] ) === 0 ) {
			return 0;
		}

		// Update Keywords that don't use a local source now.
		$result = $this->base->get_class( 'keywords' )->refresh_terms( $required_keywords['required_keywords'] );

		// If an error occured refreshing Keywords, bail.
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Get the terms for each required keyword.
		$this->keywords = $this->get_keywords_terms_columns_delimiters( $required_keywords['required_keywords'] );

		// Bail if no keywords were found.
		if ( empty( $this->keywords['terms'] ) ) {
			return 0;
		}

		// Depending on the generation method chosen, for each keyword, define the term
		// that will replace it.
		switch ( $settings['method'] ) {

			/**
			 * All
			 * Random
			 * - Generates all possible term combinations across keywords
			 */
			case 'all':
			case 'random':
				$total = 1;
				foreach ( $this->keywords['terms'] as $keyword => $terms ) {
					$total = ( $total * count( $terms ) );
				}

				return $total;

			/**
			 * Sequential
			 * - Generates term combinations across keywords matched by index
			 */
			case 'sequential':
				$total = 0;
				foreach ( $this->keywords['terms'] as $keyword => $terms ) {
					if ( count( $terms ) > 0 && ( count( $terms ) < $total || $total === 0 ) ) {
						$total = count( $terms );
					}
				}

				return $total;

		}

		return 0;

	}

	/**
	 * Generates a Page, Post or Custom Post Type for the given Group and Index
	 *
	 * @since   1.6.1
	 *
	 * @param   int         $group_id                       Group ID.
	 * @param   int         $index                          Keyword Index.
	 * @param   bool        $test_mode                      Test Mode.
	 * @param   string      $system                         System (browser|cron|cli).
	 * @param   bool|string $last_generated_post_date_time  Last Generated Post's Date and Time.
	 * @return  WP_Error|array
	 */
	public function generate_content( $group_id, $index = 0, $test_mode = false, $system = 'browser', $last_generated_post_date_time = false ) {

		// Performance debugging.
		$start = ( function_exists( 'hrtime' ) ? hrtime( true ) : microtime( true ) );

		// Clear any Dynamic Element errors from the last generated index.
		$this->clear_dynamic_element_errors();

		// Define the Group ID and Index as globals, so it can be picked up by our shortcodes when they're processed.
		global $page_generator_pro_group_id, $page_generator_pro_index;
		$page_generator_pro_group_id = $group_id;
		$page_generator_pro_index    = $index;

		// If test mode is enabled, set the debug constant.
		if ( $test_mode && ! defined( 'PAGE_GENERATOR_PRO_DEBUG' ) ) {
			define( 'PAGE_GENERATOR_PRO_DEBUG', true );
		}

		// Get group settings.
		$settings = $this->base->get_class( 'groups' )->get_settings( $group_id, false, true );

		// Bail if an error occured.
		if ( is_wp_error( $settings ) ) {
			return $settings;
		}

		/**
		 * Modify the Group's settings prior to starting the generation routine.
		 *
		 * Changes made only affect this item in the generation set, and are not persistent or saved.
		 *
		 * For Gutenberg and Page Builders with Blocks / Elements registered by this Plugin, this
		 * is a good time to convert JSON encoded strings into arrays/objects that can be iterated
		 * for better Keyword/Spintax detection.
		 *
		 * @since   3.7.8
		 *
		 * @param   array   $settings       Group Settings.
		 * @param   int     $group_id       Group ID.
		 * @param   int     $index          Keyword Index.
		 * @param   bool    $test_mode      Test Mode.
		 */
		$settings = apply_filters( 'page_generator_pro_generate_content_settings_before', $settings, $group_id, $index, $test_mode );

		// Remove some settings that we don't want to be spun/have keywords replaced on,
		// as they're for a subsection i.e. Comment Generation.
		$original_settings = $settings;
		unset( $settings['comments_generate'] );

		// If this Group has a request to cancel generation, exit.
		if ( ! $test_mode ) {
			if ( $this->base->get_class( 'groups' )->cancel_generation_requested( $group_id ) ) {
				// Stop Generation.
				$this->base->get_class( 'groups' )->stop_generation( $group_id );

				// Return error.
				return $this->generate_error_return(
					new WP_Error( 'generation_error', __( 'A request to cancel generation was made by the User. Exiting...', 'page-generator-pro' ) ),
					$group_id,
					0,
					$settings['type'],
					$test_mode,
					$system,
					false
				);
			}
		}

		// If the Group is not published, generation might fail in Gutenberg stating that no keywords could be found
		// in the Content. Change its status to published.
		if ( ! in_array( get_post_status( $group_id ), array_keys( $this->base->get_class( 'groups' )->get_group_statuses() ), true ) ) {
			$result = wp_update_post(
				array(
					'ID'          => $group_id,
					'post_status' => 'publish',
				),
				true
			);

			if ( is_wp_error( $result ) ) {
				// Return error.
				return $this->generate_error_return(
					$result,
					$group_id,
					0,
					$settings['type'],
					$test_mode,
					$system,
					false
				);
			}
		}

		// Validate group.
		$validated = $this->base->get_class( 'groups' )->validate( $group_id );
		if ( is_wp_error( $validated ) ) {
			return $this->generate_error_return(
				$validated,
				$group_id,
				0,
				$settings['type'],
				$test_mode,
				$system,
				false
			);
		}

		/**
		 * Run any actions before an individual Page, Post or Custom Post Type is generated
		 *
		 * @since   2.4.1
		 *
		 * @param   int     $group_id       Group ID.
		 * @param   array   $settings       Group Settings.
		 * @param   int     $index          Keyword Index.
		 * @param   bool    $test_mode      Test Mode
		 */
		do_action( 'page_generator_pro_generate_content_started', $group_id, $settings, $index, $test_mode );

		// Build a class array of required keywords that need replacing with data.
		$required_keywords = $this->find_keywords_in_settings( $settings );
		if ( count( $required_keywords['required_keywords'] ) === 0 ) {
			return $this->generate_error_return(
				new WP_Error( 'keyword_error', __( 'No keywords were specified in the Group.', 'page-generator-pro' ) ),
				$group_id,
				0,
				$settings['type'],
				$test_mode,
				$system,
				false
			);
		}

		// If we're in Test Mode, update Keywords that don't use a local source now.
		if ( $test_mode ) {
			$result = $this->base->get_class( 'keywords' )->refresh_terms( $required_keywords['required_keywords'] );

			// If an error occured refreshing Keywords, bail.
			if ( is_wp_error( $result ) ) {
				return $this->generate_error_return(
					$result,
					$group_id,
					0,
					$settings['type'],
					$test_mode,
					$system,
					false
				);
			}
		}

		// Build a keywords array comprising of terms, columns and delimiters for each of the required keywords.
		$this->keywords = $this->get_keywords_terms_columns_delimiters( $required_keywords['required_keywords'] );
		if ( count( $this->keywords['terms'] ) === 0 ) {
			return $this->generate_error_return(
				new WP_Error( 'keyword_error', __( 'Keywords were specified in the Group, but no keywords exist in either the Keywords section of the Plugin or as a Taxonomy.', 'page-generator-pro' ) ),
				$group_id,
				0,
				$settings['type'],
				$test_mode,
				$system,
				false
			);
		}

		// Build array of keyword --> term key/value pairs to use for this generation.
		$keywords_terms = $this->get_keywords_terms( $settings['method'], (int) $index );
		if ( is_wp_error( $keywords_terms ) ) {
			return $this->generate_error_return(
				$keywords_terms,
				$group_id,
				0,
				$settings['type'],
				$test_mode,
				$system,
				false
			);
		}

		// Rotate Author.
		if ( isset( $settings['rotateAuthors'] ) ) {
			$author_ids = $this->base->get_class( 'common' )->get_all_user_ids();
			$user_index = wp_rand( 0, ( count( $author_ids ) - 1 ) );
		}

		// Define whether we'll process shortcodes on the Post Content
		// Some Page Builders will mean we won't do this, such as Elementor, which don't use
		// the Post Content for output.
		$this->process_shortcodes_on_post_content = $this->should_process_shortcodes_on_post_content( $settings );

		// Remove all shortcode processors, so we don't process any shortcodes. This ensures page builders, galleries etc
		// will work as their shortcodes will be processed when the generated page is viewed.
		remove_all_shortcodes();

		// Add Page Generator Pro's shortcodes, so they're processed now (true = we want to register shortcodes that need processing into HTML).
		$this->base->get_class( 'shortcode' )->add_shortcodes( true );

		// Iterate through each detected Keyword to build a full $this->searches and $this->replacements arrays.
		$this->build_search_replace_arrays( $required_keywords['required_keywords_full'], $keywords_terms );

		// Determine if a Latitude and Longitude exist (either a Keyword or actual value).
		$has_lat_lng = false;
		if ( ! empty( $settings['latitude'] ) && ! empty( $settings['longitude'] ) ) {
			$has_lat_lng = true;
		}

		// Iterate through each keyword and term key/value pair.
		$settings = $this->replace_keywords( $settings );

		// Define Post Name / Slug.
		// If no Permalink exists, use the Post Title.
		if ( ! empty( $settings['permalink'] ) ) {
			$post_name = sanitize_title( $settings['permalink'] );
		} else {
			$post_name = sanitize_title( $settings['title'] );
		}

		// If the Permalink is empty, return an error.
		if ( empty( $post_name ) ) {
			return $this->generate_error_return(
				new WP_Error(
					'page_generator_pro_generate_content_permalink_error',
					__( 'The Permalink specified is empty. This is typically due to either the Title and Permalink settings empty, or the Permalink using a Keyword with an empty Term.', 'page-generator-pro' )
				),
				$group_id,
				0,
				$settings['type'],
				$test_mode,
				$system,
				$keywords_terms
			);
		}

		// Determine the Post Parent.
		$post_parent = $this->get_post_parent( $group_id, $settings );
		if ( is_wp_error( $post_parent ) ) {
			return $this->generate_error_return(
				$post_parent,
				$group_id,
				0,
				$settings['type'],
				$test_mode,
				$system,
				$keywords_terms
			);
		}

		// Depending on the Ovewrite setting, check if an existing Post exists.
		switch ( $settings['overwrite'] ) {
			/**
			 * No, skip if existing Page generated by this Group
			 */
			case 'skip_if_exists':
				// Find existing Post by Permalink generated by this Group.
				$existing_post_id = $this->post_exists( $group_id, $settings['type'], $post_parent, $post_name );

				// Bail if a Post is found, as we're skipping Generation.
				if ( $existing_post_id > 0 ) {
					return $this->generate_return(
						$group_id,
						$existing_post_id,
						$settings['type'],
						false,
						sprintf(
							/* translators: Post Type or Taxonomy Name */
							__( 'Skipped, as %s with Permalink already generated by this Group', 'page-generator-pro' ),
							$settings['type']
						),
						$start,
						$test_mode,
						$system,
						$keywords_terms,
						get_the_date( 'Y-m-d H:i:s', $existing_post_id ) // Set last generated date/time to the existing post's date time.
					);
				}
				break;

			/**
			 * No, skip if existing Page exists
			 */
			case 'skip_if_exists_any':
				// Find existing Post by Permalink, regardless of Group.
				$existing_post_id = $this->post_exists( 0, $settings['type'], $post_parent, $post_name );

				// Bail if a Post is found, as we're skipping Generation.
				if ( $existing_post_id > 0 ) {
					return $this->generate_return(
						$group_id,
						$existing_post_id,
						$settings['type'],
						false,
						sprintf(
							/* translators: Post Type or Taxonomy Name */
							__( 'Skipped, as %s with Permalink already exists in WordPress', 'page-generator-pro' ),
							$settings['type']
						),
						$start,
						$test_mode,
						$system,
						$keywords_terms,
						get_the_date( 'Y-m-d H:i:s', $existing_post_id ) // Set last generated date/time to the existing post's date time.
					);
				}
				break;

			/**
			 * Yes, if existing Page generated by this Group
			 * Yes, if existing Page generated by this Group, preserving original Publish date
			 */
			case 'overwrite':
			case 'overwrite_preseve_date':
				// Try to find existing post.
				$existing_post_id = $this->post_exists( $group_id, $settings['type'], $post_parent, $post_name );

				// If no existing post found, this will not be an overwrite option, so break.
				if ( ! $existing_post_id ) {
					break;
				}

				// This will overwrite an existing post, so remove settings from the Group that are NOT to be overwritten now.
				$settings = $this->remove_settings( $settings );
				break;

			/**
			 * Yes, if existing Page exists
			 * Yes, if existing Page exists, preserving original Publish date
			 */
			case 'overwrite_any':
			case 'overwrite_any_preseve_date':
				// Try to find existing post.
				$existing_post_id = $this->post_exists( 0, $settings['type'], $post_parent, $post_name );

				// If no existing post found, this will not be an overwrite option, so break.
				if ( ! $existing_post_id ) {
					break;
				}

				// This will overwrite an existing post, so remove settings from the Group that are NOT to be overwritten now.
				$settings = $this->remove_settings( $settings );
				break;
		}

		/**
		 * Modify the Group's settings prior to parsing shortcodes and building the Post Arguments
		 * to use for generating a single Page, Post or Custom Post Type.
		 *
		 * Changes made only affect this item in the generation set, and are not persistent or saved.
		 *
		 * For Gutenberg and Page Builders with Blocks / Elements registered by this Plugin, this
		 * is a good time to convert them to a Shortcode Block / Element / Text
		 *
		 * @since   2.6.0
		 *
		 * @param   array   $settings       Group Settings.
		 * @param   int     $group_id       Group ID.
		 * @param   int     $index          Keyword Index.
		 * @param   bool    $test_mode      Test Mode.
		 */
		$settings = apply_filters( 'page_generator_pro_generate_content_settings', $settings, $group_id, $index, $test_mode );

		// Process Dynamic Elements.
		// Blocks above that have been converted into Shortcodes will now be processed.
		array_walk_recursive( $settings, array( $this, 'process_shortcodes_in_array' ) );

		// If error(s) occured in Dynamic Element's render() functions, exit.
		if ( is_wp_error( $this->dynamic_element_errors ) ) {
			return $this->generate_error_return(
				$this->dynamic_element_errors,
				$group_id,
				0,
				$settings['type'],
				$test_mode,
				$system,
				$keywords_terms
			);
		}

		// Build Post args.
		$post_args = array(
			'post_type'      => $settings['type'],
			'post_title'     => $settings['title'],
			'post_content'   => $settings['content'],
			'post_status'    => ( $test_mode ? 'draft' : $settings['status'] ),
			'post_author'    => ( ( isset( $settings['rotateAuthors'] ) && $settings['rotateAuthors'] == 1 && isset( $author_ids ) && isset( $user_index ) ) ? $author_ids[ $user_index ] : $settings['author'] ), // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual
			'comment_status' => ( ( isset( $settings['comments'] ) && $settings['comments'] == 1 ) ? 'open' : 'closed' ), // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual
			'ping_status'    => ( ( isset( $settings['trackbacks'] ) && $settings['trackbacks'] == 1 ) ? 'open' : 'closed' ), // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual
			'post_parent'    => $post_parent,
			'post_name'      => $post_name,
			'post_date'      => $this->post_date( $settings, $last_generated_post_date_time ),
		);

		// Define Post Excerpt, if the Post Type supports it.
		if ( post_type_supports( $settings['type'], 'excerpt' ) ) {
			$post_args['post_excerpt'] = $settings['excerpt'];
		}

		/**
		 * Filters arguments used for creating or updating a Post when running
		 * content generation.
		 *
		 * @since   1.6.1
		 *
		 * @param   array   $post_args  wp_insert_post() / wp_update_post() compatible arguments.
		 * @param   array   $settings   Content Group Settings.
		 */
		$post_args = apply_filters( 'page_generator_pro_generate_post_args', $post_args, $settings );

		/**
		 * Run any actions immediately before an individual Page, Post or Custom Post Type is generated.
		 *
		 * @since   2.4.1
		 *
		 * @param   int     $group_id       Group ID.
		 * @param   array   $settings       Group Settings.
		 * @param   int     $index          Keyword Index.
		 * @param   bool    $test_mode      Test Mode.
		 */
		do_action( 'page_generator_pro_generate_content_before_insert_update_post', $group_id, $settings, $index, $test_mode );

		// Create or Update a Post.
		switch ( $settings['overwrite'] ) {

			/**
			 * Overwrite
			 */
			case 'overwrite':
				// If a Post was found, update it.
				if ( isset( $existing_post_id ) && $existing_post_id > 0 ) {
					// Define the Post ID to update.
					$post_args['ID'] = $existing_post_id;

					// Remove Post Args that we're not overwriting.
					$post_args = $this->restrict_post_args_by_overwrite_sections( array_keys( $settings['overwrite_sections'] ), $post_args );

					// Delete Attachments assigned to the existing Post ID created by this Group if we're overwriting the Content.
					if ( array_key_exists( 'post_content', $settings['overwrite_sections'] ) ) {
						$this->delete_attachments_by_post_ids( array( $existing_post_id ), $group_id );
					}

					// Delete Featured Image assigned to the existing Post ID created by this Group if we're overwriting the Featured Image.
					if ( array_key_exists( 'featured_image', $settings['overwrite_sections'] ) ) {
						$this->delete_featured_image_by_post_ids( array( $existing_post_id ), $group_id );
					}

					// Update Page, Post or CPT.
					$post_id = wp_update_post( $post_args, true );

					// Define return message.
					$log = sprintf(
						/* translators: Post Type Name */
						__( 'Updated, as %s with Permalink already generated by this Group', 'page-generator-pro' ),
						$settings['type']
					);
				} else {
					// Create Page, Post or CPT.
					$post_id = wp_insert_post( $post_args, true );

					// Define return message.
					$log = sprintf(
						/* translators: Post Type Name */
						__( 'Created, as %s with Permalink has not yet been generated by this Group', 'page-generator-pro' ),
						$settings['type']
					);
				}
				break;

			/**
			 * Overwrite Any
			 */
			case 'overwrite_any':
				// If a Post was found, update it.
				if ( isset( $existing_post_id ) && $existing_post_id > 0 ) {
					// Define the Post ID to update.
					$post_args['ID'] = $existing_post_id;

					// Remove Post Args that we're not overwriting.
					$post_args = $this->restrict_post_args_by_overwrite_sections( array_keys( $settings['overwrite_sections'] ), $post_args );

					// Delete Attachments assigned to the existing Post ID created by this Group if we're overwriting the Content.
					if ( array_key_exists( 'post_content', $settings['overwrite_sections'] ) ) {
						$this->delete_attachments_by_post_ids( array( $existing_post_id ), $group_id );
					}

					// Delete Featured Image assigned to the existing Post ID created by this Group if we're overwriting the Featured Image.
					if ( array_key_exists( 'featured_image', $settings['overwrite_sections'] ) ) {
						$this->delete_featured_image_by_post_ids( array( $existing_post_id ), $group_id );
					}

					// Update Page, Post or CPT.
					$post_id = wp_update_post( $post_args, true );

					// Define return message.
					$log = sprintf(
						/* translators: Post Type or Taxonomy Name */
						__( 'Updated, as %s with Permalink already exists in WordPress', 'page-generator-pro' ),
						$settings['type']
					);
				} else {
					// Create Page, Post or CPT.
					$post_id = wp_insert_post( $post_args, true );

					// Define return message.
					$log = sprintf(
						/* translators: Post Type or Taxonomy Name */
						__( 'Created, as %s with Permalink does not exist in WordPress', 'page-generator-pro' ),
						$settings['type']
					);
				}
				break;

			/**
			 * Don't Overwrite
			 */
			default:
				// Create Page, Post or CPT.
				$post_id = wp_insert_post( $post_args, true );

				// Define return message.
				$log = sprintf(
					__( 'Created', 'page-generator-pro' ),
					$settings['type']
				);
				break;

		}

		// Check Post creation / update worked.
		if ( is_wp_error( $post_id ) ) {
			// Fetch error codes when trying to insert / update the Post.
			$error_codes = $post_id->get_error_codes();

			// Ignore invalid_page_template errors.  wp_update_post() adds the existing page_template
			// parameter to $post_args before passing onto wp_insert_post(); however the template
			// might belong to a Page Builder Template that has / will not register the template with
			// the active Theme.
			// We manually assign _wp_page_template later on in this process, so we can safely ignore
			// this error.
			if ( count( $error_codes ) === 1 && $error_codes[0] === 'invalid_page_template' ) {
				// The Post ID will be the existing Post ID we just updated.
				$post_id = ( isset( $existing_post_id ) ? $existing_post_id : 0 );
			} else {
				// UTF-8 encode the Title, Excerpt and Content.
				$post_args['post_title']   = mb_convert_encoding( $post_args['post_title'], 'UTF-8', mb_list_encodings() );
				$post_args['post_content'] = mb_convert_encoding( $post_args['post_content'], 'UTF-8', mb_list_encodings() );
				if ( post_type_supports( $settings['type'], 'excerpt' ) ) {
					$post_args['post_excerpt'] = mb_convert_encoding( $post_args['post_excerpt'], 'UTF-8', mb_list_encodings() );
				}

				// Try again.
				if ( isset( $post_args['ID'] ) ) {
					// Remove Post Args that we're not overwriting.
					$post_args = $this->restrict_post_args_by_overwrite_sections( array_keys( $settings['overwrite_sections'] ), $post_args );

					// Update Page, Post or CPT.
					$post_id = wp_update_post( $post_args, true );
				} else {
					// Create Page, Post or CPT.
					$post_id = wp_insert_post( $post_args, true );
				}

				// If Post creation / update still didn't work, bail.
				if ( is_wp_error( $post_id ) ) {
					$post_id->add_data( $post_args, $post_id->get_error_code() );

					return $this->generate_error_return(
						$post_id,
						$group_id,
						0,
						$settings['type'],
						$test_mode,
						$system,
						$keywords_terms
					);
				}
			}
		}

		/**
		 * Run any actions immediately after an individual Page, Post or Custom Post Type is generated, but before
		 * its Page Template, Featured Image, Custom Fields, Post Meta, Geodata or Taxonomy Terms have been assigned.
		 *
		 * @since   2.4.1
		 *
		 * @param   int     $post_id        Post ID.
		 * @param   int     $group_id       Group ID.
		 * @param   array   $settings       Group Settings.
		 * @param   int     $index          Keyword Index.
		 * @param   bool    $test_mode      Test Mode.
		 */
		do_action( 'page_generator_pro_generate_content_after_insert_update_post', $post_id, $group_id, $settings, $index, $test_mode );

		// Store this Group ID and Index in the Post's meta, so we can edit/delete the generated Post(s) in the future.
		update_post_meta( $post_id, '_page_generator_pro_group', $group_id );
		update_post_meta( $post_id, '_page_generator_pro_index', $index );

		// Assign Attachments that may have been created by shortcode processing to the generated Post.
		// We do this here as shortcodes are processed before a Post is generated, therefore any Attachments
		// created won't have a Post ID.
		$result = $this->assign_attachments_to_post_id( $post_id, $group_id, $index );
		if ( is_wp_error( $result ) && defined( 'PAGE_GENERATOR_PRO_DEBUG' ) && PAGE_GENERATOR_PRO_DEBUG === true ) {
			return $this->generate_error_return(
				$result,
				$group_id,
				$post_id,
				$settings['type'],
				$test_mode,
				$system,
				$keywords_terms
			);
		}

		// Store Page Template.
		$this->set_page_template( $post_id, $group_id, $settings, $post_args );

		// Store Header and Footer Code on the Generated Post.
		$this->set_header_footer_code( $post_id, $group_id, $settings, $post_args, $keywords_terms );

		// Store Post Meta (ACF, Yoast, Page Builder data etc) on the Generated Post.
		$this->set_post_meta( $post_id, $group_id, $settings, $post_args );

		// Store Custom Fields as Post Meta on the Generated Post.
		$this->set_custom_fields( $post_id, $group_id, $settings, $post_args, $keywords_terms );

		// Store Latitude and Longitude.
		if ( $has_lat_lng ) {
			$result = $this->latitude_longitude( $post_id, $group_id, $settings );

			if ( is_wp_error( $result ) && defined( 'PAGE_GENERATOR_PRO_DEBUG' ) && PAGE_GENERATOR_PRO_DEBUG === true ) {
				return $this->generate_error_return(
					$result,
					$group_id,
					$post_id,
					$settings['type'],
					$test_mode,
					$system,
					$keywords_terms
				);
			}
		}

		// Assign Generated Post to Menu, if required and we're not in Test Mode.
		if ( ! $test_mode ) {
			$result = $this->set_menu( $post_id, $settings, $post_args );
			if ( is_wp_error( $result ) && defined( 'PAGE_GENERATOR_PRO_DEBUG' ) && PAGE_GENERATOR_PRO_DEBUG === true ) {
				return $this->generate_error_return(
					$result,
					$group_id,
					$post_id,
					$settings['type'],
					$test_mode,
					$system,
					$keywords_terms
				);
			}
		}

		// Assign Taxonomy Terms to the Generated Post.
		$result = $this->assign_taxonomy_terms_to_post( $post_id, $settings, $post_args );
		if ( is_wp_error( $result ) && defined( 'PAGE_GENERATOR_PRO_DEBUG' ) && PAGE_GENERATOR_PRO_DEBUG === true ) {
			return $this->generate_error_return(
				$result,
				$group_id,
				$post_id,
				$settings['type'],
				$test_mode,
				$system,
				$keywords_terms
			);
		}

		// Featured Image.
		// We do this last so that Page Builders e.g. Divi don't overwrite the _thumbnail_id after Custom Field / Post Meta copying.
		$image_id = $this->featured_image( $post_id, $group_id, $index, $settings, $post_args );
		if ( is_wp_error( $image_id ) && defined( 'PAGE_GENERATOR_PRO_DEBUG' ) && PAGE_GENERATOR_PRO_DEBUG === true ) {
			return $this->generate_error_return(
				$image_id,
				$group_id,
				$post_id,
				$settings['type'],
				$test_mode,
				$system,
				$keywords_terms
			);
		}

		// Generate Comments.
		$result = $this->generate_comments( $post_id, $group_id, $index, $original_settings, $post_args );
		if ( is_wp_error( $result ) && defined( 'PAGE_GENERATOR_PRO_DEBUG' ) && PAGE_GENERATOR_PRO_DEBUG === true ) {
			return $this->generate_error_return(
				$result,
				$group_id,
				$post_id,
				$settings['type'],
				$test_mode,
				$system,
				$keywords_terms
			);
		}

		// Request that the user review the Plugin, if we're not in Test Mode. Notification displayed later,
		// can be called multiple times and won't re-display the notification if dismissed.
		if ( ! $test_mode && ! $this->base->licensing->has_feature( 'whitelabelling' ) ) {
			$this->base->dashboard->request_review();
		}

		// Store current index as the last index generated for this Group, if we're not in test mode.
		if ( ! $test_mode ) {
			$this->base->get_class( 'groups' )->update_last_index_generated( $group_id, $index );
		}

		/**
		 * Run any actions after an individual Page, Post or Custom Post Type is generated
		 * successfully.
		 *
		 * @since   2.4.1
		 *
		 * @param   int     $post_id        Generated Post ID.
		 * @param   int     $group_id       Group ID.
		 * @param   array   $settings       Group Settings.
		 * @param   int     $index          Keyword Index.
		 * @param   bool    $test_mode      Test Mode.
		 * @param   array   $post_args      wp_insert_post() / wp_update_post() compatible arguments.
		 */
		do_action( 'page_generator_pro_generate_content_finished', $post_id, $group_id, $settings, $index, $test_mode, $post_args );

		// Get Generated Post's Date.
		// We don't use $post_args['post_date'] as it might not be set if overwriting dates are disabled.
		$post_date = get_the_date( 'Y-m-d H:i:s', $post_id );

		// Return success data.
		return $this->generate_return( $group_id, $post_id, $settings['type'], true, $log, $start, $test_mode, $system, $keywords_terms, $post_date );

	}

	/**
	 * Generates a Taxonomy Term for the given Group and Index
	 *
	 * @since   1.0.0
	 *
	 * @param   int    $group_id   Group ID.
	 * @param   int    $index      Keyword Index.
	 * @param   bool   $test_mode  Test Mode.
	 * @param   string $system     System (browser|cron|cli).
	 * @return  WP_Error|array
	 */
	public function generate_term( $group_id, $index, $test_mode = false, $system = 'browser' ) {

		// Performance debugging.
		$start = ( function_exists( 'hrtime' ) ? hrtime( true ) : microtime( true ) );

		// Clear any Dynamic Element errors from the last generated index.
		$this->clear_dynamic_element_errors();

		// If test mode is enabled, set the debug constant.
		if ( $test_mode && ! defined( 'PAGE_GENERATOR_PRO_DEBUG' ) ) {
			define( 'PAGE_GENERATOR_PRO_DEBUG', true );
		}

		// If this Group has a request to cancel generation, exit.
		if ( ! $test_mode ) {
			if ( $this->base->get_class( 'groups_terms' )->cancel_generation_requested( $group_id ) ) {
				$this->base->get_class( 'groups_terms' )->stop_generation( $group_id );
				return new WP_Error( 'generation_error', __( 'A request to cancel generation was made by the User. Exiting...', 'page-generator-pro' ) );
			}
		}

		// Get group settings.
		$settings = $this->base->get_class( 'groups_terms' )->get_settings( $group_id, false, true );

		// Bail if an error occured.
		if ( is_wp_error( $settings ) ) {
			return $settings;
		}

		/**
		 * Run any actions before an individual Term is generated successfully.
		 *
		 * @since   2.4.1
		 *
		 * @param   int     $group_id       Group ID.
		 * @param   array   $settings       Group Settings.
		 * @param   int     $index          Keyword Index.
		 * @param   bool    $test_mode      Test Mode.
		 */
		do_action( 'page_generator_pro_generate_term_started', $group_id, $settings, $index, $test_mode );

		// Validate group.
		$validated = $this->base->get_class( 'groups_terms' )->validate( $group_id );
		if ( is_wp_error( $validated ) ) {
			return $validated;
		}

		// Build a class array of required keywords that need replacing with data.
		$required_keywords = $this->find_keywords_in_settings( $settings );
		if ( count( $required_keywords['required_keywords'] ) === 0 ) {
			return new WP_Error( 'page_generator_pro_generate_generate_term_keyword_error', __( 'No keywords were specified in the Group.', 'page-generator-pro' ) );
		}

		// Build a keywords array comprising of terms, columns and delimiters for each of the required keywords.
		$this->keywords = $this->get_keywords_terms_columns_delimiters( $required_keywords['required_keywords'] );
		if ( count( $this->keywords['terms'] ) === 0 ) {
			return new WP_Error( 'page_generator_pro_generate_generate_term_keyword_error', __( 'Keywords were specified in the Group, but no keywords exist in either the Keywords section of the Plugin or as a Taxonomy.', 'page-generator-pro' ) );
		}

		// Build array of keyword --> term key/value pairs to use for this generation.
		$keywords_terms = $this->get_keywords_terms( $settings['method'], (int) $index );
		if ( is_wp_error( $keywords_terms ) ) {
			return $keywords_terms;
		}

		// Iterate through each detected Keyword to build a full $this->searches and $this->replacements arrays.
		$this->build_search_replace_arrays( $required_keywords['required_keywords_full'], $keywords_terms );

		// Iterate through each keyword and term key/value pair.
		$settings = $this->replace_keywords( $settings );

		// Build Term args.
		$term_args = array(
			'description' => $settings['excerpt'],
		);

		// Define Slug.
		// If no Permalink exists, use the Title.
		if ( ! empty( $settings['permalink'] ) ) {
			$term_args['slug'] = sanitize_title( $settings['permalink'] );
		}

		// If the taxonomy is hierarhical, and a parent term has been specified, determine its ID,
		// creating terms if necessary.
		$term_args['parent'] = 0;
		if ( is_taxonomy_hierarchical( $settings['taxonomy'] ) && ! empty( $settings['parent_term'] ) ) {
			$term_args['parent'] = $this->get_term_path_id( $settings['taxonomy'], $settings['parent_term'] );
		}

		/**
		 * Filters arguments used for creating or updating a Term when running
		 * content generation.
		 *
		 * @since   1.6.1
		 *
		 * @param   array   $term_args  wp_insert_term() / wp_update_term() compatible arguments.
		 * @param   array   $settings   Content Group Settings.
		 */
		$term_args = apply_filters( 'page_generator_pro_generate_term_args', $term_args, $settings );

		// Depending on the Overwrite setting, check if an existing Term exists.
		switch ( $settings['overwrite'] ) {
			/**
			 * No, skip if existing Term generated by this Group
			 */
			case 'skip_if_exists':
				// Find existing Term by Permalink generated by this Group.
				$existing_term_id = $this->term_exists( $group_id, $settings['taxonomy'], $term_args['parent'], $settings['title'] );

				// Bail if a Term is found, as we're skipping Generation.
				if ( $existing_term_id > 0 ) {
					return $this->generate_return(
						$group_id,
						$existing_term_id,
						$settings['taxonomy'],
						false,
						sprintf(
							/* translators: Post Type or Taxonomy Name */
							__( 'Skipped, as %s with Permalink already generated by this Group', 'page-generator-pro' ),
							$settings['taxonomy']
						),
						$start,
						$test_mode,
						$system,
						$keywords_terms
					);
				}
				break;

			/**
			 * No, skip if existing Term exists
			 */
			case 'skip_if_exists_any':
				// Find existing Post by Permalink, regardless of Group.
				$existing_term_id = $this->term_exists( 0, $settings['taxonomy'], $term_args['parent'], $settings['title'] );

				// Bail if a Post is found, as we're skipping Generation.
				if ( $existing_term_id > 0 ) {
					return $this->generate_return(
						$group_id,
						$existing_term_id,
						$settings['taxonomy'],
						false,
						sprintf(
							/* translators: Post Type or Taxonomy Name */
							__( 'Skipped, as %s with Permalink already exists in WordPress', 'page-generator-pro' ),
							$settings['taxonomy']
						),
						$start,
						$test_mode,
						$system,
						$keywords_terms
					);
				}
				break;

			/**
			 * Yes, if existing Term generated by this Group
			 */
			case 'overwrite':
				// Try to find existing term.
				$existing_term_id = $this->term_exists( $group_id, $settings['taxonomy'], $term_args['parent'], $settings['title'] );
				break;

			/**
			 * Yes, if existing Term exists
			 */
			case 'overwrite_any':
				// Try to find existing post.
				$existing_term_id = $this->term_exists( 0, $settings['taxonomy'], $term_args['parent'], $settings['title'] );
				break;
		}

		/**
		 * Run any actions immediately before an individual Term is generated.
		 *
		 * @since   2.4.1
		 *
		 * @param   int     $group_id       Group ID.
		 * @param   array   $settings       Group Settings.
		 * @param   int     $index          Keyword Index.
		 * @param   bool    $test_mode      Test Mode.
		 */
		do_action( 'page_generator_pro_generate_term_before_insert_update_term', $group_id, $settings, $index, $test_mode );

		// Create or Update a Term.
		switch ( $settings['overwrite'] ) {

			/**
			 * Overwrite
			 */
			case 'overwrite':
				// If a Term was found, update it.
				if ( isset( $existing_term_id ) && $existing_term_id > 0 ) {
					// Update Term.
					$term = wp_update_term( $existing_term_id, $settings['taxonomy'], $term_args );

					// Define return message.
					$log = __( 'Updated, as Term with Permalink already generated by this Group', 'page-generator-pro' );
				} else {
					// Create Term.
					$term = wp_insert_term( $settings['title'], $settings['taxonomy'], $term_args );

					// Define return message.
					$log = __( 'Created, as Term with Permalink has not yet been generated by this Group', 'page-generator-pro' );
				}
				break;

			/**
			 * Overwrite Any
			 */
			case 'overwrite_any':
				// If a Term was found, update it.
				if ( isset( $existing_term_id ) && $existing_term_id > 0 ) {
					// Update Term.
					$term = wp_update_term( $existing_term_id, $settings['taxonomy'], $term_args );

					// Define return message.
					$log = sprintf(
						/* translators: Post Type or Taxonomy Name */
						__( 'Updated, as %s with Permalink already exists in WordPress', 'page-generator-pro' ),
						$settings['taxonomy']
					);
				} else {
					// Create Term.
					$term = wp_insert_term( $settings['title'], $settings['taxonomy'], $term_args );

					// Define return message.
					$log = sprintf(
						/* translators: Post Type or Taxonomy Name */
						__( 'Created, as %s with Permalink does not exist in WordPress', 'page-generator-pro' ),
						$settings['taxonomy']
					);
				}
				break;

			/**
			 * Don't Overwrite
			 */
			default:
				// If the Term already exists in this Taxonomy, just return it.
				// This prevents calling wp_insert_term(), which would WP_Error when the Taxonomy Term already exists.
				$existing_term_id = $this->term_exists( 0, $settings['taxonomy'], $term_args['parent'], $settings['title'] );
				if ( $existing_term_id > 0 ) {
					return $this->generate_return(
						$group_id,
						$existing_term_id,
						$settings['taxonomy'],
						false,
						sprintf(
							/* translators: Post Type or Taxonomy Name */
							__( 'Skipped, as %s with Permalink already exists in WordPress', 'page-generator-pro' ),
							$settings['taxonomy']
						),
						$start,
						$test_mode,
						$system,
						$keywords_terms
					);
				} else {
					// Create Term.
					$term = wp_insert_term( $settings['title'], $settings['taxonomy'], $term_args );

					// Define return message.
					$log = sprintf(
						/* translators: Taxonomy name */
						__( 'Created', 'page-generator-pro' ),
						$settings['taxonomy']
					);
				}
				break;

		}

		// Check Term creation / update worked.
		if ( is_wp_error( $term ) ) {
			$term->add_data( $term_args, $term->get_error_code() );
			return $term;
		}

		/**
		 * Run any actions immediately after an individual Taxonomy Term is generated, but before
		 * its Custom Fields or Term Meta have been assigned.
		 *
		 * @since   2.6.3
		 *
		 * @param   array   $term       Generated Term.
		 * @param   int     $group_id   Group ID.
		 * @param   array   $settings   Group Settings.
		 * @param   int     $index      Keyword Index.
		 * @param   bool    $test_mode  Test Mode.
		 */
		do_action( 'page_generator_pro_generate_term_after_insert_update_term', $term, $group_id, $settings, $index, $test_mode );

		// Store this Group ID and Index in the Term's meta, so we can edit/delete the generated Term(s) in the future.
		update_term_meta( $term['term_id'], '_page_generator_pro_group', $group_id );
		update_term_meta( $term['term_id'], '_page_generator_pro_index', $index );

		// Store Term Meta (ACF, Yoast, Page Builder data etc) on the Generated Term.
		$this->set_term_meta( $term['term_id'], $group_id, $settings, $term_args );

		// Request that the user review the Plugin, if we're not in Test Mode. Notification displayed later,
		// can be called multiple times and won't re-display the notification if dismissed.
		if ( ! $test_mode && ! $this->base->licensing->has_feature( 'whitelabelling' ) ) {
			$this->base->dashboard->request_review();
		}

		// Store current index as the last index generated for this Group, if we're not in test mode.
		if ( ! $test_mode ) {
			$this->base->get_class( 'groups_terms' )->update_last_index_generated( $group_id, $index );
		}

		/**
		 * Run any actions after an individual Term is generated successfully.
		 *
		 * @since   2.4.1
		 *
		 * @param   array   $term       Generated Term.
		 * @param   int     $group_id   Group ID.
		 * @param   array   $settings   Group Settings.
		 * @param   int     $index      Keyword Index.
		 * @param   bool    $test_mode  Test Mode.
		 */
		do_action( 'page_generator_pro_generate_term_finished', $term, $group_id, $settings, $index, $test_mode );

		// Return the URL and keyword / term replacements used.
		return $this->generate_return( $group_id, $term['term_id'], $settings['taxonomy'], true, $log, $start, $test_mode, $system, $keywords_terms );

	}

	/**
	 * Adds the given WP_Error to the array of errors when generating an individual content item.
	 *
	 * @since   4.9.0
	 *
	 * @param   WP_Error $error  Error.
	 */
	public function add_dynamic_element_error( $error ) {

		// If this is the first error, set the property to the WP_Error $error.
		if ( ! $this->dynamic_element_errors ) {
			$this->dynamic_element_errors = $error;
			return;
		}

		// Add this error to the existing property.
		$this->dynamic_element_errors->add( $error->get_error_code(), $error->get_error_message() );

	}

	/**
	 * Clear any Dynamic Element errors stored using add_dynamic_element_error().
	 *
	 * @since   4.9.0
	 */
	public function clear_dynamic_element_errors() {

		$this->dynamic_element_errors = false;

	}

	/**
	 * Resets the Search and Replacement class arrays
	 *
	 * @since   3.0.4
	 */
	private function reset_search_replace_arrays() {

		// Reset search and replacement arrays.
		$this->searches     = array();
		$this->replacements = array();

	}

	/**
	 * For all Keyword tags found in the Group, builds search and replacement class arrays for later use
	 * when recursively iterating through a Group's settings to replace the Keyword tags with their Term counterparts
	 *
	 * @since   2.6.1
	 *
	 * @param   array $required_keywords_full     Required Keywords, Full.
	 * @param   array $keywords_terms             Keywords / Terms Key/Value Pairs.
	 */
	public function build_search_replace_arrays( $required_keywords_full, $keywords_terms ) {

		// Reset search and replacement arrays.
		$this->reset_search_replace_arrays();

		foreach ( $required_keywords_full as $keyword => $keywords_with_modifiers ) {
			// Build search and replacement arrays for this Keyword.
			foreach ( $keywords_with_modifiers as $keyword_with_modifiers ) {
				// If the Keyword isn't truly a Keyword in the database, don't do anything.
				if ( ! isset( $keywords_terms[ $keyword ] ) ) {
					continue;
				}

				// Cast keyword as a string so numeric keywords don't break search/replace.
				$this->build_search_replace_arrays_for_keyword( $keyword_with_modifiers, (string) $keyword, $keywords_terms[ $keyword ] );
			}
		}

	}

	/**
	 * Appends the search and replace arrays for the given Keyword (column name, nth term, transformations) and its applicable Term.
	 *
	 * @since   2.6.1
	 *
	 * @param   string $keyword_with_modifiers     Keyword with Modifiers (search, e.g. keyword(column):3:uppercase_all:url.
	 * @param   string $keyword                    Keyword without Modifiers (e.g. keyword).
	 * @param   string $term                       Term (replacement).
	 */
	public function build_search_replace_arrays_for_keyword( $keyword_with_modifiers, $keyword, $term ) {

		// If the Keyword with Modifiers matches the Keyword, we have no modifiers
		// Just return the term.
		if ( $keyword_with_modifiers == $keyword ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual
			$this->searches[]     = '{' . $keyword_with_modifiers . '}';
			$this->replacements[] = $term;
			return;
		}

		// Fetch an array of transformations that might exist and need applying to the Term.
		$keyword_transformations = false;
		if ( strpos( $keyword_with_modifiers, ':' ) !== false ) {
			$keyword_transformations = explode( ':', $keyword_with_modifiers );
		}

		// If Keyword Transformation(s) exist, and one Transformation is numeric, this is an nth term specifier
		// Fetch the Keyword's nth Term now as the Term to use.
		if ( $keyword_transformations ) {
			foreach ( $keyword_transformations as $keyword_transformation ) {
				if ( ! is_numeric( $keyword_transformation ) ) {
					continue;
				}

				// Keyword Transformation is an nth term specifier.
				if ( isset( $this->keywords['terms'][ $keyword ][ $keyword_transformation - 1 ] ) ) {
					$term = $this->keywords['terms'][ $keyword ][ $keyword_transformation - 1 ];
				}
			}
		}

		// If the Keyword contains a column, fetch the Keyword Term's Column value now.
		$column                                = false;
		$keyword_column_start_bracket_position = strpos( $keyword_with_modifiers, '(' );
		$keyword_column_end_bracket_position   = strpos( $keyword_with_modifiers, ')', $keyword_column_start_bracket_position );
		if ( $keyword_column_start_bracket_position !== false && $keyword_column_end_bracket_position !== false ) {
			// Extract Column Name.
			$column = substr( $keyword_with_modifiers, ( $keyword_column_start_bracket_position + 1 ), ( $keyword_column_end_bracket_position - $keyword_column_start_bracket_position - 1 ) );

			// Split the Term into Columns.
			$term_parts = str_getcsv( $term, $this->keywords['delimiters'][ $keyword ], "\"", "\\" ); // phpcs:ignore Squiz.Strings.DoubleQuoteUsage.NotRequired

			// Fetch the Column Index.
			$column_index = array_search( $column, $this->keywords['columns'][ $keyword ] ); // phpcs:ignore WordPress.PHP.StrictInArray

			// Fetch the Term.
			if ( $column_index !== false && isset( $term_parts[ $column_index ] ) ) {
				$term = trim( $term_parts[ $column_index ] );
			}
		}

		// If Keyword Transformation(s) exist, transform the Term using each Transformation in the order
		// they're listed.
		if ( $keyword_transformations ) {
			foreach ( $keyword_transformations as $keyword_transformation ) {
				// Keyword Transformation is an nth term specifier; skip as we dealt with this earlier.
				if ( is_numeric( $keyword_transformation ) ) {
					continue;
				}

				$term = $this->apply_keyword_transformation( $keyword_transformation, $term, $keyword, $column );
			}
		}

		// Add Keyword and Term to Search and Replace arrays.
		$this->searches[]     = '{' . $keyword_with_modifiers . '}';
		$this->replacements[] = $term;

		// If $term is an array, set a flag.
		if ( is_array( $term ) ) {
			$this->replacements_contain_array = true;
		}

	}

	/**
	 * Applies the given keyword transformation to the given string (term)
	 *
	 * @since   2.2.3
	 *
	 * @param   string      $keyword_transformation     Keyword Transformation.
	 * @param   string      $term                       Term.
	 * @param   string      $keyword                    Keyword.
	 * @param   bool|string $column                     Keyword Column.
	 * @return  string|array                            Transformed Term
	 */
	private function apply_keyword_transformation( $keyword_transformation, $term, $keyword, $column = false ) {

		$arguments = false;

		// Split out the Keyword Transformation's arguments, if any exist.
		preg_match_all( '/\[([^\]]*)\]/', $keyword_transformation, $matches );

		// If arguments exist, remove them from the Keyword Transformation so we get e.g.
		// nearby[city_latitude,city_longitude,3,distance] --> nearby.
		if ( ! empty( $matches[0] ) ) {
			$keyword_transformation = str_replace( $matches[0][0], '', $keyword_transformation );
			$arguments              = explode( ',', $matches[1][0] );
		}

		switch ( $keyword_transformation ) {
			/**
			 * Uppercase
			 */
			case 'uppercase_all':
				// Use i18n compatible method if available.
				if ( function_exists( 'mb_convert_case' ) ) {
					return mb_convert_case( $term, MB_CASE_UPPER );
				}

				// Fallback to basic version which doesn't support i18n.
				return strtoupper( $term );

			/**
			 * Lowercase
			 */
			case 'lowercase_all':
				// Use i18n compatible method if available.
				if ( function_exists( 'mb_convert_case' ) ) {
					return mb_convert_case( $term, MB_CASE_LOWER );
				}

				// Fallback to basic version which doesn't support i18n.
				return strtolower( $term );

			/**
			 * Upperchase first character
			 */
			case 'uppercase_first_character':
				// Use i18n compatible method if available.
				if ( function_exists( 'mb_strtoupper' ) ) {
					return mb_strtoupper( mb_substr( $term, 0, 1 ) ) . mb_substr( $term, 1 );
				}

				// Fallback to basic version which doesn't support i18n.
				return ucfirst( $term );

			/**
			 * Uppercase first character of each word
			 */
			case 'uppercase_first_character_words':
				// Use i18n compatible method if available.
				if ( function_exists( 'mb_convert_case' ) ) {
					return mb_convert_case( $term, MB_CASE_TITLE );
				}

				// Fallback to basic version which doesn't support i18n.
				return ucwords( $term );

			/**
			 * First Word
			 */
			case 'first_word':
				$term_parts = explode( ' ', $term );
				return $term_parts[0];

			/**
			 * Last Word
			 */
			case 'last_word':
				$term_parts = explode( ' ', $term );
				return $term_parts[ count( $term_parts ) - 1 ];

			/**
			 * URL
			 */
			case 'url':
				return sanitize_title( $term );

			/**
			 * URL, Underscore
			 */
			case 'url_underscore':
				return str_replace( '-', '_', sanitize_title( $term ) );

			/**
			 * Number to Words
			 */
			case 'number_to_words':
				// Strip commas.
				$term = str_replace( ',', '', $term );

				// Bail if Term isn't numeric.
				if ( ! is_numeric( $term ) ) {
					return $term;
				}

				// Bail if PHP version is older than 7.4.
				if ( phpversion() < '7.4' ) {
					return $term;
				}

				// Extract arguments.
				$args = array(
					'lang' => ( isset( $arguments[0] ) ? $arguments[0] : get_locale() ),
				);

				// Some language codes are case sensitive and become lowercased, so we need to correct them
				// for the library to work.
				switch ( $args['lang'] ) {
					case 'fr_be':
						$args['lang'] = 'fr_BE';
						break;
					case 'pt_br':
						$args['lang'] = 'pt_BR';
						break;
				}

				// Convert to integer.
				$term = (int) $term;

				// Setup class.
				$number_to_words = new NumberToWords\NumberToWords();

				// Get number transformer for the language, falling back to English
				// if the given language code is not supported.
				try {
					$transformer = $number_to_words->getNumberTransformer( $args['lang'] );
				} catch ( NumberToWords\Exception\InvalidArgumentException $e ) {
					$transformer = $number_to_words->getNumberTransformer( 'en' );
				}

				// Convert to words.
				return $transformer->toWords( $term );

			/**
			 * Currency to Words
			 */
			case 'currency_to_words':
				// Strip commas.
				$term = str_replace( ',', '', $term );

				// Bail if Term isn't numeric.
				if ( ! is_numeric( $term ) ) {
					return $term;
				}

				// Bail if PHP version is older than 7.4.
				if ( phpversion() < '7.4' ) {
					return $term;
				}

				// Extract arguments.
				$args = array(
					'lang'     => ( isset( $arguments[0] ) ? $arguments[0] : get_locale() ),
					'currency' => ( isset( $arguments[1] ) ? $arguments[1] : 'USD' ),
				);

				// Some language codes are case sensitive and become lowercased, so we need to correct them
				// for the library to work.
				switch ( $args['lang'] ) {
					case 'fr_be':
						$args['lang'] = 'fr_BE';
						break;
					case 'pt_br':
						$args['lang'] = 'pt_BR';
						break;
				}

				// Convert to integer, multiplying by 100 to convert to e.g. cents.
				$term = (int) ( $term * 100 );

				// Setup class.
				$number_to_words = new NumberToWords\NumberToWords();

				// Get number transformer for the language, falling back to English
				// if the given language code is not supported.
				try {
					$transformer = $number_to_words->getCurrencyTransformer( $args['lang'] );
				} catch ( NumberToWords\Exception\InvalidArgumentException $e ) {
					$transformer = $number_to_words->getCurrencyTransformer( 'en' );
				}

				// Convert to words.
				return $transformer->toWords( $term, $args['currency'] );

			/**
			 * All, comma separated
			 */
			case 'all':
				if ( $column ) {
					$terms = array();
					foreach ( $this->keywords['terms'][ $keyword ] as $term ) {
						// Split the term.
						$term_parts = str_getcsv( $term, $this->keywords['delimiters'][ $keyword ], "\"", "\\" ); // phpcs:ignore Squiz.Strings.DoubleQuoteUsage.NotRequired

						// Fetch the column index.
						$column_index = array_search( $column, $this->keywords['columns'][ $keyword ] ); // phpcs:ignore WordPress.PHP.StrictInArray

						// Skip if no column index could be found.
						if ( $column_index === false ) {
							continue;
						}

						$terms[] = ( isset( $term_parts[ $column_index ] ) ? trim( $term_parts[ $column_index ] ) : '' );
					}

					// Remove duplicates.
					$terms = array_values( array_unique( $terms ) );

					// Return All Column Terms for the Keyword.
					return implode( ', ', $terms );
				}

				// Return all Terms for the Keyword.
				return implode( ', ', $this->keywords['terms'][ $keyword ] );

			/**
			 * Random
			 */
			case 'random':
				if ( $column ) {
					$terms = array();
					foreach ( $this->keywords['terms'][ $keyword ] as $term ) {
						// Split the term.
						$term_parts = str_getcsv( $term, $this->keywords['delimiters'][ $keyword ], "\"", "\\" ); // phpcs:ignore Squiz.Strings.DoubleQuoteUsage.NotRequired

						// Fetch the column index.
						$column_index = array_search( $column, $this->keywords['columns'][ $keyword ] ); // phpcs:ignore WordPress.PHP.StrictInArray

						// Skip if no column index could be found.
						if ( $column_index === false ) {
							continue;
						}

						$terms[] = ( isset( $term_parts[ $column_index ] ) ? trim( $term_parts[ $column_index ] ) : '' );
					}

					// Remove duplicates.
					$terms = array_values( array_unique( $terms ) );
				} else {
					$terms = $this->keywords['terms'][ $keyword ];
				}

				// If an argument is specified, pick the given number of Terms at random
				// and return them as a comma separated list.
				if ( is_array( $arguments ) && is_numeric( $arguments[0] ) ) {
					$random_terms = array();
					for ( $i = 0; $i < $arguments[0]; $i++ ) {
						$random_terms[] = $terms[ wp_rand( 0, ( count( $terms ) - 1 ) ) ];
					}

					return implode( ', ', $random_terms );
				}

				// Return blank string if Terms array is empty.
				if ( ! count( $terms ) ) {
					return '';
				}

				// Return single random Term.
				return $terms[ wp_rand( 0, ( count( $terms ) - 1 ) ) ];

			/**
			 * Random, Different
			 * - Returns an array, so generation can pick a Term at random each time
			 */
			case 'random_different':
				if ( $column ) {
					$terms = array();
					foreach ( $this->keywords['terms'][ $keyword ] as $term ) {
						// Split the term.
						$term_parts = str_getcsv( $term, $this->keywords['delimiters'][ $keyword ], "\"", "\\" ); // phpcs:ignore Squiz.Strings.DoubleQuoteUsage.NotRequired

						// Fetch the column index.
						$column_index = array_search( $column, $this->keywords['columns'][ $keyword ] ); // phpcs:ignore WordPress.PHP.StrictInArray

						// Skip if no column index could be found.
						if ( $column_index === false ) {
							continue;
						}

						$terms[] = ( isset( $term_parts[ $column_index ] ) ? trim( $term_parts[ $column_index ] ) : '' );
					}

					// Remove duplicates.
					$terms = array_values( array_unique( $terms ) );

					// Return All Column Terms for the Keyword.
					return $terms;
				}

				// Return all Terms for the Keyword.
				return $this->keywords['terms'][ $keyword ];

			/**
			 * Random Subset
			 * - Returns e.g. three,four from a Term / Term Column of one,two,three,four,five
			 */
			case 'random_subset':
				// Just return the Term if it doesn't contain a delimiter.
				if ( strpos( $term, ',' ) === false ) {
					return $term;
				}

				// Build array of Terms.
				$terms = explode( ',', $term );

				// Fetch arguments.
				$args = array(
					'min' => absint( ( isset( $arguments[0] ) ? $arguments[0] : 1 ) ), // Minimum number of subsets to return.
					'max' => absint( ( isset( $arguments[1] ) ? $arguments[1] : 0 ) ), // Maximum number of subsets to return.
				);

				// Bail if min is zero.
				if ( ! $args['min'] ) {
					return $term;
				}

				// Determine the number of subsets to pick.
				$subsets = ( ! $args['max'] ? $args['min'] : wp_rand( $args['min'], $args['max'] ) );

				// Fetch the number of subsets from the array of Terms.
				$terms_keys = array_rand( $terms, $subsets );

				// If array_rand() is an integer, convert it to an array.
				// This happens when min/max is the same number.
				if ( is_int( $terms_keys ) ) {
					$terms_keys = array( $terms_keys );
				}

				// Shuffle the order of the keys to produce a random variation.
				if ( count( $terms_keys ) > 1 ) {
					shuffle( $terms_keys );
				}

				// Build array of subsets.
				$random_term_subsets = array();
				foreach ( $terms_keys as $index ) {
					$random_term_subsets[] = $terms[ $index ];
				}

				// Implode.
				$term = implode( ', ', $random_term_subsets );

				// Return.
				return $term;

			/**
			 * Nearby
			 */
			case 'nearby':
				$args = array(
					( isset( $arguments[0] ) ? $arguments[0] : 'city_latitude' ),
					( isset( $arguments[1] ) ? $arguments[1] : 'city_longitude' ),
					( isset( $arguments[2] ) ? $arguments[2] : 3 ),
					( isset( $arguments[3] ) ? $arguments[3] : false ),
				);

				return $this->apply_nearby_keyword_transformation(
					$term,
					$keyword,
					$column,
					$args[0],
					$args[1],
					$args[2],
					$args[3]
				);

			/**
			 * Other Transformations
			 */
			default:
				/**
				 * Filter to perform non-standard keyword transformation.
				 *
				 * @since   1.7.8
				 *
				 * @param   string      $term               Term.
				 * @param   string      $transformation     Keyword Transformation.
				 * @param   string      $keyword            Keyword.
				 * @param   string|bool $column             Keyword Column.
				 */
				$term = apply_filters( 'page_generator_pro_generate_generate_content_apply_keyword_transformation', $term, $keyword_transformation, $keyword, $column );

				return $term;
		}

	}

	/**
	 * Applies the 'Nearby' keyword transformation to the given string (term)
	 *
	 * @since   3.3.1
	 *
	 * @param   string      $term                       Term.
	 * @param   string      $keyword                    Keyword.
	 * @param   bool|string $column                     Keyword Column.
	 * @param   string      $latitude_column            Latitude Column in Keyword.
	 * @param   string      $longitude_column           Longitude Column in Keyword.
	 * @param   int         $radius                     Radius.
	 * @param   bool        $order_by_distance          Order By Distance.
	 * @return  string                                  Transformed Term
	 */
	private function apply_nearby_keyword_transformation( $term, $keyword, $column, $latitude_column, $longitude_column, $radius = 3, $order_by_distance = false ) {

		// Bail if no Column.
		if ( ! $column ) {
			return '';
		}

		// Get Latitude and Longitude of current Term.
		foreach ( $this->keywords['terms'][ $keyword ] as $row => $keyword_term ) {
			// Split the term.
			$term_parts = str_getcsv( $keyword_term, $this->keywords['delimiters'][ $keyword ], "\"", "\\" ); // phpcs:ignore Squiz.Strings.DoubleQuoteUsage.NotRequired

			// Fetch the column index.
			$column_index = array_search( $column, $this->keywords['columns'][ $keyword ] ); // phpcs:ignore WordPress.PHP.StrictInArray

			// Skip if no column index could be found.
			if ( $column_index === false ) {
				continue;
			}

			// Skip if this isn't the current Term.
			if ( trim( $term_parts[ $column_index ] ) != $term ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseNotEqual
				continue;
			}

			// If here, we found the current Term in the Keyword data.
			// Fetch the column indicies for latitude and longitude.
			$column_index_latitude  = array_search( $latitude_column, $this->keywords['columns'][ $keyword ] ); // phpcs:ignore WordPress.PHP.StrictInArray
			$column_index_longitude = array_search( $longitude_column, $this->keywords['columns'][ $keyword ] ); // phpcs:ignore WordPress.PHP.StrictInArray

			// Skip if no latitude and longitude columns exist.
			if ( $column_index_latitude === false || $column_index_longitude === false ) {
				return '';
			}

			// Get Term's Latitude and Longitude, and break.
			$term_latitude  = (float) trim( $term_parts[ $column_index_latitude ] );
			$term_longitude = (float) trim( $term_parts[ $column_index_longitude ] );
			break;
		}

		// If no Latitude and Longitude found, bail.
		if ( ! isset( $term_latitude ) || ! isset( $term_longitude ) ) {
			return '';
		}

		// Iterate through Keyword Terms again, adding to the Terms array for rows that fall within the radius of the current Term's
		// latitude and longitude .
		$terms = array();
		foreach ( $this->keywords['terms'][ $keyword ] as $row => $keyword_term ) {
			// Split the term to fetch the latitude and longitude.
			$term_parts = str_getcsv( $keyword_term, $this->keywords['delimiters'][ $keyword ], "\"", "\\" ); // phpcs:ignore Squiz.Strings.DoubleQuoteUsage.NotRequired
			$value      = trim( $term_parts[ $column_index ] );
			$latitude   = (float) ( isset( $column_index_latitude ) ? trim( $term_parts[ $column_index_latitude ] ) : 0 );
			$longitude  = (float) ( isset( $column_index_longitude ) ? trim( $term_parts[ $column_index_longitude ] ) : 0 );

			// Skip if value is the Term.
			if ( $value == $term ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual
				continue;
			}

			// Add Term if falls within radius.
			$distance = 3959 *
						acos(
							cos( 0.0174532925 * $term_latitude ) *
							cos( 0.0174532925 * $latitude ) *
							cos( ( 0.0174532925 * $longitude ) - ( 0.0174532925 * $term_longitude ) ) +
							sin( 0.0174532925 * $term_latitude ) *
							sin( 0.0174532925 * $latitude )
						);

			if ( $distance > $radius ) {
				continue;
			}

			$terms[] = array(
				'value'    => $value,
				'distance' => $distance,
			);
		}

		// If no Terms, bail.
		if ( ! count( $terms ) ) {
			return '';
		}

		// Sort by distance if required.
		if ( $order_by_distance ) {
			usort(
				$terms,
				function ( $a, $b ) {
					return $a['distance'] <=> $b['distance'];
				}
			);
		}

		// Return comma separated list.
		$terms_list = array();
		foreach ( $terms as $term ) {
			$terms_list[] = $term['value'];
		}

		return implode( ', ', $terms_list );

	}

	/**
	 * Helper method to iterate through each keyword's tags, including any modifiers,
	 * building search and replacement arrays before recursively iterating through the supplied settings,
	 * replacing the keywords and their transformations with the terms.
	 *
	 * @since   1.9.8
	 *
	 * @param   array $settings   Group Settings.
	 * @return  array               Group Settings
	 */
	public function replace_keywords( $settings ) {

		// Iterate through Group Settings, replacing $this->searches (Keywords) with $this->replacements (Terms)
		// as well as performing spintax and shortcode processing.
		array_walk_recursive( $settings, array( $this, 'replace_keywords_in_array' ) );

		// Return.
		return $settings;

	}

	/**
	 * Returns an array comprising of all keywords and their term replacements,
	 * including keywords with column names in the format keyword_column.
	 *
	 * Does not include transformations or nth terms
	 *
	 * Used to store basic keyword/term data in the generated Page's Post Meta
	 * if Store Keywords is enabled
	 *
	 * @since   2.2.8
	 *
	 * @param   array $keywords_terms     Keyword / Term Key/Value Pairs.
	 * @return  bool|array                  Keyword / Term Key/Value Pairs
	 */
	private function get_keywords_terms_array_with_columns( $keywords_terms ) {

		$store_keywords = array();

		foreach ( $keywords_terms as $keyword => $term ) {
			// Add keyword/term pair.
			$store_keywords[ $keyword ] = $term;

			// If no columns exist for this Keyword, continue.
			if ( ! isset( $this->keywords['columns'] ) ) {
				continue;
			}
			if ( ! isset( $this->keywords['columns'][ $keyword ] ) ) {
				continue;
			}

			foreach ( $this->keywords['columns'][ $keyword ] as $column ) {
				// Skip if column name is empty.
				if ( empty( $column ) ) {
					continue;
				}

				// Split the term.
				$term_parts = str_getcsv( $term, $this->keywords['delimiters'][ $keyword ], "\"", "\\" ); // phpcs:ignore Squiz.Strings.DoubleQuoteUsage.NotRequired

				// Fetch the column index.
				$column_index = array_search( $column, $this->keywords['columns'][ $keyword ] ); // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict

				// Skip if no column index could be found.
				if ( $column_index === false ) {
					continue;
				}

				// Add to the search and replace arrays.
				$store_keywords[ $keyword . '_' . $column ] = ( isset( $term_parts[ $column_index ] ) ? trim( $term_parts[ $column_index ] ) : '' );
			}
		}

		// Bail if no keywords.
		if ( count( $store_keywords ) === 0 ) {
			return false;
		}

		return $store_keywords;

	}

	/**
	 * A faster method for fetching all keyword combinations for PHP 5.5+
	 *
	 * @since   1.5.1
	 *
	 * @param   array $input  Multidimensional array of Keyword Names (keys) => Terms (values).
	 * @return  \Page_Generator_Pro_Cartesian_Product           Cartesian Product
	 */
	private function generate_all_combinations( $input ) {

		// Load class.
		require_once $this->base->plugin->folder . '/includes/admin/cartesian-product.php';

		// Return.
		return new Page_Generator_Pro_Cartesian_Product( $input );

	}

	/**
	 * Recursively goes through the settings array, finding any {keywords}
	 * specified, to build up an array of keywords we need to fetch.
	 *
	 * @since   1.0.0
	 *
	 * @param   array $settings   Settings.
	 * @return  array               Required Keywords
	 */
	public function find_keywords_in_settings( $settings ) {

		// Reset required keywords array.
		$this->required_keywords      = array();
		$this->required_keywords_full = array();

		// Recursively walk through all settings to find all keywords.
		array_walk_recursive( $settings, array( $this, 'find_keywords_in_string' ) );

		// Build results.
		$results = array(
			'required_keywords'      => $this->required_keywords, // Keywords only.
			'required_keywords_full' => $this->required_keywords_full, // Includes columns and modifiers.
		);

		// Reset required keywords array.
		$this->required_keywords      = array();
		$this->required_keywords_full = array();

		return $results;

	}

	/**
	 * For the given array of keywords, only returns keywords with terms, column names and delimiters
	 * where each keywords have terms.
	 *
	 * @since   1.6.5
	 *
	 * @param   array $required_keywords  Required Keywords.
	 * @return  array                       Keywords with Terms, Columns and Delimiters
	 */
	public function get_keywords_terms_columns_delimiters( $required_keywords ) {

		// Define blank array for keywords with terms and keywords with columns.
		$results = array(
			'terms'      => array(),
			'columns'    => array(),
			'delimiters' => array(),
		);

		foreach ( $required_keywords as $key => $keyword ) {

			// Get terms for this keyword.
			// If this keyword starts with 'taxonomy_', try to fetch the terms for the Taxonomy.
			if ( strpos( $keyword, 'taxonomy_' ) !== false && strpos( $keyword, 'taxonomy_' ) === 0 ) {
				$result = get_terms(
					array(
						'taxonomy'               => str_replace( 'taxonomy_', '', $keyword ),
						'hide_empty'             => false,
						'fields'                 => 'names',
						'update_term_meta_cache' => false,
					)
				);

				// Skip if no results.
				if ( ! is_array( $result ) ) {
					continue;
				}
				if ( count( $result ) === 0 ) {
					continue;
				}

				$results['terms'][ $keyword ] = $result;
			} else {
				$result = $this->base->get_class( 'keywords' )->get_by( 'keyword', $keyword );

				// Skip if no results.
				if ( ! is_array( $result ) ) {
					continue;
				}
				if ( count( $result ) === 0 ) {
					continue;
				}

				$results['terms'][ $keyword ] = $result['dataArr'];

				// Ensure column names are lowercase so array_search() works against our lowercase keyword + column.
				$results['columns'][ $keyword ] = array();
				foreach ( $result['columnsArr'] as $column ) {
					$results['columns'][ $keyword ][] = strtolower( $column );
				}
				$results['delimiters'][ $keyword ] = $result['delimiter'];
			}
		}

		// Return results.
		return $results;

	}

	/**
	 * Returns an array of keyword and term key / value pairs.
	 *
	 * @since   1.0.0
	 *
	 * @param   string $method     Generation Method.
	 * @param   int    $index      Generation Index.
	 * @return  WP_Error|array
	 */
	private function get_keywords_terms( $method, $index ) {

		$keywords_terms = array();

		switch ( $method ) {

			/**
			 * All
			 * - Generates all possible term combinations across keywords
			 */
			case 'all':
				// Use our Cartesian Product class, which implements a Generator
				// to allow iteration of data without needing to build an array in memory.
				// See: http://php.net/manual/en/language.generators.overview.php.
				$combinations = $this->generate_all_combinations( $this->keywords['terms'] );

				// If the current index exceeds the total number of combinations, we've exhausted all
				// options and don't want to generate any more Pages (otherwise we end up with duplicates).
				if ( $index > ( $combinations->count() - 1 ) ) {
					// If the combinations count is a negative number, we exceeded the floating point for an integer
					// Tell the user to upgrade PHP and/or reduce the number of keyword terms.
					if ( $combinations->count() < 0 ) {
						$message = __( 'The total possible number of unique keyword term combinations exceeds the maximum number value that can be stored by your version of PHP.  Please consider upgrading to a 64 bit PHP 7.0+ build and/or reducing the number of keyword terms that you are using.', 'page-generator-pro' );
					} else {
						$message = __( 'All possible keyword term combinations have been generated. Generating more Pages/Posts would result in duplicate content.', 'page-generator-pro' );
					}

					return new WP_Error( 'page_generator_pro_generate_content_keywords_exhausted', $message );
				}

				// Iterate through the combinations until we reach the one matching the index.
				foreach ( $combinations as $c_index => $combination ) {
					// Skip if not the index we want.
					if ( $c_index !== $index ) {
						continue;
					}

					// Define the keyword => term key/value pairs to use based on the current index.
					$keywords_terms = $combination;
					break;
				}
				break;

			/**
			 * Sequential
			 * - Generates term combinations across keywords matched by index
			 */
			case 'sequential':
				foreach ( $this->keywords['terms'] as $keyword => $terms ) {
					// Use modulo to get the term index for this keyword.
					$term_index = ( $index % count( $terms ) );

					// Build the keyword => term key/value pairs.
					$keywords_terms[ $keyword ] = $terms[ $term_index ];
				}
				break;

			/**
			 * Random
			 * - Gets a random term for each keyword
			 */
			case 'random':
				foreach ( $this->keywords['terms'] as $keyword => $terms ) {
					// If only one term exists, use that.
					if ( count( $terms ) === 1 ) {
						$term_index = 0;
					} else {
						$term_index = wp_rand( 0, ( count( $terms ) - 1 ) );
					}

					// Build the keyword => term key/value pairs.
					$keywords_terms[ $keyword ] = $terms[ $term_index ];
				}
				break;

			/**
			 * Invalid method
			 */
			default:
				return new WP_Error( 'page_generator_pro_generate_get_keywords_terms_invalid_method', __( 'The method given is invalid.', 'page-generator-pro' ) );
		}

		// Cleanup the terms.
		foreach ( $keywords_terms as $key => $term ) {
			$keywords_terms[ $key ] = trim( html_entity_decode( $term ) );
		}

		/**
		 * Returns an array of keyword and term key / value pairs, before any
		 * search or replacement arrays are built.
		 *
		 * @since   2.7.5
		 *
		 * @param   array   $keywords_terms     Keywords and Terms for this Page Generation.
		 * @param   string  $method             Generation Method.
		 * @param   int     $index              Generation Index.
		 */
		$keywords_terms = apply_filters( 'page_generator_pro_generate_get_keywords_terms', $keywords_terms, $method, $index );

		// Return.
		return $keywords_terms;

	}

	/**
	 * Performs a search on the given string to find any {keywords}
	 *
	 * @since   1.2.0
	 *
	 * @param   object|string $content    Array Value (string to search).
	 * @param   string        $key        Array Key.
	 */
	private function find_keywords_in_string( $content, $key ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// If $content is an object, iterate this call.
		if ( is_object( $content ) ) {
			return array_walk_recursive( $content, array( $this, 'find_keywords_in_string' ) );
		}

		// Bail if content is null.
		if ( is_null( $content ) ) { // @phpstan-ignore-line
			return;
		}

		/**
		 * Get Keywords in this string.  Covers:
		 * - Alphanumeric and accented keyword names, with hyphens and underscores
		 * - Alphanumeric and accented keyword column names, with hyphens and underscores
		 * - Keyword modifiers
		 * - Keyword modifier arguments
		 *
		 * For example:
		 * {keyword}
		 * {keyword_ü}
		 * {keyword-keyword}
		 * {keyword_keyword}
		 * {keyword:modifier}
		 * {keyword(column)}, {keyword(column_name)}, {keyword(column-name)}
		 * {keyword(column_name):modifier}, {keyword(column-name):modifier...:modifier}
		 * {keyword(column_name):modifier[args]}
		 * {keyword(column_name):modifier[arg1,argN]}
		 *
		 * Previous method "|{(.+?)}|" would include spintax and fail to extract keywords
		 * within JSON e.g. Gutenberg Block JSON strings that contain a Keyword.
		 */
		preg_match_all( '/{([\p{L}0-9_\-:,()\\[\\]]+?)}/u', $content, $matches );

		// Bail if no matches are found.
		if ( ! is_array( $matches ) ) {
			return;
		}
		if ( count( $matches[1] ) === 0 ) {
			return;
		}

		// Iterate through matches, adding them to the required keywords array.
		foreach ( $matches[1] as $m_key => $keyword ) {
			$this->add_keyword_to_required_keywords( $keyword );
		}

	}

	/**
	 * Adds the given keyword to the required keywords array, if it doesn't already exist
	 *
	 * @since   2.8.8
	 *
	 * @param   string $keyword    Possible Keyword.
	 */
	private function add_keyword_to_required_keywords( $keyword ) {

		// If a keyword is within spintax at the start of the string (e.g. {{service}|{service2}} ),
		// we get an additional leading curly brace for some reason.  Remove it.
		$keyword = str_replace( '{', '', $keyword );
		$keyword = str_replace( '}', '', $keyword );

		// Lowercase keyword, to avoid duplicates e.g. {City} and {city}.
		$keyword = strtolower( $keyword );

		// Fetch just the Keyword Name.
		$keyword_name = $this->extract_keyword_name_from_keyword( $keyword );

		// Fetch Keyword Names.
		$keywords_names = $this->base->get_class( 'keywords' )->get_keywords_names();

		// Bail if no Keyword Names are specified.
		if ( ! $keywords_names ) {
			return;
		}

		// If the Keyword Name is a spin that's just text (i.e it's not actually a Keyword), skip it.
		if ( ! in_array( $keyword_name, $keywords_names, true ) ) {
			return;
		}

		// If the Keyword Name is not in our required_keywords array, add it now.
		if ( ! in_array( $keyword_name, $this->required_keywords, true ) ) {
			$this->required_keywords[ $keyword_name ] = $keyword_name;
		}

		// If the Keyword (Full) is not in our required_keywords_full array, add it now.
		if ( ! isset( $this->required_keywords_full[ $keyword_name ] ) ) {
			$this->required_keywords_full[ $keyword_name ] = array();
		}
		if ( ! in_array( $keyword, $this->required_keywords_full[ $keyword_name ], true ) ) {
			$this->required_keywords_full[ $keyword_name ][] = $keyword;
		}

	}

	/**
	 * Returns just the keyword name, excluding any columns, nth terms and transformations
	 *
	 * @since   2.6.1
	 *
	 * @param   string $keyword    Keyword.
	 * @return  string              Keyword Name excluding any columns, nth terms and transformations
	 */
	private function extract_keyword_name_from_keyword( $keyword ) {

		if ( strpos( $keyword, ':' ) !== false ) {
			$keyword_parts = explode( ':', $keyword );
			$keyword       = trim( $keyword_parts[0] );
		}

		$keyword = preg_replace( '/\(.*?\)/', '', $keyword );

		return $keyword;

	}

	/**
	 * Callback for array_walk_recursive, which finds $this->searches, replacing with
	 * $this->replacements in $item.
	 *
	 * Also performs spintax.
	 *
	 * @since   1.3.1
	 *
	 * @param   array|object|string $item   Item.
	 * @param   string              $key    Key.
	 */
	private function replace_keywords_in_array( &$item, $key ) {

		// If the settings key's value is an array, walk through it recursively to search/replace
		// Otherwise do a standard search/replace on the string.
		if ( is_array( $item ) ) {
			// Array.
			array_walk_recursive( $item, array( $this, 'replace_keywords_in_array' ) );
		} elseif ( is_object( $item ) ) {
			// Object.
			array_walk_recursive( $item, array( $this, 'replace_keywords_in_array' ) );
		} elseif ( is_string( $item ) ) {
			// If here, we have a string.
			// Perform keyword replacement, spintax and shortcode processing now.

			// If replacements contain an array, we're using the :random_different Keyword Transformation
			// and therefore need to perform a slower search/replace to iterate through every occurance of
			// the same transformation.
			if ( $this->replacements_contain_array ) {
				foreach ( $this->searches as $index => $search ) {
					// Standard search/replace.
					if ( ! is_array( $this->replacements[ $index ] ) ) {
						$item = str_ireplace( $search, $this->replacements[ $index ], $item );
						continue;
					}

					// Pluck a value at random from the array of replacement Terms for the given search, doing this
					// every time we find the Keyword, so we get truly random Terms each time in a single string.
					$pos = stripos( $item, $search );
					while ( $pos !== false ) {
						$item = substr_replace( $item, $this->replacements[ $index ][ wp_rand( 0, ( count( $this->replacements[ $index ] ) - 1 ) ) ], $pos, strlen( $search ) );

						// Search for next occurrence of this Keyword  .
						$pos = stripos( $item, $search, $pos + 1 );
					}
				}
			} else {
				// Replace all searches with all replacements.
				$item = str_ireplace( $this->searches, $this->replacements, $item );
			}

			// Process Spintax.
			$result = $this->base->get_class( 'spintax' )->process( $item );
			if ( is_wp_error( $result ) ) {
				if ( defined( 'PAGE_GENERATOR_PRO_DEBUG' ) && PAGE_GENERATOR_PRO_DEBUG ) {
					// Store the error in the item.
					$item = $result->get_error_message();
					return;
				}
			}

			// Spintax OK - assign to item.
			$item = $result;

			// Process Block Spinning.
			$result = $this->base->get_class( 'block_spin' )->process( $item );
			if ( is_wp_error( $result ) ) {
				if ( defined( 'PAGE_GENERATOR_PRO_DEBUG' ) && PAGE_GENERATOR_PRO_DEBUG ) {
					// Store the error in the item.
					$item = $result->get_error_message();
					return;
				}
			}

			// Block Spinning OK - assign to item.
			$item = $result;

			// Process Conditional Output.
			$result = $this->base->get_class( 'conditional_output' )->process( $item );
			if ( is_wp_error( $result ) ) {
				if ( defined( 'PAGE_GENERATOR_PRO_DEBUG' ) && PAGE_GENERATOR_PRO_DEBUG ) {
					// Store the error in the item.
					$item = $result->get_error_message();
					return;
				}
			}

			// Conditional Output OK - assign to item.
			$item = $result;

			/**
			 * Perform any other keyword replacements or string processing.
			 *
			 * @since   1.9.8
			 *
			 * @param   string  $item   Group Setting String (this can be Post Meta, Custom Fields, Permalink, Title, Content etc).
			 * @param   string  $key    Group Setting Key.
			 */
			$item = apply_filters( 'page_generator_pro_generate_replace_keywords_in_array', $item, $key );
		}

	}

	/**
	 * Determines if a Post already exists that was generated by the given Group ID for the
	 * given Post Type, Parent and Slug
	 *
	 * @since   2.1.8
	 *
	 * @param   int    $group_id       Group ID (0 = any Group).
	 * @param   string $post_type      Generated Post Type.
	 * @param   int    $post_parent    Post Parent (0 = none).
	 * @param   string $post_name      Post Name.
	 * @return  bool|int
	 */
	private function post_exists( $group_id, $post_type, $post_parent, $post_name ) {

		// Fetch valid Post Statuses that can be used when generating content.
		$statuses = array_keys( $this->base->get_class( 'common' )->get_post_statuses() );

		// Build query arguments.
		$args = array(
			'post_type'              => $post_type,
			'post_status'            => $statuses,
			'post_parent'            => $post_parent,
			'post_name__in'          => array( $post_name ),

			// For performance, just return the Post ID and don't update meta or term caches.
			'fields'                 => 'ids',
			'cache_results'          => false,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		);

		// If the Group ID isn't zero, add the Group clause to the query.
		if ( $group_id > 0 ) {
			$args['meta_query'] = array(
				array(
					'key'   => '_page_generator_pro_group',
					'value' => absint( $group_id ),
				),
			);
		}

		// Try to find existing post.
		$existing_post = new WP_Query( $args );

		if ( count( $existing_post->posts ) === 0 ) {
			return false;
		}

		// Return existing Post's ID.
		return $existing_post->posts[0];

	}

	/**
	 * Determines if a Term already exists that was generated by the given Group ID for the
	 * given Taxonomy, Parent and Title.
	 *
	 * @since   2.4.8
	 *
	 * @param   int    $group_id       Group ID (0 = any Group).
	 * @param   string $taxonomy       Generated Term's Taxonomy.
	 * @param   int    $term_parent    Term Parent (0 = none).
	 * @param   string $term_name      Term Name.
	 * @return  bool|int
	 */
	private function term_exists( $group_id, $taxonomy, $term_parent, $term_name ) {

		// Build query arguments.
		$args = array(
			'taxonomy'               => array( $taxonomy ),
			'name'                   => array( $term_name ),
			'hide_empty'             => false,

			// For performance, just return the Post ID and don't update meta or term caches.
			'fields'                 => 'ids',
			'update_term_meta_cache' => false,
		);

		// If a Parent Term ID exists, restrict the above query to only check for existing Terms generated
		// that belong to the Parent.
		if ( $term_parent > 0 ) {
			$args['child_of'] = absint( $term_parent );
		}

		// If the Group ID isn't zero, add the Group clause to the query.
		if ( $group_id > 0 ) {
			$args['meta_query'] = array(
				array(
					'key'   => '_page_generator_pro_group',
					'value' => absint( $group_id ),
				),
			);
		}

		// Try to find existing term.
		$existing_terms = new WP_Term_Query( $args );

		// Bail if no existing terms exist.
		if ( is_null( $existing_terms->terms ) ) { // @phpstan-ignore-line
			return false;
		}

		// Return existing Term's ID.
		return $existing_terms->terms[0];

	}

	/**
	 * Removes some settings from the Group when:
	 * - overwrite is enabled,
	 * - an existing generated Page exists,
	 * - the Content Group is configured to NOT overwrite a given settings section.
	 *
	 * This prevents Dynamic Elements / Shortcodes being processed inside e.g. Custom Fields, when
	 * Custom Fields are not set to be overwritten.
	 *
	 * Settings removed here are where it's likely a Dynamic Element / Shortcode could be placed.
	 *
	 * @since   4.6.2
	 *
	 * @param   array $settings   Group Settings.
	 * @return  array
	 */
	private function remove_settings( $settings ) {

		// Content.
		if ( ! array_key_exists( 'post_content', $settings['overwrite_sections'] ) ) {
			$this->remove_content_from_settings( $settings );
		}

		// Excerpt.
		if ( ! array_key_exists( 'post_excerpt', $settings['overwrite_sections'] ) ) {
			unset( $settings['excerpt'] );
		}

		// Custom Fields.
		if ( ! array_key_exists( 'custom_fields', $settings['overwrite_sections'] ) ) {
			unset( $settings['meta'] );
		}

		// Header & Footer.
		if ( ! array_key_exists( 'header_footer_code', $settings['overwrite_sections'] ) ) {
			unset( $settings['header_code'] );
			unset( $settings['footer_code'] );
		}

		return $settings;

	}

	/**
	 * Removes Content from the Group Settings if it is not selected for overwriting.
	 *
	 * Page Builders hook here to remove their metadata used for the Post Content.
	 *
	 * @since   3.3.8
	 *
	 * @param   array $settings   Group Settings.
	 * @return  array               Group Settings
	 */
	private function remove_content_from_settings( $settings ) {

		// Exclude content from the settings, as it is not needed because it is not going to overwrite the existing generated post's content.
		$settings['content'] = '';

		// Return settings if no Post Meta exists that we need to check.
		if ( ! isset( $settings['post_meta'] ) ) {
			return $settings;
		}

		// Define Post Meta Keys to remove from this Content Group's Settings.
		$ignored_keys = array();

		/**
		 * Defines Post Content related Meta Keys to remove from the Content Group's Settings, so that they are not processed
		 * and not copied to the existing Generated Page, as the Content Group's Overwrite Settings disable overwriting of content
		 *
		 * @since   3.3.8
		 *
		 * @param   array   $ignored_keys   Ignored Keys (preg_match() compatible regex expressions are supported).
		 * @param   array   $settings       Group Settings.
		 */
		$ignored_keys = apply_filters( 'page_generator_pro_generate_remove_content_from_settings_ignored_keys', $ignored_keys, $settings );

		// If no Post Meta Keys need to be ignored, return.
		if ( ! count( $ignored_keys ) ) {
			return $settings;
		}

		// Iterate through Post Meta.
		foreach ( $settings['post_meta'] as $meta_key => $meta_value ) {

			// Remove ignored keys.
			if ( in_array( $meta_key, $ignored_keys, true ) ) {
				unset( $settings['post_meta'][ $meta_key ] );
				continue;
			}

			// Iterate through the ignored keys using preg_match(), so we can support
			// regular expressions.
			foreach ( $ignored_keys as $ignored_key ) {
				// Don't evaluate if not a regular expression.
				if ( strpos( $ignored_key, '/' ) === false ) {
					continue;
				}

				// Don't copy this Meta Key/Value if it's set to be ignored.
				if ( preg_match( $ignored_key, $meta_key ) ) {
					unset( $settings['post_meta'][ $meta_key ] );
					continue 2;
				}
			}
		}

		// Return Group Settings, which will now have no Post Content / Page Builder Meta.
		return $settings;

	}

	/**
	 * Callback for array_walk_recursive, which processes shortcodes.
	 *
	 * @since   1.9.7
	 *
	 * @param   array|object|string $item   Item.
	 * @param   string              $key    Key.
	 */
	private function process_shortcodes_in_array( &$item, $key ) {

		// If the settings key's value is an array, walk through it recursively to search/replace
		// Otherwise do a standard search/replace on the string.
		if ( is_array( $item ) ) {
			// Array.
			array_walk_recursive( $item, array( $this, 'process_shortcodes_in_array' ) );
		} elseif ( is_object( $item ) ) {
			// Object.
			array_walk_recursive( $item, array( $this, 'process_shortcodes_in_array' ) );
		} elseif ( is_string( $item ) ) {
			// If here, we have a string.
			// Perform shortcode processing.
			// Some Page Builders don't use the main Post Content for output, and instead use their own post meta to build the output.
			// Therefore, processing shortcodes on the Post Content would duplicate effort.
			switch ( $key ) {
				case 'content':
					if ( $this->process_shortcodes_on_post_content ) {
						$item = do_shortcode( $item );
					}
					break;

				default:
					$item = do_shortcode( $item );
					break;
			}

			/**
			 * Filter to allow registering and processing shortcodes on a string.
			 *
			 * @since   1.9.8
			 *
			 * @param   string  $item   Group Setting String (this can be Post Meta, Custom Fields, Permalink, Title, Content etc).
			 * @param   string  $key    Group Setting Key.
			 */
			$item = apply_filters( 'page_generator_pro_generate_process_shortcodes_in_array', $item, $key );
		}

	}

	/**
	 * Assigns any Attachments to the given Post ID that have the specified Group ID and Index
	 *
	 * @since   2.4.1
	 *
	 * @param   int $post_id    Generated Post ID.
	 * @param   int $group_id   Group ID.
	 * @param   int $index      Generation Index.
	 * @return  WP_Error|bool
	 */
	private function assign_attachments_to_post_id( $post_id, $group_id, $index ) {

		// Build query.
		$args = array(
			'post_type'      => 'attachment',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'meta_query'     => array(
				array(
					'key'   => '_page_generator_pro_group',
					'value' => absint( $group_id ),
				),
				array(
					'key'   => '_page_generator_pro_index',
					'value' => absint( $index ),
				),
			),
			'fields'         => 'ids',
		);

		// Get all Attachments belonging to the given Group ID and Index.
		$attachments = new WP_Query( $args );

		// If no Attachments found, return false, as there's nothing to assign.
		if ( count( $attachments->posts ) === 0 ) {
			return false;
		}

		// For each Attachment, assign it to the Post.
		foreach ( $attachments->posts as $attachment_id ) {
			$result = wp_update_post(
				array(
					'ID'          => $attachment_id,
					'post_parent' => $post_id,
				),
				true
			);

			if ( is_wp_error( $result ) ) {
				return $result;
			}
		}

		// Done.
		return true;

	}

	/**
	 * Returns a wp_insert_post() and wp_update_post() compatible date and time to publish/schedule the generated post,
	 * based on the Group's settings.
	 *
	 * @since   3.1.8
	 *
	 * @param   array       $settings                       Group Settings.
	 * @param   bool|string $last_generated_post_date_time  Last Generated Post's Date and Time.
	 */
	public function post_date( $settings, $last_generated_post_date_time = false ) {

		// Bail if the Group doesn't have a date option.
		if ( ! isset( $settings['date_option'] ) ) {
			return false;
		}

		// Define the Post Date.
		switch ( $settings['date_option'] ) {

			/**
			 * Specific Date
			 */
			case 'specific':
				// Define date.
				$date = date_create( str_replace( 'T', ' ', $settings['date_specific'] ) );

				// Just return the date and time if Status isn't Scheduled.
				if ( $settings['status'] !== 'future' ) {
					return date_format( $date, 'Y-m-d H:i:s' );
				}

				// If here, Status = Scheduled.
				// Increment the specific date by the schedule hours and unit.
				if ( $last_generated_post_date_time ) {
					$date = date_create( $last_generated_post_date_time );
					$date = date_add( $date, date_interval_create_from_date_string( '+' . $settings['schedule'] . ' ' . $settings['scheduleUnit'] ) );
				}
				break;

			/**
			 * Specific Date from Keyword
			 */
			case 'specific_keyword':
				// Define date.
				if ( is_numeric( $settings['date_specific_keyword'] ) ) {
					// Timestamp.
					$date = date_create( '@' . $settings['date_specific_keyword'] );
					break;
				}

				// Date.
				$date = date_create( str_replace( 'T', ' ', $settings['date_specific_keyword'] ) );

				// If date is false, the Term is invalid or it's still a Keyword i.e. we're trying to establish
				// the last generated post date/time.
				// Set the date to now.
				if ( ! $date ) {
					// Define date as now.
					$date = date_create( date_i18n( 'Y-m-d H:i:s' ) );
				}
				break;

			/**
			 * Random
			 */
			case 'random':
				// Define date based on random settings.
				$min = strtotime( $settings['date_min'] );
				$max = strtotime( $settings['date_max'] );

				$date = date_create( date_i18n( 'Y-m-d H:i:s', wp_rand( $min, $max ) ) );
				break;

			/**
			 * Now
			 */
			case 'now':
			default:
				// Define date as now.
				$date = date_create( date_i18n( 'Y-m-d H:i:s' ) );

				// Just return the date and time if Status isn't Scheduled.
				if ( $settings['status'] !== 'future' ) {
					return date_format( $date, 'Y-m-d H:i:s' );
				}

				// If here, Status = Scheduled.
				// Increment the current date by the schedule hours and unit.
				if ( $last_generated_post_date_time ) {
					$date = date_create( $last_generated_post_date_time );
					$date = date_add( $date, date_interval_create_from_date_string( '+' . $settings['schedule'] . ' ' . $settings['scheduleUnit'] ) );
				}
				break;

		}

		return date_format( $date, 'Y-m-d H:i:s' );

	}

	/**
	 * Defines the Featured Image for the given generated Post ID, if
	 * the Group Settings specify a Featured Image and (if overwriting)
	 * the Featured Image should be overwritten
	 *
	 * @since   2.3.5
	 *
	 * @param   int   $post_id    Generated Post ID.
	 * @param   int   $group_id   Group ID.
	 * @param   int   $index      Generation Index.
	 * @param   array $settings   Group Settings.
	 * @param   array $post_args  wp_insert_post() / wp_update_post() arguments.
	 * @return  WP_Error|bool|int
	 */
	private function featured_image( $post_id, $group_id, $index, $settings, $post_args ) {

		// Bail if the target Post Type doesn't support a Featured Image.
		if ( ! $this->base->get_class( 'common' )->post_type_supports( $settings['type'], 'thumbnail' ) ) {
			return false;
		}

		// Bail if no Featured Image source defined.
		if ( empty( $settings['featured_image_source'] ) ) {
			return false;
		}

		// Bail if we're overwriting an existing Post and don't want to overwrite the Featured Image.
		if ( isset( $post_args['ID'] ) && ! array_key_exists( 'featured_image', $settings['overwrite_sections'] ) ) {
			return false;
		}

		// Assume no image could be fetched.
		$image_id = false;

		/**
		 * Defines the Featured Image for the given generated Post ID, if
		 * the Group Settings specify a Featured Image and (if overwriting)
		 * the Featured Image should be overwritten
		 *
		 * @since   2.9.3
		 *
		 * @param   WP_Error|bool|int    $image_id   Image ID.
		 * @param   int                  $post_id    Generated Post ID.
		 * @param   int                  $group_id   Group ID.
		 * @param   int                  $index      Generation Index.
		 * @param   array                $settings   Group Settings.
		 * @param   array                $post_args  wp_insert_post() / wp_update_post() arguments.
		 * @return  WP_Error|bool|int
		 */
		$image_id = apply_filters( 'page_generator_pro_generate_featured_image_' . $settings['featured_image_source'], false, $post_id, $group_id, $index, $settings, $post_args );

		// Return the error if a WP_Error.
		if ( is_wp_error( $image_id ) ) {
			return $image_id;
		}

		// Bail if no Image ID was defined.
		if ( ! $image_id ) {
			return false;
		}

		// Update Featured Image.
		update_post_meta( $post_id, '_thumbnail_id', $image_id );

		// EXIF Data for Featured Image.
		$exif = $this->base->get_class( 'exif' )->write(
			$image_id,
			$settings['featured_image_exif_description'],
			$settings['featured_image_exif_comments'],
			$settings['featured_image_exif_latitude'],
			$settings['featured_image_exif_longitude']
		);

		if ( is_wp_error( $exif ) ) {
			return $exif;
		}

		// Return Featured Image ID.
		return $image_id;

	}

	/**
	 * Gets the Generated Post Parent for the given Group ID and its settings.
	 *
	 * If the Group has a Parent Group specified, will only return the Post Parent
	 * if it was generated by the Parent Group
	 *
	 * @since   3.0.9
	 *
	 * @param   int   $group_id           Group ID.
	 * @param   array $settings           Group Settings.
	 * @return  WP_Error|int
	 */
	private function get_post_parent( $group_id, $settings ) {

		// Assume there isn't a Post Parent.
		$post_parent = 0;

		// If Post Parent isn't set, bail.
		if ( ! isset( $settings['pageParent'] ) ) {
			return 0;
		}
		if ( ! isset( $settings['pageParent'][ $settings['type'] ] ) ) {
			return 0;
		}
		if ( empty( $settings['pageParent'][ $settings['type'] ] ) ) {
			return 0;
		}

		// Get Post Parent.
		$post_parent = $settings['pageParent'][ $settings['type'] ];

		// If the Post Parent isn't a number, convert to a Post ID.
		if ( ! is_numeric( $post_parent ) ) {
			// Convert to a slug, retaining forwardslashes.
			// This also converts special accented characters to non-accented versions.
			$post_parent = $this->base->get_class( 'common' )->sanitize_slug( $post_parent );

			// Find the Post ID based on the given name.
			$parent_page = get_page_by_path( $post_parent, OBJECT, $settings['type'] );

			// Throw an error, as we require a Post Parent but couldn't find one.
			if ( ! $parent_page ) {
				return new WP_Error(
					'page_generator_pro_generate_get_post_parent',
					sprintf(
						/* translators: slug */
						__( 'Could not find a parent page with the slug <code>%1$s</code> (<code>%2$s</code>). Make sure this %3$s is generated or manually created first', 'page-generator-pro' ),
						$post_parent,
						get_bloginfo( 'url' ) . '/' . $post_parent,
						$settings['type']
					)
				);
			}

			// Fetch the ID.
			$post_parent = $parent_page->ID;
		}

		// Check the post parent exists.
		if ( ! get_post( $post_parent ) ) {
			return new WP_Error(
				'page_generator_pro_generate_get_post_parent',
				sprintf(
					/* translators: Post ID */
					__( 'Could not find a parent page with ID %s', 'page-generator-pro' ),
					$post_parent
				)
			);
		}

		// If no Parent Group specified, just return the Post Parent now.
		$group_parent_id = wp_get_post_parent_id( $group_id );
		if ( ! $group_parent_id ) {
			return $post_parent;
		}

		// As the Group Parent is specified, don't return the Post Parent if it was not generated by the Parent Group.
		if ( ! $this->base->get_class( 'groups' )->is_generated_by_group( $post_parent, $group_parent_id ) ) {
			return new WP_Error(
				'page_generator_pro_generate_get_post_parent',
				sprintf(
					/* translators: %1$s: Translated word of 'ID' or 'Slug', %2$s: Post Parent ID or Slug, %3$s: Group ID */
					__( 'Parent page with ID %1$s exists, but the parent page was not generated by the specified Parent Group ID %2$s. Either remove the Parent Group setting, or generate the Parent Page through the Parent Group first.', 'page-generator-pro' ),
					$post_parent,
					$group_id
				)
			);
		}

		// Parent Page was generated by the Parent Group, so return the Parent Page ID.
		return $post_parent;

	}

	/**
	 * Copies Attributes > Template to the Generated Post ID, honoring
	 * the Overwrite setting.
	 *
	 * @since   2.9.0
	 *
	 * @param   int   $post_id            Generated Post ID.
	 * @param   int   $group_id           Group ID.
	 * @param   array $settings           Group Settings.
	 * @param   array $post_args          wp_insert_post() / wp_update_post() arguments.
	 * @return  bool                        Updated Page Template on Generated Post ID
	 */
	private function set_page_template( $post_id, $group_id, $settings, $post_args ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// Bail if the target Post Type doesn't support templates.
		if ( ! $this->base->get_class( 'common' )->post_type_supports( $settings['type'], 'templates' ) ) {
			return false;
		}

		// Bail if we're overwriting an existing Post and don't want to overwrite the Template.
		if ( isset( $post_args['ID'] ) && ! array_key_exists( 'attributes', $settings['overwrite_sections'] ) ) {
			return false;
		}

		// Backward compat for Free.
		if ( ! empty( $settings['pageTemplate'] ) && ! is_array( $settings['pageTemplate'] ) ) {
			update_post_meta( $post_id, '_wp_page_template', $settings['pageTemplate'] );
		}
		if ( ! empty( $settings['pageTemplate'][ $settings['type'] ] ) ) {
			update_post_meta( $post_id, '_wp_page_template', $settings['pageTemplate'][ $settings['type'] ] );
		}

		/**
		 * Action to perform any further steps with the Content Group's Page Template
		 * after the Page Template has been copied from the Content Group to the Generated Content.
		 *
		 * @since   2.9.7
		 *
		 * @param   int     $post_id        Generated Page ID.
		 * @param   array   $settings       Group Settings.
		 * @param   array   $post_args      wp_insert_post() / wp_update_post() arguments.
		 */
		do_action( 'page_generator_pro_generate_set_page_template', $post_id, $settings, $post_args );

		return true;

	}

	/**
	 * Copies Custom Fields to the Generated Post ID, if
	 * the Group Settings specify Custom Field data and (if overwriting)
	 * whether the Custom Fields data should be overwritten.
	 *
	 * @since   2.3.5
	 *
	 * @param   int   $post_id            Generated Post ID.
	 * @param   int   $group_id           Group ID.
	 * @param   array $settings           Group Settings.
	 * @param   array $post_args          wp_insert_post() / wp_update_post() arguments.
	 * @param   array $keywords_terms     Keywords / Terms Key / Value array.
	 * @return  bool                        Updated Custom Fields on Generated Post ID
	 */
	private function set_custom_fields( $post_id, $group_id, $settings, $post_args, $keywords_terms ) {

		// Bail if we're overwriting an existing Post and don't want to overwrite the Custom Fields.
		if ( isset( $post_args['ID'] ) && ! array_key_exists( 'custom_fields', $settings['overwrite_sections'] ) ) {
			return false;
		}

		// Custom Fields.
		// delete_post_meta() and add_post_meta() are used instead of update_post_meta() so that
		// multiple Custom Fields with the same Meta Key are all copied over, instead of the last one.
		if ( isset( $settings['meta'] ) && ! empty( $settings['meta'] ) ) {
			// Delete existing Custom Fields.
			foreach ( $settings['meta']['key'] as $meta_index => $meta_key ) {
				delete_post_meta( $post_id, $meta_key );
			}

			// Add Custom Fields.
			foreach ( $settings['meta']['key'] as $meta_index => $meta_key ) {
				add_post_meta( $post_id, $meta_key, $settings['meta']['value'][ $meta_index ] );
			}
		}

		// Store Keywords.
		if ( $settings['store_keywords'] ) {
			$store_keywords = $this->get_keywords_terms_array_with_columns( $keywords_terms );

			if ( $store_keywords ) {
				foreach ( $store_keywords as $meta_key => $meta_value ) {
					update_post_meta( $post_id, $meta_key, $meta_value );
				}
			}
		}

		/**
		 * Action to perform any further steps with the Content Group's Custom Fields,
		 * after all Custom Fields has been copied from the Content Group to the Generated Content.
		 *
		 * @since   2.9.7
		 *
		 * @param   int     $post_id        Generated Page ID.
		 * @param   int     $group_id       Group ID.
		 * @param   array   $meta           Group Custom Fields.
		 * @param   array   $settings       Group Settings.
		 * @param   array   $post_args      wp_insert_post() / wp_update_post() arguments.
		 * @param   array   $keywords_terms Keywords / Terms Key / Value array.
		 */
		do_action( 'page_generator_pro_generate_set_custom_fields', $post_id, $group_id, $settings['meta'], $settings, $post_args, $keywords_terms );

		return true;

	}

	/**
	 * Stores Header and Footer code in the Generated Post ID as metadata, if
	 * the Group Settings specify Header and/or Footer data and (if overwriting)
	 * whether the Header and Footer data should be overwritten.
	 *
	 * @since   4.3.2
	 *
	 * @param   int   $post_id            Generated Post ID.
	 * @param   int   $group_id           Group ID.
	 * @param   array $settings           Group Settings.
	 * @param   array $post_args          wp_insert_post() / wp_update_post() arguments.
	 * @param   array $keywords_terms     Keywords / Terms Key / Value array.
	 * @return  bool                        Updated Custom Fields on Generated Post ID
	 */
	private function set_header_footer_code( $post_id, $group_id, $settings, $post_args, $keywords_terms ) {

		// Bail if we're overwriting an existing Post and don't want to overwrite the Header and Footer Code.
		if ( isset( $post_args['ID'] ) && ! array_key_exists( 'header_footer_code', $settings['overwrite_sections'] ) ) {
			return false;
		}

		// Delete existing Header and Footer Code.
		delete_post_meta( $post_id, '_page_generator_pro_header_code' );
		delete_post_meta( $post_id, '_page_generator_pro_footer_code' );

		// Add Header Code if defined.
		if ( array_key_exists( 'header_code', $settings ) && ! empty( $settings['header_code'] ) ) {
			add_post_meta( $post_id, '_page_generator_pro_header_code', $settings['header_code'] );
		}

		// Add Footer Code if defined.
		if ( array_key_exists( 'footer_code', $settings ) && ! empty( $settings['footer_code'] ) ) {
			add_post_meta( $post_id, '_page_generator_pro_footer_code', $settings['footer_code'] );
		}

		/**
		 * Action to perform any further steps with the Content Group's Header and Footer Code,
		 * after Header and Footer code has been copied from the Content Group to the Generated Content.
		 *
		 * @since   4.3.2
		 *
		 * @param   int     $post_id        Generated Page ID.
		 * @param   int     $group_id       Group ID.
		 * @param   array   $settings       Group Settings.
		 * @param   array   $post_args      wp_insert_post() / wp_update_post() arguments.
		 * @param   array   $keywords_terms Keywords / Terms Key / Value array.
		 */
		do_action( 'page_generator_pro_generate_set_header_footer_code', $post_id, $group_id, $settings, $post_args, $keywords_terms );

		return true;

	}

	/**
	 * Copies the Content Group's Post Meta to the Generated Post ID,
	 * including Page Builder / ACF data.
	 *
	 * @since   2.3.5
	 *
	 * @param   int   $post_id    Generated Post ID.
	 * @param   int   $group_id   Group ID.
	 * @param   array $settings   Group Settings.
	 * @param   array $post_args  wp_insert_post() / wp_update_post() arguments.
	 * @return  bool                Updated Post Meta on Generated Post ID
	 */
	private function set_post_meta( $post_id, $group_id, $settings, $post_args ) {

		// Bail if no Post Meta to copy to the generated Post.
		if ( ! isset( $settings['post_meta'] ) ) {
			return false;
		}

		// Define the metadata to ignore.
		$ignored_keys = array(
			'_wp_page_template',
		);

		/**
		 * Defines Post Meta Keys in a Content Group to ignore and not copy to generated Posts / Groups.
		 *
		 * @since   2.6.1
		 *
		 * @param   array   $ignored_keys   Ignored Keys (preg_match() compatible regex expressions are supported).
		 * @param   int     $post_id        Generated Post ID.
		 * @param   array   $settings       Group Settings.
		 * @param   array   $post_args      wp_insert_post() / wp_update_post() arguments.
		 */
		$ignored_keys = apply_filters( 'page_generator_pro_generate_set_post_meta_ignored_keys', $ignored_keys, $post_id, $settings, $post_args );

		// Iterate through Post Meta.
		foreach ( $settings['post_meta'] as $meta_key => $meta_value ) {

			// Skip ignored keys.
			if ( in_array( $meta_key, $ignored_keys, true ) ) {
				continue;
			}

			// Iterate through the ignored keys using preg_match(), so we can support
			// regular expressions.
			foreach ( $ignored_keys as $ignored_key ) {
				// Don't evaluate if not a regular expression.
				if ( strpos( $ignored_key, '/' ) === false ) {
					continue;
				}

				// Don't copy this Meta Key/Value if it's set to be ignored.
				if ( preg_match( $ignored_key, $meta_key ) ) {
					continue 2;
				}
			}

			/**
			 * Filters the Group Metadata for the given Key and Value, immediately before it's
			 * saved to the Generated Page.
			 *
			 * @since   2.6.1
			 *
			 * @param   array|string|int|bool   $meta_value Meta Value.
			 * @param   int                     $post_id    Generated Post ID.
			 * @param   int                     $group_id   Group ID.
			 * @param   array                   $settings   Group Settings.
			 * @param   array                   $post_args  wp_insert_post() / wp_update_post() arguments.
			 */
			$meta_value = apply_filters( 'page_generator_pro_generate_set_post_meta_' . $meta_key, $meta_value, $post_id, $group_id, $settings, $post_args );

			// Update Generated Page's Meta Value.
			update_post_meta( $post_id, $meta_key, $meta_value );

		}

		/**
		 * Action to perform any further steps with the Content Group's Post Meta,
		 * after all Post Meta has been copied  from the Content Group to the Generated Content.
		 *
		 * @since   2.9.7
		 *
		 * @param   int     $post_id        Generated Page ID.
		 * @param   int     $group_id       Group ID.
		 * @param   array   $post_meta      Group Post Meta.
		 * @param   array   $settings       Group Settings.
		 * @param   array   $post_args      wp_insert_post() / wp_update_post() arguments.
		 */
		do_action( 'page_generator_pro_generate_set_post_meta', $post_id, $group_id, $settings['post_meta'], $settings, $post_args );

		return true;

	}

	/**
	 * Copies the Term Group's Term Meta to the Generated Term ID,
	 * including Yoast / ACF data.
	 *
	 * @since   2.6.3
	 *
	 * @param   int   $term_id    Generated Term ID.
	 * @param   int   $group_id   Group ID.
	 * @param   array $settings   Group Settings.
	 * @param   array $term_args  wp_insert_term() / wp_update_term() arguments.
	 * @return  bool                Updated Term Meta on Generated Term ID
	 */
	private function set_term_meta( $term_id, $group_id, $settings, $term_args ) {

		// Bail if no Term Meta to copy to the generated Term.
		if ( ! isset( $settings['term_meta'] ) ) {
			return false;
		}

		// Define the metadata to ignore.
		$ignored_keys = array();

		/**
		 * Defines Term Meta Keys in a Term Group to ignore and not copy to generated Terms / Groups.
		 *
		 * @since   2.6.3
		 *
		 * @param   array   $ignored_keys   Ignored Keys (preg_match() compatible regex expressions are supported).
		 * @param   int     $term_id        Generated Term ID.
		 * @param   array   $settings       Group Settings.
		 * @param   array   $term_args      wp_insert_term() / wp_update_term() arguments.
		 */
		$ignored_keys = apply_filters( 'page_generator_pro_generate_set_term_meta_ignored_keys', $ignored_keys, $term_id, $settings, $term_args );

		// Iterate through Term Meta.
		foreach ( $settings['term_meta'] as $meta_key => $meta_value ) {

			// Skip ignored keys.
			if ( in_array( $meta_key, $ignored_keys, true ) ) {
				continue;
			}

			// Iterate through the ignored keys using preg_match(), so we can support
			// regular expressions.
			foreach ( $ignored_keys as $ignored_key ) {
				// Don't evaluate if not a regular expression.
				if ( strpos( $ignored_key, '/' ) === false ) {
					continue;
				}

				// Don't copy this Meta Key/Value if it's set to be ignored.
				if ( preg_match( $ignored_key, $meta_key ) ) {
					continue 2;
				}
			}

			/**
			 * Filters the Group Metadata for the given Key and Value, immediately before it's
			 * saved to the Generated Term.
			 *
			 * @since   2.6.3
			 *
			 * @param   array|string|int|object   $value  Meta Value.
			 */
			$meta_value = apply_filters( 'page_generator_pro_generate_set_term_meta_' . $meta_key, $meta_value );

			// Update Generated Term's Meta Value.
			update_term_meta( $term_id, $meta_key, $meta_value );

		}

		/**
		 * Action to perform any further steps with the Term Group's Post Meta,
		 * after all Post Meta has been copied from the Term Group to the Generated Term.
		 *
		 * @since   2.9.7
		 *
		 * @param   int     $term_id        Generated Term ID.
		 * @param   int     $group_id       Group ID.
		 * @param   array   $term_meta      Group Term Meta.
		 * @param   array   $settings       Group Settings.
		 * @param   array   $term_args      wp_insert_term() / wp_update_term() arguments.
		 */
		do_action( 'page_generator_pro_generate_set_term_meta', $term_id, $group_id, $settings['term_meta'], $settings, $term_args );

		return true;

	}

	/**
	 * Assigns the Generated Post ID to a Menu, if defined in the Group Settings.
	 *
	 * If the Generated Post ID is already an item in the Menu, replaces it.
	 *
	 * @since   2.7.1
	 *
	 * @param   int   $post_id    Generated Post ID.
	 * @param   array $settings   Group Settings.
	 * @param   array $post_args  wp_insert_post() / wp_update_post() arguments.
	 * @return  bool|WP_Error|int
	 */
	private function set_menu( $post_id, $settings, $post_args ) {

		// Bail if no Menu is specified.
		if ( ! isset( $settings['menu'] ) || ! $settings['menu'] ) {
			return false;
		}

		// Bail if we're overwriting an existing Post and don't want to overwrite the Custom Fields.
		if ( isset( $post_args['ID'] ) && ! array_key_exists( 'menu', $settings['overwrite_sections'] ) ) {
			return false;
		}

		// Build menu arguments.
		$args = array(
			'menu-item-object-id' => $post_id,
			'menu-item-object'    => $settings['type'],
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
		);

		// If a title exists, use it instead.
		if ( isset( $settings['menu_title'] ) && ! empty( $settings['menu_title'] ) ) {
			$args['menu-item-title'] = $settings['menu_title'];
		}

		// If a parent is specified, try to find the parent menu item.
		if ( isset( $settings['menu_parent'] ) && ! empty( $settings['menu_parent'] ) ) {
			// If the parent is numeric, use it as the parent ID.
			if ( is_numeric( $settings['menu_parent'] ) ) {
				$args['menu-item-parent-id'] = $settings['menu_parent'];
			} else {
				$parent_menu_item_id = $this->get_menu_item_by_title( $settings['menu_parent'], $settings['menu'] );
				if ( $parent_menu_item_id ) {
					$args['menu-item-parent-id'] = $parent_menu_item_id;
				}
			}
		}

		// Update (or create) Menu Item.
		return wp_update_nav_menu_item(
			$settings['menu'],
			$this->get_menu_item_by_post_id( $post_id, $settings['menu'] ),
			$args
		);

	}

	/**
	 * Gets the given Post ID's Menu Item ID, if it exists in the given Menu
	 *
	 * @since   2.7.1
	 *
	 * @param   int $post_id            Generated Post ID.
	 * @param   int $menu_id            Menu ID.
	 * @param   int $menu_item_parent   Restrict search by Menu Item Parent.
	 * @return  bool|int                Existing Menu Item ID | false
	 */
	private function get_menu_item_by_post_id( $post_id, $menu_id, $menu_item_parent = 0 ) {

		$menu_items = wp_get_nav_menu_items( $menu_id );

		if ( empty( $menu_items ) ) {
			return false;
		}

		foreach ( $menu_items as $menu_item ) {
			// Skip this menu item if it doesn't match the Post ID.
			if ( (int) $menu_item->object_id !== $post_id ) {
				continue;
			}

			// Skip this menu item if it doesn't match the Menu Parent ID.
			if ( $menu_item_parent && (int) $menu_item->menu_item_parent !== $menu_item_parent ) {
				continue;
			}

			// Matching Menu Item found.
			return $menu_item->db_id;
		}

		// If here, this Post doesn't exist in the Menu.
		return false;

	}

	/**
	 * Returns a Menu Item ID by its Title, if it exists in the given Menu
	 *
	 * @since   2.7.5
	 *
	 * @param   string $title              Menu Title.
	 * @param   int    $menu_id            Menu ID.
	 * @param   int    $menu_item_parent   Restrict search by Menu Item Parent.
	 * @return  bool|int                    Existing Menu Item ID | false
	 */
	private function get_menu_item_by_title( $title, $menu_id, $menu_item_parent = 0 ) {

		$menu_items = wp_get_nav_menu_items( $menu_id );
		if ( empty( $menu_items ) ) {
			return false;
		}

		foreach ( $menu_items as $menu_item ) {
			// Skip this menu item if it doesn't match the Title.
			if ( $menu_item->title !== $title ) {
				continue;
			}

			// Skip this menu item if it doesn't match the Menu Parent ID.
			if ( $menu_item_parent && $menu_item->menu_item_parent !== $menu_item_parent ) {
				continue;
			}

			// Matching Menu Item found.
			return $menu_item->db_id;
		}

		// If here, this Post doesn't exist in the Menu.
		return false;

	}

	/**
	 * Stores the Latitude and Longitude in the Geo table against the generated Post ID and Group ID
	 *
	 * If the settings are missing a latitude and longitude, or they're not numbers, something
	 * went wrong.
	 *
	 * @since   2.3.6
	 *
	 * @param   int   $post_id    Generated Post ID.
	 * @param   int   $group_id   Group ID.
	 * @param   array $settings   Group Settings.
	 * @return  WP_Error|bool
	 */
	private function latitude_longitude( $post_id, $group_id, $settings ) {

		// Bail if we don't have a latitude or longitude.
		if ( empty( $settings['latitude'] ) && empty( $settings['longitude'] ) ) {
			return new WP_Error(
				'page_generator_pro_generate_latitude_longitude_error',
				__( 'The specified latitude and longitude values are blank. Check they exist in the Keyword.', 'page-generator-pro' )
			);
		}
		if ( ! $this->base->get_class( 'geo' )->is_latitude( $settings['latitude'] ) ) {
			return new WP_Error(
				'page_generator_pro_generate_latitude_longitude_error',
				sprintf(
					/* translators: Latitude Value */
					__( 'The specified latitude value %s is not a valid latitude. Correct this in the Keyword or Group.', 'page-generator-pro' ),
					$settings['latitude']
				)
			);
		}
		if ( ! $this->base->get_class( 'geo' )->is_longitude( $settings['longitude'] ) ) {
			return new WP_Error(
				'page_generator_pro_generate_latitude_longitude_error',
				sprintf(
					/* translators: Longitude Value */
					__( 'The specified longitude value %s is not a valid longitude. Correct this in the Keyword or Group.', 'page-generator-pro' ),
					$settings['longitude']
				)
			);
		}

		// Insert / Update Latitude and Longitude against this Post ID and Group ID.
		return $this->base->get_class( 'geo' )->update(
			$post_id,
			$group_id,
			$settings['latitude'],
			$settings['longitude']
		);

	}

	/**
	 * Assigns Taxonomy Terms to the Generated Post ID
	 *
	 * @since   1.9.5
	 *
	 * @param   int   $post_id    Generated Post ID.
	 * @param   array $settings   Group Settings.
	 * @param   array $post_args  wp_insert_post() / wp_update_post() arguments.
	 * @return  bool|WP_Error       false: No Terms assigned due to Group Settings.
	 *                              true: Terms assigned.
	 *                              WP_Error: Attempted to assign Terms but an error occured.
	 */
	private function assign_taxonomy_terms_to_post( $post_id, $settings, $post_args ) {

		// Bail if the target Post Type doesn't support Taxonomies.
		if ( ! $this->base->get_class( 'common' )->post_type_supports( $settings['type'], 'taxonomies' ) ) {
			return false;
		}

		// Bail if we're overwriting an existing Post and don't want to overwrite Taxonomy Terms.
		if ( isset( $post_args['ID'] ) && ! array_key_exists( 'taxonomies', $settings['overwrite_sections'] ) ) {
			return false;
		}

		// Define Taxonomy Terms and Post Type from Settings.
		$taxonomy_terms = $settings['tax'];
		$post_type      = $settings['type'];

		// Get Post Type Taxonomies.
		$taxonomies = $this->base->get_class( 'common' )->get_post_type_taxonomies( $post_type );

		// Bail if no Taxonomies exist.
		if ( count( $taxonomies ) === 0 ) {
			return true;
		}

		// Iterate through Taxonomies.
		foreach ( $taxonomies as $taxonomy ) {
			// Cleanup from last iteration.
			unset( $terms );

			// Bail if no Terms exist for this Taxonomy.
			if ( ! isset( $taxonomy_terms[ $taxonomy->name ] ) ) {
				continue;
			}
			if ( empty( $taxonomy_terms[ $taxonomy->name ] ) ) {
				continue;
			}
			if ( is_array( $taxonomy_terms[ $taxonomy->name ] ) && count( $taxonomy_terms[ $taxonomy->name ] ) === 0 ) { // @phpstan-ignore-line
				continue;
			}

			// Build Terms, depending on whether the Taxonomy is hierarchical or not.
			switch ( $taxonomy->hierarchical ) {
				case true:
					foreach ( $taxonomy_terms[ $taxonomy->name ] as $tax_id => $terms_string ) {
						// If Tax ID is not zero, the Term already exists in the Taxonomy.
						// Just add it to the Terms array.
						if ( $tax_id != 0 ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseNotEqual
							$terms[] = (int) $tax_id;
							continue;
						}

						// Convert string to array.
						$terms_arr = str_getcsv( $terms_string, ',', "\"", "\\" ); // phpcs:ignore Squiz.Strings.DoubleQuoteUsage.NotRequired
						foreach ( $terms_arr as $new_term ) {
							// If the term is null, ignore it.
							if ( is_null( $new_term ) ) {
								continue;
							}

							// Remove leading or trailing whitespace.
							$new_term = trim( $new_term );

							// If the term is blank, ignore it.
							if ( empty( $new_term ) ) {
								continue;
							}

							// Check if this named term already exists in the taxonomy.
							$result = term_exists( $new_term, $taxonomy->name );
							if ( $result !== 0 && $result !== null ) {
								$terms[] = (int) $result['term_id'];
								continue;
							}

							// Term does not exist in the taxonomy - create it.
							$result = wp_insert_term( $new_term, $taxonomy->name );

							// Skip if something went wrong.
							if ( is_wp_error( $result ) ) {
								return $result;
							}

							// Add to term IDs.
							$terms[] = (int) $result['term_id'];
						}
					}
					break;

				case false:
				default:
					$terms = $taxonomy_terms[ $taxonomy->name ];
					break;
			}

			// If terms are not set or empty for this Taxonomy, continue.
			if ( ! isset( $terms ) || empty( $terms ) ) {
				continue;
			}

			// Assign Terms to Post.
			$result = wp_set_post_terms( $post_id, $terms, $taxonomy->name, false );

			// Bail if an error occured.
			if ( is_wp_error( $result ) ) {
				return $result;
			}
		}

		// Terms assigned to Post successfully.
		return true;

	}

	/**
	 * Checks the given Group settings to see whether we need to process shortcodes on
	 * the main Post Content.
	 *
	 * @since   1.9.5
	 *
	 * @param   array $settings   Group Settings.
	 * @return  bool                Process Shortcodes
	 */
	private function should_process_shortcodes_on_post_content( $settings ) {

		// Assume that we will process shortcodes on the Post Content.
		$process = true;

		/**
		 * Flag whether the given Group should process shortcodes on the main Post Content
		 * (i.e. $post->post_content).
		 *
		 * @since   2.6.1
		 *
		 * @param   bool    $process    Process Shortcodes on Post Content.
		 * @param   array   $settings   Group Settings.
		 * @return  bool                Process Shortcodes on Post Content
		 */
		$process = apply_filters( 'page_generator_pro_generate_should_process_shortcodes_on_post_content', $process, $settings );

		// Return result.
		return $process;

	}

	/**
	 * Generates Comments for the given generated Post ID, based on
	 * the Content Group's Comments settings
	 *
	 * @since   2.8.8
	 *
	 * @param   int   $post_id    Generated Post ID.
	 * @param   int   $group_id   Group ID.
	 * @param   int   $index      Generation Index.
	 * @param   array $settings   Group Settings.
	 * @param   array $post_args  wp_insert_post() / wp_update_post() arguments.
	 * @return  WP_Error|bool
	 */
	private function generate_comments( $post_id, $group_id, $index, $settings, $post_args ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// Bail if the target Post Type doesn't support Comments.
		if ( ! $this->base->get_class( 'common' )->post_type_supports( $settings['type'], 'comments' ) ) {
			return false;
		}

		// Bail if Generating Comments isn't enabled.
		if ( ! isset( $settings['comments_generate'] ) ) {
			return false;
		}
		if ( ! isset( $settings['comments_generate']['enabled'] ) ) {
			return false;
		}
		if ( ! $settings['comments_generate']['enabled'] ) {
			return false;
		}

		// Bail if we're overwriting an existing Post and don't want to overwrite Comments.
		if ( isset( $post_args['ID'] ) && ! array_key_exists( 'comments', $settings['overwrite_sections'] ) ) {
			return false;
		}

		// Build a class array of required keywords that need replacing with data.
		$required_keywords = $this->find_keywords_in_settings( $settings['comments_generate'] );
		if ( count( $required_keywords['required_keywords'] ) > 0 ) {
			$this->keywords = $this->get_keywords_terms_columns_delimiters( $required_keywords['required_keywords'] );
		}

		// Determine the number of comments to generate.
		$limit = absint( $settings['comments_generate']['limit'] );
		if ( ! $limit ) {
			$limit = wp_rand( 0, 10 );
		}

		// Delete Comments.
		$result = $this->delete_comments( $group_id, $post_id );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Reset search and replace arrays before we build them for each Comment.
		$this->reset_search_replace_arrays();

		// Generate Comments.
		for ( $comments_index = 0; $comments_index < $limit; $comments_index++ ) {
			// If no Keywords exist in the Generate Comments section of the Content Group, don't build search/replace arrays.
			if ( count( $this->keywords['terms'] ) ) {
				$this->build_search_replace_arrays(
					$required_keywords['required_keywords_full'],
					$this->get_keywords_terms( $settings['method'], (int) $comments_index )
				);
			}

			// Replace Keywords and process Spintax.
			$comments_settings = $this->replace_keywords( $settings['comments_generate'] );

			// Generate Comment.
			$result = $this->generate_comment( $post_id, $group_id, $comments_index, $comments_settings );
			if ( is_wp_error( $result ) ) {
				return $result;
			}
		}

		// Done.
		return true;

	}

	/**
	 * Generates a single Comment for the given generated Post ID, based on
	 * the Content Group's Comments settings
	 *
	 * @since   2.8.8
	 *
	 * @param   int   $post_id          Generated Post ID.
	 * @param   int   $group_id         Group ID.
	 * @param   int   $comments_index   Comment Generation Index.
	 * @param   array $settings         Group Settings.
	 * @return  WP_Error|int
	 */
	private function generate_comment( $post_id, $group_id, $comments_index, $settings ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// Define the Comment Date.
		switch ( $settings['date_option'] ) {
			/**
			 * Now
			 */
			case 'now':
				$comment_date = date_i18n( 'Y-m-d H:i:s' );
				break;

			/**
			 * Specific Date
			 */
			case 'specific':
				$comment_date = $settings['date_specific'];
				break;

			/**
			 * Random
			 */
			case 'random':
			default:
				$min          = strtotime( $settings['date_min'] );
				$max          = strtotime( $settings['date_max'] );
				$comment_date = date_i18n( 'Y-m-d H:i:s', wp_rand( $min, $max ) );
				break;
		}

		// Process Shortcodes for this Comment.
		array_walk_recursive( $settings, array( $this, 'process_shortcodes_in_array' ) );

		// Build array of comment arguments.
		$comment_args = array(
			'comment_author'   => $settings['firstname'] . ' ' . $settings['surname'],
			'comment_content'  => $settings['comment'],
			'comment_date'     => $comment_date,
			'comment_date_gmt' => $comment_date,
			'comment_post_ID'  => $post_id,
			'comment_meta'     => array(
				'_page_generator_pro_group' => $group_id,
			),
		);

		/**
		 * Filters arguments used for creating a Comment when running
		 * content generation for a specific Group ID and Post ID
		 *
		 * @since   2.8.8
		 *
		 * @param   array   $comment_args   wp_insert_comment() compatible arguments.
		 * @param   array   $settings       Content Group's Comment Settings.
		 */
		$comment_args = apply_filters( 'page_generator_pro_generate_comment_args', $comment_args, $settings );

		// Insert comment.
		$comment_id = wp_insert_comment( $comment_args );

		// If false, something went wrong.
		if ( ! $comment_id ) {
			return new WP_Error(
				'page_generator_pro_generate_comment_error',
				sprintf(
					/* translators: Post ID */
					__( 'Unable to generate comment for Post ID %s', 'page-generator-pro' ),
					$post_id
				)
			);
		}

		return $comment_id;

	}

	/**
	 * Get Term ID by the given Term Path by recursing through each Term in the Term Path,
	 * finding its Term object in WordPress as a child of the previous Term in the Term Path.
	 *
	 * Where a Term doesn't exist, it's created.
	 *
	 * @since   3.5.5
	 *
	 * @param   string $taxonomy       Taxonomy.
	 * @param   string $term_path      Term Path (parent|parent/child|parent/child/grandchild etc).
	 * @return  int     $term_id        Term ID
	 */
	private function get_term_path_id( $taxonomy, $term_path ) {

		// Recurse through each Term in the Term Path, finding its Term object in WordPress
		// as a child of the previous Term in the Term Path.
		// Where a Term doesn't exist, it's created.
		$term_path_id = 0;
		foreach ( explode( '/', $term_path ) as $term ) {
			// Build query args.
			$args = array(
				'taxonomy'               => $taxonomy,
				'name'                   => $term,
				'parent'                 => $term_path_id,
				'hide_empty'             => false,

				// For performance, just return the Post ID and don't update meta or term caches.
				'fields'                 => 'ids',
				'update_term_meta_cache' => false,
			);

			// Query to see if this Term exists.
			$existing_terms = new WP_Term_Query( $args );

			// If no Term found, create it.
			if ( is_null( $existing_terms->terms ) ) { // @phpstan-ignore-line
				$new_term = wp_insert_term(
					$term,
					$taxonomy,
					array(
						'parent' => $term_path_id,
					)
				);

				// Store this Term as the Term Path ID.
				$term_path_id = $new_term['term_id'];

				// Move onto the next Term in the Term Path.
				continue;
			}

			// If here, Term exists. Assign its ID to $term_path_id.
			$term_path_id = $existing_terms->terms[0];
		}

		// Term Path ID is the 'final' Term in the path, and the Term ID we want.
		return $term_path_id;

	}

	/**
	 * Main function to trash previously generated Contents
	 * for the given Group ID
	 *
	 * @since   1.2.3
	 *
	 * @param   int        $group_id           Group ID.
	 * @param   int        $limit              Number of Generated Posts to delete (-1 = all).
	 * @param   bool|array $exclude_post_ids   Exclude Post IDs from deletion.
	 * @return  WP_Error|bool
	 */
	public function trash_content( $group_id, $limit = -1, $exclude_post_ids = false ) {

		// Get all Post IDs generated by this Group.
		$post_ids = $this->get_generated_content_post_ids( $group_id, $limit, $exclude_post_ids );

		// Bail if an error occured.
		if ( is_wp_error( $post_ids ) ) {
			return $post_ids;
		}

		// Delete Posts by their IDs.
		foreach ( $post_ids as $post_id ) {
			$result = wp_trash_post( $post_id );
			if ( ! $result ) {
				return new WP_Error(
					'page_generator_pro_generate_trash_content',
					sprintf(
						/* translators: Post ID */
						__( 'Unable to trash generated content with ID = %s', 'page-generator-pro' ),
						$post_id
					)
				);
			}
		}

		/**
		 * Run any actions after all generated content for a given Content Group has been trashd.
		 *
		 * @since   3.4.2
		 *
		 * @param   int     $group_id       Group ID.
		 * @param   array   $post_ids       Generated Post IDs that were deleted.
		 */
		do_action( 'page_generator_pro_generate_trash_content_finished', $group_id, $post_ids );

		// Done.
		return true;

	}

	/**
	 * Main function to delete previously generated Contents
	 * for the given Group ID
	 *
	 * @since   1.2.3
	 *
	 * @param   int        $group_id           Group ID.
	 * @param   int        $limit              Number of Generated Posts to delete (-1 = all).
	 * @param   bool|array $exclude_post_ids   Exclude Post IDs from deletion.
	 * @return  WP_Error|bool
	 */
	public function delete_content( $group_id, $limit = -1, $exclude_post_ids = false ) {

		// Get all Post IDs generated by this Group.
		$post_ids = $this->get_generated_content_post_ids( $group_id, $limit, $exclude_post_ids );

		// Bail if an error occured.
		if ( is_wp_error( $post_ids ) ) {
			return $post_ids;
		}

		// Delete Attachments.
		$this->delete_attachments_by_post_ids( $post_ids, $group_id );
		$this->delete_featured_image_by_post_ids( $post_ids, $group_id );

		// Delete Posts.
		foreach ( $post_ids as $post_id ) {
			$result = wp_delete_post( $post_id, true );
			if ( ! $result ) {
				return new WP_Error(
					'page_generator_pro_generate_delete_content',
					sprintf(
						/* translators: Post ID */
						__( 'Unable to delete generated content with ID = %s', 'page-generator-pro' ),
						$post_id
					)
				);
			}
		}

		/**
		 * Run any actions after all generated content for a given Content Group has been deleted.
		 *
		 * @since   3.4.2
		 *
		 * @param   int     $group_id       Group ID.
		 * @param   array   $post_ids       Generated Post IDs that were deleted.
		 */
		do_action( 'page_generator_pro_generate_delete_content_finished', $group_id, $post_ids );

		return true;

	}

	/**
	 * Main function to delete previously generated Comments
	 * for the given Group ID and Post ID
	 *
	 * @since   2.8.8
	 *
	 * @param   int $group_id   Group ID.
	 * @param   int $post_id    Post ID.
	 * @return  WP_Error|bool
	 */
	public function delete_comments( $group_id, $post_id ) {

		// Get all Comment IDs generated by this Group.
		$comment_ids = $this->get_generated_comment_ids( $group_id, $post_id );

		// Bail if no comments exist.
		if ( ! $comment_ids ) {
			return false;
		}

		// Delete Comments.
		foreach ( $comment_ids as $comment_id ) {
			$result = wp_delete_comment( $comment_id, true );
			if ( ! $result ) {
				return new WP_Error(
					'page_generator_pro_generate_delete_comments',
					sprintf(
						/* translators: Comment ID */
						__( 'Unable to trash generated comment with ID = %s', 'page-generator-pro' ),
						$comment_id
					)
				);
			}
		}

		/**
		 * Run any actions after all generated comments for a given Content Group and Generated Post has been deleted.
		 *
		 * @since   3.4.2
		 *
		 * @param   int     $group_id       Group ID.
		 * @param   int     $post_id        Generated Post ID.
		 * @param   array   $comment_ids    Generated Comment IDs that were deleted.
		 */
		do_action( 'page_generator_pro_generate_delete_comments_finished', $group_id, $post_id, $comment_ids );

		return true;

	}

	/**
	 * Deletes latitude and longitude from the Geo table when a Post is deleted.
	 *
	 * Trashed Posts are unaffected.
	 *
	 * @since   2.3.6
	 *
	 * @param   int $post_id    Post ID.
	 */
	public function delete_latitude_longitude_by_post_id( $post_id ) {

		$this->base->get_class( 'geo' )->delete( $post_id );

	}

	/**
	 * Returns all Post IDs generated by the given Group ID
	 *
	 * @since   1.9.1
	 *
	 * @param   int        $group_id           Group ID.
	 * @param   int        $limit              Number of Post IDs to return (-1 = no limit).
	 * @param   bool|array $exclude_post_ids   Exclude Post IDs.
	 * @param   bool|array $statuses           Post Statuses to include.
	 * @return  WP_Error|array
	 */
	public function get_generated_content_post_ids( $group_id, $limit = -1, $exclude_post_ids = false, $statuses = false ) {

		// Fetch valid Post Statuses that can be used when generating content.
		$statuses = ( ! $statuses ? array_keys( $this->base->get_class( 'common' )->get_post_statuses() ) : $statuses );

		$params = array(
			'post_type'              => 'any',
			'post_status'            => $statuses,
			'posts_per_page'         => $limit,
			'meta_query'             => array(
				array(
					'key'   => '_page_generator_pro_group',
					'value' => absint( $group_id ),
				),
			),
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		);

		// Add excluded Post IDs, if defined.
		if ( is_array( $exclude_post_ids ) ) {
			// Cast to integers.
			foreach ( $exclude_post_ids as $index => $post_id ) {
				$exclude_post_ids[ $index ] = absint( $post_id );
			}

			// Add to query parameters.
			$params['post__not_in'] = $exclude_post_ids;
		}

		// Get all Posts.
		$posts = new WP_Query( $params );

		// If no Posts found, return an error.
		if ( ! $posts->posts || count( $posts->posts ) === 0 ) { // @phpstan-ignore-line
			return new WP_Error( 'page_generator_pro_generate_get_generated_content_post_ids', __( 'No content has been generated by this group.', 'page-generator-pro' ) );
		}

		// Return Post IDs.
		return $posts->posts;

	}

	/**
	 * Returns all Term IDs generated by the given Group ID
	 *
	 * @since   3.0.3
	 *
	 * @param   int        $group_id           Group ID.
	 * @param   int        $limit              Number of Term IDs to return.
	 * @param   bool|array $exclude_term_ids   Exclude Term IDs.
	 * @return  WP_Error|array
	 */
	public function get_generated_term_ids( $group_id, $limit = 999999, $exclude_term_ids = false ) {

		// Get Settings.
		$settings = $this->base->get_class( 'groups_terms' )->get_settings( $group_id, false );

		// Bail if an error occured.
		if ( is_wp_error( $settings ) ) {
			return $settings;
		}

		// Build parameters.
		$params = array(
			'taxonomy'               => $settings['taxonomy'],
			'number'                 => $limit,
			'meta_query'             => array(
				array(
					'key'   => '_page_generator_pro_group',
					'value' => absint( $group_id ),
				),
			),
			'hide_empty'             => false,

			// For performance, just return the Post ID and don't update meta or term caches.
			'fields'                 => 'ids',
			'update_term_meta_cache' => false,
		);

		// Add excluded Term IDs, if defined.
		if ( is_array( $exclude_term_ids ) ) {
			// Cast to integers.
			foreach ( $exclude_term_ids as $index => $term_id ) {
				$exclude_term_ids[ $index ] = absint( $term_id );
			}

			// Add to query parameters.
			$params['exclude'] = $exclude_term_ids;
		}

		// Get all Terms.
		$terms = new WP_Term_Query( $params );

		// If no Terms found, return false, as there's nothing to delete.
		if ( is_null( $terms->terms ) ) { // @phpstan-ignore-line
			return new WP_Error(
				'page_generator_pro_generate_get_generated_term_ids',
				__( 'No Terms have been generated by this group, so there are no Terms to delete.', 'page-generator-pro' )
			);
		}

		// Return Term IDs.
		return $terms->terms;

	}

	/**
	 * Returns all Comment IDs generated by the given Group ID and Post ID
	 *
	 * @since   2.8.8
	 *
	 * @param   int $group_id   Group ID.
	 * @param   int $post_id    Post ID.
	 * @return  bool|array
	 */
	public function get_generated_comment_ids( $group_id, $post_id ) {

		// Get all Comments.
		$comments = new WP_Comment_Query(
			array(
				'post_id'    => $post_id,
				'meta_query' => array(
					array(
						'key'   => '_page_generator_pro_group',
						'value' => absint( $group_id ),
					),
				),
				'fields'     => 'ids',
			)
		);

		// If no Comments found, bail.
		if ( ! $comments->comments || count( $comments->comments ) === 0 ) { // @phpstan-ignore-line
			return false;
		}

		// Return Comment IDs.
		return $comments->comments;

	}

	/**
	 * Gets attachment IDs for the given Post IDs and Content Group ID
	 *
	 * @since   2.7.9
	 *
	 * @param   array $post_ids   Post IDs.
	 * @param   int   $group_id   Group ID.
	 * @return  bool|array
	 */
	private function get_attachment_ids_by_post_ids( $post_ids, $group_id ) {

		$results = new WP_Query(
			array(
				'post_type'       => 'attachment',
				'post_status'     => 'any',
				'posts_per_page'  => -1,
				'post_parent__in' => $post_ids,
				'meta_query'      => array(
					array(
						'key'   => '_page_generator_pro_group',
						'value' => absint( $group_id ),
					),
				),
				'fields'          => 'ids',
			)
		);

		// If no Attachments found, return false.
		if ( count( $results->posts ) === 0 ) {
			return false;
		}

		// Return.
		return $results->posts;

	}

	/**
	 * Gets all featured image IDs for the given Post IDs.
	 *
	 * @since   2.7.9
	 *
	 * @param   array $post_ids   Post IDs.
	 * @param   int   $group_id   Group ID.
	 * @return  bool|array
	 */
	private function get_featured_image_attachment_ids_by_post_ids( $post_ids, $group_id ) {

		$featured_image_ids = array();
		foreach ( $post_ids as $post_id ) {
			$image_id = absint( get_post_meta( $post_id, '_thumbnail_id', true ) );

			// Skip if empty.
			if ( ! $image_id ) {
				continue;
			}

			// Check that the image is assigned to the Content Group.
			// This prevents Featured Images assigned to generated Pages where copy = no from being deleted.
			$featured_image_group_id = absint( get_post_meta( $image_id, '_page_generator_pro_group', true ) );

			// Skip if empty.
			if ( ! $featured_image_group_id ) {
				continue;
			}

			// Skip if the image is assigned to a different Content Group.
			if ( $featured_image_group_id !== $group_id ) {
				continue;
			}

			// Add to array.
			$featured_image_ids[] = $image_id;
		}

		// If no images found, return false.
		if ( ! count( $featured_image_ids ) ) {
			return false;
		}

		return $featured_image_ids;

	}

	/**
	 * Deletes Attachments assigned to the given Post IDs and Group ID, excluding the Featured Image
	 *
	 * @since   2.4.1
	 *
	 * @param   array $post_ids   Post IDs.
	 * @param   int   $group_id   Group ID.
	 * @return  bool                Attachments were deleted (false = no attachments to delete)
	 */
	private function delete_attachments_by_post_ids( $post_ids, $group_id ) {

		// Get all Attachments belonging to the given Post IDs and Group.
		$attachment_ids = $this->get_attachment_ids_by_post_ids( $post_ids, $group_id );

		// If no Attachments found, return false, as there's nothing to delete.
		if ( ! $attachment_ids ) {
			return false;
		}

		// Get all Attachments that are Featured Images for the given Post IDs.
		$featured_images_ids = $this->get_featured_image_attachment_ids_by_post_ids( $post_ids, $group_id );

		// Delete attachments.
		foreach ( $attachment_ids as $attachment_id ) {
			// If the attachment is the Featured Image, don't delete it, as this is handled separately
			// by delete_featured_images_by_post_ids().
			if ( is_array( $featured_images_ids ) && in_array( $attachment_id, $featured_images_ids, true ) ) {
				continue;
			}

			// Delete attachment.
			wp_delete_attachment( $attachment_id );
		}

		return true;

	}

	/**
	 * Deletes Featured Images assigned to the given Post IDs.
	 *
	 * @since   2.7.9
	 *
	 * @param   array $post_ids   Post IDs.
	 * @param   int   $group_id   Group ID.
	 * @return  bool                Featured Image Attachments were deleted (false = no featured image attachments to delete)
	 */
	private function delete_featured_image_by_post_ids( $post_ids, $group_id ) {

		// Get all Attachments that are Featured Images for the given Post IDs and Group.
		$featured_images_ids = $this->get_featured_image_attachment_ids_by_post_ids( $post_ids, $group_id );

		// If no Attachments found, return false, as there's nothing to delete.
		if ( ! $featured_images_ids ) {
			return false;
		}

		// Delete attachments.
		foreach ( $featured_images_ids as $attachment_id ) {
			// Delete attachment.
			wp_delete_attachment( $attachment_id );
		}

		return true;

	}

	/**
	 * Main function to delete previously generated Terms
	 * for the given Group ID
	 *
	 * @since   1.6.1
	 *
	 * @param   int        $group_id           Group ID.
	 * @param   int        $limit              Number of Generated Posts to delete (-1 = all).
	 * @param   bool|array $exclude_term_ids   Exclude Term IDs from deletion.
	 * @return  WP_Error|bool
	 */
	public function delete_terms( $group_id, $limit = 999999, $exclude_term_ids = false ) {

		// Get all Term IDs generated by this Group.
		$term_ids = $this->get_generated_term_ids( $group_id, $limit, $exclude_term_ids );

		// Bail if an error occured.
		if ( is_wp_error( $term_ids ) ) {
			return $term_ids;
		}

		// Get Settings.
		$settings = $this->base->get_class( 'groups_terms' )->get_settings( $group_id, false );

		// Delete Terms.
		foreach ( $term_ids as $term_id ) {
			$result = wp_delete_term( $term_id, $settings['taxonomy'] );
			if ( ! $result ) {
				return new WP_Error(
					'page_generator_pro_generate_delete_terms',
					sprintf(
						/* translators: Term ID */
						__( 'Unable to delete generated Term with ID = %s', 'page-generator-pro' ),
						$term_id
					)
				);
			}
		}

		return true;

	}

	/**
	 * Removes wp_update_post() $post_args that are not selected for overwriting
	 *
	 * @since   2.3.5
	 *
	 * @param   array $overwrite_sections     Sections to Overwrite.
	 * @param   array $post_args              wp_update_post() compatible Post Arguments.
	 * @return  array                           wp_update_post() compatible Post Arguments
	 */
	private function restrict_post_args_by_overwrite_sections( $overwrite_sections, $post_args ) {

		// Fetch all available overwrite sections.
		$all_possible_overwrite_sections = array_keys( $this->base->get_class( 'common' )->get_content_overwrite_sections() );
		$overwrite_sections_to_ignore    = array_diff( $all_possible_overwrite_sections, $overwrite_sections );

		// If all overwrite sections are selected (i.e. no overwrite sections to ignore / skip), just return the post args.
		if ( empty( $overwrite_sections_to_ignore ) ) {
			return $post_args;
		}

		// For each overwrite section to ignore / skip, remove it from the Post Args.
		foreach ( $overwrite_sections_to_ignore as $overwrite_section_to_ignore ) {
			unset( $post_args[ $overwrite_section_to_ignore ] );
		}

		return $post_args;

	}

	/**
	 * Returns an array of data relating to the successfully generated Post or Term,
	 * logging the result if logging is enabled.
	 *
	 * @since   2.1.8
	 *
	 * @param   int         $group_id                       Group ID.
	 * @param   int         $post_or_term_id                Post or Term ID.
	 * @param   string      $post_type_or_taxonomy          Post Type or Taxonomy.
	 * @param   bool        $generated                      Post Generated (false = skipped).
	 * @param   string      $message                        Message to return (created, updated, skipped etc).
	 * @param   int         $start                          Start Time.
	 * @param   bool        $test_mode                      Test Mode.
	 * @param   string      $system                         System (browser|cron|cli).
	 * @param   array       $keywords_terms                 Keywords / Terms Key / Value array used.
	 * @param   bool|string $last_generated_post_date_time  Last Generated Post's Date and Time.
	 * @return  array
	 */
	private function generate_return( $group_id, $post_or_term_id, $post_type_or_taxonomy, $generated, $message, $start, $test_mode, $system, $keywords_terms, $last_generated_post_date_time = false ) {

		// Determine if we're returning data for a generated Post or Term.
		// We check if it's a Taxonomy first as post_type_exists() fails for e.g. WooCommerce Products.
		if ( taxonomy_exists( $post_type_or_taxonomy ) ) {
			// Term.
			$url = get_term_link( $post_or_term_id, $post_type_or_taxonomy );
		} else {
			$url = get_permalink( $post_or_term_id );
			if ( $test_mode ) {
				$url = add_query_arg(
					array(
						'preview' => 'true',
					),
					get_permalink( $post_or_term_id )
				);
			} else {
				$url = get_permalink( $post_or_term_id );
			}
		}

		// Performance debugging.
		$end = ( function_exists( 'hrtime' ) ? hrtime( true ) : microtime( true ) );

		// Strip HTML from Keywords Terms, to avoid issues with Generate via Browser log output.
		foreach ( $keywords_terms as $keyword => $term ) {
			$keywords_terms[ $keyword ] = wp_strip_all_tags( $term );
		}

		// Build result array.
		$result = array(
			// Item.
			'post_id'                       => $post_or_term_id,
			'url'                           => $url,
			'type'                          => ( taxonomy_exists( $post_type_or_taxonomy ) ? 'term' : 'content' ),
			'system'                        => $system,
			'test_mode'                     => $test_mode,
			'generated'                     => $generated,
			'result'                        => 'success',
			'keywords_terms'                => $keywords_terms,
			'last_generated_post_date_time' => $last_generated_post_date_time,
			'message'                       => $message,

			// Performance data.
			'start'                         => $start,
			'end'                           => $end,
			'duration'                      => ( function_exists( 'hrtime' ) ? round( ( ( $end - $start ) / 1e+9 ), 3 ) : round( ( $end - $start ), 2 ) ),
			'memory_usage'                  => round( memory_get_usage() / 1024 / 1024 ),
			'memory_peak_usage'             => round( memory_get_peak_usage() / 1024 / 1024 ),
		);

		// Add to log.
		$this->base->get_class( 'log' )->add( $group_id, $result );

		// Return.
		return $result;

	}

	/**
	 * Returns the supplied WP_Error, logging the result if logging is enabled
	 *
	 * @since   2.8.0
	 *
	 * @param   WP_Error   $error                  WP_Error.
	 * @param   int        $group_id               Group ID.
	 * @param   int        $post_or_term_id        Post or Term ID.
	 * @param   string     $post_type_or_taxonomy  Post Type or Taxonomy.
	 * @param   bool       $test_mode              Test Mode.
	 * @param   string     $system                 System (browser|cron|cli).
	 * @param   bool|array $keywords_terms         Keywords and Terms.
	 * @return  WP_Error                            Error
	 */
	private function generate_error_return( $error, $group_id, $post_or_term_id, $post_type_or_taxonomy, $test_mode, $system, $keywords_terms = false ) {

		// Determine if we're returning data for a generated Post or Term.
		// We check if it's a Taxonomy first as post_type_exists() fails for e.g. WooCommerce Products.
		$url = '';
		if ( $post_or_term_id ) {
			if ( taxonomy_exists( $post_type_or_taxonomy ) ) {
				// Term.
				$url = get_term_link( $post_or_term_id, $post_type_or_taxonomy );
			} elseif ( $test_mode ) {
				// Post, Page or CPT, draft.
				$url = get_bloginfo( 'url' ) . '?p=' . $post_or_term_id . '&preview=true';
			} else {
				// Post, Page or CPT, scheduled/published.
				$url = get_permalink( $post_or_term_id );
			}
		}

		// Build result array.
		$result = array(
			// Item.
			'post_id'           => $post_or_term_id,
			'url'               => $url,
			'type'              => ( taxonomy_exists( $post_type_or_taxonomy ) ? 'term' : 'content' ),
			'system'            => $system,
			'test_mode'         => $test_mode,
			'generated'         => 0,
			'result'            => 'error',
			'keywords_terms'    => $keywords_terms,
			'message'           => implode( "\n", $error->get_error_messages() ),

			// Performance data.
			'start'             => 0,
			'end'               => 0,
			'duration'          => 0,
			'memory_usage'      => round( memory_get_usage() / 1024 / 1024 ),
			'memory_peak_usage' => round( memory_get_peak_usage() / 1024 / 1024 ),
		);

		// Add to log.
		$this->base->get_class( 'log' )->add( $group_id, $result );

		// Return original WP_Error.
		return $error;

	}

	/**
	 * Helper method to call Log class' add_to_debug_log(), and output
	 * to the CLI if required
	 *
	 * @since   3.1.3
	 *
	 * @param   string $message    Message.
	 * @param   string $system     System.
	 * @param   bool   $is_error   Is Error Message.
	 */
	private function add_to_debug_log( $message, $system, $is_error = false ) {

		// Pass to logging class.
		$this->base->get_class( 'log' )->add_to_debug_log( $message );

		// Return if not in CLI.
		if ( $system !== 'cli' ) {
			return;
		}

		// For CLI, output the log message now.
		if ( $is_error ) {
			// Don't stop execution; generate() will determine whether to stop.
			WP_CLI::error( $message, false );
		} else {
			WP_CLI::log( $message );
		}

	}

}
