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

namespace MegaOptim\Tools;

use MegaOptim\Optimizer;

class FileSystem {


	/**
	 * Scan directory for resources
	 *
	 * @param $dir
	 *
	 * @return array
	 */
	public static function scan_dir( $dir ) {
		$resources = array();
		if ( ! is_dir( $dir ) ) {
			return $resources;
		} else {
			foreach ( PATH::accepted_types() as $extension => $mime_type ) {
				$paths = glob( $dir . DIRECTORY_SEPARATOR . '*.' . $extension );
				if ( is_array( $paths ) ) {
					$resources = array_merge( $resources, $paths );
				}
			}

			return $resources;
		}
	}


	/**
	 * Fix the resource path
	 *
	 * @param $resource
	 *
	 * @return mixed
	 */
	public static function maybe_fix_resource_path( $resource ) {
		//Double slashes if C:\ for example
		return str_replace( '\\', '\\\\', $resource );
	}

	/**
	 * Create output dir
	 *
	 * @param $save_to
	 *
	 * @return bool|string
	 * @throws \Exception
	 */
	public static function maybe_prepare_output_dir( $save_to ) {
		$parent_save_dir = dirname( $save_to );
		if ( ! file_exists( $parent_save_dir ) && ! is_dir( $parent_save_dir ) ) {
			if ( ! mkdir( $parent_save_dir, 0777, true ) ) {
				throw new \Exception( 'The directory "' . $parent_save_dir . '" can not be created.' );
			}
		}
		if ( ! is_writable( $parent_save_dir ) ) {
			throw new \Exception( 'The directory "' . $parent_save_dir . '" is not writable.' );
		}

		return $save_to;

	}

	/**
	 * Appends end slash if there is no end slash.
	 *
	 * @param $path
	 *
	 * @return string
	 */
	public static function maybe_add_trailing_slash( $path ) {
		return rtrim( $path, '/' ) . '/';
	}
}