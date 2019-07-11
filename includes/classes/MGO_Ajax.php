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

class MGO_Ajax extends MGO_BaseObject {

	const NONCE_DEFAULT = "megaoptimgnonce";
	const NONCE_SETTINGS = "megaoptimjsnonce";
	const NONCE_OPTIMIZER = "megaoptimstartnonce";

	/**
	 * MGO_Ajax constructor.
	 */
	public function __construct() {

		// General
		add_action( 'wp_ajax_megaoptim_set_apikey', array( $this, 'set_api_key' ) );
		add_action( 'wp_ajax_megaoptim_instructions_dismiss', array( $this, 'dismiss_instructions' ) );
		add_action( 'wp_ajax_megaoptim_save_settings', array( $this, 'save_settings' ) );
		add_action( 'wp_ajax_megaoptim_save_advanced_settings', array( $this, 'save_advanced_settings' ) );
		add_action( 'wp_ajax_megaoptim_export_report', array( $this, 'export_report' ) );

		// Optimizer
		add_action( 'wp_ajax_megaoptim_optimize_attachment', array( $this, 'optimize_attachment' ) );
		add_action( 'wp_ajax_megaoptim_optimize_ld_attachment', array( $this, 'optimize_ld_attachment' ) );

		add_action( 'wp_ajax_megaoptim_directory_tree', array( $this, 'directory_tree' ) );
		add_action( 'wp_ajax_megaoptim_directory_data', array( $this, 'directory_data' ) );

		add_action( 'wp_ajax_megaoptim_library_data', array( $this, 'library_data' ) );

		add_action( 'wp_ajax_megaoptim_empty_backup_dir', array( $this, 'empty_backup_dir' ) );
		add_action( 'wp_ajax_megaoptim_ticker_upload', array( $this, 'ticker_upload' ) );

		add_action( 'wp_ajax_megaoptim_get_profile', array( $this, 'get_profile' ) );
		add_action( 'wp_ajax_megaoptim_optimize_single_attachment', array( $this, 'optimize_single_attachment' ) );
		add_action( 'wp_ajax_megaoptim_restore_single_attachment', array( $this, 'restore_single_attachment' ) );

		add_action( 'wp_ajax_megaoptim_api_register', array( $this, 'api_register' ) );
	}

	/**
	 * Handles registration via popup
	 */
	public function api_register() {
		if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], MGO_Ajax::NONCE_DEFAULT ) ) {
			wp_send_json_error( array( 'error' => __( 'Internal server error.', 'megaoptim' ) ) );
		}

		$step = isset( $_REQUEST['step'] ) ? $_REQUEST['step'] : 0;

		switch ( $step ) {
			case 1:
				$data   = array();
				$fields = array(
					'first_name',
					'last_name',
					'email',
					'password',
					'password_confirmation',
					'terms_and_conditions'
				);
				foreach ( $fields as $field ) {
					if ( isset( $_POST[ $field ] ) ) {
						$data[ $field ] = $_POST[ $field ];
					}
				}
				$response = MGO_Profile::register( $data );
				if ( is_wp_error( $response ) ) {
					wp_send_json_error( $response->get_error_message() );
				} else {
					$response = json_decode( $response['body'] );
					if ( $response->status === 'ok' ) {
						if ( megaoptim_validate_email( $response->result->email ) ) {
							update_option( 'megaoptim_registration_email', sanitize_text_field( $response->result->email ) );
							wp_send_json_success( __( 'WooHoo! You are all set!' ) );
						} else {
							wp_send_json_error( array( 'email' => 'Invalid email!' ) );
						}

					} else {
						wp_send_json_error( $response );
					}
				}
				break;
			case 2:
				if ( isset( $_REQUEST['api_key'] ) && ! empty( $_REQUEST['api_key'] ) ) {
					try {
						$profile = new MGO_Profile( $_REQUEST['api_key'] );
						if ( $profile->is_valid_apikey() ) {
							MGO_Settings::instance()->update( array(
								MGO_Settings::API_KEY => $_REQUEST['api_key']
							) );
							wp_send_json_success();
						} else {
							wp_send_json_error( __( 'Invalid API key.' ), 'megaoptim' );
						}
					} catch ( MGO_Exception $e ) {
						wp_send_json_error( $e->getMessage() );
					}

				}
				break;
			default:
				wp_send_json_error( 'Invalid step.' );
		}
		die;
	}


	/**
	 * Optimize Attachment for Bulk Optimization process
	 */
	public function optimize_attachment() {

		if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], MGO_Ajax::NONCE_OPTIMIZER ) ) {
			wp_send_json_error( array( 'error' => __( 'Internal server error.', 'megaoptim' ) ) );
		}
		if ( ! isset( $_REQUEST['attachment'] ) ) {
			wp_send_json_error( array( 'error' => __( 'No attachment provided.', 'megaoptim' ) ) );
		}
		$attachment_id = $_REQUEST['attachment']['ID'];
		try {
			$result     = MGO_MediaLibrary::instance()->optimize( $attachment_id );
			$attachment = $result->get_attachment();

			$user       = null;
			$last_response   = $result->get_last_response();
			if($last_response !== false) {
				$user = $last_response->getUser();
			}

			if(!is_null($user)) {
				$tokens = $user->getTokens();
			} else {
				$profile = new MGO_Profile();
				$tokens = $profile->get_tokens_count();
			}

			if ( $attachment instanceof MGO_MediaAttachment ) {
				$response['attachment'] = $attachment->get_optimization_stats();
				$response['general']    = $result->get_optimization_info();
				$response['tokens']     = $tokens;
				wp_send_json_success( $response );
			} else {
				wp_send_json_error( array(
					'error'        => __( 'Attachment was not optimized.', 'megaoptim' ),
					'can_continue' => 1
				) );
			}
		} catch ( MGO_Exception $e ) {
			wp_send_json_error( array( 'error' => $e->getMessage(), 'can_continue' => 1 ) );
		}
	}

	public function optimize_ld_attachment() {
		if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], MGO_Ajax::NONCE_OPTIMIZER ) ) {
			wp_send_json_error( array( 'error' => __( 'Internal server error.', 'megaoptim' ) ) );
		}
		if ( ! isset( $_REQUEST['attachment'] ) ) {
			wp_send_json_error( array( 'error' => __( 'No attachment provided.', 'megaoptim' ) ) );
		}
		try {
			$result     = MGO_LocalDirectories::instance()->optimize( new MGO_File( $_REQUEST['attachment'] ) );
			$attachment = $result->get_attachment();
			if ( $attachment instanceof MGO_LocalFileAttachment ) {
				$response['attachment'] = $attachment->get_optimization_stats();
				$response['general']    = $result->get_optimization_info();
				$response['tokens']     = $result->get_last_response()->getUser()->getTokens();
				wp_send_json_success( $response );
			} else {
				wp_send_json_error( array(
					'error'        => __( 'File was not optimized.', 'megaoptim' ),
					'can_continue' => 1
				) );
			}
		} catch ( MGO_Exception $e ) {
			wp_send_json_error( array( 'error' => $e->getMessage(), 'can_continue' => 1 ) );
		}
	}

	/**
	 * Restore attachment from the WP interface
	 */
	public function set_api_key() {

		if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], self::NONCE_DEFAULT ) ) {
			wp_send_json_error( array( 'error' => __( 'Internal server error.', 'megaoptim' ), 'data' => $_REQUEST ) );
		}
		$errors = array();
		if ( ! isset( $_REQUEST['apikey'] ) || strlen( trim( $_REQUEST['apikey'] ) ) != 32 ) {
			array_push( $errors, __( 'Please provide valid MegaOptim API key.', 'megaoptim' ) );
		} else {
			try {
				$response = MGO_Profile::get_user_by_api_key( $_REQUEST['apikey'] );
				if ( $response === false ) {
					array_push( $errors, __( 'The MegaOptim api can not be reached. Please contact support.', 'megaoptim' ) );
				} else if ( ! isset( $response['status'] ) ) {
					array_push( $errors, __( 'Invalid results received. Please contact support.', 'megaoptim' ) );
				} else if ( $response['status'] != 'ok' ) {
					array_push( $errors, __( 'Your API key is invalid. Please make sure you use correct API issued by MegaOptim.com', 'megaoptim' ) );
				}
			} catch ( MGO_Exception $e ) {
				array_push( $errors, $e->getMessage() );
			}
		}
		if ( count( $errors ) === 0 ) {
			MGO_Settings::setApiKey( $_REQUEST['apikey'] );
			try {
				new MGO_Profile( $_REQUEST['apikey'] );
				wp_send_json_success( array( 'message' => __( 'Your API key is now set up, you can configure your settings on the plugin Settings page. Redirecting to settings page', 'megaoptim' ) ) );
			} catch ( MGO_Exception $e ) {
				wp_send_json_error( array( 'error' => $e->getMessage() ) );
			}
		} else {
			wp_send_json_error( array( 'error' => $errors[0], 'other' => $response ) );
		}
	}


	/**
	 * Saves the state of the instruction message once dismissed.
	 */
	public function dismiss_instructions() {
		if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], self::NONCE_DEFAULT ) ) {
			wp_send_json_error( __( 'Internal server error.', 'megaoptim' ) );
		}

		if ( isset( $_REQUEST['dismiss_instructions'] ) && is_numeric( $_REQUEST['dismiss_instructions'] ) ) {
			if ( 1 === intval( $_REQUEST['dismiss_instructions'] ) ) {
				update_option( 'megaoptim_instructions_hide', '1' );
				wp_send_json_success( __( 'Done. Instructions will be hidden.', 'megaoptim' ) );
			}
		}
		wp_send_json_success( __( 'Error, invalid data supplied.', 'megaoptim' ) );
	}


	/**
	 * Saves the Settings page in the database
	 */
	public function save_settings() {
		if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], self::NONCE_SETTINGS ) ) {
			wp_send_json_error( __( 'Internal server error.', 'megaoptim' ) );
		}
		$data   = array();
		$errors = array();

		//validations
		if ( isset( $_REQUEST[ MGO_Settings::API_KEY ] ) && ! empty( $_REQUEST[ MGO_Settings::API_KEY ] ) ) {
			try {
				$response = MGO_Profile::get_user_by_api_key( $_REQUEST['megaoptimpt_api_key'] );
				if ( $response === false ) {
					array_push( $errors, __( 'Could not verify your API key. The API can not be reached. Please contact support.', 'megaoptim' ) );
				} else if ( ! isset( $response['status'] ) ) {
					array_push( $errors, __( 'We received invalid response trying to authenticate your api key. Please contact support.', 'megaoptim' ) );
				} else if ( $response['status'] != 'ok' ) {
					array_push( $errors, __( 'Your API key is invalid. Please make sure you use correct API issued by MegaOptim.com', 'megaoptim' ) );
				}
			} catch ( MGO_Exception $e ) {
				array_push( $errors, $e->getMessage() );
			}
		}

		if ( ! isset( $_REQUEST[ MGO_Settings::RESIZE_LARGE_IMAGES ] ) ) {
			$megaoptimpt_resize_large_images = 0;
		} else {
			$megaoptimpt_resize_large_images = ( $_REQUEST[ MGO_Settings::RESIZE_LARGE_IMAGES ] == '1' ) ? 1 : 0;
		}

		if ( isset( $_REQUEST[ MGO_Settings::HTTP_USER ] ) ) {
			$megaoptimpt_http_user = $_REQUEST[ MGO_Settings::HTTP_USER ];
		} else {
			$megaoptimpt_http_user = '';
		}

		if ( isset( $_REQUEST[ MGO_Settings::HTTP_PASS ] ) ) {
			$megaoptimpt_http_pass = $_REQUEST[ MGO_Settings::HTTP_PASS ];
		} else {
			$megaoptimpt_http_pass = '';
		}

		if ( $megaoptimpt_resize_large_images ) {
			if ( ! isset( $_REQUEST[ MGO_Settings::MAX_WIDTH ] ) && ! isset( $_REQUEST[ MGO_Settings::MAX_HEIGHT ] ) ) {
				array_push( $errors, __( 'If you enabled the option for mimimum image size, you need to set at least one of the fields with correct number greater than 100.', 'megaoptim' ) );
			} else {
				if ( isset( $_REQUEST[ MGO_Settings::MAX_WIDTH ] ) && is_numeric( $_REQUEST[ MGO_Settings::MAX_WIDTH ] ) && $_REQUEST[ MGO_Settings::MAX_WIDTH ] <= 100 ) {
					array_push( $errors, __( 'Image maximum width should be greater than 100.', 'megaoptim' ) );
				}
				if ( isset( $_REQUEST[ MGO_Settings::MAX_HEIGHT ] ) && is_numeric( $_REQUEST[ MGO_Settings::MAX_HEIGHT ] ) && $_REQUEST[ MGO_Settings::MAX_HEIGHT ] <= 100 ) {
					array_push( $errors, __( 'Image maximum height should be greater than 100.', 'megaoptim' ) );
				}
			}
		}
		//Storage
		if ( count( $errors ) === 0 ) {
			$data = array();
			if ( ! $megaoptimpt_resize_large_images ) {
				$data[ MGO_Settings::MAX_WIDTH ]           = '';
				$data[ MGO_Settings::MAX_HEIGHT ]          = '';
				$data[ MGO_Settings::RESIZE_LARGE_IMAGES ] = '';

			} else {
				// Update max image sizes
				$data[ MGO_Settings::MAX_WIDTH ]           = ( isset( $_REQUEST[ MGO_Settings::MAX_WIDTH ] ) && is_numeric( $_REQUEST[ MGO_Settings::MAX_WIDTH ] ) ) ? $_REQUEST[ MGO_Settings::MAX_WIDTH ] : '';
				$data[ MGO_Settings::MAX_HEIGHT ]          = ( isset( $_REQUEST[ MGO_Settings::MAX_HEIGHT ] ) && is_numeric( $_REQUEST[ MGO_Settings::MAX_HEIGHT ] ) ) ? $_REQUEST[ MGO_Settings::MAX_HEIGHT ] : '';
				$data[ MGO_Settings::RESIZE_LARGE_IMAGES ] = 1;
			}
			// Update other metadata
			$data[ MGO_Settings::API_KEY ]       = $_REQUEST[ MGO_Settings::API_KEY ];
			$data[ MGO_Settings::AUTO_OPTIMIZE ] = isset( $_REQUEST[ MGO_Settings::AUTO_OPTIMIZE ] ) ? $_REQUEST[ MGO_Settings::AUTO_OPTIMIZE ] : 0;
			$data[ MGO_Settings::COMPRESSION ]   = isset( $_REQUEST[ MGO_Settings::COMPRESSION ] ) ? $_REQUEST[ MGO_Settings::COMPRESSION ] : 'lossy';
			$data[ MGO_Settings::PRESERVE_EXIF ] = isset( $_REQUEST[ MGO_Settings::PRESERVE_EXIF ] ) ? $_REQUEST[ MGO_Settings::PRESERVE_EXIF ] : 0;
			$data[ MGO_Settings::CMYKTORGB ]     = isset( $_REQUEST[ MGO_Settings::CMYKTORGB ] ) ? $_REQUEST[ MGO_Settings::CMYKTORGB ] : 0;
			$data[ MGO_Settings::HTTP_USER ]     = $megaoptimpt_http_user;
			$data[ MGO_Settings::HTTP_PASS ]     = $megaoptimpt_http_pass;
			MGO_Settings::instance()->update( $data );
			// Return response
			$data['success'] = true;
			$data['message'] = __( 'Settings updated successfully!', 'megaoptim' );
		} else {
			$data['success'] = false;
			$data['message'] = __( 'Please fix the following errors:', 'megaoptim' );
			$data['errors']  = $errors;
		}
		die( json_encode( $data ) );
	}

	public function save_advanced_settings() {
		if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], self::NONCE_SETTINGS ) ) {
			wp_send_json_error( __( 'Internal server error.', 'megaoptim' ) );
		}
		$data   = array();
		$errors = array();

		// Image sizes
		if ( ! isset( $_REQUEST[ MGO_Settings::IMAGE_SIZES ] ) OR ! is_array( $_REQUEST[ MGO_Settings::IMAGE_SIZES ] )
		     OR count( $_REQUEST[ MGO_Settings::IMAGE_SIZES ] ) === 0
		) {
			array_push( $errors, __( 'No image sizes selected. Please select some!', 'megaoptim' ) );
		}

		// Cloud flare
		$has_cf_email  = isset( $_REQUEST[ MGO_Settings::CLOUDFLARE_EMAIL ] ) && ! empty( $_REQUEST[ MGO_Settings::CLOUDFLARE_EMAIL ] );
		$has_cf_apikey = isset( $_REQUEST[ MGO_Settings::CLOUDFLARE_API_KEY ] ) && ! empty( $_REQUEST[ MGO_Settings::CLOUDFLARE_API_KEY ] );
		$has_cf_zone   = isset( $_REQUEST[ MGO_Settings::CLOUDFLARE_ZONE ] ) && ! empty( $_REQUEST[ MGO_Settings::CLOUDFLARE_ZONE ] );
		if ( $has_cf_email || $has_cf_apikey || $has_cf_zone ) {
			if ( ! $has_cf_email ) {
				array_push( $errors, __( 'You are missing CloudFlare Email. In order to use the feature you need to set all the fields.', 'megaoptim' ) );
			}
			if ( ! $has_cf_apikey ) {
				array_push( $errors, __( 'You are missing CloudFlare Api Key. In order to use the feature you need to set all the fields.', 'megaoptim' ) );
			}
			if ( ! $has_cf_zone ) {
				array_push( $errors, __( 'You are missing CloudFlare Zone ID. In order to use the feature you need to set all the fields.', 'megaoptim' ) );
			}
		}

		if ( empty( $errors ) ) {
			$data[ MGO_Settings::IMAGE_SIZES ]                      = $_REQUEST[ MGO_Settings::IMAGE_SIZES ];
			$data[ MGO_Settings::RETINA_IMAGE_SIZES ]               = isset( $_REQUEST[ MGO_Settings::RETINA_IMAGE_SIZES ] ) ? $_REQUEST[ MGO_Settings::RETINA_IMAGE_SIZES ] : '';
			$data[ MGO_Settings::BACKUP_MEDIA_LIBRARY_ATTACHMENTS ] = isset( $_REQUEST[ MGO_Settings::BACKUP_MEDIA_LIBRARY_ATTACHMENTS ] ) ? $_REQUEST[ MGO_Settings::BACKUP_MEDIA_LIBRARY_ATTACHMENTS ] : 0;
			$data[ MGO_Settings::BACKUP_NEXTGEN_ATTACHMENTS ]       = isset( $_REQUEST[ MGO_Settings::BACKUP_NEXTGEN_ATTACHMENTS ] ) ? $_REQUEST[ MGO_Settings::BACKUP_NEXTGEN_ATTACHMENTS ] : 0;
			$data[ MGO_Settings::BACKUP_FOLDER_FILES ]              = isset( $_REQUEST[ MGO_Settings::BACKUP_FOLDER_FILES ] ) ? $_REQUEST[ MGO_Settings::BACKUP_FOLDER_FILES ] : 0;
			$data[ MGO_Settings::CLOUDFLARE_EMAIL ]                 = isset( $_REQUEST[ MGO_Settings::CLOUDFLARE_EMAIL ] ) ? $_REQUEST[ MGO_Settings::CLOUDFLARE_EMAIL ] : '';
			$data[ MGO_Settings::CLOUDFLARE_API_KEY ]               = isset( $_REQUEST[ MGO_Settings::CLOUDFLARE_API_KEY ] ) ? $_REQUEST[ MGO_Settings::CLOUDFLARE_API_KEY ] : '';
			$data[ MGO_Settings::CLOUDFLARE_ZONE ]                  = isset( $_REQUEST[ MGO_Settings::CLOUDFLARE_ZONE ] ) ? $_REQUEST[ MGO_Settings::CLOUDFLARE_ZONE ] : '';

			// WebP Management
			$data[ MGO_Settings::WEBP_CREATE_IMAGES ]     = isset( $_REQUEST[ MGO_Settings::WEBP_CREATE_IMAGES ] ) ? $_REQUEST[ MGO_Settings::WEBP_CREATE_IMAGES ] : 0;
			$data[ MGO_Settings::WEBP_DELIVERY_METHOD ]   = isset( $_REQUEST[ MGO_Settings::WEBP_DELIVERY_METHOD ] ) ? $_REQUEST[ MGO_Settings::WEBP_DELIVERY_METHOD ] : 'none';
			$data[ MGO_Settings::WEBP_TARGET_TO_REPLACE ] = isset( $_REQUEST[ MGO_Settings::WEBP_TARGET_TO_REPLACE ] ) ? $_REQUEST[ MGO_Settings::WEBP_TARGET_TO_REPLACE ] : 'filters';

			// If WebP is disabled then set picturefill to 0
			if ( ! $data[ MGO_Settings::WEBP_CREATE_IMAGES ] ) {
				$data[ MGO_Settings::WEBP_PICTUREFILL ] = 0;
			} else {
				$data[ MGO_Settings::WEBP_PICTUREFILL ] = isset( $_REQUEST[ MGO_Settings::WEBP_PICTUREFILL ] ) ? $_REQUEST[ MGO_Settings::WEBP_PICTUREFILL ] : 0;
			}
			// Write .htaccess automatically upon save if the site is on apache or litespeed.
			// If delivery method is switched back to other remove the snippet from .htaccess
			if ( $data[ MGO_Settings::WEBP_DELIVERY_METHOD ] === 'rewrite' ) {
				$supported = 0;
				foreach ( array( 'litespeed', 'apache' ) as $webserver_name ) {
					if ( megaoptim_contains( strtolower( $_SERVER['SERVER_SOFTWARE'] ), $webserver_name ) ) {
						$supported = 1;
					}
				}
				if ( $supported ) {
					megaoptim_add_webp_support_via_htaccess();
				} else {
					megaoptim_remove_webp_support_via_htaccess();
				}
			} else {
				megaoptim_remove_webp_support_via_htaccess();
			}

			// Update
			MGO_Settings::instance()->update( $data );
			// Return response
			$data['success'] = true;
			$data['message'] = __( 'Settings updated successfully!', 'megaoptim' );
		} else {
			$data['success'] = false;
			$data['message'] = __( 'Please fix the following errors:', 'megaoptim' );
			$data['errors']  = $errors;
		}
		die( json_encode( $data ) );
	}

	/**
	 * Convert array to csv
	 */
	public function export_report() {
		if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], self::NONCE_SETTINGS ) ) {
			wp_send_json_error( __( 'Internal server error.', 'megaoptim' ) );
		}
		$name = 'megaoptim-report-' . time() . '.json';
		header( 'Content-disposition: attachment; filename=' . $name );
		header( 'Content-type: application/json' );
		$debug = new MGO_Debug();
		die( json_encode( $debug->generate_report() ) );
	}

	public function library_data() {
		if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], self::NONCE_DEFAULT ) ) {
			wp_send_json_error( __( 'Internal server error.', 'megaoptim' ) );
		}

		if ( ! isset( $_REQUEST['context'] ) ) {
			wp_send_json_error( __( 'Invalid context.', 'megaoptim' ) );
		} else {
			$context = $_REQUEST['context'];
			switch ( $context ) {
				case MEGAOPTIM_TYPE_MEDIA_ATTACHMENT:
					$stats = MGO_MediaLibrary::instance()->get_stats( true );
					break;
				default:
					$stats = apply_filters( 'megaoptim_library_data', null, $context );
			}
			if ( ! is_null( $stats ) ) {
				wp_send_json_success( $stats );
			} else {
				wp_send_json_error( 'Unsupported context.', 'megaoptim' );
			}
		}
		die;
	}

	/**
	 * Generates directory tree for given directory
	 */
	public function directory_tree() {

		if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], self::NONCE_DEFAULT ) ) {
			die;
		}

		if ( ! array_key_exists( 'HTTP_REFERER', $_SERVER ) ) {
			exit( 'No direct script access allowed' );
		}

		/**
		 * jQuery File Tree PHP Connector
		 *
		 * Version 1.1.0
		 *
		 * @author - Cory S.N. LaViska A Beautiful Site (http://abeautifulsite.net/)
		 * @author - Dave Rogers - https://github.com/daverogers/jQueryFileTree
		 *
		 * History:
		 *
		 * 1.1.1 - SECURITY: forcing root to prevent users from determining system's file structure (per DaveBrad)
		 * 1.1.0 - adding multiSelect (checkbox) support (08/22/2014)
		 * 1.0.2 - fixes undefined 'dir' error - by itsyash (06/09/2014)
		 * 1.0.1 - updated to work with foreign characters in directory/file names (12 April 2008)
		 * 1.0.0 - released (24 March 2008)
		 *
		 * Output a list of files for jQuery File Tree
		 */

		/**
		 * filesystem root - USER needs to set this!
		 * -> prevents debug users from exploring system's directory structure
		 * ex: $root = $_SERVER['DOCUMENT_ROOT'];
		 */
		//$root = null;
		$root = megaoptim_get_wp_root_path();

		if ( ! $root ) {
			exit( "ERROR: Root filesystem directory not set in jqueryFileTree.php" );
		}

		$postDir = rawurldecode( $root . ( isset( $_POST['dir'] ) ? $_POST['dir'] : null ) );

		// set checkbox if multiSelect set to true
		$checkbox    = ( isset( $_POST['multiSelect'] ) && $_POST['multiSelect'] == 'true' ) ? "<input type='checkbox' />" : null;
		$onlyFolders = ( isset( $_POST['onlyFolders'] ) && $_POST['onlyFolders'] == 'true' ) ? true : false;
		$onlyFiles   = ( isset( $_POST['onlyFiles'] ) && $_POST['onlyFiles'] == 'true' ) ? true : false;

		if ( file_exists( $postDir ) ) {

			$files     = scandir( $postDir );
			$returnDir = substr( $postDir, strlen( $root ) );

			natcasesort( $files );

			if ( count( $files ) > 2 ) { // The 2 accounts for . and ..

				echo "<ul class='jqueryFileTree'>";

				foreach ( $files as $file ) {
					$htmlRel  = htmlentities( $returnDir . $file, ENT_QUOTES );
					$htmlName = htmlentities( $file );
					$ext      = preg_replace( '/^.*\./', '', $file );

					if ( file_exists( $postDir . $file ) && $file != '.' && $file != '..' ) {
						if ( is_dir( $postDir . $file ) && ( ! $onlyFiles || $onlyFolders ) ) {

							if ( megaoptim_is_excluded( $postDir . $file ) || ( ! megaoptim_dir_contains_images( $postDir . $file ) && ! megaoptim_dir_contains_children( $postDir ) ) ) {
								continue;
							}
							echo "<li class='directory collapsed'>{$checkbox}<a rel='" . $htmlRel . "/'>" . $htmlName . "</a> <i class='megaoptim-select-directory fa fa-check' id='" . md5( $htmlRel ) . "'></i></li>";
						} else if ( ! $onlyFolders || $onlyFiles ) {
							echo "<li class='file ext_{$ext}'>{$checkbox}<a rel='" . $htmlRel . "'>" . $htmlName . "</a></li>";
						}
					}
				}

				echo "</ul>";
			}
		}
		die;
	}

	public function directory_data() {
		if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], self::NONCE_DEFAULT ) ) {
			wp_send_json_error( __( 'Internal server error.', 'megaoptim' ) );
		}
		$directory = isset( $_REQUEST['dir'] ) ? $_REQUEST['dir'] : '';
		if ( ! file_exists( $directory ) ) {
			// dir is still ones inside wordpress dir. without the /home/user/htdocs/ path.
			$root_path = rtrim( megaoptim_get_wp_root_path(), '/' );
			$directory = $root_path . $directory;
		}
		if ( ! file_exists( $directory ) || ! is_dir( $directory ) ) {
			wp_send_json_error();
		} else {
			$additional_data = array();
			if ( isset( $_REQUEST['recursive'] ) && $_REQUEST['recursive'] == 1 ) {
				$additional_data['recursive'] = 1;
			} else {
				$additional_data['recursive'] = 0;
			}
			$stats = MGO_LocalDirectories::instance()->get_stats( $directory, $additional_data );
			megaoptim_log( $stats );
			wp_send_json_success( $stats );
		}
		die;
	}

	public function empty_backup_dir() {
		if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], self::NONCE_DEFAULT ) ) {
			wp_send_json_error( __( 'Internal server error.', 'megaoptim' ) );
		}
		$context = isset( $_REQUEST['context'] ) ? $_REQUEST['context'] : '';
		switch ( $context ) {
			case MEGAOPTIM_TYPE_MEDIA_ATTACHMENT:
				$dir = megaoptim_get_ml_backup_dir();
				break;
			case MEGAOPTIM_TYPE_FILE_ATTACHMENT:
				$dir = megaoptim_get_files_backup_dir();
				break;
			default:
				$dir = null;
				break;
		}

		$dir = apply_filters( 'megaoptim_backup_dir', $dir, $context );

		if ( is_null( $dir ) ) {
			wp_send_json_error( 'Invalid context!' );
		} else {
			if ( file_exists( $dir ) && is_dir( $dir ) ) {
				$files = $dir . DIRECTORY_SEPARATOR . '*';
				array_map( 'unlink', glob( $files ) );
				wp_send_json_success( array( 'size' => megaoptim_get_dir_size( $dir ) ) );
			} else {
				wp_send_json_error( __( 'Directory does not exist!' ) );
			}
		}
		die;
	}


	public function ticker_upload() {
		if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], self::NONCE_DEFAULT ) ) {
			wp_send_json_error( __( 'Internal server error.', 'megaoptim' ) );
		}
		$attachments = isset( $_REQUEST['processing'] ) && ! empty( $_REQUEST['processing'] ) ? $_REQUEST['processing'] : array();
		$context     = isset( $_REQUEST['context'] ) ? $_REQUEST['context'] : '';
		$response    = array();
		if ( empty( $attachments ) ) {
			wp_send_json_error();
		} else {
			switch ( $context ) {
				case MEGAOPTIM_TYPE_MEDIA_ATTACHMENT:
					foreach ( $attachments as $attachment_id ) {
						try {
							$attachment                 = new MGO_MediaAttachment( $attachment_id );
							$response[ $attachment_id ] = array(
								'id'           => $attachment->get_id(),
								'is_locked'    => $attachment->is_locked(),
								'is_optimized' => $attachment->is_optimized(),
								'html'         => MGO_MediaLibrary::instance()->get_attachment_buttons( $attachment )
							);

						} catch ( MGO_Exception $e ) {
						}
					}
					break;
				default:
					$response = apply_filters( 'megaoptim_upload_ticker', $response, $context, $attachments );
			}

			$response = array_values( $response );

			if ( count( $response ) > 0 ) {
				wp_send_json_success( $response );
			} else {
				wp_send_json_error( $response );
			}
		}
	}

	public function get_profile() {
		if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], self::NONCE_DEFAULT ) ) {
			wp_send_json_error( __( 'Internal server error.', 'megaoptim' ) );
		}
		$profile = new MGO_Profile();
		$tokens  = $profile->get_tokens_count();
		wp_send_json_success( array( 'tokens' => $tokens ) );
	}

	public function optimize_single_attachment() {
		if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], self::NONCE_DEFAULT ) ) {
			wp_send_json_error( __( 'Internal server error.', 'megaoptim' ) );
		}
		$attachment_id            = isset( $_REQUEST['attachmentid'] ) ? $_REQUEST['attachmentid'] : '';
		$context                  = isset( $_REQUEST['context'] ) ? $_REQUEST['context'] : '';
		$possible_additional_data = array( 'compression' );
		$additional_params        = array();
		foreach ( $possible_additional_data as $key ) {
			if ( isset( $_REQUEST[ $key ] ) ) {
				$additional_params[ $key ] = $_REQUEST[ $key ];
			}
		}
		if ( ! empty( $attachment_id ) ) {
			switch ( $context ) {
				case MEGAOPTIM_TYPE_MEDIA_ATTACHMENT:
					megaoptim_async_optimize_attachment( $attachment_id, array(), $additional_params );
					break;
				default:
					do_action( 'megaoptim_optimize_single_attachment', $attachment_id, $context, $additional_params );
			}
		}
		wp_send_json_success();

	}

	public function restore_single_attachment() {
		if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], self::NONCE_DEFAULT ) ) {
			wp_send_json_error( __( 'Internal server error.', 'megaoptim' ) );
		}
		$attachment_id = isset( $_REQUEST['attachmentid'] ) ? $_REQUEST['attachmentid'] : '';
		$context       = isset( $_REQUEST['context'] ) ? $_REQUEST['context'] : '';
		$data          = false;
		if ( ! empty( $attachment_id ) ) {
			switch ( $context ) {
				case MEGAOPTIM_TYPE_MEDIA_ATTACHMENT:
					try {
						$attachment = new MGO_MediaAttachment( $attachment_id );
						$attachment->restore();
						$data = MGO_MediaLibrary::instance()->get_attachment_buttons( $attachment );
					} catch ( MGO_Exception $e ) {
					}
					break;
				default:
					$data = apply_filters( 'megaoptim_restore_single_attachment', $data, $attachment_id, $context );
			}
		}

		if ( false !== $data ) {
			wp_send_json_success( $data );
		} else {
			wp_send_json_error();
		}
	}
}

MGO_Ajax::instance();