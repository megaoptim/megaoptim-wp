<?php
/* @var MGO_MediaAttachment $data */
$context                   = $data::TYPE;
$optimized_thumbnails      = $data->get_optimized_thumbnails();
$total_saved_on_thumbnails = $data->get_total_saved_bytes_thumbnails( false );
$total_saved_on_thumbnails = megaoptim_human_file_size( $total_saved_on_thumbnails );

#var_dump( $data->is_optimized() );
#var_dump( $data->get_unoptimized_thumbnails() );

$text_optimized_thumbnails = count( $optimized_thumbnails['normal'] ) . ' regular thumbnails';
if ( count( $optimized_thumbnails['retina'] ) > 0 ) {
	$text_optimized_thumbnails .= ' ' . __( 'and', 'megaoptim' ) . ' ' . count( $optimized_thumbnails['retina'] ) . ' retina thumbnails';
}
?>

<div class="megaoptim-attachment-buttons">
	<?php if ( $data->is_already_optimized() && $data->get( 'success' ) != 1 ): ?>
        <p>
			<?php if ( $total_saved_on_thumbnails > 0 ): ?><?php $method = __( 'Full size attachment already optimized, no further optimization needed!', 'megaoptim' ); ?><?php echo sprintf( __( '%s Additionally we optimized its thumbnails, + %s, total saved on thumbnails %s', 'megaoptim' ), '<strong>' . $method . '</strong>', $text_optimized_thumbnails, $total_saved_on_thumbnails ); ?><?php else: ?><?php $method = __( 'Attachment already optimized, no further optimization needed!', 'megaoptim' ); ?><?php endif; ?>
        </p>
	<?php elseif ( $data->is_optimized() ): ?>
        <p style="margin-bottom: 0">
            <strong><?php echo sprintf( __( 'Success! Image optimized successfully with %s method.', 'megaoptim' ), '<u>' . $data->get( 'compression' ) . '</u>' ); ?></strong>
        </p>
        <p><?php echo sprintf( __( 'Total saved on the full version %s (-%s). %s optimized, total saved on the thumbnails %s.', 'megaoptim' ), $data->get_total_saved_bytes( true ), $data->get_saved_percent( true ), $text_optimized_thumbnails, $total_saved_on_thumbnails ); ?></p>
		<?php if ( $data->has_backup() ): ?>
            <p>
                <a data-attachmentid="<?php echo $data->get_id(); ?>" data-context="<?php echo $context; ?>" class="button megaoptim-optimize-restore"><?php _e( 'Restore', 'megaoptim' ); ?></a>
            </p>
		<?php endif; ?><?php elseif ( $data->is_locked() ): ?>
        <p>
            <a disabled="disabled" class="button megaoptim-optimize megaoptim-optimizing disabled" data-context="<?php echo $context; ?>" data-attachmentid="<?php echo $data->get_id(); ?>"><span class="megaoptim-spinner"></span> Optimizing...</a>
        </p>
	<?php else: ?>
        <div class="megaoptim-dropdown megaoptim-optimize megaoptim-optimize-attachment" data-context="<?php echo $context; ?>" data-attachmentid="<?php echo $data->get_id(); ?>">
            <input type="checkbox" id="optimize-<?php echo $data->get_id(); ?>" value="" name="optimize-<?php echo $data->get_id(); ?>">
            <label for="optimize-<?php echo $data->get_id(); ?>" class="button-primary" data-toggle="dropdown">
				<?php _e( 'Optimize', 'megaoptim' ); ?>
            </label>
            <ul>
                <li>
                    <a class="megaoptim-optimize-run" data-compression="ultra" href="#"><?php _e( 'Ultra', 'megaoptim' ); ?></a>
                </li>
                <li>
                    <a class="megaoptim-optimize-run" data-compression="intelligent" href="#"><?php _e( 'Intelligently', 'megaoptim' ); ?></a>
                </li>
                <li>
                    <a class="megaoptim-optimize-run" data-compression="lossless" href="#"><?php _e( 'Losslessly', 'megaoptim' ); ?></a>
                </li>
            </ul>
        </div>
	<?php endif; ?>
</div>