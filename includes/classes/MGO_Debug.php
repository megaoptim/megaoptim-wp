<?php
/**
 * Created by PhpStorm.
 * User: darko
 * Date: 7/11/19
 * Time: 1:28 PM
 */

class MGO_Debug {

	public function __construct() {}

	/**
	 * Get debug report
	 * @return array
	 */
	public function generate_report() {

		global $wpdb, $wp_version;

		$tokens = 0;
		$valid  = 0;

		try {
			$profile = new MGO_Profile();
			$tokens  = $profile->get_tokens_count();
			$valid   = $profile->is_connected();
		} catch ( MGO_Exception $e ) {

		}

		$uploads_dir  = wp_upload_dir();
		$ini_settings = array(
			'post_max_size',
			'upload_max_filesize',
			'memory_limit',
			'max_execution_time',
			'max_input_vars',
		);

		$theme_dir = get_template_directory();

		// Cached size
		$key                     = md5( $uploads_dir['basedir'] );
		$wp_content_uploads_size = megaoptim_cache_get( $key );
		if ( false === $wp_content_uploads_size ) {
			$wp_content_uploads_size = megaoptim_get_dir_size( $uploads_dir['basedir'] );
			megaoptim_cache_set( $key, $wp_content_uploads_size, 5 * 60 );
		}

		$curl_version = function_exists( 'curl_version' ) ? curl_version() : null;

		if ( ! is_null( $curl_version ) ) {
			$curl_info = 'Yes, cURL version: ' . $curl_version['version'];
		} else {
			$curl_info = 'No';
		}

		$report = array(
			// MegaOptim
			'API Status'                         => megaoptim_ping_api() ? 'Online' : 'Offline',
			'API Key'                            => $valid ? 'Valid' : 'Invalid',
			'API Balance'                        => $tokens == -1 ? 'Unlimited' : $tokens,
			'API PHP Client Version'             => MegaOptim\Optimizer::VERSION,
			'Plugin Version'                     => WP_MEGAOPTIM_VER,
			// WP
			'WordPress Debug'                    => defined( 'WP_DEBUG' ) ? ( WP_DEBUG ? 'Yes' : 'No' ) : 'No',
			'WordPress Version'                  => $wp_version,
			'WordPress Media Library Directory'  => wp_is_writable( $uploads_dir['basedir'] ) ? 'Writable' : 'Not Writable',
			'WordPress Theme Directory'          => wp_is_writable( $theme_dir ) ? 'Writable' : 'Not Writable',
			'WordPress Memory Limit'             => defined( 'WP_MEMORY_LIMIT' ) ? WP_MEMORY_LIMIT : 'Uknown',
			'WordPress Multisite'                => is_multisite() ? 'Yes' : 'No',
			'WordPress Language'                 => get_bloginfo( 'language' ),
			'WordPress /wp-content/uploads size' => megaoptim_human_file_size( $wp_content_uploads_size ),
			// Server
			'Operating System'                   => php_uname(),
			'Server Info'                        => isset( $_SERVER['SERVER_SOFTWARE'] ) ? $_SERVER['SERVER_SOFTWARE'] : 'Unknown',
			'PHP Version'                        => phpversion(),
			'MySQL Version'                      => $wpdb->db_version(),
			'cURL Enabled'                       => $curl_info,
			'Multibyte string'                   => ( extension_loaded( 'mbstring' ) ) ? 'Yes' : 'No',
			'Default Timezone'                   => date_default_timezone_get()
		);
		foreach ( $ini_settings as $setting ) {
			if ( ini_get( $setting ) ) {
				$report[ ucfirst( $setting ) ] = ini_get( $setting );
			}
		}

		return $report;
	}

}