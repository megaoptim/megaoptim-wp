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

namespace MegaOptim\Interfaces;

interface IFile {
	/**
	 * Overwrite the local file with the optimized file
	 * @return mixed
	 */
	public function saveOverwrite();

	/**
	 * Save the optimized file to the full path
	 *
	 * @param $path
	 *
	 * @return mixed
	 */
	public function saveAsFile( $path );


	/**
	 * Save the optimized file to the provided directory
	 *
	 * @param $dir
	 *
	 * @return mixed
	 */
	public function saveToDir( $dir );
}