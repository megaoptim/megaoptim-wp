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

namespace MegaOptim\Responses;

use MegaOptim\Http\HTTP;
use MegaOptim\Optimizer;
use MegaOptim\Tools\URL;

class Response implements HTTP {
	/**
	 * The plain response
	 * @var string
	 */
	private $raw = false;
	/**
	 * The response status
	 * @var string (ok|error)
	 */
	private $status = '';
	/**
	 * The result object
	 * @var array|Result[]
	 */
	private $result = array();

	/**
	 * Returns the user object
	 * @var null|Profile
	 */
	private $user = null;

	/**
	 * The error code
	 * @var int
	 */
	private $code = null;
	/**
	 * Array of errors, if any.
	 * @var array
	 */
	private $errors = array();

	/**
	 * Return the process uuid
	 * @var string
	 */
	private $process_id = null;

	/**
	 * Response constructor.
	 *
	 * @param $response
	 */
	public function __construct( $response ) {
		$this->raw = $response;
		$response  = @json_decode( $response );
		//$this->raw = $response;
		if ( isset( $response->status ) ) {
			$this->status = $response->status;
		}
		if ( isset( $response->result ) ) {
			if ( is_array( $response->result ) ) {
				$this->result = array();
				foreach ( $response->result as $result ) {
					array_push( $this->result, new Result( $result ) );
				}
			}
		}
		if ( isset( $response->code ) ) {
			$this->code = $response->code;
		}
		if ( isset( $response->errors ) ) {
			$this->errors = $response->errors;
		}
		if ( isset( $response->process_id ) ) {
			$this->process_id = $response->process_id;
		}

		if ( isset( $response->user ) ) {
			$this->user = new Profile();
			if ( isset( $response->user->tokens ) ) {
				$this->user->setTokens( $response->user->tokens );
			}
			if ( isset( $response->user->name ) ) {
				$this->user->setName( $response->user->name );
			}
			if ( isset( $response->user->email ) ) {
				$this->user->setEmail( $response->user->email );
			}
			$this->user->setCode( $response->code );
			$this->user->setStatus( $response->status );
		}


	}

	/**
	 * Used to set the request for this response
	 *
	 * @param $resource
	 */
	public function setLocalResources( $resource ) {
		if ( ! is_array( $resource ) ) {
			$resource = (array) $resource;
		}
		if ( count( $this->result ) !== count( $resource ) ) {
			return;
		}
		for ( $i = 0; $i < count( $resource ); $i ++ ) {
			if ( URL::validate( $resource[ $i ] ) ) {
				break;
			}
			if ( basename( $resource[ $i ] ) === $this->result[ $i ]->getFileName() ) {
				$this->result[ $i ]->setLocalPath( $resource[ $i ] );
			}
		}
	}

	/**
	 * Set process id.
	 *
	 * @param $process_id
	 */
	public function setProcessId( $process_id ) {
		$this->process_id = $process_id;
	}

	/**
	 * Returns the status
	 * @return string
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * Check if response is successful
	 * @return bool
	 */
	public function isSuccessful() {
		return isset( $this->status ) && $this->status === HTTP::STATUS_OK;
	}

	/**
	 * Is processing
	 * @return bool
	 */
	public function isProcessing() {
		return isset( $this->status ) && $this->status === HTTP::STATUS_PROCESSING;
	}

	/**
	 * Check if the response is error
	 * @return bool
	 */
	public function isError() {
		return isset( $this->status ) && $this->status === 'error';
	}

	/**
	 * Returns array of errors
	 * @return array
	 */
	public function getErrors() {
		return $this->errors;
	}

	/**
	 * Returns the error code;
	 * @return mixed
	 */
	public function getErrorCode() {
		return $this->code;
	}

	/**
	 * Returns the refreshed user info after the request.
	 * @return null|Profile
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * Return the result.
	 * @return Result[]
	 */
	public function getResult() {
		return $this->result;
	}

	/**
	 * Return the results array
	 * @return Result[]
	 */
	public function getOptimizedFiles() {
		return $this->result;
	}

	/**
	 * Return the raw response
	 * @return mixed
	 */
	public function getRawResponse() {
		return $this->raw;
	}

	/**
	 * Returns the process id
	 * @return string
	 */
	public function getProcessId() {
		return $this->process_id;
	}

	/**
	 * Returns the result by file name
	 * @param $file_name
	 *
	 * @return Result|mixed|null
	 */
	public function getResultByFileName($file_name) {
		foreach($this->result as $result) {
			if($result->getFileName() === $file_name) {
				return $result;
			}
		}
		return null;
	}
}
