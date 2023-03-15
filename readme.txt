=== Haru Filmmaker Importer ===
Contributors: mekshq, aleksandargubecka
Donate link: https://mekshq.com
Tags: youtube, vimeo, video, import, bulk, videos, importer, channel, playlist, user
Requires at least: 3.7
Tested up to: 6.1
Stable tag: 1.0.10
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html

Easily import YouTube and Vimeo videos in bulk to your posts, pages or any custom post type.

== Description ==

Haru Filmmaker Importer was originally created as a feature for our [Vlog WordPress theme](https://mekshq.com/theme/vlog/) but now it can be used on any WordPress website.

With Haru Filmmaker Importer WordPress plugin you can easily import YoutTube or Vimeo videos in bulk to your posts, pages or any custom post type registered on your website. The plugin is highly configurable and provides you with various options for fetching videos. Whether you want to pull videos from a channel, a playlist, or a specific user and even search query, we got you covered. 

Also, there are several smart options related to the import process itself. Choose a post type, post status, automatically add video description into post content, assign categories, tags or any custom taxonomies while importing the videos.

Video Importer WordPress plugin is created by [Meks](https://mekshq.com)

== Features ==

* Bulk import YouTube and Vimeo videos
* YouTube import supports Playlist, Channel, User and Search query
* Vimeo import supports User, Group and Channel query
* Automatically detects custom post types and taxonomies so you can import videos as regular posts, pages or any custom post types, as well as regular categories and tags or any custom taxonomies
* Choose a post status for imported videos (published, draft, private, pending review...)
* Choose a post format for imported videos (any post format that your current theme supports)
* Option to automatically add video description into post content
* Option to set post date to original video date
* Option to choose any website user as imported video/post author
* Save imports as templates and easily import new videos from the same source with a single click later


== Installation ==

1. Upload meks-video-import.zip to plugins via WordPress admin panel or upload unzipped folder to your wp-content/plugins/ folder
2. Activate the plugin through the "Plugins" menu in WordPress
3. In Admin panel, go to Tools -> Haru Filmmaker Importer to manage the options and import videos

== Frequently Asked Questions ==

For any questions, error reports and suggestions please visit https://mekshq.com/contact/

== Changelog ==

= 1.0.10 =

* Improved: PHP 8 compatibility tweaks

= 1.0.9 =

* Added: Notification for meks plugins

= 1.0.8 =

* Improved: Importer now inserts full video description from YouTube (not only a short version)

= 1.0.7 =

* Added: Option to import videos and its content as blocks (for WP 5.0+ websites)

= 1.0.6 =

* Fixed: Vimeo import recently stoped working due to API changes

= 1.0.5 =

* Improved: Better error messages for Vimeo authorization and import

= 1.0.4 =

* Added: Detection for non-embeddable YouTube videos before import
* Improved: Video importing not working on specific server configurations
* Improved: Better error messages

= 1.0.3 =

* Improved: Plugin scripts and styles are now loaded on the plugin options page only
* Fixed: Plugin throwing a PHP notice in some special cases

= 1.0.2 =

* Fixed: Importer throwing errors in some special cases

= 1.0.1 =

* Fixed: Vimeo importing was working only if both YouTube and Vimeo API keys were provided 

= 1.0.0 =
* Initial release