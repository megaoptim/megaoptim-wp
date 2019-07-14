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
 * Autooptimize new attachments that are added in the nextgen tables except the ones imported from WordPress media library.
 *
 * @param int $gallery_id
 * @param array $image_ids
 *
 * @throws MGO_Attachment_Already_Optimized_Exception
 * @throws MGO_Attachment_Locked_Exception
 * @throws MGO_Exception
 */
function _megaoptim_auto_optimize_ngg_attachment( $gallery_id, $image_ids ) {

	// Images imported from the library. Skip
	if ( ! empty( $_POST['nextgen_upload_image_sec'] ) && ! empty( $_POST['attachment_ids'] ) ) {
		if ( ! empty( $_POST['action'] ) && 'import_media_library' === $_POST['action'] ) {
			if ( is_array( $_POST['attachment_ids'] ) ) {
				return;
			}
		}
	}

	foreach ( $image_ids as $image_id ) {
		/**
		 * Hook to optimize the media attachment or not.
		 * @since  1.0.0
		 *
		 * @param bool $optimize True to optimize, false otherwise.
		 * @param int $post_id Attachment ID.
		 * @param array $metadata An array of attachment meta data.
		 */
		$optimize = apply_filters( 'megaoptim_auto_optimize_ngg_attachment', MGO_Settings::instance()->isAutoOptimizeEnabled(), $image_id, $gallery_id );

		if ( ! $optimize ) {
			continue;
		}
		MGO_NGGLibrary::instance()->optimize_async($image_id);
	}
}

add_action( 'ngg_after_new_images_added', '_megaoptim_auto_optimize_ngg_attachment', WP_MEGAOPTIM_INT_MAX, 2 );


/**
 * Transfer megaoptim data from medialibrary to nextgen when imported.
 *
 * @param  object $image A NGG image object.
 * @param  WP_Post $ml_attachment
 *
 * @return object
 */
function _megaoptim_ngg_medialibrary_imported_image( $image, $ml_attachment ) {

	try {
		$ngg_megaoptim_file = megaoptim_get_ngg_attachment( $image->pid );
		$ngg_attachment = new MGO_NGGAttachment( $image->pid );
		$media_library_attachment = new MGO_MediaAttachment( $ml_attachment->ID );
		if ( $media_library_attachment->is_processed() ) {
			$ngg_attachment->set( 'object_id', $image->pid );
			$ngg_attachment->set( 'type', MEGAOPTIM_TYPE_NEXTGEN_ATTACHMENT );
			$ngg_attachment->set( 'file_path', $ngg_megaoptim_file->path );
			$optimization_details = array(
				'status',
				'success',
				'original_size',
				'optimized_size',
				'saved_bytes',
				'saved_percent',
				'url',
				'process_id',
				'backup_path',
				'compression',
				'cmyktorgb',
				'keep_exif',
				'max_width',
				'max_height',
				'time',
			);
			foreach ( $optimization_details as $detail ) {
				$value = $media_library_attachment->get( $detail );
				$ngg_attachment->set( $detail, $value );
			}
			if ( $ngg_attachment->save() ) {
				megaoptim_log( 'Import of media library file to nextgen gallery that was already optimized finished successfully: ' . $ngg_attachment->get_id() );
			}
		}
	} catch ( MGO_Exception $e ) {
		megaoptim_log( __( 'Error transfering megaoptim data from media library to nextgen. Process failed with message: ' . $e->getMessage() ) );
	}

	return $image;
}

add_filter( 'ngg_medialibrary_imported_image', '_megaoptim_ngg_medialibrary_imported_image', 10, 2 );

/**
 * Optimize dynamically generated nextgen thumbnail.
 *
 * @param object $image A NGG image object.
 * @param string $size The thumbnail size name.
 */
function _megaoptim_ngg_generated_image( $image, $size ) {
	// TODO
}

add_action( 'ngg_generated_image', '_megaoptim_ngg_generated_image', WP_MEGAOPTIM_INT_MAX, 2 );

/**
 * Remove result from the database.
 *
 * @param $id
 */
function _megaoptim_ngg_delete_picture( $id ) {
	$attachment = new MGO_NGGAttachment( $id );
	// Delete Backup if exist.
	if ( $attachment->has_backup() ) {
		$attachment->delete_backup();
	}
	// Delete WebP if exist.
	$attachment->delete_webp();
	$attachment->destroy();
}

add_action( 'ngg_delete_picture', '_megaoptim_ngg_delete_picture', 1, 10 );


/**
 * @param $gallery
 */
function _megaoptim_ngg_delete_gallery( $gallery ) {
	if ( is_numeric( $gallery ) ) {
		global $wpdb;
		$wpdb->delete( $wpdb->prefix . 'megaoptim_opt', array(
			'gallery_id' => $gallery,
			'type'       => MEGAOPTIM_TYPE_NEXTGEN_ATTACHMENT
		) );
	}
}

add_action( 'ngg_delete_gallery', '_megaoptim_ngg_delete_gallery', 10, 1 );


