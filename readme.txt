=== Plugin Name ===
Contributors: aharris88
Donate link: http://www.adamwadeharris.com/donate
Tags: books, ebooks, google, library, shelf, bookshelf, read
Requires at least: 3.0.1
Tested up to: 3.5.1
Stable tag: 2.1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin allows you to display the books in your Google Books Library

== Description ==

With this plugin you can add show off your books from any of the shelves on your Google Books Library. It uses the Google Boooks API to show your Reading Now, Favorites, Have Read, To Read, or your custom shelves. You can also choose the maximum number of books that you want to display.

== Installation ==

1. Upload `googlebooks.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. For the settings, go to Settings -> Google Bookshelves (instructions on settings page)
4. Add the widget 'Google Bookshelves Widget' to the theme you are currently using
5. and/or place the shortcode [google_bookshelves shelf="4" max="10" layout="description"] on any post or page
6. and/or place `<?php google_bookshelves(); ?>` in your templates.

== Frequently Asked Questions ==

Feel free to ask me any questions you want on the support page here or on the my site: http://www.adamwadeharris.com/google-bookshelves/

== Screenshots ==

1. Widget and Grid Layout
2. Description Layout
3. Settings
4. Widget Settings

== Changelog ==

= 2.1.2 =
* Fix title of widget to work with any theme
* Make author link open in new tab

= 2.1.1 =
* Fixed width on titles on books with no cover

= 2.1 =
* Added custom titles
* Added ability to show books in random order

= 2.0 =
* Added settings page
* Added shortcode
* Added multiple layouts
* Added ability to use php function in wordpress template
* Added support for more than the default bookshelves
* Added support for books without a cover image

= 1.0 =
* This is the first version

== Upgrade Notice ==

= 2.1.2 =
* This will look better on any theme

= 2.1.1 =
* Fixed width on titles on books with no cover

= 2.1 =
This is a minor update

= 2.0 =
This is the second version

= 1.0 =
This is the first version
