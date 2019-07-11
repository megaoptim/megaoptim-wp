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
 * Time: 3:42 PM
 */
class MGO_Wr2x extends MGO_BaseObject {

	/**
	 * The core class for working with WP Retina 2x plugin
	 * @var MGO_Wr2X_Core
	 */
	protected $core;

	/**
	 * MGO_Wr2x constructor.
	 */
	public function __construct() {
		$this->core = new MGO_Wr2X_Core();
		add_action( 'wp_ajax_wr2x_generate', array( $this, 'wp_ajax_wr2x_generate' ), 5, 0 );
		add_action( 'wp_ajax_wr2x_delete', array( $this, 'wp_ajax_wr2x_delete' ), 5, 0 );
		add_action( 'wp_ajax_wr2x_delete_full', array( $this, 'wp_ajax_wr2x_delete_full' ), 5, 0 );
		add_action( 'wp_ajax_wr2x_replace', array( $this, 'wp_ajax_wr2x_replace' ), 5, 0 );
		add_action( 'wp_ajax_wr2x_upload', array( $this, 'wp_ajax_wr2x_upload' ), 5, 0 );
	}

	/**
	 * Generate retina file
	 */
	public function wp_ajax_wr2x_generate() {
		$this->check_nonce( 'wr2x_generate' );
		$this->check_capability();
		$this->validate_attachment();
		$attachment_id = intval( $_POST['attachmentId'] );
		try {
			$attachment = new MGO_MediaAttachment( $attachment_id );
			$this->core->generate_images( $attachment );
			megaoptim_log( 'Retina attachments regenerated.' );
			$this->json( array(
				'results' => $this->core->get_retina_info( $attachment ),
				'message' => __( 'Retina files generated.', 'megaoptim' ),
			) );
		} catch ( MGO_Exception $e ) {
			$this->error( $e->getMessage() );
		}
	}

	/**
	 * Removes the full retina version along with the thumbnails
	 */
	public function wp_ajax_wr2x_delete() {
		$this->check_nonce( 'wr2x_delete' );
		$this->check_capability();
		$this->validate_attachment();
		$attachmentId = intval( $_POST['attachmentId'] );
		try {
			$attachment = new MGO_MediaAttachment( $attachmentId );
			$this->core->delete_attachment( $attachment, true );
			$this->core->update_issue_status( $attachmentId );
			megaoptim_log( 'Retina attachments deleted.' );
			$this->json( array(
				'results'      => $this->core->get_retina_info( $attachment ),
				'results_full' => $this->core->get_retina_info( $attachment, 'full' ),
				'message'      => __( "Retina files deleted.", 'wp-retina-2x' )
			) );
		} catch ( MGO_Exception $e ) {
			$this->error( array( 'message' => $e->getMessage() ) );
		}
	}

	/**
	 * Removes the full retina version
	 */
	public function wp_ajax_wr2x_delete_full() {
		$this->check_nonce( 'wr2x_delete_full' );
		$this->check_capability();
		$this->validate_attachment();
		$attachmentId = intval( $_POST['attachmentId'] );
		try {
			$attachment = new MGO_MediaAttachment( $attachmentId );
			$this->core->delete_full_attachment( $attachment );
			megaoptim_log( 'Retina full size attachment deleted.' );
			$this->json( array(
				'results' => $this->core->get_retina_info( $attachment, 'full' ),
				'message' => __( 'Full retina file deleted.', 'megaoptim' ),
			) );
		} catch ( MGO_Exception $e ) {
			$this->error( $e->getMessage() );
		}
	}

	public function wp_ajax_wr2x_replace() {

		$this->check_nonce( 'wr2x_replace' );
		$tmpfname     = $this->get_ajax_uploaded_file();
		$attachmentId = (int) $_POST['attachmentId'];
		try {
			$attachment   = new MGO_MediaAttachment( $attachmentId );
			$meta         = $attachment->get_metadata();
			$current_file = $attachment->get_path();
			$this->core->delete_attachment( $attachment, false );
			$pathinfo = pathinfo( $current_file );
			$basepath = $pathinfo['dirname'];

			// Let's clean everything first
			if ( wp_attachment_is_image( $attachmentId ) ) {
				$sizes = $this->core->get_image_sizes();
				foreach ( $sizes as $name => $attr ) {
					if ( isset( $meta['sizes'][ $name ] ) && isset( $meta['sizes'][ $name ]['file'] ) && file_exists( trailingslashit( $basepath ) . $meta['sizes'][ $name ]['file'] ) ) {
						$normal_file = trailingslashit( $basepath ) . $meta['sizes'][ $name ]['file'];
						$pathinfo    = pathinfo( $normal_file );
						$retina_file = trailingslashit( $pathinfo['dirname'] ) . $pathinfo['filename'] . $this->core->retina_extension() . $pathinfo['extension'];
						// Test if the file exists and if it is actually a file (and not a dir)
						// Some old WordPress Media Library are sometimes broken and link to directories
						if ( file_exists( $normal_file ) && is_file( $normal_file ) ) {
							unlink( $normal_file );
						}
						if ( file_exists( $retina_file ) && is_file( $retina_file ) ) {
							unlink( $retina_file );
						}
					}
				}
			}

			if ( file_exists( $current_file ) ) {
				unlink( $current_file );
			}

			// Insert the new file and delete the temporary one
			rename( $tmpfname, $current_file );
			chmod( $current_file, 0644 );

			// Remove megaoptim data
			$attachment->destroy();

			// is autoptimize?
			$is_auto_optimize = apply_filters( 'megaoptim_auto_optimize_media_attachment', MGO_Settings::instance()->isAutoOptimizeEnabled(), $attachment->get_id(), $attachment->get_metadata() );

			// Remove auto upload filter
			add_filter( 'megaoptim_auto_optimize_media_attachment', array(
				__CLASS__,
				'disable_auto_optimize'
			), WP_MEGAOPTIM_INT_MAX - 20 );

			// Generate the images
			wp_update_attachment_metadata( $attachmentId, wp_generate_attachment_metadata( $attachmentId, $current_file ) );
			wp_get_attachment_metadata( $attachmentId );

			// Update object data
			$attachment->refresh();

			// Regenerate retinas
			$this->core->generate_images( $attachment );

			// Remove the temporary filter that disables auto generation
			remove_filter( 'megaoptim_auto_optimize_media_attachment', array(
				__CLASS__,
				'disable_auto_optimize'
			), WP_MEGAOPTIM_INT_MAX - 20 );

			// Is autooptimize?
			if ( $is_auto_optimize ) {
				megaoptim_async_optimize_attachment( $attachment->get_id() );
			}

			$this->json( array(
				'results' => $this->core->get_retina_info( $attachment ),
				'message' => __( 'Images replaced successfully.', 'megaoptim' ),
			) );

		} catch ( MGO_Exception $e ) {
			$this->error( $e->getMessage() );
		}
	}


	public function wp_ajax_wr2x_upload() {

		try {
			$tmpfname     = $this->get_ajax_uploaded_file();
			$attachmentId = (int) $_POST['attachmentId'];
			$meta         = wp_get_attachment_metadata( $attachmentId );
			$current_file = get_attached_file( $attachmentId );
			$pathinfo     = pathinfo( $current_file );
			$retinafile   = trailingslashit( $pathinfo['dirname'] ) . $pathinfo['filename'] . $this->core->retina_extension() . $pathinfo['extension'];

			if ( file_exists( $retinafile ) ) {
				unlink( $retinafile );
			}
			// Insert the new file and delete the temporary one
			list( $width, $height ) = getimagesize( $tmpfname );
			if ( ! $this->core->get_the_real_core()->are_dimensions_ok( $width, $height, $meta['width'] * 2, $meta['height'] * 2 ) ) {
				$this->error( array(
					'message' => "This image has a resolution of ${width}×${height} but your Full Size image requires a retina image of at least " . ( $meta['width'] * 2 ) . "x" . ( $meta['height'] * 2 ) . "."
				) );
			}
			$this->core->get_the_real_core()->resize( $tmpfname, $meta['width'] * 2, $meta['height'] * 2, null, $retinafile );
			chmod( $retinafile, 0644 );
			unlink( $tmpfname );

			// Is autooptimize?
			$attachment       = new MGO_MediaAttachment( $attachmentId );
			$is_auto_optimize = apply_filters( 'megaoptim_auto_optimize_media_attachment', MGO_Settings::instance()->isAutoOptimizeEnabled(), $attachment->get_id(), $attachment->get_metadata() );
			megaoptim_retina_remove_full_attachment_data( $attachment );
			if ( $is_auto_optimize ) {
				megaoptim_async_optimize_attachment( $attachment->get_id() );
			}

			// Get the results
			$this->core->update_issue_status( $attachmentId );
		} catch ( Exception $e ) {
			$this->error( array(
				'results' => null,
				'message' => __( "Error: " . $e->getMessage(), 'wp-retina-2x' )
			) );
		}


		$this->json( array(
			'success' => true,
			'results' => $this->core->get_retina_info( $attachment ),
			'message' => __( "Uploaded successfully.", 'wp-retina-2x' ),
			'media'   => array(
				'id'       => $attachmentId,
				'src'      => wp_get_attachment_image_src( $attachmentId, 'thumbnail' ),
				'edit_url' => get_edit_post_link( $attachmentId, 'attribute' )
			)
		) );
	}

	/**
	 * Returns the uploaded file
	 * @return mixed
	 */
	public function get_ajax_uploaded_file() {
		$this->check_capability();
		$tmpfname  = $_FILES['file']['tmp_name'];
		$file_info = getimagesize( $tmpfname );
		if ( empty( $file_info ) ) {
			megaoptim_log( "The file is not an image or the upload went wrong." );
			unlink( $tmpfname );
			$this->error( "The file is not an image or the upload went wrong." );
		}
		$filedata = wp_check_filetype_and_ext( $tmpfname, $_POST['filename'] );
		if ( $filedata["ext"] == "" ) {
			megaoptim_log( "You cannot use this file (wrong extension? wrong type?)" );
			unlink( $current_file );
			$this->error( array(
				'success' => false,
				'message' => __( "You cannot use this file (wrong extension? wrong type?).", 'wp-retina-2x' )
			) );
		}
		megaoptim_log( "The temporary file was written successfully." );

		return $tmpfname;
	}

	public static function disable_auto_optimize() {
		return false;
	}

	/**
	 * Validates data
	 */
	public function validate_attachment() {
		if ( ! isset( $_POST['attachmentId'] ) ) {
			$this->json( array(
				'success' => false,
				'message' => __( "The attachment ID is missing.", 'megaoptim' )
			) );
		}
	}

	/**
	 * Checks nonce for the specified action
	 *
	 * @param string $action
	 */
	public function check_nonce( $action ) {
		if ( ! wp_verify_nonce( $_POST['nonce'], $action ) ) {
			$this->json( array(
				'success' => false,
				'message' => __( "Invalid API request.", 'wp-retina-2x' )
			) );
		}
	}

	/**
	 * Checks if the current user has sufficient permissions to perform the Ajax actions
	 */
	public function check_capability() {
		$cap = 'upload_files';
		if ( ! current_user_can( $cap ) ) {
			$this->json( array(
				'success' => false,
				'message' => __( "You do not have permission to upload files.", 'wp-retina-2x' )
			) );
		}
	}

	/**
	 * Output json
	 *
	 * @param $data
	 */
	public function json( $data ) {
		$data = array_merge( array(
			'success' => true,
			'message' => '',
		), $data );
		echo wp_json_encode( $data );
		die;
	}

	/**
	 * Send error
	 *
	 * @param $message
	 */
	public function error( $message ) {
		$this->json( array( 'success' => false, 'message' => $message ) );
	}

}

MGO_Wr2x::instance();