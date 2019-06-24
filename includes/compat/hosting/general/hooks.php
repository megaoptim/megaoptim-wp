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

if ( ! function_exists( '_megaoptim_http_request_args' ) ) {
	/**
	 * Adjust the internal calls http request parameters
	 *
	 * @param $r
	 * @param $url
	 *
	 * @return mixed
	 */
	function _megaoptim_http_request_args( $r, $url ) {
		// Check if this is internal url!
		if ( ! megaoptim_is_internal_url( $url ) || ! megaoptim_contains( $url, 'admin-ajax.php' ) ) {
			return $r;
		}
		$user_agent = apply_filters( 'megaoptim_user_agent_for_internal_requests', megaoptim_internal_async_task_user_agent() );
		if ( ! empty( $user_agent ) ) {
			$r['user-agent'] = $user_agent;
		}

		return $r;
	}

	add_filter( 'http_request_args', '_megaoptim_http_request_args', 10, 2 );
}
