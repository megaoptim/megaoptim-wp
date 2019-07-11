<?php

/**
 * Returns true if table exist.
 * @param $table_name
 *
 * @return bool
 */
function megaoptim_table_exists($table_name) {
	global $wpdb;
	if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
		return false;
	} else {
		return true;
	}
}

/**
 * Check if column exists
 * @param $table_name
 * @param $column_name
 *
 * @return bool
 */
function megaoptim_column_exists($table_name, $column_name) {
	global $wpdb;
	$query = $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name='%s' AND column_name='%s'", $table_name, $column_name);
	$row = $wpdb->get_results($query);
	if(empty($row)) {
		return false;
	} else {
		return true;
	}
}

/**
 * Set database version
 * @param $version
 */
function megaoptim_set_db_version($version) {
	update_option('megaoptim_db_version', $version);
}

/**
 * Returns the db version
 * @return mixed|int
 */
function megaoptim_get_db_version() {
	return get_option('megaoptim_db_version');
}