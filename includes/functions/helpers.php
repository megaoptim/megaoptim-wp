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
 *
 * @param $file string
 * @param $contents string
 * @param  string  $force_flag
 */
function megaoptim_write( $file, $contents, $force_flag = '' ) {
	if ( file_exists( $file ) ) {
		$flag = $force_flag !== '' ? $force_flag : 'a';
		$fp   = fopen( $file, $flag );
		fwrite( $fp, $contents . "\n" );
	} else {
		$flag = $force_flag !== '' ? $force_flag : 'w';
		$fp   = fopen( $file, $flag );
		fwrite( $fp, $contents . "\n" );
	}
	if ( is_resource( $fp ) ) {
		fclose( $fp );
	}
}

/**
 * Makes specific dir secure.
 *
 * @param $dir
 * @param  bool  $noindex
 */
function megaoptim_protect_dir( $dir, $noindex = true ) {
	if ( ! is_dir( $dir ) ) {
		@mkdir( $dir );
	}
	// Create empty index file
	if ( is_dir( $dir ) ) {
		$index_path = $dir . DIRECTORY_SEPARATOR . 'index.html';
		if ( ! file_exists( $index_path ) ) {
			@touch( $index_path );
		}
	}
	// Create noindex to the directory for some hosting environemnts.
	if ( $noindex ) {
		$htaccess_path = $dir . DIRECTORY_SEPARATOR . '.htaccess';
		if ( ! file_exists( $htaccess_path ) ) {
			$contents = '<IfModule headers_module>
Header set X-Robots-Tag "noindex"
</IfModule>';
			megaoptim_write( $htaccess_path, $contents );
		}
	}
}

/**
 * Wrapper for writing the interactions to /wp-content/uploads/ file
 *
 * @param  mixed   $message
 * @param  string  $filename
 */
function megaoptim_log( $message, $filename = "debug.log" ) {
	$log_file_dir = megaoptim_get_tmp_path();
	megaoptim_protect_dir( $log_file_dir );
	$log_file_path = $log_file_dir . DIRECTORY_SEPARATOR . $filename;
	// Remove the log file if it is larger than 10 MB
	if ( file_exists( $log_file_path ) && filesize( $log_file_path ) > 10485760 ) {
		@unlink( $log_file_path );
	}
	if ( ! is_string( $message ) && ! is_numeric( $message ) ) {
		ob_start();
		megaoptim_dump( $message );
		$message = ob_get_clean();
	}
	megaoptim_write( $log_file_path, $message );
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
	$response = wp_remote_head( WP_MEGAOPTIM_API_BASE_URL . '/ping', array(
		'user-agent' => "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_8; pt-pt) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27",
	) );
	$httpcode = wp_remote_retrieve_response_code( $response );
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
		foreach (
			new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $path,
				FilesystemIterator::SKIP_DOTS ) ) as $object
		) {
			try {
				$bytestotal += $object->getSize();
			} catch ( \Exception $e ) {
			}
		}
	}

	return $bytestotal;
}

/**
 * Returns human readable file size from bytes
 *
 * @param $size
 * @param  string  $unit
 * @param  bool  $include_unit
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
 * @param  integer  $bytes  The filesize in Bytes.
 * @param  string  $to  The unit type to convert to. Accepts K, M, or G for Kilobytes, Megabytes, or Gigabytes, respectively.
 * @param  integer  $decimal_places  The number of decimal places to return.
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
 *
 * @param $beginning
 * @param $end
 * @param $string
 *
 * @return mixed
 */
function megaoptim_remove_between( $beginning, $end, $string ) {
	$beginning_pos = strpos( $string, $beginning );
	$end_pos       = strpos( $string, $end );
	if ( $beginning_pos === false || $end_pos === false ) {
		return $string;
	}
	$text = substr( $string, $beginning_pos, ( $end_pos + strlen( $end ) ) - $beginning_pos );
	$text = str_replace( $text, '', $string );
	$text = str_replace( $beginning, '', $text );
	$text = str_replace( $end, '', $text );

	return $text;
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

	$is_accessible_from_public = megaoptim_cache_get( 'is_accessible_from_public' );

	if ( false === $is_accessible_from_public ) {
		$parts = parse_url( site_url() );
		$host  = $parts['host'];
		$ip    = @gethostbyname( $host );
		if ( false === $ip ) {
			// One more try to avoid CNAME on www.
			if ( megaoptim_contains( $host, 'www.' ) ) {
				$host = str_replace( 'www.', '', $host );
				$ip   = @gethostbyname( $host );
			}
		}
		if ( $ip === false ) {
			$is_accessible_from_public = 0;
		} else {
			$is_accessible_from_public = megaoptim_is_ip_private( $ip ) ? 0 : 1;
		}
		megaoptim_cache_set( 'is_accessible_from_public', $is_accessible_from_public, MEGAOPTIM_ONE_HOUR_IN_SECONDS );
	}

	return (bool) apply_filters( 'megaoptim_public_site', $is_accessible_from_public );
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
	$backup_dir = wp_normalize_path( megaoptim_get_backup_dir() . DIRECTORY_SEPARATOR . MEGAOPTIM_TYPE_MEDIA_ATTACHMENT );

	return apply_filters( 'megaoptim_ml_backup_dir', $backup_dir );
}


/**
 * Returns the media library backup dir
 * @return string
 */
function megaoptim_get_files_backup_dir() {
	$backup_dir = wp_normalize_path( megaoptim_get_backup_dir() . DIRECTORY_SEPARATOR . MEGAOPTIM_TYPE_FILE_ATTACHMENT );

	return apply_filters( 'megaoptim_files_backup_dir', $backup_dir );
}

/**
 * Returns the backup path for media library attachment by ID
 *
 * @param $id
 * @param $path
 * @param  null  $size
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
    $value = defined('ABSPATH') ? ABSPATH : $_SERVER['DOCUMENT_ROOT'];
    return rtrim($value, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
}

/**
 * Returns the htaccess path
 * @return string
 */
function megaoptim_get_htaccess_path() {
	return megaoptim_get_wp_root_path() . '.htaccess';
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
	if ( count( $arr ) > 0 ) {
		foreach ( $arr as $key => $value ) {
			if ( ! in_array( $key, $keys ) ) {
				$new[ $key ] = $value;
			}
		}
	}

	return $new;
}

/**
 * Array only
 *
 * @param $arr
 * @param $keys
 *
 * @return array
 */
function megaoptim_array_only( $arr, $keys ) {
	$new = array();
	if ( count( $arr ) > 0 ) {
		foreach ( $arr as $key => $value ) {
			if ( in_array( $key, $keys ) ) {
				$new[ $key ] = $value;
			}
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
	} elseif ( megaoptim_get_wp_root_path() === $parent_dir || rtrim(megaoptim_get_wp_root_path(), '/') === $parent_dir ) {
		// we are in /
		$excluded = array(
			'wp-includes',
			'wp-admin',
			".idea"
		);
		if ( in_array( megaoptim_basename( $dir ), $excluded ) ) {
			return true;
		}
	} elseif ( $wp_content === $parent_dir ) {
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
	return \MegaOptim\Client\Tools\URL::validate( $url );
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
 * @param  array  $data
 * @param  string  $extension
 *
 * @return string
 */
function megaoptim_get_view( $file, $data = array(), $extension = '' ) {
	if ( $extension === '' ) {
		$extension = 'php';
	} elseif ( substr( $extension, 0, 1 ) === '.' ) {
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
 * @param  array  $data
 * @param  string  $extension
 */
function megaoptim_view( $file, $data = array(), $extension = '' ) {
	echo megaoptim_get_view( $file, $data, $extension );
}


/**
 * Raise the WP Memory limit.
 *
 * @param  string  $context
 */
function megaoptim_raise_memory_limit( $context = 'image' ) {
	if ( function_exists( 'wp_raise_memory_limit' ) ) {
		wp_raise_memory_limit( $context );
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
	$files = megaoptim_find_images_non_recursively( $path );

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
 * Get the images in specific folder non-recursively.
 *
 * @param $path
 *
 * @return array|false
 */
function megaoptim_find_images_non_recursively( $path ) {
    $types     = array_keys( \MegaOptim\Client\Tools\PATH::accepted_types() );
    $file_list = array();
    foreach ( $types as $ext ) {
        $found_files = glob( trailingslashit( $path) . "*" );
        $found_files =  preg_grep('/\.'.$ext.'$/i', $found_files);
        foreach ( $found_files as $file ) {
            array_push( $file_list, realpath($file) );
        }
    }
    return $file_list;
}

/**
 * List of excluded custom folders from 'Custom Folders' scan
 * @retun array
 */
function megaoptim_get_excluded_custom_dir_paths() {
	$uploads = wp_upload_dir();

	return array( $uploads['basedir'] );
}

/**
 * Get the dir contents recursively.
 *
 * @param $dir
 *
 * @param  bool  $recursive
 *
 * @param  array  $excluded_dirs
 *
 * @return array
 */
function megaoptim_find_images( $dir, $recursive = false, $excluded_dirs = array() ) {

	if ( ! $recursive ) {
		return megaoptim_find_images_non_recursively( $dir );
	}

	$files = array();

	if ( empty( $excluded_dirs ) ) {
		$files = megaoptim_find_images_quick( $dir, $recursive );
	} else {
		$directory = new RecursiveDirectoryIterator( $dir );
		$filtered  = new MGO_ImageFilter( $directory );
		$filtered->setExcluded($excluded_dirs);
		$iterator  = new RecursiveIteratorIterator( $filtered );
		$iterator  = new RegexIterator( $iterator, '/^.+(.jpe?g|.png|.gif)$/i', RecursiveRegexIterator::GET_MATCH );
		foreach ( $iterator as $file ) {
			array_push( $files, realpath( $file[0] ) );
		}
	}


	return $files;
}

/**
 * Get the dir contents recursively.
 *
 * Note: Quickier method than megaoptim_find_images() when no excluding is required.
 *
 * @param $dir
 * @param  false  $recursive
 *
 * @return array
 */
function megaoptim_find_images_quick( $dir, $recursive = false ) {

	$dir = trailingslashit( $dir );

	if ( is_dir( $dir ) === false ) {
		return array();
	}
	try {
		$res     = opendir( $dir );
		$images  = array();
		$formats = array( '.jpg', '.jpeg', '.png', '.gif' );

		while ( false !== ( $item = readdir( $res ) ) ) {
			if ( $item == "." || $item == ".." ) {
				continue;
			}
			$path = $dir . $item;
			if ( $recursive && is_dir( $path ) ) {
				$_images = megaoptim_find_images_quick( $path, $recursive );
				$images  = array_merge( $images, $_images );
			} else {
				$found = 0;
				foreach ( $formats as $format ) {
					if ( substr( $item, - strlen( $format ) ) === $format ) {
						$found ++;
					}
				}
				if ( ! $found ) {
					continue;
				}
				array_push( $images, $path );
			}
		}
	} catch ( \Exception $e ) {
		return array();
	}

	return $images;
}

/**
 * Custom basename function. With planned multibyte enhancements in future.
 *
 * @param $str
 * @param  string  $suffix
 *
 * @return string
 */
function megaoptim_basename( $str, $suffix = '' ) {
	return wp_basename( $str, $suffix );
}

/**
 * Multibyte Basename support
 *
 * @param $path
 * @param  bool  $suffix
 *
 * @return bool|mixed|string
 */
function megaoptim_mb_basename( $path, $suffix = false ) {
	$Separator = " qq ";
	$qqPath    = preg_replace( "/[^ ]/u", $Separator . "\$0" . $Separator, $path );
	if ( ! $qqPath ) { //this is not an UTF8 string!! Don't rely on basename either, since if filename starts with a non-ASCII character it strips it off
		$parts    = explode( DIRECTORY_SEPARATOR, $path );
		$fileName = end( $parts );
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
 * Remove file name from url
 * eg. http://url.com/file.jpg becomes http://url.com/
 *
 * @param $url
 *
 * @return string
 */
function megaoptim_strip_filename( $url ) {
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
		$path = get_attached_file( $id, true );
	}
	$meta = wp_generate_attachment_metadata( $id, $path );
	wp_update_attachment_metadata( $id, $meta );

	return $meta;
}

/**
 * Generate data for specific optimization
 *
 * @param  \MegaOptim\Client\Responses\Result  $file
 * @param  \MegaOptim\Client\Responses\Response  $response
 * @param  array  $params
 *
 * @return array|mixed
 */
function megaoptim_generate_attachment_data( $file, $response, $params ) {
	$webp                     = $file->getWebP();
	$params['original_size']  = $file->getOriginalSize();
	$params['optimized_size'] = $file->getOptimizedSize();
	$params['saved_bytes']    = $file->getSavedBytes(); // Remove
	$params['saved_percent']  = $file->getSavedPercent(); // Remove
	$params['webp_size']      = ! is_null( $webp ) ? $webp->optimized_size : 0;
	$params['success']        = $file->isSuccessfullyOptimized() ? 1 : 0;
	$params['status']         = $response->isSuccessful() ? 1 : 0;
	$params['time']           = date( 'Y-m-d H:i:s' );
	$params['compression']    = isset( $params['compression'] ) ? $params['compression'] : \MegaOptim\Client\Optimizer::COMPRESSION_INTELLIGENT;

	return $params;
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

/**
 * Used to prevent auto optimization
 */
function megaoptim_prevent_auto_optimization() {
	add_filter( 'megaoptim_auto_optimize_media_attachment', '__return_false' );
}

/**
 * Used to restore auto optimization
 */
function megaoptim_restore_auto_optimization() {
	remove_filter( 'megaoptim_auto_optimize_media_attachment', '__return_false' );
}

/**
 * Returns list of active conflicting plugins
 * @return array
 */
function megaoptim_get_conflicting_plugins() {
	$active  = array();
	$plugins = array(
		'ShortPixel Image Optimizer'           => array(
			'basename' => 'shortpixel-image-optimiser/wp-shortpixel.php',
		),
		'WP Smush - Image Optimization'        => array(
			'basename' => 'wp-smushit/wp-smush.php',
		),
		'Imagify Image Optimizer'              => array(
			'basename' => 'imagify/imagify.php',
		),
		'Compress JPEG & PNG images (TinyPNG)' => array(
			'basename' => 'tiny-compress-images/tiny-compress-images.php',
		),
		'Kraken.io Image Optimizer'            => array(
			'basename' => 'kraken-image-optimizer/kraken.php',
		),
		'Optimus - WordPress Image Optimizer'  => array(
			'basename' => 'optimus/optimus.php',
		),
		'EWWW Image Optimizer'                 => array(
			'basename' => 'ewww-image-optimizer/ewww-image-optimizer.php',
		),
		'EWWW Image Optimizer Cloud'           => array(
			'basename' => 'ewww-image-optimizer-cloud/ewww-image-optimizer-cloud.php',
		),
		'ImageRecycle pdf & image compression' => array(
			'basename' => 'imagerecycle-pdf-image-compression/wp-image-recycle.php',
		),
		'CheetahO Image Optimizer'             => array(
			'basename' => 'cheetaho-image-optimizer/cheetaho.php',
		),
		'Zara 4 Image Compression'             => array(
			'basename' => 'zara-4/zara-4.php',
		),
		'CW Image Optimizer'                   => array(
			'basename' => 'cw-image-optimizer/cw-image-optimizer.php',
		),
		'Simple Image Sizes'                   => array(
			'basename' => 'simple-image-sizes/simple_image_sizes.php'
		),
	);
	foreach ( $plugins as $key => $plugin ) {
		if ( is_plugin_active( $plugin['basename'] ) ) {
			$active[ $key ] = $plugin;
		}
	}

	return $active;
}

/**
 * USed to create and validate datetime object.
 *
 * @param $value
 * @param  string  $format
 *
 * @return bool|DateTime
 */
function megaoptim_create_datetime( $value, $format = 'Y-m-d' ) {
	$dt = DateTime::createFromFormat( $format, $value );

	$is_valid = ( $dt !== false && ! array_sum( $dt::getLastErrors() ) );
	if ( ! $is_valid ) {
		return false;
	} else {
		return $dt;
	}
}

/**
 * Check the ajax referer
 *
 * @param $nonce_name
 * @param $query_parameter_key
 *
 * @return bool|int
 */
function megaoptim_check_referer( $nonce_name, $query_parameter_key ) {
	return check_ajax_referer( $nonce_name, $query_parameter_key, false );
}


/**
 * Encodes url in RFC3986 format.
 *
 * @param $url
 *
 * @return array|mixed|string|string[]
 */
function megaoptim_rawurlencode( $url ) {
	$path = wp_parse_url( $url, PHP_URL_PATH );
	if ( ! empty( $path ) && $path !== '/' ) {
		$encoded_path = explode( '/', $path );
		// First decode the path, to prevent double encoding
		$encoded_path = array_map( 'rawurldecode', $encoded_path );
		$encoded_path = array_map( 'rawurlencode', $encoded_path );
		$encoded_path = implode( '/', $encoded_path );
		$url          = str_replace( $path, $encoded_path, $url );
	}
	$query = wp_parse_url( $url, PHP_URL_QUERY );
	if ( ! empty( $query ) ) {
		parse_str( $query, $parsed_query );
		$parsed_query = http_build_query( $parsed_query, '', '&amp;', PHP_QUERY_RFC3986 );
		$url          = str_replace( $query, $parsed_query, $url );
	}

	return $url;
}


/**
 * Gets the last key of an array
 *
 * @param mixed $arr An array.
 * @return mixed Returns the last key of array if the array is not empty; NULL otherwise.
 */
function megaoptim_array_key_last($arr) {
    if (!empty($arr)) {
        return key(array_slice($arr, -1, 1, true));
    }
    return '';
}