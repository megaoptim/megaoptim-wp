<?php
/*
Plugin Name: MegaOptim Image Optimizer
Plugin URI: https://megaoptim.com/tools/wordpress
Description: MegaOptim is image compression plugin that optimizes your images in the cloud using intelligent image compression methods to save as much space as possible while keeping the quality almost identical. It's compatible with NextGen Gallery, MediaPress, WP Retina 2x and many other gallery plugins.
Author: MegaOptim
Author URI: https://megaoptim.com
Version: 1.1.0
*/

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access is not allowed.' );
}

define( 'WP_MEGAOPTIM_PATH', plugin_dir_path( __FILE__ ) );
define( 'WP_MEGAOPTIM_URL', plugin_dir_url( __FILE__ ) );
define( 'WP_MEGAOPTIM_VER', '1.1.0' );
define( 'WP_MEGAOPTIM_PLUGIN_FILE_PATH', __FILE__ );
define( 'WP_MEGAOPTIM_DB_VER', 1000 );
define( 'WP_MEGAOPTIM_INT_MAX', PHP_INT_MAX - 30 );

define( 'WP_MEGAOPTIM_BASENAME', plugin_basename( __FILE__ ) );
define( 'WP_MEGAOPTIM_VIEWS_PATH', WP_MEGAOPTIM_PATH . 'includes' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR );
define( 'WP_MEGAOPTIM_ASSETS_URL', WP_MEGAOPTIM_URL . 'assets/' );
define( 'WP_MEGAOPTIM_INC_PATH', WP_MEGAOPTIM_PATH . 'includes' . DIRECTORY_SEPARATOR );
define( 'WP_MEGAOPTIM_LIBRARIES_PATH', WP_MEGAOPTIM_INC_PATH . 'libraries' . DIRECTORY_SEPARATOR );

define( 'WP_MEGAOPTIM_REGISTER_URL', 'https://megaoptim.com/register' );
define( 'WP_MEGAOPTIM_DASHBOARD_URL', 'https://megaoptim.com/dashboard' );
define( 'WP_MEGAOPTIM_REGISTER_API_URL', 'https://megaoptim.com/api/register' );
define( 'WP_MEGAOPTIM_API_BASE_URL', 'https://api.megaoptim.com' );
define( 'WP_MEGAOPTIM_API_VERSION', 'v1' );
define( 'WP_MEGAOPTIM_API_URL', WP_MEGAOPTIM_API_BASE_URL . '/' . WP_MEGAOPTIM_API_VERSION );
define( 'WP_MEGAOPTIM_API_PROFILE', WP_MEGAOPTIM_API_URL . '/users/info' );
define( 'WP_MEGAOPTIM_API_HEADER_KEY', 'X-API-KEY' );

define( 'MEGAOPTIM_ONE_MINUTE_IN_SECONDS', 60 );
define( 'MEGAOPTIM_FIVE_MINUTES_IN_SECONDS', 5 * MEGAOPTIM_ONE_MINUTE_IN_SECONDS );
define( 'MEGAOPTIM_TEN_MINUTES_IN_SECONDS', 10 * MEGAOPTIM_ONE_MINUTE_IN_SECONDS );
define( 'MEGAOPTIM_ONE_HOUR_IN_SECONDS', 60 * MEGAOPTIM_ONE_MINUTE_IN_SECONDS );
define( 'MEGAOPTIM_HALF_HOUR_IN_SECONDS', 30 * MEGAOPTIM_ONE_MINUTE_IN_SECONDS );

require_once( WP_MEGAOPTIM_LIBRARIES_PATH . 'megaoptim-php' . DIRECTORY_SEPARATOR . 'loadnoncomposer.php' );
require_once( WP_MEGAOPTIM_INC_PATH . 'loader.php' );




