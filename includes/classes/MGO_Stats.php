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

class MGO_Stats {
	// Is the gallery empty?
	public $empty_gallery;
	// Total images found in the system
	public $total_images;
	// Total: Both thumbnails and main images
	public $total_optimized_mixed;
	// Total percentage of both thumbnails + images
	public $total_optimized_mixed_percentage;
	// Total fully optimized media library attachments. Fully optimized attachment is one that also its thumbnails are optimized.
	public $total_fully_optimized_attachments;
	// Total thumbnails optimized
	public $total_thumbnails_optimized;
	// Total saved bytes overall ( both thumbnails and main images )
	public $total_saved_bytes;
	// The total saved in human readable form
	public $total_saved_bytes_human;
	// Total remaining
	public $total_remaining;
	// Remaining list
	public $remaining;

	/**
	 * Calcultes the optimized percentage.
	 */
	public function setup() {
		if ( ! in_array( 0, array( $this->total_optimized_mixed, $this->total_images ) ) ) {
			$this->total_optimized_mixed_percentage = ( $this->total_optimized_mixed / $this->total_images ) * 100;
			$this->total_optimized_mixed_percentage = round( $this->total_optimized_mixed_percentage, 1 );
		} else {
			$this->total_optimized_mixed_percentage = 0;
		}
		if ( $this->total_saved_bytes > 0 ) {
			$this->total_saved_bytes_human = megaoptim_human_file_size( $this->total_saved_bytes, "MB", false );
		} else {
			$this->total_saved_bytes_human = 0;
		}
	}

	/**
	 * @param $list
	 */
	public function set_remaining( $list ) {
		$this->remaining = array_values( $list );
	}

	/**
	 * Add other stats object to this one.
	 *
	 * @param MGO_Stats $stats
	 */
	public function add( $stats ) {
		if ( $this->empty_gallery && $stats->empty_gallery ) {
			$this->empty_gallery = 0;
		}
		$this->total_images                      += $stats->total_images;
		$this->total_optimized_mixed             += $stats->total_optimized_mixed;
		$this->total_optimized_mixed_percentage  = ( $this->total_optimized_mixed_percentage + $stats->total_optimized_mixed_percentage ) / 2;
		$this->total_fully_optimized_attachments += $stats->total_fully_optimized_attachments;
		$this->total_thumbnails_optimized        += $stats->total_thumbnails_optimized;
		$this->total_saved_bytes                 += $stats->total_saved_bytes;
		if ( empty( $this->remaining ) ) {
			$this->remaining = array();
		}
		if ( ! empty( $stats->remaining ) ) {
			$this->remaining = array_merge( $this->remaining, $stats->remaining );
		}
		$this->total_remaining = count( $this->remaining );
	}
}