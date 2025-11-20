<?php
/**
 * WooCommerce Variable Product Type.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Page Generator Pro implementation of Variable Product Type (WC_Product_Variable_Data_Store_CPT)
 * the read() function
 *
 * @since   3.3.9
 */
class Page_Generator_Pro_WC_Product_Variable_Data_Store_CPT extends WC_Product_Variable_Data_Store_CPT implements WC_Object_Data_Store_Interface, WC_Product_Variable_Data_Store_Interface {

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
