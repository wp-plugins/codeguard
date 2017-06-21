=== CodeGuard ===
Contributors: jmanuzak
Tags: backup, website backup, security, database backup, database, malware, google safebrowsing
Requires at least: 3.2
Tested up to: 4.2.4
Stable tag: trunk
License: GPLv2

CodeGuard protects your WordPress website with daily automatic backup and monitoring. Automatically fix mistakes or problems by restoring to a previous version.

== Description ==

CodeGuard is a secure, automatic daily backup service for your WordPress blog or website that notifies you when something changes and lets you undo it with ease.

Should you ever need an old file, or if you overwrite a file, delete something, or just mess up your site - don't worry - CodeGuard has you covered.

= Is it secure? =
The CodeGuard WordPress plugin uses strong cryptographic protocols, including RSA public key cryptography, to encrypt and protect your information in transit over the internet even if your web server is not configured with a Secure Socket Layer (SSL) certificate.

These security features protect the confidentiality of your data through both verification and encryption.

* Actively verifies that a backup request originated from an officially authorized CodeGuard.com server
* Encrypts all data using CodeGuard's 2048-bit RSA public key such that only officially authorized CodeGuard.com servers can read your data.
* Each WordPress plugin generates a unique RSA key pair for strong per-site security.

= What does CodeGuard offer? =
**Automated Daily Backups that never let you down**<br>
CodeGuard offers the most reliable backup on the market - 99.999999999% reliable. We achieve this by replicating your data in secure locations across the world - again and again and again.  Amazon Web Services - Simple Storage (S3) and Reduced Redundancy Storage (RRS) are used to deliver these incredible results.

**Get UNDO Power for when anything goes wrong**<br>
CodeGuard helps should anything go wrong - deleted files are now recoverable, overwritten files are now obtainable, and if your site is hacked, the malware is easily removable. All of this with nothing to install.

**Source Code and Database Differential Storage**<br>
CodeGuard seamlessly backs up your source and databases. And it does it in an elegant way that saves you space and makes it easy to see changes between each backup/version.

== Installation == 

This section describes how to install and configure the plugin.

1. Install the plugin using one of the methods listed below.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Click on the CodeGuard link in the WordPress admin sidebar.
1. Enter your CodeGuard API key from [the CodeGuard WordPress page](https://www.codeguard.com/wordpress) and click the update button.

= Automatic Installation via WordPress Catalog =
1. Select the 'Add New' link in the 'Plugins' menu.
1. Enter 'codeguard' in the search box and click 'Search Plugins.'
1. Click the 'Install Now' link

= Upload via WordPress Admin =
1. Download the codeguard.zip file from the WordPress catalog.
1. Select the 'Add New' link in the 'Plugins' menu.
1. Select the 'Upload' link.
1. Click the 'Choose File' button and locate the codegaurd.zip file you downloaded previously.
1. Click the 'Install Now' button to upload and extract the plugin.

= Manual upload =
1. Download the codeguard.zip file from the WordPress catalog and extract it to your local system.
1. Connect to your blog via FTP, SFTP or SSH.
1. Delete any existing `codeguard` folder from the `/wp-content/plugins/` directory.
1. Upload the `codeguard` folder to the `/wp-content/plugins/` directory.

== Screenshots ==
1. Screenshot of the website dashboard.
2. Screenshot of the file monitoring and database views.
3. Screenshot of the ChangeAlert emails.

== Frequently Asked Questions == 

You can see our full FAQ at [support.codeguard.com](https://www.codeguard.com/how-to-get-help/)

== Changelog == 

= 0.52 =
+ Update CodeGuard menu item.

= 0.51 =
* Updated readme and image assets.

= 0.50 =
* Re-release and many changes.

= 0.38 =
* Fixed a bug that impacted h2 tags on Admin pages with some themes

= 0.37 =
* Fixed bug that impacted h4 tags on Admin pages with some themes
* Added settings page with API key update

= 0.36 =
* New and improved user interface
* Updated view logic to only load stylesheet and javascript when needed

= 0.35 =
* More enhancements to restore process

= 0.34 =
* Enhancements to restore process

= 0.33 =
* Developement / internal release

= 0.32 =
* Initial release

== Upgrade Notice ==
