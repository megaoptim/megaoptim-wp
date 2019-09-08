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

use MegaOptim\Tools\FileSystem;
use MegaOptim\Http\Client;

class ResultWebP {
	public $url;
	public $optimized_size;
	public $saved_bytes;
	public $saved_percent;

	public function __construct($response) {
		if(isset($response->webp) && isset($response->webp->url)) {
			$this->url = $response->webp->url;
			$this->optimized_size = $response->webp->optimized_size;
			$this->saved_bytes = $response->webp->saved_bytes;
			$this->saved_percent = $response->webp->saved_percent;
		}
	}

	/**
	 * Save the optimized file to the full path
	 *
	 * @param $path
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function saveAsFile( $path ) {
		if(is_null($this->url)) {
			return false;
		}
		FileSystem::maybe_prepare_output_dir( $path );
		if ( ! Client::download( $this->url, $path ) ) {
			throw new \Exception( 'Unable to overwrite the local file.' );
		}
		return $path;

	}

	/**
	 * Return the saved bytes
	 * @return mixed
	 */
	public function getSavedBytes() {
		return $this->saved_bytes;
	}
}