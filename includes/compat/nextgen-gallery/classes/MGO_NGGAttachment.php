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
 * Class MGO_NGGAttachment
 */
class MGO_NGGAttachment extends MGO_Attachment {

	private $db;
	private $megaoptim_result_id;
	private $table_name;
	const TYPE = MEGAOPTIM_TYPE_NEXTGEN_ATTACHMENT;

	/**
	 * MGO_NGGAttachment constructor.
	 *
	 * @param $id
	 */
	public function __construct( $id ) {
		global $wpdb;
		$this->db         = &$wpdb;
		$this->table_name = $this->db->prefix . "megaoptim_opt";
		parent::__construct( $id );
	}

	/**
	 * Load the saved meta
	 */
	protected function __load() {
		$query = $this->db->prepare( "SELECT P.filename, G.path , G.gid, SOPT.* FROM {$this->db->prefix}ngg_pictures P INNER JOIN {$this->db->prefix}ngg_gallery G ON G.gid=P.galleryid LEFT JOIN {$this->table_name} SOPT ON SOPT.object_id=P.pid AND SOPT.type=%s WHERE P.pid=%d", self::TYPE, $this->ID );
		$entry = $this->db->get_row( $query, ARRAY_A );

		if ( ! is_null( $entry ) ) {
			$this->megaoptim_result_id = $entry['id'];
			$path                      = $entry['path'];
			$filename                  = $entry['filename'];
			$gallery_id                = $entry['gid'];
			unset( $entry['id'] );
			unset( $entry['filename'] );
			unset( $entry['path'] );
			unset( $entry['gid'] );
			unset( $entry['object_id'] );
			$this->data               = $entry;
			$this->data['file_path']  = wp_normalize_path( megaoptim_get_wp_root_path() . $path . $filename );
			$this->data['type']       = self::TYPE;
			$this->data['object_id']  = $this->ID;
			$this->data['gallery_id'] = $gallery_id;
			//if ( is_null( $this->megaoptim_result_id ) ) {
			//$this->data['time'] = date("Y-m-d H:i:s", time());
			//}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Has "optimizaiton" result?
	 * @return null|string
	 */
	public function has_result() {
		$query  = $this->db->prepare( "SELECT COUNT(*) FROM {$this->db->prefix}ngg_pictures P INNER JOIN {$this->db->prefix}ngg_gallery G ON G.gid=P.galleryid LEFT JOIN {$this->table_name} SOPT ON SOPT.object_id=P.pid AND SOPT.type=%s WHERE P.pid=%d AND SOPT.object_id IS NOT NULL", self::TYPE, $this->ID );
		$result = $this->db->get_var( $query );

		return $result;
	}

	/**
	 * Implements the saving meta functionality
	 * @return bool
	 */
	public function save() {
		if ( isset( $this->data['file_name'] ) ) {
			unset( $this->data['file_name'] );
		}
		// Default webp_size to 0
		if ( ( isset( $this->data['webp_size'] ) && is_null( $this->data['webp_size'] ) ) || !isset($this->data['webp_size']) ) {
			$this->data['webp_size'] = 0;
		}

		if ( $this->has_result() ) {
			$result = $this->db->update( $this->table_name, $this->data, array( 'id' => $this->megaoptim_result_id ) );
			$method = 'update';
		} else {
			$result = $this->db->insert( $this->table_name, $this->data );
			$method = 'insert';
			$this->__load();
		}

		$is_success = false !== $result && $result > 0;
		if ( ! $is_success && $method === 'insert' ) {
			megaoptim_log( 'Error while saving ' . __CLASS__ . ' object: ' . $this->db->last_error );
		}

		return $is_success;
	}

	/**
	 * Clean up the temporary files and data
	 * @return mixed
	 */
	public function clean_up() {
		// TODO: Implement clean_up() method.
	}

	/**
	 * Returns true if there is backup for specific attachment
	 *
	 * @return bool
	 */
	public function has_backup() {
		return ( isset( $this->data['backup_path'] ) && file_exists( $this->data['backup_path'] ) );
	}

	/**
	 * Creates backup copy of specific attachment
	 * @return bool|string
	 */
	public function backup() {
		$attachment_path = $this->data['file_path'];
		$backup_path     = megaoptim_get_ngg_attachment_backup_path( $this->get_id(), $attachment_path );
		$dir_path        = dirname( $backup_path );
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
				megaoptim_log( sprintf( 'Backup can not be created. Path to %s does not exists or is not writable.', $dir_path ) );

				return false;
			}
		} else {
			megaoptim_log( sprintf( 'Backup can not be created. Directory %s does not exists or is not writable.', $dir_path ) );
		}

		return false;
	}

	/**
	 * Returns associative array of data
	 * @return array
	 */
	public function get_optimization_stats() {

		$row       = array();
		$row['ID'] = $this->ID;

		// Stats Formatted
		$row['optimized_size']      = $this->get_optimized_size( true );
		$row['original_size']       = $this->get_original_size( true );
		$row['saved_bytes']         = $this->get_saved_bytes( true );
		$row['saved_percent']       = $this->get_saved_percent( false, 2 );
		$row['saved_thumbs']        = 0;
		$row['saved_thumbs_retina'] = 0;
		// Stats Raw
		$row['raw']                        = array();
		$row['raw']['optimized_size']      = $this->get_optimized_size( false );
		$row['raw']['original_size']       = $this->get_original_size( false );
		$row['raw']['saved_bytes']         = $this->get_saved_bytes( false );
		$row['raw']['saved_thumbs']        = 0;
		$row['raw']['saved_thumbs_retina'] = 0;
		$row['raw']['saved_total']         = (int) $row['raw']['saved_bytes'] + (int) $row['raw']['saved_thumbs'] + (int) $row['raw']['saved_thumbs_retina'];
		$row['raw']['saved_total_mb']      = (float) megaoptim_convert_bytes_to_specified( $row['raw']['saved_total'], 'MB', 2 );
		// Other counters
		$row['processed_thumbs']        = 0;
		$row['processed_thumbs_retina'] = 0;
		$row['processed_total']         = $row['processed_thumbs'] + $row['processed_thumbs_retina'] + 1;

		return $row;
	}

	/**
	 * If this is once optimized?
	 * @return bool
	 */
	public function is_processed() {
		return isset( $this->data['success'] ) && $this->data['success'] == 1;
	}

	/**
	 * Is image already optimized?
	 * @return bool
	 */
	public function is_already_optimized() {
		return isset( $this->data['success'] ) && $this->data['success'] == 0;
	}

	/**
	 * The overall savings from all thumbnails of this specific attachment.
	 *
	 * @param bool $formatted
	 *
	 * @return int
	 */
	public function get_total_saved_bytes( $formatted = false ) {
		$bytes = isset( $this->data['saved_bytes'] ) && is_numeric( $this->data['saved_bytes'] ) ? $this->data['saved_bytes'] : 0;
		if ( $formatted ) {
			return megaoptim_human_file_size( $bytes );
		}

		return $bytes;
	}

	/**
	 * Returns the path.
	 * @return null
	 */
	public function get_path() {
		return isset( $this->data['file_path'] ) ? $this->data['file_path'] : null;
	}

	/**
	 * Updates the nextgen gallery image meta.
	 */
	public function update_ngg_meta() {
		if ( class_exists( 'C_Image_Mapper' ) ) {
			$mapper                   = C_Image_Mapper::get_instance();
			$image                    = $mapper->find( $this->get( 'object_id' ) );
			$dimensions               = getimagesize( $this->get_path() );
			$size_meta                = array( 'width' => $dimensions[0], 'height' => $dimensions[1] );
			$image->meta_data         = array_merge( $image->meta_data, $size_meta );
			$image->meta_data['full'] = $size_meta;
			$mapper->save( $image );
		}
	}

	/**
	 * Destroy the megaoptim data
	 * @return bool
	 */
	public function destroy() {
		$id     = $this->get_id();
		$result = $this->db->delete( $this->table_name, array( 'object_id' => $id, 'type' => self::TYPE ) );

		return false !== $result && $result > 0;
	}

	/**
	 * Returns the type
	 * @return string
	 */
	public function getType() {
		return self::TYPE;
	}
}