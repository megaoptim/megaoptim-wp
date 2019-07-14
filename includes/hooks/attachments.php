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
 * Delete the backup files when attachment is being deleted.
 *
 * @param $post_id
 *
 */
function _megaoptim_delete_media_attachment_backup( $post_id ) {
	try {
		$attachment = new MGO_MediaAttachment( $post_id );
		// Delete backup if exist.
		if ( $attachment->has_backup() ) {
			$attachment->delete_backup();
		}
		// Delete WebP if exist.
		$attachment->delete_webp();
	} catch ( MGO_Exception $e ) {
		megaoptim_log( 'Error deleting the media attachment: ' . $e->getMessage() );
	}

}

add_action( 'delete_attachment', '_megaoptim_delete_media_attachment_backup' );


/**
 * Optimize media attachment?
 *
 * @param $metadata
 * @param $attachment_id
 *
 * @return mixed
 */
function _megaoptim_optimize_media_attachment( $metadata, $attachment_id ) {

	/**
	 * Hook to optimize the media attachment or not.
	 *
	 * @param bool $optimize True to optimize, false otherwise.
	 * @param int $post_id Attachment ID.
	 * @param array $metadata An array of attachment meta data.
	 *
	 * @since  1.0.0
	 *
	 */
	$optimize = apply_filters( 'megaoptim_auto_optimize_media_attachment', MGO_Settings::instance()->isAutoOptimizeEnabled(), $attachment_id, $metadata );

	if ( ! $optimize ) {
		return $metadata;
	}
	try {
		$attachment = new MGO_MediaAttachment( $attachment_id );
		$attachment->set_metadata( $metadata );
		MGO_MediaLibrary::instance()->optimize_async( $attachment );
	} catch ( MGO_Exception $e ) {
		megaoptim_log( $e->getMessage() );
	}

	return $metadata;
}

add_filter( 'wp_generate_attachment_metadata', '_megaoptim_optimize_media_attachment', WP_MEGAOPTIM_INT_MAX, 2 );


