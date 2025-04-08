<?php

class UK_Location_Taxonomy {

    const TAXONOMY_SLUG = 'location';

    public static function register() {
        $labels = array(
            'name'              => _x( 'Locations', 'taxonomy general name', 'uk-location-hierarchy' ),
            'singular_name'     => _x( 'Location', 'taxonomy singular name', 'uk-location-hierarchy' ),
            'search_items'      => __( 'Search Locations', 'uk-location-hierarchy' ),
            'all_items'         => __( 'All Locations', 'uk-location-hierarchy' ),
            'parent_item'       => __( 'Parent Location', 'uk-location-hierarchy' ),
            'parent_item_colon' => __( 'Parent Location:', 'uk-location-hierarchy' ),
            'edit_item'         => __( 'Edit Location', 'uk-location-hierarchy' ),
            'update_item'       => __( 'Update Location', 'uk-location-hierarchy' ),
            'add_new_item'      => __( 'Add New Location', 'uk-location-hierarchy' ),
            'new_item_name'     => __( 'New Location Name', 'uk-location-hierarchy' ),
            'menu_name'         => __( 'Locations', 'uk-location-hierarchy' ),
            'view_item'         => __( 'View Location', 'uk-location-hierarchy' ),
            'popular_items'              => __( 'Popular Locations', 'uk-location-hierarchy' ),
            'separate_items_with_commas' => __( 'Separate locations with commas', 'uk-location-hierarchy' ),
            'add_or_remove_items'        => __( 'Add or remove locations', 'uk-location-hierarchy' ),
            'choose_from_most_used'      => __( 'Choose from the most used locations', 'uk-location-hierarchy' ),
            'not_found'                  => __( 'No locations found.', 'uk-location-hierarchy' ),
            'no_terms'                   => __( 'No locations', 'uk-location-hierarchy' ),
            'items_list_navigation'      => __( 'Locations list navigation', 'uk-location-hierarchy' ),
            'items_list'                 => __( 'Locations list', 'uk-location-hierarchy' ),
            'back_to_items'              => __( '&larr; Back to Locations', 'uk-location-hierarchy' ),
        );

        $args = array(
            'labels'            => $labels,
            'hierarchical'      => true,
            'public'            => true,
            'show_ui'           => true,
            'show_admin_column' => true, // Show in post type list table
            'show_in_nav_menus' => true,
            'show_tagcloud'     => false,
            'show_in_rest'      => true, // Enable for Gutenberg / REST API
            'query_var'         => true,
            'rewrite'           => array( 'slug' => self::TAXONOMY_SLUG ),
        );

        // Associate with 'business_listing' CPT
        register_taxonomy( self::TAXONOMY_SLUG, array( 'business_listing' ), $args );
    }
}
?>