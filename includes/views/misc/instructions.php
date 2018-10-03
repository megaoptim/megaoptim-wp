<div class="instructions notice">
    <div class="megaoptim-card megaoptim-card-shadow">
        <div class="megaoptim-row megaoptim-header">
            <div class="megaoptim-colf">
                <p><?Php _e( 'Thanks for installing', 'megaoptim' ); ?> <strong class="green"><?php _e( 'MegaOptim Image Optimizer', 'megaoptim' ); ?></strong></p>
            </div>
        </div>
        <div class="megaoptim-row">
            <div class="megaoptim-col3">
                <div class="megaoptim-instruction">
                    <h4 class="navy"><?php _e( '1. Obtain API Key', 'megaoptim' ); ?></h4>
                    <p>
						<?php echo sprintf( __( 'The API key is essential in order to use our plugin. Obtaining free API key will give you %s tokens per month. One token = one image. You can also purcahse larger quota anytime from our dashboard.', 'megaoptim' ), '<strong>200</strong>' ); ?>
                    </p>
                    <p><a target="_blank" href="<?php echo MEGAOPTIM_URL; ?>" class="button button-primary"><?php _e('Get API Key','megaoptim'); ?></a></p>
                </div>
            </div>
            <div class="megaoptim-col3">
                <div class="megaoptim-instruction">
                    <h4 class="navy"><?php _e( '2. Setup your preferences', 'megaoptim' ); ?></h4>
                    <p>
                       <?php _e('On the plugin\'s "Settings" page you can configure how the plugin behaves. Various options available like auto-upload, which image sizes to be optimized, backup settings, etc.', 'megaoptim'); ?>
                    </p>
                    <p><a href="<?php echo MGO_Admin_UI::get_settings_url(); ?>" class="button button-primary"><?php _e('Go to Settings','megaoptim'); ?></a></p>
                </div>
            </div>
            <div class="megaoptim-col3">
                <div class="megaoptim-instruction">
                    <h4 class="navy"><?php _e( '3. Optimize Images', 'megaoptim' ); ?></h4>
                    <p>
                        <?php _e('Our plugin support the default WordPress Media Library, NextGEN Library Plugin, the ability to select a custom folders and much more! Run MegaOptim and see the results! We promise your site will be faster.', 'megaoptim'); ?>
                    </p>
                    <p><a href="<?php echo MGO_Admin_UI::get_optimizer_url(); ?>" class="button button-primary"><?php _e('Start Optimizing','megaoptim'); ?></a></p>
                </div>
            </div>
        </div>
        <button type="button" class="notice-dismiss dismiss-megaoptim-notice"><span class="screen-reader-text"><?php _e('Dismiss this notice', 'megaoptim'); ?>.</span></button>
    </div>
</div>