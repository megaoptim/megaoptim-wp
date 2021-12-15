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

namespace MegaOptim\Client\Services;

use MegaOptim\Client\Http\BaseClient;
use MegaOptim\Client\Responses\Profile;
use MegaOptim\Client\Responses\Response;

class OptimizerService {

	/**
	 * The base client
	 * @var BaseClient
	 */
	protected $client;

	/**
	 * OptimizerService constructor.
	 *
	 * @param BaseClient $client
	 */
	public function __construct( BaseClient $client ) {
		$this->client = $client;
	}

	/**
	 * @param $data
	 * @param $files
	 *
	 * @return Response
	 * @throws \Exception
	 */
	public function send( $data, $files ) {
		$url      = BaseClient::get_endpoint( BaseClient::ENDPOINT_OPTIMIZE );
		$response = $this->client->post( $url, $data, $files );

		return new Response( $response );
	}

	/**
	 * Retruns a result
	 *
	 * @param $request_id
	 * @param int $max_wait_seconds
	 *
	 * @return Response
	 * @throws \Exception
	 */
	public function get_result( $request_id, $max_wait_seconds = 85 ) {
		$url      = BaseClient::get_endpoint( BaseClient::ENDPOINT_OPTIMIZE ) . '/' . $request_id . '/result?timeout=' . $max_wait_seconds;
		$response = $this->client->post( $url, array() );

		return new Response( $response );
	}

	/**
	 * Returns the profile by the current api key.
	 * @return Profile
	 * @throws \Exception
	 */
	public function get_user_info() {
		$url      = BaseClient::get_endpoint( BaseClient::ENDPOINT_PROFILE );
		$response = $this->client->get( $url );

		return new Profile( $response );
	}

}
