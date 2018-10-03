<div id="megaoptim-apikey" class="text-center">
    <h1><?php _e( 'Getting Started', 'megaoptim' ); ?></h1>
    <p><?php _e( 'It seems that you don\'t have API key set up. In order to use MegaOptim you need to obtain free API key which will give you 150 credits per month for free.', 'megaoptim' ); ?></p>
    <p><?php echo sprintf( __( 'If you aren\'t registered yet, click %s to register and get api key.', 'megaoptim' ), '<a href="'.WP_MEGAOPTIM_REGISTER_URL.'">' . __( 'here', 'megaoptim' ) . '</a>' ); ?></p>
    <div>
        <div class="megaoptim-row">
            <input type="text" id="apikey" name="apikey" value="" placeholder="Enter valid api key">
            <a href="" class="button-primary" id="setapikey" data-wrapper=".content-wrapper">Validate</a>
        </div>
    </div>
</div>