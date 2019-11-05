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
 * Date: 26.8.2018
 * Time: 14:28
 */
class MGO_NGGLibrary extends MGO_Library {

	/**
	 * Is the site public?
	 * @var bool
	 */
	protected $is_public_environment;

	/**
	 * The background process instance
	 * @var MGO_NGGProcess
	 */
	private $background_process;

	/**
	 * MGO_NGGLibrary constructor.
	 */
	public function __construct() {
		parent::__construct();
		$this->is_public_environment = megaoptim_is_wp_accessible_from_public();
		add_action('plugins_loaded', array($this, 'initialize'));
	}

	/**
	 * Initialize
	 */
	public function initialize() {
		$this->background_process = new MGO_NGGProcess();
	}

	/**
	 * Optimizes specific attachment
	 *
	 * @param $attachment
	 * @param array $params
	 *
	 * @return MGO_ResultBag|mixed
	 * @throws MGO_Attachment_Already_Optimized_Exception
	 * @throws MGO_Attachment_Locked_Exception
	 * @throws MGO_Exception
	 */
	public function optimize( $attachment, $params = array() ) {

		$result = new MGO_ResultBag();

		if ( is_numeric( $attachment ) ) {
			$attachment = megaoptim_get_ngg_attachment( $attachment );
		}

		//Dont go further if not connected
		$profile = MGO_Profile::_is_connected();
		if ( ! $profile OR is_null( $this->optimizer ) ) {
			throw new MGO_Exception( 'Please make sure you have set up MegaOptim.com API key' );
		}
		//Check if attachment is optimized
		$attachment_object = new MGO_NGGAttachment( $attachment->ID );

		// Prevent
		if ( $attachment_object->is_locked() ) {
			throw new MGO_Attachment_Locked_Exception( 'The attachment is currently being optimized. No need to re-run the optimization.' );
		}

		// Bail if optimized!
		if ( $attachment_object->is_processed() ) {
			throw new MGO_Attachment_Already_Optimized_Exception( 'The attachment is already fully optimized.' );
		}

		// Bail if no tokens left.
		$tokens = $profile->get_tokens_count();
		if ( $tokens != -1 && $tokens <= 0 ) {
			throw new MGO_Exception( 'No tokens left. Please top up your account at https://megaoptim.com/dashboard in order to continue.' );
		}

		//Setup Request params
		$request_params = $this->build_request_params();
		if ( ! empty( $params ) ) {
			$request_params = array_merge( $request_params, $params );
		}

		/**
		 * Fired before the optimization of the attachment
		 * @since 1.0
		 *
		 * @param MGO_NGGAttachment $attachment_object
		 * @param array $request_params
		 */
		do_action( 'megaoptim_before_optimization', $attachment_object, $request_params );

		//Create Backup If needed
		if ( $this->should_backup() ) {
			$backup_path = $attachment_object->backup();
			$attachment_object->set_backup_path( $backup_path );
		}

		// Check if image exist
		if ( ! file_exists( $attachment->path ) ) {
			throw new MGO_Exception( __( 'Original image version does not exist on the server.', 'megaoptim-image-optimizer' ) );
		}

		try {
			megaoptim_log( 'Optimizing NGG attachment with id ' . $attachment->ID );
			// Optimize the original
			$attachment_object->lock();
			// Grab the resource
			$resource = $this->get_attachment_path( $attachment );
			// Optimize the original
			$response = $this->optimizer->run( $resource, $request_params );
			$result->add( 'full', $response );
			if ( $response->isError() ) {
				megaoptim_log('--- API Errors: ' . $response->getErrors() );
			} else {
				megaoptim_log( '--- Response: ' . $response->getRawResponse() );
				foreach ( $response->getOptimizedFiles() as $file ) {
					if($file->getSavedBytes() > 0 && $file->isSuccessfullyOptimized()) {
						$file->saveAsFile( $attachment->path );
					}
					$result->total_full_size++;
					$result->total_saved_bytes+=$file->getSavedBytes();
				}
				$attachment_object->set_data( $response, $request_params );
				$attachment_object->update_ngg_meta();
				$attachment_object->save();
				// No need to backup attachments that are already optimized!
				if ( $attachment_object->is_already_optimized() ) {
					$attachment_object->delete_backup();
				}
				/**
				 * Fired when attachment is successfully optimized.
				 * Tip: Use instanceof $attachment_object to check what kind of attachment was optimized.
				 * Attachemnt object get_id() method returns  the ID of the nextgen picture that was optimized.
				 * @since 1.0.0
				 *
				 * @param MGO_FileAttachment $attachment_object - The media attachment. Useful to check with instanceof.
				 * @param \MegaOptim\Responses\Response $response - The api request response
				 * @param array $request_params - The api request parameters
				 * @param string $size
				 */
				do_action( 'megaoptim_attachment_optimized', $attachment_object, $resource, $response, $request_params, $size = 'full' );
			}

			$attachment_object->unlock();

			$result->set_attachment( $attachment_object );

			return $result;
		} catch ( Exception $e ) {
			$attachment_object->unlock();
			megaoptim_log( '--- Optimizer Exception: ' . $e->getMessage() );
			throw new MGO_Exception( $e->getMessage() . ' in ' . $e->getFile() );
		}
	}

	/**
	 * Starts async optimization task for $attachment
	 *
	 * @param MGO_File|int $attachment
	 * @param array $params
	 * @param string $type
	 *
	 * @return void
	 * @throws MGO_Attachment_Already_Optimized_Exception
	 * @throws MGO_Attachment_Locked_Exception
	 * @throws MGO_Exception
	 */
	public function optimize_async( $attachment, $params = array(), $type = 'any' ) {

		if ( is_null( $this->background_process ) ) {
			_doing_it_wrong( __METHOD__, 'Called too early. Please make sure WordPress is loaded and then call this method.', WP_MEGAOPTIM_VER );
			return;
		}

		if ( is_numeric( $attachment ) ) {
			$attachment = megaoptim_get_ngg_attachment( $attachment );
		}

		//Dont go further if not connected
		$profile = MGO_Profile::_is_connected();
		if ( ! $profile OR is_null( $this->optimizer ) ) {
			throw new MGO_Exception( 'Please make sure you have set up MegaOptim.com API key' );
		}
		//Check if attachment is optimized
		$attachment_object = new MGO_NGGAttachment( $attachment->ID );

		// Prevent
		if ( $attachment_object->is_locked() ) {
			throw new MGO_Attachment_Locked_Exception( 'The attachment is currently being optimized. No need to re-run the optimization.' );
		}

		// Bail if optimized!
		if ( $attachment_object->is_processed() ) {
			throw new MGO_Attachment_Already_Optimized_Exception( 'The attachment is already fully optimized.' );
		}

		// Bail if no tokens left.
		$tokens = $profile->get_tokens_count();
		if ( $tokens != -1 && $tokens <= 0 ) {
			throw new MGO_Exception( 'No tokens left. Please top up your account at https://megaoptim.com/dashboard in order to continue.' );
		}

		//Setup Request params
		$request_params = $this->build_request_params();
		if ( ! empty( $params ) ) {
			$request_params = array_merge( $request_params, $params );
		}

		/**
		 * Fired before the optimization of the attachment
		 * @since 1.0
		 *
		 * @param MGO_NGGAttachment $attachment_object
		 * @param array $request_params
		 */
		do_action( 'megaoptim_before_optimization', $attachment_object, $request_params );

		//Create Backup If needed
		if ( $this->should_backup() ) {
			$backup_path = $attachment_object->backup();
			$attachment_object->set_backup_path( $backup_path );
		}

		// Check if image exist
		if ( ! file_exists( $attachment->path ) ) {
			throw new MGO_Exception( __( 'Original image version does not exist on the server.', 'megaoptim-image-optimizer' ) );
		}

		// Run optimization
		$resource = $this->get_attachment_path( $attachment );
		$this->background_process->push_to_queue(array(
			'resource' => $resource,
			'attachment_id' => $attachment->ID,
			'local_path' => $attachment->path,
			'params' => $request_params,
		));
		$this->background_process->save()->dispatch();
	}

	/**
	 * Returns array of the remaining images
	 *
	 * @return mixed
	 */
	public function get_remaining_images() {
		global $wpdb;
		$url     = get_site_url() . "/";
		$path    = megaoptim_get_wp_root_path() . DIRECTORY_SEPARATOR;
		$query   = $wpdb->prepare( "SELECT P.pid as ID, P.filename as title, CONCAT('%s',G.path,P.filename) as thumbnail,  CONCAT('%s',G.path,P.filename) as url, CONCAT('%s',G.path,P.filename) as path FROM {$wpdb->prefix}ngg_pictures P INNER JOIN {$wpdb->prefix}ngg_gallery G ON P.galleryid=G.gid LEFT JOIN {$wpdb->prefix}megaoptim_opt SOPT ON SOPT.object_id=P.pid AND SOPT.type='%s' WHERE SOPT.id IS NULL", $url, $url, $path, MEGAOPTIM_TYPE_NEXTGEN_ATTACHMENT );
		$results = $wpdb->get_results( $query, ARRAY_A );

		return $results;
	}

	/**
	 * Return stats about the library
	 *
	 * @param bool $include_remaining
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function get_stats( $include_remaining = false, $args = array() ) {

		// TODO: Implement get_stats() method.
		global $wpdb;
		$total_images                            = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}ngg_pictures WHERE 1" );
		$total_remaining                         = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}ngg_pictures P LEFT JOIN {$wpdb->prefix}megaoptim_opt SOPT ON SOPT.object_id=P.pid AND SOPT.type=%s WHERE SOPT.id IS NULL", MEGAOPTIM_TYPE_NEXTGEN_ATTACHMENT ) );
		$total_saved_bytes                       = $wpdb->get_var( $wpdb->prepare( "SELECT SUM(SOPT.saved_bytes) FROM {$wpdb->prefix}ngg_pictures P LEFT JOIN {$wpdb->prefix}megaoptim_opt SOPT ON SOPT.object_id=P.pid AND SOPT.type=%s WHERE SOPT.id IS NOT NULL AND SOPT.saved_bytes > 0", MEGAOPTIM_TYPE_NEXTGEN_ATTACHMENT ) );
		$total_optimized                         = $total_images - $total_remaining;
		$data                                    = new MGO_Stats();
		$data->empty_gallery                     = ( $total_images <= 0 ) ? true : false;
		$data->total_images                      = $total_images;
		$data->total_remaining                   = $total_remaining;
		$data->total_optimized_mixed             = $total_optimized;
		$data->total_fully_optimized_attachments = $total_optimized;
		$data->total_thumbnails_optimized        = 0; // No thumbnails in this gallery
		$data->total_saved_bytes                 = $total_saved_bytes;

		if ( $include_remaining ) {
			$data->set_remaining( $this->get_remaining_images() );
		}

		$data->setup();

		return $data;
	}

	/**
	 * Returns the attachment path.
	 *
	 * @param MGO_File $attachment
	 *
	 * @return bool|string
	 */
	public function get_attachment_path( $attachment ) {
		if ( $this->is_public_environment ) {
			return $attachment->url;
		} else {
			return $attachment->path;
		}
	}

	/**
	 * Should this library backup?
	 * @return bool
	 */
	public function should_backup() {
		$r = MGO_Settings::instance()->get( MGO_Settings::BACKUP_NEXTGEN_ATTACHMENTS );

		return $r == 1;
	}
}