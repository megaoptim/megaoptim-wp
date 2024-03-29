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
            add_action('init', 'megaoptim_webp_start_output_buffer');
		} else {
			$filters = megaoptim_webp_target_filters();
			foreach($filters as $filter) {
				add_filter( $filter, 'megaoptim_webp_filter_content', PHP_INT_MAX, 1 );
			}
		}
	}
}
add_action('plugins_loaded', 'megaoptim_webp_init', 5);

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