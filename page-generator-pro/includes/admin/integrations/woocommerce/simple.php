<?php
/**
 * WooCommerce Simple Product Type.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Page Generator Pro implementation of Simple Product Type (WC_Product_Data_Store_CPT)
 * the read() function
 *
 * @since   3.3.9
 */
class Page_Generator_Pro_WC_Product_Data_Store_CPT extends WC_Product_Data_Store_CPT {

	/**
	 * Method to read a product from the database.
	 *
	 * @param WC_Product $product Product object.
	 * @throws Exception If invalid product.
	 */
	public function read( &$product ) {

		$read = new Page_Generator_Pro_WC();
		$read->read( $product );

	}

}
