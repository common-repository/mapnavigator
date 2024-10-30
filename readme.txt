=== Map Navigator ===
Contributors: David Rothman
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=54AZYA26EA848&lc=US&item_name=Support%20Map%20Navigator%20development&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Tags: csv, import, batch, spreadsheet, excel, google maps, mappress,mappress pro, google map navigation, feed, wp-affiliate, wp-affiliate-platform, taxonomy, wordpress, mappress-google-maps-for-wordpress, clean database
Requires at least: 2.0.2
Tested up to: 3.2.1
Stable tag: 1.1.1

Create Posts for Map Navigation for a geographical taxonomy using MapPress Google Maps in WordPress.

== Description ==

This plugin imports a Map Navigation file into your WordPress blog. 
The Map Navigation file defines one or more Continents, Countries, Regions, States and Cities.
The plugin creates Posts that each contain a MapPress map or mashup that provide a linked
navigation of the geography using your organization's name, image file and icon in the Post's
Post, Excerpt and on the Map markers. The Map Navigation hierarchy can then be added to your 
themes header or provided as a link so that your organization can locate its members Posts
or Pages by Geographical area. The plugin also provides an affiliate Register script that adds
an Affiliate to your Map Navigation hierarchy along with their link and image when they join 
your organization. Any post can be added to the Map Navigation hierarchy using the Map Taxonomy
tags and a Custom field used by the MapPress plugin. Exisitng MapPress maps can be linked into 
the Map Navigation Hierarchy from the MapNavigator Admin Tool Interface.

This plugin is built on top of the CSV Importer plugin so it contains all of the features of 
CSV Importer with the additional customized features for MapPress Google maps navigation.

== Map Navigator features ==
* 	Map Navigation Files available (for a small fee) for geographical areas (USA available now)
*   Documentation and Sample file so that you can create your own Map Navigation File	
*   Automatically creates the MapPress Maps, Mashups and Markers with your organizations:
	- Name or Title
	- Link to website or other location 
	- Image from an http:// link
	- Icon from a list of icons supplied by MapPress or a custom icon placed into the MapPress icon directory
*   Cleans database for 'orphaned' MapPress Maps
*   Links exisitng MapPress Maps into your Map Navigation hierarchy
*   Creates a Map Taxonomy that provides easy categorization of a Post by geographical location
*   Provides the ability to add any post to your Map Navigation hierarchy without coding any
    complex MapPress parameters.
*   Provides a customized WP-Affiliate Registration script that will place your new affiliate on the
    appropriate Map in your Map Navigation hierarchy
*   Provides several customizations to the MapPress plugin to address limitations and enhancements that
    provide the above functionality
	
= CSV Importer Features =

*   Imports post title, body, excerpt, tags, date, categories etc.
*   Supports custom fields, custom taxonomies and comments
*   Deals with Word-style quotes and other non-standard characters using
    WordPress' built-in mechanism (same one that normalizes your input when you
    write your posts)
*   Columns in the CSV file can be in any order, provided that they have correct
    headings
*   Multilanguage support



== Screenshots ==

1.  Plugin's interface under Tools
2.  Top Level of the USA Taxonomy Customized for a Fire Department Association
3.  Marker for Pacific Region on Top Level
4.  Second level of the Hierarchy - Mountain Region States
5.  Marker for State of Montana - USA Mountain Region
6.  Third level of the Hierarchy - State of Montana Map 
7.  Fourth level of the Hierarchy - City of Helena, Montana Map 
8.  WP-Affiliate-Platform Custom Registration Screen
9.  Affiliate  Registration Creats a Map for Boulder Colorado



== Installation ==

Installing the plugin:

1.  Unzip the plugin's directory into `wp-content/plugins`.
2.  Activate the plugin through the 'Plugins' menu in WordPress.
3.  The plugin will be available under Tools -> Map Navigator on the WordPress administration page.
4.  Copy the contents of the "map-navigator/mappress-google-maps-for-wordpress/*" to "wp-content/plugins/mappress-google-maps-for-wordpress/"
5.  Copy the contents of the "map-navigator/wp-affiliate-platform/*" to "wp-content/plugins/wp-affiliate-platform/"
6.  Add a new Custom Field called 'map_address' in any post
7.  Go to MapPress Settings and Custom Fields section. Assign 'field for addresses' to 'map_address' 
8.  Check "Update map when post is updated" and "Update map when address is changed by a program", then Save Changes


== Usage ==

Click on the Map Navigator link on your WordPress admin page, choose the
Map Definition file you would like to import and click Import. You can
build your own Map Definition file using the example file "Test Map-Navigator-USA.csv" in the `examples` directory
inside the plugin's directory or you can download one for a small fee from:

http://mapclick.org

The Map Definition file is in CSV format that consists of rows and columns. Each row in
a CSV file represents a post; each column identifies a piece of information
that comprises a post. Each Post is assigned to a level in the Map Taxonomy which provides
the Map Navigational Hierarchy as follows:

Maps Taxonomy
* Continent
	* Country
		* Region (optional)
			* State (optional)
				* City


= Basic post information =

*   `nav_post_title` - title of the post. The name should reflect the category of the taxonomy level. For instance : 'United States Regions'
						or 'Mountain Region States' or 'California Map'
						
*   `nav_post_post` - body of the post. This will hold the MapPress  command to create a 'mashup' or to display a map in this post

*   `nav_post_excerpt` - post excerpt. This will contain the title that will appear in the Map Marker. The word 'mapClicks' 
						will be replaced with your organization's name entered on the Map Navigator Tool dialog in Wordpress.
						
*   `nav_post_categories` - a comma separated list of category names or ids. This will be the level associated with this post;
						for instance : Maps > Continent > Country > Region
						
*   `nav_post_tags` - a comma separated list of tags. Tags are used in the nav_post_post column to specify the mashup posts that will be included in the
                      mashup post. See the Examples directory for a sample file. Pay close attention to the tags defined in this column and the references
					  in the nav_post_post column.
					  
*   `nav_post_date` - about any English textual description of a date and time.
    For example, `now`, `11/16/2009 0:00`, `1999-12-31 23:55:00`, `+1 week`,
    `next Thursday`, `last year` are all valid descriptions. For technical
    details, consult PHP's `strtotime()` function [documentation][strtotime].
	
*   nav_ctax_maps - See the Examples directory for a sample file (Test Map-Navigator-USA.csv). 
                    This column is used to specify the Post's taxonomy tags and thus what the post will appear as in the 'Maps' taxonomy 
					(Continent, Country, Region, State or City)

*   'map_address' - holds the address of the MapPress map marker. This must be defined as a Custom field and specified in the 
                    MapPress setup as the field used to create maps automatically from when a post is updated.

[custom_post_types]: http://codex.wordpress.org/Custom_Post_Types
[strtotime]: http://php.net/manual/en/function.strtotime.php

= Custom fields =

Any column that doesn't start with `nav_` is considered to be a custom field
name. The data in that column will be imported as the custom field’s value.

= General remarks =

*   WordPress pages [don't have categories or tags][pages].
*   Most columns are optional. Either `nav_post_title`, `nav_post_post` or
    `nav_post_excerpt` are sufficient to create a post. If all of these
    columns are empty in a row, the plugin will skip that row.
*   The plugin will attempt to reuse existing categories or tags; if an
    existing category or tag cannot be found, the plugin will create it.
*   To specify a category that has a greater than sign (>) in the name, use
    the HTML entity `&gt;`

[pages]: http://codex.wordpress.org/Pages

= Advanced usage =

*   `nav_post_author` - numeric user id or login name. If not specified or
    user does not exist, the plugin will assign the posts to the user
    performing the import.
*   `nav_post_slug` - post slug used in permalinks.
*   `nav_post_parent` - post parent id.

== Custom taxonomies ==

Once custom taxonomies are set up in your theme's functions.php file or
by using a 3rd party plugin, `nav_ctax_(taxonomy name)` columns can be 
used to assign imported data to the taxonomies.

__Non-hierarchical taxonomies__

The syntax for non-hierarchical taxonomies is straightforward and is essentially
the same as the `nav_post_tags` syntax.

__Hierarchical taxonomies__

The syntax for hierarchical taxonomies is more complicated. Each hierarchical
taxonomy field is a tiny two-column CSV file, where _the order of columns
matters_. The first column contains the name of the parent term and the second
column contains the name of the child term. Top level terms have to be preceded
either by an empty string or a 0 (zero).

Sample `examples/custom-taxonomies.csv` file included with the plugin
illustrates custom taxonomy support. To see how it works, make sure to set up
custom taxonomies from `functions.inc.php`.

Make sure that the quotation marks used as text delimiters in `nav_ctax_`
columns are regular ASCII double quotes, not typographical quotes like “
(U+201C) and ” (U+201D).

== Comments ==
An example file with comments is included in the `examples` directory.
In short, comments can be imported along with posts by specifying columns
such as `nav_comment_*_author`, `nav_comment_*_content` etc, where * is
a comment ID number. This ID doesn't go into WordPress. It is only there
to have the connection information in the CSV file.


== Frequently Asked Questions ==

> I have quotation marks and commas as values in my Map Navigation file. How do I tell Map Navigator to use a different separator?

It doesn't really matter what kind of separator you use if your file is
properly escaped. To see what I mean by proper escaping, take a look at
`examples/sample.csv` file which has cells with quotation marks and commas.

> How can I import characters with diacritics, Cyrillic or Han characters?

Make sure to save your CSV file with utf-8 encoding.

Prior to version 6.0.4, MySQL [did not support][5] some rare Han characters. As
a workaround, you can insert characters such as &#x2028e; (U+2028E) by
converting them to HTML entities - &amp;\#x2028e;

[5]: http://dev.mysql.com/doc/refman/5.1/en/faqs-cjk.html#qandaitem-24-11-1-13


> I'm importing a file, but not all rows in it are imported and I don't see
a confirmation message. Why?

WordPress can be many things, but one thing it's not is blazing fast. The
reason why not all rows are imported and there's no confirmation message is
that the plugin times out during execution - PHP decides that it has been
running too long and terminates it.

There are a number of solutions you can try. First, make sure that you're not
using any plugins that may slow down post insertion. For example, a Twitter
plugin might attempt to tweet every post you import - not a very good idea
if you have 200 posts. Second, you can break up a file into smaller chunks that
take less time to import and therefore will not cause the plugin to time out.
Third, you can try adjusting PHP's `max_execution_time` option that sets how
long scripts are allowed to run. Description of how to do it is beyond the
scope of this FAQ - you should search the web and/or use your web host's help
to find out how. However, putting the following line in `.htaccess` file inside
public_html directory works for some people:

    # Sets max execution time to 2 minutes. Adjust as necessary.
    php_value max_execution_time 120

The problem can be approached from another angle, namely instead of giving
scripts more time to run making them run faster. There's not much I can do to
speed up the plugin (you can contact me at dvkobozev at gmail.com if you like
to prove me wrong), so you can try to speed up WordPress. It is a pretty broad
topic, ranging from database optimizations to PHP accelerators such as APC,
eAccelerator or XCache, so I'm afraid you're on your own here.


> I'm getting the following error: `Parse error: syntax error, unexpected
T_STRING, expecting T_OLD_FUNCTION or T_FUNCTION or T_VAR or '}' in .../public_html/wp-content/plugins/csv-importer/File_CSV_DataSource/DataSource.php
on line 61`. What gives?

This plugin requires PHP5, while you probably have PHP4 or older. Update your
PHP installation or ask your hosting provider to do it for you.


== Credits ==

This plugin is based upon  [csv-importer][3] by Denis Kobozev (thanks !) .
This plugin uses [php-csv-parser][3] by Kazuyoshi Tlacaelel (thanks !).

Contributors:
*   Israeli Rothman (Original Idea and Requirements Spec)



== Changelog ==

= 1.1.0 =
*   Add new Functionality
*   - MapPress Map Tables cleanup to remove orphan maps
*   - Link all maps in MapPress database into the MapNavigator Hierarchy
*   - Option to update all posts with Maps to set Maps Taxonomy and tags
*   - MapNavigator Admin Tool user interface updates for new functionality

= 1.0.4 =
*   Move mappress_pro.php to the correct directory under mappress-google-maps-for-wordpress

= 1.0.3 =
*   ReadMe update for issue with screenshots not correct on WP site

= 1.0.2 =
*   ReadMe update for issue with update version not correct on WP site

= 1.0.1 =
*   Code cleanup
*   Documentation and insertion of a link to download map Navigation files by geography.
*   Provide MapPress Icon to be inserted into Map Markers and specified on Tool Dialog

= v1.0.0 =
*   Initial version of the plugin



== Upgrade Notice ==
= 1.1.0 =
*   Add new Functionality
*   - MapPress Map Tables cleanup to remove orphan maps
*   - Link all maps in MapPress database into the MapNavigator Hierarchy
*   - Option to update all posts with MappRess Maps to set Maps Taxonomy and tags
*   - MapNavigator Admin Tool user interface updates for new functionality

= 1.0.1 =
Provide MapPress Icon to be inserted into Map Markers and specified on Tool Dialog

