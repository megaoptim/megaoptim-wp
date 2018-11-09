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

/**
 * @param null $page
 *
 * @return bool
 */
function megaoptim_is_admin_page( $page = null ) {
	$available_pages = array(
		MGO_Admin_UI::PAGE_BULK_OPTIMIZER,
		MGO_Admin_UI::PAGE_SETTINGS,
	);
	if ( isset( $_GET['page'] ) && in_array( $_GET['page'], $available_pages ) ) {
		if ( is_null( $page ) ) {
			return true;
		} else {
			return $_GET['page'] === $page;
		}
	}

	return false;
}

/**
 * Check if is optimizer?
 *
 * @param $optimizer
 *
 * @return bool
 */
function megaoptim_is_optimizer_page( $optimizer ) {
	if ( ! megaoptim_is_admin_page( MGO_Admin_UI::PAGE_BULK_OPTIMIZER ) ) {
		return false;
	}
	if ( ! isset( $_GET['module'] ) || empty( $_GET['module'] ) ) {
		if ($optimizer !== MGO_MediaAttachment::TYPE ) {
			return false;
		}else {
			return true;
		}

	}
	$module = $_GET['module'];
	if ( $optimizer === MGO_MediaAttachment::TYPE ) {
		return $module === 'wp-media-library';
	} else if ( $optimizer === MGO_LocalFileAttachment::TYPE ) {
		return $module === 'folders';
	}
	return apply_filters( 'megaoptim_is_optimizer_page', false, $optimizer, $module );
}