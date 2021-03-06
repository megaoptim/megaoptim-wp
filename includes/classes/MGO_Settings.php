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

class MGO_Settings extends MGO_BaseObject {

	const OPTIONS_KEY = 'megaoptim_settings_data';
	const API_KEY = 'megaoptimpt_api_key';

	const IMAGE_SIZES = 'megaoptimpt_image_sizes';
	const RETINA_IMAGE_SIZES = 'megaoptimpt_retina_image_sizes';

	const BACKUP_MEDIA_LIBRARY_ATTACHMENTS = 'megaoptimpt_backup_medialibrary';
	const BACKUP_NEXTGEN_ATTACHMENTS = 'megaoptimpt_backup_nextgen';
	const BACKUP_FOLDER_FILES = 'megaoptimpt_backup_files';

	const AUTO_OPTIMIZE = 'megaoptimpt_auto_optimize';
	const RESIZE_LARGE_IMAGES = 'megaoptimpt_resize_large_images';

	const MAX_WIDTH = 'megaoptimpt_max_width';
	const MAX_HEIGHT = 'megaoptimpt_max_height';
	const COMPRESSION = 'megaoptimpt_compression';
	const PRESERVE_EXIF = 'megaoptimpt_keep_exif';
	const HTTP_USER = 'megaoptimpt_http_user';
	const HTTP_PASS = 'megaoptimpt_http_pass';
	const CMYKTORGB = 'megaoptimpt_cmyktorgb';

	const CLOUDFLARE_EMAIL = 'megaoptimpt_cloudflare_email';
	const CLOUDFLARE_API_KEY = 'megaoptimpt_cloudflare_api_key';
	const CLOUDFLARE_ZONE = 'megaoptimpt_cloudflare_zone';

	const WEBP_CREATE_IMAGES = 'webp_create';
	const WEBP_DELIVERY_METHOD = 'webp_delivery_method'; // Possible values: picture, rewrite, none
	const WEBP_TARGET_TO_REPLACE = 'webp_target_to_replace'; // default (the_content, the_excerpt, post_thumbnail), global (using output buffer)
	const WEBP_PICTUREFILL = 'webp_picturefill';

	private $settings = array();

	/**
	 * MGO_Settings constructor.
	 */
	public function __construct() {
		// Set defaults if they aren't set.
		if ( ! self::was_installed_previously() ) {
			$defaults = self::defaults();
			$this->update( $defaults );
		}
	}

	/**
	 * Returns the MegaOptim WordPress settings
	 *
	 * @param $key
	 *
	 * @param null $default
	 *
	 * @return array|string
	 */

	public function get( $key = null, $default = null ) {

		// Attempt to clean up unregistered settings from the sizes array
		if ( in_array( $key, array( self::IMAGE_SIZES, self::RETINA_IMAGE_SIZES ) ) ) {
			$this->remove_unregistered_sizes();
		}

		$this->settings = $this->get_settings();
		if ( false === $this->settings ) {
			return false;
		}
		if ( ! is_null( $key ) ) {
			return isset( $this->settings[ $key ] ) ? $this->settings[ $key ] : $default;
		} else {
			return $this->settings;
		}
	}

	/**
	 * Returns megaoptim settings
	 * @return array|bool
	 */
	public function get_settings() {
		$settings = get_option( self::OPTIONS_KEY );

		return $settings;
	}


	/**
	 * Returns the api key
	 * @return string|bool
	 */

	public function getApiKey() {
		if ( empty( $this->settings ) ) {
			$this->get();
		}
		if ( ! isset( $this->settings[ self::API_KEY ] )
		     || empty( $this->settings[ self::API_KEY ] )
		     || strlen( $this->settings[ self::API_KEY ] ) < 32
		) {
			return false;
		} else {
			return $this->settings[ self::API_KEY ];
		}
	}

	/**
	 * Set API Key
	 *
	 * @param $key
	 *
	 * @return bool
	 */
	public static function setApiKey( $key ) {
		if ( empty( $key ) ) {
			return false;
		}
		$options = get_option( self::OPTIONS_KEY );
		if ( empty( $options ) ) {
			$options = array();
		}
		$options[ self::API_KEY ] = $key;

		return update_option( self::OPTIONS_KEY, $options );
	}

	/**
	 * Is auto optimize enabled?
	 * @return bool
	 */
	public function isAutoOptimizeEnabled() {
		$auto_optimize = $this->get( self::AUTO_OPTIMIZE );

		return $auto_optimize == 1;
	}

	/**
	 * Updates data in the database
	 *
	 * @param $data
	 *
	 * @return bool
	 */
	public function update( $data ) {
		$_settings = $this->get();
		foreach ( $data as $key => $value ) {
			if ( is_string( $value ) ) {
				$_settings[ $key ] = sanitize_text_field( $value );
			} else {
				$_settings[ $key ] = $value;
			}
		}

		return update_option( self::OPTIONS_KEY, $_settings );
	}

	/**
	 * Returns array of keys
	 * @return array
	 */
	public static function get_db_keys() {
		return array(
			self::API_KEY,
			self::RESIZE_LARGE_IMAGES,
			self::IMAGE_SIZES,
			self::RETINA_IMAGE_SIZES,
			self::BACKUP_MEDIA_LIBRARY_ATTACHMENTS,
			self::BACKUP_NEXTGEN_ATTACHMENTS,
			self::BACKUP_FOLDER_FILES,
			self::AUTO_OPTIMIZE,
			self::MAX_HEIGHT,
			self::MAX_WIDTH,
			self::PRESERVE_EXIF,
			self::COMPRESSION,
			self::HTTP_PASS,
			self::HTTP_USER,
			self::CMYKTORGB,
			self::CLOUDFLARE_EMAIL,
			self::CLOUDFLARE_API_KEY,
			self::CLOUDFLARE_ZONE,
			self::WEBP_CREATE_IMAGES,
			self::WEBP_DELIVERY_METHOD,
			self::WEBP_TARGET_TO_REPLACE,
			self::WEBP_PICTUREFILL
		);
	}

	/**
	 * Default values
	 */
	public static function defaults() {

		$sizes = MGO_MediaLibrary::get_image_sizes();
		if ( ! empty( $sizes ) ) {
			$sizes = array_keys( $sizes );
		}

		return array(
			self::RESIZE_LARGE_IMAGES              => 0,
			self::BACKUP_MEDIA_LIBRARY_ATTACHMENTS => 1,
			self::BACKUP_NEXTGEN_ATTACHMENTS       => 1,
			self::BACKUP_FOLDER_FILES              => 1,
			self::AUTO_OPTIMIZE                    => 1,
			self::MAX_HEIGHT                       => 0,
			self::MAX_WIDTH                        => 0,
			self::PRESERVE_EXIF                    => 0,
			self::COMPRESSION                      => 'intelligent',
			self::HTTP_PASS                        => '',
			self::HTTP_USER                        => '',
			self::CMYKTORGB                        => 1,
			self::CLOUDFLARE_API_KEY               => '',
			self::CLOUDFLARE_EMAIL                 => '',
			self::CLOUDFLARE_ZONE                  => '',
			self::IMAGE_SIZES                      => $sizes,
			self::RETINA_IMAGE_SIZES               => $sizes,
			self::WEBP_CREATE_IMAGES               => 0,
			self::WEBP_DELIVERY_METHOD             => 'picture',
			self::WEBP_TARGET_TO_REPLACE           => 'default', // default or global
			self::WEBP_PICTUREFILL                 => 0,
		);
	}

	/**
	 * Check if the plugin was installed previouysly
	 * @return bool
	 */
	public static function was_installed_previously() {
		$options = get_option( self::OPTIONS_KEY );
		if ( $options ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Removes the sizes that are still saved in the sizes array but were unregistered.
	 */
	public function remove_unregistered_sizes() {
		$settings      = $this->get_settings();
		$sizes_normal  = isset( $settings[ self::IMAGE_SIZES ] ) ? $settings[ self::IMAGE_SIZES ] : array();
		$sizes_retina  = isset( $settings[ self::RETINA_IMAGE_SIZES ] ) ? $settings[ self::RETINA_IMAGE_SIZES ] : array();
		$sizes_current = MGO_MediaLibrary::get_image_sizes();
		$is_updated    = false;
		foreach ( $sizes_normal as $key => $size ) {
			// Is it missing in the current sizes?
			if ( ! isset( $sizes_current[ $size ] ) ) {
				// If so, Unset the size that is no longer registered.
				unset( $sizes_normal[ $key ] );
				// If so, Unset the retina size that is no longer registered.
				// Note: Retina sizes are stored the same without @2x appended.
				if ( isset( $sizes_retina[ $key ] ) ) {
					unset( $sizes_retina[ $key ] );
				}
				$is_updated = true;
			}
		}
		if ( $is_updated ) {
			$this->update( array(
				self::IMAGE_SIZES        => $sizes_normal,
				self::RETINA_IMAGE_SIZES => $sizes_retina,
			) );
		}
	}
}