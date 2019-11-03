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

function _megaoptim_ngg_backup_dir( $dir, $context ) {
	if ( $context === MEGAOPTIM_TYPE_NEXTGEN_ATTACHMENT ) {
		$dir = megaoptim_get_nextgen_backup_dir();
	}

	return $dir;
}
add_filter( 'megaoptim_backup_dir', '_megaoptim_ngg_backup_dir', 10, 2 );


function _megaoptim_ngg_optimize_single_attachment( $attachment_id, $context, $additional_params ) {
	if ( $context === MEGAOPTIM_TYPE_NEXTGEN_ATTACHMENT ) {
		MGO_NGGLibrary::instance()->optimize_async($attachment_id, $additional_params);
	}
}
add_action( 'megaoptim_optimize_single_attachment', '_megaoptim_ngg_optimize_single_attachment', 10, 3 );


function _megaoptim_ngg_restore_single_attachment( $data, $attachment_id, $context ) {
	try {
		$attachment = new MGO_NGGAttachment( $attachment_id );
		$attachment->restore();
		$data = megaoptim_get_ngg_attachment_buttons( $attachment );
	} catch ( MGO_Exception $e ) {

	}

	return $data;
}
add_filter( 'megaoptim_restore_single_attachment', '_megaoptim_ngg_restore_single_attachment', 10, 3 );


function _megaoptim_ngg_upload_ticker( $response, $context, $attachments ) {
	if ( ! is_array( $response ) ) {
		$response = array();
	}
	foreach ( $attachments as $attachment_id ) {
		try {
			$attachment                 = new MGO_NGGAttachment( $attachment_id );
			$response[ $attachment_id ] = array(
				'id'           => $attachment->get_id(),
				'is_locked'    => $attachment->is_locked(),
				'is_optimized' => $attachment->is_processed(),
				'html'         => megaoptim_get_ngg_attachment_buttons( $attachment )
			);

		} catch ( MGO_Exception $e ) {
		}
	}

	return $response;
}
add_filter( 'megaoptim_upload_ticker', '_megaoptim_ngg_upload_ticker', 10, 3 );


function _megaoptim_ngg_optimizer_view( $optimizer, $module, $menu ) {
	return 'optimizers/nextgen';
}
add_filter( 'megaoptim_optimizer_view', '_megaoptim_ngg_optimizer_view', 10, 3 );


function _megaoptim_ngg_optimizer_params( $params, $optimizer, $module, $menu ) {
	return array(
		'stats'   => MGO_NGGLibrary::instance()->get_stats( true ),
		'menu'    => $menu,
		'module'  => $module,
		'profile' => MGO_Profile::get_profile()
	);
}
add_filter( 'megaoptim_optimizer_params', '_megaoptim_ngg_optimizer_params', 10, 4 );


function _megaoptim_ngg_is_optimizer_page($is_optimizer, $optimizer, $module) {
	if( $optimizer === MEGAOPTIM_TYPE_NEXTGEN_ATTACHMENT ) {
		if( $module === 'nextgen' ) {
			$is_optimizer = true;
		}
	}
	return $is_optimizer;
}
add_filter('megaoptim_is_optimizer_page', '_megaoptim_ngg_is_optimizer_page', 10, 3);


function _megaoptim_ngg_library_data($stats, $context) {
	if( $context === MEGAOPTIM_TYPE_NEXTGEN_ATTACHMENT ) {
		$stats = MGO_NGGLibrary::instance()->get_stats(true );
	}
	return $stats;
}
add_filter('megaoptim_library_data', '_megaoptim_ngg_library_data', 10, 2);