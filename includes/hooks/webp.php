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


/**
 * WebP functionality
 */
function megaoptim_webp_init() {

	$delivery_method = MGO_Settings::instance()->get(MGO_Settings::WEBP_DELIVERY_METHOD);
	if($delivery_method === 'picture') {
		$target = MGO_Settings::instance()->get(MGO_Settings::WEBP_TARGET_TO_REPLACE);
		if($target === 'global') {
			ob_start();
			add_action('shutdown', function() {
				$final = '';
				$levels = ob_get_level();
				for ($i = 0; $i < $levels; $i++) {
					$final .= ob_get_clean();
				}
				echo apply_filters('megaoptim_final_output', $final);
			}, 0);
			add_filter('megaoptim_final_output', 'megaoptim_webp_filter_content');
		} else {
			$filters = megaoptim_webp_target_filters();
			foreach($filters as $filter) {
				add_filter( $filter, 'megaoptim_webp_filter_content', 9999, 1 );
			}
		}
	}
}
add_action('init', 'megaoptim_webp_init', 0);

/**
 * Enqueues required scripts.
 */
function megaoptim_webp_enqueue_scripts() {
	$delivery_method = MGO_Settings::instance()->get(MGO_Settings::WEBP_DELIVERY_METHOD);
	$picture_fill = MGO_Settings::instance()->get(MGO_Settings::WEBP_PICTUREFILL);
	if($delivery_method === 'picture' && $picture_fill) {
		wp_enqueue_script('megaoptim-picturefill', WP_MEGAOPTIM_ASSETS_URL . 'js/picturefill.min.js', array(), null, false);
	}
}
add_action('wp_enqueue_scripts', 'megaoptim_webp_enqueue_scripts', 100);


/**
 * @param MGO_MediaAttachment $attachment_object - The media attachment object
 * @param string $resource - Path or url to the resource
 * @param \MegaOptim\Responses\Response $response - The api response object
 * @param array $params - Array of parameters
 * @param $size - Can be: full, or other registered thumbnail size.
 */
function megaoptim_webp_attachment_optimized($attachment_object, $resource, $response, $params, $size) {

	// Bail, If this no webp requested.
	if($params['webp'] != 1) {
		return;
	}

	// Only one per request always in this plugin.
	$webp = null;
	foreach($response->getOptimizedFiles() as $file) {
		$webp = $file->getWebP();
		break;
	}
	if(is_null($webp) || ! $webp instanceof \MegaOptim\Responses\ResultWebP) {
		// Bail if no ResultWebP instance!
		return;
	} else {
		// Handle WebP file here
		// Save dir of the original attachment
		$file_dir = dirname($attachment_object->get_path());
		// Get file name of the optimized resource
		$file_name = basename($resource);
		// Assemble full path
		$full_path = $file_dir . DIRECTORY_SEPARATOR . $file_name . '.webp';
		try {
			MegaOptim\Http\Client::download( $webp->url, $full_path );
			if(file_exists($full_path)) {
				$attachment_object->set_webp($webp, $size);
				$attachment_object->save();
			}
		} catch ( Exception $e ) {
			megaoptim_log('Failed to download webp version to '. $full_path . '. Error: '.$e->getMessage());
		}
	}
}
add_action('megaoptim_attachment_optimized', 'megaoptim_webp_attachment_optimized', 15, 5);