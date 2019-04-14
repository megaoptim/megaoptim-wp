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

if ( ! function_exists( '_megaoptim_install' ) ) {
	function _megaoptim_install() {
		// Upgrade db
		$upgrader = new MGO_Upgrader();
		$upgrader->maybe_upgrade();
		// Set defaults
		if ( ! MGO_Settings::was_installed_previously() ) {
			MGO_Settings::instance()->update( MGO_Settings::defaults() );
		}
		// Make the tmp dir secure
		$dir = megaoptim_get_tmp_path();
		megaoptim_protect_dir($dir);
	}
	register_activation_hook( WP_MEGAOPTIM_PLUGIN_FILE_PATH, '_megaoptim_install' );
}


