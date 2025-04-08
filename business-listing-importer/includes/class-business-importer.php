<?php
/**
 * Handles importing business listings from a JSON file.
 *
 * @package Business_Listing_Importer
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class Business_Importer
 */
class Business_Importer {

	/**
	 * Path to the JSON data file.
	 *
	 * @var string
	 */
	private $file_path;

	/**
	 * Whether to output verbose logging.
	 *
	 * @var bool
	 */
	private $verbose = false;

	/**
	 * Constructor.
	 *
	 * @param string $file_path Path to the JSON file. Defaults to the seed file.
	 * @param bool   $verbose   Whether to enable verbose logging.
	 */
	public function __construct( $file_path = '', $verbose = false ) {
		if ( empty( $file_path ) ) {
			$this->file_path = BUSINESS_LISTING_IMPORTER_PLUGIN_DIR . 'data/business_listings_seed.json';
		} else {
			$this->file_path = $file_path;
		}
		$this->verbose = (bool) $verbose;
	}

	/**
	 * Run the import process.
	 */
	public function import() {
		$data = $this->read_json_data();

		if ( false === $data ) {
			WP_CLI::error( __( 'Failed to read or decode JSON file.', 'business-listing-importer' ) );
			return;
		}

		if ( empty( $data ) ) {
			WP_CLI::warning( __( 'No business listings found in the JSON file.', 'business-listing-importer' ) );
			return;
		}

		$imported_count = 0;
		$updated_count  = 0;
		$skipped_count  = 0;

		// Iterate through location groups (top-level array).
		foreach ( $data as $location_group ) {
			// Extract postcode prefix from the location group.
			$postcode_prefix = $location_group['post_code_prefix'] ?? null;

			if ( empty( $postcode_prefix ) ) {
				WP_CLI::warning( sprintf( __( 'Skipping location group due to missing or empty post_code_prefix: %s', 'business-listing-importer' ), wp_json_encode( $location_group ) ) );
				// Count skipped based on potential listings inside, or just skip the group. For simplicity, just log and continue.
				continue;
			}

			// Check if listings exist and is an array.
			if ( ! isset( $location_group['listings'] ) || ! is_array( $location_group['listings'] ) ) {
				WP_CLI::warning( sprintf( __( 'Skipping location group with prefix %s due to missing or invalid "listings" array.', 'business-listing-importer' ), $postcode_prefix ) );
				continue;
			}

			// Iterate through the listings within this location group.
			foreach ( $location_group['listings'] as $listing_data ) {
				// Check for required data within the individual listing.
				// Note: Adjust 'address_postcode' if the actual field name is different inside 'listings'. Assuming 'name' is required.
				if ( ! isset( $listing_data['name'] ) /* Add other required fields like address if necessary, e.g., || ! isset( $listing_data['address'] ) */ ) {
					WP_CLI::warning( sprintf( __( 'Skipping listing in group %s due to missing required data (e.g., name): %s', 'business-listing-importer' ), $postcode_prefix, wp_json_encode( $listing_data ) ) );
					$skipped_count++;
					continue;
				}

				// Pass the extracted postcode_prefix along with the listing data.
				$result = $this->create_or_update_listing( $listing_data, $postcode_prefix );

				if ( 'imported' === $result ) {
					$imported_count++;
				} elseif ( 'updated' === $result ) {
					$updated_count++;
				} else {
					$skipped_count++; // Treat errors as skipped for counting purposes.
				}
			}
		}

		WP_CLI::success( sprintf( __( 'Import complete. Imported: %d, Updated: %d, Skipped: %d', 'business-listing-importer' ), $imported_count, $updated_count, $skipped_count ) );
	}

	/**
	 * Reads and decodes the JSON data file.
	 *
	 * @return array|false Array of listing data or false on failure.
	 */
	private function read_json_data() {
		if ( ! file_exists( $this->file_path ) ) {
			WP_CLI::error( sprintf( __( 'JSON file not found at path: %s', 'business-listing-importer' ), $this->file_path ) );
			return false;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$json_content = file_get_contents( $this->file_path );
		if ( false === $json_content ) {
			WP_CLI::error( sprintf( __( 'Could not read JSON file: %s', 'business-listing-importer' ), $this->file_path ) );
			return false;
		}

		$data = json_decode( $json_content, true );
		if ( null === $data && json_last_error() !== JSON_ERROR_NONE ) {
			WP_CLI::error( sprintf( __( 'Error decoding JSON: %s', 'business-listing-importer' ), json_last_error_msg() ) );
			return false;
		}

		return $data;
	}

	/**
	 * Finds an existing business listing based on title and postcode meta.
	 *
	 * @param array $listing_data Data for the listing.
	 * @return int|null Post ID if found, null otherwise.
	 */
	private function find_existing_listing( $listing_data ) {
		$args = array(
			'post_type'      => 'business_listing',
			'post_status'    => 'any', // Check all statuses.
			'posts_per_page' => 1,
			'title'          => sanitize_text_field( $listing_data['name'] ),
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'     => '_business_address_postcode',
					'value'   => sanitize_text_field( $listing_data['address_postcode'] ),
					'compare' => '=',
				),
			),
			'fields'         => 'ids', // Only get the ID.
		);

		$query = new WP_Query( $args );

		if ( $query->have_posts() ) {
			return $query->posts[0];
		}

		return null;
	}

	/**
	 * Creates or updates a business listing post.
	 *
	 * @param array  $listing_data    Data for the individual listing.
	 * @param string $postcode_prefix The postcode prefix from the parent location group.
	 * @return string Status: 'imported', 'updated', or 'skipped'.
	 */
	private function create_or_update_listing( $listing_data, $postcode_prefix ) {
		$existing_post_id = $this->find_existing_listing( $listing_data );
		$post_status      = 'publish'; // Default to published.

		$post_data = array(
			'post_title'   => sanitize_text_field( $listing_data['name'] ),
			'post_content' => isset( $listing_data['description'] ) ? wp_kses_post( $listing_data['description'] ) : '',
			'post_status'  => $post_status,
			'post_type'    => 'business_listing',
		);

		if ( $existing_post_id ) {
			// Update existing post.
			$post_data['ID'] = $existing_post_id;
			$post_id         = wp_update_post( $post_data );
			$action          = 'updated';
		} else {
			// Insert new post.
			$post_id = wp_insert_post( $post_data );
			$action  = 'imported';
		}

		$listing_name = $listing_data['name'] ?? '[Unknown Name]';
		if ( is_wp_error( $post_id ) ) {
			WP_CLI::warning( sprintf( __( 'Failed to %s listing "%s" in group %s: %s', 'business-listing-importer' ), $action, $listing_name, $postcode_prefix, $post_id->get_error_message() ) );
			return 'skipped';
		}

		if ( 0 === $post_id ) {
			// wp_update_post returns 0 if nothing changed, treat as updated for consistency.
			if ( 'updated' === $action ) {
				$post_id = $existing_post_id; // Need the ID for meta/terms.
			} else {
				WP_CLI::warning( sprintf( __( 'Failed to %s listing "%s" in group %s (unknown error).', 'business-listing-importer' ), $action, $listing_name, $postcode_prefix ) );
				return 'skipped';
			}
		}


		// Save meta data.
		$this->save_listing_meta( $post_id, $listing_data );

		// Set terms (Location and Business Tags).
		$this->set_listing_terms( $post_id, $listing_data, $postcode_prefix );

		if ( $this->verbose ) {
			WP_CLI::log( sprintf( __( '%s listing: %s (ID: %d) in group %s', 'business-listing-importer' ), ucfirst( $action ), $listing_name, $post_id, $postcode_prefix ) );
		}
		return $action;
	}

	/**
	 * Saves custom meta fields for the listing.
	 *
	 * @param int   $post_id      The post ID.
	 * @param array $listing_data Data for the individual listing.
	 */
	private function save_listing_meta( $post_id, $listing_data ) {
	 // --- Core Meta ---
	 // Store postcode from the listing data if available (e.g., 'address_postcode' or similar). Adjust key as needed.
	 if ( isset( $listing_data['address_postcode'] ) ) {
	 	update_post_meta( $post_id, '_business_address_postcode', sanitize_text_field( $listing_data['address_postcode'] ) );
	 }
	 // Store the full address if available. Adjust key as needed.
	 if ( isset( $listing_data['address'] ) ) {
	 	// Consider if 'address' is a string or an object needing specific handling.
	 	if ( is_array( $listing_data['address'] ) ) {
	 		// Example: Handle structured address - adjust keys as needed.
	 		// update_post_meta( $post_id, '_business_address_street', sanitize_text_field( $listing_data['address']['street'] ?? '' ) );
	 		// update_post_meta( $post_id, '_business_address_city', sanitize_text_field( $listing_data['address']['city'] ?? '' ) );
	 		// Or store the whole thing as JSON if appropriate.
	 		update_post_meta( $post_id, '_business_address_structured', wp_json_encode( $listing_data['address'] ) );
	 	} else {
	 		update_post_meta( $post_id, '_business_address_full', sanitize_textarea_field( $listing_data['address'] ) );
	 	}
	 }
	 if ( isset( $listing_data['phone'] ) ) {
	 	update_post_meta( $post_id, '_business_phone', sanitize_text_field( $listing_data['phone'] ) );
	 }
	 if ( isset( $listing_data['website'] ) ) {
	 	update_post_meta( $post_id, '_business_website', esc_url_raw( $listing_data['website'] ) );
	 }

	 // --- Additional Meta Examples (adjust keys and sanitization based on actual data) ---
	 if ( isset( $listing_data['coordinates'] ) && is_array( $listing_data['coordinates'] ) ) {
	 	// Example: Assuming coordinates is { "latitude": ..., "longitude": ... }
	 	update_post_meta( $post_id, '_business_latitude', sanitize_text_field( $listing_data['coordinates']['latitude'] ?? '' ) );
	 	update_post_meta( $post_id, '_business_longitude', sanitize_text_field( $listing_data['coordinates']['longitude'] ?? '' ) );
	 }
	 if ( isset( $listing_data['business_hours'] ) ) {
	 	// Example: Store as JSON or break down if structure is consistent.
	 	update_post_meta( $post_id, '_business_hours', wp_json_encode( $listing_data['business_hours'] ) );
	 }
	 if ( isset( $listing_data['menu'] ) ) {
	 	// Example: Store menu URL or JSON representation.
	 	if ( is_array( $listing_data['menu'] ) ) {
	 		update_post_meta( $post_id, '_business_menu_data', wp_json_encode( $listing_data['menu'] ) );
	 	} else {
	 		update_post_meta( $post_id, '_business_menu_url', esc_url_raw( $listing_data['menu'] ) );
	 	}
	 }
	 // Add other meta fields as needed, ensuring appropriate sanitization (sanitize_text_field, sanitize_email, esc_url_raw, wp_kses_post, wp_json_encode etc.).
	}

	/**
	 * Sets the location and business tag terms for the listing.
	 *
	 * @param int    $post_id         The post ID.
	 * @param array  $listing_data    Data for the individual listing (used for tags).
	 * @param string $postcode_prefix The postcode prefix from the parent location group.
	 */
	private function set_listing_terms( $post_id, $listing_data, $postcode_prefix ) {
		$location_term_id = $this->get_location_term( $postcode_prefix );
		$term_ids_to_set  = array();

		// --- Location Term ---
		if ( $location_term_id ) {
			$term_ids_to_set['location'] = array( $location_term_id );
			if ( $this->verbose ) {
				WP_CLI::log( sprintf( ' - Found location term ID %d for prefix %s', $location_term_id, $postcode_prefix ) );
			}
		} else {
			WP_CLI::warning( sprintf( __( ' - Could not find location term for postcode prefix: %s', 'business-listing-importer' ), $postcode_prefix ) );
			// Decide if you want to clear existing location terms if none is found now.
			// wp_set_post_terms( $post_id, array(), 'location', false );
		}

		// --- Business Tags ---
		if ( ! empty( $listing_data['tags'] ) && is_array( $listing_data['tags'] ) ) {
			$tag_names = array_map( 'sanitize_text_field', $listing_data['tags'] );
			// wp_set_post_terms will create tags that don't exist.
			$term_ids_to_set['business_tag'] = $tag_names;
			if ( $this->verbose ) {
				WP_CLI::log( sprintf( ' - Setting business tags: %s', implode( ', ', $tag_names ) ) );
			}
		} else {
			// Clear existing tags if none are provided in the import data.
			$term_ids_to_set['business_tag'] = array();
			if ( $this->verbose ) {
				WP_CLI::log( ' - No business tags provided or clearing existing tags.' );
			}
		}

		// Set terms for both taxonomies.
		foreach ( $term_ids_to_set as $taxonomy => $terms ) {
			$result = wp_set_post_terms( $post_id, $terms, $taxonomy, false ); // false = replace existing terms.
			if ( is_wp_error( $result ) ) {
				WP_CLI::warning( sprintf( __( ' - Failed to set %s terms for post ID %d: %s', 'business-listing-importer' ), $taxonomy, $post_id, $result->get_error_message() ) );
			} elseif ( empty( $result ) && ! empty( $terms ) ) {
				// Sometimes returns empty array on success when setting by name, check if terms were expected.
				WP_CLI::warning( sprintf( __( ' - Setting %s terms for post ID %d might have failed (empty result).', 'business-listing-importer' ), $taxonomy, $post_id ) );
			}
		}
	}

	/**
	 * Gets the term ID for the 'location' taxonomy based on the postcode prefix (slug).
	 * Assumes the 'uk-location-hierarchy' plugin created terms with slugs matching postcode prefixes.
	 *
	 * @param string $postcode_prefix The postcode prefix (e.g., 'PL1', 'SW1A').
	 * @return int|null Term ID if found, null otherwise.
	 */
	private function get_location_term( $postcode_prefix ) {
		if ( empty( $postcode_prefix ) ) {
			return null;
		}

		// Sanitize the prefix to match potential slug format.
		$slug = sanitize_title( $postcode_prefix );

		// Check if the 'location' taxonomy exists.
		if ( ! taxonomy_exists( 'location' ) ) {
			WP_CLI::warning( __( "Taxonomy 'location' does not exist. Is the 'uk-location-hierarchy' plugin active?", 'business-listing-importer' ) );
			return null;
		}

		$term = get_term_by( 'slug', $slug, 'location' );

		if ( $term && ! is_wp_error( $term ) ) {
			return $term->term_id;
		}

		return null;
	}
}