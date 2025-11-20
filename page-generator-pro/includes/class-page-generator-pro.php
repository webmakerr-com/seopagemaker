<?php
/**
 * Page Generator Pro class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Main Page Generator Pro class, used to load the Plugin.
 *
 * @package   Page_Generator_Pro
 * @author    WP Zinc
 * @version   1.0.0
 */
class Page_Generator_Pro {

	/**
	 * Holds the class object.
	 *
	 * @since   1.1.3
	 *
	 * @var     object
	 */
	public static $instance;

	/**
	 * Holds the plugin information object.
	 *
	 * @since   1.0.0
	 *
	 * @var     object
	 */
	public $plugin;

	/**
	 * Holds the dashboard class object.
	 *
	 * @since   1.1.6
	 *
	 * @var     object
	 */
	public $dashboard;

	/**
	 * Holds the licensing class object.
	 *
	 * @since   1.1.6
	 *
	 * @var     object
	 */
	public $licensing;

	/**
	 * Classes
	 *
	 * @since   1.9.8
	 *
	 * @var     object
	 */
	public $classes;

	/**
	 * Constructor. Acts as a bootstrap to load the rest of the plugin
	 *
	 * @since   1.0.0
	 */
	public function __construct() {

		// Plugin Details.
		$this->plugin                    = new stdClass();
		$this->plugin->name              = 'page-generator-pro';
		$this->plugin->displayName       = 'Page Generator Pro';
		$this->plugin->description       = 'The most powerful WordPress content generator plugin on the market.';
		$this->plugin->author_name       = 'WP Zinc';
		$this->plugin->version           = PAGE_GENERATOR_PRO_PLUGIN_VERSION;
		$this->plugin->buildDate         = PAGE_GENERATOR_PRO_PLUGIN_BUILD_DATE;
		$this->plugin->php_requires      = '7.4';
		$this->plugin->folder            = PAGE_GENERATOR_PRO_PLUGIN_PATH;
		$this->plugin->url               = PAGE_GENERATOR_PRO_PLUGIN_URL;
		$this->plugin->documentation_url = 'https://www.wpzinc.com/documentation/page-generator-pro';
		$this->plugin->support_url       = 'https://www.wpzinc.com/support';
		$this->plugin->upgrade_url       = 'https://www.wpzinc.com/plugins/page-generator-pro';

		// Design.
		$this->plugin->logo                        = PAGE_GENERATOR_PRO_PLUGIN_URL . 'assets/images/icons/logo.svg';
		$this->plugin->header_background_color     = '#ffffff';
		$this->plugin->header_primary_text_color   = '#3d3d3d';
		$this->plugin->header_secondary_text_color = '#6e6e6e';

		// Review.
		$this->plugin->review_name = 'page-generator';

		// Run actions that need to be performed before WordPress' init hook.
		require_once $this->plugin->folder . 'includes/global/init.php';

		// Defer loading of Plugin Classes.
		add_action( 'admin_init', array( $this, 'deactivate_free_version' ) );

		// Defer loading of Plugin Classes.
		add_action( 'init', array( $this, 'initialize' ), 1 );
		add_action( 'init', array( $this, 'upgrade' ), 2 );

		// Admin Menus.
		add_action( 'page_generator_pro_admin_admin_menu', array( $this, 'admin_menus' ) );

		// Localization.
		add_action( 'init', array( $this, 'load_language_files' ) );

	}

	/**
	 * Register menus and submenus.
	 *
	 * @since   4.9.9
	 *
	 * @param   string $minimum_capability  Minimum capability required for access.
	 */
	public function admin_menus( $minimum_capability ) {

		// Bail if we cannot access any menus.
		if ( ! $this->licensing->can_access( 'show_menu' ) ) {
			return;
		}

		// Licensing.
		add_menu_page( $this->plugin->displayName, $this->plugin->displayName, $minimum_capability, $this->plugin->name, array( $this->licensing, 'licensing_screen' ), $this->plugin->logo );
		add_submenu_page( $this->plugin->name, __( 'Licensing', 'page-generator-pro' ), __( 'Licensing', 'page-generator-pro' ), $minimum_capability, $this->plugin->name, array( $this->licensing, 'licensing_screen' ) );

		// Bail if the product is not licensed.
		if ( ! $this->licensing->check_license_key_valid() ) {
			return;
		}

		// Licensed - add additional menu entries, if access permitted.
		if ( $this->licensing->can_access( 'show_menu_settings' ) ) {
			$settings_page = add_submenu_page( $this->plugin->name, __( 'Settings', 'page-generator-pro' ), __( 'Settings', 'page-generator-pro' ), $minimum_capability, $this->plugin->name . '-settings', array( $this->get_class( 'admin' ), 'settings_screen' ) );
		}

		// Register any buttons required for the Content Groups WP_List_Table as hidden submenu pages.
		$buttons = $this->get_class( 'groups_ui' )->get_add_new_buttons();
		if ( count( $buttons ) ) {
			foreach ( $buttons as $key => $button ) {
				add_submenu_page( '', $button['label'], $button['label'], $minimum_capability, $key, array( $this->get_class( $button['class'] ), 'maybe_load' ) );
			}
		}

		if ( $this->licensing->can_access( 'show_menu_keywords' ) ) {
			$keywords_page = add_submenu_page( $this->plugin->name, __( 'Keywords', 'page-generator-pro' ), __( 'Keywords', 'page-generator-pro' ), $minimum_capability, $this->plugin->name . '-keywords', array( $this->get_class( 'admin' ), 'keywords_screen' ) );
			add_action( "load-$keywords_page", array( $this->get_class( 'admin' ), 'add_keyword_screen_options' ) );
		}

		if ( $this->licensing->can_access( 'show_menu_generate' ) ) {
			$groups_page     = add_submenu_page( $this->plugin->name, __( 'Generate Content', 'page-generator-pro' ), __( 'Generate Content', 'page-generator-pro' ), $minimum_capability, 'edit.php?post_type=' . $this->get_class( 'post_type' )->post_type_name );
			$groups_tax_page = add_submenu_page( $this->plugin->name, __( 'Generate Terms', 'page-generator-pro' ), __( 'Generate Terms', 'page-generator-pro' ), $minimum_capability, 'edit-tags.php?taxonomy=' . $this->get_class( 'taxonomy' )->taxonomy_name );
			$generate_page   = add_submenu_page( $this->plugin->name, __( 'Generate', 'page-generator-pro' ), __( 'Generate', 'page-generator-pro' ), $minimum_capability, $this->plugin->name . '-generate', array( $this->get_class( 'admin' ), 'generate_screen' ) );
		}

		if ( $this->licensing->can_access( 'show_menu_logs' ) ) {
			if ( $this->get_class( 'settings' )->get_setting( $this->plugin->name . '-generate', 'log_enabled', '0' ) ) {
				$log_page = add_submenu_page( $this->plugin->name, __( 'Logs', 'page-generator-pro' ), __( 'Logs', 'page-generator-pro' ), $minimum_capability, $this->plugin->name . '-logs', array( $this->get_class( 'admin' ), 'log_screen' ) );
				add_action( "load-$log_page", array( $this->get_class( 'admin' ), 'add_log_screen_options' ) );
			}
		}

		if ( $this->licensing->can_access( 'show_menu_import_export' ) ) {
			do_action( 'page_generator_pro_admin_menu_import_export' );
		}

		if ( $this->licensing->can_access( 'show_menu_support' ) ) {
			do_action( 'page_generator_pro_admin_menu_support' );
		}

	}

	/**
	 * Detects if the Free version of the Plugin is running, and if so,
	 * deactivates it.
	 *
	 * @since   1.6.7
	 */
	public function deactivate_free_version() {

		// Bail if the function is not available.
		if ( ! function_exists( 'deactivate_plugins' ) ) {
			return;
		}

		// Bail if the Free version is not active.
		if ( ! is_plugin_active( 'page-generator/page-generator.php' ) ) {
			return;
		}

		// Deactivate the Free version.
		deactivate_plugins( 'page-generator/page-generator.php' );

	}

	/**
	 * Initializes required and licensed classes
	 *
	 * @since   1.9.8
	 */
	public function initialize() {

		// Translate review string.
		$this->plugin->review_notice = sprintf(
			/* translators: %s: Plugin Name */
			__( 'Thanks for using %s to generate content!', 'page-generator-pro' ),
			$this->plugin->displayName
		);

		// Licensing Submodule.
		if ( ! class_exists( 'LicensingUpdateManager' ) ) {
			require_once $this->plugin->folder . '_modules/licensing/class-licensingupdatemanager.php';
		}
		$this->licensing = new LicensingUpdateManager( $this->plugin, 'https://www.wpzinc.com' );

		// Run Plugin Display Name, URLs and logo through Whitelabelling if available.
		$this->plugin->displayName       = $this->licensing->get_feature_parameter( 'whitelabelling', 'display_name', $this->plugin->displayName );
		$this->plugin->support_url       = $this->licensing->get_feature_parameter( 'whitelabelling', 'support_url', $this->plugin->support_url );
		$this->plugin->documentation_url = $this->licensing->get_feature_parameter( 'whitelabelling', 'documentation_url', $this->plugin->documentation_url );
		if ( ! empty( $this->licensing->get_feature_parameter( 'whitelabelling', 'logo', $this->plugin->logo ) ) ) {
			$this->plugin->logo = $this->licensing->get_feature_parameter( 'whitelabelling', 'logo', $this->plugin->logo );
		}
		if ( ! empty( $this->licensing->get_feature_parameter( 'whitelabelling', 'header_background_color', $this->plugin->header_background_color ) ) ) {
			$this->plugin->header_background_color = $this->licensing->get_feature_parameter( 'whitelabelling', 'header_background_color', $this->plugin->header_background_color );
		}
		if ( ! empty( $this->licensing->get_feature_parameter( 'whitelabelling', 'header_primary_text_color', $this->plugin->header_primary_text_color ) ) ) {
			$this->plugin->header_primary_text_color = $this->licensing->get_feature_parameter( 'whitelabelling', 'header_primary_text_color', $this->plugin->header_primary_text_color );
		}
		if ( ! empty( $this->licensing->get_feature_parameter( 'whitelabelling', 'header_secondary_text_color', $this->plugin->header_secondary_text_color ) ) ) {
			$this->plugin->header_secondary_text_color = $this->licensing->get_feature_parameter( 'whitelabelling', 'header_secondary_text_color', $this->plugin->header_secondary_text_color );
		}

		// Dashboard Submodule.
		if ( ! class_exists( 'WPZincDashboardWidget' ) ) {
			require_once $this->plugin->folder . '_modules/dashboard/class-wpzincdashboardwidget.php';
		}
		$this->dashboard = new WPZincDashboardWidget( $this->plugin, 'https://www.wpzinc.com/wp-content/plugins/lum-deactivation' );

		// Show Support Menu and hide Upgrade Menu.
		$this->dashboard->show_support_menu();
		$this->dashboard->hide_upgrade_menu();

		// Disable Review Notification if whitelabelling is enabled.
		if ( $this->licensing->has_feature( 'whitelabelling' ) ) {
			$this->dashboard->disable_review_request();
		}

		$this->classes = new stdClass();

		$this->initialize_admin_or_frontend_editor();
		$this->initialize_cli_cron();
		$this->initialize_frontend();

	}

	/**
	 * Initialize classes for the WordPress Administration interface or a frontend Page Builder
	 *
	 * @since   2.5.2
	 */
	private function initialize_admin_or_frontend_editor() {

		// Bail if this request isn't for the WordPress Administration interface and isn't for a frontend Page Builder.
		if ( ! $this->is_admin_or_frontend_editor() ) {
			return;
		}

		// Initialize classes used by the activation and update processes, before the Plugin might be licensed.
		$this->classes->admin                       = new Page_Generator_Pro_Admin( self::$instance );
		$this->classes->airtable                    = new Page_Generator_Pro_Airtable( self::$instance );
		$this->classes->common                      = new Page_Generator_Pro_Common( self::$instance );
		$this->classes->cron                        = new Page_Generator_Pro_Cron( self::$instance );
		$this->classes->install                     = new Page_Generator_Pro_Install( self::$instance );
		$this->classes->geo                         = new Page_Generator_Pro_Geo( self::$instance );
		$this->classes->groups                      = new Page_Generator_Pro_Groups( self::$instance );
		$this->classes->groups_directory            = new Page_Generator_Pro_Groups_Directory( self::$instance );
		$this->classes->groups_ai                   = new Page_Generator_Pro_Groups_AI( self::$instance );
		$this->classes->indexnow                    = new Page_Generator_Pro_IndexNow( self::$instance );
		$this->classes->keywords                    = new Page_Generator_Pro_Keywords( self::$instance );
		$this->classes->keywords_source_local       = new Page_Generator_Pro_Keywords_Source_Local( self::$instance );
		$this->classes->keywords_source_csv         = new Page_Generator_Pro_Keywords_Source_CSV( self::$instance );
		$this->classes->keywords_source_csv_url     = new Page_Generator_Pro_Keywords_Source_CSV_URL( self::$instance );
		$this->classes->keywords_source_database    = new Page_Generator_Pro_Keywords_Source_Database( self::$instance );
		$this->classes->keywords_source_rss         = new Page_Generator_Pro_Keywords_Source_RSS( self::$instance );
		$this->classes->keywords_source_spreadsheet = new Page_Generator_Pro_Keywords_Source_Spreadsheet( self::$instance );
		$this->classes->log                         = new Page_Generator_Pro_Log( self::$instance );
		$this->classes->notices                     = new Page_Generator_Pro_Notices( self::$instance );
		$this->classes->notion                      = new Page_Generator_Pro_Notion( self::$instance );
		$this->classes->phone_area_codes            = new Page_Generator_Pro_Phone_Area_Codes( self::$instance );
		$this->classes->post_type                   = new Page_Generator_Pro_PostType( self::$instance );
		$this->classes->settings                    = new Page_Generator_Pro_Settings( self::$instance );
		$this->classes->screen                      = new Page_Generator_Pro_Screen( self::$instance );
		$this->classes->shortcode                   = new Page_Generator_Pro_Shortcode( self::$instance );
		$this->classes->taxonomy                    = new Page_Generator_Pro_Taxonomy( self::$instance );

		// Initialize licensed classes.
		if ( $this->licensing->check_license_key_valid() ) {
			$this->classes->acf                         = new Page_Generator_Pro_ACF( self::$instance );
			$this->classes->aioseo                      = new Page_Generator_Pro_AIOSEO( self::$instance );
			$this->classes->alibaba                     = new Page_Generator_Pro_Alibaba( self::$instance );
			$this->classes->all_in_one_video_gallery    = new Page_Generator_Pro_All_In_One_Video_Gallery( self::$instance );
			$this->classes->articleforge                = new Page_Generator_Pro_ArticleForge( self::$instance );
			$this->classes->authentic                   = new Page_Generator_Pro_Authentic( self::$instance );
			$this->classes->avia                        = new Page_Generator_Pro_Avia( self::$instance );
			$this->classes->ajax                        = new Page_Generator_Pro_AJAX( self::$instance );
			$this->classes->beaver_builder              = new Page_Generator_Pro_Beaver_Builder( self::$instance );
			$this->classes->betheme                     = new Page_Generator_Pro_Betheme( self::$instance );
			$this->classes->breakdance                  = new Page_Generator_Pro_Breakdance( self::$instance );
			$this->classes->bricks                      = new Page_Generator_Pro_Bricks( self::$instance );
			$this->classes->brizy                       = new Page_Generator_Pro_Brizy( self::$instance );
			$this->classes->block_spin                  = new Page_Generator_Pro_Block_Spin( self::$instance );
			$this->classes->chimprewriter               = new Page_Generator_Pro_ChimpRewriter( self::$instance );
			$this->classes->claude_ai                   = new Page_Generator_Pro_Claude_AI( self::$instance );
			$this->classes->conditional_output          = new Page_Generator_Pro_Conditional_Output( self::$instance );
			$this->classes->contentbot                  = new Page_Generator_Pro_ContentBot( self::$instance );
			$this->classes->cornerstone                 = new Page_Generator_Pro_Cornerstone( self::$instance );
			$this->classes->creative_commons            = new Page_Generator_Pro_Creative_Commons( self::$instance );
			$this->classes->ddpro                       = new Page_Generator_Pro_DDPro( self::$instance );
			$this->classes->deepseek                    = new Page_Generator_Pro_Deepseek( self::$instance );
			$this->classes->divi                        = new Page_Generator_Pro_Divi( self::$instance );
			$this->classes->editor                      = new Page_Generator_Pro_Editor( self::$instance );
			$this->classes->elementor                   = new Page_Generator_Pro_Elementor( self::$instance );
			$this->classes->exif                        = new Page_Generator_Pro_Exif( self::$instance );
			$this->classes->export                      = new Page_Generator_Pro_Export( self::$instance );
			$this->classes->fifu                        = new Page_Generator_Pro_FIFU( self::$instance );
			$this->classes->flatsome                    = new Page_Generator_Pro_Flatsome( self::$instance );
			$this->classes->flotheme                    = new Page_Generator_Pro_Flotheme( self::$instance );
			$this->classes->fresh_framework             = new Page_Generator_Pro_Fresh_Framework( self::$instance );
			$this->classes->fusion_builder              = new Page_Generator_Pro_Fusion_Builder( self::$instance );
			$this->classes->gemini_ai                   = new Page_Generator_Pro_Gemini_AI( self::$instance );
			$this->classes->gemini_ai_image             = new Page_Generator_Pro_Gemini_AI_Image( self::$instance );
			$this->classes->generate                    = new Page_Generator_Pro_Generate( self::$instance );
			$this->classes->genesis                     = new Page_Generator_Pro_Genesis( self::$instance );
			$this->classes->georocket                   = new Page_Generator_Pro_Georocket( self::$instance );
			$this->classes->goodlayers                  = new Page_Generator_Pro_GoodLayers( self::$instance );
			$this->classes->goodlayers_infinite         = new Page_Generator_Pro_Goodlayers_Infinite( self::$instance );
			$this->classes->google_places               = new Page_Generator_Pro_Google_Places( self::$instance );
			$this->classes->grok_ai                     = new Page_Generator_Pro_Grok_AI( self::$instance );
			$this->classes->grok_ai_image               = new Page_Generator_Pro_Grok_AI_Image( self::$instance );
			$this->classes->groups_table                = new Page_Generator_Pro_Groups_Table( self::$instance );
			$this->classes->groups_ui                   = new Page_Generator_Pro_Groups_UI( self::$instance );
			$this->classes->groups_terms                = new Page_Generator_Pro_Groups_Terms( self::$instance );
			$this->classes->groups_terms_table          = new Page_Generator_Pro_Groups_Terms_Table( self::$instance );
			$this->classes->groups_terms_ui             = new Page_Generator_Pro_Groups_Terms_UI( self::$instance );
			$this->classes->gutenberg                   = new Page_Generator_Pro_Gutenberg( self::$instance );
			$this->classes->hybrid_composer             = new Page_Generator_Pro_Hybrid_Composer( self::$instance );
			$this->classes->i18n                        = new Page_Generator_Pro_I18n( self::$instance );
			$this->classes->ideogram_ai                 = new Page_Generator_Pro_Ideogram_AI( self::$instance );
			$this->classes->image_url                   = new Page_Generator_Pro_Image_URL( self::$instance );
			$this->classes->import                      = new Page_Generator_Pro_Import( self::$instance );
			$this->classes->integrations                = new Page_Generator_Pro_Integrations( self::$instance );
			$this->classes->keywords_generate_locations = new Page_Generator_Pro_Keywords_Generate_Locations( self::$instance );
			$this->classes->landkit                     = new Page_Generator_Pro_Landkit( self::$instance );
			$this->classes->listingpro                  = new Page_Generator_Pro_ListingPro( self::$instance );
			$this->classes->live_composer               = new Page_Generator_Pro_Live_Composer( self::$instance );
			$this->classes->make_theme                  = new Page_Generator_Pro_Make_Theme( self::$instance );
			$this->classes->medicenter                  = new Page_Generator_Pro_Medicenter( self::$instance );
			$this->classes->media_library               = new Page_Generator_Pro_Media_Library( self::$instance );
			$this->classes->metabox_io                  = new Page_Generator_Pro_Metabox_IO( self::$instance );
			$this->classes->midjourney                  = new Page_Generator_Pro_Midjourney( self::$instance );
			$this->classes->mistral_ai                  = new Page_Generator_Pro_Mistral_AI( self::$instance );
			$this->classes->neve                        = new Page_Generator_Pro_Neve( self::$instance );
			$this->classes->oceanwp                     = new Page_Generator_Pro_OceanWP( self::$instance );
			$this->classes->openai                      = new Page_Generator_Pro_OpenAI( self::$instance );
			$this->classes->openai_image                = new Page_Generator_Pro_OpenAI_Image( self::$instance );
			$this->classes->openrouter                  = new Page_Generator_Pro_OpenRouter( self::$instance );
			$this->classes->open_weather_map            = new Page_Generator_Pro_Open_Weather_Map( self::$instance );
			$this->classes->optimizepress               = new Page_Generator_Pro_OptimizePress( self::$instance );
			$this->classes->ovic                        = new Page_Generator_Pro_Ovic( self::$instance );
			$this->classes->oxygen                      = new Page_Generator_Pro_Oxygen( self::$instance );
			$this->classes->page_builders               = new Page_Generator_Pro_PageBuilders( self::$instance );
			$this->classes->performance                 = new Page_Generator_Pro_Performance( self::$instance );
			$this->classes->perplexity                  = new Page_Generator_Pro_Perplexity( self::$instance );
			$this->classes->persistent_cache            = new Page_Generator_Pro_Persistent_Cache( self::$instance );
			$this->classes->pexels                      = new Page_Generator_Pro_Pexels( self::$instance );
			$this->classes->pixabay                     = new Page_Generator_Pro_Pixabay( self::$instance );
			$this->classes->platinum_seo                = new Page_Generator_Pro_Platinum_SEO( self::$instance );
			$this->classes->popup_maker                 = new Page_Generator_Pro_Popup_Maker( self::$instance );
			$this->classes->rank_math                   = new Page_Generator_Pro_Rank_Math( self::$instance );
			$this->classes->research                    = new Page_Generator_Pro_Research( self::$instance );
			$this->classes->salient                     = new Page_Generator_Pro_Salient( self::$instance );
			$this->classes->schema_pro                  = new Page_Generator_Pro_Schema_Pro( self::$instance );
			$this->classes->search_exclude              = new Page_Generator_Pro_Search_Exclude( self::$instance );
			$this->classes->seopress                    = new Page_Generator_Pro_SEOPress( self::$instance );
			$this->classes->seopressor                  = new Page_Generator_Pro_SEOPressor( self::$instance );
			$this->classes->shortcode_ai                = new Page_Generator_Pro_Shortcode_AI( self::$instance );
			$this->classes->shortcode_custom_field      = new Page_Generator_Pro_Shortcode_Custom_Field( self::$instance );
			$this->classes->shortcode_google_map        = new Page_Generator_Pro_Shortcode_Google_Map( self::$instance );
			$this->classes->shortcode_open_street_map   = new Page_Generator_Pro_Shortcode_Open_Street_Map( self::$instance );
			$this->classes->shortcode_related_links     = new Page_Generator_Pro_Shortcode_Related_Links( self::$instance );
			$this->classes->shortcode_research          = new Page_Generator_Pro_Shortcode_Research( self::$instance );
			$this->classes->siteorigin                  = new Page_Generator_Pro_SiteOrigin( self::$instance );
			$this->classes->slim_seo                    = new Page_Generator_Pro_Slim_SEO( self::$instance );
			$this->classes->smartcrawl_seo              = new Page_Generator_Pro_SmartCrawl_SEO( self::$instance );
			$this->classes->spin_rewriter               = new Page_Generator_Pro_Spin_Rewriter( self::$instance );
			$this->classes->spinnerchief                = new Page_Generator_Pro_SpinnerChief( self::$instance );
			$this->classes->spintax                     = new Page_Generator_Pro_Spintax( self::$instance );
			$this->classes->squirrly_seo                = new Page_Generator_Pro_Squirrly_SEO( self::$instance );
			$this->classes->straico                     = new Page_Generator_Pro_Straico( self::$instance );
			$this->classes->thebuilt                    = new Page_Generator_Pro_TheBuilt( self::$instance );
			$this->classes->thesaurus                   = new Page_Generator_Pro_Thesaurus();
			$this->classes->theseven                    = new Page_Generator_Pro_TheSeven( self::$instance );
			$this->classes->the_seo_framework           = new Page_Generator_Pro_The_SEO_Framework( self::$instance );
			$this->classes->thrive_architect            = new Page_Generator_Pro_Thrive_Architect( self::$instance );
			$this->classes->visual_composer             = new Page_Generator_Pro_Visual_Composer( self::$instance );
			$this->classes->woocommerce                 = new Page_Generator_Pro_WooCommerce( self::$instance );
			$this->classes->wikipedia                   = new Page_Generator_Pro_Wikipedia( self::$instance );
			$this->classes->wikipedia_image             = new Page_Generator_Pro_Wikipedia_Image( self::$instance );
			$this->classes->wordai                      = new Page_Generator_Pro_WordAI( self::$instance );
			$this->classes->wp_all_export               = new Page_Generator_Pro_WP_All_Export( self::$instance );
			$this->classes->wpbakery_page_builder       = new Page_Generator_Pro_WPBakery_Page_Builder( self::$instance );
			$this->classes->wpsso                       = new Page_Generator_Pro_WPSSO( self::$instance );
			$this->classes->wptouch_pro                 = new Page_Generator_Pro_WPTouch_Pro( self::$instance );
			$this->classes->yelp                        = new Page_Generator_Pro_Yelp( self::$instance );
			$this->classes->yoast_seo                   = new Page_Generator_Pro_Yoast_SEO( self::$instance );
			$this->classes->yootheme                    = new Page_Generator_Pro_Yootheme( self::$instance );
			$this->classes->youtube                     = new Page_Generator_Pro_Youtube( self::$instance );
			$this->classes->zion_builder                = new Page_Generator_Pro_Zion_Builder( self::$instance );
		}

	}

	/**
	 * Initialize classes for WP-CLI and WP-Cron
	 *
	 * @since   2.5.2
	 */
	private function initialize_cli_cron() {

		// Bail if this isn't a CLI or CRON request.
		if ( ! $this->is_cli() && ! $this->is_cron() ) {
			return;
		}

		// Initialize classes used by the activation and update processes, before the Plugin might be licensed.
		$this->classes->admin                       = new Page_Generator_Pro_Admin( self::$instance );
		$this->classes->airtable                    = new Page_Generator_Pro_Airtable( self::$instance );
		$this->classes->common                      = new Page_Generator_Pro_Common( self::$instance );
		$this->classes->cron                        = new Page_Generator_Pro_Cron( self::$instance );
		$this->classes->install                     = new Page_Generator_Pro_Install( self::$instance );
		$this->classes->geo                         = new Page_Generator_Pro_Geo( self::$instance );
		$this->classes->groups                      = new Page_Generator_Pro_Groups( self::$instance );
		$this->classes->indexnow                    = new Page_Generator_Pro_IndexNow( self::$instance );
		$this->classes->keywords                    = new Page_Generator_Pro_Keywords( self::$instance );
		$this->classes->keywords_source_local       = new Page_Generator_Pro_Keywords_Source_Local( self::$instance );
		$this->classes->keywords_source_csv         = new Page_Generator_Pro_Keywords_Source_CSV( self::$instance );
		$this->classes->keywords_source_csv_url     = new Page_Generator_Pro_Keywords_Source_CSV_URL( self::$instance );
		$this->classes->keywords_source_database    = new Page_Generator_Pro_Keywords_Source_Database( self::$instance );
		$this->classes->keywords_source_rss         = new Page_Generator_Pro_Keywords_Source_RSS( self::$instance );
		$this->classes->keywords_source_spreadsheet = new Page_Generator_Pro_Keywords_Source_Spreadsheet( self::$instance );
		$this->classes->log                         = new Page_Generator_Pro_Log( self::$instance );
		$this->classes->notices                     = new Page_Generator_Pro_Notices( self::$instance );
		$this->classes->notion                      = new Page_Generator_Pro_Notion( self::$instance );
		$this->classes->phone_area_codes            = new Page_Generator_Pro_Phone_Area_Codes( self::$instance );
		$this->classes->post_type                   = new Page_Generator_Pro_PostType( self::$instance );
		$this->classes->settings                    = new Page_Generator_Pro_Settings( self::$instance );
		$this->classes->screen                      = new Page_Generator_Pro_Screen( self::$instance );
		$this->classes->shortcode                   = new Page_Generator_Pro_Shortcode( self::$instance );
		$this->classes->taxonomy                    = new Page_Generator_Pro_Taxonomy( self::$instance );

		// Bail if the Plugin isn't licensed.
		if ( ! $this->licensing->check_license_key_valid() ) {
			return;
		}

		$this->classes->aioseo                    = new Page_Generator_Pro_AIOSEO( self::$instance );
		$this->classes->alibaba                   = new Page_Generator_Pro_Alibaba( self::$instance );
		$this->classes->all_in_one_video_gallery  = new Page_Generator_Pro_All_In_One_Video_Gallery( self::$instance );
		$this->classes->authentic                 = new Page_Generator_Pro_Authentic( self::$instance );
		$this->classes->avia                      = new Page_Generator_Pro_Avia( self::$instance );
		$this->classes->beaver_builder            = new Page_Generator_Pro_Beaver_Builder( self::$instance );
		$this->classes->betheme                   = new Page_Generator_Pro_Betheme( self::$instance );
		$this->classes->breakdance                = new Page_Generator_Pro_Breakdance( self::$instance );
		$this->classes->bricks                    = new Page_Generator_Pro_Bricks( self::$instance );
		$this->classes->brizy                     = new Page_Generator_Pro_Brizy( self::$instance );
		$this->classes->block_spin                = new Page_Generator_Pro_Block_Spin( self::$instance );
		$this->classes->chimprewriter             = new Page_Generator_Pro_ChimpRewriter( self::$instance );
		$this->classes->claude_ai                 = new Page_Generator_Pro_Claude_AI( self::$instance );
		$this->classes->conditional_output        = new Page_Generator_Pro_Conditional_Output( self::$instance );
		$this->classes->contentbot                = new Page_Generator_Pro_ContentBot( self::$instance );
		$this->classes->common                    = new Page_Generator_Pro_Common( self::$instance );
		$this->classes->creative_commons          = new Page_Generator_Pro_Creative_Commons( self::$instance );
		$this->classes->ddpro                     = new Page_Generator_Pro_DDPro( self::$instance );
		$this->classes->deepseek                  = new Page_Generator_Pro_Deepseek( self::$instance );
		$this->classes->divi                      = new Page_Generator_Pro_Divi( self::$instance );
		$this->classes->elementor                 = new Page_Generator_Pro_Elementor( self::$instance );
		$this->classes->exif                      = new Page_Generator_Pro_Exif( self::$instance );
		$this->classes->fifu                      = new Page_Generator_Pro_FIFU( self::$instance );
		$this->classes->flatsome                  = new Page_Generator_Pro_Flatsome( self::$instance );
		$this->classes->flotheme                  = new Page_Generator_Pro_Flotheme( self::$instance );
		$this->classes->fresh_framework           = new Page_Generator_Pro_Fresh_Framework( self::$instance );
		$this->classes->fusion_builder            = new Page_Generator_Pro_Fusion_Builder( self::$instance );
		$this->classes->gemini_ai                 = new Page_Generator_Pro_Gemini_AI( self::$instance );
		$this->classes->gemini_ai_image           = new Page_Generator_Pro_Gemini_AI_Image( self::$instance );
		$this->classes->generate                  = new Page_Generator_Pro_Generate( self::$instance );
		$this->classes->genesis                   = new Page_Generator_Pro_Genesis( self::$instance );
		$this->classes->geo                       = new Page_Generator_Pro_Geo( self::$instance );
		$this->classes->georocket                 = new Page_Generator_Pro_Georocket( self::$instance );
		$this->classes->google_places             = new Page_Generator_Pro_Google_Places( self::$instance );
		$this->classes->grok_ai                   = new Page_Generator_Pro_Grok_AI( self::$instance );
		$this->classes->grok_ai_image             = new Page_Generator_Pro_Grok_AI_Image( self::$instance );
		$this->classes->groups                    = new Page_Generator_Pro_Groups( self::$instance );
		$this->classes->groups_terms              = new Page_Generator_Pro_Groups_Terms( self::$instance );
		$this->classes->gutenberg                 = new Page_Generator_Pro_Gutenberg( self::$instance );
		$this->classes->hybrid_composer           = new Page_Generator_Pro_Hybrid_Composer( self::$instance );
		$this->classes->i18n                      = new Page_Generator_Pro_I18n( self::$instance );
		$this->classes->ideogram_ai               = new Page_Generator_Pro_Ideogram_AI( self::$instance );
		$this->classes->image_url                 = new Page_Generator_Pro_Image_URL( self::$instance );
		$this->classes->import                    = new Page_Generator_Pro_Import( self::$instance );
		$this->classes->keywords                  = new Page_Generator_Pro_Keywords( self::$instance );
		$this->classes->keywords_source_local     = new Page_Generator_Pro_Keywords_Source_Local( self::$instance );
		$this->classes->keywords_source_csv       = new Page_Generator_Pro_Keywords_Source_CSV( self::$instance );
		$this->classes->keywords_source_csv_url   = new Page_Generator_Pro_Keywords_Source_CSV_URL( self::$instance );
		$this->classes->keywords_source_database  = new Page_Generator_Pro_Keywords_Source_Database( self::$instance );
		$this->classes->landkit                   = new Page_Generator_Pro_Landkit( self::$instance );
		$this->classes->live_composer             = new Page_Generator_Pro_Live_Composer( self::$instance );
		$this->classes->log                       = new Page_Generator_Pro_Log( self::$instance );
		$this->classes->make_theme                = new Page_Generator_Pro_Make_Theme( self::$instance );
		$this->classes->medicenter                = new Page_Generator_Pro_Medicenter( self::$instance );
		$this->classes->media_library             = new Page_Generator_Pro_Media_Library( self::$instance );
		$this->classes->metabox_io                = new Page_Generator_Pro_Metabox_IO( self::$instance );
		$this->classes->midjourney                = new Page_Generator_Pro_Midjourney( self::$instance );
		$this->classes->mistral_ai                = new Page_Generator_Pro_Mistral_AI( self::$instance );
		$this->classes->neve                      = new Page_Generator_Pro_Neve( self::$instance );
		$this->classes->oceanwp                   = new Page_Generator_Pro_OceanWP( self::$instance );
		$this->classes->openai                    = new Page_Generator_Pro_OpenAI( self::$instance );
		$this->classes->openai_image              = new Page_Generator_Pro_OpenAI_Image( self::$instance );
		$this->classes->openrouter                = new Page_Generator_Pro_OpenRouter( self::$instance );
		$this->classes->open_weather_map          = new Page_Generator_Pro_Open_Weather_Map( self::$instance );
		$this->classes->optimizepress             = new Page_Generator_Pro_OptimizePress( self::$instance );
		$this->classes->ovic                      = new Page_Generator_Pro_Ovic( self::$instance );
		$this->classes->oxygen                    = new Page_Generator_Pro_Oxygen( self::$instance );
		$this->classes->page_builders             = new Page_Generator_Pro_PageBuilders( self::$instance );
		$this->classes->pexels                    = new Page_Generator_Pro_Pexels( self::$instance );
		$this->classes->perplexity                = new Page_Generator_Pro_Perplexity( self::$instance );
		$this->classes->persistent_cache          = new Page_Generator_Pro_Persistent_Cache( self::$instance );
		$this->classes->phone_area_codes          = new Page_Generator_Pro_Phone_Area_Codes( self::$instance );
		$this->classes->popup_maker               = new Page_Generator_Pro_Popup_Maker( self::$instance );
		$this->classes->post_type                 = new Page_Generator_Pro_PostType( self::$instance );
		$this->classes->pixabay                   = new Page_Generator_Pro_Pixabay( self::$instance );
		$this->classes->platinum_seo              = new Page_Generator_Pro_Platinum_SEO( self::$instance );
		$this->classes->rank_math                 = new Page_Generator_Pro_Rank_Math( self::$instance );
		$this->classes->salient                   = new Page_Generator_Pro_Salient( self::$instance );
		$this->classes->schema_pro                = new Page_Generator_Pro_Schema_Pro( self::$instance );
		$this->classes->search_exclude            = new Page_Generator_Pro_Search_Exclude( self::$instance );
		$this->classes->seopress                  = new Page_Generator_Pro_SEOPress( self::$instance );
		$this->classes->seopressor                = new Page_Generator_Pro_SEOPressor( self::$instance );
		$this->classes->settings                  = new Page_Generator_Pro_Settings( self::$instance );
		$this->classes->shortcode_ai              = new Page_Generator_Pro_Shortcode_AI( self::$instance );
		$this->classes->shortcode_custom_field    = new Page_Generator_Pro_Shortcode_Custom_Field( self::$instance );
		$this->classes->shortcode_google_map      = new Page_Generator_Pro_Shortcode_Google_Map( self::$instance );
		$this->classes->shortcode_open_street_map = new Page_Generator_Pro_Shortcode_Open_Street_Map( self::$instance );
		$this->classes->shortcode_related_links   = new Page_Generator_Pro_Shortcode_Related_Links( self::$instance );
		$this->classes->shortcode_research        = new Page_Generator_Pro_Shortcode_Research( self::$instance );
		$this->classes->shortcode                 = new Page_Generator_Pro_Shortcode( self::$instance );
		$this->classes->siteorigin                = new Page_Generator_Pro_SiteOrigin( self::$instance );
		$this->classes->slim_seo                  = new Page_Generator_Pro_Slim_SEO( self::$instance );
		$this->classes->smartcrawl_seo            = new Page_Generator_Pro_SmartCrawl_SEO( self::$instance );
		$this->classes->spin_rewriter             = new Page_Generator_Pro_Spin_Rewriter( self::$instance );
		$this->classes->spinnerchief              = new Page_Generator_Pro_SpinnerChief( self::$instance );
		$this->classes->spintax                   = new Page_Generator_Pro_Spintax( self::$instance );
		$this->classes->squirrly_seo              = new Page_Generator_Pro_Squirrly_SEO( self::$instance );
		$this->classes->straico                   = new Page_Generator_Pro_Straico( self::$instance );
		$this->classes->taxonomy                  = new Page_Generator_Pro_Taxonomy( self::$instance );
		$this->classes->thebuilt                  = new Page_Generator_Pro_TheBuilt( self::$instance );
		$this->classes->thesaurus                 = new Page_Generator_Pro_Thesaurus();
		$this->classes->theseven                  = new Page_Generator_Pro_TheSeven( self::$instance );
		$this->classes->the_seo_framework         = new Page_Generator_Pro_The_SEO_Framework( self::$instance );
		$this->classes->thrive_architect          = new Page_Generator_Pro_Thrive_Architect( self::$instance );
		$this->classes->visual_composer           = new Page_Generator_Pro_Visual_Composer( self::$instance );
		$this->classes->woocommerce               = new Page_Generator_Pro_WooCommerce( self::$instance );
		$this->classes->wikipedia                 = new Page_Generator_Pro_Wikipedia( self::$instance );
		$this->classes->wikipedia_image           = new Page_Generator_Pro_Wikipedia_Image( self::$instance );
		$this->classes->wordai                    = new Page_Generator_Pro_WordAI( self::$instance );
		$this->classes->wp_all_export             = new Page_Generator_Pro_WP_All_Export( self::$instance );
		$this->classes->wpbakery_page_builder     = new Page_Generator_Pro_WPBakery_Page_Builder( self::$instance );
		$this->classes->wpsso                     = new Page_Generator_Pro_WPSSO( self::$instance );
		$this->classes->wptouch_pro               = new Page_Generator_Pro_WPTouch_Pro( self::$instance );
		$this->classes->yelp                      = new Page_Generator_Pro_Yelp( self::$instance );
		$this->classes->yoast_seo                 = new Page_Generator_Pro_Yoast_SEO( self::$instance );
		$this->classes->yootheme                  = new Page_Generator_Pro_Yootheme( self::$instance );
		$this->classes->youtube                   = new Page_Generator_Pro_Youtube( self::$instance );
		$this->classes->zion_builder              = new Page_Generator_Pro_Zion_Builder( self::$instance );

		// Register the CLI command(s).
		if ( class_exists( 'WP_CLI' ) ) {
			$this->classes->cli = new Page_Generator_Pro_CLI( self::$instance );
		}

	}

	/**
	 * Initialize classes for the frontend web site
	 *
	 * @since   2.5.2
	 */
	private function initialize_frontend() {

		// Bail if this request isn't for the frontend web site.
		if ( is_admin() ) {
			return;
		}

		$this->classes->betheme                   = new Page_Generator_Pro_Betheme( self::$instance );
		$this->classes->block_spin                = new Page_Generator_Pro_Block_Spin( self::$instance );
		$this->classes->bricks                    = new Page_Generator_Pro_Bricks( self::$instance );
		$this->classes->common                    = new Page_Generator_Pro_Common( self::$instance );
		$this->classes->conditional_output        = new Page_Generator_Pro_Conditional_Output( self::$instance );
		$this->classes->cornerstone               = new Page_Generator_Pro_Cornerstone( self::$instance );
		$this->classes->divi                      = new Page_Generator_Pro_Divi( self::$instance );
		$this->classes->elementor                 = new Page_Generator_Pro_Elementor( self::$instance );
		$this->classes->frontend                  = new Page_Generator_Pro_Frontend( self::$instance );
		$this->classes->fusion_builder            = new Page_Generator_Pro_Fusion_Builder( self::$instance );
		$this->classes->keywords                  = new Page_Generator_Pro_Keywords( self::$instance );
		$this->classes->generate                  = new Page_Generator_Pro_Generate( self::$instance );
		$this->classes->geo                       = new Page_Generator_Pro_Geo( self::$instance );
		$this->classes->google_places             = new Page_Generator_Pro_Google_Places( self::$instance );
		$this->classes->gutenberg                 = new Page_Generator_Pro_Gutenberg( self::$instance );
		$this->classes->indexnow                  = new Page_Generator_Pro_IndexNow( self::$instance );
		$this->classes->persistent_cache          = new Page_Generator_Pro_Persistent_Cache( self::$instance );
		$this->classes->post_type                 = new Page_Generator_Pro_PostType( self::$instance );
		$this->classes->settings                  = new Page_Generator_Pro_Settings( self::$instance );
		$this->classes->screen                    = new Page_Generator_Pro_Screen( self::$instance );
		$this->classes->shortcode_custom_field    = new Page_Generator_Pro_Shortcode_Custom_Field( self::$instance );
		$this->classes->shortcode_open_street_map = new Page_Generator_Pro_Shortcode_Open_Street_Map( self::$instance );
		$this->classes->shortcode_related_links   = new Page_Generator_Pro_Shortcode_Related_Links( self::$instance );
		$this->classes->shortcode                 = new Page_Generator_Pro_Shortcode( self::$instance );
		$this->classes->spintax                   = new Page_Generator_Pro_Spintax( self::$instance );
		$this->classes->taxonomy                  = new Page_Generator_Pro_Taxonomy( self::$instance );
		$this->classes->yelp                      = new Page_Generator_Pro_Yelp( self::$instance );

	}

	/**
	 * Improved version of WordPress' is_admin(), which includes whether we're
	 * editing on the frontend using a Page Builder, or a developer / Addon
	 * wants to load Editor, Media Management and Upload classes on the frontend
	 * of the site.
	 *
	 * @since   2.5.2
	 *
	 * @return  bool    Is Admin or Frontend Editor Request
	 */
	public function is_admin_or_frontend_editor() {

		// If we're in the wp-admin, return true.
		if ( is_admin() ) {
			return true;
		}

		// Pro.
		if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_URI', $_SERVER ) ) { // @phpstan-ignore-line
			if ( strpos( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), '/cornerstone/edit/' ) !== false ) {
				return true;
			}
			if ( strpos( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), '/pro/' ) !== false ) {
				return true;
			}
			if ( strpos( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), '/x/' ) !== false ) {
				return true;
			}
			if ( strpos( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), 'cornerstone-endpoint' ) !== false ) {
				return true;
			}
		}

		// Bricks.
		if ( array_key_exists( 'HTTP_X_BRICKS_IS_BUILDER', $_SERVER ) ) {
			return true;
		}

		// If the request global exists, check for specific request keys which tell us
		// that we're using a frontend editor.
		if ( ! empty( $_REQUEST ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			// Sanitize request.
			$request = array_map( 'sanitize_text_field', $_REQUEST ); // phpcs:ignore WordPress.Security.NonceVerification

			// Beaver Builder.
			if ( array_key_exists( 'fl_builder', $request ) ) {
				return true;
			}

			// Bricks.
			if ( array_key_exists( 'bricks', $request ) ) {
				return true;
			}

			// Cornerstone (AJAX).
			if ( array_key_exists( '_cs_nonce', $request ) ) {
				return true;
			}
			if ( array_key_exists( '_locale', $request ) ) { // Hacky. Only registers frontend shortcodes otherwise, breaks CS if removed.
				return true;
			}

			// Divi.
			if ( array_key_exists( 'et_fb', $request ) ) {
				return true;
			}

			// Elementor.
			if ( array_key_exists( 'action', $request ) && $request['action'] === 'elementor' ) {
				return true;
			}

			// Fusion Builder (Avada Live).
			if ( array_key_exists( 'fb-edit', $request ) ) {
				return true;
			}

			// Kallyas.
			if ( array_key_exists( 'zn_pb_edit', $request ) ) {
				return true;
			}

			// LiveCanvas.
			if ( array_key_exists( 'lc_action_launch_editing', $request ) ) {
				return true;
			}

			// Oxygen.
			if ( array_key_exists( 'ct_builder', $request ) ) {
				return true;
			}

			// Themify Builder.
			if ( array_key_exists( 'tb-preview', $request ) && array_key_exists( 'tb-id', $request ) ) {
				return true;
			}

			// Thrive Architect.
			if ( array_key_exists( 'tve', $request ) ) {
				return true;
			}

			// Visual Composer.
			if ( array_key_exists( 'vcv-editable', $request ) ) {
				return true;
			}

			// WPBakery Page Builder.
			if ( array_key_exists( 'vc_editable', $request ) ) {
				return true;
			}
		} else {
			$request = false;
		}

		// Assume we're not in the Administration interface.
		$is_admin_or_frontend_editor = false;

		/**
		 * Filters whether the current request is a WordPress Administration / Frontend Editor request or not.
		 *
		 * Page Builders can set this to true to allow Media Library Organizer and its Addons to load its
		 * functionality.
		 *
		 * @since   2.5.2
		 *
		 * @param   bool    $is_admin_or_frontend_editor    Is WordPress Administration / Frontend Editor request.
		 * @param   array   $request                        Sanitized request data.
		 */
		$is_admin_or_frontend_editor = apply_filters( 'page_generator_pro_is_admin_or_frontend_editor', $is_admin_or_frontend_editor, $request );

		// Return filtered result.
		return $is_admin_or_frontend_editor;

	}

	/**
	 * Detects if the request is through the WP-CLI
	 *
	 * @since   2.5.2
	 *
	 * @return  bool    Is WP-CLI Request
	 */
	public function is_cli() {

		if ( ! defined( 'WP_CLI' ) ) {
			return false;
		}
		if ( ! WP_CLI ) {
			return false;
		}

		return true;

	}

	/**
	 * Detects if the request is through the WP CRON
	 *
	 * @since   2.5.2
	 *
	 * @return  bool    Is WP CRON Request
	 */
	public function is_cron() {

		if ( ! defined( 'DOING_CRON' ) ) {
			return false;
		}
		if ( ! DOING_CRON ) {
			return false;
		}

		return true;

	}

	/**
	 * Runs the upgrade routine once the plugin has loaded
	 *
	 * @since   1.1.7
	 */
	public function upgrade() {

		// Bail if we're not in the WordPress Admin.
		if ( ! is_admin() ) {
			return;
		}

		// Run upgrade routine.
		$this->get_class( 'install' )->upgrade();

	}

	/**
	 * Loads plugin textdomain
	 *
	 * @since   1.0.0
	 */
	public function load_language_files() {

		load_plugin_textdomain( 'page-generator-pro', false, $this->plugin->name . '/languages' );

	}

	/**
	 * Returns the given class
	 *
	 * @since   1.9.8
	 *
	 * @param   string $name   Class Name.
	 * @return  object          Class Object
	 */
	public function get_class( $name ) {

		// If the class hasn't been loaded, throw a WordPress die screen
		// to avoid a PHP fatal error.
		if ( ! isset( $this->classes->{ $name } ) ) {
			// Define the error.
			$error = new WP_Error(
				'page_generator_pro_get_class',
				sprintf(
					/* translators: %1$s: Plugin Name, %2$s: PHP class name */
					__( '%1$s: Error: Could not load Plugin class %2$s', 'page-generator-pro' ),
					$this->plugin->displayName, // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					$name
				)
			);

			// Depending on the request, return or display an error.
			// Admin UI.
			if ( is_admin() ) {
				wp_die(
					esc_html( $error->get_error_message() ),
					sprintf(
						/* translators: Plugin Name */
						esc_html__( '%s: Error', 'page-generator-pro' ),
						esc_html( $this->plugin->displayName ) // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					),
					array(
						'back_link' => true,
					)
				);
			}

			// CLI.
			return $error;
		}

		// Return the class object.
		return $this->classes->{ $name };

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since   1.1.6
	 *
	 * @return  object Class.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) { // @phpstan-ignore-line
			self::$instance = new self();
		}

		return self::$instance;

	}

}
