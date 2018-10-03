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

function _megaoptim_async_optimize_media_library_attachment() {

	megaoptim_log( 'Optimizing attachment async.' );

	// Validations
	if ( ! isset( $_POST['attachment_id'] ) || ! isset( $_POST['action'] ) || ! isset( $_POST['_nonce'] ) ) {
		wp_die();
	}
	$nonce_key = $_POST['action'] . '_' . $_POST['attachment_id'];
	if ( ! wp_verify_nonce( $_POST['_nonce'], $nonce_key ) ) {
		megaoptim_log( 'Invalid nonce!' );
		wp_die();
	}
	$id = $_POST['attachment_id'];

	$params = isset( $_REQUEST['params'] ) && ! empty( $_REQUEST['params'] ) ? $_REQUEST['params'] : array();

	// Handle task
	if ( is_numeric( $id ) ) {
		try {
			megaoptim_log( 'Optimizing media library attachment with id ' . $id . ' in background.' );
			$attachment = new MGO_MediaAttachment( $id );
			if ( isset( $_POST['metadata'] ) && ! empty( $_POST['metadata'] ) ) {
				$attachment->set_metadata( $_POST['metadata'] );
			}
			MGO_MediaLibrary::instance()->optimize( $attachment, $params );
		} catch ( \Exception $e ) {
			megaoptim_log( 'Error auto optimizing media attachment: ' . $e->getMessage() );
		}
	}
}

add_action( 'wp_ajax_megaoptim_async_optimize_ml_attachment', '_megaoptim_async_optimize_media_library_attachment' );
add_action( 'wp_ajax_nopriv_megaoptim_async_optimize_ml_attachment', '_megaoptim_async_optimize_media_library_attachment' );