<?php
/**
 * Common Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Helper and generic functions that don't fit into a specific class.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 1.0.0
 */
class Page_Generator_Pro_Common {

	/**
	 * Holds the base object.
	 *
	 * @since   1.9.8
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   1.9.8
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

	}

	/**
	 * Helper method to retrieve Generation Systems
	 *
	 * @since   2.6.1
	 *
	 * @return  array   Generation Systems
	 */
	public function get_generation_systems() {

		// Get systems.
		$systems = array(
			'browser' => __( 'Browser', 'page-generator-pro' ),
			'cron'    => __( 'Server', 'page-generator-pro' ),
			'cli'     => __( 'WP-CLI', 'page-generator-pro' ),
		);

		/**
		 * Defines available Generation Systems
		 *
		 * @since   2.6.1
		 *
		 * @param   array   $systems    Generation Systems.
		 */
		$systems = apply_filters( 'page_generator_pro_common_get_generation_systems', $systems );

		// Return filtered results.
		return $systems;

	}

	/**
	 * Helper method to retrieve Generation Results
	 *
	 * @since   2.8.0
	 *
	 * @return  array   Generation Results
	 */
	public function get_generation_results() {

		// Get results.
		$results = array(
			'success' => __( 'Success', 'page-generator-pro' ),
			'error'   => __( 'Error', 'page-generator-pro' ),
		);

		/**
		 * Defines available Generation Results
		 *
		 * @since   2.8.0
		 *
		 * @param   array   $results    Generation Results.
		 */
		$results = apply_filters( 'page_generator_pro_common_get_generation_results', $results );

		// Return filtered results.
		return $results;

	}

	/**
	 * Helper method to retrieve public Post Types
	 *
	 * @since   1.1.3
	 *
	 * @return  array   Public Post Types
	 */
	public function get_post_types() {

		// Get public Post Types.
		$types = get_post_types(
			array(
				'public' => true,
			),
			'objects'
		);

		// Remove excluded Post Types from $types.
		$excluded_types = $this->get_excluded_post_types();
		if ( is_array( $excluded_types ) ) {
			foreach ( $excluded_types as $excluded_type ) {
				unset( $types[ $excluded_type ] );
			}
		}

		/**
		 * Defines the available public Post Type Objects that content can be generated for.
		 *
		 * @since   1.1.3
		 *
		 * @param   array   $types  Post Types.
		 */
		$types = apply_filters( 'page_generator_pro_common_get_post_types', $types );

		// Return filtered results.
		return $types;

	}

	/**
	 * Returns an array of Post Types supporting the given feature
	 *
	 * @since   3.3.9
	 *
	 * @param   string $feature    post_type_supports() compatible $feature argument.
	 * @return  array               Post Types supporting feature
	 */
	public function get_post_types_supporting( $feature ) {

		// Get public Post Types.
		$types = get_post_types(
			array(
				'public' => true,
			),
			'objects'
		);

		// Remove excluded Post Types from $types.
		$excluded_types = $this->get_excluded_post_types();
		if ( is_array( $excluded_types ) ) {
			foreach ( $excluded_types as $excluded_type ) {
				unset( $types[ $excluded_type ] );
			}
		}

		// Get some settings we might check.
		$post_types_templates = $this->base->get_class( 'common' )->get_post_types_templates();

		foreach ( $types as $post_type => $type ) {
			// Some features aren't returned by post_type_supports().
			switch ( $feature ) {
				case 'hierarchical':
					// Remove this Post Type if it doesn't support this feature.
					if ( ! $type->hierarchical ) {
						unset( $types[ $post_type ] );
					}
					break;

				case 'templates':
					// Remove this Post Type if it doesn't have any Templates.
					if ( ! $post_types_templates ) {
						unset( $types[ $post_type ] );
						break;
					}
					if ( ! isset( $post_types_templates[ $post_type ] ) ) {
						unset( $types[ $post_type ] );
						break;
					}
					break;

				case 'taxonomies':
					// Remove this Post Type if it doesn't have any Taxonomies.
					if ( ! count( get_object_taxonomies( (string) $post_type ) ) ) {
						unset( $types[ $post_type ] );
					}
					break;

				default:
					// Remove this Post Type if it doesn't support the feature.
					if ( ! post_type_supports( (string) $post_type, $feature ) ) {
						unset( $types[ $post_type ] );
					}
					break;
			}
		}

		// Just get Post Type names.
		return array_values( array_keys( $types ) );

	}

	/**
	 * Improved version of post_type_supports() that can detect whether the Post Type supports:
	 * - Hierarchical structure
	 * - Templates
	 * - Taxonomies
	 *
	 * @since   3.3.9
	 *
	 * @param   string $post_type  Post Type.
	 * @param   string $feature    Feature.
	 * @return  bool                Feature supported by Post Type
	 */
	public function post_type_supports( $post_type, $feature ) {

		return in_array( $post_type, $this->get_post_types_supporting( $feature ), true );

	}

	/**
	 * Helper method to retrieve public Post Types, as key/value pairs
	 *
	 * @since   2.5.1
	 *
	 * @return  array   Public Post Types
	 */
	public function get_post_types_key_value_array() {

		$post_types = array();
		$types      = $this->get_post_types();
		foreach ( $types as $post_type ) {
			$post_types[ $post_type->name ] = $post_type->labels->name;
		}

		return $post_types;

	}

	/**
	 * Helper method to retrieve hierarchical public Post Types
	 *
	 * @since   1.2.1
	 *
	 * @return  array   Public Post Types
	 */
	public function get_hierarchical_post_types() {

		// Get public hierarchical Post Types.
		$types = get_post_types(
			array(
				'public'       => true,
				'hierarchical' => true,
			),
			'objects'
		);

		// Filter out excluded post types.
		$excluded_types = $this->get_excluded_post_types();
		if ( is_array( $excluded_types ) ) {
			foreach ( $excluded_types as $excluded_type ) {
				unset( $types[ $excluded_type ] );
			}
		}

		/**
		 * Defines the available public hierarchical Post Type Objects that content can be generated for.
		 *
		 * @since   1.2.1
		 *
		 * @param   array   $types  Post Types.
		 */
		$types = apply_filters( 'page_generator_pro_common_get_hierarchical_post_types', $types );

		// Return filtered results.
		return $types;

	}

	/**
	 * Helper method to retrieve Post Types that have excerpt support
	 *
	 * @since       1.9.7
	 * @deprecated  3.3.9. Use get_post_types_supporting( 'excerpt' ) instead
	 *
	 * @return  array   Public Post Types supporting Excerpts.
	 */
	public function get_excerpt_post_types() {

		// Warn the developer that they shouldn't use this function.
		_deprecated_function( __FUNCTION__, '3.3.9', 'get_post_types_supporting( \'excerpt\' )' );

		// Get Post Types supporting Excerpts.
		$types = $this->get_post_types_supporting( 'excerpt' );

		/**
		 * Defines the available public hierarchical Post Type Objects that content can be generated for.
		 *
		 * @since   1.9.7
		 *
		 * @param   array   $types  Post Types.
		 */
		$types = apply_filters( 'page_generator_pro_common_get_excerpt_post_types', $types );

		// Return filtered results.
		return $types;

	}

	/**
	 * Helper method to retrieve excluded Post Types
	 *
	 * @since   1.1.3
	 *
	 * @return  array                       Excluded Post Types
	 */
	public function get_excluded_post_types() {

		// Get excluded Post Types.
		$types = array(
			$this->base->get_class( 'post_type' )->post_type_name,
			'attachment',
			'revision',
			'nav_menu_item',
		);

		/**
		 * Defines the Post Type Objects that content cannot be generated for.
		 *
		 * @since   1.1.3
		 *
		 * @param   array   $types  Post Types.
		 */
		$types = apply_filters( 'page_generator_pro_common_get_excluded_post_types', $types );

		// Return filtered results.
		return $types;

	}

	/**
	 * Returns any available Templates for each Post Type
	 *
	 * @since   1.5.8
	 *
	 * @return  bool|array   Post Types and Templates
	 */
	public function get_post_types_templates() {

		// Get Post Types.
		$post_types = $this->get_post_types();

		// Bail if no Post Types.
		if ( empty( $post_types ) ) {
			return false;
		}

		// Load necessary library if get_page_templates() isn't available.
		if ( ! function_exists( 'get_page_templates' ) ) {
			include_once ABSPATH . 'wp-admin/includes/theme.php';
		}

		// Bail if get_page_templates() still isn't available.
		if ( ! function_exists( 'get_page_templates' ) ) {
			return false;
		}

		// Build templates.
		$templates = array();
		foreach ( $post_types as $post_type ) {
			// Skip if this Post Type doesn't have any templates.
			$post_type_templates = get_page_templates( null, $post_type->name );
			if ( empty( $post_type_templates ) ) {
				continue;
			}

			$templates[ $post_type->name ] = $post_type_templates;
		}

		/**
		 * Defines available Theme Templates for each Post Type that can have content
		 * generated for it.
		 *
		 * @since   1.5.8
		 *
		 * @param   array   $templates  Templates by Post Type.
		 */
		$templates = apply_filters( 'page_generator_pro_common_get_post_type_templates', $templates );

		// Return filtered results.
		return $templates;

	}

	/**
	 * Helper method to retrieve all Taxonomies
	 *
	 * @since   1.1.3
	 *
	 * @return  array               Taxonomies
	 */
	public function get_taxonomies() {

		// Get all taxonomies.
		$taxonomies = get_taxonomies();

		// Get information for each taxonomy.
		foreach ( $taxonomies as $index => $taxonomy ) {
			// Ignore our own taxonomy.
			if ( $taxonomy === $this->base->get_class( 'taxonomy' )->taxonomy_name ) {
				unset( $taxonomies[ $index ] );
				continue;
			}

			$taxonomies[ $index ] = get_taxonomy( $taxonomy );
		}

		// Get excluded taxonomies.
		$excluded_taxonomies = $this->get_excluded_taxonomies();

		// Remove excluded taxonomies from the main taxonomies array.
		foreach ( $excluded_taxonomies as $excluded_taxonomy ) {
			unset( $taxonomies[ $excluded_taxonomy ] );
		}

		/**
		 * Defines available taxonomies.
		 *
		 * @since   1.1.3
		 *
		 * @param   array   $taxonomies             Taxonomies.
		 * @param   array   $excluded_taxonomies    Excluded Taxonomies (these have already been removed from $taxonomies).
		 */
		$taxonomies = apply_filters( 'page_generator_pro_common_get_taxonomies', $taxonomies, $excluded_taxonomies );

		// Return filtered results.
		return $taxonomies;

	}

	/**
	 * Helper method to retrieve all Taxonomies as key/value pairs
	 *
	 * @since   2.5.1
	 *
	 * @return  array   Public Post Types
	 */
	public function get_taxonomies_key_value_array() {

		$taxonomies = array();
		foreach ( $this->get_taxonomies() as $taxonomy ) {
			$taxonomies[ $taxonomy->name ] = $taxonomy->labels->name;
		}

		return $taxonomies;

	}

	/**
	 * Helper method to retrieve all Taxonomies, with a flag for each
	 * denoting whether the Taxonomy is hierarchical or not.
	 *
	 * @since   1.8.2
	 *
	 * @return  array   Taxonomies
	 */
	public function get_taxonomies_hierarchical_status() {

		// Get taxonomies.
		$taxonomies                     = $this->get_taxonomies();
		$taxonomies_hierarchical_status = array();

		// Iterate through taxonomies, defining flag for whether they are hierarchical or not.
		foreach ( $taxonomies as $taxonomy ) {
			$taxonomies_hierarchical_status[ $taxonomy->name ] = $taxonomy->hierarchical;
		}

		// Get excluded taxonomies.
		$excluded_taxonomies = $this->get_excluded_taxonomies();

		// Remove excluded taxonomies from the main taxonomies array.
		foreach ( $excluded_taxonomies as $excluded_taxonomy ) {
			unset( $taxonomies_hierarchical_status[ $excluded_taxonomy ] );
		}

		/**
		 * Defines available hierarchical taxonomies.
		 *
		 * @since   1.8.2
		 *
		 * @param   array   $taxonomies             Taxonomies.
		 * @param   array   $excluded_taxonomies    Excluded Taxonomies (these have already been removed from $taxonomies).
		 */
		$taxonomies_hierarchical_status = apply_filters( 'page_generator_pro_common_get_taxonomies_hierarchical_status', $taxonomies_hierarchical_status, $excluded_taxonomies );

		// Return filtered results.
		return $taxonomies_hierarchical_status;

	}

	/**
	 * Helper method to retrieve Taxonomies for the given Post Type
	 *
	 * @since   1.1.3
	 *
	 * @param   string $post_type  Post Type.
	 * @return  array               Taxonomies
	 */
	public function get_post_type_taxonomies( $post_type = '' ) {

		// Get Post Type Taxonomies.
		$taxonomies = get_object_taxonomies( $post_type, 'objects' );

		// Get excluded taxonomies.
		$excluded_taxonomies = $this->get_excluded_taxonomies();

		// Remove excluded taxonomies from the main taxonomies array.
		foreach ( $excluded_taxonomies as $excluded_taxonomy ) {
			unset( $taxonomies[ $excluded_taxonomy ] );
		}

		/**
		 * Defines available taxonomies for the given Post Type.
		 *
		 * @since   1.1.3
		 *
		 * @param   array   $taxonomies             Taxonomies.
		 * @param   array   $excluded_taxonomies    Excluded Taxonomies (these have already been removed from $taxonomies).
		 * @param   string  $post_type              Post Type.
		 */
		$taxonomies = apply_filters( 'page_generator_pro_common_get_post_type_taxonomies', $taxonomies, $excluded_taxonomies, $post_type );

		// Return filtered results.
		return $taxonomies;

	}

	/**
	 * Helper method to retrieve excluded Taxonomies
	 *
	 * @since   1.1.3
	 *
	 * @return  array   Taxonomies
	 */
	private function get_excluded_taxonomies() {

		// Get excluded Taxonomies.
		$excluded_taxonomies = array(
			'nav_menu',
			'link_category',
			'post_format',
		);

		/**
		 * Defines taxonomies to exclude from displaying on the Generate screens.
		 *
		 * @since   1.1.3
		 *
		 * @param   array   $excluded_taxonomies    Excluded Taxonomies.
		 */
		$excluded_taxonomies = apply_filters( 'page_generator_pro_common_get_excluded_taxonomies', $excluded_taxonomies );

		// Return filtered results.
		return $excluded_taxonomies;

	}

	/**
	 * Helper method to return all WordPress User IDs.
	 *
	 * @since   4.6.6
	 *
	 * @return  array   Authors
	 */
	public function get_all_user_ids() {

		// Get all user IDs.
		$user_ids = get_users(
			array(
				'fields'  => 'ID',
				'orderby' => 'ID',
			)
		);

		/**
		 * Defines available user IDs.
		 *
		 * @since   4.6.6
		 *
		 * @param   array   $user_ids    User IDs.
		 */
		$user_ids = apply_filters( 'page_generator_pro_common_get_all_user_ids', $user_ids );

		// Return filtered results.
		return $user_ids;

	}

	/**
	 * Helper method to retrieve post statuses
	 *
	 * @since   1.1.3
	 *
	 * @return  array   Post Statuses
	 */
	public function get_post_statuses() {

		// Get statuses.
		$statuses = array(
			'draft'   => __( 'Draft', 'page-generator-pro' ),
			'future'  => __( 'Scheduled', 'page-generator-pro' ),
			'pending' => __( 'Pending Review', 'page-generator-pro' ),
			'private' => __( 'Private', 'page-generator-pro' ),
			'publish' => __( 'Publish', 'page-generator-pro' ),
		);

		/**
		 * Defines available Post Statuses for generated content.
		 *
		 * @since   1.1.3
		 *
		 * @param   array   $statuses   Statuses.
		 */
		$statuses = apply_filters( 'page_generator_pro_common_get_post_statuses', $statuses );

		// Return filtered results.
		return $statuses;

	}

	/**
	 * Helper method to retrieve post date options
	 *
	 * @since   1.1.6
	 *
	 * @return  array   Date Options
	 */
	public function get_date_options() {

		// Get date options.
		$date_options = array(
			'now'              => __( 'Now', 'page-generator-pro' ),
			'specific'         => __( 'Specify Date', 'page-generator-pro' ),
			'specific_keyword' => __( 'Specify Date from Keyword', 'page-generator-pro' ),
			'random'           => __( 'Random Date', 'page-generator-pro' ),
		);

		/**
		 * Defines available scheduling options for generated content.
		 *
		 * @since   1.1.6
		 *
		 * @param   array   $date_options   Date Options.
		 */
		$date_options = apply_filters( 'page_generator_pro_common_get_date_options', $date_options );

		// Return filtered results.
		return $date_options;

	}

	/**
	 * Helper method to retrieve post schedule units
	 *
	 * @since   1.1.3
	 *
	 * @return  array   Post Schedule Units
	 */
	public function get_schedule_units() {

		// Get units.
		$units = array(
			'minutes' => __( 'Minutes', 'page-generator-pro' ),
			'hours'   => __( 'Hours', 'page-generator-pro' ),
			'days'    => __( 'Days', 'page-generator-pro' ),
			'weeks'   => __( 'Weeks', 'page-generator-pro' ),
			'months'  => __( 'Months', 'page-generator-pro' ),
		);

		/**
		 * Defines available scheduling units.
		 *
		 * @since   1.1.3
		 *
		 * @param   array   $units  Units.
		 */
		$units = apply_filters( 'page_generator_pro_common_get_schedule_units', $units );

		// Return filtered results.
		return $units;

	}

	/**
	 * Helper method to retrieve Post/Page generation methods
	 *
	 * @since   1.1.5
	 *
	 * @return  array   Generation Methods
	 */
	public function get_methods() {

		// Get methods.
		$methods = array(
			'all'        => __( 'All', 'page-generator-pro' ),
			'sequential' => __( 'Sequential', 'page-generator-pro' ),
			'random'     => __( 'Random', 'page-generator-pro' ),
		);

		/**
		 * Defines available content generation methods.
		 *
		 * @since   1.1.5
		 *
		 * @param   array   $methods    Content Generation Methods.
		 */
		$methods = apply_filters( 'page_generator_pro_common_get_methods', $methods );

		// Return filtered results.
		return $methods;

	}

	/**
	 * Helper method to retrieve available overwrite generation methods
	 *
	 * @since   1.5.8
	 *
	 * @param   string $group_type     Group Type.
	 * @return  array                   Overwrite Methods
	 */
	public function get_overwrite_methods( $group_type = 'content' ) {

		// Define the label, depending on the Group Type.
		switch ( $group_type ) {
			case 'terms':
				$label = __( 'Term', 'page-generator-pro' );
				break;

			case 'content':
			default:
				$label = __( 'Page', 'page-generator-pro' );
				break;
		}

		// Get methods.
		$methods = array(
			0                    => __( 'No', 'page-generator-pro' ),
			/* translators: 'Page' or 'Term', translated above */
			'skip_if_exists'     => sprintf( __( 'No, skip if existing %s generated by this Group', 'page-generator-pro' ), $label ),
			/* translators: 'Page' or 'Term', translated above */
			'skip_if_exists_any' => sprintf( __( 'No, skip if existing %s exists', 'page-generator-pro' ), $label ),
			/* translators: 'Page' or 'Term', translated above */
			'overwrite'          => sprintf( __( 'Yes, if existing %s generated by this Group', 'page-generator-pro' ), $label ),
			/* translators: 'Page' or 'Term', translated above */
			'overwrite_any'      => sprintf( __( 'Yes, if existing %s exists', 'page-generator-pro' ), $label ),
		);

		/**
		 * Defines available content overwrite options.
		 *
		 * @since   1.5.8
		 *
		 * @param   array   $methods    Content Overwrite Options.
		 */
		$methods = apply_filters( 'page_generator_pro_common_get_overwrite_methods', $methods );

		// Return filtered results.
		return $methods;

	}

	/**
	 * Helper method to retrieve available overwrite sections for Content Groups
	 *
	 * @since   2.3.5
	 *
	 * @return  array                       Overwrite Sections
	 */
	public function get_content_overwrite_sections() {

		// Get sections.
		$sections = array(
			'post_title'         => __( 'Title', 'page-generator-pro' ),
			'post_content'       => __( 'Content', 'page-generator-pro' ),
			'post_excerpt'       => __( 'Excerpt', 'page-generator-pro' ),
			'post_author'        => __( 'Author', 'page-generator-pro' ),
			'post_date'          => __( 'Published Date', 'page-generator-pro' ),
			'comment_status'     => __( 'Allow Comments', 'page-generator-pro' ),
			'comments'           => __( 'Comments', 'page-generator-pro' ),
			'ping_status'        => __( 'Allow track / pingbacks', 'page-generator-pro' ),
			'custom_fields'      => __( 'Custom Fields', 'page-generator-pro' ),
			'header_footer_code' => __( 'Header & Footer Code', 'page-generator-pro' ),
			'featured_image'     => __( 'Featured Image', 'page-generator-pro' ),
			'attributes'         => __( 'Template', 'page-generator-pro' ),
			'taxonomies'         => __( 'Taxonomies', 'page-generator-pro' ),
			'menu'               => __( 'Menu', 'page-generator-pro' ),
		);

		/**
		 * Defines available content overwrite sections.
		 *
		 * @since   2.3.5
		 *
		 * @param   array   $sections    Content Overwrite Sections
		 */
		$sections = apply_filters( 'page_generator_pro_common_get_content_overwrite_sections', $sections );

		// Return filtered results.
		return $sections;

	}

	/**
	 * Helper method to retrieve Order By options
	 *
	 * @since   1.7.2
	 *
	 * @return  array   Order By Options
	 */
	public function get_order_by_options() {

		// Get order by options.
		$order_by = array(
			'none'          => __( 'No Order', 'page-generator-pro' ),
			'ID'            => __( 'Post ID', 'page-generator-pro' ),
			'author'        => __( 'Author', 'page-generator-pro' ),
			'title'         => __( 'Title', 'page-generator-pro' ),
			'name'          => __( 'Name', 'page-generator-pro' ),
			'type'          => __( 'Post Type', 'page-generator-pro' ),
			'date'          => __( 'Published Date', 'page-generator-pro' ),
			'modified'      => __( 'Modified Date', 'page-generator-pro' ),
			'rand'          => __( 'Random', 'page-generator-pro' ),
			'comment_count' => __( 'Number of Comments', 'page-generator-pro' ),
			'relevance'     => __( 'Relevance', 'page-generator-pro' ),
			'distance'      => __( 'Distance', 'page-generator-pro' ),
		);

		/**
		 * Defines WP_Query compatible order by options
		 *
		 * @since   1.7.2
		 *
		 * @param   array   $order_by   Order By options.
		 */
		$order_by = apply_filters( 'page_generator_pro_common_get_order_by_options', $order_by );

		// Return filtered results.
		return $order_by;

	}

	/**
	 * Helper method to retrieve Location Order By options
	 *
	 * @since   1.7.8
	 *
	 * @return  array   Order By Options
	 */
	public function get_locations_order_by_options() {

		// Get order by options.
		$order_by = array(
			'city_name'       => __( 'City Name', 'page-generator-pro' ),
			'city_population' => __( 'City Population', 'page-generator-pro' ),
			'county_name'     => __( 'County Name', 'page-generator-pro' ),
			'region_name'     => __( 'Region Name', 'page-generator-pro' ),
			'zipcode'         => __( 'ZIP Code', 'page-generator-pro' ),
		);

		/**
		 * Defines GeoRocket API compatible order by options
		 *
		 * @since   1.7.8
		 *
		 * @param   array   $order_by   GeoRocket API order by options.
		 */
		$order_by = apply_filters( 'page_generator_pro_common_get_location_order_by_options', $order_by );

		// Return filtered results.
		return $order_by;

	}

	/**
	 * Helper method to retrieve Order options
	 *
	 * @since   1.7.2
	 *
	 * @return  array   Order Options
	 */
	public function get_order_options() {

		// Get order options.
		$order = array(
			'asc'  => __( 'Ascending (A-Z)', 'page-generator-pro' ),
			'desc' => __( 'Descending (Z-A)', 'page-generator-pro' ),
		);

		/**
		 * Defines WP_Query compatible order options
		 *
		 * @since   1.7.2
		 *
		 * @param   array   $order  Order options
		 */
		$order = apply_filters( 'page_generator_pro_common_get_order_options', $order );

		// Return filtered results.
		return $order;

	}

	/**
	 * Helper method to retrieve Comparison operators
	 *
	 * @since   2.2.8
	 *
	 * @return  array   Comparison Options
	 */
	public function get_comparison_operators() {

		// Get operator options.
		$operators = array(
			'=='       => __( 'Equals', 'page-generator-pro' ),
			'!='       => __( 'Does Not Equal', 'page-generator-pro' ),
			'>'        => __( 'Greater Than', 'page-generator-pro' ),
			'>='       => __( 'Greater Than or Equal To', 'page-generator-pro' ),
			'<'        => __( 'Less Than', 'page-generator-pro' ),
			'<='       => __( 'Less Than or Equal To', 'page-generator-pro' ),
			'LIKE'     => __( 'Similar To', 'page-generator-pro' ),
			'NOT LIKE' => __( 'Not Similar To', 'page-generator-pro' ),
		);

		/**
		 * Define MySQL compliant operator options
		 *
		 * @since   2.2.8
		 *
		 * @param   array   $order  Order options.
		 */
		$operators = apply_filters( 'page_generator_pro_common_get_comparison_operators', $operators );

		// Return filtered results.
		return $operators;

	}

	/**
	 * Helper method to retrieve Operator options
	 *
	 * @since   2.2.2
	 *
	 * @return  array   Operator Options
	 */
	public function get_operator_options() {

		// Get operator options.
		$operators = array(
			'AND' => __( 'All', 'page-generator-pro' ),
			'OR'  => __( 'Any', 'page-generator-pro' ),
		);

		/**
		 * Define MySQL compliant operator options
		 *
		 * @since   2.2.2
		 *
		 * @param   array   $order  Order options.
		 */
		$operators = apply_filters( 'page_generator_pro_common_get_operator_options', $operators );

		// Return filtered results.
		return $operators;

	}

	/**
	 * Returns configuration for autocomplete instances across tribute.js, TinyMCE and Gutenberg
	 * for keyword autocomplete functionality.
	 *
	 * @since   3.2.2
	 *
	 * @param   bool $is_group           If true, autocomplete fields are for a Content or Term Group.
	 *                                   If false, autocomplete fields are for Related Links shortcode on a Page or Post.
	 * @return  bool|array
	 */
	public function get_autocomplete_configuration( $is_group ) {

		// Get values, casting to an autocomplete compatible array as necessary.
		$values = $this->base->get_class( 'keywords' )->get_keywords_and_columns( true );

		// If no Keywords exist, don't initialize autocompleters.
		if ( ! $values ) {
			return false;
		}

		foreach ( $values as $index => $value ) {
			$values[ $index ] = array(
				'key'   => $value,
				'value' => $value,
			);
		}

		// Define autocomplete configuration.
		$autocomplete_configuration = array(
			array(
				'fields'   => $this->get_autocomplete_enabled_fields( $is_group ),
				'triggers' => array(
					array(
						'name'                    => 'keywords',
						'trigger'                 => '{',
						'values'                  => $values,
						'allowSpaces'             => false,
						'menuItemLimit'           => 20,

						// TinyMCE specific.
						'triggerKeyCode'          => 219, // Left square/curly bracket.
						'triggerKeyShiftRequired' => true, // Require shift key to also be pressed.
						'tinyMCEName'             => 'page_generator_pro_autocomplete_keywords',
					),
				),
			),
		);

		/**
		 * Define autocompleters to use across Content Groups, Term Group and TinyMCE
		 *
		 * @since   3.2.2
		 *
		 * @param   array   $autocomplete_configuration     Autocomplete Configuration.
		 * @param   bool    $is_group   If true, autocomplete fields are for a Content or Term Group.
		 *                              If false, autocomplete fields are for Related Links shortcode on a Page or Post.
		 */
		$autocomplete_configuration = apply_filters( 'page_generator_pro_common_get_autocomplete_configuration', $autocomplete_configuration, $is_group );

		// Return filtered results.
		return $autocomplete_configuration;

	}

	/**
	 * Returns an array of Javascript DOM selectors to enable the keyword
	 * autocomplete functionality on.
	 *
	 * @since   2.0.2
	 *
	 * @param   bool $is_group   If true, autocomplete fields are for a Content or Term Group.
	 *                           If false, autocomplete fields are for Related Links shortcode on a Page or Post.
	 * @return  array   Javascript DOM Selectors
	 */
	public function get_autocomplete_enabled_fields( $is_group = true ) {

		// Get fields.
		if ( $is_group ) {
			// Register autocomplete selectors across Group fields.
			$fields = array(
				// Classic Editor.
				'input[type=text]:not(#term-selectized)', // type=text prevents autocomplete greedily running on selectize inputs.
				'textarea',
				'div[contenteditable=true]',

				// Gutenberg.
				'h1[contenteditable=true]',

				// TinyMCE Plugins.
				'.wpzinc-autocomplete',
			);
		} else {
			// Register autocomplete selectors for Plugin-specific fields only
			// i.e. Related Links Shortcode.
			$fields = array(
				// Gutenberg
				// Is now handled using Dashboard Submodule's WPZincAutocompleterControl in autocomplete-gutenberg.js.

				// TinyMCE Plugins.
				'.wpzinc-autocomplete',
			);
		}

		/**
		 * Defines an array of Javascript DOM selectors to enable the keyword
		 * autocomplete functionality on.
		 *
		 * @since   2.0.2
		 *
		 * @param   array   $fields     Supported Fields.
		 * @param   bool    $is_group   If true, autocomplete fields are for a Content or Term Group.
		 *                              If false, autocomplete fields are for Related Links shortcode on a Page or Post.
		 */
		$fields = apply_filters( 'page_generator_pro_common_get_autocomplete_enabled_fields', $fields, $is_group );

		// Return filtered results.
		return $fields;

	}

	/**
	 * Returns an array of Javascript DOM selectors to enable the
	 * selectize functionality on.
	 *
	 * @since   2.5.4
	 *
	 * @return  array   Javascript DOM Selectors
	 */
	public function get_selectize_enabled_fields() {

		// Get fields.
		$fields = array(
			'freeform'  => array(
				'input.wpzinc-selectize-freeform',
				'.wpzinc-selectize-freeform input',
			),

			'drag_drop' => array(
				'select.wpzinc-selectize-drag-drop',
				'.wpzinc-selectize-drag-drop select',
			),

			'search'    => array(
				'select.wpzinc-selectize-search',
				'.wpzinc-selectize-search select',
			),

			'api'       => array(
				'select.wpzinc-selectize-api',
				'.wpzinc-selectize-api select',
			),

			'standard'  => array(
				'select.wpzinc-selectize',
				'.wpzinc-selectize select',
			),
		);

		/**
		 * Defines an array of Javascript DOM selectors to enable the
		 * selectize functionality on.
		 *
		 * @since   2.5.4
		 *
		 * @param   array   $fields  Supported Fields
		 */
		$fields = apply_filters( 'page_generator_pro_common_get_selectize_enabled_fields', $fields );

		// Return filtered results.
		return $fields;

	}

	/**
	 * Returns an array of events to reinitialize selectize instances
	 * on within Appearance > Customize
	 *
	 * @since   2.7.7
	 *
	 * @return  array   Events and Selectors
	 */
	public function get_selectize_reinit_events() {

		return array(
			'click'  => array(
				'li.accordion-section h3.accordion-section-title', // Top level Panels.
			),
			'change' => array(
				'input[name="_customize-radio-show_on_front"]', // Homepage Settings > Your homepage displays.
			),
		);

	}

	/**
	 * Helper method to retrieve available <a> target options
	 *
	 * @since   2.5.8
	 *
	 * @return  array   Link Target Options
	 */
	public function get_link_target_options() {

		// Get link target options.
		$targets = array(
			'_blank'  => __( 'New Window / Tab (_blank)', 'page-generator-pro' ),
			'_self'   => __( 'Same Window / Tab (_self)', 'page-generator-pro' ),
			'_parent' => __( 'Parent Frame (_parent)', 'page-generator-pro' ),
			'_top'    => __( 'Full Body of Window (_top)', 'page-generator-pro' ),
		);

		/**
		 * Defines link target options.
		 *
		 * @since   2.5.8
		 *
		 * @param   array   $targets   Link Target Options.
		 */
		$targets = apply_filters( 'page_generator_pro_common_get_link_target_options', $targets );

		// Return filtered results.
		return $targets;

	}

	/**
	 * Helper method to retrieve available WordPress Image Size options
	 *
	 * @since   1.7.9
	 *
	 * @return  array   Image Size Options
	 */
	public function get_media_library_image_size_options() {

		// Get registered image sizes from WordPress.
		$image_sizes = array();
		foreach ( get_intermediate_image_sizes() as $image_size ) {
			$image_sizes[ $image_size ] = $image_size;
		}

		/**
		 * Defines available registered image sizes in WordPress
		 *
		 * @since   1.7.9
		 *
		 * @param   array   $image_sizes  Image Sizes.
		 */
		$image_sizes = apply_filters( 'page_generator_pro_common_get_media_library_image_size_options', $image_sizes );

		// Return filtered results.
		return $image_sizes;

	}

	/**
	 * Helper method to return an array of WordPress Role Capabilities that should be disabled
	 * when a Content Group is Generating Content
	 *
	 * @since   1.9.9
	 *
	 * @return  array   Capabilities
	 */
	public function get_capabilities_to_disable_on_group_content_generation() {

		// Get capabilities.
		$capabilities = array(
			'delete_post',
			'edit_post',
		);

		/**
		 * Defines Role Capabilities that should be disabled when a Content Group is Generating Content.
		 *
		 * @since   1.9.9
		 *
		 * @param   array   $capabilities   Capabilities.
		 */
		$capabilities = apply_filters( 'page_generator_pro_common_get_capabilities_to_disable_on_group_content_generation', $capabilities );

		// Return filtered results.
		return $capabilities;

	}

	/**
	 * Helper method to return an array of WordPress Role Capabilities that should be disabled
	 * when a Term Group is Generating Terms
	 *
	 * @since   1.9.9
	 *
	 * @return  array   Capabilities
	 */
	public function get_capabilities_to_disable_on_group_term_generation() {

		// Get capabilities.
		$capabilities = array(
			'delete_term',
			'edit_term',
		);

		/**
		 * Defines Role Capabilities that should be disabled when a Term Group is Generating Terms.
		 *
		 * @since   1.9.9
		 *
		 * @param   array   $capabilities   Capabilities.
		 */
		$capabilities = apply_filters( 'page_generator_pro_common_get_capabilities_to_disable_on_group_term_generation', $capabilities );

		// Return filtered results.
		return $capabilities;

	}

	/**
	 * Returns get_plugins() that can be selected to be loaded when
	 * the Performance Addon is enabled, excluding required plugins
	 * that must always be loaded
	 *
	 * @since   2.9.2
	 *
	 * @return  array   Plugins
	 */
	public function get_use_mu_plugin_required_plugins() {

		// Plugins that shouldn't be included, as they
		// are always loaded.
		return array(
			// ACF is required for Overwrite Sections setting to be honored.
			'advanced-custom-fields-pro/acf.php',
			'advanced-custom-fields/acf.php',

			// Category Tag Pages, so Taxonomies are registered.
			'category-tag-pages/category-tag-pages.php',

			// Page Builders.
			'cornerstone/cornerstone.php',
			'elementor/elementor.php',
			'elementor-pro/elementor-pro.php',
			'divi-builder/divi-builder.php',

			// FIFU, so fake Attachments are created for Featured Images to work.
			'featured-image-from-url/featured-image-from-url.php',

			// Metabox.io, so Metabox.io Field Groups can be used to overwrite Content.
			'meta-box/meta-box.php',
			'meta-box-aio/meta-box-aio.php',
			'meta-box-lite/meta-box-lite.php',

			// Our own Plugin, obviously.
			'page-generator-pro/page-generator-pro.php',

			// i18n.
			'polylang/polylang.php',
			'sitepress-multilingual-cms/sitepress.php',

			// Search Exclude.
			'search-exclude/search-exclude.php',
		);

	}

	/**
	 * Helper method to retrieve AI content types to generate.
	 *
	 * @since   4.6.8
	 *
	 * @return  array
	 */
	public function get_ai_content_types() {

		return array(
			'article'           => __( 'Article', 'page-generator-pro' ),
			'faq'               => __( 'FAQs', 'page-generator-pro' ),
			'freeform'          => __( 'Freeform Prompt', 'page-generator-pro' ),
			'paragraph'         => __( 'Paragraph', 'page-generator-pro' ),
			'review'            => __( 'Review', 'page-generator-pro' ),
			'review_plain_text' => __( 'Review (Plain Text, no schema)', 'page-generator-pro' ),
		);

	}

	/**
	 * Helper method to retrieve language codes and names
	 *
	 * @since   4.1.1
	 *
	 * @return  array   Languages
	 */
	public function get_languages() {

		return array(
			'en'    => 'English',
			'en-au' => 'English (Australia)',
			'en-bz' => 'English (Belize)',
			'en-ca' => 'English (Canada)',
			'en-ie' => 'English (Ireland)',
			'en-jm' => 'English (Jamaica)',
			'en-nz' => 'English (New Zealand)',
			'en-za' => 'English (South Africa)',
			'en-tt' => 'English (Trinidad)',
			'en-gb' => 'English (United Kingdom)',
			'en-us' => 'English (United States)',
			'af'    => 'Afrikaans',
			'sq'    => 'Albanian',
			'am'    => 'Amharic',
			'ar-dz' => 'Arabic (Algeria)',
			'ar-bh' => 'Arabic (Bahrain)',
			'ar-eg' => 'Arabic (Egypt)',
			'ar-iq' => 'Arabic (Iraq)',
			'ar-jo' => 'Arabic (Jordan)',
			'ar-kw' => 'Arabic (Kuwait)',
			'ar-lb' => 'Arabic (Lebanon)',
			'ar-ly' => 'Arabic (Libya)',
			'ar-ma' => 'Arabic (Morocco)',
			'ar-om' => 'Arabic (Oman)',
			'ar-qa' => 'Arabic (Qatar)',
			'ar-sa' => 'Arabic (Saudi Arabia)',
			'ar-sy' => 'Arabic (Syria)',
			'ar-tn' => 'Arabic (Tunisia)',
			'ar-ae' => 'Arabic (U.A.E.)',
			'ar-ye' => 'Arabic (Yemen)',
			'hy'    => 'Armenian',
			'az'    => 'Azerbaijani',
			'eu'    => 'Basque',
			'be'    => 'Belarusian',
			'bn'    => 'Bengali',
			'bs'    => 'Bosnian',
			'bg'    => 'Bulgarian',
			'ca'    => 'Catalan',
			'ceb'   => 'Cebuano',
			'ny'    => 'Chichewa',
			'zh'    => 'Chinese (Simplified)',
			'zh-hk' => 'Chinese (Hong Kong)',
			'zh-cn' => 'Chinese (PRC)',
			'zh-sg' => 'Chinese (Singapore)',
			'zh-tw' => 'Chinese (Traditional)',
			'co'    => 'Corsican',
			'hr'    => 'Croatian',
			'cs'    => 'Czech',
			'da'    => 'Danish',
			'nl-be' => 'Dutch (Belgium)',
			'nl'    => 'Dutch (Standard)',
			'eo'    => 'Esperanto',
			'et'    => 'Estonian',
			'fo'    => 'Faeroese',
			'fa'    => 'Persian',
			'tl'    => 'Filipino',
			'fi'    => 'Finnish',
			'fr-be' => 'French (Belgium)',
			'fr-ca' => 'French (Canada)',
			'fr-lu' => 'French (Luxembourg)',
			'fr'    => 'French (Standard)',
			'fr-ch' => 'French (Switzerland)',
			'fy'    => 'Frisian',
			'gd'    => 'Scots Gaelic',
			'gl'    => 'Galician',
			'ka'    => 'Georgian',
			'de-at' => 'German (Austria)',
			'de-li' => 'German (Liechtenstein)',
			'de-lu' => 'German (Luxembourg)',
			'de'    => 'German (Standard)',
			'de-ch' => 'German (Switzerland)',
			'el'    => 'Greek',
			'gu'    => 'Gujarati',
			'ht'    => 'Haitian Creole',
			'ha'    => 'Hausa',
			'haw'   => 'Hawaiian',
			'iw'    => 'Hebrew',
			'hi'    => 'Hindi',
			'hmn'   => 'Hmong',
			'hu'    => 'Hungarian',
			'is'    => 'Icelandic',
			'ig'    => 'Igbo',
			'id'    => 'Indonesian',
			'ga'    => 'Irish',
			'it'    => 'Italian (Standard)',
			'it-ch' => 'Italian (Switzerland)',
			'ja'    => 'Japanese',
			'jw'    => 'Javanese',
			'kn'    => 'Kannada',
			'kk'    => 'Kazakh',
			'km'    => 'Khmer',
			'rw'    => 'Kinyarwanda',
			'ko'    => 'Korean',
			'ku'    => 'Kurdish (Kurmanji)',
			'ky'    => 'Kyrgyz',
			'lo'    => 'Lao',
			'la'    => 'Latin',
			'lv'    => 'Latvian',
			'lt'    => 'Lithuanian',
			'lb'    => 'Luxembourgish',
			'mk'    => 'Macedonian',
			'mg'    => 'Malagasy',
			'ms'    => 'Malay',
			'ml'    => 'Malayalam',
			'mt'    => 'Maltese',
			'mi'    => 'Maori',
			'mr'    => 'Marathi',
			'mn'    => 'Mongolian',
			'my'    => 'Myanmar (Burmese)',
			'ne'    => 'Nepali',
			'no'    => 'Norwegian',
			'nb'    => 'Norwegian (Bokmål)',
			'nn'    => 'Norwegian (Nynorsk)',
			'or'    => 'Odia (Oriya)',
			'ps'    => 'Pashto',
			'pl'    => 'Polish',
			'pt-br' => 'Portuguese (Brazil)',
			'pt'    => 'Portuguese (Portugal)',
			'pa'    => 'Punjabi',
			'rm'    => 'Rhaeto-Romanic',
			'ro'    => 'Romanian',
			'ro-md' => 'Romanian (Republic of Moldova)',
			'ru'    => 'Russian',
			'ru-md' => 'Russian (Republic of Moldova)',
			'sm'    => 'Samoan',
			'sr'    => 'Serbian',
			'st'    => 'Sesotho',
			'sn'    => 'Shona',
			'sd'    => 'Sindhi',
			'si'    => 'Sinhala',
			'sk'    => 'Slovak',
			'sl'    => 'Slovenian',
			'sb'    => 'Sorbian',
			'so'    => 'Somali',
			'es-ar' => 'Spanish (Argentina)',
			'es-bo' => 'Spanish (Bolivia)',
			'es-cl' => 'Spanish (Chile)',
			'es-co' => 'Spanish (Colombia)',
			'es-cr' => 'Spanish (Costa Rica)',
			'es-do' => 'Spanish (Dominican Republic)',
			'es-ec' => 'Spanish (Ecuador)',
			'es-sv' => 'Spanish (El Salvador)',
			'es-gt' => 'Spanish (Guatemala)',
			'es-hn' => 'Spanish (Honduras)',
			'es-mx' => 'Spanish (Mexico)',
			'es-ni' => 'Spanish (Nicaragua)',
			'es-pa' => 'Spanish (Panama)',
			'es-py' => 'Spanish (Paraguay)',
			'es-pe' => 'Spanish (Peru)',
			'es-pr' => 'Spanish (Puerto Rico)',
			'es'    => 'Spanish (Spain)',
			'es-uy' => 'Spanish (Uruguay)',
			'es-ve' => 'Spanish (Venezuela)',
			'su'    => 'Sundanese',
			'sw'    => 'Swahili',
			'sv'    => 'Swedish',
			'sv-fi' => 'Swedish (Finland)',
			'tg'    => 'Tajik',
			'ta'    => 'Tamil',
			'tt'    => 'Tatar',
			'te'    => 'Telugu',
			'th'    => 'Thai',
			'ts'    => 'Tsonga',
			'tn'    => 'Tswana',
			'tr'    => 'Turkish',
			'tk'    => 'Turkmen',
			'uk'    => 'Ukrainian',
			'ur'    => 'Urdu',
			'ug'    => 'Uyghur',
			'uz'    => 'Uzbek',
			've'    => 'Venda',
			'vi'    => 'Vietnamese',
			'cy'    => 'Welsh',
			'xh'    => 'Xhosa',
			'ji'    => 'Yiddish',
			'yo'    => 'Yoruba',
			'zu'    => 'Zulu',
		);

	}

	/**
	 * Helper method to retrieve country codes and names
	 *
	 * @since   1.1.7
	 *
	 * @return  array   Countries
	 */
	public function get_countries() {

		// Get countries.
		$countries = array(
			'AF' => 'Afghanistan',
			'AX' => 'Aland Islands',
			'AL' => 'Albania',
			'DZ' => 'Algeria',
			'AS' => 'American Samoa',
			'AD' => 'Andorra',
			'AO' => 'Angola',
			'AI' => 'Anguilla',
			'AQ' => 'Antarctica',
			'AG' => 'Antigua And Barbuda',
			'AR' => 'Argentina',
			'AM' => 'Armenia',
			'AW' => 'Aruba',
			'AU' => 'Australia',
			'AT' => 'Austria',
			'AZ' => 'Azerbaijan',
			'BS' => 'Bahamas',
			'BH' => 'Bahrain',
			'BD' => 'Bangladesh',
			'BB' => 'Barbados',
			'BY' => 'Belarus',
			'BE' => 'Belgium',
			'BZ' => 'Belize',
			'BJ' => 'Benin',
			'BM' => 'Bermuda',
			'BT' => 'Bhutan',
			'BO' => 'Bolivia',
			'BA' => 'Bosnia And Herzegovina',
			'BW' => 'Botswana',
			'BV' => 'Bouvet Island',
			'BR' => 'Brazil',
			'IO' => 'British Indian Ocean Territory',
			'BN' => 'Brunei Darussalam',
			'BG' => 'Bulgaria',
			'BF' => 'Burkina Faso',
			'BI' => 'Burundi',
			'KH' => 'Cambodia',
			'CM' => 'Cameroon',
			'CA' => 'Canada',
			'CV' => 'Cape Verde',
			'KY' => 'Cayman Islands',
			'CF' => 'Central African Republic',
			'TD' => 'Chad',
			'CL' => 'Chile',
			'CN' => 'China',
			'CX' => 'Christmas Island',
			'CC' => 'Cocos (Keeling) Islands',
			'CO' => 'Colombia',
			'KM' => 'Comoros',
			'CG' => 'Congo',
			'CD' => 'Congo, Democratic Republic',
			'CK' => 'Cook Islands',
			'CR' => 'Costa Rica',
			'CI' => 'Cote D\'Ivoire',
			'HR' => 'Croatia',
			'CU' => 'Cuba',
			'CY' => 'Cyprus',
			'CZ' => 'Czech Republic',
			'DK' => 'Denmark',
			'DJ' => 'Djibouti',
			'DM' => 'Dominica',
			'DO' => 'Dominican Republic',
			'EC' => 'Ecuador',
			'EG' => 'Egypt',
			'SV' => 'El Salvador',
			'GQ' => 'Equatorial Guinea',
			'ER' => 'Eritrea',
			'EE' => 'Estonia',
			'ET' => 'Ethiopia',
			'FK' => 'Falkland Islands (Malvinas)',
			'FO' => 'Faroe Islands',
			'FJ' => 'Fiji',
			'FI' => 'Finland',
			'FR' => 'France',
			'GF' => 'French Guiana',
			'PF' => 'French Polynesia',
			'TF' => 'French Southern Territories',
			'GA' => 'Gabon',
			'GM' => 'Gambia',
			'GE' => 'Georgia',
			'DE' => 'Germany',
			'GH' => 'Ghana',
			'GI' => 'Gibraltar',
			'GR' => 'Greece',
			'GL' => 'Greenland',
			'GD' => 'Grenada',
			'GP' => 'Guadeloupe',
			'GU' => 'Guam',
			'GT' => 'Guatemala',
			'GG' => 'Guernsey',
			'GN' => 'Guinea',
			'GW' => 'Guinea-Bissau',
			'GY' => 'Guyana',
			'HT' => 'Haiti',
			'HM' => 'Heard Island & Mcdonald Islands',
			'VA' => 'Holy See (Vatican City State)',
			'HN' => 'Honduras',
			'HK' => 'Hong Kong',
			'HU' => 'Hungary',
			'IS' => 'Iceland',
			'IN' => 'India',
			'ID' => 'Indonesia',
			'IR' => 'Iran, Islamic Republic Of',
			'IQ' => 'Iraq',
			'IE' => 'Ireland',
			'IM' => 'Isle Of Man',
			'IL' => 'Israel',
			'IT' => 'Italy',
			'JM' => 'Jamaica',
			'JP' => 'Japan',
			'JE' => 'Jersey',
			'JO' => 'Jordan',
			'KZ' => 'Kazakhstan',
			'KE' => 'Kenya',
			'KI' => 'Kiribati',
			'KR' => 'Korea',
			'KW' => 'Kuwait',
			'KG' => 'Kyrgyzstan',
			'LA' => 'Lao People\'s Democratic Republic',
			'LV' => 'Latvia',
			'LB' => 'Lebanon',
			'LS' => 'Lesotho',
			'LR' => 'Liberia',
			'LY' => 'Libyan Arab Jamahiriya',
			'LI' => 'Liechtenstein',
			'LT' => 'Lithuania',
			'LU' => 'Luxembourg',
			'MO' => 'Macao',
			'MK' => 'Macedonia',
			'MG' => 'Madagascar',
			'MW' => 'Malawi',
			'MY' => 'Malaysia',
			'MV' => 'Maldives',
			'ML' => 'Mali',
			'MT' => 'Malta',
			'MH' => 'Marshall Islands',
			'MQ' => 'Martinique',
			'MR' => 'Mauritania',
			'MU' => 'Mauritius',
			'YT' => 'Mayotte',
			'MX' => 'Mexico',
			'FM' => 'Micronesia, Federated States Of',
			'MD' => 'Moldova',
			'MC' => 'Monaco',
			'MN' => 'Mongolia',
			'ME' => 'Montenegro',
			'MS' => 'Montserrat',
			'MA' => 'Morocco',
			'MZ' => 'Mozambique',
			'MM' => 'Myanmar',
			'NA' => 'Namibia',
			'NR' => 'Nauru',
			'NP' => 'Nepal',
			'NL' => 'Netherlands',
			'AN' => 'Netherlands Antilles',
			'NC' => 'New Caledonia',
			'NZ' => 'New Zealand',
			'NI' => 'Nicaragua',
			'NE' => 'Niger',
			'NG' => 'Nigeria',
			'NU' => 'Niue',
			'NF' => 'Norfolk Island',
			'MP' => 'Northern Mariana Islands',
			'NO' => 'Norway',
			'OM' => 'Oman',
			'PK' => 'Pakistan',
			'PW' => 'Palau',
			'PS' => 'Palestinian Territory, Occupied',
			'PA' => 'Panama',
			'PG' => 'Papua New Guinea',
			'PY' => 'Paraguay',
			'PE' => 'Peru',
			'PH' => 'Philippines',
			'PN' => 'Pitcairn',
			'PL' => 'Poland',
			'PT' => 'Portugal',
			'PR' => 'Puerto Rico',
			'QA' => 'Qatar',
			'RE' => 'Reunion',
			'RO' => 'Romania',
			'RU' => 'Russian Federation',
			'RW' => 'Rwanda',
			'BL' => 'Saint Barthelemy',
			'SH' => 'Saint Helena',
			'KN' => 'Saint Kitts And Nevis',
			'LC' => 'Saint Lucia',
			'MF' => 'Saint Martin',
			'PM' => 'Saint Pierre And Miquelon',
			'VC' => 'Saint Vincent And Grenadines',
			'WS' => 'Samoa',
			'SM' => 'San Marino',
			'ST' => 'Sao Tome And Principe',
			'SA' => 'Saudi Arabia',
			'SN' => 'Senegal',
			'RS' => 'Serbia',
			'SC' => 'Seychelles',
			'SL' => 'Sierra Leone',
			'SG' => 'Singapore',
			'SK' => 'Slovakia',
			'SI' => 'Slovenia',
			'SB' => 'Solomon Islands',
			'SO' => 'Somalia',
			'ZA' => 'South Africa',
			'GS' => 'South Georgia And Sandwich Isl.',
			'ES' => 'Spain',
			'LK' => 'Sri Lanka',
			'SD' => 'Sudan',
			'SR' => 'Suriname',
			'SJ' => 'Svalbard And Jan Mayen',
			'SZ' => 'Swaziland',
			'SE' => 'Sweden',
			'CH' => 'Switzerland',
			'SY' => 'Syrian Arab Republic',
			'TW' => 'Taiwan',
			'TJ' => 'Tajikistan',
			'TZ' => 'Tanzania',
			'TH' => 'Thailand',
			'TL' => 'Timor-Leste',
			'TG' => 'Togo',
			'TK' => 'Tokelau',
			'TO' => 'Tonga',
			'TT' => 'Trinidad And Tobago',
			'TN' => 'Tunisia',
			'TR' => 'Turkey',
			'TM' => 'Turkmenistan',
			'TC' => 'Turks And Caicos Islands',
			'TV' => 'Tuvalu',
			'UG' => 'Uganda',
			'UA' => 'Ukraine',
			'AE' => 'United Arab Emirates',
			'GB' => 'United Kingdom',
			'US' => 'United States',
			'UM' => 'United States Outlying Islands',
			'UY' => 'Uruguay',
			'UZ' => 'Uzbekistan',
			'VU' => 'Vanuatu',
			'VE' => 'Venezuela',
			'VN' => 'Viet Nam',
			'VG' => 'Virgin Islands, British',
			'VI' => 'Virgin Islands, U.S.',
			'WF' => 'Wallis And Futuna',
			'EH' => 'Western Sahara',
			'YE' => 'Yemen',
			'ZM' => 'Zambia',
			'ZW' => 'Zimbabwe',
		);

		/**
		 * Defines available GeoRocket API country codes and names.
		 *
		 * @since   1.1.7
		 *
		 * @param   array   $countries  Countries.
		 */
		$countries = apply_filters( 'page_generator_pro_common_get_countries', $countries );

		// Return filtered results.
		return $countries;

	}

	/**
	 * Helper method to retrieve output types for Generate Locations
	 *
	 * @since   1.7.8
	 *
	 * @return  array
	 */
	public function get_locations_methods() {

		// Define Output Types.
		$methods = array(
			'radius' => __( 'Radius', 'page-generator-pro' ),
			'area'   => __( 'Area', 'page-generator-pro' ),
		);

		/**
		 * Defines available output types for Generate Locations.
		 *
		 * @since   1.7.8
		 *
		 * @param   array   $methods    Methods
		 */
		$methods = apply_filters( 'page_generator_pro_common_get_locations_restrictions', $methods );

		// Return filtered results.
		return $methods;

	}

	/**
	 * Helper method to retrieve location restrictions for Generate Locations
	 *
	 * @since   1.7.8
	 *
	 * @return  array
	 */
	public function get_locations_restrictions() {

		// Define Restrictions.
		$restrictions = array(
			'radius' => __( 'Radius', 'page-generator-pro' ),
			'city'   => __( 'City', 'page-generator-pro' ),
			'county' => __( 'County', 'page-generator-pro' ),
			'region' => __( 'Region', 'page-generator-pro' ),
		);

		/**
		 * Defines available location restrictions for Generate Locations
		 *
		 * @since   1.7.8
		 *
		 * @param   array   $restrictions   Output Types.
		 */
		$restrictions = apply_filters( 'page_generator_pro_common_get_locations_restrictions', $restrictions );

		// Return filtered results.
		return $restrictions;

	}

	/**
	 * Helper method to retrieve output types for Generate Locations
	 *
	 * @since   1.5.0
	 *
	 * @param   string $provider   Provider (georocket,openai,gemini etc).
	 * @return  array
	 */
	public function get_locations_output_types( $provider = 'georocket' ) {

		// Define Output Types.
		$output_types = array_merge(
			$this->get_locations_output_types_street_names( $provider ),
			$this->get_locations_output_types_zipcodes( $provider ),
			$this->get_locations_output_types_zipcode_districts( $provider ),
			$this->get_locations_output_types_cities( $provider ),
			$this->get_locations_output_types_counties( $provider ),
			$this->get_locations_output_types_regions( $provider )
		);

		/**
		 * Defines available output types for Generate Locations.
		 *
		 * @since   5.0.1
		 *
		 * @param   array   $output_types   Output Types.
		 */
		$output_types = apply_filters( 'page_generator_pro_common_get_locations_output_types_' . $provider, $output_types );

		/**
		 * Defines available output types for Generate Locations.
		 *
		 * @since   1.5.0
		 *
		 * @param   string  $provider       Provider.
		 * @param   array   $output_types   Output Types.
		 */
		$output_types = apply_filters( 'page_generator_pro_common_get_locations_output_types', $output_types, $provider );

		// Return filtered results.
		return $output_types;

	}

	/**
	 * Helper method to retrieve Street Names output types for Generate Locations
	 *
	 * @since   2.4.5
	 *
	 * @param   string $provider   Provider (georocket,openai,gemini etc).
	 * @return  array
	 */
	public function get_locations_output_types_street_names( $provider = 'georocket' ) {

		// Define Output Types.
		$output_types = array(
			'street_name' => __( 'Street Name', 'page-generator-pro' ),
		);

		/**
		 * Defines available Street Names output types for Generate Locations.
		 *
		 * @since   5.0.1
		 *
		 * @param   array   $output_types   Output Types.
		 */
		$output_types = apply_filters( 'page_generator_pro_common_get_locations_output_types_street_names_' . $provider, $output_types );

		/**
		 * Defines available Street Names output types for Generate Locations.
		 *
		 * @since   2.4.5
		 *
		 * @param   array   $output_types   Output Types.
		 */
		$output_types = apply_filters( 'page_generator_pro_common_get_locations_output_types_street_names', $output_types );

		// Return filtered results.
		return $output_types;

	}

	/**
	 * Helper method to retrieve ZIP Codes output types for Generate Locations
	 *
	 * @since   2.4.5
	 *
	 * @param   string $provider   Provider (georocket,openai,gemini etc).
	 * @return  array
	 */
	public function get_locations_output_types_zipcodes( $provider = 'georocket' ) {

		// Define Output Types.
		$output_types = array(
			'zipcode'           => __( 'ZIP Code', 'page-generator-pro' ),
			'zipcode_latitude'  => __( 'ZIP Code: Latitude', 'page-generator-pro' ),
			'zipcode_longitude' => __( 'ZIP Code: Longitude', 'page-generator-pro' ),
		);

		/**
		 * Defines available ZIP Code output types for Generate Locations.
		 *
		 * @since   5.0.1
		 *
		 * @param   array   $output_types   Output Types.
		 */
		$output_types = apply_filters( 'page_generator_pro_common_get_locations_output_types_zipcodes_' . $provider, $output_types );

		/**
		 * Defines available ZIP Code output types for Generate Locations.
		 *
		 * @since   2.4.5
		 *
		 * @param   array   $output_types   Output Types.
		 */
		$output_types = apply_filters( 'page_generator_pro_common_get_locations_output_types_zipcodes', $output_types );

		// Return filtered results.
		return $output_types;

	}

	/**
	 * Helper method to retrieve Zip Code Districts output types for Generate Locations
	 *
	 * @since   2.4.5
	 *
	 * @param   string $provider   Provider (georocket,openai,gemini etc).
	 * @return  array
	 */
	public function get_locations_output_types_zipcode_districts( $provider = 'georocket' ) {

		// Define Output Types.
		$output_types = array(
			'zipcode_district' => __( 'ZIP Code District', 'page-generator-pro' ),
		);

		/**
		 * Defines available Zip Code Districts output types for Generate Locations.
		 *
		 * @since   5.0.1
		 *
		 * @param   array   $output_types   Output Types.
		 */
		$output_types = apply_filters( 'page_generator_pro_common_get_locations_output_types_zipcode_districts_' . $provider, $output_types );

		/**
		 * Defines available Zip Code Districts output types for Generate Locations.
		 *
		 * @since   2.4.5
		 *
		 * @param   array   $output_types   Output Types.
		 */
		$output_types = apply_filters( 'page_generator_pro_common_get_locations_output_types_zipcode_districts', $output_types );

		// Return filtered results.
		return $output_types;

	}

	/**
	 * Helper method to retrieve Cities output types for Generate Locations
	 *
	 * @since   2.4.5
	 *
	 * @param   string $provider   Provider (georocket,openai,gemini etc).
	 * @return  array
	 */
	public function get_locations_output_types_cities( $provider = 'georocket' ) {

		// Define Output Types.
		$output_types = array(
			'city_name'                        => __( 'City', 'page-generator-pro' ),
			'city_latitude'                    => __( 'City: Latitude', 'page-generator-pro' ),
			'city_longitude'                   => __( 'City: Longitude', 'page-generator-pro' ),
			'city_population'                  => __( 'City: Population', 'page-generator-pro' ),
			'city_population_male'             => __( 'City: Population: Male', 'page-generator-pro' ),
			'city_population_male_percent'     => __( 'City: Population: Male %', 'page-generator-pro' ),
			'city_population_female'           => __( 'City: Population: Female', 'page-generator-pro' ),
			'city_population_female_percent'   => __( 'City: Population: Female %', 'page-generator-pro' ),
			'city_population_children'         => __( 'City: Population: Children', 'page-generator-pro' ),
			'city_population_children_percent' => __( 'City: Population: Children %', 'page-generator-pro' ),
			'city_population_adults'           => __( 'City: Population: Adults', 'page-generator-pro' ),
			'city_population_adults_percent'   => __( 'City: Population: Adults %', 'page-generator-pro' ),
			'city_population_elderly'          => __( 'City: Population: Elderly', 'page-generator-pro' ),
			'city_population_elderly_percent'  => __( 'City: Population: Elderly %', 'page-generator-pro' ),
			'city_median_household_income'     => __( 'City: Median Household Income', 'page-generator-pro' ),
			'city_wikipedia_url'               => __( 'City: Wikipedia URL', 'page-generator-pro' ),
		);

		/**
		 * Defines available Cities output types for Generate Locations.
		 *
		 * @since   5.0.1
		 *
		 * @param   array   $output_types   Output Types.
		 */
		$output_types = apply_filters( 'page_generator_pro_common_get_locations_output_types_cities_' . $provider, $output_types );

		/**
		 * Defines available Cities output types for Generate Locations.
		 *
		 * @since   2.4.5
		 *
		 * @param   array   $output_types   Output Types.
		 */
		$output_types = apply_filters( 'page_generator_pro_common_get_locations_output_types_cities', $output_types );

		// Return filtered results.
		return $output_types;

	}

	/**
	 * Helper method to retrieve Counties output types for Generate Locations
	 *
	 * @since   2.4.5
	 *
	 * @param   string $provider   Provider (georocket,openai,gemini etc).
	 * @return  array
	 */
	public function get_locations_output_types_counties( $provider = 'georocket' ) {

		// Define Output Types.
		$output_types = array(
			'county_name'          => __( 'County', 'page-generator-pro' ),
			'county_name_local'    => __( 'County (Local Language)', 'page-generator-pro' ),
			'county_code'          => __( 'County: Code', 'page-generator-pro' ),
			'county_wikipedia_url' => __( 'County: Wikipedia URL', 'page-generator-pro' ),
		);

		/**
		 * Defines available Counties output types for Generate Locations.
		 *
		 * @since   5.0.1
		 *
		 * @param   array   $output_types   Output Types.
		 */
		$output_types = apply_filters( 'page_generator_pro_common_get_locations_output_types_counties_' . $provider, $output_types );

		/**
		 * Defines available Counties output types for Generate Locations.
		 *
		 * @since   2.4.5
		 *
		 * @param   array   $output_types   Output Types.
		 */
		$output_types = apply_filters( 'page_generator_pro_common_get_locations_output_types_counties', $output_types );

		// Return filtered results.
		return $output_types;

	}

	/**
	 * Helper method to retrieve Regions output types for Generate Locations
	 *
	 * @since   2.4.5
	 *
	 * @param   string $provider   Provider (georocket,openai,gemini etc).
	 * @return  array
	 */
	public function get_locations_output_types_regions( $provider = 'georocket' ) {

		// Define Output Types.
		$output_types = array(
			'region_name'          => __( 'Region', 'page-generator-pro' ),
			'region_name_local'    => __( 'Region (Local Language)', 'page-generator-pro' ),
			'region_code'          => __( 'Region: Code', 'page-generator-pro' ),
			'region_wikipedia_url' => __( 'Region: Wikipedia URL', 'page-generator-pro' ),
		);

		/**
		 * Defines available Regions output types for Generate Locations.
		 *
		 * @since   5.0.1
		 *
		 * @param   array   $output_types   Output Types.
		 */
		$output_types = apply_filters( 'page_generator_pro_common_get_locations_output_types_regions_' . $provider, $output_types );

		/**
		 * Defines available Regions output types for Generate Locations.
		 *
		 * @since   2.4.5
		 *
		 * @param   array   $output_types   Output Types.
		 */
		$output_types = apply_filters( 'page_generator_pro_common_get_locations_output_types_regions', $output_types );

		// Return filtered results.
		return $output_types;

	}

	/**
	 * Helper method to retrieve output types for Generate Phone Area Codes.
	 *
	 * @since   1.5.9
	 *
	 * @param   string $provider   Provider (georocket,openai,gemini etc).
	 * @return  array
	 */
	public function get_phone_area_code_output_types( $provider = 'georocket' ) {

		// Define Output Types.
		$output_types = array(
			'city'         => __( 'City', 'page-generator-pro' ),
			'area_code'    => __( 'Phone Area Code', 'page-generator-pro' ),
			'country_code' => __( 'Phone Country Code', 'page-generator-pro' ),
		);

		/**
		 * Defines available output types for Generate Phone Area Codes.
		 *
		 * @since   5.0.1
		 *
		 * @param   array   $output_types   Output Types.
		 */
		$output_types = apply_filters( 'page_generator_pro_common_get_phone_area_output_types_' . $provider, $output_types );

		/**
		 * Defines available output types for Generate Phone Area Codes.
		 *
		 * @since   1.5.9
		 *
		 * @param   array   $output_types   Output Types.
		 */
		$output_types = apply_filters( 'page_generator_pro_common_get_phone_area_output_types', $output_types );

		// Return filtered results.
		return $output_types;

	}

	/**
	 * Sanitizes the given string with spaces and accented characters to a slug, retaining forwardslashes.
	 *
	 * If a forwardslash has a space before and after, it is replaced by a single space, as this might be a Keyword term
	 * e.g. "Metro / Second and Hume" --> sanitize_title( "Metro Second and Hume" ) --> "metro-second-and-hume".
	 *
	 * Special accented characters are converted to non-accented versions.
	 *
	 * @since   2.2.6
	 *
	 * @param   string $slug   Slug to sanitize.
	 * @return  string          Sanitized Slug
	 */
	public function sanitize_slug( $slug ) {

		// Replace any forwardslashes with spaces immediately before and after it with a single space.
		$slug = str_replace( ' / ', ' ', $slug );

		// Split by forwardslash.
		$slug_parts = explode( '/', $slug );

		// Sanitize each part.
		foreach ( $slug_parts as $index => $slug ) {
			$slug_parts[ $index ] = sanitize_title( $slug );
		}

		// Convert array back to string.
		$slug = implode( '/', $slug_parts );

		// Return.
		return $slug;

	}

	/**
	 * Helper function to determine if the request is a REST API request.
	 *
	 * Used in some render() functions to determine if Gutenberg is making
	 * a request to render a block's output in the editor.
	 *
	 * @since   3.6.6
	 *
	 * @return  bool    Is REST API Request
	 */
	public function is_rest_api_request() {

		if ( ! defined( 'REST_REQUEST' ) ) {
			return false;
		}

		if ( ! REST_REQUEST ) {
			return false;
		}

		return true;

	}

	/**
	 * Fetch a file's contents the WordPress way, using WP_Filesystem.
	 *
	 * @since   3.7.8
	 *
	 * @param   string $file   Path and file.
	 * @return  bool|string    File contents
	 */
	public function file_get_contents( $file ) {

		// Allow us to easily interact with the filesystem.
		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
		global $wp_filesystem;

		// Get file contents.
		return $wp_filesystem->get_contents( $file );

	}

}
