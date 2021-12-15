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

namespace MegaOptim\Client\Http;

interface HTTP {

	const STATUS_OK = 'ok';
	const STATUS_COMPLETED = 'completed';
	const STATUS_PROCESSING = 'processing';

	const CODE_OK = 200;                    // Endpoints: /info, /register, /shrink/uuid/result
	const CODE_ACCEPTED = 202;              // Endpoints: /shrink
	const CODE_BAD_REQUEST = 400;           // Endpoints: /register, /shrink, /shrink/uuid/result
	const CODE_UNAUTHORIZED = 401;          // Endpoints: /info
	const CODE_NOT_FOUND = 404;             // Endpoints: /shrink/uuid/result, AUTH
	const CODE_CONFLICT = 409;              // Endpoints: /register ( when duplicate signup detected by ip )
	const CODE_INSUFFICIENT_TOKENS = 455;   // Endpoints: AUTH
	const CODE_SERVER_ERROR = 500;          // Endpoints: /register, /shrink/uuid/result, /shrink
	const CODE_SERVER_TIMEOUT = 504;        // Endpoints: /shrink/uuid/result
}
