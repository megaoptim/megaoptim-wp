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
 * Created by PhpStorm.
 * User: dg
 * Date: 8/28/2018
 * Time: 5:34 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access is not allowed.' );
}

class MGO_LocalFileAttachment extends MGO_Attachment {
	const TYPE = MEGAOPTIM_TYPE_FILE_ATTACHMENT;
	protected $path;
	protected $db;
	protected $table_name;

	public function __construct( $id ) {
		$this->path = $id; // Path initially
		global $wpdb;
		$this->db         = &$wpdb;
		$this->table_name = $this->db->prefix . "megaoptim_opt";
		$this->ID         = md5( $id );
		parent::__construct( $id );
	}

	/**
	 * Load the saved meta
	 */
	protected function __load() {
		$query = $this->db->prepare( "SELECT * FROM {$this->table_name} MGOPT WHERE MGOPT.object_id=%s AND MGOPT.type=%s", $this->ID, self::TYPE );
		$entry = $this->db->get_row( $query, ARRAY_A );
		if ( ! is_null( $entry ) ) {
			$this->data = $entry;
		} else {
			$this->data['object_id'] = $this->ID;
			$this->data['type']      = self::TYPE;
			$this->data['file_path'] = $this->path;
			$this->data['status']    = 0;
		}

		return true;
	}

	/**
	 * Has "optimizaiton" result?
	 * @return null|string
	 */
	public function has_result() {
		$query  = $this->db->prepare( "SELECT COUNT(*) FROM {$this->table_name} MGOPT WHERE MGOPT.object_id=%s AND MGOPT.type=%s", $this->ID, self::TYPE );
		$result = $this->db->get_var( $query );

		return $result;
	}

	/**
	 * Implements the saving meta functionality
	 * @return bool
	 */
	public function save() {
		if ( isset( $this->data['webp_size'] ) && is_null( $this->data['webp_size'] ) ) {
			$this->data['webp_size'] = 0;
		}
		if ( $this->has_result() ) {
			$result = $this->db->update( $this->table_name, megaoptim_array_except( $this->data, array(
				'id',
				'object_id',
				'file_name',
			) ), array( 'id' => $this->data['id'] ) );
		} else {
			$result = $this->db->insert( $this->table_name, megaoptim_array_except( $this->data, array( 'file_name' ) ), array(
				'%s',
				'%s',
				'%s',
				'%d'
			) );
			$this->__load();
		}
		$is_success = false !== $result && $result > 0;
		if ( ! $is_success ) {
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
	 *
	 * @return bool
	 */
	public function backup() {
		$attachment_path = $this->data['file_path'];
		$backup_path     = megaoptim_get_files_attachment_backup_path( $this->get_id(), $attachment_path );
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
				megaoptim_log( sprintf( __( 'Backup can not be created. Path to %s does not exists or is not writable.', 'megaoptim' ), $dir_path ) );

				return false;
			}
		} else {
			megaoptim_log( sprintf( __( 'Backup can not be created. Directory %s does not exists or is not writable.', 'megaoptim' ), $dir_path ) );
		}

		return false;
	}

	/**
	 * Returns associative array of data
	 * @return array
	 */
	public function get_optimization_stats() {
		$row                     = array();
		$row['ID']               = $this->ID;
		$row['optimized_size']   = megaoptim_human_file_size( $this->get_original_size() - $this->get_saved_bytes() );
		$row['original_size']    = megaoptim_human_file_size( $this->get_original_size() );
		$row['saved_bytes']      = megaoptim_human_file_size( $this->get_saved_bytes() );
		$row['saved_percent']    = megaoptim_round( $this->get_saved_percent(), 2 );
		$row['optimized_thumbs'] = 0;

		return $row;
	}

	/**
	 * If this is once optimized?
	 * @return bool
	 */
	public function is_optimized() {
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
		return isset( $this->data['saved_bytes'] ) && is_numeric( $this->data['saved_bytes'] ) ? $this->data['saved_bytes'] : 0;
	}

	/**
	 * Returns the path.
	 * @return null
	 */
	public function get_path() {
		return isset( $this->data['file_path'] ) ? $this->data['file_path'] : null;
	}

	/**
	 * Destroy the megaoptim data
	 * @return bool
	 */
	public function destroy() {
		$id     = $this->ID;
		$result = $this->db->delete( $this->table_name, array( 'id' => $id ) );

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