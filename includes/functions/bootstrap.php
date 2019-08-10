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

/**
 * Include files
 *
 * @param $files
 */
function megaoptim_include_files( $files ) {
	if ( count( $files ) > 0 ) {
		foreach ( $files as $key => $file ) {
			$include = true;
			if ( ! is_numeric( $key ) && is_bool( $file ) ) {
				$file_name = $key;
				if ( true !== $file ) {
					$include = false;
				}
			} else {
				$file_name = $file;
			}
			if ( $include ) {
				$full_path = WP_MEGAOPTIM_INC_PATH . str_replace( '/', DIRECTORY_SEPARATOR, $file_name );
				require_once( $full_path );
			}
		}
	}
}

/**
 * Prints out update nag
 */
function megaoptim_update_nag() {
	?>
	<div class="update-nag">
		<?php echo sprintf('%s %s.', __('Update your PHP version if you want to run', 'megaoptim'), '<strong>'.__('MegaOptim Image Optimizer', 'megaoptim') . '</strong>'); ?> <br/>
		<?php _e( 'Your actual version is:', 'megaoptim' ) ?>
		<strong><?php echo phpversion(); ?></strong>, <?php _e( 'required is', 'megaoptim' ) ?>
		<strong><?php echo WP_MEGAOPTIM_PHP_MINIMUM; ?></strong>
		<?php _e( '. Please contact your hosting or MegaOptim support for further assistence.', 'megaoptim' ) ?>
	</div>
	<?php
}

/**
 * Include file from megaoptim plugin
 * @param $path
 * @param $require
 */
function megaoptim_include_file($path, $require = true) {
	$path = str_replace('/', DIRECTORY_SEPARATOR, $path);
	$full_path = WP_MEGAOPTIM_PATH . DIRECTORY_SEPARATOR . $path;
	if($require) {
		require_once $full_path;
	} else {
		include_once $full_path;
	}
}

/**
 * Initializes the library
 */
function megaoptim_prepare_optimizer() {
	global $wp_version;
	require_once( WP_MEGAOPTIM_LIBRARIES_PATH . 'megaoptim-php' . DIRECTORY_SEPARATOR . 'loadnoncomposer.php' );
	\MegaOptim\Http\BaseClient::$api_url = WP_MEGAOPTIM_API_BASE_URL;
	\MegaOptim\Http\BaseClient::set_user_agent('WordPress ' . $wp_version . ' / Plugin ' . WP_MEGAOPTIM_VER);
}

/**
 * Check if nextgen is active
 * @return bool
 */
function megaoptim_is_nextgen_active() {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

	return is_plugin_active( 'nextgen-gallery/nggallery.php' ) || class_exists( 'C_NextGEN_Bootstrap' );
}

/**
 * IS WP Retina 2x active?
 * @return bool
 */
function megaoptim_is_wr2x_active() {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

	return is_plugin_active( 'wp-retina-2x/wp-retina-2x.php' ) || is_plugin_active( 'wp-retina-2x-pro/wp-retina-2x-pro.php' ) || class_exists( 'Meow_WR2X_Core' );
}

/**
 * Is WPEngine environment?
 * @return bool
 */
function megaoptim_is_wpengine() {
	return function_exists('is_wpe') && is_wpe();
}

/**
 * Is the current PHP version compatible?
 */
function megaoptim_is_php_version_compatible() {
	return version_compare(phpversion(), WP_MEGAOPTIM_PHP_MINIMUM, '>=');
}