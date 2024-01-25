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

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access is not allowed.' );
}

class MGO_As3cf {

	/**
	 * @var MGO_As3cf_Util
	 */
	private $util;

	/**
	 * MGO_As3cf constructor.
	 */
	public function __construct() {
		add_action( 'as3cf_init', array( $this, 'init' ) );
	}


	/**
	 * Initialize the AS3CF integration
	 *
	 * @param Amazon_S3_And_CloudFront $as3cf
	 */
	public function init( $as3cf ) {


		$this->util = new MGO_As3cf_Util( $as3cf );

		// Bail if the plugin is not set up.
		if ( ! $as3cf->is_plugin_setup( true ) ) {
			$this->util->log( 'init', 'Plugin not setup. Integration will not be added.' );

            return;
        }
        add_filter('wp_check_filetype_and_ext', array($this, 'add_webp_support'), 10, 4);
        //add_filter('get_attached_file', array($this, 'prevent_filtering_s3_paths'), 10, 2);
        add_filter('as3cf_pre_update_attachment_metadata', array($this, 'prevent_initial_upload'), 10, 4);
        add_filter('as3cf_attachment_file_paths', array($this, 'add_webp_paths'), 15, 1);
        add_filter('as3cf_remove_attachment_paths', array($this, 'remove_webp_paths'), 15, 1);

        add_action('megaoptim_attachment_optimized', array($this, 'upload_attachment'), 10, 1);
        add_action('megaoptim_after_restore_attachment', array($this, 'restore_attachment'), 10, 1);
        add_filter('megaoptim_webp_uploads_base', array($this, 'webp_uploads_base'), 10, 2);
        add_filter('megaoptim_webp_file_404', array($this, 'fix_remote_webp_path'), 10, 4);
    }


	/**
	 * Once attachment is optimized, upload it to remote.
	 *
	 * @param MGO_Attachment $attachment
	 */
	public function upload_attachment( $attachment ) {

        $this->util->log('upload_attachment', 'Handling upload #'.$attachment->get_id());

		// Bail if not a Media Library attachment.
		if ( ! ( $attachment instanceof MGO_MediaAttachment ) ) {
            $this->util->log('upload_attachment', 'Invalid attachment');
            return;
		}
		try {
			$this->util->upload_attachment( $attachment );
		} catch ( \Exception $e ) {
            $this->util->log( 'upload_attachment', $e->getMessage() );
		}
	}

	/**
	 * Once attachment is restored, re-upload it to remote.
	 *
	 * @param MGO_Attachment $attachment
	 */
	public function restore_attachment( $attachment ) {

		// Bail if not a Media Library attachment.
		if ( ! ( $attachment instanceof MGO_MediaAttachment ) ) {
			return;
		}
		try {
			$this->util->reupload_attachment( $attachment );
		} catch ( \Exception $e ) {
			$this->util->log( 'restore_attachment', $e->getMessage() );
		}

	}


	/**
	 * Do not filter the attached files.
	 *
	 * @param $file
	 * @param $id
	 *
	 * @return false|string
	 */
	public function prevent_filtering_s3_paths( $file, $id ) {
		$scheme = parse_url( $file, PHP_URL_SCHEME );
		if ( !empty($scheme) && strpos( $scheme, 's3' ) !== false ) {
			return get_attached_file( $id, true );
		}

		return $file;
	}


	/**
	 * If the auto optimize feature is enabled stop the auto upload that
	 * is done by WP Offload Media.
	 * We will rely on MegaOptim auto-optimize feature and upload the images
	 * after optimization.
	 *
	 * @param $bool
	 * @param $data
	 * @param $post_id
	 * @param \DeliciousBrains\WP_Offload_Media\Items\Item $old_provider_object
	 *
	 * @return bool
	 */
	public function prevent_initial_upload( $bool, $data, $post_id, $old_provider_object ) {

		$auto_optimize = (int) MGO_Settings::instance()->get( MGO_Settings::AUTO_OPTIMIZE, 1 );

		if ( $auto_optimize ) {
            $this->util->log( 'prevent_initial_upload', 'Cancelled the S3 upload process, MegaOptim will re-trigger it.' );
			return true;
		}

		return $bool;
	}


	/**
	 * Sets the extension and mime type for .webp files.
	 *
	 * @param $types
	 * @param string $file Full path to the file.
	 * @param string $filename The name of the file (may differ from $file due to
	 *                                          $file being in a tmp directory).
	 * @param array $mimes Key is the file extension with value as the mime type.
	 *
	 * @return mixed
	 */
	public function add_webp_support( $types, $file, $filename, $mimes ) {
		if ( false !== strpos( $filename, '.webp' ) ) {
			$types['ext'] = 'webp';
			$types['type'] = 'image/webp';
		}
		return $types;
	}

	/**
	 * Add webp paths if they exist for offloading
	 *
	 * @param $paths
	 *
	 * @return array
	 */
	public function add_webp_paths( $paths ) {
		$paths = $this->util->get_paths_including_webp( $paths, true );

		return $paths;
	}

	/**
	 * Remove webp paths if they exist
     *
     * @param $paths
     *
     * @return array
     */
    public function remove_webp_paths($paths)
    {
        $paths = $this->util->get_paths_including_webp($paths, false);

        return $paths;
    }


    /**
     * Change the uploads base so that equals to the remote storage base if this item exist on the remote storage.
     *
     * @param $url
     * @param $original
     *
     * @return mixed
     */
    public function webp_uploads_base($url, $original)
    {
        if ($url === false) {
            return $this->convert_webp_path($url, $original);
        } elseif ( ! empty($this->util->cname) && ! is_null($this->util->cname)) {
            if (megaoptim_contains($original, $this->util->cname)) {
                return $this->convert_webp_path($url, $original);
            }
        }
        return $url;
    }

    /**
     * Convert normal images that are uploaded to S3 to their .webp versions if they exist on the remote storage.
     *
     * @param $url
     * @param $original
     *
     * @return string|string[]
     */
    public function convert_webp_path($url, $original)
    {
        $remote_item = $this->util->get_item_by_url($original);
        if ($remote_item === false) {
            $replaced_url = preg_replace('/-\d+x\d*/i', '', $original);
            $remote_item  = $this->util->get_item_by_url($replaced_url);
        }
        if ($remote_item === false) {
            return $url;
        }
        $parsed = parse_url($original);
        $url    = str_replace($parsed['scheme'].'://', '', $original);
        $url    = str_replace(basename($url), '', $url);
        return $url;
    }

    /**
     * @param $bool
     * @param $webp_file
     * @param $file_url
     * @param $uploads_path_base
     *
     * @return mixed
     */
    public function fix_remote_webp_path($bool, $webp_file, $file_url, $uploads_path_base)
    {
        return megaoptim_contains($file_url, $uploads_path_base) ? $webp_file : $bool;
    }
}

new MGO_As3cf();