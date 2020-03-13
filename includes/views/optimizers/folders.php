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
$settings_url  = admin_url( "admin.php?page=megaoptim_settings" );
$is_profile_valid = $profile instanceof MGO_Profile && ( $profile->has_api_key() && $profile->is_valid_apikey() );
$tokens           = $is_profile_valid ? $profile->get_tokens_count() : 0;


?>
<script>window.megaoptim_attachment_list = []</script>
<div class="megaoptim-postbox">
    <form class="content-wrapper" method="POST" id="megaoptim-folder-toptimizer">
        <div class="megaoptim-middle-content">
			<?php if ( $is_profile_valid ): ?>
				<?php if( $tokens == -1 || $tokens > 0 ): ?>
                    <div id="megaoptim-folder-picker">
                        <div class="row text-center">
                            <h3><?php _e('Optimize folders', 'megaoptim-image-optimizer'); ?></h3>
                            <p><?php _e('On this screen you can optimize your folders that contain images and are outside of the WordPress Media Library or the NextGen Galleries.', 'megaoptim-image-optimizer'); ?></p>
                            <p><?php _e(sprintf('Click on "Select custom folder" to choose a folder that contains images or optimize your current theme %s folder. (Recommended)', '<strong>'.wp_get_theme()->get( 'Name' ).'</strong>'), 'megaoptim-image-optimizer'); ?></p>
                            <div class="megaoptim-actions">
                                <p>
									<?php if(is_child_theme()): ?>
                                        <a id="megaoptim-select-parent-theme-folder" class="button-primary megaoptim-optimize-theme-folder" data-themedir="<?php echo get_template_directory(); ?>" class="button-primary"><?php _e('Scan parent theme folder', 'megaoptim-image-optimizer'); ?></a>
                                        <a id="megaoptim-select-current-theme-folder" class="button-primary megaoptim-optimize-theme-folder" data-themedir="<?php echo get_stylesheet_directory(); ?>" class="button-primary"><?php _e('Scan child theme folder', 'megaoptim-image-optimizer'); ?></a>
									<?php else: ?>
                                        <a id="megaoptim-select-current-theme-folder" class="button-primary megaoptim-optimize-theme-folder" data-themedir="<?php echo get_template_directory(); ?>" class="button-primary"><?php _e('Scan theme folder', 'megaoptim-image-optimizer'); ?></a>
									<?php endif; ?>
                                    <a id="megaoptim-select-folder" href="#" class="button-primary"><?php _e('Select custom folder', 'megaoptim-image-optimizer'); ?></a>
                                </p>
                            </div>
                            <div id="megaoptim-selected-folder" style="display: none;" class="megaoptim-actions"></div>
                        </div>
                    </div>
				<?php else: ?>

                    <?php echo megaoptim_get_view('parts/out-of-tokens'); ?>
                    <div class="megaoptim-actions">
                        <p>
                            <a disabled=disabled class="button-primary"><?php _e('Select custom folder', 'megaoptim-image-optimizer'); ?></a>
                            <a disabled=disabled class="button-primary"><?php _e('Scan theme folder', 'megaoptim-image-optimizer'); ?></a>
                        </p>
                    </div>
                <?php endif; ?>

                <div style="display: none;" id="megaoptim-file-optimizer">
                    <div id="megaoptim-stats">
                        <div class="megaoptim-row">
                            <div class="megaoptim-col-4 megaoptim-extra-xs-full">
                                <div class="megaoptim-stats-box">
                                    <div class="megaoptim-stats-square megaoptim-bg-secondary megaoptim-border-primary" id="total_optimized">
                                        0
                                    </div>
                                    <div class="megaoptim-stats-label">
										<?php _e( 'Images Optimized', 'megaoptim-image-optimizer' ); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="megaoptim-col-4 megaoptim-extra-xs-full">
                                <div class="megaoptim-stats-box">
                                    <div class="megaoptim-stats-square megaoptim-bg-secondary megaoptim-border-primary" id="total_remaining">0</div>
                                    <div class="megaoptim-stats-label">
										<?php _e( 'Remaining Images', 'megaoptim-image-optimizer' ); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="megaoptim-col-4 megaoptim-extra-xs-full">
                                <div class="megaoptim-stats-box">
                                    <div class="megaoptim-stats-square megaoptim-bg-secondary megaoptim-border-primary" id="total_saved_bytes">0</div>
                                    <div class="megaoptim-stats-label">
										<?Php _e( 'Total saved MB', 'megaoptim-image-optimizer' ); ?>
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
                                <button id="megaoptim-toggle-optimizer" data-action="megaoptim_optimize_ld_attachment" data-context="<?php echo MEGAOPTIM_MODULE_MEDIA_LIBRARY; ?>" data-next-state="start" data-stop-text="Stop Bulk Optimizer" data-start-text="Start Bulk Optimizer" class="button button-primary button-extra-large">
									<?php _e( 'Start Bulk Optimizer', 'megaoptim-image-optimizer' ); ?>
                                </button>
                                <p><a class="megaoptim-cancel-optimizer" href="<?php echo MGO_Admin_UI::get_optimizer_url(MEGAOPTIM_MODULE_FOLDERS); ?>"><?php _e( 'Cancel', 'megaoptim-image-optimizer' ); ?></a></p>
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

<?php megaoptim_view('modals/folders'); ?>

<div id="megaoptim-results">
    <table id="megaoptim-results-table" class="megaoptim-table wp-list-table widefat fixed striped media">
        <thead>
        <tr>
            <th class="thumbnail"></th>
            <th class="column-primary"><?php _e( 'Name', 'megaoptim-image-optimizer' ); ?></th>
            <th class="column"><?php _e( 'Original Size', 'megaoptim-image-optimizer' ); ?></th>
            <th class="column"><?php _e( 'After Compression', 'megaoptim-image-optimizer' ); ?></th>
            <th class="column"><?php _e( 'Reduced By', 'megaoptim-image-optimizer' ); ?></th>
            <th class="column"><?php _e( 'Savings', 'megaoptim-image-optimizer' ); ?></th>
            <th class="column"><?php _e( 'Thumbnails', 'megaoptim-image-optimizer' ); ?></th>
            <th class="column"><?php _e( 'Status', 'megaoptim-image-optimizer' ); ?></th>
        </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
</div>
