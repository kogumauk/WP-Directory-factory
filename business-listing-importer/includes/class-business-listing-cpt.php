<?php
/**
 * Registers the Business Listing Custom Post Type.
 *
 * @package Business_Listing_Importer
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class Business_Listing_CPT
 */
class Business_Listing_CPT {

	/**
	 * Register the Custom Post Type.
	 */
	public static function register() {
		$labels = array(
			'name'                  => _x( 'Business Listings', 'Post type general name', 'business-listing-importer' ),
			'singular_name'         => _x( 'Business Listing', 'Post type singular name', 'business-listing-importer' ),
			'menu_name'             => _x( 'Business Listings', 'Admin Menu text', 'business-listing-importer' ),
			'name_admin_bar'        => _x( 'Business Listing', 'Add New on Toolbar', 'business-listing-importer' ),
			'add_new'               => __( 'Add New', 'business-listing-importer' ),
			'add_new_item'          => __( 'Add New Business Listing', 'business-listing-importer' ),
			'new_item'              => __( 'New Business Listing', 'business-listing-importer' ),
			'edit_item'             => __( 'Edit Business Listing', 'business-listing-importer' ),
			'view_item'             => __( 'View Business Listing', 'business-listing-importer' ),
			'all_items'             => __( 'All Business Listings', 'business-listing-importer' ),
			'search_items'          => __( 'Search Business Listings', 'business-listing-importer' ),
			'parent_item_colon'     => __( 'Parent Business Listings:', 'business-listing-importer' ),
			'not_found'             => __( 'No business listings found.', 'business-listing-importer' ),
			'not_found_in_trash'    => __( 'No business listings found in Trash.', 'business-listing-importer' ),
			'featured_image'        => _x( 'Business Listing Logo', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'business-listing-importer' ),
			'set_featured_image'    => _x( 'Set logo', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'business-listing-importer' ),
			'remove_featured_image' => _x( 'Remove logo', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'business-listing-importer' ),
			'use_featured_image'    => _x( 'Use as logo', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'business-listing-importer' ),
			'archives'              => _x( 'Business Listing archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'business-listing-importer' ),
			'insert_into_item'      => _x( 'Insert into business listing', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'business-listing-importer' ),
			'uploaded_to_this_item' => _x( 'Uploaded to this business listing', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'business-listing-importer' ),
			'filter_items_list'     => _x( 'Filter business listings list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'business-listing-importer' ),
			'items_list_navigation' => _x( 'Business listings list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'business-listing-importer' ),
			'items_list'            => _x( 'Business listings list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'business-listing-importer' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'business-listing' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
			'show_in_rest'       => true, // Enable Gutenberg editor support.
			'taxonomies'         => array( 'business_tag', 'location' ), // Add taxonomies here.
		);

		register_post_type( 'business_listing', $args );
	}
}