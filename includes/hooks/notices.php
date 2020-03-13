<?php

/**
 * Notify the user about zero user balance
 */
function _megaoptim_notice_user_balance() {
	if ( ! is_admin() ) {
		return;
	}
	try {
		$profile      = new MGO_Profile();
		$api_key      = MGO_Settings::instance()->get( MGO_Settings::API_KEY );
		$tokens_count = $profile->get_tokens_count();
		if ( !empty($api_key) && intval( $tokens_count ) === 0 ) {
			$message = sprintf(
				'%s %s %s %s.',
				__( 'Your MegaOptim account is out of optimization tokens. To continue using', 'megaoptim-image-optimizer' ),
				'<strong>' . __( 'MegaOptim Image Optimizer', 'megaoptim-image-optimizer' ) . '</strong>',
				__( 'please top up your account' ),
				'<a target="_blank" href="' . WP_MEGAOPTIM_DASHBOARD_URL . '">' . __( 'here', 'megaoptim-image-optimizer' ) . '</a>'
			);
			MGO_Admin_Notices::instance()->warning( 'insufficient_balance', $message, 1 );
		}
	} catch ( \Exception $exception ) {

	}
}

add_action( 'init', '_megaoptim_notice_user_balance', 1000 );

/**
 * Notify the user about conflicting plugins
 * // TODO: Deactivate button.
 */
function _megaoptim_notice_conflicting_plugins() {
	if ( ! is_admin() ) {
		return;
	}

	$active_plugins = megaoptim_get_conflicting_plugins();
	if ( is_array( $active_plugins ) && count( $active_plugins ) ) {
		$message = sprintf(
			'%s %s:',
			__( 'The following plugins will likely conflict with', 'megaoptim-image-optimizer' ),
			'<strong>' . __( 'MegaOptim Image Optimizer' ) . '</strong>'
		);
		$message = '<p>' . $message . '</p>';
		$message .= '<ul>';
		foreach ( $active_plugins as $name => $data ) {
			$message .= '<li>' . $name . '</li>';
		}
		$message .= '</ul>';
		$message .= __( 'Please consider deactivating those plugins to ensure better integration with MegaOptim. Only leave those plugins active if you know what you are doing.', 'megaoptim-image-optimizer' );

		MGO_Admin_Notices::instance()->warning( 'conflicting_plugins', $message, 1 );
	}


}

add_action( 'init', '_megaoptim_notice_conflicting_plugins', 1000 );