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
 * Set item to the persistent cahce
 *
 * @param $key
 * @param $value
 * @param $expiration
 *
 * @return bool
 */
function megaoptim_cache_set( $key, $value, $expiration ) {
	return MGO_Cache::instance()->set( $key, $value, $expiration );
}

/**
 * Get item from the persistent cache
 *
 * @param $key
 *
 * @return bool|mixed
 */
function megaoptim_cache_get( $key ) {
	return MGO_Cache::instance()->get( $key );
}

/**
 * Remove item from persistent cache
 *
 * @param $key
 *
 * @return bool
 */
function megaoptim_cache_remove( $key ) {
	return MGO_Cache::instance()->remove( $key );
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
	return MGO_Cache::instance()->mem_set( $key, $value );
}

/**
 * Get the item from ram cache
 *
 * @param $key
 *
 * @return mixed
 */
function megaoptim_memcache_get( $key ) {
	return MGO_Cache::instance()->mem_get( $key );
}

/**
 * Remove item from the memory cache
 *
 * @param $key
 *
 * @return bool
 */
function megaoptim_memcache_remove( $key ) {
	return MGO_Cache::instance()->mem_remove( $key );
}