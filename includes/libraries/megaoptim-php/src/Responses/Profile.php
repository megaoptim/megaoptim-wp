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

namespace MegaOptim\Client\Responses;

/**
 * Class Profile
 * @package MegaOptim\Client\Responses
 */
class Profile {

	/**
	 * The plain response
	 * @var string
	 */
	private $raw = false;

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
	 * The response status
	 * @var string (ok|error)
	 */
	private $status = '';

	/**
	 * Tokens left
	 * @var int
	 */
	private $tokens = null;

	/**
	 * The profile name
	 * @var string
	 */
	private $name = null;

	/**
	 * The profile email
	 * @var null
	 */
	private $email = null;

	/**
	 * Profile constructor.
	 *
	 * @param $response
	 */
	public function __construct( $response = null ) {

		if ( ! is_null( $response ) ) {
			$this->raw = $response;
			$response  = json_decode( $response );
			if ( isset( $response->status ) ) {
				$this->status = $response->status;
			}
			if ( isset( $response->code ) ) {
				$this->code = $response->code;
			}
			if ( isset( $response->errors ) ) {
				$this->errors = $response->errors;
			}
			if ( isset( $response->result->name ) ) {
				$this->name = $response->result->name;
			}
			if ( isset( $response->result->email ) ) {
				$this->email = $response->result->email;
			}
			if ( isset( $response->result->tokens ) ) {
				$this->tokens = $response->result->tokens;
			}
		}
	}

	/**
	 * Returns the response code
	 * @return int
	 */
	public function getCode() {
		return $this->code;
	}

	/**
	 * Returns array of errors
	 * @return array
	 */
	public function getErrors() {
		return $this->errors;
	}

	/**
	 * Raw response
	 * @return string
	 */
	public function getRawResponse() {
		return $this->raw;
	}

	/**
	 * Returns the status
	 * @return string
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * Returns the tokens left
	 * @return int
	 */
	public function getTokens() {
		return $this->tokens;
	}

	/**
	 * Returns the profile name
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Returns email
	 * @return null
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * Set the tokens left
	 *
	 * @param $tokens
	 */
	public function setTokens( $tokens ) {
		$this->tokens = $tokens;
	}

	/**
	 * Set the profile name
	 *
	 * @param $name
	 */
	public function setName( $name ) {
		$this->name = $name;
	}

	/**
	 * Set email
	 *
	 * @param $email
	 */
	public function setEmail( $email ) {
		$this->email = $email;
	}

	/**
	 * Set the code
	 *
	 * @param $code
	 */
	public function setCode( $code ) {
		$this->code = $code;
	}

	/**
	 * Set the status
	 *
	 * @param $status
	 */
	public function setStatus( $status ) {
		$this->status = $status;
	}
}
