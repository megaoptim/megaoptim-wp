<?php
#Paths
define( 'WP_MEGAOPTIM_DB_VER', 1000 );
define( 'WP_MEGAOPTIM_INT_MAX', PHP_INT_MAX - 30 );
define( 'WP_MEGAOPTIM_VIEWS_PATH', WP_MEGAOPTIM_PATH . 'includes' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR );
define( 'WP_MEGAOPTIM_ASSETS_URL', WP_MEGAOPTIM_URL . 'assets/' );
define( 'WP_MEGAOPTIM_LIBRARIES_PATH', WP_MEGAOPTIM_INC_PATH . 'libraries' . DIRECTORY_SEPARATOR );
define( 'WP_MEGAOPTIM_PHP_MINIMUM', '5.3' );

#General
define( 'MEGAOPTIM_PAGE_BULK_OPTIMIZER', 'megaoptim_bulk_optimizer' );
define( 'MEGAOPTIM_PAGE_SETTINGS', 'megaoptim_settings' );
define( 'MEGAOPTIM_TYPE_MEDIA_ATTACHMENT', 'wp' );
define( 'MEGAOPTIM_TYPE_FILE_ATTACHMENT', 'localfiles' );
define( 'MEGAOPTIM_TYPE_NEXTGEN_ATTACHMENT', 'nextgenv2' );
define( 'MEGAOPTIM_MODULE_MEDIA_LIBRARY', 'wp-media-library' );
define( 'MEGAOPTIM_MODULE_FOLDERS', 'folders' );
define( 'MEGAOPTIM_MODULE_NEXTGEN', 'nextgen' );
define( 'MEGAOPTIM_MODULE_WEBP_CONVERTER', 'webp-converter' );
define( 'MEGAOPTIM_CACHE_PREFIX', 'megaoptim' );

#API Config
define( 'WP_MEGAOPTIM_REGISTER_URL', 'https://app.megaoptim.com/register' );
define( 'WP_MEGAOPTIM_DASHBOARD_URL', 'https://app.megaoptim.com/' );
define( 'WP_MEGAOPTIM_REGISTER_API_URL', 'https://app.megaoptim.com/api/register' );
define( 'WP_MEGAOPTIM_API_BASE_URL', 'https://api.megaoptim.com' );
define( 'WP_MEGAOPTIM_API_VERSION', 'v1' );
define( 'WP_MEGAOPTIM_API_URL', WP_MEGAOPTIM_API_BASE_URL . '/' . WP_MEGAOPTIM_API_VERSION );
define( 'WP_MEGAOPTIM_API_PROFILE', WP_MEGAOPTIM_API_URL . '/users/info' );
define( 'WP_MEGAOPTIM_API_HEADER_KEY', 'X-API-KEY' );

# Time
define( 'MEGAOPTIM_ONE_MINUTE_IN_SECONDS', 60 );
define( 'MEGAOPTIM_FIVE_MINUTES_IN_SECONDS', 5 * MEGAOPTIM_ONE_MINUTE_IN_SECONDS );
define( 'MEGAOPTIM_TEN_MINUTES_IN_SECONDS', 10 * MEGAOPTIM_ONE_MINUTE_IN_SECONDS );
define( 'MEGAOPTIM_ONE_HOUR_IN_SECONDS', 60 * MEGAOPTIM_ONE_MINUTE_IN_SECONDS );
define( 'MEGAOPTIM_HALF_HOUR_IN_SECONDS', 30 * MEGAOPTIM_ONE_MINUTE_IN_SECONDS );
