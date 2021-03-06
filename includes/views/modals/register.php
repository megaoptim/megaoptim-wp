<?php
$step = 1;

$is_pending = megaoptim_is_registration_pending();

if ( $is_pending ) {
	$step = 2;
}

$api_key = MGO_Settings::instance()->getApiKey();

if ( ! empty( $api_key ) ) {
	$step = 3;
}
?>

<div class="remodal megaoptim-panel megaoptim-modal" id="megaoptim-register" style="display: none;" data-remodal-id="megaoptim-register">
    <form method="POST" id="megaoptim-register-form" class="megaoptim-panel-inner" data-step="<?php echo $step; ?>">
        <div class="megaoptim-panel-header">
            <div class="megaoptim-panel-header-inner">
                <h3 class="megaoptim-panel-title"><?php _e( 'Create MegaOptim Account', 'megaoptim-image-optimizer' ); ?></h3>
                <p class="megaoptim-panel-desc">
					<?php _e( 'Obtain MegaOptim api key and start optimizing your galleries.', 'megaoptim-image-optimizer' ); ?> </p>
            </div>
        </div>
        <div class="megaoptim-panel-body">
            <div class="megaoptim-panel-body-inner">
                <div id="megaoptim-register-form-step1" style="<?php echo $step === 1 ? 'display:block;' : 'display:none;'; ?>">
                    <div class="mgo-form-group" id="mgo-first_name">
                        <label><?php _e( 'First Name', 'megaoptim-image-optimizer' ); ?></label>
                        <input type="text" name="first_name" placeholder="<?php _e( 'Enter first name', 'megaoptim-image-optimizer' ); ?>"/>
                    </div>
                    <div class="mgo-form-group" id="mgo-last_name">
                        <label><?php _e( 'Last Name', 'megaoptim-image-optimizer' ); ?></label>
                        <input type="text" name="last_name" placeholder="<?php _e( 'Enter last name', 'megaoptim-image-optimizer' ); ?>"/>
                    </div>
                    <div class="mgo-form-group" id="mgo-email">
                        <label><?php _e( 'E-mail Address', 'megaoptim-image-optimizer' ); ?></label>
                        <input type="text" name="email" placeholder="<?php _e( 'Enter e-mail address', 'megaoptim-image-optimizer' ); ?>"/>
                    </div>
                    <div class="mgo-form-group" id="mgo-password">
                        <label><?php _e( 'Enter Password', 'megaoptim-image-optimizer' ); ?></label>
                        <input type="password" name="password" placeholder="<?php _e( 'Password', 'megaoptim-image-optimizer' ); ?>"/>
                    </div>
                    <div class="mgo-form-group" id="mgo-password_confirmation">
                        <label><?php _e( 'Confirm your Password', 'megaoptim-image-optimizer' ); ?></label>
                        <input type="password" name="password_confirmation" placeholder="<?php _e( 'Password Confirmation', 'megaoptim-image-optimizer' ); ?>"/>
                    </div>
                    <div class="mgo-form-group" id="mgo-terms_and_conditions">
                        <label>
                            <input type="checkbox" name="terms_and_conditions" value="yes"/> <?php echo sprintf( __( 'I accept the %s', 'megaoptim-image-optimizer' ), '<a target="_blank" href="https://megaoptim.com/terms-and-conditions">' . __( 'Terms and Conditions', 'megaoptim-image-optimizer' ) . '</a>' ); ?>
                        </label>
                    </div>
                </div>
                <div id="megaoptim-register-form-step2" style="<?php echo $step === 2 ? 'display:block;' : 'display:none;'; ?>">
                    <div class="megaoptim-form-success">
                        <h2><?php _e( 'Check your email!', 'megaoptim-image-optimizer' ); ?></h2>
                        <p><?php _e( 'We sent you an email to the address you used to sign up with confirmation link and the api key.', 'megaoptim-image-optimizer' ); ?></p>
                        <p><?php _e( 'If you haven\'t confirmed your account yet, simply confirm your account and copy the api key from the email, paste it below and proceed!', 'megaoptim-image-optimizer' ); ?></p>
                        <p><?php _e(sprintf('You can also find the API key in %s', '<a target="_blank" href="'.WP_MEGAOPTIM_DASHBOARD_URL.'">'.__('MegaOptim dashboard', 'megaoptim-image-optimizer').'</a>'), 'megaoptim-image-optimizer'); ?></p>
                        <div class="mgo-form-group" id="mgo-api_key">
                            <input type="text" name="api_key" placeholder="<?php _e( 'Enter your api key', 'megaoptim-image-optimizer' ); ?>"/>
                        </div>
                    </div>
                </div>
                <div id="megaoptim-register-form-step3" style="<?php echo $step === 3 ? 'display:block;' : 'display:none;'; ?>">
                    <h2><i class="fa fa-check"></i> <?php _e( 'You are all set!', 'megaoptim-image-optimizer' ); ?></h2>
                    <p class="megaoptim-mb-0"><?php _e( 'Your WordPress instance is successfully connected to MegaOptim Cloud.', 'megaoptim-image-optimizer' ); ?></p>
                    <p class="megaoptim-mt-5"><?php _e(sprintf('%s to start with optimization, or %s to adjust your settings!', '<a target="_blank" href="'.MGO_Admin_UI::get_optimizer_url().'">'.__('Click here', 'megaoptim-image-optimizer').'</a>', '<a target="_blank" href="'.MGO_Admin_UI::get_settings_url().'">'.__('click here', 'megaoptim-image-optimizer').'</a>'),'megaoptim-image-optimizer'); ?></p>
                </div>
            </div>
        </div>
        <div class="megaoptim-panel-footer">
            <div class="megaoptim-panel-footer-inner">
                <button data-remodal-action="cancel" class="megaoptim-btn megaoptim-cancel"><?php _e( 'Cancel', 'megaoptim-image-optimizer' ); ?></button>
				<?php if ( $step < 3 ): ?>
                    <button type="submit" class="megaoptim-btn megaoptim-ok"><?php _e( 'OK', 'megaoptim-image-optimizer' ); ?></button>
				<?php endif; ?>
            </div>
        </div>
    </form>
</div>