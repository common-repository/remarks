=== Plugin Name ===
Contributors: CiviFirst John
Donate link: http://kiva.com
Tags: comments, comments analysis, comments review, popular, remarks, meta, metrics, information, analysis, geolocation, geomapping, comment locations, comment geolocations, community, targetting, discussion, feedback, map, demographics
Requires at least: 3.1.1
Tested up to: 4.8
Stable tag: 4.2

Analysis of your site's comments, showing which posts, authors, and categories generate the most discussion with tables, charts, and geolocation.

== Description ==

Remarks gives useful charts, tables, and geolocations of your blog's comments, and may help you to decide how to focus your blog for even greater comment harvesting.  You will be able to see which of your posts, categories, and authors generate the most discussion. The breakdowns that Remarks produces are accessible via the WordPress Admin menu, under Comments.

Remarks uses D3 JS (https://d3js.org/) to draw bar and pie charts, and Leaflet (http://leafletjs.com) to draw maps.

All feedback is really appreciated - please mail to john HAT civifirst DOT com

== Installation ==

1. In the "Plugins" section of the WordPress Admin menu, click on "Add New".

2. Enter the phrase "Remarks" into the search bar.

3. Press "download" and hit yes when the prompt asks you to.

4. After the download, hit "Activate".

== Frequently Asked Questions ==

= What should I do if the installation fails? =

Contact the CiviFirst team at john@civifirst.com

= How is the graph populated? =

The graph is populated upon installation, and whenever a comment is approved. Coordinates are removed when a comment is unapproved, marked as spam, or deleted.

== Screenshots ==

1. This first screenshot shows the Overview Screen.

2. This screen shows the Posts ordered by the number of comments.

3. This screen shows the Categories section, specifically the Table of information. 

4. This screen shows which Authors have the most comments.

5. This screen shows the Geolocation of the comments.

== Changelog ==
= 4.2 =
* Update to use Leaflet for maps, as Google now requires an API key.
* Added a legend to the pie charts.
* Further heavy refactoring - eliminated global variables.

= 4.0 =
* Update to use D3.
* Major backend overhaul to use objects, reduce use of globals, and for greater code reuse.
* Update to match changes to FreeGeoIP format.

= 3.0 =
* Upgraded to match WP v 4.2.4.
* Rebranded

= 2.0 =
* Entirely new interface.

= 1.3 =
* Moved away from hostIP to FreeGeoIP. This resulted in a greater number of non UK posts being geolocated correctly (100% of our sample was placed right).
* Minor changes based on feedback.
* Reworked the buttons system to reduce code duplication and ease maintainability.

= 1.2 =
* Added a geolocation section. You can now find out which countries and cities host your biggest contributors.
* Other changes based on feedback.

= 1.1 =
* Laid out data in table format 
* Added dashboard buttons to show data in sections


== Upgrade Notice ==
= 4.2 =
* Update to use Leaflet for maps, as Google now requires an API key.
* Added a legend to the pie charts.

= 4.0 =
* Updated graphs to use D3 Javascript library - much more slick!
* Updated Map to use latest FreeGeoIP format.

= 3.0 =
* Upgraded to match WP v 4.2.4.

= 2.0 =
* Entirely new interface.

= 1.3 =
* Moved away from HostIP to FreeGeoIP. This resulted in a greater number of non UK posts being geolocated correctly (100% of our sample was placed right).
* Fixed a minor bug with the geolocation button.

= 1.2 =
Added a geolocation section. You can now find out which countries and cities host your biggest contributors.

= 1.1 =
This upgrade gives Remarks buttons to show and hide the data in sections. The data is also laid out in an ultra-clear table format.
