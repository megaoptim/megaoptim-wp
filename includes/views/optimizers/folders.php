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
<script>window.megaoptim_attachment_list = []</script>
<div class="megaoptim-postbox">
    <form class="content-wrapper" method="POST" id="megaoptim-folder-toptimizer">
        <div class="megaoptim-middle-content">
			<?php if ( $profile_valid ): ?>
                <div id="megaoptim-folder-picker">
                    <div class="row text-center">
                        <?php if( $tokens > 0 ): ?>
                            <h1><?php _e('Optimize folders', 'megaoptim'); ?></h1>
                            <p><?php _e('On this screen you can optimize your folders that contain images and are outside of the WordPress Media Library or the NextGen Galleries.', 'megaoptim'); ?></p>
                            <p><?php _e(sprintf('Click on "Select custom folder" to choose a folder that contains images or optimize your current theme %s folder. ( Recommended )', '<strong>'.wp_get_theme()->get( 'Name' ).'</strong>'), 'megaoptim'); ?></p>
                            <div class="megaoptim-actions">
                                <p>
                                    <a id="megaoptim-select-folder" href="#sos-dir-select" class="button-primary"><?php _e('Select custom folder', 'megaoptim'); ?></a> <?php _e('or', 'megaoptim'); ?>
                                    <a id="megaoptim-select-current-theme-folder" data-themedir="<?php echo get_template_directory(); ?>" class="button-primary"><?php _e('Scan theme folder', 'megaoptim'); ?></a>
                                </p>
                            </div>
                            <div id="megaoptim-selected-folder" style="display: none;" class="megaoptim-actions">
                            </div>
                        <?php else: ?>
                             <?php echo megaoptim_get_view('parts/out-of-tokens'); ?>
                            <div class="megaoptim-actions">
                                <p>
                                    <a disabled=disabled class="button-primary"><?php _e('Select custom folder', 'megaoptim'); ?></a> <?php _e('or', 'megaoptim'); ?>
                                    <a disabled=disabled class="button-primary"><?php _e('Scan theme folder', 'megaoptim'); ?></a>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div style="display: none;" id="megaoptim-file-optimizer">
                    <div id="megaoptim-stats">
                        <div class="megaoptim-row">
                            <div class="megaoptim-col-4 megaoptim-extra-xs-full">
                                <div class="megaoptim-stats-box">
                                    <div class="megaoptim-stats-square megaoptim-bg-secondary megaoptim-border-primary" id="total_optimized_mixed"></div>
                                    <div class="megaoptim-stats-label">
										<?php _e( 'Images Optimized', 'megaoptim' ); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="megaoptim-col-4 megaoptim-extra-xs-full">
                                <div class="megaoptim-stats-box">
                                    <div class="megaoptim-stats-square megaoptim-bg-secondary megaoptim-border-primary" id="total_remaining"></div>
                                    <div class="megaoptim-stats-label">
										<?php _e( 'Remaining Images', 'megaoptim' ); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="megaoptim-col-4 megaoptim-extra-xs-full">
                                <div class="megaoptim-stats-box">
                                    <div class="megaoptim-stats-square megaoptim-bg-secondary megaoptim-border-primary" id="total_saved_bytes"></div>
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
                                <div class="megaoptim-info"></div>
                                <div class="megaoptim-progress-bar megaoptim-bg-secondary">
                                    <span id="progress_percentage" class="megaoptim-progress-bar-content"></span>
                                    <div id="progress_percentage_bar" class="megaoptim-progress-bar-fill megaoptim-bg-primary" style=""></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="megaoptim-control">
                        <div class="megaoptim-row">
                            <div class="megaoptim-col megaoptim-col-12 text-center">
                                <button id="megaoptim-toggle-optimizer" data-action="megaoptim_optimize_ld_attachment" data-context="<?php echo MGO_LocalFileAttachment::TYPE; ?>" data-next-state="start" data-stop-text="Stop Bulk Optimizer" data-start-text="Start Bulk Optimizer" class="button button-primary button-extra-large">
									<?php _e( 'Start Bulk Optimizer', 'megaoptim' ); ?>
                                </button>
                            </div>
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

<div class="remodal megaoptim-panel megaoptim-modal" id="sos-dir-select" data-remodal-id="sos-dir-select">
    <div class="megaoptim-panel-inner">
        <div class="megaoptim-panel-header">
            <div class="megaoptim-panel-header-inner">
                <h1 class="megaoptim-panel-title"><?php _e('Select Folder', 'megaoptim'); ?></h1>
                <p class="megaoptim-panel-desc">
                    <?php _e('Click on the check icon to select a folder you want to optimize! The icon will be green if you selected a folder correctly.', 'megaoptim'); ?> </p>
            </div>
        </div>
        <div class="megaoptim-panel-body">
            <div class="megaoptim-panel-body-inner">
                <div class="megaoptimdirtree"></div>
            </div>
        </div>
        <div class="megaoptim-panel-footer">
            <div class="megaoptim-panel-footer-inner">
                <button data-remodal-action="cancel" class="megaoptim-btn megaoptim-cancel"><?php _e('Cancel', 'megaoptim'); ?></button>
                <button id="sos-dir-select-action" class="megaoptim-btn megaoptim-ok"><?php _e('OK', 'megaoptim'); ?></button>
            </div>
        </div>
    </div>
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
