=== Business Listing Importer ===
Contributors:      Your Name
Tags:              import, business listing, custom post type, taxonomy, cli, json
Requires at least: 5.0
Tested up to:      6.5
Stable tag:        1.0.0
Requires PHP:      7.4
License:           GPL v2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Imports business listings from a JSON file into a custom post type, linking them to UK locations provided by the 'UK Location Hierarchy' plugin. Includes a WP-CLI command for running the import.

== Description ==

This plugin provides functionality to import business listings from a structured JSON file. It creates a 'Business Listing' custom post type and a 'Business Tag' custom taxonomy.

The importer links each business listing to a 'location' taxonomy term (provided by the companion 'UK Location Hierarchy' plugin) based on a `post_code_prefix` field in the JSON data.

It includes a WP-CLI command `wp business import run` to trigger the import process. The import is designed to be idempotent, meaning running it multiple times with the same data will not create duplicate listings (it checks based on name and postcode).

**Features:**

*   Registers 'Business Listing' Custom Post Type (`business_listing`).
*   Registers 'Business Tag' Custom Taxonomy (`business_tag`).
*   Imports data from a JSON file (default: `data/business_listings_seed.json`).
*   Saves business phone, website, and postcode as post meta.
*   Assigns business tags (creates new tags if they don't exist).
*   Links listings to the 'location' taxonomy based on `post_code_prefix` (requires 'UK Location Hierarchy' plugin).
*   Provides WP-CLI command `wp business import run [--file=<path>]` for importing.
*   Basic idempotency check based on listing name and postcode meta.

**Requires:**

*   WordPress 5.0 or higher.
*   PHP 7.4 or higher.
*   (Recommended) [UK Location Hierarchy](link-to-other-plugin-if-available) plugin for location linking.

== Installation ==

1.  Upload the `business-listing-importer` folder to the `/wp-content/plugins/` directory.
2.  Activate the plugin through the 'Plugins' menu in WordPress.
3.  (Optional but Recommended) Install and activate the 'UK Location Hierarchy' plugin and run its import command (`wp location import run`) first to populate location terms.
4.  Use the WP-CLI command to import listings: `wp business import run` or `wp business import run --file=/path/to/your/listings.json`.

== Frequently Asked Questions ==

= What is the format for the JSON file? =

The JSON file should be an array of objects. Each object represents a business listing and should ideally contain:
*   `name`: (Required) The name of the business.
*   `address_postcode`: (Required) The full postcode of the business. Used for idempotency check.
*   `post_code_prefix`: (Required) The postcode prefix (e.g., "PL1", "SW1A") used to link to the 'location' taxonomy term slug.
*   `phone`: (Optional) The phone number.
*   `website`: (Optional) The website URL.
*   `description`: (Optional) A description of the business.
*   `tags`: (Optional) An array of strings representing business tags.

Example:
`[{"name": "Example Biz", "address_postcode": "PL4 0DW", "phone": "01752123456", "tags": ["example", "service"], "post_code_prefix": "PL4", "description": "An example business."}]`

= How does the location linking work? =

It uses the `post_code_prefix` value from your JSON data (e.g., "SW1A") and looks for a term in the 'location' taxonomy with a matching *slug*. The 'UK Location Hierarchy' plugin is designed to create these terms with slugs matching the prefixes. If the 'location' taxonomy or the specific term slug doesn't exist, the listing won't be linked to a location.

= Can I run the import multiple times? =

Yes, the import checks if a listing with the same name and `_business_address_postcode` meta value already exists. If it finds one, it updates the existing listing instead of creating a new one.

== Screenshots ==

(No screenshots yet)

== Changelog ==

= 1.0.0 =
*   Initial release.

== Upgrade Notice ==

= 1.0.0 =
Initial release.