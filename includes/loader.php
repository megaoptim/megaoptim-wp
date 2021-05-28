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

// Load the constants configuration
require_once 'constants.php';

// Load the MegaOptim Bootstrapping functionality
require_once(
	WP_MEGAOPTIM_INC_PATH .
	DIRECTORY_SEPARATOR .
	'functions' .
	DIRECTORY_SEPARATOR .
	'bootstrap.php'
);

// Check if the PHP version requirements are met.
if(!megaoptim_is_php_version_compatible()) {
	add_action('admin_notices', 'megaoptim_update_nag');
	return;
}


// Load the MegaOptim Library
megaoptim_prepare_optimizer();

$includes = array(

	//Load Internal Functions
	'functions/compat.php',
	'functions/database.php',
	'functions/helpers.php',
	'functions/admin.php',
	'functions/cache.php',

	// Dependencies
	'libraries/wp-background-processing/wp-async-request.php',
	'libraries/wp-background-processing/wp-background-process.php',

	// Classes
	'classes/MGO_BaseObject.php',
	'classes/MGO_ResultBag.php',
	'classes/MGO_Upgrader.php',
	'classes/MGO_ImageFilter.php',
	'classes/Models/MGO_Attachment.php',
	'classes/Adapters/MGO_Library.php',
	'classes/Exceptions/MGO_Exception.php',
	'classes/Exceptions/MGO_Attachment_Already_Optimized_Exception.php',
	'classes/Exceptions/MGO_Attachment_Locked_Exception.php',
	'classes/Exceptions/MGO_API_Response_Exception.php',
	'classes/MGO_File.php',
	'classes/MGO_Stats.php',
	'classes/MGO_Profile.php',
	'classes/MGO_Settings.php',
	'classes/MGO_Debug.php',
	'classes/Models/MGO_MediaAttachment.php',
	'classes/Adapters/MGO_MediaLibrary.php',
	'classes/Models/MGO_FileAttachment.php',
	'classes/Adapters/MGO_FileLibrary.php',
	'compat/nextgen-gallery/classes/MGO_NGGAttachment.php' => megaoptim_is_nextgen_active(),
	'compat/nextgen-gallery/classes/MGO_NGGLibrary.php'    => megaoptim_is_nextgen_active(),
	'classes/MGO_Ajax.php',
	'classes/MGO_Admin_UI.php'                             => is_admin(),
	'classes/MGO_Admin_Notices.php'                        => is_admin(),

	// Jobs
	'classes/Jobs/MGO_Background_Process.php',
	'classes/Jobs/MGO_MediaLibrary_Process.php',
	'compat/nextgen-gallery/classes/MGO_NGGProcess.php'    => megaoptim_is_nextgen_active(),

	// CLI
	'classes/MGO_CLI.php' => class_exists('WP_CLI'),


	//Load Internal Hooks
	'hooks/general.php',
	'hooks/internal.php',
	'hooks/attachments.php',
	'hooks/notices.php',

	// Webp Support
	'functions/webp.php',
	'hooks/webp.php',

	// Compatibility with third party.

	// -- CloudFlare
	'compat/cloudflare/classes/MGO_CloudFlare.php',
	'compat/cloudflare/hooks.php',

	// -- Hosting platforms
	'compat/hosting/general/hooks.php',

	// -- MediaPress
	'compat/mediapress/hooks/attachments.php',
);


// -- WP Offload Media
if(megaoptim_is_as3cf_active()) {
	$includes = array_merge( $includes, array(
		'compat/wp-offload-media/MGO_As3cf_Util.php',
		'compat/wp-offload-media/MGO_As3cf.php',
	) );
}


// -- WP Retina 2x
if ( megaoptim_is_wr2x_active() ) {
	$includes = array_merge( $includes, array(
		'compat/wp-retina-2x/hooks/general.php',
	) );
}

// -- Nextgen Library
if ( megaoptim_is_nextgen_active() ) {
	$includes = array_merge( $includes, array(
		'compat/nextgen-gallery/helpers.php',
		'compat/nextgen-gallery/hooks/ajax.php',
		'compat/nextgen-gallery/hooks/attachments.php',
		'compat/nextgen-gallery/hooks/general.php',
		'compat/nextgen-gallery/hooks/list.php',
	) );
}

// -- WPEngine
if ( megaoptim_is_wpengine() ) {
	$includes = array_merge( $includes, array(
		'compat/hosting/wpengine/hooks.php'
	) );
}

megaoptim_include_files( $includes );

// Load
MGO_Ajax::instance();
MGO_MediaLibrary::instance();
if ( megaoptim_is_nextgen_active() ) {
	MGO_NGGLibrary::instance();
}

