<?php
/********************************************************************
 * Copyright (C) 2019 MegaOptim (https://megaoptim.com)
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

class MGO_Rev_1000 extends MGO_Rev {

	/**
	 * The revision ID
	 * @var int
	 */
	public $id = 1000;

	/**
	 * Executes the revision
	 */
	public function run() {

		global $wpdb;
		$charset_collate                        = $wpdb->get_charset_collate();
		$table_name                             = $wpdb->prefix . "megaoptim_opt";

		if(!megaoptim_table_exists($table_name)) {
			$schema = "CREATE TABLE $table_name (
  id mediumint(9) NOT NULL AUTO_INCREMENT,
  object_id VARCHAR(128) NOT NULL,
  gallery_id VARCHAR(128) DEFAULT NULL,
  type VARCHAR(100) DEFAULT NULL,
  file_path TEXT DEFAULT NULL,
  status mediumint(9) DEFAULT 0,
  success mediumint(9) DEFAULT 0,
  original_size decimal DEFAULT 0,
  optimized_size decimal DEFAULT 0,
  saved_bytes decimal DEFAULT 0,
  saved_percent decimal DEFAULT 0,
  webp_size decimal DEFAULT 0,
  url TEXT DEFAULT NULL,
  process_id VARCHAR(100) DEFAULT NULL,
  backup_path TEXT DEFAULT NULL,
  compression VARCHAR(100) DEFAULT NULL,
  cmyktorgb smallint DEFAULT NULL,
  keep_exif smallint DEFAULT NULL,
  max_width mediumint(9) DEFAULT NULL,
  max_height mediumint(9) DEFAULT NULL,
  directory TEXT DEFAULT NULL,
  time datetime DEFAULT NULL,
  PRIMARY KEY  (id),
  CONSTRAINT megaoptim_cons_object_id UNIQUE (object_id, type)
) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $schema );
			megaoptim_set_db_version($this->id);
		}
	}

	/**
	 * Is revision required?
	 */
	public function is_required() {
		global $wpdb;
		$table_name = $wpdb->prefix . "megaoptim_opt";
		$current_db_version = megaoptim_get_db_version();
		if(!$current_db_version || $current_db_version < $this->id || !megaoptim_table_exists($table_name)){
			return true;
		} else {
			return false;
		}
	}
}
