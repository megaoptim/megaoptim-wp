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

if ( ! function_exists( 'wp_parse_url' ) ) {
	/**
	 * Compatibility for WP < 4.4.0
	 *
	 * @param $url
	 * @param int $component
	 *
	 * @return mixed
	 */
	function wp_parse_url( $url, $component = - 1 ) {
		return parse_url( $url, $component );
	}
}


if ( ! function_exists( 'wp_normalize_path' ) ) {

	/**
	 * Compatibility for WP < 3.9.0 && WP >= 3.5.0
	 *
	 * Normalize a filesystem path.
	 *
	 * On windows systems, replaces backslashes with forward slashes
	 * and forces upper-case drive letters.
	 * Allows for two leading slashes for Windows network shares, but
	 * ensures that all other duplicate slashes are reduced to a single.
	 *
	 * @since 3.9.0
	 * @since 4.4.0 Ensures upper-case drive letters on Windows systems.
	 * @since 4.5.0 Allows for Windows network shares.
	 * @since 4.9.7 Allows for PHP file wrappers.
	 *
	 * @param string $path Path to normalize.
	 *
	 * @return string Normalized path.
	 */
	function wp_normalize_path( $path ) {
		$wrapper = '';
		if ( wp_is_stream( $path ) ) {
			list( $wrapper, $path ) = explode( '://', $path, 2 );
			$wrapper .= '://';
		}
		// Standardise all paths to use /
		$path = str_replace( '\\', '/', $path );
		// Replace multiple slashes down to a singular, allowing for network shares having two slashes.
		$path = preg_replace( '|(?<=.)/+|', '/', $path );
		// Windows paths should uppercase the drive letter
		if ( ':' === substr( $path, 1, 1 ) ) {
			$path = ucfirst( $path );
		}

		return $wrapper . $path;
	}
}