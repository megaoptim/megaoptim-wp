<?php

namespace MegaOptim\Tools;

class PATH {
	/**
	 * Check if the given path is support image type (jpg,png,gif,svg)
	 *
	 * @param string $path - The local temporary path
	 *
	 * @return bool
	 */
	public static function is_supported( $path ) {
		return array_key_exists( pathinfo( $path, PATHINFO_EXTENSION ), self::accepted_types() );
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