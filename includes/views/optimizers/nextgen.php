<?php
/********************************************************************
 * Copyright (C) 2018 MegaOptim (https://megaoptim.com)
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
/* @var MGO_Profile $profile */
$settings_url     = admin_url( "admin.php?page=megaoptim_settings" );
$is_profile_valid = $profile instanceof MGO_Profile && ( $profile->has_api_key() && $profile->is_valid_apikey() );
$tokens           = $profile->get_tokens_count();

?>
<script>window.megaoptim_attachment_list = ''</script>
<div class="megaoptim-postbox">
	<form class="content-wrapper" method="POST" id="megaoptim-report-export">
		<div class="megaoptim-middle-content">
			<?php if ( $is_profile_valid && $tokens > 0 ): ?>
				<div id="megaoptim-optimizer-scan" class="text-center">
					<h1><?php _e( 'Scan for unoptimized images', 'megaoptim' ); ?></h1>
					<P class="megaoptim-mb-20 megaoptim-mt-20"><?php _e( sprintf( 'Click on the button below to scan your %s for unoptimized images. If images are found you will be able to optimize them by clicking Start button.', '<strong>' . __( 'NextGen Galleries', 'megaoptim' ) . '</strong>' ), 'megaoptim' ); ?></P>
					<div id="megaoptim-control">
						<div class="megaoptim-row">
							<div class="megaoptim-col megaoptim-col-12 text-center">
								<button id="megaoptim-scan-library" class="button button-primary button-extra-large" data-context="<?php echo MGO_NextGenAttachment::TYPE; ?>">
									<?php _e( 'Start now', 'megaoptim' ); ?>
							</div>
						</div>
					</div>
				</div>
				<div style="display: none;" id="megaoptim-optimizer-wrapper">
					<div id="megaoptim-stats">
						<div class="megaoptim-row">
							<div class="megaoptim-col-4 megaoptim-extra-xs-full">
								<div class="megaoptim-stats-box">
									<div class="megaoptim-stats-square megaoptim-bg-secondary megaoptim-border-primary" id="total_optimized">
										0
									</div>
									<div class="megaoptim-stats-label">
										<?php _e( 'Images Optimized', 'megaoptim' ); ?>
									</div>
								</div>
							</div>
							<div class="megaoptim-col-4 megaoptim-extra-xs-full">
								<div class="megaoptim-stats-box">
									<div class="megaoptim-stats-square megaoptim-bg-secondary megaoptim-border-primary" id="total_remaining">
										0
									</div>
									<div class="megaoptim-stats-label">
										<?php _e( 'Remaining Images', 'megaoptim' ); ?>
									</div>
								</div>
							</div>
							<div class="megaoptim-col-4 megaoptim-extra-xs-full">
								<div class="megaoptim-stats-box">
									<div class="megaoptim-stats-square megaoptim-bg-secondary megaoptim-border-primary" id="total_saved_bytes">
										0
									</div>
									<div class="megaoptim-stats-label">
										<?Php _e( 'Total saved MB', 'megaoptim' ); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div id="megaoptim-progress-bar">
						<div class="megaoptim-row">
							<div class="megaoptim-col megaoptim-col-12">
								<div class="megaoptim-info">
									<p>
										<strong><?php _e( 'Important:', 'megaoptim' ); ?></strong> <?php _e( sprintf( 'In order to keep the optimizer running, you need to keep the tab open, you can always open a %s and continue in that tab.', '<a href="' . admin_url() . '" target="_blank">new tab</a>' ), 'megaoptim' ); ?>
									</p>
									<p>
										<?php _e('If you stop/close the optimizer you can continue later from where you stopped.', 'megaoptim'); ?>
									</p>
								</div>
								<div id="megaoptim-running-spinner" class="text-center" style="display: none;">
									<div class="mgo-spinner spinner-1 mgo-spinner-navy"></div>
								</div>
								<div class="megaoptim-progress-bar megaoptim-bg-secondary">
									<span id="progress_percentage" class="megaoptim-progress-bar-content">0%</span>
									<div id="progress_percentage_bar" class="megaoptim-progress-bar-fill megaoptim-bg-primary" style="width: 0%;"></div>
								</div>
							</div>
						</div>
					</div>
					<div id="megaoptim-control">
						<div class="megaoptim-row">
							<div class="megaoptim-col megaoptim-col-12 text-center">
								<button id="megaoptim-toggle-optimizer" data-action="megaoptim_ngg_optimize_attachment" data-context="<?php echo MGO_NextGenAttachment::TYPE; ?>" data-next-state="start" data-stop-text="Stop Bulk Optimizer" data-start-text="Start Bulk Optimizer" class="button button-primary button-extra-large"><?php _e( 'Start Bulk Optimizer', 'megaoptim' ); ?></button>
							</div>
						</div>
					</div>
				</div>
			<?php elseif ( $is_profile_valid && $tokens <= 0 ): ?>
				<div id="megaoptim-error">
					<div class="megaoptim-row">
						<div class="megaoptim-col-12">
							<?php megaoptim_view( 'parts/out-of-tokens.php' ); ?>
						</div>
					</div>
				</div>
			<?php else: ?>
				<div id="megaoptim-error">
					<div class="megaoptim-row">
						<div class="megaoptim-col-12">
							<?php megaoptim_view( 'settings/key' ); ?>
						</div>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</form>
</div>

<div id="megaoptim-results">
	<table id="megaoptim-results-table" class="megaoptim-table wp-list-table widefat fixed striped media">
		<thead>
		<tr>
			<th class="thumbnail"></th>
			<th class="column-primary"><?php _e( 'Name', 'megaoptim' ); ?></th>
			<th class="column"><?php _e( 'Original Size', 'megaoptim' ); ?></th>
			<th class="column"><?php _e( 'After Compression', 'megaoptim' ); ?></th>
			<th class="column"><?php _e( 'Reduced By', 'megaoptim' ); ?></th>
			<th class="column"><?php _e( 'Savings', 'megaoptim' ); ?></th>
			<th class="column"><?php _e( 'Thumbnails', 'megaoptim' ); ?></th>
			<th class="column"><?php _e( 'Status', 'megaoptim' ); ?></th>
		</tr>
		</thead>
		<tbody>

		</tbody>
	</table>
</div>