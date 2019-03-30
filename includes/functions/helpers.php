<?php
/********************************************************************
 * Copyright (C) 2018 MegaOptim (https://megaoptim.com)
 *
 * This file is part of MegaOptim Image Optimizer
 *
 * MegaOptim Image Optimizer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * MegaOptim Image Optimizer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MegaOptim Image Optimizer. If not, see <https://www.gnu.org/licenses/>.
 **********************************************************************/

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access is not allowed.' );
}

/**
 * Check if the WP instance is connected to the MegaOptim.com API
 *
 * @return bool|MGO_Profile
 */
function megaoptim_is_connected() {
	try {
		$profile = new MGO_Profile();

		return $profile->is_connected();
	} catch ( MGO_Exception $e ) {
		return false;
	}
}

/**
 * Returns the megaoptim path
 * @return array|string
 */
function megaoptim_get_tmp_path() {
	$log_file_path = wp_upload_dir();
	$log_file_path = $log_file_path['basedir'] . DIRECTORY_SEPARATOR . 'megaoptim';

	return $log_file_path;
}

/**
 * Used to write contents into file provided by parameters
 * @param $file string
 * @param $contents string
 */
function megaoptim_write($file, $contents) {
	if ( file_exists( $file ) ) {
		$fp = fopen( $file, 'a' );
		fwrite( $fp, $contents . "\n" );
	} else {
		$fp = fopen( $file, 'w' );
		fwrite( $fp, $contents . "\n" );
	}
	fclose( $fp );
}

/**
 * Makes specific dir secure.
 * @param $dir
 * @param bool $noindex
 */
function megaoptim_protect_dir($dir, $noindex = true) {
	if(!is_dir($dir)) {
		@mkdir($dir);
	}
	// Create empty index file
	if(is_dir($dir)) {
		$index_path = $dir . DIRECTORY_SEPARATOR . 'index.html';
		if(!file_exists($index_path)) {
			@touch($index_path);
		}
	}
	// Create noindex to the directory for some hosting environemnts.
	if($noindex) {
		$htaccess_path = $dir . DIRECTORY_SEPARATOR . '.htaccess';
		if(!file_exists($htaccess_path)) {
			$contents = '<IfModule headers_module>
Header set X-Robots-Tag "noindex"
</IfModule>';
			megaoptim_write($htaccess_path, $contents);
		}
	}
}

/**
 * Wrapper for writing the interactions to /wp-content/uploads/ file
 *
 * @param        $message
 * @param string $filename
 */
function megaoptim_log( $message, $filename = "debug.log" ) {
	$log_file_dir = megaoptim_get_tmp_path();
	megaoptim_protect_dir($log_file_dir);
	if ( ! file_exists( $log_file_dir ) ) {
		@mkdir( $log_file_dir );
	}
	$log_file_path = $log_file_dir . DIRECTORY_SEPARATOR . $filename;
	// TODO: Remove after some time
	$old_file_path = $log_file_dir . DIRECTORY_SEPARATOR . 'debug.txt';
	if(file_exists($old_file_path)) {
		@rename($old_file_path, $log_file_path);
	}
	// END TODO
	if ( ! is_string( $message ) && ! is_numeric( $message ) ) {
		ob_start();
		megaoptim_dump( $message );
		$message = ob_get_clean();
	}
	megaoptim_write($log_file_path, $message);
}

/**
 * @param $data
 */
function megaoptim_dump( $data ) {
	$prev = ini_get( 'xdebug.overload_var_dump' );
	if ( ! empty( $prev ) ) {
		ini_set( "xdebug.overload_var_dump", "off" );
	}
	var_dump( $data );
	if ( ! empty( $prev ) ) {
		ini_set( "xdebug.overload_var_dump", $prev );
	}
}

/**
 * Check if API is online
 * @return bool
 */
function megaoptim_ping_api() {
	$agent = "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_8; pt-pt) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27";
	$ch    = curl_init();
	curl_setopt( $ch, CURLOPT_URL, WP_MEGAOPTIM_API_BASE_URL );
	curl_setopt( $ch, CURLOPT_USERAGENT, $agent );
	curl_setopt( $ch, CURLOPT_NOBODY, true );
	curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_VERBOSE, false );
	curl_setopt( $ch, CURLOPT_TIMEOUT, 5 );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
	curl_exec( $ch );
	$httpcode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
	curl_close( $ch );
	if ( $httpcode >= 200 && $httpcode < 300 ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Returns the directory size
 *
 * @param $path
 *
 * @return int
 */
function megaoptim_get_dir_size( $path ) {
	$bytestotal = 0;
	$path       = realpath( $path );
	if ( $path !== false && $path != '' && file_exists( $path ) ) {
		foreach ( new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $path, FilesystemIterator::SKIP_DOTS ) ) as $object ) {
			$bytestotal += $object->getSize();
		}
	}

	return $bytestotal;
}

/**
 * Returns human readable file size from bytes
 *
 * @param $size
 * @param string $unit
 * @param bool $include_unit
 *
 * @return string
 */
function megaoptim_human_file_size( $size, $unit = "", $include_unit = true ) {
	if ( ( ! $unit && $size >= 1 << 30 ) || $unit == "GB" ) {
		$formatted = number_format( $size / ( 1 << 30 ), 2 );
		if ( $include_unit ) {
			$formatted .= " GB";
		}

		return $formatted;
	}
	if ( ( ! $unit && $size >= 1 << 20 ) || $unit == "MB" ) {
		$formatted = number_format( $size / ( 1 << 20 ), 2 );
		if ( $include_unit ) {
			$formatted .= " MB";
		}

		return $formatted;
	}
	if ( ( ! $unit && $size >= 1 << 10 ) || $unit == "KB" ) {
		$formatted = number_format( $size / ( 1 << 10 ), 2 );
		if ( $include_unit ) {
			$formatted .= " KB";
		}

		return $formatted;
	}

	return number_format( $size ) . " bytes";
}


/**
 * Convert bytes to the unit specified by the $to parameter.
 *
 * @param integer $bytes The filesize in Bytes.
 * @param string $to The unit type to convert to. Accepts K, M, or G for Kilobytes, Megabytes, or Gigabytes, respectively.
 * @param integer $decimal_places The number of decimal places to return.
 *
 * @return integer Returns only the number of units, not the type letter. Returns 0 if the $to unit type is out of scope.
 *
 */
function megaoptim_convert_bytes_to_specified( $bytes, $to, $decimal_places = 1 ) {
	$formulas = array(
		'KB' => number_format( $bytes / 1024, $decimal_places ),
		'MB' => number_format( $bytes / 1048576, $decimal_places ),
		'GB' => number_format( $bytes / 1073741824, $decimal_places )
	);

	return isset( $formulas[ $to ] ) ? $formulas[ $to ] : 0;
}


/**
 * Get debug report
 * @return array
 */
function megaoptim_get_debug_report() {

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
		'API Remaining Tokens'               => $tokens,
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

/**
 * Check if the url is internal one
 *
 * @param $url
 *
 * @return bool
 */
function megaoptim_is_internal_url( $url ) {
	$url_host      = wp_parse_url( $url, PHP_URL_HOST );
	$site_url_host = wp_parse_url( get_site_url(), PHP_URL_HOST );

	return $url_host === $site_url_host;
}


/**
 * Returns placeholder
 * @return string
 */
function megaoptim_get_placeholder() {
	return WP_MEGAOPTIM_ASSETS_URL . 'img/placeholder.jpg';
}

/**
 * Check if substring is contained in string
 *
 * @param $str
 * @param $substring
 *
 * @return bool
 */
function megaoptim_contains( $str, $substring ) {
	return ( strpos( $str, $substring ) !== false );
}


/**
 * Returns user agent
 * @return string
 */
function megaoptim_internal_async_task_user_agent() {
	$ua = 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:61.0) Gecko/' . time() . ' Firefox/47.0 Mozilla/5.0 (Macintosh; Intel Mac OS X x.y; rv:42.0) Gecko/20100101 Firefox/42.0';

	return $ua;
}


/**
 * Is ip private?
 *
 * TODO: Ipv6 support
 *
 * @param $ip
 *
 * @return bool
 */
function megaoptim_is_ip_private( $ip ) {
	$result = filter_var(
		$ip,
		FILTER_VALIDATE_IP,
		FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
	);
	if ( ! $result ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Check if WordPress can be accessed from public? Is this site running on local host?
 *
 * TODO: Ipv6 support
 *
 * @return bool
 */
function megaoptim_is_wp_accessible_from_public() {

	if ( ! $is_accessible_from_public = megaoptim_cache_get( 'is_accessible_from_public' ) ) {
		$parts = parse_url( site_url() );
		$host  = $parts['host'];
		$ip    = @gethostbyname( $host );
		if ( $ip === false ) {
			$is_accessible_from_public = false;
		} else {
			$is_accessible_from_public = ! megaoptim_is_ip_private( $ip );
		}
		megaoptim_cache_set( 'is_accessible_from_public', $is_accessible_from_public, MEGAOPTIM_ONE_HOUR_IN_SECONDS );
	}

	return apply_filters( 'megaoptim_public_site', $is_accessible_from_public );
}

/**
 * Returns the megaoptim backup dir
 * @return string
 */
function megaoptim_get_backup_dir() {
	return wp_normalize_path( megaoptim_get_tmp_path() . DIRECTORY_SEPARATOR . 'backup' );
}


/**
 * Returns the media library backup dir
 * @return string
 */
function megaoptim_get_ml_backup_dir() {
	$backup_dir = wp_normalize_path( megaoptim_get_backup_dir() . DIRECTORY_SEPARATOR . MGO_MediaAttachment::TYPE );

	return apply_filters( 'megaoptim_ml_backup_dir', $backup_dir );
}


/**
 * Returns the media library backup dir
 * @return string
 */
function megaoptim_get_files_backup_dir() {
	$backup_dir = wp_normalize_path( megaoptim_get_backup_dir() . DIRECTORY_SEPARATOR . MGO_LocalFileAttachment::TYPE );

	return apply_filters( 'megaoptim_files_backup_dir', $backup_dir );
}

/**
 * Returns the backup path for media library attachment by ID
 *
 * @param $id
 * @param $path
 * @param null $size
 *
 * @return string
 */
function megaoptim_get_ml_attachment_backup_path( $id, $path, $size = null ) {
	$ext         = pathinfo( $path, PATHINFO_EXTENSION );
	$backup_path = megaoptim_get_ml_backup_dir() . DIRECTORY_SEPARATOR . $id;
	if ( ! is_null( $size ) ) {
		$backup_path = $backup_path . '-' . $size;
	}
	$backup_path = $backup_path . '.' . $ext;

	return wp_normalize_path( $backup_path );
}

/**
 * Returns the backup path for  next gen attachment by ID
 *
 * @param $id
 * @param $path
 *
 * @return string
 */
function megaoptim_get_files_attachment_backup_path( $id, $path ) {
	$ext         = pathinfo( $path, PATHINFO_EXTENSION );
	$backup_path = megaoptim_get_files_backup_dir() . DIRECTORY_SEPARATOR . $id . ".{$ext}";

	return wp_normalize_path( $backup_path );
}

/**
 * Return the query parameters.
 * @return array
 */
function megaoptim_get_allowed_query_parameters() {
	return array(
		'compression',
		'cmyktorgb',
		'keep_exif',
		'max_width',
		'max_height',
	);
}

/**
 * Check if this is the folders module
 * @return bool
 */
function megaoptim_is_folders_module() {
	return isset( $_GET['module'] ) && $_GET['module'] === 'folders';
}


/**
 * Check if this is the nextgen module
 * @return bool
 */
function megaoptim_is_nextgen_module() {
	return isset( $_GET['module'] ) && $_GET['module'] === 'nextgen';
}

/**
 * Check if this is the wp media library module
 * @return bool
 */
function megaoptim_is_wp_module() {
	return ! isset( $_GET['module'] ) || ( isset( $_GET['module'] ) && $_GET['module'] === 'wp-media-libray' );
}

/**
 * Return the WordPress root path.
 * @return mixed
 */
function megaoptim_get_wp_root_path() {
	return $_SERVER['DOCUMENT_ROOT'];
}

/**
 * Array except
 *
 * @param $arr
 * @param $keys
 *
 * @return array
 */
function megaoptim_array_except( $arr, $keys ) {
	$new = array();
	foreach ( $arr as $key => $value ) {
		if ( ! in_array( $key, $keys ) ) {
			$new[ $key ] = $value;
		}
	}

	return $new;
}

/**
 * Check if the dir should be exclued from the directory tree.
 *
 * @param $dir
 *
 * @return bool
 */
function megaoptim_is_excluded( $dir ) {
	$upload_dir      = wp_upload_dir();
	$base_upload_dir = $upload_dir['basedir'];
	$parent_dir      = dirname( $dir );
	$wp_content      = substr( $base_upload_dir, 0, strpos( $base_upload_dir, "wp-content" ) );

	// TODO: Better multisite support

	if ( $base_upload_dir === $parent_dir ) {
		$excluded = array(
			'megaoptim',
			'CheetahoBackups',
			'ShortpixelBackups',
			'mediapress',
			'woocommerce_uploads',
			'wc-logs'
		);
		for ( $i = 2000; $i < 2100; $i ++ ) {
			array_push( $excluded, $i );
		}
		if ( in_array( megaoptim_basename( $dir ), $excluded ) ) {
			return true;
		}
	} else if ( megaoptim_get_wp_root_path() === $parent_dir ) {
		// we are in /
		$excluded = array(
			'wp-includes',
			'wp-admin',
			".idea"
		);
		if ( in_array( megaoptim_basename( $dir ), $excluded ) ) {
			return true;
		}
	} else if ( $wp_content === $parent_dir ) {
		// we are in /wp-content
		// TODO: If any?
	}

	return false;
}

/**
 * Returns true if url is valid
 *
 * @param $url
 *
 * @return bool
 */
function megaoptim_is_url( $url ) {
	return \MegaOptim\Tools\URL::validate($url);
}

/**
 * Round decimal point
 *
 * @param $number
 * @param $precision
 *
 * @return float|int
 */
function megaoptim_round( $number, $precision ) {
	$fig = pow( 10, $precision );

	return ( ceil( $number * $fig ) / $fig );
}

/**
 * Returns megaoptim view
 *
 * @param $file
 * @param array $data
 * @param string $extension
 *
 * @return string
 */
function megaoptim_get_view( $file, $data = array(), $extension = '' ) {
	if ( $extension === '' ) {
		$extension = 'php';
	} else if ( substr( $extension, 0, 1 ) === '.' ) {
		$extension = substr( $extension, 1 );
	}
	$file = WP_MEGAOPTIM_VIEWS_PATH . DIRECTORY_SEPARATOR . $file . '.' . $extension;
	if ( file_exists( $file ) ) {
		ob_start();
		if ( ! empty( $data ) ) {
			extract( $data );
		}
		include $file;

		return ob_get_clean();
	}

	return '';
}

/**
 * eturns megaoptim view
 *
 * @param $file
 * @param array $data
 * @param string $extension
 */
function megaoptim_view( $file, $data = array(), $extension = '' ) {
	echo megaoptim_get_view( $file, $data, $extension );
}


/**
 * Initiates async task
 *
 * @param $data
 */
function megaoptim_async_task( $data ) {
	$args = array(
		'timeout'   => 3,
		'blocking'  => false,
		'body'      => $data,
		'cookies'   => isset( $_COOKIE ) && is_array( $_COOKIE ) ? $_COOKIE : array(),
		'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
	);
	/**
	 * Filter the arguments for the non-blocking request.
	 *
	 * @param array $args
	 */
	$args = apply_filters( 'megaoptim_async_task_args', $args );
	wp_remote_post( admin_url( 'admin-ajax.php' ), $args );
}


/**
 * Raise the WP Memory limit.
 */
function megaoptim_raise_memory_limit() {
	if ( function_exists( 'wp_raise_memory_limit' ) ) {
		wp_raise_memory_limit();
	}
}

/**
 * Check if dir contains images
 *
 * @param $path
 *
 * @return bool
 */
function megaoptim_dir_contains_images( $path ) {
	$files = glob( $path . DIRECTORY_SEPARATOR . "*.{jpg,jpeg,png,gif}", GLOB_BRACE );

	return ! empty( $files );
}

/**
 * Check if dir contains images
 *
 * @param $dir
 *
 * @return bool
 */
function megaoptim_dir_contains_children( $dir ) {
	$result = false;
	if ( $dh = opendir( $dir ) ) {
		while ( ! $result && ( $file = readdir( $dh ) ) !== false ) {
			$result = $file !== "." && $file !== "..";
		}

		closedir( $dh );
	}

	return $result;
}


/**
 * Get the dir contents recursively.
 *
 * @param $dir
 *
 * @return array
 */
function megaoptim_find_images( $dir ) {
	$direcotry_iterator = new RecursiveDirectoryIterator( $dir );
	$iterator           = new RecursiveIteratorIterator( $direcotry_iterator );
	$r_iterator         = new RegexIterator( $iterator, '/^.+(.jpe?g|.png|.gif)$/i', RecursiveRegexIterator::GET_MATCH );
	$images             = array();
	foreach ( $r_iterator as $image ) {
		array_push( $images, $image[0] );
	}

	return $images;
}

/**
 * Custom basename function. With planned multibyte enhancements in future.
 *
 * @param $str
 * @param string $suffix
 *
 * @return string
 */
function megaoptim_basename( $str, $suffix = '' ) {
	return wp_basename( $str, $suffix );
}

/**
 * Multibyte Basename support
 * @param $path
 * @param bool $suffix
 *
 * @return bool|mixed|string
 */
function megaoptim_mb_basename( $path, $suffix = false ) {
	$Separator = " qq ";
	$qqPath    = preg_replace( "/[^ ]/u", $Separator . "\$0" . $Separator, $path );
	if ( ! $qqPath ) { //this is not an UTF8 string!! Don't rely on basename either, since if filename starts with a non-ASCII character it strips it off
		$fileName = end( explode( DIRECTORY_SEPARATOR, $path ) );
		$pos      = strpos( $fileName, $suffix );
		if ( $pos !== false ) {
			return substr( $fileName, 0, $pos );
		}

		return $fileName;
	}
	$suffix = preg_replace( "/[^ ]/u", $Separator . "\$0" . $Separator, $suffix );
	$Base   = megaoptim_basename( $qqPath, $suffix );
	$Base   = str_replace( $Separator, "", $Base );

	return $Base;
}


/**
 * The attachment buttons?
 *
 * @param MGO_NextGenAttachment|MGO_MediaAttachment|MGO_LocalFileAttachment $attachment
 *
 * @return string
 */
function megaoptim_get_attachment_buttons( $attachment ) {
	return megaoptim_get_view( 'misc/buttons-ml', array( 'data' => $attachment ) );
}

/**
 * Optimizes media library attachment in background
 *
 * @param $attachment_id
 * @param array $metadata
 * @param array $params
 */
function megaoptim_async_optimize_attachment( $attachment_id, $metadata = array(), $params = array() ) {
	$params = array(
		'action'        => 'megaoptim_async_optimize_ml_attachment',
		'_nonce'        => wp_create_nonce( 'megaoptim_async_optimize_ml_attachment' . '_' . $attachment_id ),
		'attachment_id' => $attachment_id,
		'params'        => $params
	);
	if ( is_array( $metadata ) && ! empty( $metadata ) ) {
		$params['metadata'] = $metadata;
	}
	megaoptim_async_task( $params );
}


/**
 * Check if autoptimize is enabled.
 * @return bool
 */
function megaoptim_is_auto_optimize_enabled() {
	$auto_optimize = MGO_Settings::instance()->get( MGO_Settings::AUTO_OPTIMIZE );

	return $auto_optimize == 1;
}

/**
 * Remove file name from url
 * eg. http://url.com/file.jpg becomes http://url.com/
 *
 * @param $url
 *
 * @return string
 */
function smratoptim_strip_filename( $url ) {
	$pieces = explode( "/", $url ); // split the URL by /
	if ( count( $pieces ) < 4 ) {
		return $url . "/";
	}
	if ( strpos( end( $pieces ), "." ) !== false ) { // we got a filename at the end
		array_pop( $pieces ); // remove the filename
	} elseif ( end( $pieces ) !== "" ) { // it ends with a name without an extension, i.e. a directory
		array_push( $pieces, "" ); // when $pieces is imploded, a "/" and then this "" will be appended
	}

	// else, already ends with a slash
	return implode( "/", $pieces );
}


function megaoptim_is_retina_enabled() {
	return true;
}

/**
 * Regenerates thumbnails
 *
 * @param $id
 * @param $path
 *
 * @return mixed
 */
function megaoptim_regenerate_thumbnails( $id, $path = null ) {
	if ( ! function_exists( 'wp_generate_attachment_metadata' ) || ! function_exists( 'wp_update_attachment_metadata' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );
	}
	if ( is_null( $path ) || empty( $path ) ) {
		$path = get_attached_file( $id );
	}
	$meta = wp_generate_attachment_metadata( $id, $path );
	wp_update_attachment_metadata( $id, $meta );

	return $meta;
}


/**
 * @param \MegaOptim\Responses\Response $response
 * @param array $params
 *
 * @return array|mixed
 */
function megaoptim_generate_thumbnail_data( $response, $params ) {
	$files        = $response->getOptimizedFiles();
	$thumb_object = array();
	if ( $response->isSuccessful() && ! empty( $files ) ) {
		$raw          = $response->getRawResponse();
		$data         = json_decode( $raw, true );
		$thumb_object = array();
		if ( is_array( $data['result'] ) ) {
			$excluded = MGO_Attachment::excluded_params();
			foreach ( $data['result'] as $optimization ) {
				foreach ( $excluded as $exl ) {
					unset( $optimization[ $exl ] );
				}
				$thumb_object = $optimization;
				break;
			}
		}
		$thumb_object['status']     = (int) $response->isSuccessful();
		$thumb_object['process_id'] = $response->getProcessId();
		$thumb_object['time']       = date( 'Y-m-d H:i:s', time() );
		foreach ( megaoptim_get_allowed_query_parameters() as $parameter ) {
			if ( isset( $params[ $parameter ] ) ) {
				$thumb_object[ $parameter ] = $params[ $parameter ];
			}
		}
	}

	return $thumb_object;
}


/**
 * Fix incorrectly formatted url.
 *
 * @param $url
 *
 * @return string
 */
function megaoptim_maybe_fix_url( $url ) {
	$url = str_replace( "\\", "/", $url );

	return $url;
}

/**
 * Validates email
 *
 * @param $email
 *
 * @return bool
 */
function megaoptim_validate_email( $email = '' ) {
	if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
		return false;
	}

	return true;
}

/**
 * Is registration pending?
 * @return bool
 */
function megaoptim_is_registration_pending() {
	$is_pending = megaoptim_validate_email( get_option( 'megaoptim_registration_email' ) );
	return $is_pending;
}

/**
 * Is registration pending?
 * @return bool|string
 */
function megaoptim_get_validation_email() {
	$email      = get_option( 'megaoptim_registration_email' );
	$is_pending = megaoptim_validate_email( $email );
	return $is_pending ? $email : false;
}