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

class MGO_As3cf_Util {

	/**
	 * The AS3CF Instance
	 * @var AS3CF_Plugin_Base|Amazon_S3_And_CloudFront
	 */
	protected $as3cf;

	/**
	 * MGO_As3cf_Util constructor.
	 *
	 * @param AS3CF_Plugin_Base $as3cf
	 */
	public function __construct( $as3cf ) {
		$this->as3cf = $as3cf;
	}


	/**
	 * Upload attachment to remote storage
	 *
	 * @param MGO_MediaAttachment $attachment
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function upload_attachment( $attachment ) {

		if ( ! $this->as3cf->get_setting( 'copy-to-s3' ) ) {
			return false;
		}
		$attachment_id = $attachment->get_id();
		$metadata = $attachment->get_metadata();
		return $this->as3cf->upload_attachment( $attachment_id, $metadata );
	}

	/**
	 * Removes attachment from remote storage.
	 *
	 * @param MGO_MediaAttachment $attachment
	 */
	public function remove_attachment( $attachment ) {

		$attachment_id = $attachment->get_id();
		$this->as3cf->delete_attachment( $attachment_id );
	}


	/**
	 * Re-upload attachment to remote storage (First remove the old one if any and then re-upload.)
	 *
	 * @param MGO_MediaAttachment $attachment
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function reupload_attachment( $attachment ) {

		// First remove the old attachment (if any)
		$this->remove_attachment( $attachment );

		return $this->upload_attachment( $attachment );

	}


	/**
	 * Returns the pat
	 *
	 * @param $paths
	 * @param bool $check_exists
	 *
	 * @return array
	 */
	public function get_paths_including_webp( $paths, $check_exists = true ) {
		$new_paths = array();
		foreach ( $paths as $size => $path ) {
			$path_webp          = $path . '.webp';
			$new_paths[ $size ] = $path;
			if ( $check_exists ) {
				if ( file_exists( $path_webp ) ) {
					$new_paths[ $size . '_webp' ] = $path_webp;
				}
			} else {
				$new_paths[ $size . '_webp' ] = $path_webp;
			}
		}

		return $new_paths;
	}

	/**
	 * Logs
	 *
	 * @param $tag
	 * @param $msg
	 */
	public function log( $tag, $msg ) {
		megaoptim_log( 'MegaOptim -> WP Offload Media: In ' . $tag . ' message: ' . $msg );
	}

}
