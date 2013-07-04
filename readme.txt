=== BruteProtect ===
Contributors: hotchkissconsulting, roccotripaldi
Tags: security, brute force, brute force attack, harden wp, login lockdown
Requires at least: 3.0
Tested up to: 3.6
Stable tag: trunk

BruteProtect is a cloud-powered Brute Force attack prevention plugin.  We leverage the millions of WordPress sites to identify and block malicious IPs.  Once you install the plugin, you will need to get a free BruteProtect API key, which you can do directly from your WordPress dashboard.

== Description ==

##BruteProtect now supports Multisite Networks

BruteProtect tracks failed login attempts across all installed users of the plugin.  If any single IP has too many failed attempts in a short period of time, they are blocked from logging in to any site with this plugin installed.  Once you install the plugin, you will need to get a free BruteProtect API key, which you can do directly from your WordPress dashboard.

This allows you to protect yourself against traditional brute force attacks AND distributed brute force attacks that use many servers and many IPs

== Installation ==

1.  Extract the zip file and just drop the contents in the wp-content/plugins/ directory of your WordPress installation
2.  Activate the Plugin from Plugins page
3.  Open the BruteProtect settings under the \"Plugin\" section of the WordPress dashboard
4.  Follow the simple instructions to obtain and enter a free API key

== Screenshots ==
1. Simply create an API key...

2. If a blocked user shows up on your login page, they will see this message

== Changelog ==

= 0.9.8 =
* Added a fallback for failed multisite blog count reporting
* Added the ability to hide BruteProtect stats from network blog dashboards

= 0.9.7.2 =
* Fixed a minor display issue in 0.9.7.1

= 0.9.7.1 =
* Fixed a minor display issue in 0.9.7

= 0.9.7 =
* BruteProtect now supports multisite networks!  One key will protect every site in your network, and will always be free for small networks!
* Fixed API URI logic so that we fall back to non-https if your server doesn't support SSL
* Fixed admin config page image (thanks, flick!)
* Added index.php to prevent directory contents from being displayed (thanks, flick!)

= 0.9.6 =
* Admin-side updates for better compatibility and readability -- Thanks again, Michael Cain!

= 0.9.5 =
* Changed API server to HTTPS for increased security
* Improved domain check method even further
* Added a "Settings" link to the Plugins page
* Made things prettier

= 0.9.4 =
* Changed domain check method to reduce API key errors

= 0.9.3 =
* Added hooks in for upcoming remote security and uptime scans

= 0.9.2 =
* Fixed error if Login Lockdown was installed
* Improve admin styling (thanks Michael Cain!)
* Added statistics to your dashboard
* If the API server goes down, we fall back to a math-based human verification