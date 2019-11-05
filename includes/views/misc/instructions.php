<div class="instructions notice">
    <div class="megaoptim-card megaoptim-card-shadow">
        <div class="megaoptim-row megaoptim-header">
            <div class="megaoptim-colf">
                <p class="lead"><?Php _e( 'Thanks for installing', 'megaoptim' ); ?> <strong
                            class="green"><?php _e( 'MegaOptim Image Optimizer', 'megaoptim' ); ?></strong></p>
                <p class="desc"><?php echo sprintf( __( 'To %s with image optimization follow the steps below.', 'megaoptim' ), '<strong>' . __( 'get started', 'megaoptim' ) . '</strong>' ); ?></p>
            </div>
        </div>
        <div class="megaoptim-row">
            <div class="megaoptim-col3">
                <div class="megaoptim-instruction">
                    <h4 class="navy"><?php _e( '1. Get FREE API key', 'megaoptim' ); ?></h4>
                    <p>
						<?php

						$title = __( 'In the dahsboard you can monitor see your optimization reports, api key, open support tickets and much more', 'megaoptim' );

						echo sprintf( __( 'In order to use MegaOptim you will need %s. You can register for %s that comes with %s per month for %s. If you need larger quota you can get our premium %s plan for only %s or any other from our %s.', 'megaoptim' ),
							'<strong>API KEY</strong>',
							'<strong>FREE API KEY</strong>',
							'<strong>500 tokens</strong>',
							'<strong>FREE</strong>',
							'<strong>UNLIMITED</strong>',
							'<strong>$9.99/month</strong>',
							'<a title="' . $title . '" href="https://megaoptim.com/pricing" target="_blank">' . __( 'website', 'megaoptim' ) . '</a>' );


						?>
                    </p>
                    <p><a target="_blank"
                          id="megaoptim-trigger-register"
                          data-remodal-target="megaoptim-register"
                          class="button button-primary"><?php _e( 'Get API Key', 'megaoptim' ); ?></a> <?php _e('or', 'megaoptim'); ?> <a
                                href="<?php echo WP_MEGAOPTIM_REGISTER_URL; ?>" target="_blank"><?php _e('register here', 'megaoptim'); ?></a></p>
                </div>
            </div>
            <div class="megaoptim-col3">
                <div class="megaoptim-instruction">
                    <h4 class="navy"><?php _e( '2. Set your API Key', 'megaoptim' ); ?></h4>
                    <p>
						<?php echo sprintf( __( 'On the "Settings" page you need to %s from step 1 and you can configure how the plugin behaves. Various options are available like auto-optimization on upload, which image sizes to be optimized, backup settings, etc.', 'megaoptim' ), '<strong><u>' . __( 'enter the api key', 'megaoptim' ) . '</u></strong>' ); ?>
                    </p>
                    <p>
                        <a data-remodal-target="megaoptim-setapikey" class="button button-primary"><?php _e( 'Set API Key', 'megaoptim' ); ?></a>
	                    <?php _e('or', 'megaoptim'); ?>
                        <a href="<?php echo MGO_Admin_UI::get_settings_url(); ?>" target="_blank"><?php _e('verify preferences', 'megaoptim'); ?></a>
                    </p>
                </div>
            </div>
            <div class="megaoptim-col3">
                <div class="megaoptim-instruction">
                    <h4 class="navy"><?php _e( '3. Start Optimizing', 'megaoptim' ); ?></h4>
                    <p>
						<?php echo sprintf( __( 'You can optimize your %s . The plugin supports bulk mode, one by one from the Media page in the admin or auto optimization after the upload. Auto optimization is turned on by default.', 'megaoptim' ), '<strong>' . __( 'Media Library, NextGen Galleries or Local Folders', 'megaoptim' ) . '</strong>' ); ?>
                    </p>
                    <p><a href="<?php echo MGO_Admin_UI::get_optimizer_url(); ?>"
                          class="button button-primary"><?php _e( 'Start Optimizing', 'megaoptim' ); ?></a></p>
                </div>
            </div>
        </div>
        <div class="megaoptim-row">
            <hr/>
        </div>
        <div class="megaoptim-row megaoptim-mb-10">
            <div class="megaoptim-colf">
                <div class="megaoptim-extra">
                    <h4 class="navy"><?php _e( 'Referral program', 'megaoptim' ); ?></h4>
                    <p><?php echo sprintf( __( 'We have a referral program available for everyone. Help us spread by sharing your %s with your friends and get %s on each signup and %s when the referral subscribes to any plan.' ), '<strong><a target="_blank" href="https://app.megaoptim.com/referral">' . __( 'referral url' ) . '</a></strong>', '<strong>' . __( '120 tokens', 'megaoptim' ) . '</strong>', '<strong>' . __( '500 tokens', 'megaoptim' ) . '</strong>' ); ?></p>
                </div>
            </div>
        </div>
        <button type="button" class="notice-dismiss dismiss-megaoptim-notice"><span class="screen-reader-text"><?php _e( 'Dismiss this notice', 'megaoptim' ); ?>.</span></button>
    </div>
</div>
<?php megaoptim_view('modals/setapikey'); ?>