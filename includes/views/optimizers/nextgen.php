<?php
/********************************************************************
 * Copyright (C) 2017 Darko Gjorgjijoski (http://darkog.com)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 **********************************************************************/

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access is not allowed.' );
}
/* @var array $stats */
/* @var MGO_Profile $profile */
$settings_url  = admin_url( "admin.php?page=megaoptim_settings" );
$profile_valid = $profile instanceof MGO_Profile && ( $profile->has_api_key() && $profile->is_valid_apikey() );
$tokens = $profile->get_tokens_count();
?>
<script>window.megaoptim_attachment_list = <?php echo json_encode( $stats->remaining ); ?></script>
<div class="megaoptim-postbox">
    <form class="content-wrapper" method="POST" id="megaoptim-report-export">
        <div class="megaoptim-middle-content">
			<?php if ( $profile_valid ): ?>

				<?php if ( ! $stats->empty_gallery ): ?>
                    <div id="megaoptim-stats">
                        <div class="megaoptim-row">
                            <div class="megaoptim-col-4 megaoptim-extra-xs-full">
                                <div class="megaoptim-stats-box">
                                    <div class="megaoptim-stats-square megaoptim-bg-secondary megaoptim-border-primary" id="total_optimized_mixed">
										<?php echo isset( $stats->total_optimized_mixed ) ? $stats->total_optimized_mixed : 0; ?>
                                    </div>
                                    <div class="megaoptim-stats-label">
										<?php _e( 'Images Optimized', 'megaoptim' ); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="megaoptim-col-4 megaoptim-extra-xs-full">
                                <div class="megaoptim-stats-box">
                                    <div class="megaoptim-stats-square megaoptim-bg-secondary megaoptim-border-primary" id="total_remaining">
										<?php echo isset( $stats->total_remaining ) ? $stats->total_remaining : 0; ?>
                                    </div>
                                    <div class="megaoptim-stats-label">
										<?php _e( 'Remaining Images', 'megaoptim' ); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="megaoptim-col-4 megaoptim-extra-xs-full">
                                <div class="megaoptim-stats-box">
                                    <div class="megaoptim-stats-square megaoptim-bg-secondary megaoptim-border-primary" id="total_saved_bytes">
										<?php echo $stats->total_saved_bytes_human; ?>
                                    </div>
                                    <div class="megaoptim-stats-label">
										<?Php _e( 'Total saved MB', 'megaoptim' ); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
				<?php else: ?>
                    <div id="megaoptim-stats">
                        <div class="megaoptim-row">
                            <div class="megaoptim-col megaoptim-col-12 text-center">
                                <h1><?php _e( 'No media attachments found!', 'megaoptim' ); ?></h1>
                            </div>
                        </div>
                    </div>
				<?php endif; ?>
                <div id="megaoptim-progress-bar">
                    <div class="megaoptim-row">
                        <div class="megaoptim-col megaoptim-col-12">
                            <div class="megaoptim-info">
                                <?php if ( $tokens > 0 ): ?>
    								<?php if ( $stats->empty_gallery ): ?>
                                        <p class="text-center"><?php _e( 'It seems that all your nextgen attachments are optimized, once you upload some attachments come back on this page if you don\'t have Auto Optimize option enabled so you can optimize them all from there.', 'megaoptim' ); ?></p>
    								<?php elseif ( $stats->total_optimized_mixed > 0 && $stats->total_remaining === 0 ): ?>
                                        <p class="text-center"><strong><?php _e( 'Congratulations', 'megaoptim' ); ?>
                                                !</strong> <?php _e( 'Your nextgen images are fully optimized, please come back later when new attachments are available for optimization.', 'megaoptim' ); ?>
                                        </p>
    								<?php else: ?>
                                        <p>
    										<?php echo sprintf( __( 'On this screen you can optimize all of your nextgen attachments which will result in significant speed boost, better SEO and other benefits. Make sure you check the %s once more if you haven\'t still to make sure it\'s all set up for your needs.', 'megaoptim' ), '<a target="_blank" href="' . $settings_url . '">' . __( 'Settings page', 'megaoptim' ) . '</a>' ); ?>
                                        <p>
                                            <strong><?php _e( 'Important:', 'megaoptim' ); ?></strong> <?php _e( 'In order the plugin to work, you need to keep the tab open, you can always open a new tab and continue in that tab. If you close this tab the optimizer will stop but don\'t worry, you can always continue later where you left off.', 'megaoptim' ); ?>
                                        </p>
    								<?php endif; ?>
                                <?php else: ?>
                                
                                    <?php echo megaoptim_get_view('parts/out-of-tokens'); ?>                               
                                
                                <?php endif; ?>
                            </div>
                            <?php if ( ! $stats->empty_gallery ): ?>
                                <div class="megaoptim-progress-bar megaoptim-bg-secondary">
                                    <span class="megaoptim-progress-bar-content"><?php echo $stats->total_optimized_mixed_percentage; ?>%</span>
                                    <div class="megaoptim-progress-bar-fill megaoptim-bg-primary" style="width: <?php echo $stats->total_optimized_mixed_percentage; ?>%;"></div>
                                </div>
							<?php endif; ?>

                        </div>
                    </div>
                </div>
                <div id="megaoptim-control">
                    <div class="megaoptim-row">
                        <div class="megaoptim-col megaoptim-col-12 text-center">
                            <?php if($tokens>0): ?>
    							<?php if ( $stats->total_remaining > 0 ): ?>
                                    <button id="megaoptim-toggle-optimizer" data-action="megaoptim_ngg_optimize_attachment" data-context="<?php echo MGO_NextGenAttachment::TYPE; ?>" data-next-state="start" data-stop-text="Stop Bulk Optimizer" data-start-text="Start Bulk Optimizer" class="button button-primary button-extra-large">
    									<?php _e( 'Start Bulk Optimizer', 'megaoptim' ); ?>
                                    </button>
    							<?php endif; ?>
                            <?php else: ?>
                                <button disabled="disabled" class="button button-primary button-extra-large" ><?php _e( 'Start Bulk Optimizer', 'megaoptim' ); ?></button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
			<?php else: ?>
                <div id="megaoptim-error">
                    <div class="megaoptim-row">
                        <div class="megaoptim-col-12">
							<?php echo megaoptim_get_view( 'settings/key' ); ?>
                        </div>
                    </div>
                </div>
			<?php endif; ?>
        </div>
    </form>
</div>

<?php if ( $profile_valid && ( $stats->total_remaining > 0 || ! $stats->empty_gallery ) ): ?>
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
<?php endif; ?>
