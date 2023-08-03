<div class="instructions notice">
    <div class="megaoptim-card megaoptim-card-shadow">
        <div class="megaoptim-row megaoptim-header">
            <div class="megaoptim-colf">
                <p class="lead"><?Php _e( 'Thanks for installing', 'megaoptim-image-optimizer' ); ?> <strong
                            class="green"><?php _e( 'MegaOptim Image Optimizer', 'megaoptim-image-optimizer' ); ?></strong></p>
                <p class="desc"><?php echo sprintf( __( 'To %s with image optimization follow the steps below.', 'megaoptim-image-optimizer' ), '<strong>' . __( 'get started', 'megaoptim-image-optimizer' ) . '</strong>' ); ?></p>
            </div>
        </div>
        <div class="megaoptim-row">
            <div class="megaoptim-col3">
                <div class="megaoptim-instruction">
                    <h4 class="navy"><?php _e( '1. Get FREE API key', 'megaoptim-image-optimizer' ); ?></h4>
                    <p>
						<?php

						$title = __( 'In the dahsboard you can monitor see your optimization reports, api key, open support tickets and much more', 'megaoptim-image-optimizer' );

						echo sprintf( __( 'In order to use MegaOptim you will need %s. You can register for %s that comes with %s per month for %s. If you need larger quota you can get our premium %s plan for only %s or any other from our %s.', 'megaoptim-image-optimizer' ),
							'<strong>API KEY</strong>',
							'<strong>FREE API KEY</strong>',
							'<strong>750 tokens</strong>',
							'<strong>FREE</strong>',
							'<strong>UNLIMITED</strong>',
							'<strong>$9.99/month</strong>',
							'<a title="' . $title . '" href="https://megaoptim.com/pricing" target="_blank">' . __( 'website', 'megaoptim-image-optimizer' ) . '</a>' );


						?>
                    </p>
                    <p><a target="_blank"
                          id="megaoptim-trigger-register"
                          data-remodal-target="megaoptim-register"
                          class="button button-primary"><?php _e( 'Get API Key', 'megaoptim-image-optimizer' ); ?></a> <?php _e('or', 'megaoptim-image-optimizer'); ?> <a
                                href="<?php echo WP_MEGAOPTIM_REGISTER_URL; ?>" target="_blank"><?php _e('register here', 'megaoptim-image-optimizer'); ?></a></p>
                </div>
            </div>
            <div class="megaoptim-col3">
                <div class="megaoptim-instruction">
                    <h4 class="navy"><?php _e( '2. Set your API Key', 'megaoptim-image-optimizer' ); ?></h4>
                    <p>
						<?php echo sprintf( __( 'On the "Settings" page you need to %s from step 1 and you can configure how the plugin behaves. Various options are available like auto-optimization on upload, which image sizes to be optimized, backup settings, etc.', 'megaoptim-image-optimizer' ), '<strong><u>' . __( 'enter the api key', 'megaoptim-image-optimizer' ) . '</u></strong>' ); ?>
                    </p>
                    <p>
                        <a data-remodal-target="megaoptim-setapikey" class="button button-primary"><?php _e( 'Set API Key', 'megaoptim-image-optimizer' ); ?></a>
	                    <?php _e('or', 'megaoptim-image-optimizer'); ?>
                        <a href="<?php echo MGO_Admin_UI::get_settings_url(); ?>" target="_blank"><?php _e('verify preferences', 'megaoptim-image-optimizer'); ?></a>
                    </p>
                </div>
            </div>
            <div class="megaoptim-col3">
                <div class="megaoptim-instruction">
                    <h4 class="navy"><?php _e( '3. Start Optimizing', 'megaoptim-image-optimizer' ); ?></h4>
                    <p>
						<?php echo sprintf( __( 'You can optimize your %s . The plugin supports bulk mode, one by one from the Media page in the admin or auto optimization after the upload. Auto optimization is turned on by default.', 'megaoptim-image-optimizer' ), '<strong>' . __( 'Media Library, NextGen Galleries or Local Folders', 'megaoptim-image-optimizer' ) . '</strong>' ); ?>
                    </p>
                    <p><a href="<?php echo MGO_Admin_UI::get_optimizer_url(); ?>"
                          class="button button-primary"><?php _e( 'Start Optimizing', 'megaoptim-image-optimizer' ); ?></a></p>
                </div>
            </div>
        </div>
        <div class="megaoptim-row">
            <hr/>
        </div>
        <div class="megaoptim-row megaoptim-mb-10">
            <div class="megaoptim-colf">
                <div class="megaoptim-extra">
                    <h4 class="navy"><?php _e( 'Developer Happiness', 'megaoptim-image-optimizer' ); ?> :)</h4>
                    <p><?php echo sprintf(__('If you are developer or you already know about WP CLI, click %s to read more about how to use our new WP CLI commands to optimize images from your command-line interface.', 'megaoptim-image-optimizer'), '<a target="_blank" href="http://megaoptim.com/wpcli">'.__('here', 'megaoptim-image-optimizer').'</a>'); ?></p>
                </div>
            </div>
        </div>
        <button type="button" class="notice-dismiss dismiss-megaoptim-notice"><span class="screen-reader-text"><?php _e( 'Dismiss this notice', 'megaoptim-image-optimizer' ); ?>.</span></button>
    </div>
</div>
<?php megaoptim_view('modals/setapikey'); ?>