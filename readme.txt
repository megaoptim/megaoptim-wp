=== MegaOptim Image Optimizer ===
Contributors: megaoptim, darkog
Tags: convert webp, webp, optimize images, optimize, images, compress
Requires at least: 3.6
Tested up to: 6.6
Requires PHP: 5.3
Stable tag: 1.4.24
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Optimize and compress your images to speed up your site and boost your SEO.

== Description ==

**MegaOptim is image optimization plugin and that is easy to use, stable and actively maintained by dedicated team of experts. :)**

The plugin uses minimal resources on your server and all the heavy lifting is done by our API service on our servers. No binaries will be ever installed on your server that will slow down your site.

We strive to make the plugin as lightweight as possible and will never bloat your dashboard with ads, notifications, upsells or run background services that will cause high CPU usage constantly. The plugin only runs when it's really necessary.

## What is image optimization and why it is important?

Image Optimization is delivering the high-quality images in the right format, dimension, size, and resolution while keeping the smallest possible size.

## What features do MegaOptim offer?

- Bulk optimization for the Media Library, NextGen, MediaPress, etc
- Bulk optimization of Folders by your choice in your server
- WP CLI Support for optimization and restore (bulk or single)
- Uses progressive JPEG format for faster rendering
- Supports <strong>WebP</strong>. Creates and serves WebP when possible
- Supports Sub-accounts support with detailed statistics
- Supports Backups/Restore + option to configure what to back up
- Option to select which thumbnails to optimize
- Option to enable/disable conversion from CMYK to RGB
- Option to preserve/remove the EXIF/IPTC metadata
- Option to auto-resize images to given dimensions upon optimization/upload
- Compatible with WP Retina 2x
- Compatible with WP Offload Media / Amazon S3
- Compatible with CloudFlare. Purges image cache upon optimization
- Compatible with WP 5.3+ BIG Image Threshold feature
- Compatible with Windows/UNIX hosting environments
- Compatible with WP Engine, SiteGround and other providers
- Compatible with localhost and password-protected sites
- No credits charged if %5 or less is saved per image
- Multisite Support

## How much does MegaOptim cost?

The service comes with **500 FREE images/tokens per month for everyone**. We also have <strong>unlimited plan</strong> at only **$7.50**. Check our pricing <a href="https://megaoptim.com/pricing/" target="_blank">here</a>.

## Have a question? Contact us!

We have dedicated support team ready to help you 24/7.

* Email  [<a href="https://megaoptim.com/contact">Click Here</a>]
* Twitter [<a href="https://twitter.com/MegaoptimO">Click Here</a>]
* Facebook [<a href="https://www.facebook.com/megaoptimio">Click Here</a>]

## Developer Hooks / CLI

Click <a target="_blank" href="https://megaoptim.com/tools/wordpress">here</a> to read more about our WP CLI integration and the actions and filters that are available.

== Installation ==
1.) Sign up for api key at https://megaoptim.com/register
2.) You will receive your API key in the email address you provided.
3.) Upload the MegaOptim plugin to /wp-content/plugins or install it via the wp-admin interface, finally activate it.
4.) Navigate to wp-admin and in the sidebar menu click on "MegaOptim", hover over and go to "Settings", here you can paste the api key and save the settings.
5.) In the same section check the other settings, change them according to your needs.
6.) You are all set! Begin optimizing by navigating to "MegaOptim" > "Optimizer" ( In the top right corner you can select optimizer )

== Frequently Asked Questions ==

= What is API token? =
    One API token is one image. Please note that WordPress generates multiple thumbnails per one image. You will be charged one token for each thumbnail. If the total saved size per image is less than %5 you will not be charged.

= Can I use the same API Key on multiple websites? =
    Absolutely. You can use your API key on as many websites as you want, or you can also create subaccounts for each of the sites you manage if you want to keep the things separate or bill your clients separate.

= Do you offer CDN? =
    No, but our plugin integrates with WP Offload Media and that way you can easily use Amazon S3 bucket as your CDN which is much cheaper than actual CDN service.

= Is the plugin compatible with WP Offload Media? =
    Yes. If you have configured WP Offload Media no further configurations are needed, the images will be automatically optimized and offloaded to your S3 bucket.

= How the WebP feature works?
    The WebP versions are free of charge and our API service will create optimized WebP for each image upon optimization and store it in your storage. If you enable Front-End delivery the content will be re-written and if .webp version exist for specific image it will be used instead.

= Can I back up and restore the images? =
    Yes, MegaOptim have option to back up the images, and it is enabled by default. You can alawys restore the original images if there is backup.

= Can I manage multiple sites separately with subaccounts? =
    Sure! You can create subaccount for your client and transfer tokens to the subaccount balance. This way your client will have separate account and api key and you will be managing it. You have the option to add or remove api tokens from it.

= Is WP CLI (command line) supported? =
    Sure! You can optimize and restore images from the command line using WP CLI. Please see this <a href="https://megaoptim.com/blog/how-to-optimize-wordpress-images-with-wp-cli-and-megaoptim/">guide</a> for more information.

= How to delete the MegaOptim database data? =
    To delete the database data, set define('WP_DEBUG', true) in wp-config.php, navigate to "Settings" > "MegaOptim" > "Advanced" and in the bottom you will see the option "Delete MegaOptim database metadata". Use it with caution and read about the consequences.

= Is this plugin heavy for my site? =
    The plugin optimizes images on our external servers and does not run anything on your own servers that may cause slowndowns. In addition, it only runs when you start bulk process or if you upload image via Media Library to autoamatically optimize it. It's not active in background unlike some other plugins.

= I used ShortPixel, Imagify and other plugins. Will your plugin reduce the images size furthermore? =
    In most cases yes! The plugin will make attempt to optimize the image further. If the saved size result is less or equal to 5% no token will be charged!

= What happens if i stop using or deactivate MegaOptim plugin? =
    Nothing, your images will remain optimized, if you used the WebP feature the site won't serve WebP any longer.

= What are the pricing packages? =
    We do both one-time and monthly. Please check out <a href="https://megaoptim.com/pricing">here</a>.

= Can I cancel my subscription? =
    Absolutely! You can cancel your subscription whenever you want.

= Do you have an API? =
   Yes, please check out <a href="https://megaoptim.com/docs/api">here</a> for more documentation. Need to optimize images in your custom projects? You can even use our <a href="https://github.com/megaoptim/megaoptim-php">PHP Library</a> via composer.

= Will MegaOptim work with CloudFlare? =
    Yes! You need to use the CloudFlare plugin with correct credentials or setup credentials in the Settings > Advanced menu. If the credentials are set up correctly, the plugin will automatically purge from the CloudFlare cached images when they are optimized with MegaOptim.

= Do you have referral program? =
    Sure! Help us spread MegaOptim and earn 120 tokens per sign up and 300 tokens once customer becomes paid customer. Our Referral program can be found in the <a target="_blank" href="https://app.megaoptim.com/">dashboard area</a>.

= I have problem, the plugin won't work. =
   Before anything, please <a target="_blank" href="https://megaoptim.com/contact">contact us</a> as soon as possible, and we can assist you. We even have a live chat on our site.


== Screenshots ==
1. Media Library Optimizer
2. Media Library Table List
3. General Settings
4. Advanced Settings
5. Debug Page
6. NextGen Library Optimizer
7. Media Library Single Attachment Page
8. WP CLI - All commands
9. WP CLI - Bulk Optimize command
10. WP CLI - Bulk Restore command

== Changelog ==

= 1.4.24 =
* Test compatibility with WordPress 6.6
* Replace deprecated function mb_convert_encoding

= 1.4.23 =
* Test compatibility with WordPress 6.4
* Improve error logging
* Fix compatibility with WP Offload Media
* Fix webp loading when using with WP Offload Media
* Fix bugs during the async upload
* Upgrade wp-background-processing library

= 1.4.22 =
* Test compatibility with WordPress 6.3
* Update plugin resources

= 1.4.21 =
* Fix issue related to file name encodings which prevented to correctly optimize that used special characters in file name

= 1.4.20 =
* Added option to delete attachment metadata via Settings > MegaOptim > Advanced if WP_DEBUG is enabled.
* Added additional safeguarding of the original images if invalid/corrupt image is received through the API.
* Fix 'Custom folders' optimizer issue, not finding files with all uppercase.
* Fix 'Custom Folders' optimizer issue, not correctly transforming local to public path in some hosting environments.

* Fix various warnings related to PHP8.1

= 1.4.19 =
* Added PHP 8.1 support
* Tested with WordPress 5.9

= 1.4.18 =
* Removed the cURL API calls and rewrote it to use the WordPress HTTP API instead
* Fixed various bugs in the HTTP client making the plugin behave creepy in some environments.
* Fixed end(explode()) call that triggered error. We should pass variables only here.
* Added various codebase and PHP 8.1 improvements

= 1.4.17 =
- Fix PHP warnings related to the WebP features

= 1.4.16 =
- Fix url encoding for attachments that contain weird characters

= 1.4.15 =
- Fix problem related to the the constants definitions loading
- Fix url validation problem causing bulk optimizer to hang
- Fix conflict with File Manager plugins that use elfinder library
- Fix WordFence false-positive related to php_uname()
- Update api timeouts to prevent ui blocking if megaoptim api is down
- Improved bulk optimizer. Add better error handling.

= 1.4.14 =
- Added UI sidebar for links and other information
- Added free tokens promo link
- Added various responsive UI fixes
- Fixed problem with CLI. Allow up to 10 consecutive errors before terminating bulk upload.

= 1.4.13 =
- Terminate the WP CLI Bulk process only if API problem is found e.g. Insufficuent tokens.

= 1.4.12 =
- Performance optimizations in /wp-admin
- Performance optimizations in the Bulk optimization process through the admin UI
- Imrpoved WP CLI Bulk optimizer. If there is an error like "No tokens left", terminate the process completely
- Removed confirm dialog when refreshing optimizer page for insufficient tokens

= 1.4.11 =
- Fix error triggered when activating the plugin

= 1.4.10 =
- Add compatibility with PHP 8
- Add compatibility with WordPress 5.6
- Speed improvements to the 'Custom Folders' module
- Fix fatal error in some occuasions when generating debug report.
- Fix forever loading when "Optimize" button is pressed in Media Library list table screen in certain cases
- Fix file naming problem when '#' is present in file names
- Ignore wp-content/uploads from 'Custom Folders' scan

= 1.4.9 =
- Fix broken WebP images when both file.png.webp and file.webp exists on the server
- Improved WebP settings UI/UX
- Tested on WordPress 5.4

= 1.4.8 =
- Added chunked Media Library scanning for large media libraries to prevent memory exhaustion and timeouts
- Added error reporting for cases when there is fatal error during bulk optimization caused by third party. eg: plugin updates, etc.
- Improved Bulk Optimizer counters
- Improved WPCLI Bulk Optimizer command
- Improved Bulk Folder/File Optimizer
- Improved Bulk Media Library Optimizer
- Improved Bulk NextGen Library Optimizer
- Improved UI out-of-tokens signalization
- Fixed notice title conflicts
- Fixed several typos

= 1.4.7 =
- Fix WebP php warning

= 1.4.6 =
- Added scan filters
- Improved Bulk Optimizer UI (Added confirmation on exit and Cancel button)
- Improved WebP/WP Media Offload support: Detect if WebP image exist on the remote s3 bucket use it in the final markup
- Improved WebP/WP Media Offload support: Added support for installs that use CNAME to mask the s3 bucket url.
- Improved readme

= 1.4.5 =
- Added compatibility for WP 5.3 "BIG Image" threshold. If Auto-Optimize is enabled AND Max Width/Height are set in the options, disable the "BIG Image" treshold
- Fix the auto resize width/height option (was not working correctly)
- Improved Media library table rendering
- Improved logging function. Automatically remove log files bigger than 10 MB

= 1.4.4 =
- Added WP Offload Media (Lite/Pro) support
- Improved WebP support

= 1.4.3 =
- Added file/directory support to `wp megaoptim optimize` command. You can now optimize files and folders by specifying path.
- Improved file optimizer
- Improved UI

= 1.4.2 =
- Compatibility with WordPress 5.3
- Improved image URL validation functionality
- Improved public site detection functionality.

= 1.4.1 =

- Added support for l18n standard
- Added notice to suggest to the user to switch to list mode in the Media Library screen
- Improved the welcome instructions screen
- Updated usage message for wp megaoptim restore command to match the command
- Refactored ajax security checks and added some more security hardening (thanks pluginvulnerabilities for the suggestions)
- Added improved error reporting in the Media Library list mode

= 1.4.0 =
- Added WP CLI command for optimizing images eg. `wp megaoptim optimize <ID> [--force] [--level=<option>]`
- Added WP CLI command for restoring images eg. `wp megaoptim restore <IDorAll>`
- Added WP CLI command for setting api key. eg. `wp megaoptim set_api_key <api_key>`
- Added WP CLI command for querying api tokens eg. `wp megaoptim info`
- Security Improvements

= 1.3.2 =
- Add detection to skip corrupted/unoptimized files in some unique cases of environments
- Force HTTP 1.1 for now because of problems with some web servers.

= 1.3.1 =
- Fix svn problem

= 1.3.0 =
- Added Support for the Unlimited plan
- Added Support for sub accounts
- Revamped media library optimizer
- Revamped nextgen library optimizer
- Revamped retina support & WP Retina @2x integration
- Improved optimization speed by at least 50%
- Improved WebP support
- Improved Settings pages
- Improved WPEngine compatibility
- Fixed Cloudflare purge compatibility
- Fixed problem when auto-optimizing nextgen gallery images
- Improved stats display when optimizing single attachment in Media Library table
- Added detection for the child theme folder in 'Local Folders' optimizer
- Fixed several typos

= 1.2.1 =
- Improved Admin pages
- Improved WebP support

= 1.2.0 =
* Added WebP Support
* Removed MegaOptim from main menu and added the settings part in "Settings" and the optimizer in "Media" tabs.
* Improved settings data persistence
* Improved security
* Improved Admin UI
* Fixed PHP Strict Standards warning
* Fixed saving max dimensions value
* Revamped database upgrade process
