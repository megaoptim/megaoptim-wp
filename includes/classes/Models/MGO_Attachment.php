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

abstract class MGO_Attachment {

	const TYPE = '';
	protected $ID = null;
	protected $data;

	public function __construct( $id ) {

		if ( is_null( $this->ID ) ) {
			$this->ID = $id;
		}
		if ( ! $this->__load() ) {
			$this->data = array();
		}
	}

	/**
	 * Load the saved meta
	 */
	abstract protected function __load();


	/**
	 * Implements the saving meta functionality
	 */
	abstract public function save();

	/**
	 * Clean up the temporary files and data
	 * @return mixed
	 */
	abstract public function clean_up();

	/**
	 * Returns true if there is backup for specific attachment
	 *
	 * @return bool
	 */
	abstract public function has_backup();

	/**
	 * Creates backup copy of specific attachment
	 *
	 * @return bool
	 * @throws MGO_Exception
	 */
	abstract public function backup();

	/**
	 * Restores a backed up file. Returns false if the target file or directory is NOT writable
	 *
	 * @return bool
	 * @throws MGO_Exception
	 */
	/**
	 * Restores a backed up file. Returns false if the target file or directory is NOT writable
	 *
	 * @return bool
	 * @throws MGO_Exception
	 */
	public function restore() {
		if ( $this->has_backup() ) {
			if ( isset( $this->data['file_path'] ) ) {
				if ( @copy( $this->data['backup_path'], $this->data['file_path'] ) ) {
					unlink( $this->data['backup_path'] );
					$this->data['backup_path'] = null;
					if ( $this->destroy() ) {
						$this->data = array();
					}
					do_action('megaoptim_after_restore_attachment', $this);
					return true;
				} else {
					$last = error_get_last();
					if ( ! is_null( $last ) ) {
						throw new MGO_Exception( $last['message'] );
					} else {
						throw new MGO_Exception( __( 'Error while trying to restore attachment.', 'megaoptim' ) );
					}

				}
			}

		} else {
			throw new MGO_Exception( 'No backup available for this attachment.' );
		}

		return false;
	}

	/**
	 * Deletes backup for the attachment
	 * @return bool
	 */
	public function delete_backup() {
		$backup_path = $this->get( 'backup_path' );
		if ( ! is_null( $backup_path ) && file_exists( $backup_path ) && is_writable( $backup_path ) ) {
			if ( unlink( $backup_path ) ) {
				$this->set( 'backup_path', null );
				$this->save();
			}
		}

		return false;
	}

	/**
	 * If this is once optimized?
	 * @return bool
	 */
	abstract public function is_optimized();

	/**
	 * Is image already optimized?
	 * @return bool
	 */
	abstract public function is_already_optimized();

	/**
	 * Returns the attachment ID
	 * @return mixed
	 */
	public function get_id() {
		return $this->ID;
	}

	/**
	 * Returns optimization time
	 * @return mixed
	 */
	public function get_time() {
		return isset( $this->data['time'] ) ? $this->data['time'] : false;
	}

	/**
	 * Returns the backup path
	 * @return string
	 */
	public function get_backup_path() {
		return isset( $this->data['backup_path'] ) ? $this->data['backup_path'] : false;
	}

	/**
	 * Returns the original size before optimization
	 *
	 * @param bool $formatted
	 *
	 * @return int|string
	 */
	public function get_original_size( $formatted = false ) {

		$bytes = isset( $this->data['original_size'] )
			? $this->data['original_size'] : 0;

		if ( $formatted ) {
			return megaoptim_human_file_size( $bytes );
		}

		return $bytes;
	}

	/**
	 * Returns the new size
	 *
	 * @param bool $formatted
	 *
	 * @return int|string
	 */
	public function get_optimized_size( $formatted = false ) {

		$bytes = isset( $this->data['optimized_size'] )
			? $this->data['optimized_size'] : 0;

		if ( $formatted ) {
			return megaoptim_human_file_size( $bytes );
		}

		return $bytes;
	}

	/**
	 * Returns the saved bytes
	 *
	 * @param bool $formatted
	 *
	 * @return int|string
	 */
	public function get_saved_bytes( $formatted = false ) {

		$bytes = isset( $this->data['saved_bytes'] ) ? $this->data['saved_bytes'] : 0;

		if ( $formatted ) {
			return megaoptim_human_file_size( $bytes );
		}

		return $bytes;
	}

	/**
	 * Returns the saved percent
	 *
	 * @param bool $formatted
	 *
	 * @return int|string
	 */
	public function get_saved_percent( $formatted = false ) {

		$percent = isset( $this->data['saved_percent'] )
			? $this->data['saved_percent'] : 0;
		if ( $formatted ) {
			$percent = megaoptim_round( $percent, 2 ) . '%';
		}

		return $percent;
	}

	/**
	 * The overall savings from all thumbnails of this specific attachment.
	 *
	 * @param bool $formatted
	 *
	 * @return int
	 */
	public abstract function get_total_saved_bytes( $formatted = false );

	/**
	 * Returns the raw data array.
	 * @return array
	 */
	public function get_raw_data() {
		return $this->data;
	}

	/**
	 * Is optimized?
	 * @return int|mixed
	 */
	public function get_optimized_status() {
		return isset( $this->data['status'] ) ? $this->data['status'] : 0;
	}

	/**
	 * Is optimized?
	 *
	 * @param int $status
	 */
	public function set_optimized_status( $status ) {
		$this->data['status'] = (int) $status;
	}


	/**
	 * The backup path
	 *
	 * @param string $path
	 */
	public function set_backup_path( $path ) {
		$this->data['backup_path'] = $path;
	}

	/**
	 * Original Size
	 *
	 * @param int $size
	 */
	public function set_original_size( $size ) {
		$this->data['original_size'] = $size;
	}

	/**
	 * Saved Size
	 *
	 * @param int $size
	 */
	public function set_optimized_size( $size ) {
		$this->data['saved_size'] = $size;
	}

	/**
	 * Saved bytes
	 *
	 * @param int $bytes
	 */
	public function set_saved_bytes( $bytes ) {
		$this->data['saved_bytes'] = $bytes;
	}

	/**
	 * Saved Percentage
	 *
	 * @param float $saved_percent
	 */
	public function set_saved_percent( $saved_percent ) {
		$this->data['saved_percent'] = $saved_percent;
	}

	/**
	 * Optimizaiton timestamp
	 *
	 * @param $time
	 */
	public function set_optimization_time( $time ) {
		$this->data['time'] = $time;
	}

	/**
	 * Set the process id
	 *
	 * @param $process_id
	 */
	public function set_process_id( $process_id ) {
		$this->data['process_id'] = $process_id;
	}

	/**
	 * Set the query parameters
	 *
	 * @param $params
	 */
	public function set_query_params( $params ) {
		foreach ( megaoptim_get_allowed_query_parameters() as $parameter ) {
			if ( isset( $params[ $parameter ] ) ) {
				$this->data[ $parameter ] = $params[ $parameter ];
			}
		}
	}

	/**
	 * Set key value
	 *
	 * @param $key
	 * @param $value
	 */
	public function set( $key, $value ) {
		$this->data[ $key ] = $value;
	}

	/**
	 * Returns specific data field
	 *
	 * @param $key
	 *
	 * @return mixed|null
	 */
	public function get( $key ) {
		return isset( $this->data[ $key ] ) ? $this->data[ $key ] : null;
	}

	/**
	 * Set metadata non-Thumb file
	 *
	 * @param \MegaOptim\Responses\Response $response
	 * @param $params
	 */
	public function set_data( \MegaOptim\Responses\Response $response, $params ) {
		$this->set_optimized_status( (int) $response->isSuccessful() );
		$files = $response->getOptimizedFiles();
		if ( ! empty( $files ) ) {
			$data = json_decode( $response->getRawResponse(), true );
			if ( ! empty( $data['result'] ) ) {
				foreach ( $data['result'] as $optimization ) {
					foreach ( $optimization as $key => $value ) {
						if ( ! in_array( $key, self::excluded_params() ) ) {
							if($key === 'webp') {
								if(isset($value['optimized_size'])) {
									$this->data['webp_size'] = $value['optimized_size'];
								}
							} else {
								$this->data[ $key ] = $value;
							}
						}
					}
					// Only one optimization per request!
					break;
				}
			}
			$this->set_process_id( $response->getProcessId() );
			$this->set_optimization_time( date( 'Y-m-d H:i:s', time() ) );
			$this->set_query_params( $params );
		}
	}

	/**
	 * Set raw data.
	 *
	 * @param $data
	 */
	public function set_raw_data( $data ) {
		$this->data = $data;
	}

	abstract public function getType();

	/**
	 * Destroy the megaoptim data
	 * @return bool
	 */
	abstract public function destroy();

	/**
	 * Returns the key for the current lock
	 * @return string
	 */
	protected function get_lock_key() {
		$lock = 'lock_' . $this->getType() . '_' . $this->get_id();

		return $lock;
	}

	/**
	 * Lock the attachment, no further optimization should happen until unlocked!
	 * @return mixed
	 */
	public function lock() {
		$lock = $this->get_lock_key();

		return megaoptim_cache_set( $lock, time(), MEGAOPTIM_TEN_MINUTES_IN_SECONDS );
	}

	/**
	 * Unlock the attachment
	 * @return mixed
	 */
	public function unlock() {
		$lock = $this->get_lock_key();

		return megaoptim_cache_remove( $lock );
	}

	/**
	 * Check if attachment is locked
	 * @return mixed
	 */
	public function is_locked() {
		$lock = $this->get_lock();

		return $lock != false;
	}

	/**
	 * Return the lock ( time() value when this attachment was locked )
	 * @return mixed
	 */
	public function get_lock() {
		$lock = $this->get_lock_key();

		return megaoptim_cache_get( $lock );
	}

	/**
	 * Returns array of excluded params
	 * @return array
	 */
	public static function excluded_params() {
		return array( 'http_user', 'http_pass' );
	}

	/**
	 * Set WebP data if exist.
	 * @param $data
	 * @param string $size
	 */
	public function set_webp($data, $size = 'full') {
		// TODO: Remove later.
	}


	public function get_webp_path() {
		return isset($this->data['file_path']) ? $this->data['file_path'] . '.webp' : false;
	}

	/**
	 * Removes WebP file.
	 */
	public function delete_webp() {
		$webp_path = $this->get_webp_path();
		if(false !== $webp_path && file_exists($webp_path)) {
			@unlink($webp_path);
		}
	}
}