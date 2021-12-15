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

require_once( 'src/Interfaces/IFile.php' );

require_once( 'src/Tools/PATH.php' );
require_once( 'src/Tools/URL.php' );
require_once( 'src/Tools/FileSystem.php' );

require_once( 'src/Http/Multipart/Util.php' );
require_once( 'src/Http/Multipart/Multipart.php' );
require_once( 'src/Http/Multipart/MultipartAlternative.php' );
require_once( 'src/Http/Multipart/MultipartFormData.php' );
require_once( 'src/Http/Multipart/MultipartMixed.php' );
require_once( 'src/Http/Multipart/MultipartRelated.php' );

require_once( 'src/Http/HTTP.php' );
require_once( 'src/Http/BaseClient.php' );
require_once( 'src/Http/CurlClient.php' );
require_once( 'src/Http/WPClient.php' );

require_once( 'src/Responses/ResultWebP.php' );
require_once( 'src/Responses/Result.php' );
require_once( 'src/Responses/Response.php' );
require_once( 'src/Responses/Profile.php' );

require_once( 'src/Services/OptimizerService.php' );

require_once( 'src/Optimizer.php' );
