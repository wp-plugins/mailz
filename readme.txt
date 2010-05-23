=== Zingiri Mailing List ===
Contributors: EBO
Donate link: http://www.zingiri.com/
Tags: mailing, mailing list
Requires at least: 2.1.7
Tested up to: 2.9.1
Stable tag: 0.8

Zingiri Mailing List is a Wordpress plugin that brings together a great content management system with the fantastic PHPmail mailing list solution.

== Description ==

Zingiri Mailing List brings a state of the art mailing list system - PHPlist - to the Wordpress world.

phplist is the world's most popular open source email campaign manager

== Installation ==

1. Upload the `mailz` folder to the `/wp-content/plugins/` directory
2. Change permissions on directory mailz/cache to world writable (777)
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to the Wordpress Settings page and find the link to Zingiri Mailing List, and start configuring ...

Please visit the [Zingiri](http://forums.zingiri.com/ "Zingiri Support Forum") for more information and support.

== Frequently Asked Questions ==

= I have more questions! =
Please visit the [Zingiri](http://forums.zingiri.com/ "Zingiri Support Forum") for more information and support.

== Screenshots ==

Screenshots are not yet available, anyway, just install the plugin and try it out, it's pretty easy.

== Upgrade notice ==

Simply upload the new version and go to the control panel to ugprade your version.
Don't forget to take a database backup before you upgrade!

== Other ==
Fixes applied in PHPlists:
* admin/structure.php: changed length of index of user_blacklist_data (limited to 1000, i.e. 333 bytes in UTF-8)

== Changelog ==

= 0.8 =
* Fixed issue with max key length exceeded on table user_blacklist_data
* Fixed issue with plugin not installing on GoDaddy servers because of setting magic_quotes_gpc on in .htaccess file (moved to php.ini file)

= 0.7 =
* Fixed issue with options array not being initialized properly causing an installation problem

= 0.6 = 
* Forced admin user as default user that connects to PHPmail
* Added hourly cron job using WP scheduling functionality to process mail queue

= 0.5 =
* Adapted code to be compatible with PHP4
* Corrected issue when trying to upload files 

= 0.4 = 
* Fixed issue with admin login to PHPlist back-end

= 0.3 = 
* Better initialisation of default configuration options

= 0.2 =
* First release

= 0.1 =
* Working version
