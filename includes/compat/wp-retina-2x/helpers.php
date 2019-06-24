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

/**
 * Removes retina data
 *
 * @param MGO_MediaAttachment $attachment
 * @param bool $include_full
 *
 * @return bool
 */
function megaoptim_retina_remove_attachment_data( $attachment, $include_full = true ) {
	$data = $attachment->get_raw_data();
	if ( ! isset( $data['retina'] ) ) {
		return false;
	}
	if ( $include_full ) {
		unset( $data['retina'] );
	} else {
		$thumbs = null;
		if ( isset( $data['retina']['thumb'] ) ) {
			$thumbs = $data['retina']['thumbs'];
		}
		unset( $data['retina'] );
		if ( ! is_null( $thumbs ) ) {
			$data['retina']           = array();
			$data['retina']['thumbs'] = $thumbs;
		}
	}
	$attachment->set_raw_data( $data );
	$attachment->save();

	return true;
}

/**
 * @param MGO_MediaAttachment $attachment
 *
 * @return bool
 */
function megaoptim_retina_remove_full_attachment_data( $attachment ) {
	$data = $attachment->get_raw_data();
	if ( ! isset( $data['retina'] ) ) {
		return false;
	}
	$thumbs = null;
	if ( isset( $data['retina']['thumb'] ) ) {
		$thumbs = $data['retina']['thumbs'];
	}
	unset( $data['retina'] );
	if ( ! is_null( $thumbs ) ) {
		$data['retina']           = array();
		$data['retina']['thumbs'] = $thumbs;
		$attachment->set_raw_data( $data );
		$attachment->save();
	}
}

/**
 * @param MGO_MediaAttachment $attachment
 * @param $size
 *
 * @return bool
 */
function megaoptim_retina_is_size_optimized( $attachment, $size ) {
	$data = $attachment->get_raw_data();
	if ( $size === 'full' ) {
		return isset( $data['retina']['full']['status'] ) && ! empty( $data['retina']['full']['status'] );
	} else {
		return isset( $data['retina']['thumbs'][ $size ]['status'] ) && ! empty( $data['retina']['thumbs'][ $size ]['status'] );
	}
}

/**
 * Check if retina size exists
 *
 * @param MGO_MediaAttachment $attachment
 * @param $size
 *
 * @return bool
 */
function megaoptim_retina_size_exists( $attachment, $size ) {
	$path = MGO_MediaLibrary::instance()->get_attachment_path( $attachment->get_id(), $size, true );

	return $path !== false;
}

/**
 * Total saved full_size
 *
 * @param MGO_MediaAttachment $attachment
 * @param bool $formatted
 * @param bool $include_thumbnails
 *
 * @return float|int|string
 */
function megaoptim_retina_get_total_saved_bytes( $attachment, $formatted = false, $include_thumbnails = false ) {
	$data  = $attachment->get_raw_data();
	$bytes = 0;
	if ( $include_thumbnails ) {
		$bytes += megaoptim_retina_get_total_saved_bytes_thumbnails( $attachment, false );
	}
	$bytes += isset( $data['retina']['full']['saved_bytes'] ) ? $data['retina']['full']['saved_bytes'] : 0;

	if ( $formatted ) {
		$bytes = megaoptim_human_file_size( $bytes );
	}

	return $bytes;
}

/**
 * Total saved on thumbnails
 *
 * @param MGO_MediaAttachment $attachment
 * @param bool $formatted
 *
 * @return float|int|string
 */
function megaoptim_retina_get_total_saved_bytes_thumbnails( $attachment, $formatted = false ) {
	$data = $attachment->get_raw_data();

	if ( ! isset( $data['retina']['thumbs'] ) || ! is_array( $data['retina']['thumbs'] ) ) {
		return 0;
	}

	$bytes = 0;

	foreach ( $data['retina']['thumbs'] as $thumb ) {
		if ( $thumb['saved_bytes'] > 0 ) {
			$bytes += (float) $thumb['saved_bytes'];
		}
	}

	if ( $formatted ) {
		$bytes = megaoptim_human_file_size( $bytes );
	}

	return $bytes;
}

/**
 * Returns modified object ready for saving with retina data.
 *
 * @param MGO_MediaAttachment $attachment
 * @param \MegaOptim\Responses\Response $response
 * @param array $params
 *
 * @return MGO_MediaAttachment
 */
function megaoptim_retina_set_data( $attachment, $response, $params ) {

	$data = $attachment->get_raw_data();

	if ( ! isset( $data['retina'] ) ) {
		$data['retina'] = array();
	}
	if ( ! isset( $data['retina']['full'] ) ) {
		$data['retina']['full'] = array();
	}

	$data['retina']['full']['status'] = (int) $response->isSuccessful();

	$files = $response->getOptimizedFiles();
	if ( ! empty( $files ) ) {
		$response_data = json_decode( $response->getRawResponse(), true );
		if ( ! empty( $response_data['result'] ) ) {
			foreach ( $response_data['result'] as $optimization ) {
				foreach ( $optimization as $key => $value ) {
					if ( ! in_array( $key, MGO_Attachment::excluded_params() ) ) {
						$data['retina']['full'][ $key ] = $value;
					}
				}
				break;
			}
		}

		$data['retina']['full']['process_id'] = $response->getProcessId();
		$data['retina']['full']['time']       = date( 'Y-m-d H:i:s', time() );
		foreach ( megaoptim_get_allowed_query_parameters() as $parameter ) {
			if ( isset( $params[ $parameter ] ) ) {
				$data['retina']['full'][ $parameter ] = $params[ $parameter ];
			}
		}
	}

	$attachment->set_raw_data( $data );

	return $attachment;
}

/**
 * Set the thumbnail data for the response.
 *
 * @param MGO_MediaAttachment $attachment
 * @param $size
 * @param \MegaOptim\Responses\Response $response
 * @param $params
 *
 * @return MGO_MediaAttachment
 */
function megaoptim_retina_set_thumbnail_data( $attachment, $size, $response, $params ) {

	$data = $attachment->get_raw_data();

	$thumbnail_data = megaoptim_generate_thumbnail_data( $response, $params );

	if ( ! isset( $data['retina'] ) ) {
		$data['retina'] = array();
	}
	if ( ! isset( $data['retina']['thumbs'] ) ) {
		$data['retina']['thumbs'] = array();
	}
	if ( ! empty( $thumbnail_data ) ) {
		$data['retina']['thumbs'][ $size ] = $thumbnail_data;
	}

	$attachment->set_raw_data( $data );

	return $attachment;
}


