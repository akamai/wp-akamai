=== Akamai for WordPress ===
Contributors: dshafik
Tags: akamai, cache, purge, cdn
Requires at least: 4.3
Tested up to: 4.9
Stable tag: trunk
License: Apache 2.0
License URI: https://www.apache.org/licenses/LICENSE-2.0

The Akamai for WordPress plugin integrates automatically with Akamai to purge the cache when you create or update a post/page.

== Description ==

The Akamai for WordPress plugin will automatically purge the cache using the new Fast Purge feature whenever you create or update a post.

With the Fast Purge API, you can automate your publishing flow to maximize performance and offload without compromising on freshness. Switching to a Hold ‘Til Told methodology, you can cache semi-dynamic content with long TTLs, and then refresh it in approximately 5 seconds with Fast Purge.

= Development & Support =

This plugin is developed on [GitHub](https://github.com/akamai/wp-akamai). Issues should be filed [here](https://github.com/akamai/wp-akamai/issues).

== Installation ==

1. Upload the plugin directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Edit the Settings to add credentials

== Frequently Asked Questions ==

= How long does purging take? =

The purge request is almost instantaneous, and the purge itself is completed within 5 seconds.

= What is purged? =

By default, the front page, and the individual post page are purged. You can additionally enable purging of tag, category, and monthly archives.

= What are the limitations? =

This API supports the following rate limits per customer:

* 6,000 URLs per minute (sustained)
* 5,000 URLs per second (burst)

Purge requests submitted at a higher rate results in a 507 error code. If you receive this error, wait 5 seconds and try again.

In addition, there is a strictly enforced 50,000 byte limit on the size of each request.

== Screenshots ==

1. Akamai for WordPress Settings screen

== Changelog ==

= 0.6 =

* Fix fatal errors when no .edgerc exists or is not found

= 0.5 =
* Fix banner image

= 0.4 =
* Rename plugin to "akamai"

= 0.3 =
* Add support for purging on comments

= 0.2 =
* Use latest Akamai libraries

= 0.1 =
* Initial release

== References ==

* [Akamai OPEN APIs](https://developer.akamai.com)
* [Akamai Credentials](https://developer.akamai.com/introduction/Identity_Model.html)
* [Content Control Utility API v3 (CCUv3) Documentation](https://developer.akamai.com/api/purge/ccu/overview.html)
