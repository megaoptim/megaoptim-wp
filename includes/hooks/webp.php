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

add_filter( 'the_content', 'megaoptim_webp_filter_content', 9999, 1 );
add_filter( 'the_excerpt', 'megaoptim_webp_filter_content', 9999, 1 );
add_filter( 'post_thumbnail_html', 'megaoptim_webp_filter_content', 9999, 1 );
/**
 * The actual filter attached to the_content, the_excerpt and post_thumbnail_html
 * @param $content
 * @return string|string[]|null
 */
function megaoptim_webp_filter_content( $content ) {
	if ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
		return $content;
	}
	return megaoptim_webp_convert_text($content);
}