<?php
/**
 * Plugin Name:       Business Listing Importer
 * Plugin URI:        #
 * Description:       Imports business listings from a JSON file, linking them to UK locations.
 * Version:           1.0.0
 * Author:            Your Name
 * Author URI:        #
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       business-listing-importer
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'BUSINESS_LISTING_IMPORTER_VERSION', '1.0.0' );
define( 'BUSINESS_LISTING_IMPORTER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BUSINESS_LISTING_IMPORTER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BUSINESS_LISTING_IMPORTER_PLUGIN_FILE', __FILE__ );

/**
 * Load CPT and Taxonomy classes.
 */
require_once BUSINESS_LISTING_IMPORTER_PLUGIN_DIR . 'includes/class-business-listing-cpt.php';
require_once BUSINESS_LISTING_IMPORTER_PLUGIN_DIR . 'includes/class-business-tag-taxonomy.php';

/**
 * Initialize CPT and Taxonomy registration.
 */
add_action( 'init', array( 'Business_Listing_CPT', 'register' ) );
add_action( 'init', array( 'Business_Tag_Taxonomy', 'register' ) );

/**
 * Register WP-CLI command if WP_CLI is defined.
 */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once BUSINESS_LISTING_IMPORTER_PLUGIN_DIR . 'includes/class-business-importer.php';
	require_once BUSINESS_LISTING_IMPORTER_PLUGIN_DIR . 'cli/class-import-command.php';
	WP_CLI::add_command( 'business-import', 'Business_Listing_Import_Command' );
}

/**
 * Activation hook.
 */
function business_listing_importer_activate() {
	// Ensure CPT and Taxonomies are registered before flushing rewrite rules.
	Business_Listing_CPT::register();
	Business_Tag_Taxonomy::register();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'business_listing_importer_activate' );

/**
 * Deactivation hook.
 */
function business_listing_importer_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'business_listing_importer_deactivate' );