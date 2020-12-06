<?Php
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

class MGO_FileLibrary extends MGO_Library {

	/**
	 * Is the site public?
	 * @var bool
	 */
	protected $is_public_environment;

	/**
	 * MGO_FileLibrary constructor.
	 */
	public function __construct() {
		parent::__construct();
		$this->is_public_environment = megaoptim_is_wp_accessible_from_public();
	}

	/**
	 * @param MGO_File $attachment
	 * @param array $params
	 *
	 * @return MGO_ResultBag
	 * @throws MGO_Attachment_Already_Optimized_Exception
	 * @throws MGO_Exception
	 */
	public function optimize( $attachment, $params = array() ) {

		if ( is_string( $attachment ) ) {
			if ( file_exists( $attachment ) ) {
				$attachment = new MGO_File( $this->get_image( $attachment ) );
			}
		}

		$result = new MGO_ResultBag();

		//Dont go further if not connected
		$profile = MGO_Profile::_is_connected();
		if ( ! $profile OR is_null( $this->optimizer ) ) {
			throw new MGO_Exception( 'Please make sure you have set up MegaOptim.com API key' );
		}
		//Check if attachment is optimized
		$attachment_object = new MGO_FileAttachment( $attachment->path );

		// Bail if optimized!
		if ( $attachment_object->is_processed() ) {
			throw new MGO_Attachment_Already_Optimized_Exception( 'The attachment is already fully optimized.' );
		}

		// Bail if no tokens left.
		$tokens = $profile->get_tokens_count();
		if ( $tokens != - 1 && $tokens <= 0 ) {
			throw new MGO_Exception( 'No tokens left. Please top up your account at https://megaoptim.com/dashboard in order to continue.' );
		}

		//Setup Request params
		$request_params = $this->build_request_params();
		if ( ! empty( $params ) ) {
			$request_params = array_merge( $request_params, $params );
		}

		/**
		 * Fired before the optimization of the attachment
		 *
		 * @param MGO_FileAttachment $attachment_object
		 * @param array $request_params
		 *
		 * @since 1.0
		 *
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
			// Grab the resource
			$resource = $this->get_attachment_path( $attachment );
			// Optimize the original
			$response = $this->optimizer->run( $resource, $request_params );
			$result->add( 'full', $response );
			if ( $response->isError() ) {
				megaoptim_log( $response->getErrors() );
			} else {
			    megaoptim_log($response);
				foreach ( $response->getOptimizedFiles() as $file ) {
					if ( $file->getSavedBytes() > 0 && $file->isSuccessfullyOptimized() ) {
						$file->saveAsFile( $attachment->path );
					}
					$result->total_full_size ++;
					$result->total_saved_bytes += $file->getSavedBytes();
                    $webp = $file->getWebP();
                    if ( ! is_null( $webp ) ) {
                        if ( $webp->getSavedBytes() > 0 ) {
                            $webp->saveAsFile( $attachment->path . '.webp' );
                        }
                    }
				}
				$attachment_object->set_data( $response, $request_params );
				$attachment_object->set( 'directory', $attachment->directory );
				$attachment_object->save();
				// No need to backup attachments that are already optimized!
				if ( $attachment_object->is_already_optimized() ) {
					$attachment_object->delete_backup();
				}
				/**
				 * Fired when attachment is successfully optimized.
				 * Tip: Use instanceof $attachment_object to check what kind of attachment was optimized.
				 * Attachemnt object get_id() returns md5 hash of the file path. The get_path() method returns the path.
				 *
				 * @param MGO_FileAttachment $attachment_object - The media attachment. Useful to check with instanceof.
				 * @param \MegaOptim\Responses\Response $response - The api request response
				 * @param array $request_params - The api request parameters
				 * @param string $size
				 *
				 * @since 1.0.0
				 *
				 */
				do_action( 'megaoptim_size_optimized', $attachment_object, $resource, $response, $request_params, $size = 'full' );
			}

			$result->set_attachment( $attachment_object );

			do_action( 'megaoptim_attachment_optimized', $attachment_object, $request_params, $result );

			return $result;
		} catch ( Exception $e ) {
			throw new MGO_Exception( $e->getMessage() . ' in ' . $e->getFile() );
		}
	}

	/**
	 * Starts async optimization task for $attachment
	 *
	 * @param int|string $attachment
	 * @param array $params
	 * @param string $type
	 *
	 * @return void
	 */
	public function optimize_async( $attachment, $params = array(), $type = 'any' ) {
		// TODO: Implement optimize_async() method.
	}


	/**
	 * Returns image path
	 *
	 * @param $path
	 *
	 * @return array
	 */
	public function get_image( $path ) {

		$file_id = md5( $path );
		$url     = get_site_url() . '/' . str_replace( megaoptim_get_wp_root_path() . '/', '', $path );

		return array(
			'ID'        => $file_id,
			'title'     => megaoptim_basename( $path ),
			'thumbnail' => $url,
			'directory' => dirname( $path ),
			'path'      => $path,
			'url'       => $url,
		);
	}

	/**
	 * @param $directory
	 *
	 * @return array
	 */
	public function get_images( $directory ) {
		$types     = array_keys( \MegaOptim\Tools\PATH::accepted_types() );
		$file_list = array();
		foreach ( $types as $ext ) {
			$found_files = glob( $directory . "*." . $ext );
			foreach ( $found_files as $file ) {
				array_push( $file_list, $this->get_image( $file ) );
			}
			array_merge( $file_list, $found_files );
		}

		return $file_list;
	}

	/**
	 * Returns all the images for specific directory
	 *
	 * @param $directory
	 *
	 * @return array
	 */
	public function get_all_images( $directory ) {
		return $this->get_images( $directory );
	}

	/**
	 * Returns array of the remaining images
	 *
	 * @param null $directory
	 *
	 * @return array|mixed
	 */
	public function get_remaining_images( $directory = null ) {

		if ( is_null( $directory ) ) {
			return array();
		}
		$images = $this->get_images( $directory );
		global $wpdb;
		foreach ( $images as $key => $image ) {
			$query = $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}megaoptim_opt SOPT WHERE SOPT.object_id=%s and SOPT.type=%s", $image['ID'], 'localfiles' );
			$r     = $wpdb->get_var( $query );
			if ( $r > 0 ) {
				unset( $images[ $key ] );
			}
		}

		return $images;
	}


	public function get_saved_bytes( $directory ) {
		global $wpdb;

		return $wpdb->get_var( $wpdb->prepare( "SELECT SUM(SOPT.saved_bytes) FROM {$wpdb->prefix}megaoptim_opt SOPT WHERE SOPT.directory=%s AND SOPT.saved_bytes > 0 and SOPT.type=%s", $directory, 'localfiles' ) );
	}

	/**
	 * Return stats about the library
	 *
	 * @param string $directory
	 * @param array $additional_data
	 *
	 * @return bool|mixed|MGO_Stats
	 */
	public function get_stats( $directory = '', $additional_data = array() ) {

		megaoptim_raise_memory_limit();

		if ( isset( $additional_data['recursive'] ) && $additional_data['recursive'] == 1 ) {
			$stats       = new MGO_Stats();
			$directories = array();
			$excludes    = megaoptim_get_excluded_custom_dir_paths();
			$files       = megaoptim_find_images( $directory, true,  $excludes);
			foreach ( $files as $file ) {
				array_push( $directories, dirname( $file ) . DIRECTORY_SEPARATOR );
			}
			$directories = array_unique( $directories );
			foreach ( $directories as $dir ) {
				$dir_stats = $this->get_dir_stats( $dir );
				$stats->add( $dir_stats );
			}
			$stats->setup();
		} else {
			$stats = $this->get_dir_stats( $directory );
		}

		return $stats;

	}

	/**
	 * Returns data about current dir
	 *
	 * @param $directory
	 *
	 * @return MGO_Stats
	 */
	public function get_dir_stats( $directory ) {
		$data                                    = new MGO_Stats();
		$all_images                              = $this->get_all_images( $directory );
		$remaining_images                        = $this->get_remaining_images( $directory );
		$total_saved_bytes                       = $this->get_saved_bytes( $directory );
		$total_remaining                         = count( $remaining_images );
		$data->empty_gallery                     = count( $remaining_images ) === 0;
		$data->total_images                      = count( $all_images );
		$data->total_optimized_mixed             = $data->total_images - $total_remaining;
		$data->total_fully_optimized_attachments = $data->total_images - $total_remaining;
		$data->total_thumbnails_optimized        = 0;
		$data->total_saved_bytes                 = is_null( $total_saved_bytes ) ? 0 : $total_saved_bytes;
		$data->total_remaining                   = $total_remaining;
		$data->set_remaining( $remaining_images );
		$data->setup();

		return $data;
	}

	/**
	 * Returns the attachment path
	 *
	 * @param MGO_File $attachment
	 */
	public function get_attachment_path( MGO_File $attachment ) {
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
		$r = MGO_Settings::instance()->get( MGO_Settings::BACKUP_FOLDER_FILES );

		return $r == 1;
	}
}