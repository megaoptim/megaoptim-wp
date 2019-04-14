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

	const TYPE = 'wp';

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
		$attachment_path = get_attached_file( $this->get_id() );
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
				megaoptim_log( sprintf( __( 'Backup can not be created. Path to %s does not exists or is not writable.', 'megaoptim' ), $dir_path ) );

				return false;
			}
		} else {
			megaoptim_log( sprintf( __( 'Backup can not be created. Directory %s does not exists or is not writable.', 'megaoptim' ), $dir_path ) );
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
		$attachment_path = get_attached_file( $this->get_id() );
		$backup_path     = $this->get_backup_path();
		if ( empty( $backup_path ) || ! file_exists( $backup_path ) ) {
			throw new MGO_Exception( "Backup path is empty!" );
		}
		if ( ! is_writable( dirname( $attachment_path ) ) ) {
			throw new MGO_Exception( "Directory is not writable!" );
		}
		if ( @rename( $backup_path, $attachment_path ) ) {
			$meta_data      = megaoptim_regenerate_thumbnails( $this->get_id(), $attachment_path );
			$this->metadata = $meta_data;
			$this->destroy();
			do_action( 'megaoptim_after_restore_attachment', $this );
		}

		return true;
	}

	/**
	 * The overall savings from all thumbnails of this specific attachment.
	 *
	 * @param bool $formatted
	 * @param bool $include_thumbnails
	 *
	 * @return float|int|string
	 */
	public function get_total_saved_bytes( $formatted = false, $include_thumbnails = false ) {
		$bytes = $this->get_saved_bytes();
		if ( $include_thumbnails ) {
			$bytes += $this->get_total_saved_bytes_thumbnails( false );
		}
		$bytes = apply_filters( 'megaoptim_ml_total_saved_bytes', $bytes, $this );
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
	 * @return float|int|string
	 */
	public function get_total_saved_bytes_thumbnails( $formatted = false ) {
		$bytes = 0;
		if ( isset( $this->data['thumbs'] ) && is_array( $this->data['thumbs'] ) ) {
			foreach ( $this->data['thumbs'] as $thumb ) {
				if ( $thumb['saved_bytes'] > 0 ) {
					$bytes += (float) $thumb['saved_bytes'];
				}
			}
		}

		$bytes = apply_filters( 'megaoptim_ml_total_saved_bytes_thumbnails', $bytes, $this );

		if ( $formatted ) {
			$bytes = megaoptim_human_file_size( $bytes );
		}

		return $bytes;
	}

	/**
	 * Set the attachment data
	 *
	 * @param \MegaOptim\Responses\Response $response
	 * @param $params
	 */
	public function set_data( \MegaOptim\Responses\Response $response, $params ) {
		parent::set_data( $response, $params );
	}

	/**
	 * Sets response for specific thumbnail
	 *
	 * @param $size
	 * @param \MegaOptim\Responses\Response $response
	 * @param $params
	 */
	public function set_thumbnail_data( $size, \MegaOptim\Responses\Response $response, $params ) {
		$thumbnail_data = megaoptim_generate_thumbnail_data( $response, $params );
		if ( ! isset( $this->data['thumbs'] ) ) {
			$this->data['thumbs'] = array();
		}
		if ( ! empty( $thumbnail_data ) ) {
			$this->data['thumbs'][ $size ] = $thumbnail_data;
		}
	}


	/**
	 * Returns associative array of data
	 * @return array
	 */
	public function get_optimization_stats() {

		$optimized_thumbnails = $this->get_optimized_thumbnails();

		$row                            = array();
		$row['ID']                      = $this->ID;
		$row['optimized_size']          = megaoptim_human_file_size( $this->get_original_size() - $this->get_saved_bytes() );
		$row['original_size']           = megaoptim_human_file_size( $this->get_original_size() );
		$row['saved_bytes']             = megaoptim_human_file_size( $this->get_saved_bytes() );
		$row['saved_percent']           = megaoptim_round( $this->get_saved_percent(), 2 );
		$row['optimized_thumbs']        = count( $optimized_thumbnails['normal'] );
		$row['optimized_thumbs_retina'] = count( $optimized_thumbnails['retina'] );
		$row['saved_thumbs']            = $this->get_total_saved_bytes_thumbnails( true );

		return apply_filters( 'megaoptim_attachment_optimization_stats', $row, $this );
	}

	/**
	 * Only return true if the image is fully optimized
	 * @return bool
	 */
	public function is_optimized() {
		if ( $this->get_optimized_status() !== 1 ) {
			return false;
		}
		$unoptimized_thumbnails = $this->get_unoptimized_thumbnails();
		$is_optimized           = isset( $unoptimized_thumbnails['normal'] ) ? count( $unoptimized_thumbnails['normal'] ) === 0 : 0;

		return apply_filters( 'megaoptim_ml_attachmed_is_optimized', $is_optimized, $this );
	}

	/**
	 * Size file exist?
	 *
	 * @param $size
	 *
	 * @return bool
	 */
	public function thumbnail_exists( $size ) {
		$path = MGO_MediaLibrary::instance()->get_attachment_path( $this->get_id(), $size, false );

		return file_exists( $path );
	}

	/**
	 * Is thumbnail optimized ?
	 *
	 * @param $size
	 *
	 * @return bool
	 */
	public function is_thumbnail_optimized( $size ) {
		if ( ! isset( $this->data['thumbs'][ $size ] ) || ! isset( $this->data['thumbs'][ $size ]['status'] ) || $this->data['thumbs'][ $size ]['status'] != 1 ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Is specific size optimized?
	 *
	 * @param $size
	 *
	 * @return bool
	 */
	public function is_size_optimized( $size ) {
		$path = MGO_MediaLibrary::instance()->get_attachment_path( $this->get_id(), $size, false );
		if ( $size === 'full' ) {
			return false !== $path && isset( $this->data['status'] ) && ! empty( $this->data['status'] );
		} else {
			return false !== $path && isset( $this->data['thumbs'][ $size ]['status'] ) && ! empty( $this->data['thumbs'][ $size ]['status'] );
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
	public function get_unoptimized_thumbnails() {
		$allowed_sizes        = MGO_Settings::instance()->get( MGO_Settings::IMAGE_SIZES );
		$thumbnails           = array();
		$thumbnails['normal'] = array();
		$thumbnails['retina'] = array();
		if ( isset( $this->metadata['sizes'] ) && ! empty( $this->metadata['sizes'] ) ) {
			if ( is_array( $allowed_sizes ) ) {
				foreach ( $this->metadata['sizes'] as $key => $size ) {
					if ( ! in_array( $key, $allowed_sizes ) ) {
						continue;
					}
					// Iff the size doesn't exist, or exist but the status doesn't exist, or size and status exist but they aren't
					if ( $this->thumbnail_exists( $key ) && ! $this->is_thumbnail_optimized( $key ) ) {
						array_push( $thumbnails['normal'], $key );
					}
				}
			}
		}

		return apply_filters( 'megaoptim_ml_attachment_unoptimized_thumbnails', $thumbnails, $this );
	}

	/**
	 * Return the thumbnail count
	 * @return array
	 */
	public function get_optimized_thumbnails() {
		$thumbnails           = array();
		$thumbnails['normal'] = array();
		$thumbnails['retina'] = array();
		if ( isset( $this->data['thumbs'] ) && is_array( $this->data['thumbs'] ) ) {
			foreach ( $this->data['thumbs'] as $thumb ) {
				if ( isset( $thumb['status'] ) && intval( $thumb['status'] ) === 1 ) {
					array_push( $thumbnails['normal'], $thumb );
				}
			}
		}

		return apply_filters( 'megaoptim_ml_attachment_optimized_thumbnails', $thumbnails, $this );
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
			$file = get_attached_file( $this->get_id() );
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
	 * Return WebP
	 *
	 * @param string $size
	 *
	 * @return \MegaOptim\Responses\ResultWebP|null
	 */
	public function get_webp( $size = 'full' ) {
		$fields = array( 'url', 'optimized_size', 'saved_bytes', 'saved_percent' );
		if ( $size === 'full' ) {
			$webp = isset( $this->metadata['webp'] ) ? $this->metadata['webp'] : null;
		} else {
			$webp = isset( $this->metadata['thumbs'][ $size ]['webp'] ) ? $this->metadata['thumbs'][ $size ]['webp'] : null;
		}
		if ( is_array( $webp ) ) {
			$result = new \MegaOptim\Responses\ResultWebP();
			foreach ( $fields as $field ) {
				if ( isset( $webp[ $field ] ) ) {
					$result->$field = $webp[ $field ];
				}
			}

			return $result;
		} else {
			return null;
		}

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
		if ( isset($this->metadata['sizes']) && is_array( $this->metadata['sizes'] ) ) {
			foreach ( $this->metadata['sizes'] as $key => $thumb ) {
				$thumbnail_path      = MGO_MediaLibrary::instance()->get_attachment_path( $this->get_id(), $key, false );
				$thumbnail_path_webp = $thumbnail_path . '.webp';
				if ( file_exists( $thumbnail_path_webp ) ) {
					@unlink( $thumbnail_path_webp );
				}
			}
		}
	}
}