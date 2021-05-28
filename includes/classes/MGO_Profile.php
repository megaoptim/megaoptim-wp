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

class MGO_Profile {

	private $data = false;
	private $_api_key = null;
	private $_cache_key = null;
	private $_cache_time = MEGAOPTIM_TEN_MINUTES_IN_SECONDS; // 10 minutes

	public $fresh = false; // If true, the data is not loaded from cache.

	/**
	 * MGO_Profile constructor.
	 *
	 * @param null $api_key
	 *
	 * @param bool $forceReload
	 *
	 * @throws MGO_Exception
	 */
	public function __construct( $api_key = null, $forceReload = false ) {
		if ( is_null( $api_key ) ) {
			$this->_api_key = MGO_Settings::instance()->getApiKey();
		} else {
			$this->_api_key = $api_key;
		}
		$this->_cache_key = 'profile_' . $this->_api_key;
		if ( $this->__load( $forceReload ) ) {
			$this->fresh = true;
		}
	}


	/**
	 * @param bool $forceReload
	 *
	 * @return bool
	 * @throws MGO_Exception
	 */
	private function __load( $forceReload = false ) {
		$data = null;
		if ( $forceReload || ( false === ( $data = megaoptim_cache_get( $this->_cache_key ) ) ) ) {
			if ( ! is_null( $this->_api_key ) ) {
				$response = self::get_user_by_api_key( $this->_api_key );
				if ( false !== $response ) {
					if ( ! isset( $response['status'] ) || $response['status'] !== 'ok' ) {
						$this->data['valid'] = 0;
					} else {
						$this->data          = $response['result'];
						$this->data['valid'] = 1;
					}
					megaoptim_cache_set( $this->_cache_key, $this->data, MEGAOPTIM_ONE_MINUTE_IN_SECONDS );

					return true;
				}
			}
		} else {
			$this->data = $data;
		}

		return false;
	}

	/**
	 * Returns the user by api key.
	 *
	 * @param $api_key
	 *
	 * @return array|mixed|object
	 * @throws MGO_Exception
	 */
	public static function get_user_by_api_key( $api_key ) {
		$response = wp_remote_post( WP_MEGAOPTIM_API_PROFILE, array(
				'method'      => 'POST',
				'timeout'     => 10,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => array( strtolower( WP_MEGAOPTIM_API_HEADER_KEY ) => $api_key ),
				'sslverify'   => false,
			)
		);
		if ( is_wp_error( $response ) ) {
			throw new MGO_Exception( $response->get_error_message() );
		} else {
			return @json_decode( $response['body'], true );
		}
	}

	/**
	 * Register api url
	 *
	 * @param $data
	 *
	 * @return array|WP_Error
	 */
	public static function register( $data ) {
		$response = wp_remote_post( WP_MEGAOPTIM_REGISTER_API_URL, array(
				'method'      => 'POST',
				'timeout'     => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => true,
				'body'        => $data,
				'sslverify'   => false,
			)
		);

		return $response;
	}

	/**
	 * Returns the name
	 * @return mixed|string
	 */
	public function get_name() {
		return $this->get( 'name' );
	}

	/**
	 * Returns the email
	 * @return mixed|string
	 */
	public function get_email() {
		return $this->get( 'email' );
	}

	/**
	 * Returns the Tokens count left.
	 *
	 * @param $refresh - Refresh if the data is not fresh.
	 *
	 * @return mixed|string
	 */
	public function get_tokens_count( $refresh = true ) {
		if ( ! $this->fresh && $refresh ) {
			$this->refresh();
		}

		$tokens = $this->get( 'tokens' );
		if ( ! is_numeric( $tokens ) ) {
			return 0;
		}

		return $tokens;
	}

	/**
	 * Check if connection is successful ?
	 * @return bool|MGO_Profile
	 */
	public function is_connected() {

		if ( is_null( $this->_api_key ) ) {
			return false;
		}
		if ( ! isset( $this->data['valid'] ) || $this->data['valid'] !== 1 ) {
			return false;
		}

		return $this;
	}

	/**
	 * Is the api key valid?
	 * @return bool
	 */
	public function is_valid_apikey() {
		if ( ! isset( $this->data['valid'] ) ) {
			return false;
		}
		if ( $this->data['valid'] === 0 ) {
			return false;
		}

		return true;
	}

	/**
	 * Is the api key set in the admin interface?
	 * @return bool
	 */
	public function has_api_key() {
		return ! empty( $this->_api_key );
	}

	/**
	 * Generic function to get value from the values map
	 *
	 * @param $field
	 *
	 * @return mixed|string
	 */
	public function get( $field = null ) {
		if ( $field === null ) {
			return $this->data;
		}

		return isset( $this->data[ $field ] ) ? $this->data[ $field ] : '';
	}

	/**
	 * Force refresh of the data
	 */
	public function refresh() {
		try {
			$this->__load( true );
		} catch ( MGO_Exception $e ) {
		}
	}

	/**
	 * Flush caches
	 *
	 * @param null $new_api_key
	 **/
	public static function flushCaches( $new_api_key = null ) {
		try {
			new self( $new_api_key, true );
		} catch ( MGO_Exception $e ) {

		}
	}

	/**
	 * Update the current data with the new data
	 *
	 * @param StdClass $object
	 */
	public function update( $object ) {

		if ( ! is_array( $object ) ) {
			$data = @json_decode( json_encode( $object ), true );
		} else {
			$data = $object;
		}
		if ( isset( $data['name'], $data['email'], $data['tokens'] ) ) {
			$canUpdate = true;
			if ( ! $data['name'] || strlen( $data['name'] ) === '' ) {
				$canUpdate = false;
			}
			if ( ! $data['email'] || strlen( $data['email'] ) === '' ) {
				$canUpdate = false;
			}
			if ( ! $data['tokens'] || ! is_numeric( $data['tokens'] ) ) {
				$canUpdate = false;
			}
			if ( $canUpdate ) {
				set_transient( $this->_cache_key, serialize( $data ), $this->_cache_time );
				$this->data = $data;
			}
		}

	}

	/**
	 * @return bool|MGO_Profile
	 */
	public static function get_profile() {
		try {
			$profile = new self();
		} catch ( MGO_Exception $e ) {
			$profile = false;
		}

		return $profile;
	}

	/**
	 * Check if the WP instance is connected to the MegaOptim.com API
	 *
	 * @return bool|MGO_Profile
	 */
	public static function _is_connected() {
		try {
			$profile = new MGO_Profile();

			return $profile->is_connected();
		} catch ( MGO_Exception $e ) {
			return false;
		}
	}

}