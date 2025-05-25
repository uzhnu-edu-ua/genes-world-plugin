=== Plugin Name ===
Contributors: Alex Dubiv
Tags: genome, sequencing, data, table, statistics
Requires at least: 6.0.1
Tested up to: 6.5
Stable tag: 6.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WordPress plugin "Genes-WORLD" provides an intuitive and user-friendly interface for researchers to explore, filter, and contribute to a global collection of genomic data.

== Description ==

Genes-WORLD operates with two core visualization components: an interactive world map and a tabular data viewer. The interactive map is the primary data exploration tool, allowing users to visualize global genomic datasets geographically (Figure 1). The map is equipped with dynamic layer filters that enable users to customize the display based on various criteria, such as the number of samples publicly available by country, samples available upon request, genome project types, etc. 
Beneath the map, the tabular data viewer offers a detailed view of the underlying data. This table supports live filtering, sorting, and pagination, allowing users to browse large datasets efficiently. Each row in the table represents a specific genomic project, and clicking on a row expands an additional section that reveals the complete list of data attributes for that project with corresponding values. This feature includes detailed metadata such as the population name, country of origin, sequencing technology used, data availability, direct links to the project’s website or publication, etc. This granular level of detail ensures that users have comprehensive access to all relevant information, making it easier to assess the scope and applicability of each dataset.
Additional details and live accces: https://globgen.uzhnu.edu.ua/ (https://globgen.uzhnu.edu.ua/world-geo-data/)

== Installation ==

1. Upload `genes-world-plugin` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. On WP admin panel go to `Tools -> Genes WORLD` and upload prepared .CSV with corresponding data
1. Create dedicated page on a website and add there shortcode `[genes_world_geodata]` - this is placeholder to output interactive map and related tables.

== Screenshots ==

1. Front-end: Map view.
2. Front-end: Data table view.
3. Admin configuration panel of plugin.

== Changelog ==

= 1.0 =
* Initial commit and plugin publication
