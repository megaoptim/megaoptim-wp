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

if ( ! function_exists( '_megaoptim_cloudflare_purge' ) ) {
	/**
	 * @param MGO_MediaAttachment|MGO_FileAttachment|MGO_NGGLibrary $attachment
	 * @param string $resource
	 * @param \MegaOptim\Client\Responses\Response $response
	 * @param array $params
	 * @param array $size
	 */
	function _megaoptim_cloudflare_purge( $attachment, $resource, $response, $params, $size ) {

		$cloudflare = new MGO_CloudFlare();
		if ( $response->isSuccessful() && $cloudflare->valid_credentials() ) {
			$urls_to_purge = array();
			if ( $attachment instanceof MGO_MediaAttachment ) {
				// Purge thumbnails
				$url_data = wp_get_attachment_image_src( $attachment->get_id(), $size );
				if ( ! empty( $url_data ) ) {
					array_push( $urls_to_purge, $url_data[0] );
				}
			} else if ( $attachment instanceof MGO_NGGAttachment || $attachment instanceof MGO_FileAttachment ) {
				// If $resource is url it means that the website is public and probably uses cloudflare.
				if ( megaoptim_is_url( $resource ) ) {
					array_push( $urls_to_purge, $response );
				}
			}
			// If urls found, purge them.
			if ( ! empty( $urls_to_purge ) && count($urls_to_purge) > 0 ) {
				if ( ! $cloudflare->purge_files( $urls_to_purge ) ) {
					megaoptim_log( 'Warning: Failed to purge the cloudflare urls.' );
				}
			}
		}
	}
	add_action( 'megaoptim_size_optimized', '_megaoptim_cloudflare_purge', 10, 5 );
}
