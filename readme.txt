=== BruteProtect ===
Contributors: hotchkissconsulting 
Tags: security, brute force, brute force attack, harden wp, login lockdown
Requires at least: 3.0
Tested up to: 3.6
Stable tag: trunk

BruteProtect is a cloud-powered Brute Force attack prevention plugin.  We leverage the millions of WordPress sites to identify and block malicious IPs.  Once you install the plugin, you will need to get a free BruteProtect API key, which you can do directly from your WordPress dashboard.

== Description ==

BruteProtect tracks failed login attempts across all installed users of the plugin.  If any single IP has too many failed attempts in a short period of time, they are blocked from logging in to any site with this plugin installed.  Once you install the plugin, you will need to get a free BruteProtect API key, which you can do directly from your WordPress dashboard.

This allows you to protect yourself against tradition brute force attacks AND distributed brute force attacks that use many servers and many IPs

== Installation ==

1.  Extract the zip file and just drop the contents in the wp-content/plugins/ directory of your WordPress installation
2.  Activate the Plugin from Plugins page
3.  Open the BruteProtect settings under the \"Plugin\" section of the WordPress dashboard
4.  Follow the simple instructions to obtain and enter a free API key

== Screenshots ==
1. Simply create an API key...

2. If a blocked user shows up on your login page, they will see this message