<?php
/********************************************************************
 * Copyright (C) 2019 MegaOptim (https://megaoptim.com)
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

if ( ! function_exists( '_megaoptim_wpengine_purge' ) ) {
	/**
	 * Purges wpengine cdn cache once attachment is optimized.
	 */
	function _megaoptim_wpengine_purge() {
		if ( class_exists( 'WpeCommon' ) ) {
			if ( method_exists( 'WpeCommon', 'clear_maxcdn_cache' ) ) {
				WpeCommon::clear_maxcdn_cache();
			}
		}
	}
	add_action( 'megaoptim_attachment_optimized', '_megaoptim_wpengine_purge', 10, 0 );
}