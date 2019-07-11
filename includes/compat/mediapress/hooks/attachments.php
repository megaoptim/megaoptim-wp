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

if ( ! function_exists( '_megaoptim_optimize_mediapress_attachment' ) ) {

	/**
	 * Optimize media attachment?
	 *
	 * @param $metadata
	 * @param $attachment_id
	 *
	 * @return mixed
	 */
	function _megaoptim_optimize_mediapress_attachment( $metadata, $attachment_id ) {

		/**
		 * Hook to optimize the media attachment or not.
		 *
		 * @since  1.0.0
		 *
		 * @param bool $optimize True to optimize, false otherwise.
		 * @param int $post_id Attachment ID.
		 * @param array $metadata An array of attachment meta data.
		 */
		$optimize = apply_filters( 'megaoptim_auto_optimize_mediapress_attachment', MGO_Settings::instance()->isAutoOptimizeEnabled(), $attachment_id, $metadata );

		if ( ! $optimize ) {
			return $metadata;
		}

		megaoptim_async_optimize_attachment( $attachment_id, $metadata );

		return $metadata;
	}

	add_filter( 'mpp_generate_metadata', '_megaoptim_optimize_mediapress_attachment', WP_MEGAOPTIM_INT_MAX, 2 );
}



