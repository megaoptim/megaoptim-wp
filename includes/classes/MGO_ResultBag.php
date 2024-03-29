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

class MGO_ResultBag {

	public $total_full_size = 0;
	public $total_thumbnails = 0;
	public $total_saved_bytes = 0;

	/**
	 * @var MGO_Attachment|MGO_FileAttachment|MGO_MediaAttachment|MGO_NGGAttachment|null
	 */
	private $attachment = null;

	/**
	 * Contains all the API response objects for the main image and the thumbnails.
	 * @var \MegaOptim\Client\Responses\Response[]
	 */
	private $responses = array();

	/**
	 * MGO_ResultBag constructor.
	 */
	public function __construct() {
	}

	/**
	 * Set the attachment
	 *
	 * @param MGO_Attachment|MGO_MediaAttachment|MGO_FileAttachment|MGO_NGGAttachment $attachment
	 */
	public function set_attachment( $attachment ) {
		$this->attachment = $attachment;
	}

	/**
	 * Add API Response object to the bag of results.
	 *
	 * @param $key
	 * @param \MegaOptim\Client\Responses\Response $response
	 */
	public function add( $key, $response ) {
		$this->responses[ $key ] = $response;
	}

	/**
	 * Removes response from the responses bag
	 *
	 * @param $key
	 */
	public function remove( $key ) {
		unset( $this->responses[ $key ] );
	}

	/**
	 * Returns the last response from the api. For example if multiple batches are sent to the api.
	 * @return \MegaOptim\Client\Responses\Response|mixed
	 */
	public function get_last_response() {
		return end( $this->responses );
	}

	/**
	 * Returns all the responses
	 * @return \MegaOptim\Client\Responses\Response[]
	 */
	public function get_responses() {
		return $this->responses;
	}

	/**
	 * Returns the attachment
	 * @return MGO_Attachment|MGO_FileAttachment|MGO_MediaAttachment|MGO_NGGAttachment|null
	 */
	public function get_attachment() {
		return $this->attachment;
	}

	/**
	 * Returns the totals
	 * @return array
	 */
	public function get_optimization_info() {
		return array(
			'total'            => $this->total_thumbnails + $this->total_full_size,
			'total_full_size'  => $this->total_full_size,
			'total_thumbnails' => $this->total_thumbnails,
			'saved_bytes'      => $this->total_saved_bytes,
			'saved_megabytes'  => megaoptim_convert_bytes_to_specified( $this->total_saved_bytes, 'MB', 3 )
		);
	}

	/**
	 * Is errorneous?
	 */
	public function is_erroneous() {
		$totalE = 0;
		$totalR = 0;
		foreach ( $this->responses as $response ) {
			if ( $response->isError() ) {
				$totalE ++;
			}
			$totalR ++;
		}
		return $totalR > 0 && $totalR === $totalE;
	}

	/**
	 * Throw the last error.
	 * @throws MGO_Exception
	 */
	public function throw_last_error() {
		$error = null;
		foreach ( $this->responses as $response ) {
			if ( method_exists( $response, 'isError' ) && $response->isError() ) {
				$error = $response;
			}
		}
		if ( ! empty( $error ) ) {
			$messages = $error->getErrors();
			if ( count( $messages ) > 0 ) {
				throw new MGO_API_Response_Exception( $messages[0], $error->getErrorCode() );
			}
		}
	}
}
