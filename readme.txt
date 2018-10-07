=== MegaOptim Image Optimizer ===
Contributors: megaoptim, darkog
Tags: image optimizer, pagespeed, compression, compress, image, compression, optimize, image optimiser, image optimiser, image compression, resize, compress pdf, compress jpg, compress png, image compression, compress retina
Requires at least: 3.6
Tested up to: 4.9.8
Requires PHP: 5.3.0
Stable tag: 1.0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

With MegaOptim you can compress your WordPress images and boost your website load speed and SEO rankings.

== Description ==
**MegaOptim is image optimization plugin/service and a freemium that is easy to use, stable and actively maintained by dedicated team behind.**

Image compression is playing important role in your page load speed, google's pagespeed score and SEO rankings. Having correctly compressed images will give your website faster loading times, save bandwidth and disk space, keep the image quality identical and boost your SEO rankings and that can be achieved with MegaOptim.

The plugin uses minimal resources on your hosting and all the heavy lifting is done by our API service in the cloud, meaning that no binaries will be installed on your server to resize or compress images unlike some other plugins.

The plugin supports all major image formats (JPG, PNG, GIF) and has three different compression levels (lossless, intelligent and ultra).

For most of the websites **intelligent** method is enough, it auto determines the level of compression of the image that works best for the human eye. The **ultra** mode is similar to **intelligent** but goes a bit further to save more space. If you are willing to sacrifice small bits of the image quality then this mode is the way to go. Finally, with the lossless method the service attempts to optimize the images without touching the image quality. It is not recommended method if you are looking for google pagespeed score or page load speed.


**What makes MegaOptim a better choice than the other image compression tools?**

* supports all popular image formats such as JPG, PNG, GIF
* supports three different optimization levels (ultra, intelligent, losses)
* supports optimization of the images automatically on upload for WordPress Media Library, NextGen, MediaPress, Envira and other gallery plugins.
* supports large images, no file size limit.
* supports bulk optimization modes for WordPress Media Library, Custom Folders and NextGen Galleries.
* using custom folders feature optimize any other folder you want in your web hosting account.
* supports WordPress multisite, you can separately setup the plugin on each site in the network.
* supports integration with WordPress Media Screens and NextGen gallery editor screen, in both screens you will have optimize/restroe buttons and stats.
* supports option to choose which regular thumbnails to optimize
* supports WP Retina 2x and separate option where you can choose which retina thumbnails to be optimized
* supports CloudFlare and integrates with CloudFlare plugins, purges(refreshes) CloudFlare image urls after optimization
* supports backups and management for the backups, choose what you want to backup, clean up specific backup when you don't need them, etc.
* option to enable/disable auto convert from CMYK to RGB(A better color profile for web)
* option to choose if you want to preserve the EXIF(location, time, camera model) data - useful option for photographers.
* option to resize images while optimizing to specific maximum width or height (bigger of two)
* option to restore optimized image (if backups available/enabled) - Useful if you want to test the compression results.
* supports basic http authentication, if your website is protected with http password.
* easy to reoptimize the image using different technique (if backups are enabled)
* works seamlessly on localhost ( development environments )
* works great with plugins like Envira, FooGallery, MediaPress, etc.
* debug page - if something is wrong go to Settings > Debug. You can even export it and send it to us to check your configuration.
* supports both HTTP and HTTPS
* uses progressive JPEG for the larger images to display them faster in the browser.
* single api key for multiple sites
* compatible with WP Engine, Siteground and all other hosting providers
* compatible with both Windows/UNIX based servers
* images that are optimized and the total saved size is less than 5% are free, no tokens are charged.
* optimization reports in the plugin or in our <a target="_blank" href="https://megaoptim.com/dashboard">dashboard area</a>
* supports WooCommerce & Easy Digital Downloads, etc
* free optimization credits for non-profit organizations, you can contact us about that.
* dedicated support team, ready to help you 24/7.

**How much does MegaOptim cost?**

Our service comes with 200 images/tokens per month for free. Additional tokens can be purchased starting from $4.99 for 5,500 tokens. Check out our prices <a href="https://megaoptim.com/pricing">here</a>.

Want more tokens for free? Help us spread MegaOptim and earn 120 tokens per sign up and 300 tokens once customer becomes paid customer. Our Referral program can be found in the <a target="_blank" href="https://megaoptim.com/dashboard">dashboard area</a>.

**Have a question? Contact us!**

* Email  [<a href="https://megaoptim.com/contact">Click Here</a>]
* Twitter [<a href="https://twitter.com/MegaoptimO">Click Here</a>]
* Facebook [<a href="https://www.facebook.com/MegaOptim-234727427394835">Click Here</a>]

== Installation ==
1.) Sign up for api key at https://megaoptim.com/register
2.) You will receive your API key in the email address you provided.
3.) Upload the MegaOptim plugin to /wp-content/plugins or install it via the wp-admin interface, finally activate it.
4.) Navigate to wp-admin and in the sidebar menu click on "MegaOptim", hover over and go to "Settings", here you can paste the api key and save the settings.
5.) In the same section check the other settings, change them according to your needs.
6.) You are all set! Begin optimizing by navigating to "MegaOptim" > "Optimizer" ( In the top right corner you can select optimizer )


== Hooks for Developers ==

Click <a target="_blank" href="https://megaoptim.com/tools/wordpress#hooks">here</a> to read more about the actions and filters that are available for developers.


== Frequently Asked Questions ==

= What is API token? =
    One API token is one image. Please note that WordPress generates multiple thumbnails per one image. You will be charged one token for each thumbnail. If the total saved size per image is less than %5 you will not be charged.

= Can i use the same API Key on multiple websites? =
    Absolutely. You can use your API key on as many websites as you want.

= I am using PHP 5.2 but want to still use the plugin?
    MegaOptim plugin supports PHP 5.3 or newer, but no worries. We offer up to one hour support to upgrade your hosting account with newer PHP version if you have access to do so.

= Is the plugin compatible with WP offload S3 and WP Stateless? =
    Not fully compatible, but we are already working and testing this feature. It will be released soon.

= Can i optimize the WordPress Media Library =
    Sure, just go to MegaOptim > Optimizer and in the top right select \"WP Media Library\" and start the process!

= Can i backup the images? =
    Yes, MegaOptim have option to backup the images and it is enabled by default.

= Can i restore the images? =
    If you have backups enabled you can easily restore any compressed image from backup.

= What image formats can be optimized? =
    MegaOptim supports JPEG, PNG, GIF (animated or non animated)

= Does the plugin optimizes the images in cloud or in my website's hosting? =
    MegaOptim send each image to our cloud service and each image is optimized and then downloaded and replaced with the original, if you have backups enabled the image will be backed up additionally.

= What payment methods do you support? =
    We support both PayPal and Credit Card via Paddle

= What happens if i stop using or deactivate MegaOptim plugin? =
    Nothing, your images will remain optimized.

= Do you have one time packages? =
    Yes. Please check out <a href="https://megaoptim.com/pricing">here</a>.

= Do you have monthly packages? =
    Yes. Please check out <a href="https://megaoptim.com/pricing">here</a>.

= Can i cancel my subscription? =
    Absolutely! You can cancel your subscription whenever you want.

= Do you have an API? =
   Yes, please check out <a href="https://megaoptim.com/docs/api">here</a> for more documentation.

= Will MegaOptim work with CloudFlare? =
    Yes! You need to use the CloudFlare plugin with correct credentials or setup credentials in the Settings > Advanced menu. If the credentials are setup correctly, the plugin will automatically purge from the CloudFlare cached images when they are optimized with MegaOptim.

= I have problem, the plugin won't work. =
   Please <a target="_blank" href="https://megaoptim.com/contact">contact us</a> as soon as possible and we can assist you!


== Screenshots ==
1. General Settings
2. Advanced Settings
3. Debug Page
4. Media Library: Optimizer
5. Media Library: Table screen buttons integration
6. Media Library: Edit attachment screen buttons integration
7. Custom Folders: Folder select dialog
8. Custom Folders: Optimizer
9. NextGen Galleries: Optimizer
10. NextGen Galleries: Gallery table screen buttons Integration

== Changelog ==

= 1.0.1 =
* Added megaoptim_after_restore_attachment action hook (Called after attachment is restored.)
* Improved readme and fixed typos

= 1.0.0 =
* Initial release