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

class MGO_DB_Init {

	private $table_definitions;

	public function __construct() {
		$this->define();
	}

	private function define() {
		global $wpdb;
		$this->table_definitions                = array();
		$charset_collate                        = $wpdb->get_charset_collate();
		$table_name                             = $wpdb->prefix . "megaoptim_opt";
		$this->table_definitions[ $table_name ] = "CREATE TABLE $table_name (
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

	}

	/**
	 * Install the required database tables.
	 */
	public function run() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		foreach ( $this->table_definitions as $table_name => $table_definition ) {
			if ( ! $this->table_exist( $table_name ) ) {
				dbDelta( $table_definition );
			}
		}
		update_option( 'megaoptim_db_version', WP_MEGAOPTIM_DB_VER );
	}

	/**
	 * Check if table exist
	 *
	 * @param $table_name
	 *
	 * @return bool
	 */
	public function table_exist( $table_name ) {
		global $wpdb;
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
			return false;
		} else {
			return true;
		}
	}

}