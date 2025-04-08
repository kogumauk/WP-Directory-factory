<?php
/**
 * Plugin Name: UK Location Hierarchy Importer
 * Description: Imports UK location data into a custom taxonomy.
 * Version: 1.0.0
 * Author: AI Assistant
 * Text Domain: uk-location-hierarchy
 */

// Prevent direct access
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Define constants
define( 'UK_LOC_HIERARCHY_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'UK_LOC_HIERARCHY_DATA_FILE', UK_LOC_HIERARCHY_PLUGIN_DIR . 'data/location_hierarchy_seed.json' );

// Include necessary files
require_once UK_LOC_HIERARCHY_PLUGIN_DIR . 'includes/class-location-taxonomy.php';
require_once UK_LOC_HIERARCHY_PLUGIN_DIR . 'includes/class-location-importer.php';

// Register Taxonomy on init
add_action( 'init', array( 'UK_Location_Taxonomy', 'register' ) );

// --- WP-CLI Integration ---
if ( defined( 'WP_CLI' ) && WP_CLI ) {
    require_once UK_LOC_HIERARCHY_PLUGIN_DIR . 'includes/cli/class-import-command.php';
    WP_CLI::add_command( 'location import', 'UK_Location_Import_Command' );
}

// --- Admin UI Trigger (Placeholder - Not implemented in this phase) ---
// Consider adding an admin page/button later if WP-CLI is not sufficient.

?>