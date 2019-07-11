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

function megaoptim_cache_create_key($key) {
	return MEGAOPTIM_CACHE_PREFIX . '_' . $key;
}

/**
 * Set item to the persistent cahce
 *
 * @param $key
 * @param $value
 * @param $expiration
 *
 * @return bool
 */
function megaoptim_cache_set( $key, $value, $expiration ) {
	return set_transient( megaoptim_cache_create_key( $key ), $value, $expiration );
}

/**
 * Get item from the persistent cache
 *
 * @param $key
 *
 * @return bool|mixed
 */
function megaoptim_cache_get( $key ) {
	return get_transient( megaoptim_cache_create_key( $key ) );
}

/**
 * Remove item from persistent cache
 *
 * @param $key
 *
 * @return bool
 */
function megaoptim_cache_remove( $key ) {
	return delete_transient( megaoptim_cache_create_key( $key ) );
}

/**
 * Set item to the memory cache
 *
 * @param $key
 * @param $value
 *
 * @return bool
 */
function megaoptim_memcache_set( $key, $value ) {
	return wp_cache_set( $key, $value, MEGAOPTIM_CACHE_PREFIX, 0 );
}

/**
 * Get the item from ram cache
 *
 * @param $key
 *
 * @return mixed
 */
function megaoptim_memcache_get( $key ) {
	return wp_cache_get( $key, MEGAOPTIM_CACHE_PREFIX );
}

/**
 * Remove item from the memory cache
 *
 * @param $key
 *
 * @return bool
 */
function megaoptim_memcache_remove( $key ) {
	return wp_cache_delete( $key, MEGAOPTIM_CACHE_PREFIX );
}