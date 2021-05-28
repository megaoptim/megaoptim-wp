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

class MGO_CLI {

	/**
	 * Display info about the current user
	 *
	 * ## EXAMPLES
	 *
	 *     wp megaoptim info
	 */
	public function info() {

		$profile = new MGO_Profile();

		if ( $profile->is_connected() ) {

			$user_name           = $profile->get_name();
			$tokens_count        = $profile->get_tokens_count();
			$tokens_count_string = $tokens_count == - 1 ? __( 'Unlimited', 'megaoptim-image-optimizer' ) : $tokens_count;

			$str_user = __( 'User', 'megaoptim-image-optimizer' );
			WP_CLI::line( WP_CLI::colorize( "%G[{$str_user}]%n: $user_name" ) );

			$str_rt = __( 'Remaining tokens', 'megaoptim-image-optimizer' );
			WP_CLI::line( WP_CLI::colorize( "%G[{$str_rt}]%n: $tokens_count_string" ) );


		} else {

			WP_CLI::error( __( 'Not connected. To set your api key type: wp megaoptim set_api_key=your-api-key', 'megaoptim-image-optimizer' ), false );
			WP_CLI::error( __( 'If you do not have api key. Please sign up at https://megaoptim.com/register', 'megaoptim-image-optimizer' ) );
		}

	}


	/**
	 * Optimizes single attachment
	 *
	 * <api_key>
	 * : The api key you want to set
	 *
	 *
	 * ## EXAMPLES
	 *
	 *     wp megaoptim set_api_key your-api-key
	 */
	public function set_api_key( $args1 ) {
		$key = $args1[0];
		MGO_Settings::setApiKey( $key );
		WP_CLI::success( 'API KEY set' );
	}

	/**
	 * Optimizes single media library attachment
	 *
	 * <ID>
	 * : The ID of the single media library attachment
	 *
	 * ## OPTIONS
	 *
	 * [--force]
	 * : Force re-optimize if there is backup.
	 *
	 * [--level=<option>]
	 * : Set compression level
	 * ---
	 * default: intelligent
	 * options:
	 *   - ultra
	 *   - intelligent
	 *   - lossless
	 * ---
	 *
	 * [--recursive]
	 * : Only used when optimizing directory
	 *
	 * ## EXAMPLES
	 *
	 *     wp megaoptim optimize 5
	 *     wp megaoptim optimize 5 --force
	 *     wp megaoptim optimize /path/to/file.jpg
	 *     wp megaoptim optimize /path/to/folder
	 *     wp megaoptim optimize /path/to/folder --recursive
	 *
	 */
	public function optimize( $args1, $args2 ) {

		$ID = $args1[0];

		$force     = 0;
		$recursive = 0;
		$level     = '';

		// Collect the optional parameters
		if ( isset( $args2['force'] ) ) {
			$force = (int) $args2['force'];
		}
		if ( isset( $args2['recursive'] ) ) {
			$recursive = (int) $args2['recursive'];
		}
		if ( isset( $args2['level'] ) ) {
			$level = $args2['level'];
			if ( ! \MegaOptim\Optimizer::valid_compression_level( $level ) ) {
				WP_CLI::error( __( 'The level parameter should be one of the following: ultra, intelligent, lossless', 'megaoptim-image-optimizer' ) );

				return;
			}
		}

		if ( file_exists( $ID ) ) { // File path
			$path = $ID;
			if ( is_dir( $ID ) ) {
				$images = megaoptim_find_images( $path, $recursive );
				if ( ! empty( $images ) ) {
					$total = 0;
					WP_CLI::success( __( 'Images found ' ) . ': ' . count( $images ) );
					foreach ( $images as $image ) {
						$r = $this->optimize_file( $image, $force, $level );
						if ( ! is_null( $r ) ) {
							$total += $r->total_saved_bytes;
						}
					}
					WP_CLI::success( __( 'Total saved on all images ' ) . ': ' . megaoptim_human_file_size( $total ) );
				} else {
					WP_CLI::error( __( 'No images found in directory' ) . ': ' . $path );
				}
			} else {
				$this->optimize_file( $path, $force, $level );
			}
		} else if ( is_numeric( $ID ) ) { // Media attachment
			$ID = (int) $ID;
			$this->optimize_media_library( $ID, $force, $level );
		} else { // Unknown
			WP_CLI::error( __( 'Parameter 1 should be ID, path to image or path to folder with images.' ) );
		}


	}


	/**
	 * Optimizes Media Library images in bulk mode.
	 *
	 * ## OPTIONS
	 *
	 * [--date_from=<DATE>]
	 * : Filter images from date in Y-m-d format.
	 *
	 * [--date_to=<DATE>]
	 * : Filter images to date in Y-m-d format. If omited the today's date will be taken.
	 *
	 * [--author=<ID>]
	 * : Filter images that are uploaded by specific author user. If omited no author will be taken into account. Images from ANY authors will be queried.
	 *
	 * [--level=<option>]
	 * : Set compression level
	 * ---
	 * default: intelligent
	 * options:
	 *   - ultra
	 *   - intelligent
	 *   - lossless
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     wp megaoptim bulk
	 *     wp megaoptim bulk --date_from=2019-11-01
	 *     wp megaoptim bulk --date_from=2019-11-01 --date_to=2019-11-05 --author=1
	 *     wp megaoptim bulk --author=2
	 *
	 */
	public function bulk( $args, $optional_params = array() ) {

		$time_start = microtime( true );

		$params = array(
			'date_from' => '',
			'date_to'   => date( 'Y-m-d' ),
			'author'    => '',
			'level'     => '',
		);

		$params = wp_parse_args( $optional_params, $params );
		$query  = array();

		// Validate Date
		if ( ! empty( $params['date_from'] ) && ! empty( $params['date_to'] ) ) {
			$from = megaoptim_create_datetime( $params['date_from'], 'Y-m-d' );
			$to   = megaoptim_create_datetime( $params['date_to'], 'Y-m-d' );
			if ( $from instanceof DateTime && $to instanceof DateTime ) {
				$query['date_from'] = $from->format( 'Y-m-d 00:00:00' );
				$query['date_to']   = $to->format( 'Y-m-d 23:59:59' );
			}
		}

		// Validate Author
		if ( ! empty( $params['author'] ) ) {
			$author = get_user_by( 'ID', $params['author'] );
			if ( $author instanceof WP_User ) {
				$query['author'] = $author->ID;
			}
		}

		// Find Results
		$media_library = MGO_MediaLibrary::instance();
		$results       = $media_library->get_stats( true, $query );

		// Prepare
		$images = is_array( $results->remaining ) ? $results->remaining : array();

		if ( count( $images ) === 0 ) {
			\WP_CLI::warning( __( 'No images found for your query.', 'megaoptim-image-optimizer' ) );
		} else {
			// Run
			$total_saved     = 0;
			$total_optimized = 0;
			foreach ( $images as $image ) {
				try {
					$api_params = array();
					if ( \MegaOptim\Optimizer::valid_compression_level( $params['level'] ) ) {
						$api_params['compression'] = $params['level'];
					}
					$result          = $media_library->optimize( $image['ID'], $api_params );
					$total_saved     += $result->total_saved_bytes;
					$total_optimized += $result->total_thumbnails + $result->total_full_size;

					if ( $result->total_saved_bytes == 0 ) {
						$message = sprintf( __( 'Attachment already %s optimized. No further optimization needed.', 'megaoptim-image-optimizer' ), $image['ID'] );
					} else {
						$message = sprintf( __( 'Attachment %s optimized. Total thumbnails %s, Total saved %s', 'megaoptim-image-optimizer' ), $image['ID'], $result->total_thumbnails, megaoptim_human_file_size( $result->total_saved_bytes ) );
					}
					\WP_CLI::success( $message );
				} catch ( \MGO_Exception $e ) {
					if ( $e instanceof MGO_Attachment_Already_Optimized_Exception ) {
						\WP_CLI::success( sprintf( __( 'Attachment %s already optimized. No further optimization needed.', 'megaoptim-image-optimizer' ), $image['ID'] ) );
					} else if ( $e instanceof MGO_Attachment_Locked_Exception ) {
						\WP_CLI::warning( sprintf( __( 'Attachment %s not optimized. Reason: %s', 'megaoptim-image-optimizer' ), $image['ID'], $e->getMessage() ) );
					} else {
						\WP_CLI::warning( sprintf( __( 'Attachment %s not optimized. Reason: %s', 'megaoptim-image-optimizer' ), $image['ID'], $e->getMessage() ) );
						break;
					}
				}
			}
			$time_elapsed_secs = microtime( true ) - $time_start;
			WP_CLI::success( sprintf( __( 'Process finished in %s seconds. Total optimized %s, Totaal saved %s', 'megaoptim-image-optimizer' ), megaoptim_round( $time_elapsed_secs, 5 ), $total_optimized, megaoptim_human_file_size( $total_saved ) ) );
		}
	}


	/**
	 * Restores Media library Attachments that have backup. Supports: Single restore or bulk restore. Beaware: Restore doesn't mean refund.
	 *
	 * <IDorAll>
	 * : Either ID or 'all' value if you want to restore all
	 *
	 * ## EXAMPLES
	 *
	 *     wp megaoptim restore 5
	 *     wp megaoptim restore all
	 *
	 */
	public function restore( $param ) {

		$ID_OR_ALL = $param[0];

		$time_start = microtime( true );

		$usage = 'Pleasse enter valid ID if you want to restore single attachment OR enter "all" if you want to restore all the attachments';


		if ( is_numeric( $ID_OR_ALL ) && false !== get_post_status( $ID_OR_ALL ) ) {

			try {
				$attachment = new MGO_MediaAttachment( $ID_OR_ALL );
				if ( $attachment->has_backup() ) {
					$attachment->restore();
					WP_CLI::success( sprintf( __( 'Attachment %s successfully restored.', 'megaoptim-image-optimizer' ), $attachment->get_id() ) );
				} else {
					WP_CLI::warning( sprintf( __( 'Attachment %s not restored. Reason: Missing Backup.', 'megaoptim-image-optimizer' ), $attachment->get_id() ) );
				}
			} catch ( MGO_Exception $e ) {
				WP_CLI::error( sprintf( __( 'Failed to restore attachment %s. Reason: %s', 'megaoptim-image-optimizer' ), $attachment->get_id(), $e->getMessage() ) );
			}
		} else if ( strtolower( $ID_OR_ALL ) === 'all' ) {

			global $wpdb;

			$query                              = "SELECT PM.post_id as ID, PM.meta_value as data FROM $wpdb->postmeta PM WHERE PM.meta_key='_megaoptim_data'";
			$optimized_attachments              = $wpdb->get_results( $query );
			$optimized_attachments_with_backups = array();
			if ( empty( $optimized_attachments ) ) {
				WP_CLI::error( __( 'No optimized attachments found.', 'megaoptim-image-optimizer' ) );

				return;
			} else {
				foreach ( $optimized_attachments as $attachment ) {
					$attachment_data        = @unserialize( $attachment->data );
					$attachment_backup_path = null;
					if ( isset( $attachment_data['backup_path'] ) ) {
						$attachment_backup_path = $attachment_data['backup_path'];
					} else if ( isset( $attachment_data->backup_path ) ) {
						$attachment_backup_path = $attachment_data->backup_path;
					}
					if ( ! is_null( $attachment_backup_path ) && file_exists( $attachment_backup_path ) ) {
						array_push( $optimized_attachments_with_backups, array(
							'ID'          => $attachment->ID,
							'backup_path' => $attachment_backup_path
						) );
					}
				}
			}

			$total_with_backups = count( $optimized_attachments_with_backups );

			if ( $total_with_backups <= 0 ) {
				WP_CLI::error( __( 'No optimized attachments with valid backups found.', 'megaoptim-image-optimizer' ) );
			} else {

				WP_CLI::confirm( sprintf( __( 'We are about tore restore %d attachments. Are you sure you want to continue?', 'megaoptim-image-optimizer' ), $total_with_backups ) );

				$total_restored = 0;

				megaoptim_prevent_auto_optimization();
				foreach ( $optimized_attachments_with_backups as $optimized_attachment_with_backups ) {

					$attachment_ID          = $optimized_attachment_with_backups['ID'];
					$attachment_path        = get_attached_file( $attachment_ID );
					$attachment_backup_path = $optimized_attachment_with_backups['backup_path'];
					$attachment_dir         = dirname( $attachment_path );
					if ( ! is_writable( $attachment_dir ) ) {
						WP_CLI::warning( sprintf( __( 'Failed to restore attachment %s. Reason: %s', 'megaoptim-image-optimizer' ), $attachment_ID, __( 'Directory not writable', 'megaoptim-image-optimizer' ) ) );
						continue;
					}
					if ( @rename( $attachment_backup_path, $attachment_path ) ) {
						megaoptim_regenerate_thumbnails( $attachment_ID, $attachment_path );
						delete_post_meta( $attachment_ID, '_megaoptim_data' );
						do_action( 'megaoptim_after_restore_attachment', new MGO_MediaAttachment( $attachment_ID ) );
						WP_CLI::success( sprintf( __( 'Attachment %s successfully restored.', 'megaoptim-image-optimizer' ), $attachment_ID ) );
						$total_restored ++;
					} else {
						WP_CLI::warning( sprintf( __( 'Failed to restore attachment %s. Reason: %s', 'megaoptim-image-optimizer' ), $attachment_ID, __( 'File not writable', 'megaoptim-image-optimizer' ) ) );
					}
				}
				megaoptim_restore_auto_optimization();

				$time_elapsed_secs = microtime( true ) - $time_start;
				WP_CLI::success( sprintf( __( 'Process finished in %s seconds. Total restored %s attachments.', 'megaoptim-image-optimizer' ), $time_elapsed_secs, $total_restored ) );
			}

		} else {
			WP_CLI::error( $usage );
		}

	}

	/////////////////////////// UTILS ///////////////////////

	/**
	 * Optimize single file
	 *
	 * @param $file
	 * @param $force
	 * @param $level
	 *
	 * @return MGO_ResultBag
	 */
	public static function optimize_file( $file, $force, $level ) {
		$params = array();
		// Setup params
		if ( ! empty( $level ) ) {
			$params['compression'] = $level;
		}
		try {
			$result  = MGO_FileLibrary::instance()->optimize( $file, $params );
			$message = sprintf( __( 'File %s optimized. Total saved %s', 'megaoptim-image-optimizer' ), $file, megaoptim_human_file_size( $result->total_saved_bytes ) );
			\WP_CLI::success( $message );

			return $result;
		} catch ( MGO_Attachment_Already_Optimized_Exception $e ) {
			$message = sprintf( __( 'File already %s optimized. No further optimization needed.', 'megaoptim-image-optimizer' ), $file );
			\WP_CLI::success( $message );
		} catch ( MGO_Exception $e ) {
			$message = sprintf( __( 'File %s not optimized. Reason: %s', 'megaoptim-image-optimizer' ), $file, $e->getMessage() );
			\WP_CLI::warning( $message );
		}

		return null;
	}

	/**
	 * Optimize single media library attachment
	 *
	 * @param $ID
	 * @param $force
	 * @param $level
	 */
	public static function optimize_media_library( $ID, $force, $level ) {
		// Attemtpt to optimize
		try {
			$params     = array();
			$attachment = new MGO_MediaAttachment( $ID );
			// If force, first restore it.
			if ( $force ) {
				if ( $attachment->has_backup() ) {
					$attachment->restore();
				}
			}
			// Setup params
			if ( ! empty( $level ) ) {
				$params['compression'] = $level;
			}
			// Optimize now
			$result  = MGO_MediaLibrary::instance()->optimize( $attachment, $params );
			$message = sprintf( __( 'Attachment %s optimized. Total thumbnails %s, Total saved %s', 'megaoptim-image-optimizer' ), $ID, $result->total_thumbnails, megaoptim_human_file_size( $result->total_saved_bytes ) );
			\WP_CLI::success( $message );
		} catch ( MGO_Attachment_Already_Optimized_Exception $e ) {
			$message = sprintf( __( 'Attachment already %s optimized. No further optimization needed.', 'megaoptim-image-optimizer' ), $ID );
			\WP_CLI::success( $message );
		} catch ( \Exception $e ) {
			$message = sprintf( __( 'Attachment %s not optimized. Reason: %s', 'megaoptim-image-optimizer' ), $ID, $e->getMessage() );
			\WP_CLI::warning( $message );
		}
	}


}

WP_CLI::add_command( 'megaoptim', 'MGO_CLI' );
