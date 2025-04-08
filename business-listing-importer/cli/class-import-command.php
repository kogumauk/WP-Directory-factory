<?php
/**
 * WP-CLI command for importing business listings.
 *
 * @package Business_Listing_Importer
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Ensure WP_CLI class exists.
if ( ! class_exists( 'WP_CLI_Command' ) ) {
	return;
}

/**
 * Imports business listings from a specified JSON file.
 */
class Business_Listing_Import_Command extends WP_CLI_Command {

	/**
	 * Imports business listings from the bundled JSON file or a specified file.
	 *
	 * ## OPTIONS
	 *
	 * [--file=<file>]
	 * : Path to the JSON file containing business listings. Defaults to the plugin's seed file.
	 *
	 * [--verbose]
	 * : Show detailed log messages during import.
	 *
	 * ## EXAMPLES
	 *
	 *     # Import using the default seed file
	 *     wp business-import
	 *
	 *     # Import using a specific file with verbose output
	 *     wp business-import --file=/path/to/your/listings.json --verbose
	 *
	 * @when after_wp_load
	 *
	 * @param array $args       Positional arguments (not used in this command).
	 * @param array $assoc_args Associative arguments (options like --file).
	 */
	public function __invoke( $args, $assoc_args ) {
		$file_path = WP_CLI\Utils\get_flag_value( $assoc_args, 'file', '' );
		$verbose   = WP_CLI\Utils\get_flag_value( $assoc_args, 'verbose', false );

		if ( empty( $file_path ) ) {
			$file_path = BUSINESS_LISTING_IMPORTER_PLUGIN_DIR . 'data/business_listings_seed.json';
			// Use line for default messages, log for verbose details if needed later
			WP_CLI::line( sprintf( __( 'No file specified, using default seed file: %s', 'business-listing-importer' ), $file_path ) );
		} else {
			// Resolve relative paths if needed, though absolute is safer.
			if ( ! file_exists( $file_path ) ) {
				WP_CLI::error( sprintf( __( 'Specified file not found: %s', 'business-listing-importer' ), $file_path ) );
				return;
			}
			WP_CLI::line( sprintf( __( 'Using specified file: %s', 'business-listing-importer' ), $file_path ) );
		}

		// Check if the importer class exists (it should have been loaded by the main plugin file).
		if ( ! class_exists( 'Business_Importer' ) ) {
			WP_CLI::error( __( 'Business_Importer class not found. Is the plugin active and loaded correctly?', 'business-listing-importer' ) );
			return;
		}

		WP_CLI::line( __( 'Starting business listing import...', 'business-listing-importer' ) );

		$importer = new Business_Importer( $file_path, $verbose );
		$importer->import();

		// The import() method in Business_Importer handles success/error messages.
	}
}