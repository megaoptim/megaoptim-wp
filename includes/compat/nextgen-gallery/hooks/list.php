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

add_filter( 'ngg_manage_images_number_of_columns', '_megaoptim_ngg_manage_images_number_of_columns' );
/**
 * Add MegaOptim column
 *
 * @param  int $count Number of columns.
 *
 * @return int Incremented number of columns.
 */
function _megaoptim_ngg_manage_images_number_of_columns( $count ) {
	$count ++;
	add_filter( 'ngg_manage_images_column_' . $count . '_header', '_megaoptim_ngg_manage_images_column_header' );
	add_filter( 'ngg_manage_images_column_' . $count . '_content', '_megaoptim_ngg_manage_images_column_content', 10, 2 );

	return $count;
}

/**
 * Get the column title.
 *
 * @return string
 */
function _megaoptim_ngg_manage_images_column_header() {
	return 'MegaOptim';
}

/**
 * Get the column content.
 *
 * @param  string $output The column content.
 * @param  object $image An NGG Image object.
 *
 * @return string
 */
function _megaoptim_ngg_manage_images_column_content( $output, $image ) {

	$id         = $image->pid;
	$attachment = new MGO_NextGenAttachment( $id );

	$output = '<div class="megaoptim_nextgen_attachment" id="megaoptim-galleryimage-id-' . $id . '" data-attachmentid="' . $id . '">';
	$output .= megaoptim_get_ngg_attachment_buttons( $attachment );
	$output .= '</div>';

	return $output;
}