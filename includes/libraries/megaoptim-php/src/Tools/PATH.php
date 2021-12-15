<?php

namespace MegaOptim\Client\Tools;

class PATH {
	/**
	 * Check if the given path is support image type (jpg,png,gif,svg)
	 *
	 * @param  string  $path  - The local temporary path
	 *
	 * @return bool
	 */
	public static function is_supported( $path ) {
		return array_key_exists( pathinfo( $path, PATHINFO_EXTENSION ), self::accepted_types() );
	}

	/**
	 * Returns the content type of a given path
	 *
	 * @param $path
	 *
	 * @return mixed|string|null
	 */
	public static function get_content_type( $path ) {
		$ext         = pathinfo( $path, PATHINFO_EXTENSION );
		$whitelisted = self::accepted_types();
		return isset( $whitelisted[ $ext ] ) ? $whitelisted[ $ext ] : null;
	}

	/**
	 * Return the accepted file types
	 * @return array
	 */
	public static function accepted_types() {
		return array(
			'png'  => 'image/png',
			'jpe'  => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'jpg'  => 'image/jpeg',
			'gif'  => 'image/gif',
			//'bmp' => 'image/bmp',
			//'ico' => 'image/vnd.microsoft.icon',
			//'tiff' => 'image/tiff',
			//'tif' => 'image/tiff',
			//'svg'  => 'image/svg+xml',
			//'svgz' => 'image/svg+xml',
		);
	}
}
