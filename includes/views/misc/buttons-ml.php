<?php
/* @var MGO_MediaAttachment $data */

$context     = $data::TYPE;
$retina_full = array( 'full' => $data->get_retina() );
if ( isset( $retina_full['full']['thumbs'] ) ) {
	unset( $retina_full['full']['thumbs'] );
}
$processed_thumbnails = $data->get_processed_thumbnails();
if ( ! empty( $retina_full['full'] ) ) {
	$processed_thumbnails['retina'] = array_merge( $retina_full, $processed_thumbnails['retina'] );
}


$success_icon = '<img src="' . WP_MEGAOPTIM_ASSETS_URL . '/img/check.png" alt="Success" width="12px"/>';
?>

<div class="megaoptim-attachment-buttons">

	<?php if ( $data->is_processed() ): ?>
        <div class="megaoptim-attachment-info-row megaoptim-attachment-general-info">
            <p>
				<?php
				if ( $data->get_saved_bytes() > 0 ) {
					$message = sprintf( __( '%s Optimization %s with the %s method.', 'megaoptim' ), $success_icon, '<strong>success</strong>', '<u>' . $data->get( 'compression' ) . '</u>' );
				} else {
					$message = __( 'Great Job! Attachment is already optimized.' );
				}
				echo '<p><strong>' . $message . '</strong></p>';
				?>
            </p>
        </div>
        <div class="megaoptim-attachment-info-row megaoptim-attachment-fullsize-stats">
			<?php
			$message = '';
			if ( $data->get_saved_bytes() > 0 ) {
				$message = sprintf( '<p>' . __( 'Original Size: %s', 'megaoptim' ) . '</p>', $data->get_original_size( true ) );
				$message .= sprintf( '<p>' . __( 'Optimized Size: %s', 'megaoptim' ) . '</p>', $data->get_optimized_size( true ) );
				$message .= sprintf( '<p>' . __( 'Percentage: %s', 'megaoptim' ) . '</p>', $data->get_saved_percent( true ) );
			} else {
				$message .= '<p>' . __( 'No further optimization needed.' ) . '</p>';
			}
			if ( $data->get( 'webp_size' ) > 0 ) {
				$message .= sprintf( __( 'WebP Size: %s', 'megaoptim' ), megaoptim_human_file_size( $data->get( 'webp_size' ) ) );
			}
			echo $message;
			?>
        </div>
        <div class="megaoptim-attachment-info-row megaoptim-attachment-actions">
            <p>
                <a href="#" class="button megaoptim-see-stats"><?php _e( 'Show More Info', 'megaoptim' ); ?></a>
				<?php if ( $data->has_backup() ): ?>
                    <a data-attachmentid="<?php echo $data->get_id(); ?>" data-context="<?php echo $context; ?>"
                       class="button megaoptim-optimize-restore"><?php _e( 'Restore', 'megaoptim' ); ?></a>
				<?php endif; ?>
            </p>
        </div>
        <div class="megaoptim-attachment-info-row megaoptim-attachment-stats" style="display: none;">
            <div class="megaoptim-attachment-thumbnail-stats">
                <table>
                    <thead>
                    <tr>
                        <th>Thumbnail</th>
                        <th>Details</th>
                    </tr>
                    </thead>
                    <tbody>
					<?php foreach ( array( 'normal', 'retina' ) as $type ): ?>
						<?php foreach ( $processed_thumbnails[ $type ] as $size => $thumb ): ?>
                            <tr>
                                <td>
									<?php
									$size = $type === 'retina' ? $size . '@2x' : $size;
									echo $size;
									?>
                                </td>
                                <td>
									<?php
									if ( ! $thumb['success'] ) {
										$message = __( 'Already optimized. No further processing needed.', 'megaoptim' );
										echo $message;
									} else {
										//$saved_bytes = $thumb['original_size'] - $thumb['optimized_size'];
										$message = sprintf( __( 'Original Size: %s', 'megaoptim' ), megaoptim_human_file_size( $thumb['original_size'] ) ) . '<br/>';
										$message .= sprintf( __( 'Optimized Size: %s', 'megaoptim' ), megaoptim_human_file_size( $thumb['optimized_size'] ) ) . '<br/>';
										$message .= sprintf( __( 'Reduction: %s', 'megaoptim' ), megaoptim_round( $thumb['saved_percent'], 2 ) . '%' );
										if ( $thumb['webp_size'] > 0 ) {
											$message .= '<br>' . sprintf( __( 'WebP Size: %s', 'megaoptim' ), megaoptim_human_file_size( $thumb['webp_size'] ) );
										}
										echo $message;
									}
									?>
                                </td>
                            </tr>
						<?php endforeach; ?>
					<?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
	<?php elseif ( $data->is_locked() ): ?>
        <p>
            <a disabled="disabled" class="button megaoptim-optimize megaoptim-optimizing disabled"
               data-context="<?php echo $context; ?>" data-attachmentid="<?php echo $data->get_id(); ?>"><span
                        class="megaoptim-spinner"></span> Optimizing...</a>
        </p>
	<?php else: ?>
        <div class="megaoptim-dropdown megaoptim-optimize megaoptim-optimize-attachment"
             data-context="<?php echo $context; ?>" data-attachmentid="<?php echo $data->get_id(); ?>">
            <!-- Optimize button start -->
            <input type="checkbox" id="optimize-<?php echo $data->get_id(); ?>" value=""
                   name="optimize-<?php echo $data->get_id(); ?>">
            <label for="optimize-<?php echo $data->get_id(); ?>" class="button-primary"
                   data-toggle="dropdown"><?php _e( 'Optimize', 'megaoptim' ); ?></label>
            <ul>
                <li>
                    <a class="megaoptim-optimize-run" data-compression="ultra"
                       href="#"><?php _e( 'Ultra', 'megaoptim' ); ?></a>
                </li>
                <li>
                    <a class="megaoptim-optimize-run" data-compression="intelligent"
                       href="#"><?php _e( 'Intelligently', 'megaoptim' ); ?></a>
                </li>
                <li>
                    <a class="megaoptim-optimize-run" data-compression="lossless"
                       href="#"><?php _e( 'Losslessly', 'megaoptim' ); ?></a>
                </li>
            </ul>
            <!-- Optimize button end -->
        </div>
	<?php endif; ?>
</div>