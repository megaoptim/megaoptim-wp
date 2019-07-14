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

function _megaoptim_megaoptim_optimize_ngg_attachment() {
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], MGO_Ajax::NONCE_OPTIMIZER ) ) {
		wp_send_json_error( array( 'error' => __( 'Internal server error.', 'megaoptim' ) ) );
	}
	if ( ! isset( $_REQUEST['attachment'] ) ) {
		wp_send_json_error( array( 'error' => __( 'No attachment provided.', 'megaoptim' ) ) );
	}
	try {
		$result = MGO_NGGLibrary::instance()->optimize( new MGO_File( $_REQUEST['attachment'] ) );
		$attachment = $result->get_attachment();
		if ( $attachment instanceof MGO_NGGAttachment ) {
			$response['attachment'] = $attachment->get_optimization_stats();
			$response['general']    = $result->get_optimization_info();
			$response['tokens']     = $result->get_last_response()->getUser()->getTokens();
			wp_send_json_success( $response );
		} else {
			wp_send_json_error( array( 'error' => __( 'Attachment was not optimized.', 'megaoptim' ), 'can_continue' => 1 ) );
		}
	} catch ( MGO_Exception $e ) {
		wp_send_json_error( array( 'error' => $e->getMessage(), 'can_continue' => 1 ) );
	}
}

add_action( 'wp_ajax_megaoptim_ngg_optimize_attachment', '_megaoptim_megaoptim_optimize_ngg_attachment' );