<?php
/**
 * Created by PhpStorm.
 * User: darko
 * Date: 7/11/19
 * Time: 1:57 PM
 */

class MGO_MediaLibrary_Process extends MGO_Background_Process {


	/**
	 * The media optimization action
	 * @var string
	 */
	protected $action = 'megaoptim_media_optimization';

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param mixed $item Queue item to iterate over.
	 *
	 * @return mixed
	 */
	protected function task( $item ) {

		@set_time_limit( 460 );

        if ( ! isset($item['chunk_items']) || count($item['chunk_items']) == 0) {
            megaoptim_log('Chunk items not found. Skipping.');
            megaoptim_log( $item );
            return false;
        }

		$optimizer = MGO_Library::get_optimizer();

		if(empty($optimizer)) {
			return false; // Prevent if the optimizer is not set up. (No api key).
		}

		$attachment_id  = (int) $item['attachment_id'];
		$request_params = $item['request_params'];

		megaoptim_log( 'Optimizing images chunk of the attachment with id ' . $attachment_id . ' in background.' );

		try {
			$attachment = new MGO_MediaAttachment( $attachment_id );
		} catch ( \Exception $e ) {
			megaoptim_log( '--- Attachment Exception: ' . $e->getMessage() );

			return false; // Remove
		}

        // Save the responses
        $trans_key = 'mgo_async_'.$attachment_id.'_responses';
        $responses = get_transient($trans_key);
        if(!is_array($responses)) {
            $responses = [];
        }

		if ( $attachment->is_locked() ) {
			return false;
		} else {

			// Collect the resources
			$resources = array();
			foreach ( $item['chunk_items'] as $_itm ) {
				array_push( $resources, $_itm['attachment_resource'] );
			}

			// Try to send them for optimization
			try {

				// Lock
				$attachment->lock();

				// Run Optimizer
				$response = $optimizer->run( $resources, $request_params );

                // Save response
                $responses['chunk_'.$item['chunk_id']] = $response;

				if ( $response->isError() ) {
					$error     = $response->getErrors();
					$error_str = count( $error ) > 0 ? $error[0] : 'Unknown error.';
					$attachment->set_attachment_data( 'full', array( 'error' => $error_str ), false );
					$attachment->save();
					$attachment->unlock();

					return false;
				}

				megaoptim_log( '--- Response: ' . $response->getRawResponse() );

				// Loop through the files and save the results.
				foreach ( $item['chunk_items'] as $_itm ) {

					$attachment->refresh();
					//$attachment_id  = $_itm['attachment_id'];
					$size       = $_itm['attachment_size'];
					$resource   = $_itm['attachment_resource'];
					$local_path = $_itm['attachment_local_path'];
					$is_retina  = $_itm['type'] === 'retina';

					$filename = basename( $resource );
					$file     = $response->getResultByFileName( $filename );

					if ( ! is_null( $file ) ) {

						// Save data
						$data = megaoptim_generate_attachment_data( $file, $response, $request_params );
						$attachment->refresh();
						$attachment->set_attachment_data( $size, $data, $is_retina );
						$attachment->save();

						// Save files
						if ( $file->getSavedBytes() > 0 && $file->isSuccessfullyOptimized() ) {
							$file->saveAsFile( $local_path );
						}
						$webp = $file->getWebP();
						if ( ! is_null( $webp ) ) {
							if ( $webp->getSavedBytes() > 0 ) {
								$webp->saveAsFile( $local_path . '.webp' );
							}
						}

						$size = $is_retina ? $size . '@2x' : $size;

						/**
						 * Fired when attachment thumbnail was successfully optimized and saved.
						 *
						 * @param MGO_MediaAttachment $attachment_object - The media attachment that was optimized
						 * @param string $path - The result of the optimization for this attachment
						 * @param array $request_params - The api parameters
						 * @param string $size - The thumbnail version
						 *
						 * @since 1.0.0
						 */
						do_action( 'megaoptim_size_optimized', $attachment, $local_path, $response, $request_params, $size );
					} else {
						megaoptim_log( '--- Saving Response: Response by filename not found. File name: '.$filename );
					}
				}

			} catch ( \Exception $e ) {

				megaoptim_log( '--- Optimizer Exception: ' . $e->getMessage() . '. (' . json_encode( $resources ) . ')' );

				$attachment->set_attachment_data( 'full', array( 'error' => $e->getMessage() ), false );
				$attachment->save();
			}
		}

		// END
		$attachment->unlock();

        // Trigger hook
        if(isset($item['is_final']) && (bool) $item['is_final']) {
            $result = new MGO_ResultBag();
            $attachment->refresh();
            $stats = $attachment->get_optimization_stats();
            $result->total_thumbnails = isset($stats['processed_total']) ? $stats['processed_total'] : 0;
            $result->total_full_size = isset($stats['saved_bytes']) && $stats['saved_bytes'] > 0 ? 1 : 0;
            $result->total_saved_bytes = (isset($stats['saved_bytes']) ? (int) $stats['saved_bytes'] : 0) + (isset($stats['saved_thumbs_retina']) ? (int) $stats['saved_thumbs_retina'] : 0);
            foreach($responses as $key => $response) {
                $result->add($key, $response);
            }
            do_action( 'megaoptim_attachment_optimized', $attachment, $request_params, $result );
            delete_transient($trans_key);
        } else {
            set_transient($trans_key, $responses, MEGAOPTIM_ONE_MINUTE_IN_SECONDS * 10);
        }

        return false;
	}
}
