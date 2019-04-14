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

class MGO_Rev_1001 extends MGO_Rev {

	public $id = 1001;

	public function run() {
		global $wpdb;
		$table_name = $wpdb->prefix . "megaoptim_opt";
		if(!megaoptim_column_exists($table_name, 'webp_size')) {
			$wpdb->query("ALTER TABLE $table_name ADD webp_size decimal NOT NULL DEFAULT 0");
			if(megaoptim_column_exists($table_name, 'webp_size')) {
				megaoptim_set_db_version($this->id);
			}
		}
	}

	public function is_required() {
		global $wpdb;
		$table_name = $wpdb->prefix . "megaoptim_opt";
		$current_db_version = megaoptim_get_db_version();
		if(!$current_db_version || $current_db_version < $this->id || !megaoptim_column_exists($table_name, 'webp_size')){
			return true;
		} else {
			return false;
		}

	}

}