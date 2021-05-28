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
 * Class MGO_Exception
 */
class MGO_Exception extends Exception {

	/**
	 * The errors
	 * @var array|string[]
	 */
	private $errors;

	/**
	 * MGO_Exception constructor.
	 *
	 * @param array $message
	 * @param int $code
	 * @param null $previous
	 */
	public function __construct( $message = "", $code = 0, $previous = null ) {

		$this->errors = array();
		if ( is_array( $message ) ) {
			$primary_error_message = $message[0];
			$this->errors          = $message;
		} else {
			$primary_error_message = $message;
			$this->errors          = array( $message );
		}
		parent::__construct( $primary_error_message, $code, $previous );
	}

	/**
	 * Return the errors
	 * @return array|string[]
	 */
	public function get_errors() {
		return $this->errors;
	}
}