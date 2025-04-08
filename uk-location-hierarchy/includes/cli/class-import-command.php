<?php

if ( ! class_exists( 'WP_CLI_Command' ) ) {
    return;
}

/**
 * Manages the UK Location Hierarchy import.
 */
class UK_Location_Import_Command extends WP_CLI_Command {

    /**
     * Imports the location hierarchy from the bundled JSON file.
     *
     * Reads data from `wp-content/plugins/uk-location-hierarchy/data/location_hierarchy_seed.json`
     * and populates the 'location' taxonomy. The import is idempotent.
     *
     * ## OPTIONS
     *
     * [--verbose]
     * : Show detailed log messages during import.
     *
     * ## EXAMPLES
     *
     *     # Run the import
     *     wp location import
     *
     *     # Run the import with detailed output
     *     wp location import --verbose
     *
     */
    public function __invoke( $args, $assoc_args ) {
        $verbose = WP_CLI\Utils\get_flag_value( $assoc_args, 'verbose', false );

        WP_CLI::line( 'Starting UK location hierarchy import...' );

        // Ensure the importer class exists
        if ( ! class_exists('UK_Location_Importer') ) {
             WP_CLI::error( 'Importer class UK_Location_Importer not found. Ensure the plugin files are loaded correctly.' );
             return;
        }

        $importer = new UK_Location_Importer();
        $result = $importer->run_import();

        // Output summary results
        WP_CLI::line( "\n" . WP_CLI::colorize('%gImport Summary:%n') );
        WP_CLI::line( "- Terms Created: " . $result['created'] );
        WP_CLI::line( "- Terms Existing/Verified: " . $result['updated'] );
        WP_CLI::line( "- Items Skipped: " . $result['skipped'] );
        WP_CLI::line( "- Errors: " . $result['errors'] );

        // Output detailed logs if requested or if errors occurred
        if ( $verbose || $result['errors'] > 0 || $result['skipped'] > 0 ) {
            if ( ! empty( $result['details'] ) ) {
                WP_CLI::line( "\n" . WP_CLI::colorize('%gDetails:%n') );
                foreach( $result['details'] as $detail ) {
                    if ( stripos( $detail, 'error:' ) !== false ) {
                         WP_CLI::warning( $detail );
                    } elseif ( stripos( $detail, 'warning:' ) !== false ) {
                         WP_CLI::log( WP_CLI::colorize('%y' . $detail . '%n') ); // Yellow for warnings
                    } elseif ( stripos( $detail, 'skipped:' ) !== false ) {
                         WP_CLI::log( WP_CLI::colorize('%y' . $detail . '%n') ); // Yellow for skipped
                    } elseif ( $verbose ) {
                         WP_CLI::log( $detail ); // Normal log if verbose
                    }
                }
            }
        }


        if ( $result['errors'] > 0 ) {
            WP_CLI::error( 'Import completed with errors. Please check the details above.' );
        } else {
            WP_CLI::success( 'Import completed successfully.' );
        }
    }
}
?>