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

class MGO_CloudFlare {

	private $email;
	private $api_key;
	private $zone_id;

	/**
	 * SOS_Cloudflare constructor.
	 */
	public function __construct() {
		$creds = self::get_credentials();
		if ( false !== $creds ) {
			$this->email   = $creds['email'];
			$this->api_key = $creds['api_key'];
			$this->zone_id = $creds['zone'];
		}
	}

	/**
	 * Check if the credentials are valid.
	 * @return bool
	 */
	public function valid_credentials() {
		return self::is_valid( array( $this->email, $this->api_key, $this->zone_id ) );
	}

	/**
	 * Purges files from the api cache.
	 *
	 * @param $files
	 *
	 * @return array|mixed|object
	 */
	public function purge_files( $files ) {
		return self::purge( $files, $this->email, $this->api_key, $this->zone_id );
	}

	/**
	 * Check if the credentials are valid?
	 *
	 * @param $arr
	 *
	 * @return bool
	 */
	public static function is_valid( $arr ) {
		foreach ( $arr as $value ) {
			if ( is_null( $value ) || empty( $value ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Clean up the domain name.
	 *
	 * @param $domain
	 *
	 * @return string
	 */
	public static function strip_scheme( $domain ) {
		return rtrim( str_replace( 'https://', '', str_replace( 'http://', '', $domain ) ), '/' );
	}

	/**
	 * Returns the CloudFlare credentials from the following sources
	 *  1. MegaOptim settings page
	 *  2. If 1. is not setup, then look for them from the LittleBizzy plugin
	 *  3. If 1.,2. is not setup then look for them from the CloudFlare plugin
	 */
	public static function get_credentials() {

		// Check for MegaOptim CF credentials first.
		$email   = MGO_Settings::instance()->get( MGO_Settings::CLOUDFLARE_EMAIL );
		$api_key = MGO_Settings::instance()->get( MGO_Settings::CLOUDFLARE_API_KEY );
		$zone    = MGO_Settings::instance()->get( MGO_Settings::CLOUDFLARE_ZONE );
		if ( self::is_valid( array( $email, $api_key, $zone ) ) ) {
			$zone = self::strip_scheme( $zone );
			if ( filter_var( "https://{$zone}", FILTER_VALIDATE_URL ) ) {
				$zone = self::get_zone_id( $zone, $email, $api_key );
			}

			return array(
				'email'   => $email,
				'api_key' => $api_key,
				'zone'    => $zone
			);
		}

		// No MegaOptim CloudFlare credentials? Check for LittleBizzy plugin maybe?
		if ( ! self::is_valid( array( $email, $api_key, $zone ) ) ) {
			if ( class_exists( '\LittleBizzy\CloudFlare\Core\Data' ) ) {
				$data    = \LittleBizzy\CloudFlare\Core\Data::instance();
				$api_key = defined( 'CLOUDFLARE_API_KEY' ) ? CLOUDFLARE_API_KEY : $data->key;
				$email   = defined( 'CLOUDFLARE_API_EMAIL' ) ? CLOUDFLARE_API_EMAIL : $data->email;
				$zone    = isset( $data->zone['id'] ) ? $data->zone['id'] : null;
				if ( self::is_valid( array( $api_key, $email, $zone ) ) ) {
					return array(
						'email'   => $email,
						'api_key' => $api_key,
						'zone'    => $zone
					);
				}
			}
		}

		// Still not CloudFlare credentials? Check for CloudFlare plugin maybe?
		if ( ! self::is_valid( array( $email, $api_key, $zone ) ) ) {
			if ( class_exists( '\CF\WordPress\WordPressClientAPI' ) ) {

				$email   = get_option( 'cloudflare_api_email' );
				$api_key = get_option( 'cloudflare_api_key' );
				$zone    = get_option( 'cloudflare_cached_domain_name' );
				if ( self::is_valid( array( $email, $api_key, $zone ) ) ) {
					$zone = self::get_zone_id( $zone, $email, $api_key );

					return array(
						'email'   => $email,
						'api_key' => $api_key,
						'zone'    => $zone
					);
				}
			}
		}

		// Still not valid?
		return false;
	}

	/**
	 * Returns zone Id by domain.
	 *
	 * @param $domain
	 * @param $email
	 * @param $api_key
	 *
	 * @return bool
	 */
	public static function get_zone_id( $domain, $email, $api_key ) {
		// TODO: Cache
		$endpoint = 'https://api.cloudflare.com/client/v4/zones?name=' . $domain;
		$response = self::get( $endpoint, self::get_headers( $email, $api_key ) );
		if ( false !== $response ) {
			if ( isset( $response->result[0]->id ) ) {
				return $response->result[0]->id;
			}
		}

		return false;
	}

	/**
	 * Purges file from the CloudFlare API.
	 *
	 * @param $files
	 * @param $email
	 * @param $api_key
	 * @param $zone
	 *
	 * @return array|mixed|object
	 */
	public static function purge( $files, $email, $api_key, $zone ) {
		$endpoint = "https://api.cloudflare.com/client/v4/zones/" . $zone . "/purge_cache";
		$files    = is_array( $files ) ? $files : array( $files );
		$params   = json_encode( array( 'files' => $files ) );
		$headers  = self::get_headers( $email, $api_key );

		$response = self::delete( $endpoint, $params, $headers );

		return isset( $response['success'] ) ? $response['success'] : false;
	}

	/**
	 * Returns the required api headers
	 *
	 * @param $email
	 * @param $api_key
	 *
	 * @return array
	 */
	public static function get_headers( $email, $api_key ) {
		return array(
			'X-Auth-Email' => $email,
			'X-Auth-Key'   => $api_key,
			'Content-Type' => 'application/json'
		);
	}

	/**
	 * Sends DELETE request to given url
	 *
	 * @param $endpoint
	 * @param $json_params
	 * @param $headers
	 *
	 * @return array|mixed|object
	 */
	public static function delete( $endpoint, $json_params, $headers ) {

		$headers_mod = array();
		foreach ( $headers as $key => $value ) {
			array_push( $headers_mod, "{$key}:{$value}" );
		}

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $endpoint );
		curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "DELETE" );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $json_params );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers_mod );
		curl_setopt( $ch, CURLOPT_USERAGENT, '"User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.87 Safari/537.36"' );

		$response = curl_exec( $ch );
		$result   = json_decode( $response, true );
		curl_close( $ch );

		return $result;
	}

	/**
	 * Sends GET request to given url
	 *
	 * @param $endpoint
	 * @param $headers
	 *
	 * @return array|mixed|object
	 */
	public static function get( $endpoint, $headers ) {
		$response = wp_remote_get( $endpoint, array( 'timeout' => 30, 'headers' => $headers ) );
		if ( is_wp_error( $response ) ) {
			return false;
		}

		return json_decode( $response['body'] );

	}
}