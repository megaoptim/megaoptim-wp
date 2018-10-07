<div class="instructions notice">
    <div class="megaoptim-card megaoptim-card-shadow">
        <div class="megaoptim-row megaoptim-header">
            <div class="megaoptim-colf">
                <p class="lead"><?Php _e( 'Thanks for installing', 'megaoptim' ); ?> <strong class="green"><?php _e( 'MegaOptim Image Optimizer', 'megaoptim' ); ?></strong></p>
                <p class="desc"><?php echo sprintf(__('To %s with image optimization follow the steps below.', 'megaoptim'), '<strong>'.__('get started', 'megaoptim').'</strong>'); ?></p>
            </div>
        </div>
        <div class="megaoptim-row">
            <div class="megaoptim-col3">
                <div class="megaoptim-instruction">
                    <h4 class="navy"><?php _e( '1. Obtain API Key', 'megaoptim' ); ?></h4>
                    <p>
						<?php echo sprintf( __( 'The API key is essential in order to use our plugin. Getting free API key will give you %s tokens per month. One token = one image. You can also purcahse larger quota anytime from our %s.', 'megaoptim' ), '<strong>200</strong>', '<a title="'.__('In the dahsboard you can monitor see your optimization reports, api key, open support tickets and much more', 'megaoptim').'" href="https://megaoptim.com/dashboard/api/credentials" target="_blank">' . __( 'dashboard', 'megaoptim' ) . '</a>' ); ?>
                    </p>
                    <p><a target="_blank" href="<?php echo MEGAOPTIM_URL; ?>" class="button button-primary"><?php _e('Get API Key','megaoptim'); ?></a></p>
                </div>
            </div>
            <div class="megaoptim-col3">
                <div class="megaoptim-instruction">
                    <h4 class="navy"><?php _e( '2. Setup your preferences', 'megaoptim' ); ?></h4>
                    <p>
                       <?php echo sprintf(__('On the plugin\'s "Settings" page you need to %s from step 1 and can configure how the plugin behaves. Various options are available like auto-optimization on upload, which image sizes to be optimized, backup settings, etc.', 'megaoptim'), '<strong>'.__('enter the api key','megaoptim').'</strong>'); ?>
                    </p>
                    <p><a href="<?php echo MGO_Admin_UI::get_settings_url(); ?>" class="button button-primary"><?php _e('Go to Settings','megaoptim'); ?></a></p>
                </div>
            </div>
            <div class="megaoptim-col3">
                <div class="megaoptim-instruction">
                    <h4 class="navy"><?php _e( '3. Start Optimizing', 'megaoptim' ); ?></h4>
                    <p>
                        <?php _e('Our plugin support the default WordPress Media Library, NextGEN Library Plugin, the ability to select a custom folders and much more! Run MegaOptim and see the results! We promise your site will be faster.', 'megaoptim'); ?>
                    </p>
                    <p><a href="<?php echo MGO_Admin_UI::get_optimizer_url(); ?>" class="button button-primary"><?php _e('Start Optimizing','megaoptim'); ?></a></p>
                </div>
            </div>
        </div>
        <div class="megaoptim-row">
            <hr/>
        </div>
        <div class="megaoptim-row">
            <div class="megaoptim-colf">
                <div class="megaoptim-extra">
                    <h4 class="navy"><?php _e('Referral program', 'megaoptim'); ?></h4>
                    <p><?php echo sprintf(__('We have a nice referral program available for everyone. Share your %s with your friedns and get %s on each signup and %s when the referral subscribes to any plan.'), '<strong><a target="_blank" href="https://megaoptim.com/dashboard/referral">'.__('referral url').'</a></strong>', '<strong>'.__('120 tokens', 'megaoptim').'</strong>', '<strong>'.__('300 tokens', 'megaoptim').'</strong>');?></p>
                </div>
            </div>
        </div>
        <button type="button" class="notice-dismiss dismiss-megaoptim-notice"><span class="screen-reader-text"><?php _e('Dismiss this notice', 'megaoptim'); ?>.</span></button>
    </div>
</div>