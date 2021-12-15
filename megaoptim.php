<?php
/*–
Plugin Name: MegaOptim Image Optimizer
Plugin URI: https://megaoptim.com/tools/wordpress
Description: Compress and optimize your WordPress images and save bandwidth, disk space and improve your pagespeed/lighthouse & seo score. Integrates seamlessly with NextGen, WP Retina 2x, Envira and other media/image plugins.
Author: MegaOptim
Author URI: https://megaoptim.com
Version: 1.4.18-dev
Text Domain: megaoptim-image-optimizer
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access is not allowed.' );
}

define( 'WP_MEGAOPTIM_VER', '1.4.18' );
define( 'WP_MEGAOPTIM_PATH', plugin_dir_path( __FILE__ ) );
define( 'WP_MEGAOPTIM_URL', plugin_dir_url( __FILE__ ) );
define( 'WP_MEGAOPTIM_BASENAME', plugin_basename( __FILE__ ) );
define( 'WP_MEGAOPTIM_PLUGIN_FILE_PATH', __FILE__ );
define( 'WP_MEGAOPTIM_INC_PATH', trailingslashit( WP_MEGAOPTIM_PATH . 'includes' ) );

require_once( WP_MEGAOPTIM_INC_PATH . 'loader.php' );
