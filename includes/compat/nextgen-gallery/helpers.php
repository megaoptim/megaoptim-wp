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
 * Returns the ngg image
 *
 * @param $id
 *
 * @return null|MGO_File
 */
function megaoptim_get_ngg_attachment( $id ) {
	$image = nggdb::find_image( absint( $id ) );
	if ( false === $image ) {
		return null;
	}
	$attachment     = new MGO_File();
	$attachment->ID = $id;
	if ( isset( $image->imagePath ) ) {
		$attachment->path      = $image->imagePath;
		$attachment->title     = megaoptim_basename( $image->imagePath );
		$attachment->directory = dirname( $image->imagePath );
	}
	if ( isset( $image->imageURL ) ) {
		$attachment->url = $image->imageURL;
	}
	if ( isset( $image->thumbURL ) ) {
		$attachment->thumbnail = $image->thumbURL;
	}
	if ( isset( $image->thumbPath ) ) {
		$attachment->thumbnail_path = $image->thumbPath;
	}

	return $attachment;
}

/**
 * Initiate async optimization request.
 *
 * @param $image_id
 * @param array $params
 */
function megaoptim_async_optimize_ngg_attachment( $image_id, $params = array() ) {
	megaoptim_async_task( array(
		'action'        => 'megaoptim_async_optimize_ngg_attachment',
		'_nonce'        => wp_create_nonce( 'megaoptim_async_optimize_ngg_attachment' . '_' . $image_id ),
		'attachment_id' => $image_id,
		'params'        => $params
	) );
}

/**
 * The attachment buttons?
 *
 * @param MGO_NextGenAttachment
 *
 * @return string
 */
function megaoptim_get_ngg_attachment_buttons( $attachment ) {
	return megaoptim_get_view( 'misc/buttons-ngg', array( 'data' => $attachment ) );
}


/**
 * Returns the media library backup dir
 * @return string
 */
function megaoptim_get_nextgen_backup_dir() {
	$backup_dir = megaoptim_get_backup_dir() . DIRECTORY_SEPARATOR . MGO_NextGenAttachment::TYPE;

	return apply_filters( 'megaoptim_nextgen_backup_dir', $backup_dir );
}

/**
 * Returns the backup path for  next gen attachment by ID
 *
 * @param $id
 * @param $path
 *
 * @return string
 */
function megaoptim_get_ngg_attachment_backup_path( $id, $path ) {
	$ext         = pathinfo( $path, PATHINFO_EXTENSION );
	$backup_path = megaoptim_get_nextgen_backup_dir() . DIRECTORY_SEPARATOR . $id;
	$backup_path = $backup_path . '.' . $ext;

	return $backup_path;
}