<div id="megaoptim-apikey" class="text-center">
    <h1><?php _e( 'Getting Started', 'megaoptim' ); ?></h1>
    <p><strong><?php _e( 'MegaOptim requires API key from MegaOptim.com to work.' ); ?></strong></p>
    <p><?php echo sprintf( 'You can find your api key in the %s. If you are %s yet, register %s, copy the key form the %s, paste it below and hit "%s".', '<a title="'.__('In the dahsboard you can monitor see your optimization reports, api key, open support tickets and much more', 'megaoptim').'" href="https://megaoptim.com/dashboard/api/credentials" target="_blank">' . __( 'MegaOptim Dashboard', 'megaoptim' ) . '</a>', '<strong>' . __( 'not registered', 'megaoptim' ) . '</strong>' , '<a href="' . WP_MEGAOPTIM_REGISTER_URL . '" target="_blank">' . __( 'here', 'megaoptim' ) . '</a>',  '<a title="'.__('In the dahsboard you can monitor see your optimization reports, api key, open support tickets and much more', 'megaoptim').'" href="https://megaoptim.com/dashboard/api/credentials" target="_blank">' . __( 'dashboard', 'megaoptim' ) . '</a>', '<strong>'.__('Validate your API key', 'megaoptim').'</strong>' ); ?></p>
    <div>
        <div class="megaoptim-row">
            <p>
                <input type="text" id="apikey" name="apikey" value="" placeholder="Enter valid api key" size="55">
            </p>
        </div>
        <div class="megaoptim-row">
            <p>
                <a href="#" class="button-primary" id="setapikey" data-wrapper=".content-wrapper"><?php _e('Validate your API key', 'megaoptim'); ?></a>
                <a href="#" target="_blank" id="megaoptim-trigger-register" data-remodal-target="megaoptim-register" class="button"><?php _e('Register for free API Key', 'megaoptim'); ?></a> <?php _e('or', 'megaoptim'); ?> <a target="_blank" href="https://megaoptim.com/register"><?php _e('register here', 'megaoptim'); ?></a>
            </p>
        </div>
    </div>
</div>