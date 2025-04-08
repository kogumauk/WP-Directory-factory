<?php
/**
 * Registers the Business Tag Custom Taxonomy.
 *
 * @package Business_Listing_Importer
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class Business_Tag_Taxonomy
 */
class Business_Tag_Taxonomy {

	/**
	 * Register the Custom Taxonomy.
	 */
	public static function register() {
		$labels = array(
			'name'                       => _x( 'Business Tags', 'taxonomy general name', 'business-listing-importer' ),
			'singular_name'              => _x( 'Business Tag', 'taxonomy singular name', 'business-listing-importer' ),
			'search_items'               => __( 'Search Business Tags', 'business-listing-importer' ),
			'popular_items'              => __( 'Popular Business Tags', 'business-listing-importer' ),
			'all_items'                  => __( 'All Business Tags', 'business-listing-importer' ),
			'parent_item'                => null, // Tags are non-hierarchical.
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Business Tag', 'business-listing-importer' ),
			'update_item'                => __( 'Update Business Tag', 'business-listing-importer' ),
			'add_new_item'               => __( 'Add New Business Tag', 'business-listing-importer' ),
			'new_item_name'              => __( 'New Business Tag Name', 'business-listing-importer' ),
			'separate_items_with_commas' => __( 'Separate tags with commas', 'business-listing-importer' ),
			'add_or_remove_items'        => __( 'Add or remove business tags', 'business-listing-importer' ),
			'choose_from_most_used'      => __( 'Choose from the most used business tags', 'business-listing-importer' ),
			'not_found'                  => __( 'No business tags found.', 'business-listing-importer' ),
			'menu_name'                  => __( 'Business Tags', 'business-listing-importer' ),
			'back_to_items'              => __( '← Back to Business Tags', 'business-listing-importer' ),
		);

		$args = array(
			'hierarchical'          => false, // Like post tags.
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'business-tag' ),
			'show_in_rest'          => true, // Enable Gutenberg support.
		);

		register_taxonomy( 'business_tag', array( 'business_listing' ), $args ); // Associate with 'business_listing' CPT.
	}
}