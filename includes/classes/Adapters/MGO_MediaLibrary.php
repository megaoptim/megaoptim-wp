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

class MGO_MediaLibrary extends MGO_Library {

	/**
	 * If it is public environment?
	 * @var bool
	 */
	private $is_public_environment;

	/**
	 * The background process instance
	 * @var MGO_MediaLibrary_Process
	 */
	protected $background_process = null;

	/**
	 * MGO_MediaLibrary constructor.
	 */
	public function __construct() {
		parent::__construct();
		$this->is_public_environment = megaoptim_is_wp_accessible_from_public();
		add_action( 'plugins_loaded', array( $this, 'initialize' ) );
	}


	/**
	 * Initializes the background process queue for processing.
	 */
	public function initialize() {
		$this->background_process = new MGO_MediaLibrary_Process();
	}

	/**
	 * Optimizes specific attachment
	 *
	 * @param int|MGO_MediaAttachment $attachment
	 * @param array $params
	 *
	 * @return MGO_ResultBag
	 * @throws MGO_Attachment_Already_Optimized_Exception
	 * @throws MGO_Exception
	 */
	public function optimize( $attachment, $params = array() ) {

		//@set_time_limit(0);

		$result = new MGO_ResultBag();

		//Don't go further if not connected
		$profile = MGO_Profile::_is_connected();
		if ( ! $profile OR is_null( $this->optimizer ) ) {
			throw new MGO_Exception( 'Please make sure you have set up MegaOptim.com API key' );
		}

		//Check if Attachment is image
		$att_id = $attachment instanceof MGO_MediaAttachment ? $attachment->get_id() : $attachment;
		if ( ! wp_attachment_is_image( $att_id ) ) {
			throw new MGO_Exception( 'Attachment id is not valid image or pdf file' );
		}

		//Check the attachment object
		if ( $attachment instanceof MGO_MediaAttachment ) {
			$attachment_object = $attachment;
			$attachment        = $attachment_object->get_id();
		} else {
			$attachment_object = new MGO_MediaAttachment( $attachment );
		}

		// Prevent
		if ( $attachment_object->is_locked() ) {
			throw new MGO_Attachment_Locked_Exception( 'The attachment is currently being optimized. No need to re-run the optimization.' );
		}

		// Bail if optimized!
		if ( $attachment_object->is_optimized() ) {
			throw new MGO_Attachment_Already_Optimized_Exception( 'The attachment is already fully optimized.' );
		}

		// Bail if no tokens left.
		$tokens = $profile->get_tokens_count();
		if ( $tokens != -1 && $tokens <= 0 ) {
			throw new MGO_Exception( 'No tokens left. Please top up your account at https://megaoptim.com/dashboard in order to continue.' );
		}

		//Setup Request params
		$request_params = $this->filter_params( $this->build_request_params(), $attachment_object );
		if ( ! empty( $params ) ) {
			$request_params = array_merge( $request_params, $params );
		}

		/**
		 * Fired before the optimization of the attachment
		 *
		 * @param MGO_MediaAttachment $attachment_object
		 * @param array $request_params
		 *
		 * @since 1.0
		 *
		 */
		do_action( 'megaoptim_before_optimization', $attachment_object, $request_params );

		//Get the file names
		$original_resource = $this->get_attachment( $attachment, 'full', false );
		$original_path     = $this->get_attachment_path( $attachment, 'full', false );
		if ( ! file_exists( $original_path ) ) {
			throw new MGO_Exception( __( 'Original image version does not exist on the server.', 'megaoptim' ) );
		}

		//Create Backup If Enabled
		if ( $this->should_backup() ) {
			$backup_path = $attachment_object->backup();
			$attachment_object->set_backup_path( $backup_path );
		}

		// Optimize the original and the thumbnails
		try {
			$resources   = array();
			$attachments = array();

			// Collect the full size one
			if ( ! $attachment_object->is_size_optimized( 'full' ) ) {
				array_push( $resources, $original_resource );
				array_push( $attachments, array( 'size' => 'full', 'save_path' => $original_path ) );
			}

			// Collect the thumbnails
			$attachment_object->maybe_set_metadata();
			$remaining_thumbnails = $attachment_object->get_unoptimized_thumbnails();
			foreach ( $remaining_thumbnails['normal'] as $size ) {
				if ( $attachment_object->is_size_optimized( $size ) ) {
					continue;
				}
				$thumbnail_resource = $this->get_attachment( $attachment, $size, false );
				$thumbnail_path     = $this->get_attachment_path( $attachment, $size, false );
				array_push( $resources, $thumbnail_resource );
				array_push( $attachments, array( 'size' => $size, 'save_path' => $thumbnail_path ) );
			}

			$resource_chunks = array_chunk( $resources, 5 );

			for ( $i = 0; $i < count( $resource_chunks ); $i ++ ) {

				$resource_chunk = $resource_chunks[ $i ];

				if ( count( $resource_chunk ) > 0 ) {
					$response = $this->optimizer->run( $resource_chunk, $request_params );
					$result->add( 'chunk_' . ( $i + 1 ), $response );
					if ( $response->isError() ) {
						megaoptim_log( $response->getErrors() );
					} else {

						foreach($attachments as $att) {

							$file = $response->getResultByFileName(basename( $att['save_path'] ));

							if(!is_null($file)) {

								// Save data
								$data = megaoptim_generate_attachment_data($file, $response, $request_params);
								$attachment_object->set_attachment_data( $att['size'], $data );
								$attachment_object->save();

								// Save files
								$file->saveAsFile( $att['save_path'] );
								$webp = $file->getWebP();
								if(!is_null($webp)) {
									$webp->saveAsFile( $att['save_path'] . '.webp' );
								}

								// Set Stats
								if($att['size'] !== 'full') {
									$result->total_thumbnails = $result->total_thumbnails + 1;
								} else {
									$result->total_full_size = $result->total_full_size + 1;
								}
								$result->total_saved_bytes = $result->total_saved_bytes + $file->getSavedBytes();


								/**
								 * Fired when attachment thumbnail was successfully optimized and saved.
								 *
								 * @param MGO_MediaAttachment $attachment_object - The media attachment that was optimized
								 * @param string $path - The result of the optimization for this attachment
								 * @param array $request_params - The api parameters
								 * @param string $size - The thumbnail version
								 *
								 * @since 1.0.0
								 */
								do_action( 'megaoptim_attachment_optimized', $attachment_object, $att['save_path'], $response, $request_params, $att['size']);
								//break; // breaks the inner loop.
							}
						}
					}
				}
			}

			do_action( 'megaoptim_before_finish', $attachment_object, $request_params, $result );
			$attachment_object->unlock();
			$attachment_object->save();
			$attachment_object->refresh();
			$result->set_attachment( $attachment_object );

			return $result;
		} catch ( Exception $e ) {
			$attachment_object->unlock();
			echo $e->getMessage();
			throw new MGO_Exception( $e->getMessage() . ' in ' . $e->getFile() );
		}
	}

	/**
	 * Starts async optimization task for $attachment
	 *
	 * @param int|string $attachment
	 * @param array $params
	 *
	 * @return void
	 * @throws MGO_Attachment_Already_Optimized_Exception
	 * @throws MGO_Attachment_Locked_Exception
	 * @throws MGO_Exception
	 */
	public function optimize_async( $attachment, $params = array() ) {

		if ( is_null( $this->background_process ) ) {
			_doing_it_wrong( __METHOD__, 'Called too early. Please make sure WordPress is loaded and then call this method.', WP_MEGAOPTIM_VER );
			return;
		}


		//Don't go further if not connected
		$profile = MGO_Profile::_is_connected();
		if ( ! $profile OR is_null( $this->optimizer ) ) {
			throw new MGO_Exception( 'Please make sure you have set up MegaOptim.com API key' );
		}

		//Check if Attachment is image
		$att_id = $attachment instanceof MGO_MediaAttachment ? $attachment->get_id() : $attachment;
		if ( ! wp_attachment_is_image( $att_id ) ) {
			throw new MGO_Exception( 'Attachment id is not valid image or pdf file' );
		}

		//Check the attachment object
		if ( $attachment instanceof MGO_MediaAttachment ) {
			$attachment_object = $attachment;
			$attachment        = $attachment_object->get_id();
		} else {
			$attachment_object = new MGO_MediaAttachment( $attachment );
		}

		// Prevent
		if ( $attachment_object->is_locked() ) {
			throw new MGO_Attachment_Locked_Exception( 'The attachment is currently being optimized. No need to re-run the optimization.' );
		}

		// Bail if optimized!
		if ( $attachment_object->is_optimized() ) {
			throw new MGO_Attachment_Already_Optimized_Exception( 'The attachment is already fully optimized.' );
		}

		// Bail if no tokens left.
		$tokens = $profile->get_tokens_count();
		if ( $tokens != - 1 && $tokens <= 0 ) {
			throw new MGO_Exception( 'No tokens left. Please top up your account at https://megaoptim.com/dashboard in order to continue.' );
		}

		//Setup Request params
		$request_params = $this->filter_params( $this->build_request_params(), $attachment_object );
		if ( ! empty( $params ) ) {
			$request_params = array_merge( $request_params, $params );
		}

		/**
		 * Fired before the optimization of the attachment
		 *
		 * @param MGO_MediaAttachment $attachment_object
		 * @param array $request_params
		 *
		 * @since 1.0
		 *
		 */
		do_action( 'megaoptim_before_optimization', $attachment_object, $request_params );

		//Get the file names
		$original_path     = $this->get_attachment_path( $attachment, 'full', false );
		if ( ! file_exists( $original_path ) ) {
			throw new MGO_Exception( __( 'Original image version does not exist on the server.', 'megaoptim' ) );
		}

		//Create Backup If Enabled
		if ( $this->should_backup() ) {
			$backup_path = $attachment_object->backup();
			$attachment_object->set_backup_path( $backup_path );
		}

		$sizes = array();
		// Collect the full size one
		if ( ! $attachment_object->is_size_optimized( 'full' ) ) {
			array_push( $sizes, 'full' );

		}
		// Collect the thumbnails
		$unoptimized = $attachment_object->get_unoptimized_thumbnails();
		foreach ( $unoptimized['normal'] as $size ) {
			array_push( $sizes, $size );
		}

		$sizes = apply_filters( 'megaoptim_attachment_optimization_sizes', $sizes, $attachment_object );

		// Create item objects
		$items = array();
		foreach ( $sizes as $size ) {
			$item = array(
				'attachment_id'         => $attachment_object->get_id(),
				'attachment_size'       => $size,
				'attachment_resource'   => $this->get_attachment( $attachment_object->get_id(), 'full', false ),
				'attachment_local_path' => $this->get_attachment_path( $attachment_object->get_id(), 'full', false ),
				'params'                => $request_params,
			);
			array_push($items, $item);
		}

		// Chunk and Dispatch
		if(count($sizes)) {
			$chunks = array_chunk( $items, 5 );
			foreach ( $chunks as $chunk ) {
				$this->background_process->push_to_queue( $chunk );
			}
			$this->background_process->save()->dispatch();
		}
	}

	/**
	 * Returns all the available media attachments.
	 * @return array|null|object
	 */
	public function get_images() {
		global $wpdb;
		//$query  = $wpdb->prepare( "SELECT P.ID, P.post_title FROM $wpdb->posts P WHERE P.post_type='attachment' AND P.post_mime_type LIKE %s", "%image%" );

		$query  = "SELECT P.ID, P.post_title, PM1.meta_value as metadata, PM2.meta_value as megaoptim FROM {$wpdb->posts} P INNER JOIN {$wpdb->postmeta} PM1 ON PM1.post_id=P.ID AND PM1.meta_key='_wp_attachment_metadata' LEFT JOIN {$wpdb->postmeta} PM2 ON PM2.post_id=P.ID AND PM2.meta_key='_megaoptim_data' WHERE P.post_type='attachment' AND P.post_mime_type IN ('image/jpeg', 'image/png', 'image/gif') ORDER BY P.post_date DESC";
		$result = $wpdb->get_results( $query, ARRAY_A );

		return $result;
	}

	/**
	 * Returns stats of optimization
	 *
	 * @param bool $include_remaining
	 *
	 * @return mixed|MGO_Stats
	 */
	public function get_stats( $include_remaining = false ) {
		@set_time_limit(0);
		$images_total         = 0;
		$optimized_total      = 0;
		$optimized_thumbnails = 0;
		$optimized_fully      = 0;
		$saved_bytes          = 0;
		$remaining_list       = array();
		$remaining_total      = 0;
		$images               = $this->get_images();
		$empty_gallery        = true;
		if ( ! empty( $images ) && count( $images ) > 0 ) {
			$empty_gallery = false;
			foreach ( $images as $image ) {
				try {
					// Create attachments with one query! Not one database hit per query
					$attachment = MGO_MediaAttachment::create( $image['ID'], $image['metadata'], $image['megaoptim'] );
					//OLD WAY $attachment = new MGO_MediaAttachment( $image['ID'] );

					if ( $attachment->get_optimized_status() == 1 ) {
						$optimized_total ++;
					}
					$optimized_thumbnails_arr = $attachment->get_optimized_thumbnails();

					$images_total ++;
					$optimized_thumbnails_count = count( $optimized_thumbnails_arr['normal'] ) + count( $optimized_thumbnails_arr['retina'] );
					$optimized_total            += $optimized_thumbnails_count;
					$optimized_thumbnails       += $optimized_thumbnails_count;
					$images_total               += $optimized_thumbnails_count;
					$saved_bytes                += $attachment->get_total_saved_bytes( false, true );
					if ( $attachment->is_optimized() ) {
						$optimized_fully ++;
					} else {
						//$original_opt = false;
						if ( $attachment->get_optimized_status() <= 0 ) {
							//$original_opt = true;
							$remaining_total ++;
						}
						$remaining_thumbnails       = $attachment->get_unoptimized_thumbnails();
						$remaining_thumbnails_count = count( $remaining_thumbnails['normal'] ) + count( $remaining_thumbnails['retina'] );
						$remaining_total            += $remaining_thumbnails_count;
						$images_total               += $remaining_thumbnails_count;
						if ( $include_remaining ) {
							array_push( $remaining_list, array(
								'ID'                    => $attachment->get_id(),
								'title'                 => $image['post_title'],
								'thumbnail'             => $attachment->get_thumbnail_url(),
								'is_original_optimized' => $attachment->get_optimized_status(),
								'remaining_thumbnails'  => array_values( $remaining_thumbnails ),
							) );
						}

					}
				} catch ( MGO_Exception $e ) {
					continue;
				}
			}
		}

		$data                                    = new MGO_Stats();
		$data->empty_gallery                     = $empty_gallery;
		$data->total_images                      = $images_total;
		$data->total_optimized_mixed             = $optimized_total;
		$data->total_fully_optimized_attachments = $optimized_fully;
		$data->total_thumbnails_optimized        = $optimized_thumbnails;
		$data->total_saved_bytes                 = $saved_bytes;
		$data->total_remaining                   = $remaining_total;

		if ( $include_remaining ) {
			$data->set_remaining( $remaining_list );
		}
		$data->setup();

		return apply_filters( 'megaoptim_ml_stats', $data, $this );
	}

	/**
	 * Returns the attachment PATH
	 *
	 * @param $attachment_id
	 * @param $wp_image_size
	 * @param bool $retina
	 *
	 * @return bool|false|string
	 */
	public function get_attachment_path( $attachment_id, $wp_image_size, $retina = false ) {
		$path_with_size           = '';
		$original_attachment_path = get_attached_file( $attachment_id );
		if ( $wp_image_size !== 'full' ) {
			$meta_data = get_post_meta( $attachment_id, '_wp_attachment_metadata', true );
			$directory = pathinfo( $original_attachment_path, PATHINFO_DIRNAME );
			if ( ! empty( $meta_data ) ) {
				$path_with_size = $directory;
				$path_with_size .= DIRECTORY_SEPARATOR;
				if ( isset( $meta_data['sizes'][ $wp_image_size ]['file'] ) ) {
					$filename = $meta_data['sizes'][ $wp_image_size ]['file'];
					if ( $retina ) {
						$size_ext  = pathinfo( $filename, PATHINFO_EXTENSION );
						$size_name = pathinfo( $filename, PATHINFO_FILENAME );
						$filename  = $size_name . '@2x.' . $size_ext;
					} else {
						$filename = $meta_data['sizes'][ $wp_image_size ]['file'];
					}
					$path_with_size .= $filename;
				} else {
					return false;
				}
				if ( ! file_exists( $path_with_size ) ) {
					return false;
				}
			}
		} else {
			if ( $retina ) {
				$path_with_size = pathinfo( $original_attachment_path, PATHINFO_DIRNAME );
				$path_with_size .= DIRECTORY_SEPARATOR;
				$path_with_size .= pathinfo( $original_attachment_path, PATHINFO_FILENAME );
				$path_with_size = $path_with_size . '@2x.' . pathinfo( $original_attachment_path, PATHINFO_EXTENSION );
				if ( ! file_exists( $path_with_size ) ) {
					return false;
				}
			} else {
				$path_with_size = $original_attachment_path;
			}
		}

		return wp_normalize_path( $path_with_size );
	}

	/**
	 * Returns the attachment url
	 *
	 * @param $attachment_id
	 * @param $wp_image_size
	 * @param $retina
	 *
	 * @return bool
	 */
	public function get_attachment_url( $attachment_id, $wp_image_size, $retina = false ) {

		$url       = wp_get_attachment_image_src( $attachment_id, $wp_image_size );
		$meta_data = get_post_meta( $attachment_id, '_wp_attachment_metadata', true );
		if ( $wp_image_size != 'full' ) {
			if ( ! empty( $meta_data ) ) {
				if ( isset( $meta_data['sizes'][ $wp_image_size ] ) ) {
					$size = $meta_data['sizes'][ $wp_image_size ];
					if ( $size['file'] !== megaoptim_basename( $url[0] ) ) {
						return false;
					}
				} else {
					return false;
				}
			} else {
				return false;
			}
		}
		if ( $retina ) {
			$path = $this->get_attachment_path( $attachment_id, $wp_image_size, $retina );
			if ( false === $path ) {
				return false;
			} else {
				$size_ext  = pathinfo( $url[0], PATHINFO_EXTENSION );
				$size_name = pathinfo( $url[0], PATHINFO_FILENAME );
				$dir       = megaoptim_strip_filename( $url[0] );

				return $dir . '/' . $size_name . '@2x.' . $size_ext;
			}
		}

		return $url[0];
	}

	/**
	 * Returns attachment path or url depending if the plugin runs on localhost or not.
	 *  - If the plugin runs on local host the function returns PATH so the php client will upload the file
	 *  - If the plugin doesn't runs on local host the function returns URL so the php client will post the url and the server will download it.
	 *
	 * @param int $attachment_id
	 * @param string $wp_image_size
	 * @param bool $retina
	 *
	 * @return string|false
	 */
	public function get_attachment( $attachment_id, $wp_image_size, $retina = false ) {
		if ( $this->is_public_environment ) {
			$file = $this->get_attachment_url( $attachment_id, $wp_image_size, $retina );
		} else {
			$file = $this->get_attachment_path( $attachment_id, $wp_image_size, $retina );
		}

		return $file;
	}

	/**
	 * Get size information for all currently-registered image sizes.
	 *
	 * @global $_wp_additional_image_sizes
	 * @uses   get_intermediate_image_sizes()
	 * @return array $sizes Data for all currently-registered image sizes.
	 */
	public static function get_image_sizes() {
		global $_wp_additional_image_sizes;

		$sizes = array();

		foreach ( get_intermediate_image_sizes() as $_size ) {
			if ( in_array( $_size, array( 'thumbnail', 'medium', 'medium_large', 'large' ) ) ) {
				$sizes[ $_size ]['width']  = get_option( "{$_size}_size_w" );
				$sizes[ $_size ]['height'] = get_option( "{$_size}_size_h" );
				$sizes[ $_size ]['crop']   = (bool) get_option( "{$_size}_crop" );
			} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
				$sizes[ $_size ] = array(
					'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
					'height' => $_wp_additional_image_sizes[ $_size ]['height'],
					'crop'   => $_wp_additional_image_sizes[ $_size ]['crop'],
				);
			}
		}

		return $sizes;
	}

	/**
	 * Get size information for a specific image size.
	 *
	 * @uses   get_image_sizes()
	 *
	 * @param  string $size The image size for which to retrieve data.
	 *
	 * @return bool|array $size Size data about an image size or false if the size doesn't exist.
	 */
	public static function get_image_size( $size ) {
		$sizes = static::get_image_sizes();

		if ( isset( $sizes[ $size ] ) ) {
			return $sizes[ $size ];
		}

		return false;
	}

	/**
	 * Get the width of a specific image size.
	 *
	 * @uses   get_image_size()
	 *
	 * @param  string $size The image size for which to retrieve data.
	 *
	 * @return bool|string $size Width of an image size or false if the size doesn't exist.
	 */
	public static function get_image_width( $size ) {
		if ( ! $size = static::get_image_size( $size ) ) {
			return false;
		}

		if ( isset( $size['width'] ) ) {
			return $size['width'];
		}

		return false;
	}

	/**
	 * Get the height of a specific image size.
	 *
	 * @uses   get_image_size()
	 *
	 * @param  string $size The image size for which to retrieve data.
	 *
	 * @return bool|string $size Height of an image size or false if the size doesn't exist.
	 */
	public static function get_image_height( $size ) {
		if ( ! $size = static::get_image_size( $size ) ) {
			return false;
		}

		if ( isset( $size['height'] ) ) {
			return $size['height'];
		}

		return false;
	}

	/**
	 * Returns array of the remaining images
	 *
	 * @return mixed
	 */
	public function get_remaining_images() {
		// TODO: Implement get_remaining_images() method.
	}


	/**
	 * Should this library backup?
	 * @return bool
	 */
	public function should_backup() {
		$r = MGO_Settings::instance()->get( MGO_Settings::BACKUP_MEDIA_LIBRARY_ATTACHMENTS );

		return $r == 1;
	}

	/**
	 * Filter the api parameters based on the attachment.
	 *
	 * @param $params
	 * @param MGO_MediaAttachment $attachment
	 *
	 * @return array
	 */
	private function filter_params( $params, $attachment ) {

		$largest_thumbnail_dimensions = $attachment->get_largest_thumbnail_dimensions();

		if ( isset( $params['max_width'] ) && $params['max_width'] > 0 ) {
			if ( $params['max_width'] < $largest_thumbnail_dimensions['width'] ) {
				unset( $params['max_width'] );
			}
		}
		if ( isset( $params['max_height'] ) && $params['max_height'] > 0 ) {
			if ( $params['max_height'] < $largest_thumbnail_dimensions['height'] ) {
				unset( $params['max_height'] );
			}
		}

		return $params;
	}

	/**
	 * The attachment buttons?
	 *
	 * @param MGO_NextGenAttachment|MGO_MediaAttachment|MGO_LocalFileAttachment $attachment
	 *
	 * @return string
	 */
	public function get_attachment_buttons($attachment) {
		return megaoptim_get_view( 'misc/buttons-ml', array( 'data' => $attachment ) );
	}
}