<?php

class UK_Location_Importer {

    private $taxonomy_slug;
    private $data_file_path;
    private $log = array(
        'created' => 0,
        'updated' => 0, // Using 'updated' to mean 'existing/verified'
        'skipped' => 0,
        'errors'  => 0,
        'details' => []
    );

    public function __construct() {
        $this->taxonomy_slug = UK_Location_Taxonomy::TAXONOMY_SLUG;
        $this->data_file_path = UK_LOC_HIERARCHY_DATA_FILE;
    }

    /**
     * Main function to run the import process.
     * @return array Status log.
     */
    public function run_import() {
        // Reset log for this run
        $this->log = array( 'created' => 0, 'updated' => 0, 'skipped' => 0, 'errors'  => 0, 'details' => [] );

        // 1. Check if taxonomy exists
        if ( ! taxonomy_exists( $this->taxonomy_slug ) ) {
            $this->log['errors']++;
            $this->log['details'][] = "Error: Taxonomy '{$this->taxonomy_slug}' is not registered.";
            return $this->log;
        }

        // 2. Read JSON file
        $json_data = $this->read_json_file();
        if ( $json_data === false ) {
            return $this->log; // Error logged in read_json_file
        }

        // 3. Validate basic structure (adapt based on actual JSON)
        // 3. Validate basic structure - Expecting an object (associative array) at the top level
        if ( ! is_array( $json_data ) || ! $this->is_assoc_array($json_data) ) {
             $this->log['errors']++;
             $this->log['details'][] = "Error: Invalid JSON structure - expected an object (key-value pairs) at the top level.";
             return $this->log;
        }
         if ( empty( $json_data ) ) {
             $this->log['skipped']++;
             $this->log['details'][] = "Warning: JSON data file is empty or contains no location data.";
             // Not necessarily an error, could be intentional
             return $this->log;
         }


        // 4. Start Recursive Import
        // Defer term counting for potentially better performance on large imports
        wp_defer_term_counting( true );

        // Assuming top level is an array of countries or similar top-level items
        // Iterate through the top-level keys (e.g., "England") and their values (nested data)
        foreach ( $json_data as $location_name => $location_children_data ) {
             $this->process_location_level( $location_name, $location_children_data, 0 ); // Pass name, children data, and parent ID 0
        }

        // Re-enable term counting
        wp_defer_term_counting( false );

        return $this->log;
    }

    /**
     * Reads and decodes the JSON data file.
     * @return array|false Decoded data or false on failure.
     */
    private function read_json_file() {
        if ( ! file_exists( $this->data_file_path ) ) {
            $this->log['errors']++;
            $this->log['details'][] = "Error: Data file not found at {$this->data_file_path}";
            return false;
        }

        $content = file_get_contents( $this->data_file_path );
        if ( $content === false ) {
            $this->log['errors']++;
            $this->log['details'][] = "Error: Could not read data file at {$this->data_file_path}";
            return false;
        }

        $data = json_decode( $content, true ); // true for associative array
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            $this->log['errors']++;
            $this->log['details'][] = "Error: Failed to decode JSON - " . json_last_error_msg();
            return false;
        }

        return $data;
    }

    /**
     * Recursively processes a level of the location hierarchy.
     *
     * @param string $location_name        The name of the current location (e.g., "England", "Kent", "CT1").
     * @param mixed  $location_children_data The children data (nested object/array or array of postcodes).
     * @param int    $parent_term_id       The term_id of the parent location. 0 for top-level.
     */
    private function process_location_level( $location_name, $location_children_data, $parent_term_id ) {
    
        $current_term_name = trim( (string) $location_name );
    
        if ( empty( $current_term_name ) ) {
            $this->log['skipped']++;
            $this->log['details'][] = "Skipped item with empty name under parent ID {$parent_term_id}.";
            return; // Skip if no identifiable name
        }

        // Generate a slug. Use a more robust slug generation if needed.
        $term_slug = sanitize_title( $current_term_name );
        // If the slug is empty after sanitization (e.g., name was just symbols), create a fallback or skip
        if ( empty( $term_slug ) ) {
             $term_slug = 'location-' . md5($current_term_name); // Example fallback
             $this->log['details'][] = "Generated fallback slug '{$term_slug}' for term '{$current_term_name}'.";
        }


        // --- Check if term exists ---
        // We need to check for the term *with the correct parent* to ensure hierarchy.
        $existing_term_id = $this->get_term_id_by_slug_and_parent( $term_slug, $parent_term_id );

        $current_term_id = null;

        if ( $existing_term_id ) {
            $current_term_id = $existing_term_id;
            $this->log['updated']++; // Term exists with correct parent
            $this->log['details'][] = "Existing: '{$current_term_name}' (ID: {$current_term_id}, Parent: {$parent_term_id})";
            // Optional: Update term meta or description here if needed using wp_update_term()
        } else {
            // Term does not exist with this parent, create it
            $term_args = array(
                'slug'   => $term_slug,
                'parent' => $parent_term_id,
                // 'description' => $location_data['description'] ?? '', // Add if available in JSON
            );

            $insert_result = wp_insert_term(
                $current_term_name,      // The term name
                $this->taxonomy_slug,    // The taxonomy slug
                $term_args               // Arguments including parent and slug
            );

            if ( is_wp_error( $insert_result ) ) {
                $error_code = $insert_result->get_error_code();
                // Check if it's a duplicate slug error, which might happen if our parent check wasn't sufficient
                // or if the same slug exists under a *different* parent (which is allowed by WP but maybe not desired here)
                if ( $error_code === 'term_exists' ) {
                     $existing_term_data = $insert_result->get_error_data();
                     $existing_id = $existing_term_data['term_id'] ?? null;
                     $existing_term_object = get_term($existing_id, $this->taxonomy_slug);

                     // Log detailed error about potential conflict
                     $this->log['errors']++;
                     $this->log['details'][] = "Error creating term '{$current_term_name}' (Slug: {$term_slug}): Term or slug likely exists, possibly with a different parent. Existing Term Parent: " . ($existing_term_object ? $existing_term_object->parent : 'N/A') . ". Expected Parent: {$parent_term_id}. Error: " . $insert_result->get_error_message();
                     // Decide how to handle: skip, try alternative slug, etc. Skipping is safest.
                     return; // Stop processing this branch
                } else {
                    // Other insertion error
                    $this->log['errors']++;
                    $this->log['details'][] = "Error creating term '{$current_term_name}' (Slug: {$term_slug}): " . $insert_result->get_error_message();
                    return; // Stop processing this branch on error
                }
            } else {
                $current_term_id = $insert_result['term_id'];
                $this->log['created']++;
                $this->log['details'][] = "Created: '{$current_term_name}' (ID: {$current_term_id}, Parent: {$parent_term_id})";
            }
        }

        // --- Process Children Based on Structure ---
        if ( $current_term_id ) { // Only proceed if the parent term was created/found successfully
    
            // Case 1: Children data is an object/associative array (nested locations)
            if ( is_array( $location_children_data ) && $this->is_assoc_array($location_children_data) ) {
                foreach ( $location_children_data as $child_name => $child_data ) {
                    $this->process_location_level( $child_name, $child_data, $current_term_id );
                }
            }
            // Case 2: Children data is a simple array (list of postcode prefixes)
            elseif ( is_array( $location_children_data ) && ! $this->is_assoc_array($location_children_data) ) {
                 foreach ( $location_children_data as $postcode_prefix ) {
                     if ( is_string( $postcode_prefix ) && ! empty( trim( $postcode_prefix ) ) ) {
                         $this->insert_postcode_term( trim( $postcode_prefix ), $current_term_id );
                     } else {
                         $this->log['skipped']++;
                         $this->log['details'][] = "Skipped invalid postcode prefix under term '{$current_term_name}' (ID: {$current_term_id}). Data: " . print_r($postcode_prefix, true);
                     }
                 }
            }
            // Case 3: Unexpected data type for children (log a warning)
            elseif ( ! empty( $location_children_data ) ) {
                 $this->log['skipped']++;
                 $this->log['details'][] = "Warning: Unexpected data type for children of '{$current_term_name}' (ID: {$current_term_id}). Expected object or array. Data: " . print_r($location_children_data, true);
            }
            // Case 4: No children data (leaf node before postcodes, or empty object/array) - Do nothing further.
        }
    }
    
    /**
     * Helper function to insert or find a postcode term.
     *
     * @param string $postcode_prefix The postcode prefix string.
     * @param int    $parent_term_id  The parent term ID.
     */
    private function insert_postcode_term( $postcode_prefix, $parent_term_id ) {
        $term_name = $postcode_prefix; // Use prefix directly as name
        $term_slug = sanitize_title( $term_name );
    
        if ( empty( $term_slug ) ) {
             $term_slug = 'postcode-' . md5($term_name); // Fallback slug
             $this->log['details'][] = "Generated fallback slug '{$term_slug}' for postcode '{$term_name}'.";
        }
    
        // Check if term exists with this specific parent
        $existing_term_id = $this->get_term_id_by_slug_and_parent( $term_slug, $parent_term_id );
    
        if ( $existing_term_id ) {
            $this->log['updated']++;
            $this->log['details'][] = "Existing Postcode: '{$term_name}' (ID: {$existing_term_id}, Parent: {$parent_term_id})";
        } else {
            $term_args = array(
                'slug'   => $term_slug,
                'parent' => $parent_term_id,
            );
            $insert_result = wp_insert_term( $term_name, $this->taxonomy_slug, $term_args );
    
            if ( is_wp_error( $insert_result ) ) {
                 $error_code = $insert_result->get_error_code();
                 if ( $error_code === 'term_exists' ) {
                      $existing_term_data = $insert_result->get_error_data();
                      $existing_id = $existing_term_data['term_id'] ?? null;
                      $existing_term_object = get_term($existing_id, $this->taxonomy_slug);
                      $this->log['errors']++;
                      $this->log['details'][] = "Error creating postcode '{$term_name}' (Slug: {$term_slug}): Term or slug likely exists with a different parent. Existing Parent: " . ($existing_term_object ? $existing_term_object->parent : 'N/A') . ". Expected Parent: {$parent_term_id}. Error: " . $insert_result->get_error_message();
                 } else {
                     $this->log['errors']++;
                     $this->log['details'][] = "Error creating postcode '{$term_name}' (Slug: {$term_slug}): " . $insert_result->get_error_message();
                 }
            } else {
                $new_term_id = $insert_result['term_id'];
                $this->log['created']++;
                $this->log['details'][] = "Created Postcode: '{$term_name}' (ID: {$new_term_id}, Parent: {$parent_term_id})";
            }
        }
    }

    /**
     * Helper function to find a term ID by slug AND parent ID.
     * term_exists() checks slug globally within the taxonomy.
     * get_terms() allows filtering by parent.
     *
     * @param string $term_slug The slug of the term to find.
     * @param int    $parent_term_id The required parent term ID.
     * @return int|null The term ID if found, null otherwise.
     */
    private function get_term_id_by_slug_and_parent( $term_slug, $parent_term_id ) {
        $terms = get_terms( array(
            'taxonomy'   => $this->taxonomy_slug,
            'slug'       => $term_slug,
            'parent'     => $parent_term_id,
            'hide_empty' => false, // Important: find terms even if they have no posts
            'number'     => 1,     // We only need one match
            'fields'     => 'ids', // Only fetch the ID
        ) );

        if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
            return (int) $terms[0];
        }

        return null;
    }
    
    /**
     * Helper function to check if an array is associative (like an object).
     * PHP arrays can be numerically indexed or string-keyed (associative).
     * json_decode($json, true) creates associative arrays for JSON objects.
     *
     * @param array $arr The array to check.
     * @return bool True if the array is associative, false otherwise.
     */
    private function is_assoc_array(array $arr): bool {
        if (array() === $arr) return false; // Empty array is not associative for our purpose
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}
?>