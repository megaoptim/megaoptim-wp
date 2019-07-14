<?php

class MGO_NGGProcess extends MGO_Background_Process {

	protected $action = 'megaoptim_ngg_optimization';

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

		$optimizer      = MGO_Library::get_optimizer();
		$resource       = $item['resource'];
		$request_params = $item['params'];
		$local_path     = $item['local_path'];

		megaoptim_log( 'Optimizing NGG attachment with id ' . $item['attachment_id'] . ' in background.' );

		try {
			$attachment = new MGO_NGGAttachment( $item['attachment_id'] );
		} catch ( \Exception $e ) {
			megaoptim_log( '--- Attachment Exception: ' . $e->getMessage() );

			return false;
		}

		if ( $attachment->is_locked() ) {
			return $item;
		}

		try {
			$attachment->lock();
			$response = $optimizer->run( $resource, $request_params );
			if($response->isError()) {
				megaoptim_log( '--- API Errors: ' . json_encode($response->getErrors()) );
			} else {
				megaoptim_log( '--- Response: ' . $response->getRawResponse() );
				foreach ( $response->getOptimizedFiles() as $file ) {
					$file->saveAsFile( $local_path );
					$webp = $file->getWebP();
					if ( ! is_null( $webp ) ) {
						$webp->saveAsFile( $local_path . '.webp' );
					}
				}
				$attachment->set_data( $response, $request_params );
				$attachment->update_ngg_meta();
				$attachment->save();
				// No need to backup attachments that are already optimized!
				if ( $attachment->is_already_optimized() ) {
					$attachment->delete_backup();
				}
				$attachment->unlock();
			}
			do_action( 'megaoptim_attachment_optimized', $attachment, $resource, $response, $request_params, $size = 'full' );
		} catch ( \Exception $e ) {
			megaoptim_log( '--- Optimizer Exception: ' . $e->getMessage() );
			$attachment->unlock();
		}

		return false;
	}
}