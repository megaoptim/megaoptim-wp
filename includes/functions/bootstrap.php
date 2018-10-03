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
 * Include files
 *
 * @param $files
 */
function megaoptim_include_files( $files ) {
	if ( count( $files ) > 0 ) {
		foreach ( $files as $key => $file ) {
			$include = true;
			if ( ! is_numeric( $key ) && is_bool( $file ) ) {
				$file_name = $key;
				if ( true !== $file ) {
					$include = false;
				}
			} else {
				$file_name = $file;
			}
			if ( $include ) {
				$full_path = WP_MEGAOPTIM_INC_PATH . str_replace( '/', DIRECTORY_SEPARATOR, $file_name );
				require_once( $full_path );
			}
		}
	}
}

/**
 * Check if nextgen is active
 * @return bool
 */
function megaoptim_is_nextgen_active() {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

	return is_plugin_active( 'nextgen-gallery/nggallery.php' ) || class_exists( 'C_NextGEN_Bootstrap' );
}

/**
 * IS WP Retina 2x active?
 * @return bool
 */
function megaoptim_is_wr2x_active() {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

	return is_plugin_active( 'wp-retina-2x/wp-retina-2x.php' ) || is_plugin_active( 'wp-retina-2x-pro/wp-retina-2x-pro.php' ) || class_exists( 'Meow_WR2X_Core' );
}