=== UK Location Hierarchy Importer ===
Contributors:      AI Assistant
Tags:              location, hierarchy, import, taxonomy, uk
Requires at least: 5.0
Tested up to:      6.5
Stable tag:        1.0.0
Requires PHP:      7.4
License:           GPLv2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Imports a UK-based location hierarchy (Country, Region, County, City, Neighbourhood, Postcode Prefix) into a custom 'location' taxonomy from a bundled JSON file. Designed for use with the Fish & Chip Shop Directory project.

== Description ==

This plugin registers a hierarchical custom taxonomy named `location` and provides a WP-CLI command to import data into it.

The data is expected in a specific JSON format located at `wp-content/plugins/uk-location-hierarchy/data/location_hierarchy_seed.json`.

The `location` taxonomy is intended to be associated with a `business_listing` custom post type (which should be registered separately).

**Features:**

*   Registers hierarchical `location` taxonomy.
*   Imports nested location data from `data/location_hierarchy_seed.json`.
*   Provides WP-CLI command: `wp location import`
*   Import process is idempotent (safe to run multiple times).
*   Establishes correct parent/child term relationships.

== Installation ==

1.  Upload the `uk-location-hierarchy` folder to the `/wp-content/plugins/` directory.
2.  Activate the plugin through the 'Plugins' menu in WordPress.
3.  Ensure the `data/location_hierarchy_seed.json` file exists within the plugin directory and contains valid JSON data in the expected hierarchical format.
4.  (Optional but Recommended) Use WP-CLI to run the import: `wp location import`

== Frequently Asked Questions ==

= How do I run the import? =

The primary method is via WP-CLI. Navigate to your WordPress installation directory in your terminal and run:
`wp location import`

You can add `--verbose` for more detailed output:
`wp location import --verbose`

= What is the expected JSON format? =

The importer expects an array of top-level location objects (e.g., countries). Each object should have a `name` key and optionally a `children` key (an array of child location objects) or a `post_code_prefix` key for the lowest level terms.

Example Snippet:
`[ { "name": "England", "children": [ { "name": "South East", "children": [ { "name": "Hertfordshire", "children": [ { "name": "St Albans", "children": [ { "name": "Fleetville", "children": [ { "post_code_prefix": "AL1" }, { "post_code_prefix": "AL2" } ] } ] } ] } ] } ] } ]`

*(Adapt the example based on the actual final structure)*

= What Custom Post Type does this work with? =

The `location` taxonomy is registered for association with a CPT having the slug `business_listing`. Ensure this CPT is registered by another plugin or your theme.

== Changelog ==

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.0 =
Initial version.