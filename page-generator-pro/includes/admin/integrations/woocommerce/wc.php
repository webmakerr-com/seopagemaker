<?php
/**
 * WooCommerce Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Page Generator Pro implementation of WC_Product_Data_Store_CPT, replacing
 * the read() function
 *
 * @since   3.3.9
 */
class Page_Generator_Pro_WC extends WC_Product_Data_Store_CPT {

	/**
	 * Method to read a product from the database.
	 *
	 * Used by Product Classes
	 *
	 * @param WC_Product $product Product object.
	 * @throws Exception If invalid product.
	 */
	public function read( &$product ) {

		$product->set_defaults();

		$post_object = get_post( $product->get_id() );

		// The below logic exists in WC_Product_Data_Store_CPT, but rightly throws an exception
		// as we're editing a Content Group Post Type with Product data, not a Product Post Type.
		// if ( ! $product->get_id() || ! $post_object || 'product' !== $post_object->post_type ) ...

		$product->set_props(
			array(
				'name'              => $post_object->post_title,
				'slug'              => $post_object->post_name,
				'date_created'      => 0 < $post_object->post_date_gmt ? wc_string_to_timestamp( $post_object->post_date_gmt ) : null,
				'date_modified'     => 0 < $post_object->post_modified_gmt ? wc_string_to_timestamp( $post_object->post_modified_gmt ) : null,
				'status'            => $post_object->post_status,
				'description'       => $post_object->post_content,
				'short_description' => $post_object->post_excerpt,
				'parent_id'         => $post_object->post_parent,
				'menu_order'        => $post_object->menu_order,
				'post_password'     => $post_object->post_password,
				'reviews_allowed'   => 'open' === $post_object->comment_status,
			)
		);

		$this->read_attributes( $product );
		$this->read_downloads( $product );
		$this->read_visibility( $product );
		$this->read_product_data( $product );
		$this->read_extra_data( $product );
		$product->set_object_read( true );

		do_action( 'woocommerce_product_read', $product->get_id() );

	}

}
