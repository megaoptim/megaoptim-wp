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

spl_autoload_register( function ( $className ) {
	/**
	 * Initial path and vars.
	 */
	$ds  = DIRECTORY_SEPARATOR;
	$dir = dirname( __FILE__ ) . $ds . 'src';

	/**
	 * Generate the direct path to the class
	 */
	$className = str_replace( 'MegaOptim\\Client\\', '', $className );
	$className = str_replace( '\\', $ds, $className );

	/**
	 * Load the class if all fine.
	 */
	$file = "{$dir}{$ds}{$className}.php";
	if ( is_readable( $file ) ) {
		require_once $file;
	}
} );
