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
 * Executes immediately after install
 */
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
	megaoptim_protect_dir( $dir );
}

register_activation_hook( WP_MEGAOPTIM_PLUGIN_FILE_PATH, '_megaoptim_install' );

/**
 * Executes on admin init
 */
function megaoptim_current_screen() {

	$screen = get_current_screen();

	// If the user is on Media (upload) screen notify him to switch to the List View screen
	if ( $screen->id === 'upload' ) {
		$mode = get_user_option( 'media_library_mode', get_current_user_id() ) ? get_user_option( 'media_library_mode', get_current_user_id() ) : 'grid';
		if ( $mode === 'grid' ) {
			$url = admin_url( 'upload.php?mode=list' );
			$list_mode_url  = '<a href="' . $url . '">' . __( 'Click here', 'megaoptim' ) . '</a>';
			$list_mode_text = '<strong>' . __( 'list', 'megaoptim' ) . '</strong>';
			$message        = sprintf( __( 'MegaOptim provides optimization buttons in the list mode where you can optimize or restore sinle attachments. %s to switch to %s mode.' ), $list_mode_url, $list_mode_text );
			MGO_Admin_Notices::instance()->info( 'notify_media_list_features', $message, 1 );
		}
	}
}
add_action( 'current_screen', 'megaoptim_current_screen', 15 );
