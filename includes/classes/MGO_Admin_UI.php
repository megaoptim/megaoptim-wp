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

class MGO_Admin_UI extends MGO_BaseObject {

	/**
	 * MegaOptim_Admin_UI constructor.
	 */
	public function __construct() {

		add_action( 'admin_menu', array( $this, 'register_ui_pages' ), 20, 0 );
		add_filter( 'admin_body_class', array( $this, 'admin_body_class' ), 10, 1 );
		add_action( 'admin_notices', array( $this, 'activation_guide' ) );
		add_filter( 'manage_media_columns', array( $this, 'manage_media_columns' ), 10, 1 );
		add_action( 'manage_media_custom_column', array( $this, 'manage_media_custom_column' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'add_meta_boxes', array( $this, 'register_info_metabox' ) );
		add_filter( "plugin_action_links_" . WP_MEGAOPTIM_BASENAME, array( $this, 'add_settings_link' ), 20, 1 );
	}

	/**
	 * Register the UI pages
	 */
	public function register_ui_pages() {

		add_media_page(
			__( 'MegaOptim Bulk Optimizer', 'megaoptim-image-optimizer' ),
			__( 'Bulk MegaOptim', 'megaoptim-image-optimizer' ),
			'upload_files',
			'megaoptim_bulk_optimizer',
			array(
				$this,
				'render_bulk_optimizer_page'
			)
		);

		add_options_page(
			__( 'MegaOptim Settings', 'megaoptim-image-optimizer' ),
			__( 'MegaOptim', 'megaoptim-image-optimizer' ),
			'manage_options',
			'megaoptim_settings',
			array(
				$this,
				'render_settings_page'
			)
		);
	}

	/**
	 * Render Settings Page
	 */
	public function render_bulk_optimizer_page() {
		$menu = 'optimizer';
		megaoptim_view( 'parts/header', array( 'menu' => $menu ) );
		$module = isset( $_GET['module'] ) ? $_GET['module'] : '';

		$optimizer = '';
		$params    = array();

		if ( empty( $module ) || $module === MEGAOPTIM_MODULE_MEDIA_LIBRARY ) {
			$optimizer = 'optimizers/media-library';
			$params    = array(
				'menu'    => $menu,
				'module'  => $module,
				'profile' => MGO_Profile::get_profile()
			);
		} else if ( $module === MEGAOPTIM_MODULE_FOLDERS ) {
			$optimizer = 'optimizers/folders';
			$params    = array(
				'menu'    => $menu,
				'module'  => $module,
				'profile' => MGO_Profile::get_profile()
			);
		} else if ( $module === MEGAOPTIM_MODULE_WEBP_CONVERTER ) {
			$optimizer = 'optimizers/webp-converter';
			$params    = array(
				'menu'    => $menu,
				'module'  => $module,
				'profile' => MGO_Profile::get_profile()
			);
		} else {
			$optimizer = apply_filters( 'megaoptim_optimizer_view', $optimizer, $module, $menu );
			$params    = apply_filters( 'megaoptim_optimizer_params', $params, $optimizer, $module, $menu );
		}

		if ( empty( $optimizer ) && empty( $menu ) ) {
			wp_die( '{MegaOptim} Unknown optimizer module!' );
		}
		megaoptim_view( $optimizer, $params );
		megaoptim_view( 'parts/footer', array( 'menu' => $menu ) );
	}

	/**
	 * Render the settings page
	 */
	public function render_settings_page() {
		$section = isset( $_GET['section'] ) && ! empty( $_GET['section'] ) ? $_GET['section'] : false;
		$menu    = 'settings';
		megaoptim_view( 'parts/header', array( 'menu' => $menu ) );
		$data = array( 'menu' => $menu );
		switch ( $section ) {
			case 'status':
				$view = 'settings/status';
				break;
			case 'advanced':
				$view                                 = 'settings/advanced';
				$data['medialibrary_backup_dir_size'] = megaoptim_get_dir_size( megaoptim_get_ml_backup_dir() );
				$data['nextgen_backup_dir_size']      = function_exists( 'megaoptim_get_nextgen_backup_dir' ) ? megaoptim_get_dir_size( megaoptim_get_nextgen_backup_dir() ) : 0;
				$data['localfiles_backup_dir_size']   = megaoptim_get_dir_size( megaoptim_get_files_backup_dir() );
				break;
			default:
				$view = 'settings/general';
		}
		megaoptim_view( $view, $data );
		megaoptim_view( 'parts/footer', array( 'menu' => $menu ) );
	}

	/**
	 * Add MegaOptim body classe
	 *
	 * @param $classes
	 *
	 * @return string
	 */
	public function admin_body_class( $classes ) {
		if ( megaoptim_is_admin_page() ) {
			$custom = array( 'megaoptim-page' );
			if ( megaoptim_is_admin_page( MEGAOPTIM_PAGE_BULK_OPTIMIZER ) ) {
				array_push( $custom, 'megaoptim-page-optimizer' );
			}
			$classes = ' ' . implode( ' ', $custom ) . ' ';
		}

		return $classes;
	}

	/**
	 * Outputs small guide how to use our plugin
	 * @return void
	 */
	public function activation_guide() {
		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if ( is_plugin_active( WP_MEGAOPTIM_BASENAME ) ) {
			$instructions_hidden = get_option( 'megaoptim_instructions_hide' );
			if ( ! $instructions_hidden || empty( $instructions_hidden ) || intval( $instructions_hidden ) !== 1 ) {
				$disallowed = array( 'megaoptim_settings', 'megaoptim_bulk_optimizer' );
				$page       = isset( $_GET['page'] ) ? $_GET['page'] : null;
				if ( ! in_array( $page, $disallowed ) ) {
					echo megaoptim_get_view( 'misc/instructions' );
				}
			}
		}
	}

	/**
	 * Add Support megaoptim media library column
	 *
	 * @param $columns
	 *
	 * @return mixed
	 */
	public function manage_media_columns( $columns ) {
		if ( MGO_Profile::_is_connected() ) {
			$columns['megaoptim_media_attachment'] = __( 'MegaOptim', 'megaoptim-image-optimizer' );
		}

		return $columns;
	}

	/**
	 * Print value in MegaOptim media column
	 *
	 * @param $column_name
	 * @param $attachment_id
	 *
	 * @throws MGO_Exception
	 */
	public function manage_media_custom_column( $column_name, $attachment_id ) {
		if ( MGO_Profile::_is_connected() ) {
			switch ( $column_name ) {
				case 'megaoptim_media_attachment':
					try {
						$attachment = new MGO_MediaAttachment( $attachment_id );
						echo MGO_MediaLibrary::instance()->get_attachment_buttons( $attachment );
					} catch ( \Exception $e ) {
						echo $e->getMessage();
					}
					break;
			}
		}
	}


	public function register_info_metabox() {
		if ( isset( $_GET['post'] ) && 'attachment' === get_post_type( $_GET['post'] ) ) {
			add_meta_box(
				'megaoptim_info_metabox',
				__( 'MegaOptim', 'megaoptim-image-optimizer' ),
				array( &$this, 'render_media_edit_buttons' ),
				null,
				'side'
			);
		}
	}

	/**
	 * Prints the attachment stats modal to the footer.
	 */
	public function render_media_edit_buttons() {
		$attachment_id = isset( $_GET['post'] ) ? $_GET['post'] : null;
		if ( ! is_null( $attachment_id ) && 'attachment' === get_post_type( $attachment_id ) && MGO_Profile::_is_connected() ) {
			echo '<div class="megaoptim_media_attachment">';
			try {
				$attachment = new MGO_MediaAttachment( $attachment_id );
				echo MGO_MediaLibrary::instance()->get_attachment_buttons( $attachment );
			} catch ( \Exception $e ) {
				echo $e->getMessage();
			}
			echo '</div>';
		}
	}

	/**
	 * Add the settings link
	 *
	 * @param $links
	 *
	 * @return array
	 */
	public function add_settings_link( $links ) {
		$custom_links = array(
			'<a href="admin.php?page=megaoptim_settings">' . __( 'Settings' ) . '</a>'
		);
		$links        = array_merge( $custom_links, $links );

		return $links;
	}

	/**
	 * Enqueues the required admin scripts
	 */
	public function admin_enqueue_scripts() {

		$current_screen = get_current_screen();
		$is_optimizer   = megaoptim_is_admin_page( MEGAOPTIM_PAGE_BULK_OPTIMIZER );

		//Enqueue Loading Overlay
		wp_register_script( 'megaoptim-loadingoverlay', WP_MEGAOPTIM_ASSETS_URL . 'js/loadingoverlay.min.js', array( 'jquery' ), '', true );
		wp_enqueue_script( 'megaoptim-loadingoverlay' );
		//Enqueue remodal.min.css
		wp_register_style( 'megaoptim-remodal', WP_MEGAOPTIM_ASSETS_URL . 'css/remodal.min.css', '', time(), 'screen' );
		wp_enqueue_style( 'megaoptim-remodal' );
		wp_register_script( 'megaoptim-remodal', WP_MEGAOPTIM_ASSETS_URL . 'js/remodal.min.js', array( 'jquery' ), time(), true );
		wp_enqueue_script( 'megaoptim-remodal' );

		//Enqueues jqueryfiletree plugin
		wp_register_script( 'megaoptim-filetree', WP_MEGAOPTIM_ASSETS_URL . 'resources/jqueryfiletree/dist/jQueryFileTree.min.js', array( 'jquery' ), time(), true );
		wp_register_style( 'megaoptim-filetree', WP_MEGAOPTIM_ASSETS_URL . 'resources/jqueryfiletree/dist/jQueryFileTree.min.css', '', time(), 'screen' );
		if ( megaoptim_is_folders_module() ) {
			wp_enqueue_script( 'megaoptim-filetree' );
			wp_enqueue_style( 'megaoptim-filetree' );
		}

		// Font awesome
		wp_register_style( 'megaoptim-fontawesome', WP_MEGAOPTIM_ASSETS_URL . 'resources/font-awesome/css/font-awesome.min.css', '', time(), 'screen' );
		wp_enqueue_style( 'megaoptim-fontawesome' );

		if ( $is_optimizer ) {
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_register_style( 'jquery-ui', WP_MEGAOPTIM_ASSETS_URL . 'resources/jquery-ui/jquery-ui.min.css' );
			wp_enqueue_style( 'jquery-ui' );
		}

		//Enqueues megaoptim.css,js
		wp_register_style( 'megaoptim-ui', WP_MEGAOPTIM_ASSETS_URL . 'css/megaoptim.css', '', time(), 'screen' );
		wp_enqueue_style( 'megaoptim-ui' );
		wp_register_script( 'megaoptim-ui', WP_MEGAOPTIM_ASSETS_URL . 'js/megaoptim.js', array( 'jquery' ), time(), true );
		wp_enqueue_script( 'megaoptim-ui' );
		wp_localize_script(
			'megaoptim-ui', 'MegaOptim', array(
				'ajax_url'       => admin_url( 'admin-ajax.php' ),
				'nonce_default'  => wp_create_nonce( MGO_Ajax::NONCE_DEFAULT ),
				'nonce_settings' => wp_create_nonce( MGO_Ajax::NONCE_SETTINGS ),
				'root_path'      => megaoptim_get_wp_root_path(),
				'strings'        => array(
					'clean'                 => __( 'Clean', 'megaoptim-image-optimizer' ),
					'backup_delete_confirm' => __( 'Are you sure you want to delete your backups? This action can not be reversed!', 'megaoptim-image-optimizer' ),
					'optimize'              => __( 'Optimize', 'megaoptim-image-optimizer' ),
					'optimizing'            => __( 'Optimizing...', 'megaoptim-image-optimizer' ),
					'working'               => __( 'Working...', 'megaoptim-image-optimizer' ),
					'no_tokens'             => __( 'No enough tokens left. You can always top up your account at https://megaoptim.com/dashboard/', 'megaoptim-image-optimizer' ),
					'profile_error'         => __( 'Error! We can not retrieve your profile. Please check if there is active internet connection or open a ticket in our dashboard area.', 'megaoptim-image-optimizer' ),
					'show_thumbnail_info'   => __( 'Show More Info', 'megaoptim-image-optimizer' ),
					'hide_thumbnail_info'   => __( 'Hide More Info', 'megaoptim-image-optimizer' ),
					'leave'                 => __( 'Are you sure you want to leave?', 'megaoptim-image-optimizer' ),
					'clear'                 => __( 'Clear', 'megaoptim-image-optimizer' ),
					'current_filters'       => __( 'Current Filters', 'megaoptim-image-optimizer' ),
					'confirm_delete_db'     => __( 'Are you sure? Please confirm ONLY if you read and agree with the consequences of this operation just above the delete button.', 'megaoptim-image-optimizer' ),
				),
				'context'        => array(
					'medialibrary' => MEGAOPTIM_TYPE_MEDIA_ATTACHMENT,
					'nextgen'      => 'nextgenv2',
					'files'        => MEGAOPTIM_TYPE_FILE_ATTACHMENT,
				),
				'page'           => $current_screen->id,
				'ticker'         => array(
					'enabled'  => in_array( $current_screen->id, array(
						'upload',
						'nggallery-manage-images',
						'attachment'
					) ),
					'context'  => $current_screen->id,
					'interval' => apply_filters( 'megaoptim_upload_ticker_interval', 4000 ),
				),
				'endpoints'      => array(
					'profile'   => WP_MEGAOPTIM_API_PROFILE,
					'setapikey' => admin_url( 'admin-ajax.php' ) . '?action=megaoptim_set_apikey',
				),
				'urls'           => array(
					'main'     => admin_url( "admin.php?page=megaoptimmain" ),
					'settings' => admin_url( "admin.php?page=megaoptimsettings" ),
					'status'   => admin_url( "admin.php?page=megaoptimstatus" ),
				),
				'spinner'        => '<span class="megaoptim-spinner"></span>',
			)
		);

		// Bulk Processor ( After megaoptim.js )
		wp_register_script( 'megaoptim-processor', WP_MEGAOPTIM_ASSETS_URL . 'js/megaoptim-processor.js', array( 'jquery' ), time(), true );
		if ( $is_optimizer ) {

			wp_enqueue_script( 'megaoptim-processor' );
			wp_localize_script(
				'megaoptim-processor', 'MGOProcessorData', array(
					'ajax_url'        => admin_url( 'admin-ajax.php' ),
					'nonce_optimizer' => wp_create_nonce( MGO_Ajax::NONCE_OPTIMIZER ),
					'strings'         => array(
						'finished'                   => __( 'Finished', 'megaoptim-image-optimizer' ),
						'waiting'                    => __( 'In queue', 'megaoptim-image-optimizer' ),
						'optimizing'                 => __( 'Optimizing', 'megaoptim-image-optimizer' ),
						'failed'                     => __( 'Failed', 'megaoptim-image-optimizer' ),
						'error'                      => __( 'Error', 'megaoptim-image-optimizer' ),
						'cancelling'                 => __( 'Cancelling', 'megaoptim-image-optimizer' ),
						'already_optimized'          => __( 'Already Optimized', 'megaoptim-image-optimizer' ),
						'loader_working_title'       => __( 'Preparing...', 'megaoptim-image-optimizer' ),
						'loader_working_description' => __( 'Hiring ultrasonic optimizers...', 'megaoptim-image-optimizer' ),
						'parse_error'                => __( 'Unable to parse the optimizer response', 'megaoptim-image-optimizer' ),
						'unprocessable'              => __( 'Attachment could not be processed. It\'s either optimized or there is some error.', 'megaoptim-image-optimizer' ),
						'consecutive_errors'         => __( 'Ow snap! Unfortunately there were _number_ consecutive errors. This is not normal behaviour and there is either a problem on your site or MegaOptim servers. Please contact support for help.', 'megaoptim-image-optimizer' ),
					),
					'context'         => array(
						'media_library' => MEGAOPTIM_TYPE_MEDIA_ATTACHMENT,
						'local_folders' => MEGAOPTIM_TYPE_FILE_ATTACHMENT,
						'ngg'           => class_exists( 'MGO_NGGAttachment' ) ? MEGAOPTIM_TYPE_NEXTGEN_ATTACHMENT : - 1,
					)
				)
			);
		}

		// Localfiles processor
		wp_register_script( 'megaoptim-localfiles', WP_MEGAOPTIM_ASSETS_URL . 'js/megaoptim-localfiles.js', array( 'jquery' ), time(), true );
		if ( megaoptim_is_optimizer_page( MEGAOPTIM_TYPE_FILE_ATTACHMENT ) ) {
			wp_localize_script( 'megaoptim-localfiles', 'MGOLocalFiles', array(
				'ajax_url'      => admin_url( 'admin-ajax.php' ),
				'nonce_default' => wp_create_nonce( MGO_Ajax::NONCE_DEFAULT ),
				'root_path'     => megaoptim_get_wp_root_path(),
				'strings'       => array(
					'alert_select_files'  => __( 'Please select a folder you want to optimize from the list.', 'megaoptim-image-optimizer' ),
					'info_optimized'      => '<p>' . __( 'Congratulations! This folder is fully optimized. Come back later when there are more images.', 'megaoptim-image-optimizer' ) . '</p>',
					'info_not_optimized'  => '<p>' . __( 'In order the plugin to work, you need to keep the tab open, you can always open a new tab and continue in that tab. If you close this tab the optimizer will stop but don\'t worry, you can always continue later from where you stopped.', 'megaoptim-image-optimizer' ) . '</p>',
					'selected_folder'     => __( 'Selected Folder', 'megaoptim-image-optimizer' ),
					'loading_title'       => __( 'Scanning...', 'megaoptim-image-optimizer' ),
					'loading_description' => __( 'We are currently scanning the selected folder for unoptimized images. Once finished if any unoptimized images are found you will be able to start optimizing.', 'megaoptim-image-optimizer' )
				)
			) );
			wp_enqueue_script( 'megaoptim-localfiles' );
		}

		// Library processor
		wp_register_script( 'megaoptim-library', WP_MEGAOPTIM_ASSETS_URL . 'js/megaoptim-library.js', array( 'jquery' ), time(), true );

		$is_media_library = megaoptim_is_optimizer_page( MEGAOPTIM_TYPE_MEDIA_ATTACHMENT );
		$is_ngg_library   = ( class_exists( 'MGO_NGGAttachment' ) && megaoptim_is_optimizer_page( MEGAOPTIM_TYPE_NEXTGEN_ATTACHMENT ) );

		if ( $is_media_library || $is_ngg_library ) {
			wp_localize_script( 'megaoptim-library', 'MGOLibrary', array(
				'ajax_url'       => admin_url( 'admin-ajax.php' ),
				'nonce_default'  => wp_create_nonce( MGO_Ajax::NONCE_DEFAULT ),
				'root_path'      => megaoptim_get_wp_root_path(),
				'strings'        => array(
					'loading_title'       => __( 'Scanning...', 'megaoptim-image-optimizer' ),
					'loading_description' => __( 'We are currently scanning for unoptimized images... Once finished if any unoptimized images are found you will be able to start optimizing.', 'megaoptim-image-optimizer' )
				),
				'max_chunk_size' => apply_filters( 'megaoptim_max_scan_chunk_size', 1500 ),
			) );
			wp_enqueue_script( 'megaoptim-library' );
		}
	}

	/**
	 * Returns the settings url.
	 * @return string
	 */
	public static function get_settings_url() {
		return admin_url( 'options-general.php?page=megaoptim_settings' );
	}

	/**
	 * Returns the optimizer url
	 *
	 * @param null $module
	 *
	 * @return string
	 */
	public static function get_optimizer_url( $module = null ) {
		$url = admin_url( 'upload.php?page=megaoptim_bulk_optimizer' );
		if ( ! is_null( $module ) ) {
			$url = add_query_arg( 'module', $module, $url );
		}

		return $url;
	}
}

MGO_Admin_UI::instance();