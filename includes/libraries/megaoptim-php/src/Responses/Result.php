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

use MegaOptim\Http\Client;
use MegaOptim\Interfaces\IFile;
use MegaOptim\Tools\FileSystem;

class Result implements IFile {
	/**
	 * The file name
	 * @var string
	 */
	private $file_name = '';
	/**
	 * The size of the orignal image
	 * @var int
	 */
	private $original_size = 0;
	/**
	 * The size of the optimized image
	 * @var int
	 */
	private $optimized_size = 0;
	/**
	 * How much it saved in bytes
	 * @var int
	 */
	private $saved_bytes = 0;
	/**
	 * What is the saved percentage?
	 * @var int
	 */
	private $saved_percent = 0;
	/**
	 * What is the url?
	 * @var string
	 */
	private $url = '';

	/**
	 * WebP Result
	 * @var ResultWebP
	 */
	private $webp;

	/**
	 * If image optimization saved percent is equal or less than 5% it wont count so this will be 0.
	 * @var int
	 */
	private $success;

	/**
	 * The local file
	 * If this is not null it means a file by path was optimized and this is it's local path.
	 * If this is null it means that url was optimized and there is no local path.
	 * @var string
	 */
	private $prev_local_path = null;

	/**
	 * Result constructor.
	 *
	 * @param $result
	 */
	public function __construct( $result ) {
		if ( is_string( $result ) ) {
			$result = @json_decode( $result );
		}
		if ( isset( $result->file_name ) ) {
			$this->file_name = $result->file_name;
		}
		if ( isset( $result->original_size ) ) {
			$this->original_size = $result->original_size;
		}
		if ( isset( $result->optimized_size ) ) {
			$this->optimized_size = $result->optimized_size;
		}
		if ( isset( $result->saved_bytes ) ) {
			$this->saved_bytes = $result->saved_bytes;
		}
		if ( isset( $result->saved_percent ) ) {
			$this->saved_percent = $result->saved_percent;
		}
		if ( isset( $result->url ) ) {
			$this->url = $result->url;
		}
		if ( isset( $result->success ) ) {
			$this->success = intval($result->success);
		}
		if ( isset( $result->webp ) ) {
			$this->webp = new ResultWebP($result);
		}
	}


	/**
	 * Set the local file of this one
	 *
	 * @param $lp
	 */
	public function setLocalPath( $lp ) {
		$this->prev_local_path = $lp;
	}

	/**
	 * Returns the original size
	 * @return int
	 */
	public function getOriginalSize() {
		return $this->original_size;
	}

	/**
	 * Returns the optimized size
	 * @return int
	 */
	public function getOptimizedSize() {
		return $this->optimized_size;
	}

	/**
	 * Returns the saved size in bytes
	 * @return int
	 */
	public function getSavedBytes() {
		return $this->saved_bytes;
	}

	/**
	 * Returns the file name
	 * @return string
	 */
	public function getFileName() {
		return $this->file_name;
	}

	/**
	 * Returns the saved percentage
	 * @return int
	 */
	public function getSavedPercent() {
		return $this->saved_percent;
	}

	/**
	 * Returns the optimized url
	 * @return string
	 */
	public function getUrl() {
		return $this->url;
	}

	/**
	 * Returns WebP object
	 * @return ResultWebP
	 */
	public function getWebP() {
		return $this->webp;
	}

	/**
	 * Check if the attachment was optimized.
	 * @return bool
	 */
	public function isSuccessfullyOptimized() {
		return $this->success == 1;
	}

	/**
	 * Check if the attachment was already optimized.
	 * @return bool
	 */
	public function isAlreadyOptimized() {
		return $this->success == 0;
	}

	/**
	 * Overwrite the local file with the optimized file
	 * @return mixed
	 * @throws \Exception
	 */
	public function saveOverwrite() {
		if ( is_null( $this->prev_local_path ) || ! file_exists( $this->prev_local_path ) ) {
			throw new \Exception( 'There is no local file for this result to overwrite, If the source is url, you should save it with saveToFile() or saveToDir() methods.' );
		} else {
			if ( ! Client::download( $this->url, $this->prev_local_path ) ) {
				throw new \Exception( 'Unable to overwrite the local file.' );
			}
		}

		return $this->prev_local_path;
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
		FileSystem::maybe_prepare_output_dir( $path );
		if ( ! Client::download( $this->url, $path ) ) {
			throw new \Exception( 'Unable to overwrite the local file.' );
		}

		return $path;

	}


	/**
	 * Save the optimized file to the provided directory
	 *
	 * @param $dir
	 *
	 * @return mixed|null|string
	 * @throws \Exception
	 */
	public function saveToDir( $dir ) {
		$path = FileSystem::maybe_add_trailing_slash( $dir . DIRECTORY_SEPARATOR ) . $this->getFileName();
		FileSystem::maybe_prepare_output_dir( $path );
		if ( ! Client::download( $this->url, $path ) ) {
			throw new \Exception( 'Unable to overwrite the local file.' );
		}

		return $path;

	}

}