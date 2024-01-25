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

use DeliciousBrains\WP_Offload_Media\Items\Item;
use DeliciousBrains\WP_Offload_Media\Items\Media_Library_Item;
use DeliciousBrains\WP_Offload_Media\Items\Remove_Provider_Handler;
use DeliciousBrains\WP_Offload_Media\Items\Upload_Handler;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access is not allowed.' );
}

class MGO_As3cf_Util {

	/**
	 * The AS3CF Instance
	 * @var AS3CF_Plugin_Base|Amazon_S3_And_CloudFront
	 */
	protected $as3cf;

    /**
     * Is it cname?
     * @var
     */
    public $cname = null;

	/**
	 * MGO_As3cf_Util constructor.
	 *
	 * @param AS3CF_Plugin_Base $as3cf
	 */
	public function __construct( $as3cf ) {
		$this->as3cf = $as3cf;
        if ('cloudfront' === $this->as3cf->get_setting('domain')) {
            $this->cname = $this->as3cf->get_setting('cloudfront');
        }
	}

	/**
	 * Upload attachment to remote storage
	 *
	 * @param MGO_MediaAttachment $attachment
	 *
	 * @return array|bool|Media_Library_Item|WP_Error
	 * @throws Exception
	 */
	public function upload_attachment( $attachment ) {

        $this->log('upload_attachment', 'Offloading item');

		if ( ! $this->as3cf->get_setting( 'copy-to-s3' ) ) {
			return false;
		}

        $as3cf_item = Media_Library_Item::get_by_source_id( $attachment->get_id() );

        if(empty($as3cf_item) || is_wp_error($as3cf_item)) {
            $as3cf_item = Media_Library_Item::create_from_source_id(  $attachment->get_id() );
            $this->log('upload_attachment', 'Created new item');
        }

        // Remove the objects from the provider
        $upload_handler = $this->as3cf->get_item_handler( Upload_Handler::get_item_handler_key_name() );
        $upload_handler->handle( $as3cf_item, array( 'offloaded_files' => [] ) );

        $this->log('upload_attachment', 'Item offloaded');


        return true;
	}

	/**
	 * Removes attachment from remote storage.
	 *
	 * @param MGO_MediaAttachment $attachment
	 */
	public function remove_attachment( $attachment ) {

        $as3cf_item = Media_Library_Item::get_by_source_id( $attachment->get_id() );
        if(empty($as3cf_item) || is_wp_error($as3cf_item)) {
            $this->log('remove_attachment', 'Already removed');
            return;
        }

        // Remove the objects from the provider
        $remove_provider_handler = $this->as3cf->get_item_handler( Remove_Provider_Handler::get_item_handler_key_name() );
        $remove_provider_handler->handle( $as3cf_item, array( 'verify_exists_on_local' => false ) );
        $as3cf_item->delete();

        $this->log('remove_attachment', 'Item removed');

	}


	/**
	 * Re-upload attachment to remote storage (First remove the old one if any and then re-upload.)
	 *
	 * @param MGO_MediaAttachment $attachment
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function reupload_attachment( $attachment ) {

		// First remove the old attachment (if any)
		$this->remove_attachment( $attachment );
		return $this->upload_attachment( $attachment );
	}


	/**
	 * Returns the pat
	 *
	 * @param $paths
	 * @param bool $check_exists
	 *
	 * @return array
	 */
	public function get_paths_including_webp( $paths, $check_exists = true ) {
		$new_paths = array();
		foreach ( $paths as $size => $path ) {
			$path_webp          = $path . '.webp';
			$new_paths[ $size ] = $path;
			if ( $check_exists ) {
				if ( file_exists( $path_webp ) ) {
					$new_paths[ $size . '_webp' ] = $path_webp;
				}
			} else {
				$new_paths[ $size . '_webp' ] = $path_webp;
			}
		}

		return $new_paths;
	}

    /**
     * Returns item by id
     * @param $id
     *
     * @return bool|Media_Library_Item
     */
	public function get_item_by_id($id) {
        return Media_Library_Item::get_by_source_id($id);
    }

    /**
     * Returns  item by url
     * @param $url
     *
     * @return bool|Media_Library_Item
     */
    public function get_item_by_url($url) {
        $source_id = Media_Library_Item::get_source_id_by_remote_url($url);
        if($source_id === false) {
            $source = false;
        } else {
            $source = $this->get_item_by_id($source_id);
        }
        return $source;
    }

	/**
	 * Logs
	 *
	 * @param $tag
	 * @param $msg
	 */
	public function log( $tag, $msg ) {
		megaoptim_log( sprintf('MegaOptim -> WP Offload Media -> %s: %s', $tag, is_scalar($msg) ? $msg : print_r($msg, true) )  );
	}

}
