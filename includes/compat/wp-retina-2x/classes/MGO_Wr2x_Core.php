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
 * Date: 9/5/2018
 * Time: 5:30 PM
 */
class MGO_Wr2X_Core {

	/**
	 * @param MGO_MediaAttachment $attachment |string
	 *
	 * @throws MGO_Exception
	 */
	public function generate_images( $attachment ) {

		if ( $attachment instanceof MGO_MediaAttachment ) {
			$optimized_file_path = false;
			if ( $attachment->is_size_optimized( 'full' ) && $attachment->has_backup() ) {
				$optimized_file_path = $this->try_restore_original( $attachment );
			}
			// Create retina images.
			wr2x_generate_images( $attachment->get_metadata() );

			// Put the optimized full-sized file back.
			if ( $optimized_file_path ) {
				megaoptim_log( 'Restring optimized file from temporary safe place...', 'wr2x.log' );
				$this->move_optimized_back( $attachment, $optimized_file_path );
			}
		} else {
			// Create retina images.
			wr2x_generate_images( $attachment );
		}
	}


	/**
	 * Moves the optimized file to safe place.
	 *
	 * @param  MGO_MediaAttachment $attachment
	 *
	 * @return bool|string
	 * @throws MGO_Exception
	 */
	public function move_optimized_file_to_safe( $attachment ) {
		$backup_dir = megaoptim_get_tmp_path() . DIRECTORY_SEPARATOR . 'wr2x';
		if ( ! file_exists( $backup_dir ) ) {
			@mkdir( $backup_dir );
		}
		clearstatcache();
		if ( ! file_exists( $backup_dir ) ) {
			throw new MGO_Exception( 'Make sure ' . dirname( $backup_dir ) . ' is writable!' );
		}
		$file_path   = $attachment->get_path();
		$file_name   = basename( $file_path );
		$backup_path = $backup_dir . DIRECTORY_SEPARATOR . $file_name;
		if ( @rename( $file_path, $backup_path ) ) {
			return $backup_path;
		} else {
			return false;
		}
	}

	/**
	 * @param MGO_MediaAttachment $attachment
	 *
	 * @return bool|string
	 * @throws MGO_Exception
	 */
	public function try_restore_original( $attachment ) {
		$original_backup_path = $attachment->get_backup_path();
		$original_file_path   = $attachment->get_path();
		$optimized_file_path  = $this->move_optimized_file_to_safe( $attachment );
		//megaoptim_log( $optimized_backup_path, 'wr2x.log' );
		if ( ! $optimized_file_path ) {
			return false;
		}

		if ( ! @copy( $original_backup_path, $original_file_path ) ) {
			throw new MGO_Exception( 'Please make sure the directory ' . dirname( $original_file_path ) . ' is writable' );
		}

		return $optimized_file_path;
	}


	/**
	 * Restores the optimized file
	 *
	 * @param MGO_MediaAttachment $attachment
	 * @param $backup_path
	 *
	 * @return bool
	 */
	public function move_optimized_back( $attachment, $backup_path ) {
		$attached_file = $attachment->get_path();
		if ( file_exists( $backup_path ) ) {
			if ( ! @rename( $backup_path, $attached_file ) ) {
				return false;
			} else {
				return true;
			}
		}

		return false;
	}


	/**
	 * Removes the retina images (all or only thumbnails)
	 *
	 * @param MGO_MediaAttachment $attachment
	 * @param $delete_full_image
	 *
	 * @return true
	 */
	public function delete_attachment( $attachment, $delete_full_image ) {
		wr2x_delete_attachment( $attachment->get_id(), $delete_full_image );
		megaoptim_retina_remove_attachment_data( $attachment, $delete_full_image );

		return true;
	}

	/**
	 * Removes the full attachment
	 *
	 * @param  MGO_MediaAttachment $attachment
	 *
	 * @return bool
	 * @throws MGO_Exception
	 */
	public function delete_full_attachment( $attachment ) {
		$originalfile = $attachment->get_path();
		$pathinfo     = pathinfo( $originalfile );
		$retina_file  = trailingslashit( $pathinfo['dirname'] ) . $pathinfo['filename'] . $this->retina_extension() . $pathinfo['extension'];
		if ( $retina_file && file_exists( $retina_file ) ) {
			if ( @unlink( $retina_file ) ) {
				megaoptim_retina_remove_full_attachment_data( $attachment );

				return true;
			} else {
				throw new MGO_Exception( 'Can not delete the full size 2@x retina file. Please check your permissions.' );
			}
		}

		return false;
	}


	/**
	 * UPDATE THE ISSUE STATUS OF THIS ATTACHMENT
	 *
	 * @param $attachmentId
	 * @param null $issues
	 * @param null $info
	 */
	function update_issue_status( $attachmentId, $issues = null, $info = null ) {
		global $wr2x_core;
		$can_use_core = $this->is_wr2x_core( array( 'update_issue_status' ) );
		if ( ! $can_use_core ) {
			return;
		}
		$wr2x_core->update_issue_status( $attachmentId, $issues, $info );
	}

	/**
	 * Returns retina info
	 *
	 * @param MGO_MediaAttachment $attachment
	 * @param string $type
	 *
	 * @return array|string
	 */
	public function get_retina_info( $attachment, $type = 'basic' ) {
		global $wr2x_core;

		$can_use_core = $this->is_wr2x_core( array(
			'retina_info',
			'html_get_basic_retina_info_full',
			'html_get_basic_retina_info'
		) );

		if ( ! $can_use_core ) {
			return '';
		}

		$attachment_id = $attachment->get_id();
		$info          = $wr2x_core->retina_info( $attachment_id );

		if ( 'full' === $type ) {
			return array(
				$attachment_id => $wr2x_core->html_get_basic_retina_info_full( $attachment_id, $info ),
			);
		}

		return array(
			$attachment_id => $wr2x_core->html_get_basic_retina_info( $attachment_id, $info ),
		);
	}

	/**
	 * Returns the image sizes
	 * @return array
	 */
	public function get_image_sizes() {
		global $wr2x_core;
		if ( ! $this->is_wr2x_core( array( 'get_image_sizes' ) ) ) {
			return array();
		}

		return $wr2x_core->get_image_sizes();
	}

	/**
	 * Check if wr2x_core object is there
	 *
	 * @param array $methods
	 *
	 * @return bool
	 */
	public function is_wr2x_core( $methods = array() ) {
		global $wr2x_core;

		$valid = $wr2x_core && is_object( $wr2x_core );

		foreach ( $methods as $method ) {
			$valid = $valid && method_exists( $wr2x_core, $method );
			if ( ! $valid ) {
				break;
			}
		}

		return $valid;
	}

	/**
	 * Returns the Meow_WR2X_Core object.
	 * @return bool|Meow_WR2X_Core
	 */
	public function get_the_real_core() {
		if ( $this->is_wr2x_core() ) {
			global $wr2x_core;

			return $wr2x_core;
		}

		return false;
	}


	/**
	 * Return the retina extension followed by a dot
	 * @return string
	 */
	public function retina_extension() {
		return '@2x.';
	}
}