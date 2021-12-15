<?php
/********************************************************************
 * Copyright (C) 2020 MegaOptim (https://megaoptim.com)
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

namespace MegaOptim\Client\Tools;

/**
 * Class PATH
 * @package MegaOptim\Client\Tools
 */
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
