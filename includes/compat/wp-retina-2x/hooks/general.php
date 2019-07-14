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
 * @param MGO_MediaAttachment $attachment_object
 * @param $request_params
 * @param MGO_ResultBag $result
 */
function _megaoptim_wr2x_before_finish( $attachment_object, $request_params, $result ) {

	try {
		$megaoptim = MGO_Library::get_optimizer();
		megaoptim_log( 'Optimizing retinas...' );
		// Optimize the original retina?

		$exists       = megaoptim_retina_size_exists( $attachment_object, 'full' );
		$is_optimized = megaoptim_retina_is_size_processed( $attachment_object, 'full' );

		if ( $exists && ! $is_optimized ) {
			megaoptim_log( 'Found main retina image, optimizing...' );
			$retina_resource = MGO_MediaLibrary::instance()->get_attachment( $attachment_object->get_id(), 'full', true );
			if ( ! empty( $retina_resource ) ) {
				$response = $megaoptim->run( $retina_resource, $request_params );
				$result->add( 'full@2x', $response );
				if ( $response->isError() ) {
					megaoptim_log( $response->getErrors() );
				} else {
					$retina_path = MGO_MediaLibrary::instance()->get_attachment_path( $attachment_object->get_id(), 'full', true );
					foreach ( $response->getOptimizedFiles() as $file ) {
						$file->saveAsFile( $retina_path );
					}
					$attachment_object = megaoptim_retina_set_data( $attachment_object, $response, $request_params );
					$attachment_object->save();
					/**
					 * Fired when attachment is successfully optimized
					 * Tip: Use instanceof $attachment_object to check what kind of attachment was optimized.
					 * @since 1.0.0
					 *
					 * @param MGO_MediaAttachment $attachment_object - The media attachment.
					 * @param \MegaOptim\Responses\Response $response - The api request response
					 * @param array $request_params - The api request parameters
					 * @param string $size - The size optimized
					 */
					do_action( 'megaoptim_retina_attachment_optimized', $attachment_object, $retina_resource, $response, $request_params, 'full' );

					megaoptim_log( 'Full size retina version successfully optimized' );
				}
			}
		}

		$remaining_thumbnails = $attachment_object->get_remaining_thumbnails();
		if ( ! empty( $remaining_thumbnails['retina'] ) ) {
			megaoptim_log( 'Found retina ' . count( $remaining_thumbnails['retina'] ) . ' thumbnails, optimizing them now.' );
			foreach ( $remaining_thumbnails['retina'] as $size ) {
				$exists       = megaoptim_retina_size_exists( $attachment_object, $size );
				$is_optimized = megaoptim_retina_is_size_processed( $attachment_object, $size );

				if ( $exists && ! $is_optimized ) {
					$thumbnail_resource = MGO_MediaLibrary::instance()->get_attachment( $attachment_object->get_id(), $size, true );

					if ( ! empty( $thumbnail_resource ) ) {
						$response = $megaoptim->run( $thumbnail_resource, $request_params );
						$result->add( "{$size}@2x", $response );
						if ( $response->isError() ) {
							megaoptim_log( $response->getErrors() );
						} else {
							$thumbnail_path = MGO_MediaLibrary::instance()->get_attachment_path( $attachment_object->get_id(), $size, true );
							foreach ( $response->getOptimizedFiles() as $file ) {
								// TODO: Maybe backup thumbnail?
								$file->saveAsFile( $thumbnail_path );
							}
							$attachment_object = megaoptim_retina_set_thumbnail_data( $attachment_object, $size, $response, $request_params );
							megaoptim_log( $attachment_object->get_raw_data() );
							$attachment_object->save();
							/**
							 * Fired when attachment thumbnail was successfully optimized
							 * Tip: Use instanceof $attachment_object to check what kind of attachment was optimized.
							 * @since 1.0.0
							 *
							 * @param MGO_MediaAttachment $attachment_object - The media attachment. Useful to check with instanceof.
							 * @param \MegaOptim\Responses\Response $response - The api request response
							 * @param array $request_params - The api request parameters
							 * @param string $size - The thumbnail version
							 */
							do_action( 'megaoptim_retina_attachment_optimized', $attachment_object, $thumbnail_resource, $response, $request_params, $size );
							megaoptim_log( 'Retina thumbnail ' . $size . 'optimized successfully!' );
						}
					}
				}
			}
		}

	} catch ( Exception $e ) {
		megaoptim_log( $e->getMessage() );
	}
}

add_action( 'megaoptim_before_finish', '_megaoptim_wr2x_before_finish', 10, 3 );


/**
 * Setup the unoptimized retina thumbnails
 *
 * @param $thumbnails
 * @param MGO_MediaAttachment $attachment
 *
 * @return mixed
 */
function _megaoptim_wr2x_ml_attachment_unoptimized_thumbnails( $thumbnails, $attachment ) {
	$metadata     = $attachment->get_metadata();
	$alloed_sizes = MGO_Settings::instance()->get( MGO_Settings::RETINA_IMAGE_SIZES );

	// Bail if there is nothing to optimize.
	if ( empty( $alloed_sizes ) ) {
		return $thumbnails;
	}

	if ( ! isset( $thumbnails['retina'] ) ) {
		$thumbnails['retina'] = array();
	}
	foreach ( $metadata['sizes'] as $key => $size ) {
		if ( ! in_array( $key, $alloed_sizes ) ) {
			continue;
		}
		if ( megaoptim_retina_size_exists( $attachment, $key ) && ! megaoptim_retina_is_size_processed( $attachment, $key ) ) {
			array_push( $thumbnails['retina'], $key );
		}
	}

	return $thumbnails;
}
add_filter( 'megaoptim_ml_attachment_unoptimized_thumbnails', '_megaoptim_wr2x_ml_attachment_unoptimized_thumbnails', 10, 2 );

/**
 * Register the optimized retina thumbnails.
 *
 * @param $thumbnails
 * @param MGO_MediaAttachment $attachment
 *
 * @return mixed
 */
function _megaoptim_wr2x_ml_attachment_optimized_thumbnails( $thumbnails, $attachment ) {
	$megaoptim_data = $attachment->get_raw_data();
	if ( ! isset( $megaoptim_data['retina']['thumbs'] ) || ! is_array( $megaoptim_data['retina']['thumbs'] ) ) {
		return $thumbnails;
	}
	foreach ( $megaoptim_data['retina']['thumbs'] as $key => $thumb ) {
		if ( isset( $thumb['status'] ) && intval( $thumb['status'] ) == 1 ) {
			$thumbnails['retina'][$key] = $thumb;
		}
	}

	return $thumbnails;
}

add_filter( 'megaoptim_ml_attachment_optimized_thumbnails', '_megaoptim_wr2x_ml_attachment_optimized_thumbnails', 10, 2 );


/**
 * Returns the optimized thumbnails
 *
 * @param $status
 * @param MGO_MediaAttachment $attachment
 *
 * @return bool
 */
function _megaoptim_wr2x_ml_is_optimized( $status, $attachment ) {

	$data                   = $attachment->get_raw_data();
	$unoptimized_thumbnails = $attachment->get_remaining_thumbnails();
	$total_unoptimized      = isset( $unoptimized_thumbnails['retina'] ) ? count( $unoptimized_thumbnails['retina'] ) : 0;

	$is_full_optimized = isset( $data['retina']['full']['status'] ) && ! empty( $data['retina']['full']['status'] );
	$retina_full       = wr2x_get_retina( $attachment->get_path() );

	if ( file_exists( $retina_full ) ) {
		return $status && $total_unoptimized === 0 && $is_full_optimized;
	} else {
		return $status && $total_unoptimized === 0;
	}
}

add_filter( 'megaoptim_ml_attachmed_is_optimized', '_megaoptim_wr2x_ml_is_optimized', 10, 2 );


/**
 * Calculate full retina saved bytes
 *
 * @param $saved_bytes
 * @param $attachment
 *
 * @return float|int|string
 */
function _megaoptim_wr2x_ml_total_saved_bytes( $saved_bytes, $attachment ) {
	$retina_saved_bytes = megaoptim_retina_get_total_saved_bytes( $attachment, false, false );

	return $saved_bytes + $retina_saved_bytes;
}

add_filter( 'megaoptim_ml_total_saved_bytes', '_megaoptim_wr2x_ml_total_saved_bytes', 10, 2 );

/**
 * @param $saved_bytes
 * @param $attachment
 *
 * @return float|int|string
 */
function _megaoptim_wr2x_ml_total_saved_bytes_thumbnails( $saved_bytes, $attachment ) {
	$retina_saved_bytes = megaoptim_retina_get_total_saved_bytes_thumbnails( $attachment, false );

	return $saved_bytes + $retina_saved_bytes;
}

add_filter( 'megaoptim_ml_total_saved_bytes_thumbnails', '_megaoptim_wr2x_ml_total_saved_bytes_thumbnails', 10, 2 );


/**
 * @param array $row
 * @param MGO_MediaAttachment $attachment
 *
 * @return array
 */
function _megaoptim_attachment_optimization_stats( $row, $attachment ) {
	$row['saved_thumbs_retina'] = megaoptim_retina_get_total_saved_bytes_thumbnails( $attachment, true );

	return $row;
}

add_filter( 'megaoptim_attachment_optimization_stats', '_megaoptim_attachment_optimization_stats', 10, 2 );