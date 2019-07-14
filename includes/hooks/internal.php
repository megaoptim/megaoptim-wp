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
 * Created by PhpStorm.
 * User: dg
 * Date: 8/30/2018
 * Time: 2:25 PM
 */

/**
 * Before optimizaiton hook
 * @return void
 */
function _megaoptim_before_optimization() {
	megaoptim_raise_memory_limit();
}
add_action( 'megaoptim_before_optimization', '_megaoptim_before_optimization', 10, 0 );

/**
 * Output scripts to admin footer
 */
function _megaoptim_admin_footer() {

	if ( megaoptim_is_admin_page() ) {
		megaoptim_view( 'modals/loader' );
	}

	// Requires: Remodal.min.js
	megaoptim_view( 'modals/register' );
}
add_action( 'admin_footer', '_megaoptim_admin_footer' );

/**
 * Check and run database upgrade if needed.
 */
function _megaoptim_database_upgrade() {
	if ( is_admin() ) {
		MGO_Upgrader::instance()->maybe_upgrade();
	}
}
add_action( 'plugins_loaded', '_megaoptim_database_upgrade', 100 );