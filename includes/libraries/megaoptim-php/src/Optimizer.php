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

namespace MegaOptim;

use MegaOptim\Http\Client;
use MegaOptim\Responses\Response;
use MegaOptim\Services\OptimizerService;
use MegaOptim\Tools\FileSystem;
use MegaOptim\Tools\PATH;
use MegaOptim\Tools\URL;

class Optimizer {

	const COMPRESSION_ULTRA = 'ultra';
	const COMPRESSION_INTELLIGENT = 'intelligent';
	const COMPRESSION_LOSSLESS = 'lossless';

	const RESOURCE_FILE = 'file';
	const RESOURCE_URL = 'url';
	const RESOURCE_URLS = 'urls';
	const RESOURCE_FILES = 'files';
	const MAX_ALLOWED_RESOURCES = 5;

	const VERSION = '1.0.5';
	/**
	 * @var string
	 */
	public $separator;
	/**
	 * The MegaOptim Api service
	 * @var OptimizerService
	 */
	private $service;
	/**
	 * The default arguments for calling the api.
	 * @var array $defaults
	 */
	private $defaults;

	/**
	 * Set the current resource
	 * @var null
	 */
	private $current_resource = null;

	/**
	 * Optimizer constructor.
	 *
	 * @param $api_key
	 * @param string $http_client_class
	 */
	public function __construct( $api_key, $http_client_class = '' ) {

		if ( class_exists( $http_client_class ) ) {
			$client = new $http_client_class( $api_key );
		} else {
			$client = new Client( $api_key );
		}

		$this->service   = new OptimizerService( $client );
		$this->defaults  = array(
			'max_width'   => 0,
			'max_height'  => 0,
			'keep_exif'   => 0,
			'cmyktorgb'   => 1,
			'compression' => Optimizer::COMPRESSION_INTELLIGENT,
			'webp'        => 0,
		);
		$this->separator = DIRECTORY_SEPARATOR;
	}

	/**
	 * Optimizes specific file or url
	 *
	 * @param string|array $resource (url, local path, array of urls, array of local paths)
	 * @param bool $local_wait Not reliable as waiting on the server but better if you want to offload the CPU for some time while this is processing.
	 * @param array $args
	 *
	 * @return Response
	 * @throws \Exception
	 */
	public function run( $resource, $args = array(), $local_wait = false ) {

		// Prepare initial args
		$args = array_merge( $this->defaults, $args );

		// If resource is directory, collect all the images from it.
		if ( ! is_array( $resource ) && is_dir( $resource ) ) {
			$resource = FileSystem::scan_dir( $resource );
		}
		// Validate the data, throws exception
		self::validate( $resource, $args );

		if ( true === $local_wait ) {
			$args['wait'] = false;
		}

		// Prepare the data for the api if the provided data is valid
		$data = self::prepare_data( $resource, $args );

		// Get max wait seconds from input.
		$max_wait_seconds = $data['wait'];

		// Call the api to schedule optimization
		$response = $this->service->send( $data['args'], $data['files'] );

		// Store the current resource
		$this->current_resource = $resource;

		// Wait for the optimization
		// If wait parameter is set as 0 or false
		if ( false !== $max_wait_seconds ) {
			if ( $response->isProcessing() ) {
				$processID = $response->getProcessId();
				if ( ! empty( $processID ) ) {
					$final_response = $this->service->get_result( $processID, $max_wait_seconds );
					$final_response->setLocalResources( $resource );
					$final_response->setProcessId( $processID );

					return $final_response;
				}
			}
		} else {
			$pid = $response->getProcessId();
			if ( true === $local_wait && ! empty( $pid ) ) {
				$wait_time  = 3; // TODO: Try to determine this automatically.
				$total_time = 0;
				while ( true ) {
					if ( $total_time > 120 ) {
						// Set timeout to 2 minutes, never enter in infinity loop.
						break;
					}
					sleep( $wait_time );
					$total_time   += $wait_time;
					$tmp_response = $this->service->get_result( $response->getProcessId(), 1 );
					if ( $tmp_response->isSuccessful() && ! $tmp_response->isProcessing() ) {
						$tmp_response->setLocalResources( $resource );
						$tmp_response->setProcessId( $pid );

						return $tmp_response;
					}
				}
			}
		}

		return $response;
	}

	/**
	 * Returns the results of the process
	 *
	 * @param $process_id
	 * @param int $max_wait_seconds
	 *
	 * @return Response
	 * @throws \Exception
	 */
	public function get_result( $process_id, $max_wait_seconds = 5 ) {
		$result = $this->service->get_result( $process_id, $max_wait_seconds );
		$result->setLocalResources( $this->current_resource );
		$result->setProcessId( $process_id );

		return $result;
	}

	/**
	 * Validates the specific file. If directory is is provided it skips the validation because
	 * with directory provided we will always have the correct types based on the implementation in
	 * scan_dir() function.
	 *
	 * @param $resource
	 * @param $args
	 *
	 * @throws \Exception
	 */
	public static function validate( $resource, $args ) {
		if ( isset( $args['callback_url'] ) && URL::validate( $resource ) ) {
			throw new \Exception( 'Invalid callback url' );
		}
	}

	/**
	 * Prepare the data for the api server
	 *
	 * @param $resource
	 * @param $args
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public static function prepare_data( $resource, $args ) {

		if ( is_array( $resource ) ) {
			$is_url  = 0;
			$is_file = 0;
			foreach ( $resource as $file ) {

				if ( file_exists( $file ) && is_file( $file ) ) {
					$is_file = 1;
					$is_url  = 0;
				} else if ( file_exists( $file ) && is_dir( $file ) ) {
					$is_file = 0;
					$is_url  = 0;
				} else if ( URL::validate( $file ) ) {
					$is_file = 0;
					$is_url  = 1;
				} else {
					$is_file = 0;
					$is_url  = 0;
				}
			}
			if ( $is_url ) {
				$resource_type = Optimizer::RESOURCE_URLS;
			} else if ( $is_file ) {
				$resource_type = Optimizer::RESOURCE_FILES;
			} else {
				throw new \Exception( 'Unknown resource type' );
			}
		} else if ( file_exists( $resource ) ) {
			$resource_type = Optimizer::RESOURCE_FILE;
		} else if ( URL::validate( $resource ) ) {
			$resource_type = Optimizer::RESOURCE_URL;
		} else {
			throw new \Exception( 'Unknown resource type' );
		}

		$args['type'] = $resource_type;
		$files        = array();

		switch ( $resource_type ) {
			case Optimizer::RESOURCE_URL:
				$args['url'] = $resource;
				break;
			case Optimizer::RESOURCE_FILE:
				$files['file'] = $resource;
				break;
			case Optimizer::RESOURCE_URLS:
				$index = 0;
				foreach ( $resource as $url ) {
					if ( $index === self::MAX_ALLOWED_RESOURCES ) {
						break;
					}
					$args[ 'url' . ( $index + 1 ) ] = $url;
					$index ++;
				}
				break;
			case Optimizer::RESOURCE_FILES:
				$index = 0;
				foreach ( $resource as $path ) {
					if ( $index === self::MAX_ALLOWED_RESOURCES ) {
						break;
					}
					$files[ 'file' . ( $index + 1 ) ] = $path;
					$index ++;
				}
				break;
			default:
				throw new \Exception( 'Invalid resource type' );
		}

		$data = array(
			'args'  => $args,
			'files' => $files
		);

		// Wait
		if ( isset( $args['wait'] ) && ! empty( $args['wait'] ) ) {
			if ( $args['wait'] === false || $args['wait'] === 0 || $args['wait'] === '0' || $args['wait'] < 0 ) {
				$data['wait'] = false;
			} else {
				if ( $args['wait'] < 30 || $args['wait'] > 120 ) {
					$data['wait'] = 75;
				} else {
					$data['wait'] = $args['wait'];
				}
			}
		} else {
			$data['wait'] = 75;
		}

		return $data;
	}

	/**
	 * Returns the user profile info
	 * @return Responses\Profile
	 * @throws \Exception
	 */
	public function get_user_info() {
		return $this->service->get_user_info();
	}


	/**
	 * Is valid compression level?
	 *
	 * @param $level
	 *
	 * @return bool
	 */
	public static function valid_compression_level( $level ) {
		return in_array( $level, array(
			self::COMPRESSION_LOSSLESS,
			self::COMPRESSION_INTELLIGENT,
			self::COMPRESSION_ULTRA,
		) );
	}
}
