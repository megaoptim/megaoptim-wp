<?php
/* @var MGO_NGGAttachment $data */
$context = $data::TYPE;
?>

<div class="megaoptim-attachment-buttons">
	<?php if ( $data->is_already_optimized() ): ?>
        <p>
			<?php echo '<strong>' . __( 'Full size image already optimized, no further optimization needed!', 'megaoptim-image-optimizer' ) . '</strong>'; ?>
        </p>
	<?php elseif ( $data->is_processed() ): ?>
        <p>
			<?php $method = sprintf( __( 'Success! Image is optimized successfully with %s method.', 'megaoptim-image-optimizer' ), '<strong>' . $data->get( 'compression' ) . '</strong>' ); ?>
			<?php echo sprintf( __( '%s Total saved %s (%s)' ), '<strong>' . $method . '</strong>', $data->get_total_saved_bytes( true ), $data->get_saved_percent( true ) ); ?>
        </p><p>
			<?php if ( $data->has_backup() ): ?>
                <a data-attachmentid="<?php echo $data->get_id(); ?>" data-context="<?php echo $context; ?>" class="button-primary megaoptim-optimize-restore"><?php _e('Optimizing...', 'megaoptim-image-optimizer'); ?></a>
			<?php endif; ?>
        </p>
	<?php elseif ( $data->is_locked() ): ?>
        <p>
            <a disabled="disabled" class="button megaoptim-optimize megaoptim-optimizing disabled" data-context="<?php echo $context; ?>" data-attachmentid="<?php echo $data->get_id(); ?>"><span class="megaoptim-spinner"></span> <?php _e('Optimizing...', 'megaoptim-image-optimizer'); ?></a>
        </p>
	<?php else: ?>
        <div class="megaoptim-dropdown megaoptim-optimize megaoptim-optimize-attachment" data-context="<?php echo $context; ?>" data-attachmentid="<?php echo $data->get_id(); ?>">
            <input type="checkbox" id="optimize-<?php echo $data->get_id(); ?>" value="" name="optimize-<?php echo $data->get_id(); ?>">
            <label for="optimize-<?php echo $data->get_id(); ?>" class="button-primary" data-toggle="dropdown">
				<?php _e( 'Optimize', 'megaoptim-image-optimizer' ); ?>
            </label>
            <ul>
                <li>
                    <a class="megaoptim-optimize-run" data-compression="ultra" href="#"><?php _e( 'Ultra', 'megaoptim-image-optimizer' ); ?></a>
                </li>
                <li>
                    <a class="megaoptim-optimize-run" data-compression="intelligent" href="#"><?php _e( 'Intelligently', 'megaoptim-image-optimizer' ); ?></a>
                </li>
                <li>
                    <a class="megaoptim-optimize-run" data-compression="lossless" href="#"><?php _e( 'Losslessly', 'megaoptim-image-optimizer' ); ?></a>
                </li>
            </ul>
        </div>
	<?php endif; ?>
</div>