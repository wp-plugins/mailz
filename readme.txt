=== ccMails ===
Contributors: EBO
Donate link: http://www.choppedcode.com/
Tags: mailing, mailing list
Requires at least: 2.1.7
Tested up to: 3.0.4
Stable tag: 1.1.2

ccMails is a Wordpress plugin that brings together a great content management system with the fantastic PHPmail mailing list solution.

== Description ==

ccMails brings a state of the art mailing list system - PHPlist - to the Wordpress world.

phplist is the world's most popular open source email campaign manager

== Installation ==

1. Upload the `mailz` folder to the `/wp-content/plugins/` directory
2. Change permissions on directory mailz/cache to world writable (777)
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to the Wordpress Settings page and find the link to ccMails, and start configuring ...

Please visit the [ChoppedCode](http://choppedcode.com/forums/ "ChoppedCode Support Forum") for more information and support.

== Frequently Asked Questions ==

= I have more questions! =
Please visit the [ChoppedCode](http://choppedcode.com/forums/ "ChoppedCode Support Forum") for more information and support.

== Screenshots ==

Screenshots are not yet available, anyway, just install the plugin and try it out, it's pretty easy.

== Upgrade notice ==

Simply upload the new version and go to the control panel to ugprade your version.
Don't forget to take a database backup before you upgrade!

== Other ==
Fixes applied in PHPlists:
* admin/structure.php: changed length of index of user_blacklist_data (limited to 1000, i.e. 333 bytes in UTF-8)
* admin/editlist.php: replaced $_GET['id'] with $_REQUEST['id'];
* admin/editattributes.php: replaced $id = !empty($_GET['id']) ? sprintf('%d',$_GET['id']) : 0; with $id = sprintf('%d',$_REQUEST['id']);
* admin/spagedit.php: replaced $_GET['id'] by $_REQUEST['id']

== Changelog ==

= 1.1.2 =
* Removed definition of GetUserIp function which is not used and causes a conflict with Nextgen Gallery Voting plugin

= 1.1.1 =
* Added test on cache directory being writable
* Added new option to import all Wordpress users in a default mailing list
* Tested up to Wordpress version 3.0.4
* Fixed potential compatibility issue with other ChoppedCode plugins

= 1.1.0 =
* Fixed issue with editing of subscribe pages duplicating the page instead of updating it
* Added a check to see if CURL is installed
* Renamed plugin to ccMails
* Changed support site to http://choppedcode.com

= 1.0.0 = 
* Updated to work with Wordpress 3.0.1
* Fixed issue with editing of lists duplicating the list instead of simply updating it
* Fixed issue with user reconciliation features being directed to an unaccessible page
* Fixed issue with website url in PHPlist not being updated correctly. Should be siteurl rather than home url.
* Fixed issue with "Error: No such attribute: 0" when trying to add an attribute.
* Added display of version in control panel

= 0.9.4 =
* Fixed compatibility issue with ccTickets plugin
* Fixed issue with backslashes in front of single and double quotes

= 0.9.3 =
* Now uses dedicated phplist.css for front end, different from the one used in the admin back-end
* Cleaned up unused sidebar code
* Fixed issue with HTML links on mailing page

= 0.9.2 =
* Corrected footer (PHPlist instead of osTicket)
* Fixed issue with PHP magic quotes set to on in PHPlist

= 0.9.1 = 
* Fixed issue with pages and posts being shown as blank after activation of plugin
* Code clean up

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
