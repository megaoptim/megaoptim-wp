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
 * Class MGO_File
 */
class MGO_File {
	public $ID;
	public $title;
	public $thumbnail;
	public $thumbnail_path;
	public $directory;
	public $path;
	public $url;

	/**
	 * MGO_File constructor.
	 *
	 * @param null $data
	 */
	public function __construct( $data = null ) {
		if ( is_array( $data ) ) {
			foreach ( $data as $key => $value ) {
				$this->$key = $value;
			}
		}

		foreach(array('thumbnail_path', 'directory', 'path') as $key) {
			if(!empty($this->$key) && !is_null($this->$key)) {
				$this->$key = wp_normalize_path($this->$key);
			}
		}

		foreach(array('thumbnail', 'url') as $key) {
			if(!empty($this->$key) && !is_null($this->$key)) {
				$this->$key = megaoptim_maybe_fix_url($this->$key);
			}
		}
	}

	/**
	 * Check if the file exist locally.
	 * @return bool
	 */
	public function exists() {
		return ! is_null( $this->path ) ? file_exists( $this->path ) : false;
	}
}