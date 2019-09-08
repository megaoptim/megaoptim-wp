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

namespace MegaOptim\Http;

abstract class BaseClient {

	/**
	 * The API url
	 */
	public static $api_url = 'https://api.megaoptim.com';

	/**
	 * The api version
	 */
	const API_VERSION = 'v1';

	/**
	 * The MegaOptim api endpoint.
	 */
	const AUTH_HEADER = 'X-API-KEY';

	/**
	 * Optimize
	 * @url https://api.megaoptim.com/v1/optimize
	 */
	const ENDPOINT_OPTIMIZE = '/optimize';

	/**
	 * Profile
	 * @url https://api.megaoptim.com/v1/users/info
	 */
	const ENDPOINT_PROFILE = '/users/info';

	/**
	 * Maximum timeout for a request in second
	 * @var int
	 */
	const TIMEOUT = 150;

	/**
	 * Additional user agent info
	 * @var string
	 */
	public static $user_agent = 'MegaOptim PHP Client';

	/**
	 * The MegaOptim api key that is used to authenticate the request
	 * @var string $api_key
	 */
	protected $api_key;


	/**
	 * Send HTTP POST request to the server.
	 * The method must throw exception in case of error.
	 *
	 * @param $url
	 * @param $data
	 * @param $files ( List of files the key:path, the key is the handle for the file. )
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	abstract public function post( $url, $data, $files = array() );


	/**
	 *  Send HTTP GET request to server
	 *    The method must throw exception in case of error.
	 *
	 * @param $url
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	abstract public function get( $url );


	/**
	 * Send HTTP POST request to the server.
	 * The method must throw exception in case of error.
	 *
	 * @param $url
	 * @param $data
	 * @param $files
	 * @param null $api_key
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public static function _post( $url, $data, $files = array(), $api_key = null ) {

	}


	/**
	 *  Send HTTP GET request to server
	 *    The method must throw exception in case of error.
	 *
	 * @param $url
	 * @param null $api_key
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public static function _get( $url, $api_key = null ) {

	}


	/**
	 * Used to download image from url. Returns the local path or false on failure.
	 *
	 * @param string $url
	 * @param string $save_filepath
	 *
	 * @return string
	 * @throws \Exception
	 */
	public static function download( $url, $save_filepath ) {
		$ch = curl_init( $url );
		curl_setopt( $ch, CURLOPT_HEADER, 0 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_BINARYTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array( "Accept" => "application/json" ) );
		curl_setopt( $ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1 );
		$response = curl_exec( $ch );
		if ( false === $response ) {
			$curl_error = curl_error( $ch );
			$curl_errno = curl_errno( $ch );
			curl_close( $ch );
			throw new \Exception( $curl_error, $curl_errno );
		} else {
			$fp = fopen( $save_filepath, 'w+' );
			fwrite( $fp, $response );
			fclose( $fp );
			curl_close( $ch );
		}

		return $save_filepath;
	}


	/**
	 * Convert $resource to CURLFile, also backwards compatible with
	 * version lower than 5.5
	 *
	 * @param $resource
	 *
	 * @return \CURLFile|string
	 */
	public static function to_curl_file( $resource ) {
		if ( ! class_exists( 'CURLFile' ) ) {
			return '@' . $resource;
		} else {
			return new \CURLFile( $resource );
		}
	}

	/**
	 * Returns the MegaOptim PHP Client user agent
	 * @return string
	 */
	public static function get_user_agent() {
		return self::$user_agent;
	}

	/**
	 * Set the user agent
	 *
	 * @param $user_agent
	 */
	public static function set_user_agent( $user_agent ) {
		static::$user_agent = $user_agent;
	}

	/**
	 * Returns full url based on the endpoint
	 *
	 * @param $endpoint
	 *
	 * @return string
	 */
	public static function get_endpoint( $endpoint ) {
		return self::$api_url . '/' . self::API_VERSION . $endpoint;
	}
}