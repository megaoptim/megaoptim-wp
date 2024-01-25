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
 * Class MGO_MediaAttachment
 */
class MGO_MediaAttachment extends MGO_Attachment {

	const TYPE = MEGAOPTIM_TYPE_MEDIA_ATTACHMENT;
	const PM_DATA_KEY = '_megaoptim_data';
	const WP_METADATA_KEY = '_wp_attachment_metadata';

	private $has_metadata;
	/**
	 * The wordpress attachment metadata - _wp_attachment_metadata
	 * @var array
	 */
	protected $metadata;

	/**
	 * MGO_MediaAttachment constructor.
	 *
	 * @param $id
	 *
	 * @throws MGO_Exception
	 */
	public function __construct( $id = null ) {

		if ( ! is_null( $id ) ) {
			if ( ! wp_attachment_is_image( $id ) ) {
				throw ( new MGO_Exception( 'Not a valid image.' ) );
			}
			parent::__construct( $id );
		}

	}

	/**
	 * Removes meta data
	 */
	private function delete_data() {
		delete_post_meta( $this->get_id(), self::PM_DATA_KEY );
	}

	/**
	 * Returns meta data
	 *
	 * @param $key
	 *
	 * @return mixed
	 */
	private function get_meta( $key ) {
		return get_post_meta( $this->ID, $key, true );
	}

	/**
	 * Saves meta into db
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return bool|int
	 */
	private function save_meta( $key, $value ) {
		return update_post_meta( $this->ID, $key, $value );
	}

	/**
	 * Implements the saving meta functionality
	 */
	public function save() {
		$this->save_meta( self::PM_DATA_KEY, $this->data );
	}

	/**
	 * Load the saved meta
	 */
	protected function __load() {
		$this->metadata = $this->get_meta( self::WP_METADATA_KEY );
		if ( empty( $this->metadata ) ) {
			$this->has_metadata = false;
		} else {
			$this->has_metadata = true;
		}
		$this->data = $this->get_meta( self::PM_DATA_KEY );
		if ( empty( $this->data ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Clean up the temporary files and data
	 * @return mixed
	 */
	public function clean_up() {
		delete_post_meta( $this->ID, self::PM_DATA_KEY );

		return true;
	}

	/**
	 * Returns true if there is backup for specific attachment
	 *
	 * @return bool
	 */
	public function has_backup() {
		if ( ! $this->get_backup_path() || $this->get_backup_path() === '' ) {
			return false;
		}

		return file_exists( $this->get_backup_path() );
	}

	/**
	 * Creates backup copy of specific attachment
	 *
	 * @return string
	 */
	public function backup() {
		$attachment_path = get_attached_file( $this->get_id(), true );
		$backup_path     = megaoptim_get_ml_attachment_backup_path( $this->get_id(), $attachment_path );
		$dir_path        = dirname( $backup_path );
		$dir_path        = wp_normalize_path( $dir_path );
		if ( ! file_exists( $dir_path ) ) {
			@mkdir( $dir_path . DIRECTORY_SEPARATOR, 0755, true );
		}
		if ( file_exists( $dir_path ) && is_dir( $dir_path ) ) {
			@copy( $attachment_path, $backup_path );
			if ( file_exists( $backup_path ) ) {
				$this->set_backup_path( $backup_path );
				$this->save();

				return $backup_path;
			} else {
				megaoptim_log( 'Backup can not be created. Path to %s does not exists or is not writable.', $dir_path );

				return false;
			}
		} else {
			megaoptim_log( sprintf( 'Backup can not be created. Directory %s does not exists or is not writable.', $dir_path ) );
		}

		return false;

	}

	/**
	 * Restores a backed up file. Returns false if the target file or directory is NOT writable
	 *
	 * @return bool
	 * @throws MGO_Exception
	 */
	public function restore() {
		$attachment_path = get_attached_file( $this->get_id(), true );
		$backup_path     = $this->get_backup_path();
		if ( empty( $backup_path ) || ! file_exists( $backup_path ) ) {
			throw new MGO_Exception( "Backup path is empty!" );
		}
		if ( ! is_writable( dirname( $attachment_path ) ) ) {
			throw new MGO_Exception( "Directory is not writable!" );
		}
		if ( @rename( $backup_path, $attachment_path ) ) {
			// This must be called here because the megaoptim_regenerate_thumbnails() triggers action which autoptimizes the thumbnails.
			megaoptim_prevent_auto_optimization();
			$meta_data = megaoptim_regenerate_thumbnails( $this->get_id(), $attachment_path );
			megaoptim_restore_auto_optimization();
			$this->metadata = $meta_data;
			$this->destroy();
			$this->save();
			do_action( 'megaoptim_after_restore_attachment', $this );
		}

		return true;
	}

	/**
	 * The overall savings from all thumbnails of this specific attachment.
	 *
	 * @param bool $formatted
	 * @param bool $include_thumbnails
	 * @param bool $include_retina
	 *
	 * @return float|int|string
	 */
	public function get_total_saved_bytes( $formatted = false, $include_thumbnails = false, $include_retina = true ) {
		// Sum 'Full' size attachment saved bytes
		$bytes = $this->get_saved_bytes();
		// Sum 'Full' size retina attachment saved bytes
		$bytes += isset( $this->data['retina']['saved_bytes'] ) ? $this->data['retina']['saved_bytes'] : 0;
		// Sum Thumbnails saved bytes (normal or normal + retina)
		if ( $include_thumbnails ) {
			$bytes += $this->get_total_saved_bytes_thumbnails( false, $include_retina );
		}
		// Format?
		if ( $formatted ) {
			$bytes = megaoptim_human_file_size( $bytes );
		}

		return $bytes;
	}

	/**
	 * Total saved on thumbnails
	 *
	 * @param bool $formatted
	 *
	 * @param bool $include_retina
	 *
	 * @return float|int|string
	 */
	public function get_total_saved_bytes_thumbnails( $formatted = false, $include_retina = true ) {
		$bytes = 0;
		if ( isset( $this->data['thumbs'] ) && is_array( $this->data['thumbs'] ) ) {
			foreach ( $this->data['thumbs'] as $thumb ) {
				if ( $thumb['saved_bytes'] > 0 ) {
					$bytes += (float) $thumb['saved_bytes'];
				}
			}
		}
		if ( $include_retina && isset( $this->data['retina']['thumbs'] ) ) {
			foreach ( $this->data['retina']['thumbs'] as $thumb ) {
				if ( $thumb['saved_bytes'] > 0 ) {
					$bytes += (float) $thumb['saved_bytes'];
				}
			}
		}
		if ( $formatted ) {
			$bytes = megaoptim_human_file_size( $bytes );
		}

		return $bytes;
	}

	/**
	 * Set the attachment data
	 *
	 * @param \MegaOptim\Client\Responses\Response $response
	 * @param $params
	 */
	public function set_data( $response, $params ) {
		//parent::set_data( $response, $params );
		_deprecated_function( __METHOD__, '1.3.0', 'set_attachment_data' );
	}

	/**
	 * Sets response for specific thumbnail
	 *
	 * @param $size
	 * @param \MegaOptim\Client\Responses\Response $response
	 * @param $params
	 */
	public function set_thumbnail_data( $size, $response, $params ) {
		_deprecated_function( __METHOD__, '1.3.0', 'set_attachment_data' );
	}


	/**
	 * Set attachment data
	 *
	 * @param $size
	 * @param $params
	 * @param bool $retina
	 */
	public function set_attachment_data( $size, $params, $retina = false ) {
		if ( ! $retina ) {
			if ( $size === 'full' ) {
				$this->data = array_merge( $this->data, $params );
			} else {
				if ( ! isset( $this->data['thumbs'] ) ) {
					$this->data['thumbs'] = array();
				}
				if ( ! empty( $params ) ) {
					$this->data['thumbs'][ $size ] = $params;
				}
			}
		} else {
			if ( ! isset( $this->data['retina'] ) || ! is_array( $this->data['retina'] ) ) {
				$this->data['retina'] = array();
			}
			if ( $size === 'full' ) {
				$this->data['retina'] = array_merge( $this->data['retina'], $params );
			} else {
				if ( ! isset( $this->data['retina']['thumbs'] ) ) {
					$this->data['retina']['thumbs'] = array();
				}
				if ( ! empty( $params ) ) {
					$this->data['retina']['thumbs'][ $size ] = $params;
				}
			}
		}
		if ( $this->get_saved_bytes() > 0 ) {
			$this->remove_error( $size );
		}

	}

	/**
	 * Returns associative array of data
	 * @return array
	 */
	public function get_optimization_stats() {

		$thumbnails = $this->get_processed_thumbnails();
		$row        = array();
		$row['ID']  = $this->ID;

		// Stats Formatted
		$row['optimized_size']      = $this->get_optimized_size( true );
		$row['original_size']       = $this->get_original_size( true );
		$row['saved_bytes']         = $this->get_saved_bytes( true );
		$row['saved_percent']       = $this->get_saved_percent( false, 2 );
		$row['saved_thumbs']        = $this->get_total_saved_bytes_thumbnails( true, false );
		$row['saved_thumbs_retina'] = $this->get_total_saved_bytes_thumbnails( true, true );
		// Stats Raw
		$row['raw']                        = array();
		$row['raw']['optimized_size']      = $this->get_optimized_size( false );
		$row['raw']['original_size']       = $this->get_original_size( false );
		$row['raw']['saved_bytes']         = $this->get_saved_bytes( false );
		$row['raw']['saved_thumbs']        = $this->get_total_saved_bytes_thumbnails( false, false );
		$row['raw']['saved_thumbs_retina'] = $this->get_total_saved_bytes_thumbnails( false, true );
		$row['raw']['saved_total']         = (int) $row['raw']['saved_bytes'] + (int) $row['raw']['saved_thumbs'] + (int) $row['raw']['saved_thumbs_retina'];
		$row['raw']['saved_total_mb']      = (float) megaoptim_convert_bytes_to_specified( $row['raw']['saved_total'], 'MB', 2 );
		// Other counters
		$row['processed_thumbs']        = count( $thumbnails['normal'] );
		$row['processed_thumbs_retina'] = count( $thumbnails['retina'] );
		$row['processed_total']         = $row['processed_thumbs'] + $row['processed_thumbs_retina'] + 1;

		return apply_filters( 'megaoptim_attachment_optimization_stats', $row, $this );
	}

	/**
	 * Only return true if the image is fully optimized
	 * @return bool
	 */
	public function is_processed() {

		// Check if full size normal is processed
		if ( ! isset( $this->data['status'] ) ) {
			return false;
		}

		// Check if full size retina is processed.
		$full_retina_found = $this->thumbnail_exists( 'full', true );
		if ( $full_retina_found ) {
			if ( ! isset( $this->data['retina']['status'] ) || ! isset( $this->data['retina']['thumbs'] ) ) {
				return false;
			}
		}

		// Check thumbnails
		$remaining_thumbs = $this->get_remaining_thumbnails();
		$normal_processed = isset( $remaining_thumbs['normal'] ) ? count( $remaining_thumbs['normal'] ) === 0 : 0;
		$retina_processed = isset( $remaining_thumbs['retina'] ) ? count( $remaining_thumbs['retina'] ) === 0 : 0;

		return $normal_processed && $retina_processed;
	}

	/**
	 * Size file exist?
	 *
	 * @param $size
	 *
	 * @param bool $retina
	 *
	 * @return bool
	 */
	public function thumbnail_exists( $size, $retina = false ) {
		$path = MGO_MediaLibrary::instance()->get_attachment_path( $this->get_id(), $size, $retina );

		return file_exists( $path );
	}

	/**
	 * Is specific size optimized?
	 *
	 * @param $size
	 *
	 * @param bool $retina
	 *
	 * @return bool
	 */
	public function is_size_processed( $size, $retina = false ) {
		$path = MGO_MediaLibrary::instance()->get_attachment_path( $this->get_id(), $size, $retina );
		if ( ! $retina ) {
			if ( $size === 'full' ) {
				return false !== $path && isset( $this->data['status'] )
				       && in_array( $this->data['status'], array( 0, 1 ) );
			} else {
				return false !== $path && isset( $this->data['thumbs'][ $size ]['status'] )
				       && in_array( $this->data['thumbs'][ $size ]['status'], array( 0, 1 ) );
			}
		} else {
			if ( $size === 'full' ) {
				return false !== $path && isset( $this->data['retina']['status'] )
				       && in_array( $this->data['retina']['status'], array( 0, 1 ) );
			} else {
				return false !== $path && isset( $this->data['retina']['thumbs'][ $size ]['status'] )
				       && in_array( $this->data['retina']['thumbs'][ $size ]['status'], array( 0, 1 ) );
			}
		}
	}


	/**
	 * Is image already optimized?
	 * @return bool
	 */
	public function is_already_optimized() {
		return isset( $this->data['success'] ) && $this->data['success'] == 0;
	}


	/**
	 * Returns array of the unoptimized thumbnails
	 * @return array
	 */
	public function get_remaining_thumbnails() {
		$allowed_sizes        = MGO_Settings::instance()->get( MGO_Settings::IMAGE_SIZES, array() );
		$allowed_sizes_r      = MGO_Settings::instance()->get( MGO_Settings::RETINA_IMAGE_SIZES, array() );
		$thumbnails           = array();
		$thumbnails['normal'] = array();
		$thumbnails['retina'] = array();
		if ( isset( $this->metadata['sizes'] ) && ! empty( $this->metadata['sizes'] ) ) {
			// Collect normal files
			foreach ( $this->metadata['sizes'] as $key => $size ) {
				if ( ! in_array( $key, $allowed_sizes ) || ! $this->thumbnail_exists( $key ) ) {
					continue;
				}
				// If the size isn't processed push it for processing.
				if ( ! $this->is_size_processed( $key ) ) {
					array_push( $thumbnails['normal'], $key );
				}
			}
			// Collect retinas
			if ( is_array( $allowed_sizes_r ) ) {
				foreach ( $this->metadata['sizes'] as $key => $size ) {
					if ( ! in_array( $key, $allowed_sizes_r ) || ! $this->thumbnail_exists( $key, true ) ) {
						continue;
					}
					// If the size isn't processed push it for processing.
					if ( ! $this->is_size_processed( $key, true ) ) {
						array_push( $thumbnails['retina'], $key );
					}
				}
			}
		}

		return $thumbnails;
	}

	/**
	 * Return the thumbnail count
	 * @return array
	 */
	public function get_processed_thumbnails() {
		$thumbnails           = array();
		$thumbnails['normal'] = array();
		$thumbnails['retina'] = array();
		// Collect normal thumbs
		if ( isset( $this->data['thumbs'] ) && is_array( $this->data['thumbs'] ) ) {
			foreach ( $this->data['thumbs'] as $key => $thumb ) {
				if ( isset( $thumb['status'] ) && intval( $thumb['status'] ) === 1 ) {
					$thumbnails['normal'][ $key ] = $thumb;
				}
			}
		}
		// Collect retina thumbs
		if ( isset( $this->data['retina']['thumbs'] ) && is_array( $this->data['retina']['thumbs'] ) ) {
			foreach ( $this->data['retina']['thumbs'] as $key => $thumb ) {
				if ( isset( $thumb['status'] ) && intval( $thumb['status'] ) === 1 ) {
					$thumbnails['retina'][ $key ] = $thumb;
				}
			}
		}

		return $thumbnails;
	}

	/**
	 * Returns the retina object
	 * @return array
	 */
	public function get_retina() {
		return isset( $this->data['retina'] ) ? $this->data['retina'] : array();
	}

	/**
	 * Returns the normal object
	 * @return array
	 */
	public function get_normal() {
		$data = $this->data;
		if ( isset( $data['retina'] ) ) {
			unset( $data['retina'] );
		}

		return $data;
	}

	/**
	 * Returns number of thumbnails count
	 * @return int
	 */
	public function get_total_thumbnails_count() {
		if ( isset( $this->metadata['sizes'] ) && is_array( $this->metadata['sizes'] ) ) {
			return count( $this->metadata['sizes'] );
		} else {
			return 0;
		}
	}

	/**
	 * Return the thumbnail for this attachment
	 * @return string
	 */
	public function get_thumbnail_url() {
		$image_thumb = wp_get_attachment_image_src( $this->get_id(), 'thumbnail' );
		if ( ! empty( $image_thumb ) && count( $image_thumb ) > 0 ) {
			return $image_thumb[0];
		} else {
			return megaoptim_get_placeholder();
		}
	}


	/**
	 * Destroy the megaoptim data
	 */
	public function destroy() {
		$this->data = array();
		$this->delete_data();
	}

	/**
	 * Set the metadata. In rare instances this is needed when the metadata is still not written in the db.
	 *
	 * @param $metadata
	 * @param bool $has_metadata if true the object exist before.
	 */
	public function set_metadata( $metadata, $has_metadata = false ) {
		if ( $has_metadata ) {
			$this->has_metadata = true;
		}
		$this->metadata = $metadata;
	}

	/**
	 * Returns true if the object has metadata already
	 * @return mixed
	 */
	public function has_metadata() {
		return $this->has_metadata;
	}

	/**
	 * Deletes metadata
	 * @return bool
	 */
	public function delete_metadata() {
		return delete_post_meta( self::WP_METADATA_KEY, $this->get_id() );
	}

	/**
	 * This is used in rare cases when the metadata is still not set in the database but we have the metadata available in this object
	 * Mostly used with the wp_generate_attachment_metadata hook.
	 * @return bool
	 */
	public function maybe_set_metadata() {
		if ( ! $this->has_metadata() && ! empty( $this->metadata ) ) {
			update_post_meta( $this->get_id(), self::WP_METADATA_KEY, $this->metadata );

			return true;
		}

		return false;
	}

	/**
	 * Returns the largest thumbnails dimensions
	 * @return array
	 */
	public function get_largest_thumbnail_dimensions() {
		$width  = 0;
		$height = 0;
		foreach ( $this->metadata['sizes'] as $size ) {
			if ( ! isset( $size['width'] ) || ! isset( $size['height'] ) ) {
				continue;
			}
			if ( $size['width'] > $width ) {
				$width = $size['width'];
			}
			if ( $size['height'] > $height ) {
				$height = $size['height'];
			}
		}

		return array(
			'width'  => $width,
			'height' => $height,
		);
	}

	/**
	 * Returns the type
	 * @return string
	 */
	public function getType() {
		return self::TYPE;
	}

	/**
	 * Returns the path of the attachment
	 * @return false|mixed|string
	 */
	public function get_path() {
		$key  = 'attached_file_' . $this->get_id();
		$file = megaoptim_memcache_get( $key );
		if ( ! $file ) {
			$file = get_attached_file( $this->get_id(), true );
			megaoptim_memcache_set( $key, $file );
		}

		return $file;
	}

	/**
	 * Get metadata
	 * @return array
	 */
	public function get_metadata() {
		return $this->metadata;
	}

	/**
	 * Set ID
	 *
	 * @param $ID
	 */
	public function set_id( $ID ) {
		$this->ID = $ID;
	}

	/**
	 * Returns the object manually constructed from ID, metadata, megaoptim_data
	 *
	 * @param $ID
	 * @param $metadata
	 * @param $data
	 *
	 * @return null|MGO_MediaAttachment
	 */
	public static function create( $ID, $metadata, $data ) {
		try {

			$object = new MGO_MediaAttachment();
			$object->set_id( $ID );
			if ( is_string( $metadata ) ) {
				$metadata = unserialize( $metadata );
			}
			if ( ! is_null( $data ) ) {
				$data = unserialize( $data );
			} else {
				$data = array();
			}
			$object->set_metadata( $metadata, true );
			$object->set_raw_data( $data );

			return $object;
		} catch ( MGO_Exception $e ) {
			return null;
		}
	}

	/**
	 * Refresh the model
	 */
	public function refresh() {
		$this->__load();
	}

	/**
	 * Remove the webp images when attachment is deleted.
	 */
	public function delete_webp() {
		$full_webp_path = MGO_MediaLibrary::instance()->get_attachment_path( $this->get_id(), 'full', false );
		$full_webp_path = $full_webp_path . '.webp';
		if ( ! empty( $full_webp_path ) && file_exists( $full_webp_path ) ) {
			@unlink( $full_webp_path );
		}
		if ( isset( $this->metadata['sizes'] ) && is_array( $this->metadata['sizes'] ) ) {
			foreach ( $this->metadata['sizes'] as $key => $thumb ) {
				$thumbnail_path      = MGO_MediaLibrary::instance()->get_attachment_path( $this->get_id(), $key, false );
				$thumbnail_path_webp = $thumbnail_path . '.webp';
				if ( file_exists( $thumbnail_path_webp ) ) {
					@unlink( $thumbnail_path_webp );
				}
			}
		}
	}

	/**
	 * Returns the error? Error can be set in the background task.
	 *
	 * @param $size
	 *
	 * @return bool
	 */
	public function get_error( $size ) {

		if ( $size === 'full' ) {
			return isset( $this->data['error'] ) && ! empty( $this->data['error'] ) ? $this->data['error'] : false;
		} else {
			return isset( $this->data['thumbs'][ $size ]['error'] ) && ! empty( $this->data['thumbs'][ $size ]['error'] ) ? $this->data['thumbs'][ $size ]['error'] : false;
		}
	}

	/**
	 * Is error?
	 *
	 * @param $size
	 *
	 * @return bool
	 */
	public function is_error( $size ) {
		$error = $this->get_error( $size );

		return false !== $error;
	}

	/**
	 * Removes current error
	 *
	 * @param $size
	 */
	public function remove_error( $size ) {
		if ( $size === 'full' ) {
			if ( isset( $this->data['error'] ) ) {
				unset( $this->data['error'] );
			}
		} else {
			if ( isset( $this->data['thumbs'][ $size ]['error'] ) ) {
				unset( $this->data['error'] );
			}
		}
	}


}
