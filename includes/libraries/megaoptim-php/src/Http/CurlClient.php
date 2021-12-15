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

namespace MegaOptim\Client\Http;

class CurlClient extends BaseClient {

	/**
	 * Client constructor.
	 *
	 * @param string $api_key
	 */
	public function __construct( $api_key ) {
		$this->api_key = $api_key;
	}

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
	public function post( $url, $data, $files = array() ) {
		return self::_post( $url, $data, $files, $this->api_key );
	}

	/**
	 *  Send HTTP GET request to server
	 *    The method must throw exception in case of error.
	 *
	 * @param $url
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function get( $url ) {
		return self::_get( $url, $this->api_key );
	}

	/**
	 * Downloads a file.
	 * @param  string  $url
	 * @param  string  $save_filepath
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
	 *  Send HTTP GET request to server
	 *
	 * @param $url
	 * @param null $api_key
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public static function _get( $url, $api_key = null ) {
		@set_time_limit( 450 );
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_USERAGENT, self::get_user_agent() );
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_FAILONERROR, 0 );
		curl_setopt( $ch, CURLOPT_TIMEOUT, self::TIMEOUT );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1 );

		// Setup headers
		$headers = array( 'Accept: application/json' );
		if ( ! is_null( $api_key ) ) {
			array_push( $headers, self::AUTH_HEADER . ': ' . $api_key );
		}
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );

		$response = curl_exec( $ch );
		curl_close( $ch );
		if ( false === $response ) {
			throw new \Exception( curl_error( $ch ), curl_errno( $ch ) );
		}

		return $response;
	}


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
		@set_time_limit( 450 );
		$ch = curl_init();
		if ( ! empty( $files ) ) {
			foreach ( $files as $key => $file ) {
				if ( file_exists( $file ) ) {
					$data[ $key ] = self::to_curl_file( $file );
				}
			}
		}
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_USERAGENT, self::get_user_agent() );
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
		curl_setopt( $ch, CURLOPT_FAILONERROR, 0 );
		curl_setopt( $ch, CURLOPT_TIMEOUT, self::TIMEOUT );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1 );

		// Setup headers
		$headers = array( 'Accept: application/json' );
		if ( ! is_null( $api_key ) ) {
			array_push( $headers, self::AUTH_HEADER . ': ' . $api_key );
		}
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );

		$response = curl_exec( $ch );
		if ( false === $response ) {
			$curl_error = curl_error( $ch );
			$curl_errno = curl_errno( $ch );
			curl_close( $ch );
			throw new \Exception( $curl_error, $curl_errno );
		}
		curl_close( $ch );

		return $response;
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
	 * Append Curl identifier to the user agent.
	 * @return string
	 */
	public static function get_user_agent() {
		return parent::get_user_agent() . ' / cURL';
	}

}
