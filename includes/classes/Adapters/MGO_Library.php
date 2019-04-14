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

/**
 * Created by PhpStorm.
 * User: darko
 * Date: 3/24/2017
 * Time: 11:36 PM
 */
abstract class MGO_Library extends MGO_BaseObject {

	/**
	 * The PHP MegaOptim optimizer instance
	 *
	 * @var \MegaOptim\Optimizer
	 */
	protected $optimizer;

	/**
	 * @var MGO_Settings
	 */
	protected $settings;

	/**
	 * MGO_Library constructor.
	 */
	public function __construct() {
		$this->optimizer = self::get_optimizer();
		$this->settings   = MGO_Settings::instance();
	}

	/**
	 * Returns the megaoptim optimizer
	 * @return null|\MegaOptim\Optimizer
	 */
	public static function get_optimizer() {
		$api_key = MGO_Settings::instance()->getApiKey();
		if ( false !== $api_key ) {
			return ( new \MegaOptim\Optimizer( $api_key ) );
		}

		return null;
	}

	/**
	 * Optimizes specific attachment
	 *
	 * @param $attachment
	 * @param array $params
	 *
	 * @return mixed
	 */
	abstract public function optimize( $attachment, $params = array() );

	/**
	 * Starts async optimization task for $attachment
	 *
	 * @param int|string $attachment
	 *
	 * @return void
	 */
	abstract public function optimize_async( $attachment );

	/**
	 * Returns array of the remaining images
	 *
	 * @return mixed
	 */
	abstract public function get_remaining_images();

	/**
	 * Should this library backup?
	 * @return bool
	 */
	abstract public function should_backup();

	/**
	 * Build the parameters
	 * @return array
	 */
	protected function build_request_params() {

		$params      = array();
		$max_width   = $this->settings->get( MGO_Settings::MAX_WIDTH );
		$max_height  = $this->settings->get( MGO_Settings::MAX_HEIGHT );
		$compression = $this->settings->get( MGO_Settings::COMPRESSION );
		$keep_exif   = $this->settings->get( MGO_Settings::PRESERVE_EXIF );
		$cmyktorgb   = $this->settings->get( MGO_Settings::CMYKTORGB );
		$http_user   = $this->settings->get( MGO_Settings::HTTP_USER );
		$http_pass   = $this->settings->get( MGO_Settings::HTTP_PASS );
		$webp        = $this->settings->get( MGO_Settings::WEBP_CREATE_IMAGES );

		if ( ! empty( $max_width ) && $max_width > 0 ) {
			$params['max_width'] = $max_width;
		}
		if ( ! empty( $max_height ) && $max_height > 0 ) {
			$params['max_height'] = $max_height;
		}
		if ( in_array( $compression, array( 'intelligent', 'ultra', 'lossless' ) ) ) {
			$params['compression'] = $compression;
		} else {
			$params['compression'] = 'intelligent';
		}
		if ( $keep_exif != 1 ) {
			$params['keep_exif'] = 0;
		} else {
			$params['keep_exif'] = $keep_exif;
		}
		if ( $cmyktorgb != 1 ) {
			$params['cmyktorgb'] = 0;
		} else {
			$params['cmyktorgb'] = $cmyktorgb;
		}
		if( $webp == 1 ) {
			$params['webp'] = 1;
		}
		if ( ! empty( $http_user ) && ! empty( $http_pass ) ) {
			$params['http_user'] = $http_user;
			$params['http_pass'] = $http_pass;
		}

		return $params;
	}
}