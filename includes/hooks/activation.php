<?php
function megaoptim_plugin_activation() {
	if ( version_compare( phpversion(), WP_MEGAOPTIM_PHP_MINIMUM, '<' ) ) {
		if ( current_user_can( 'activate_plugins' ) ) {
			add_action( 'admin_init', '__megaoptim_deactivate_this' );
			add_action( 'admin_notices', '__megaoptim_deactivation_notice' );
			function __megaoptim_deactivate_this() {
				deactivate_plugins( WP_MEGAOPTIM_BASENAME );
			}

			function __megaoptim_deactivation_notice() {
				?>
                <div class="update-nag">
					<?php _e( 'You need to update your PHP version to run MegaOptim Image Optimizer.', 'megaoptim' ); ?> <br/>
					<?php _e( 'Actual version is:', 'megaoptim' ) ?>
                    <strong><?php echo phpversion(); ?></strong>, <?php _e( 'required is', 'megaoptim' ) ?>
                    <strong><?php echo WP_MEGAOPTIM_BASENAME; ?></strong>
	                <?php _e( '. Please contact your hosting or MegaOptim support for further assistence.', 'megaoptim' ) ?>
                </div>
				<?php

				if ( isset( $_GET['activate'] ) ) {
					unset( $_GET['activate'] );
				}
			}
		}
	}
}
register_activation_hook( WP_MEGAOPTIM_PLUGIN_FILE_PATH, 'megaoptim_plugin_activation' );