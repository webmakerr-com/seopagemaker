<?php
/**
 * Divi Den Pro Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Divi Den Pro as a Plugin integration:
 * - Copy / don't copy metadata to generated Pages, depending on if the integration is active
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 2.9.7
 */
class Page_Generator_Pro_DDPro extends Page_Generator_Pro_Integration {

	/**
	 * Holds the base object.
	 *
	 * @since   2.9.7
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   2.9.7
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Set Plugin.
		$this->plugin_folder_filename = 'ddpro/ddpro.php';

		add_action( 'page_generator_pro_generate_set_post_meta', array( $this, 'generate_set_post_meta' ), 10, 3 );

	}

	/**
	 * Copies multiple Divi Den Pro Meta Keys, which have the same Key Name, and their values,
	 * to the Generated Page,
	 *
	 * @since   2.9.7
	 *
	 * @param   int   $post_id        Generated Page ID.
	 * @param   int   $group_id       Group ID.
	 * @param   array $post_meta      Group Post Meta.
	 */
	public function generate_set_post_meta( $post_id, $group_id, $post_meta ) {

		// Bail if Divi Den Pro isn't active.
		if ( ! $this->is_active() ) {
			return;
		}

		// Iterate through Post Meta, building an array of Meta Keys and Values
		// related to Divi Den Pro.
		$divi_den_pro_meta = array();
		foreach ( $post_meta as $key => $values ) {
			// Skip if this isn't a Divi Den Pro Meta Key.
			if ( strpos( $key, 'ddp-css-' ) === false ) {
				continue;
			}

			// Get all Meta Values for this Key, as there'll be more than one,
			// each stored in the same Key Name.
			$divi_den_pro_meta[ $key ] = get_post_meta( $group_id, $key, false );
		}

		// If no Divi Den Pro Metadata was found, bail.
		if ( ! count( $divi_den_pro_meta ) ) {
			return;
		}

		// Iterate through each Divi Den Pro Metadata Key/Value Pair.
		foreach ( $divi_den_pro_meta as $key => $values ) {
			// Add each value using add_post_meta(), so multiple Meta Keys with the same name are stored
			// as required by Divi Den Pro.
			foreach ( $values as $value ) {
				add_post_meta( $post_id, $key, $value );
			}
		}

	}

}
