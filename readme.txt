=== MegaOptim Image Optimizer ===
Contributors: megaoptim, darkog
Tags: image optimizer, image compression, pagespeed, compress, optimize images, image optimiser, image compressor, optimize images, optimize jpg, compress jpg, compress png, compress retina
Requires at least: 3.6
Tested up to: 5.3
Requires PHP: 5.3
Stable tag: 1.4.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Optimize and compress your images to speed up your site and boost your SEO.

== Description ==

**MegaOptim is image optimization plugin/service and that is easy to use, stable and actively maintained by dedicated team of experts. :)**

The plugin uses minimal resources on your server and all the heavy lifting is done by our API service on our servers. No binaries will be ever installed on your server that will slowdown your site.

= What is image optimization / compression and why it is important? =

Image Optimization is delivering the high-quality images in the right format, dimension, size, and resolution while keeping the smallest possible size.

= How much does MegaOptim cost? =

The service comes with **500 FREE images/tokens per month for everyone**. We also have <strong>unlimited plan</strong> at only $9.99 that will help us maintain the service. Check our pricing <a href="https://megaoptim.com/pricing" target="_blank">here</a>.

Want more tokens for free? Help us spread MegaOptim and earn 120 tokens per sign up and 300 tokens once customer becomes paid customer. Our Referral program can be found in the <a target="_blank" href="https://app.megaoptim.com/">dashboard area</a>.

= What features does MegaOptim offer? =

- Bulk optimization for the Media Library, NextGen Libaries, MediaPress, Envira and other
- Bulk optimization of Custom folders by your choice in your hosting account
- WP CLI Support for optimization and restore (either bulk or single attachment)
- Compatible with WP Offload Media (formerly WP Offload S3)
- WebP Support (Enable/Disable WebP. Convert images to WebP upon optimization and automatically replace the content images with WebP version if available)
- Backups & Restore Support. Select which attachment to backup
- Sub Accounts Support (Manage your client sites and have separate statistics and subaccount/api key for each. Transfer tokens from onetime or monthly balances between accounts.)
- WordPress Multisite Support. Setup the plugin separately on the sites you want
- Integrates with WP Retina 2x(Free and Pro). It will optimize all the retina images too
- Integrates with CloudFlare. Purges CloudFlare image cache once specific image is optimized
- Option to select which thumbnails to optimize
- Option to enable/disable auto conversion from CMYK to RGB (A better color profile for web)
- Option to preserve EXIF data(location, time, camera model) or remove it upon the optimization
- Option to specify maximum dimensions of optimized images. You may not need plugins like Imansity anymore
- Works fine on Public, Localhost or Password Protected via htpasswd
- Uses progressive JPEG for the larger images to display them faster in the browser
- Compatible with WP Engine, SiteGround and other hosting providers
- Compatible with Windows/UNIX(Linux, OSx) hosting environments
- Images that are optimized and the total saved size is less than 5% are free, no tokens are charged
- Optimization stats available at the <a target="_blank" href="https://app.megaoptim.com/dashboard">dashboard area</a>
- Dedicated support team ready to help you 24/7.

= Have a question? Contact us! =

* Email  [<a href="https://megaoptim.com/contact">Click Here</a>]
* Twitter [<a href="https://twitter.com/MegaoptimO">Click Here</a>]
* Facebook [<a href="https://www.facebook.com/megaoptimio">Click Here</a>]

= Are you a developer? =

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

= Can i use the same API Key on multiple websites? =
    Absolutely. You can use your API key on as many websites as you want or you can also create sub accounts for each of the sites you manage if you want to keep the things separate or bill your clients separate.

= Can i manage multiple sites separately with sub-accounts?
    You can create sub-account for your client and transfer tokens to the sub-account balance. This way your client will have separate account and api key and you will be managing it. You have the option to add or remove api tokens from it.

= Is the plugin compatible with WP Offload Media? =
    Yes. If you have configured WP Offload Media no further configurations are needed.

= Can i backup and restore the images? =
    Yes, MegaOptim have option to backup the images and it is enabled by default. You can alawys restore the original images if there is backup.

= What image formats can be optimized? =
    MegaOptim supports JPEG, PNG, GIF (animated or non animated) and WebP

= Do i need separate tokens for WebP Images?
    No, WebP versions are free of charge!

= Is there WP CLI commands to run MegaOptim optimizer or restore?
    Yes, Please check the <a href="https://megaoptim.com/tools/wordpress/">documentation</a>.

= Does the plugin optimizes the images on the site server and overloads my server? =
    The plugin does not install libraries for compression. Each image is sent to our servers and optimized by them to avoid resource drain on the website server.

= What payment methods do you support? =
    We support both PayPal and Credit Card via Paddle. The payments can be recurring or onetime.

= What happens if i stop using or deactivate MegaOptim plugin? =
    Nothing, your images will remain optimized, if you used the WebP feature the site won't serve WebP any longer.

= What are the pricing packages? =
    We do both one-time and monthly. Please check out <a href="https://megaoptim.com/pricing">here</a>.

= Can i cancel my subscription? =
    Absolutely! You can cancel your subscription whenever you want.

= Do you have an API? =
   Yes, please check out <a href="https://megaoptim.com/docs/api">here</a> for more documentation.

= Will MegaOptim work with CloudFlare? =
    Yes! You need to use the CloudFlare plugin with correct credentials or setup credentials in the Settings > Advanced menu. If the credentials are setup correctly, the plugin will automatically purge from the CloudFlare cached images when they are optimized with MegaOptim.

= I have problem, the plugin won't work. =
   Please <a target="_blank" href="https://megaoptim.com/contact">contact us</a> as soon as possible and we can assist you. We even have a live chat.


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
10.WP CLI - Bulk Restore command

== Changelog ==

= 1.4.5 =
- Added compatibility for WP 5.3 "BIG Image" threshold. If Auto-Optimize is enabled AND Max Width/Height are set in the options, disable the "BIG Image" treshold.
- Fix the auto resize width/height option (was not working correctly)
- Improved Media library table rendering

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