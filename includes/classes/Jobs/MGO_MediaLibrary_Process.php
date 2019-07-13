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

		if(count($item) == 0) {
			return false;
		}

		$optimizer      = MGO_Library::get_optimizer();

		// NOTE: This will only work if all items are from the same attachment.
		$attachment_id  = $item[0]['attachment_id'];

		try {
			$attachment = new MGO_MediaAttachment($attachment_id);
		} catch (\Exception $e) {
			megaoptim_log($e->getMessage());
			return false; // Remove
		}

		if($attachment->is_locked()) {
			return $item;
		} else {

			// Lock
			$attachment->lock();

			foreach($item as $_itm) {
				//$attachment_id  = $_itm['attachment_id'];
				$size           = $_itm['attachment_size'];
				$resource       = $_itm['attachment_resource'];
				$local_path     = $_itm['attachment_local_path'];
				$request_params = $_itm['params'];

				try{

					$response = $optimizer->run($resource, $request_params);
					$file = $response->getResultByFileName( basename( $resource ) );

					if(!is_null($file)) {

						// Save data
						$data = megaoptim_generate_attachment_data($file, $response, $request_params);
						$attachment->set_attachment_data( $size, $data );
						$attachment->save();

						// Save files
						$file->saveAsFile( $local_path );
						$webp = $file->getWebP();
						if(!is_null($webp)) {
							$webp->saveAsFile( $local_path . '.webp' );
						}

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
						do_action( 'megaoptim_attachment_optimized', $attachment, $local_path, $response, $request_params, $size);
					}

				} catch (\Exception $e) {
					megaoptim_log($e->getMessage());
					// Unlock on error
					$attachment->unlock();
				}
			}

			// Unlock on success
			$attachment->unlock();
		}

		return false;

	}
}