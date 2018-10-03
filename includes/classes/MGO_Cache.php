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
 * Class MGO_Cache
 */
class MGO_Cache extends MGO_BaseObject {

	/**
	 * The cache group
	 */
	const PREFIX = 'megaoptim';

	/**
	 * Set item to the persistent cahce
	 *
	 * @param $key
	 * @param $value
	 * @param $expiration
	 *
	 * @return bool
	 */
	public function set( $key, $value, $expiration ) {
		return set_transient( $this->get_cache_key( $key ), $value, $expiration );
	}

	/**
	 * Get item from the persistent cache
	 *
	 * @param $key
	 *
	 * @return bool|mixed
	 */
	public function get( $key ) {
		return get_transient( $this->get_cache_key( $key ) );
	}

	/**
	 * Remove item from persistent cache
	 *
	 * @param $key
	 *
	 * @return bool
	 */
	public function remove( $key ) {
		return delete_transient( $this->get_cache_key( $key ) );
	}


	/**
	 * Set item to the memory cache
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return bool
	 */
	public function mem_set( $key, $value ) {
		return wp_cache_set( $key, $value, self::PREFIX, 0 );
	}

	/**
	 * Get item from the memory cache
	 *
	 * @param $key
	 *
	 * @return bool|mixed
	 */
	public function mem_get( $key ) {
		return wp_cache_get( $key, self::PREFIX );
	}

	/**
	 * Remove item from the memory cache
	 *
	 * @param $key
	 *
	 * @return bool
	 */
	public function mem_remove( $key ) {
		return wp_cache_delete( $key, self::PREFIX );
	}

	/**
	 * Returns the cache key prefixed.
	 *
	 * @param $key
	 *
	 * @return string
	 */
	private function get_cache_key( $key ) {
		return self::PREFIX . '_' . $key;
	}
}