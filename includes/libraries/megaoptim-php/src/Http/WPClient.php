<?php
/********************************************************************
 * Copyright (C) 2021 MegaOptim (https://megaoptim.com)
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

use MegaOptim\Client\Http\Multipart\MultipartFormData;
use MegaOptim\Client\Tools\PATH;

/**
 * Class WPClient
 * @package MegaOptim\Client\Http
 */
class WPClient extends BaseClient {

	/**
	 * Client constructor.
	 *
	 * @param  string  $api_key
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
	 * @param $files  ( List of files the key:path, the key is the handle for the file. )
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
	 *
	 * @param  string  $url
	 * @param  string  $save_filepath
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function download( $url, $save_filepath ) {

		$response = self::_get( $url );

		$fp = fopen( $save_filepath, 'w+' );
		fwrite( $fp, $response );
		fclose( $fp );

		return $save_filepath;
	}


	/**
	 *  Send HTTP GET request to server
	 *
	 * @param $url
	 * @param  null  $api_key
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public static function _get( $url, $api_key = null ) {

		@set_time_limit( 240 );

		$args = array(
			'headers'     => array(
				'accept' => 'application/json',
			),
			'timeout'     => self::default_request_timeout(),
			'httpversion' => '1.1',
			'sslverify'   => false,
			'user-agent'  => self::get_user_agent(),
		);

		if ( ! is_null( $api_key ) ) {
			$args['headers'][ self::AUTH_HEADER ] = $api_key;
		}

		/**
		 * Some plugins will hook into this filter and modify it.
		 * To avoid complications we will force the value only for this request.
		 */
		add_filter( 'http_request_timeout', array( WPClient::class, 'default_request_timeout' ), PHP_INT_MAX - 10 );
		$response = wp_remote_get( $url, $args );
		remove_filter( 'http_request_timeout', array( WPClient::class, 'default_request_timeout' ), PHP_INT_MAX - 10 );

		if ( is_wp_error( $response ) ) {
			throw new \Exception( $response->get_error_message() ? $response->get_error_message() : 'Unknown error happened.' );
		}

		return isset( $response['body'] ) ? $response['body'] : null;

	}


	/**
	 * Send HTTP POST request to the server.
	 * The method must throw exception in case of error.
	 *
	 * @param $url
	 * @param $data
	 * @param $files
	 * @param  null  $api_key
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public static function _post( $url, $data, $files = array(), $api_key = null ) {

		@set_time_limit( 450 );

		$multipart = new MultipartFormData();

		if ( ! empty( $data ) ) {
			foreach ( $data as $key => $value ) {
				$val = (string) $value;
				$multipart->addValue( $key, $val );
			}
		}
		if ( ! empty( $files ) ) {
			foreach ( $files as $key => $path ) {
				if ( PATH::is_supported( $path ) && file_exists( $path ) ) {
					$content_type = PATH::get_content_type( $path );
					$multipart->addFile( $key, basename( $path ), file_get_contents( $path ), $content_type );
				}
			}
		}
		$multipart->finish();

		$params = array(
			'body'        => (string) $multipart,
			'headers'     => array(
				'accept'       => 'application/json',
				'content-type' => 'multipart/form-data; boundary=' . $multipart->getBoundary(),
			),
			'timeout'     => self::default_request_timeout(),
			'httpversion' => '1.1',
			'sslverify'   => false,
			'user-agent'  => self::get_user_agent(),
		);

		if ( ! is_null( $api_key ) ) {
			$params['headers'][ self::AUTH_HEADER ] = $api_key;
		}

		/**
		 * Some plugins will hook into this filter and modify it.
		 * To avoid complications we will force the value only for this request.
		 */
		add_filter( 'http_request_timeout', array( WPClient::class, 'default_request_timeout' ), PHP_INT_MAX - 10 );
		$response = wp_remote_post( $url, $params );
		remove_filter( 'http_request_timeout', array( WPClient::class, 'default_request_timeout' ), PHP_INT_MAX - 10 );

		if ( is_wp_error( $response ) ) {
			throw new \Exception( $response->get_error_message() ? $response->get_error_message() : 'Unknown error happened.' );
		}

		return isset( $response['body'] ) ? $response['body'] : null;

	}

	/**
	 * Append WP identifier to the user agent.
	 * @return string
	 */
	public static function get_user_agent() {
		return parent::get_user_agent() . ' / WP';
	}

	/**
	 * The default HTTP call timeout
	 * @return int
	 */
	public static function default_request_timeout() {
		return 120;
	}

}
