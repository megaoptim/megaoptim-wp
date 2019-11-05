<?php
$profile      = new MGO_Profile();
$is_connected = $profile->is_connected();
?>

<div class="remodal megaoptim-panel megaoptim-modal" id="megaoptim-apikey-modal" data-remodal-id="megaoptim-setapikey">
    <form class="megaoptim-modal-form megaoptim-apikey-form" method="POST">
        <div class="megaoptim-panel-header">
            <div class="megaoptim-panel-header-inner">
                <h1 class="megaoptim-panel-title"><?php _e( 'Connect to MegaOptim', 'megaoptim' ); ?></h1>
                <p class="megaoptim-panel-desc">
					<?php _e( 'If you already have MegaOptim API Key please enter it below', 'megaoptim' ); ?> </p>
            </div>
        </div>
        <div class="megaoptim-panel-body">
            <div class="megaoptim-panel-body-inner">

                <div class="megaoptim-modal-status" style="display: none;"></div>

                <div class="mgo-form-group" id="mgo-apikey">
					<?php if ( ! $is_connected ): ?>
                        <label for="apikey"><?php _e( 'API KEY', 'megaoptim' ); ?></label>
                        <input type="text"
                               id="apikey"
                               name="apikey"
                               value="<?php echo MGO_Settings::instance()->getApiKey(); ?>"
                               placeholder="<?php _e( 'Enter api key', 'megaoptim' ); ?>"/>
					<?php else: ?>
                        <p class="megaoptim-mb-0 megaoptim-mt-5 megaoptim-status-line megaoptim-status-line-success"><?php _e( 'You are already connected', 'megaoptim' ); ?></p>
					<?php endif; ?>
                </div>
            </div>
        </div>
        <div class="megaoptim-panel-footer">
            <div class="megaoptim-panel-footer-inner">
                <button data-remodal-action="cancel"
                        class="megaoptim-btn megaoptim-cancel"><?php _e( 'Cancel', 'megaoptim' ); ?></button>
				<?php if ( !$is_connected ): ?>
                    <button type="submit" class="megaoptim-btn megaoptim-ok"><?php _e( 'OK', 'megaoptim' ); ?></button>
				<?php endif; ?>
            </div>
        </div>
    </form>
</div>