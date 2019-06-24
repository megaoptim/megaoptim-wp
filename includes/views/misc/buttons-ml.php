<?php
/* @var MGO_MediaAttachment $data */

$context = $data::TYPE;
$optimized_thumbnails = $data->get_optimized_thumbnails();
$total_saved_on_thumbnails = $data->get_total_saved_bytes_thumbnails(false);
$total_saved_on_thumbnails = megaoptim_human_file_size($total_saved_on_thumbnails);

$text_optimized_thumbnails = count($optimized_thumbnails['normal']) . ' regular thumbnails';
if (count($optimized_thumbnails['retina']) > 0) {
	$text_optimized_thumbnails .= ' ' . __('and', 'megaoptim') . ' ' . count($optimized_thumbnails['retina']) . ' retina thumbnails';
}
?>

<?php
// Webp
$webp_size = $data->get('webp_size');
$origianl_webp = is_numeric($webp_size) ? $webp_size > 0 : 0;
$thumbnails_webp = 0;
if (isset($optimized_thumbnails['normal'])) {
	foreach ($optimized_thumbnails['normal'] as $thumbnail) {
		if (isset($thumbnail['webp_size']) && $thumbnail['webp_size'] > 0) {
			$thumbnails_webp++;
		}
	}
}
if ($origianl_webp || $thumbnails_webp) {
	$webp_info = '<p class="webp-info"><img src="' . WP_MEGAOPTIM_ASSETS_URL . '/img/check.png" alt="Success" width="12px"/> ';
	$webp_info .= 'WebP Created';
	if ($origianl_webp) {
		$webp_info .= ' for the full size image';
	}
	if ($thumbnails_webp > 0) {
		if ($origianl_webp > 0 && $thumbnails_webp > 0) {
			$webp_info .= ' and';
		}
		$webp_info .= ' for ' . $thumbnails_webp . ' thumbnails.';
	}
	$webp_info .= '</p>';
} else {
	$webp_info = '';
}

?>


<div class="megaoptim-attachment-buttons">
	<?php if ($data->is_already_optimized()): ?>
        <p>
			<?php echo '<strong>' . __('Attachment already optimized, no further optimization needed!', 'megaoptim') . '</strong>'; ?><br/>
			<?php if ($total_saved_on_thumbnails > 0): ?>
				<?php echo sprintf(__('Additionally we optimized its thumbnails, + %s, total saved on thumbnails %s', 'megaoptim'), $text_optimized_thumbnails, $total_saved_on_thumbnails); ?>
			<?php endif; ?>
			<?php echo $webp_info; ?>
        </p>
	<?php elseif ($data->is_optimized()): ?>
        <p style="margin-bottom: 0">
            <strong><?php echo sprintf(__('Success! Image optimized successfully with %s method.', 'megaoptim'), '<u>' . $data->get('compression') . '</u>'); ?></strong>
        </p>
        <p><?php echo sprintf(__('Total saved on the full version %s (-%s). %s optimized, total saved on the thumbnails %s.', 'megaoptim'), $data->get_total_saved_bytes(true), $data->get_saved_percent(true), $text_optimized_thumbnails, $total_saved_on_thumbnails); ?></p>
		<?php echo $webp_info; ?>
		<?php if ($data->has_backup()): ?>
            <p>
                <a data-attachmentid="<?php echo $data->get_id(); ?>" data-context="<?php echo $context; ?>"
                   class="button megaoptim-optimize-restore"><?php _e('Restore', 'megaoptim'); ?></a>
            </p>
		<?php endif; ?>
	<?php elseif ($data->is_locked()): ?>
        <p>
            <a disabled="disabled" class="button megaoptim-optimize megaoptim-optimizing disabled"
               data-context="<?php echo $context; ?>" data-attachmentid="<?php echo $data->get_id(); ?>"><span
                        class="megaoptim-spinner"></span> Optimizing...</a>
        </p>
	<?php else: ?>
        <div class="megaoptim-dropdown megaoptim-optimize megaoptim-optimize-attachment" data-context="<?php echo $context; ?>" data-attachmentid="<?php echo $data->get_id(); ?>">

            <!-- Optimize button start -->
            <input type="checkbox" id="optimize-<?php echo $data->get_id(); ?>" value="" name="optimize-<?php echo $data->get_id(); ?>">
            <label for="optimize-<?php echo $data->get_id(); ?>" class="button-primary" data-toggle="dropdown">
				<?php _e('Optimize', 'megaoptim'); ?>
            </label>
            <ul>
                <li>
                    <a class="megaoptim-optimize-run" data-compression="ultra"
                       href="#"><?php _e('Ultra', 'megaoptim'); ?></a>
                </li>
                <li>
                    <a class="megaoptim-optimize-run" data-compression="intelligent"
                       href="#"><?php _e('Intelligently', 'megaoptim'); ?></a>
                </li>
                <li>
                    <a class="megaoptim-optimize-run" data-compression="lossless"
                       href="#"><?php _e('Losslessly', 'megaoptim'); ?></a>
                </li>
            </ul>
            <!-- Optimize button end -->

			<?php
			$unoptimized_thumbnails = $data->get_unoptimized_thumbnails();
			$optimized_thumbnails = $data->get_optimized_thumbnails();

			$unoptimized_normal_thumbnails_count = count($unoptimized_thumbnails['normal']);
			$optimized_normal_thumbnails_count   = count($optimized_thumbnails['normal']);
			$total_normal_thumbnails_count       = $unoptimized_normal_thumbnails_count + $optimized_normal_thumbnails_count;

			$p_info = '';
			if($unoptimized_normal_thumbnails_count > 0 && $unoptimized_normal_thumbnails_count != $total_normal_thumbnails_count) {
				$p_info .= '<p>'.sprintf(__('%d remaining unoptimized thumbnails of total %d.', 'megaoptim'), $unoptimized_normal_thumbnails_count, $total_normal_thumbnails_count  ) . '</p>';
			}

			$unoptimized_retina_thumbnails_count = count($unoptimized_thumbnails['retina']);
			$optimized_retina_thumbnails_count   = count($optimized_thumbnails['retina']);
			$total_retina_thumbnails_count       = $unoptimized_retina_thumbnails_count + $optimized_retina_thumbnails_count;

			if($unoptimized_retina_thumbnails_count > 0 && $unoptimized_retina_thumbnails_count != $total_retina_thumbnails_count) {
				$p_info .= '<p>'.sprintf(__('%d remaining unoptimized retina thumbnails of total %d.', 'megaoptim'), $unoptimized_retina_thumbnails_count, $total_retina_thumbnails_count  ) . '</p>';
			}

			if(!empty($p_info)) {
				echo $p_info;
			}
			?>

        </div>
	<?php endif; ?>
</div>