<?php

/**
 * Notify the user about zero user balance
 */
function _megaoptim_notice_user_balance() {
	if(!is_admin()) {
		return;
	}
	try {
		$profile = new MGO_Profile();
		$tokens_count = $profile->get_tokens_count();
		if( intval($tokens_count) === 0) {
			$message = sprintf(
				'%s %s %s %s.',
				__('Your image tokens balance is 0, to continue using', 'megaoptim'),
				'<strong>'.__('MegaOptim Image Optimizer', 'megaoptim').'</strong>',
				__('please top up your account'),
				'<a href="'.WP_MEGAOPTIM_DASHBOARD_URL.'">'.__('here', 'megaoptim').'</a>'
			);
			MGO_Admin_Notices::instance()->warning('insufficient_balance', $message, 1);
		}
	} catch (\Exception $exception) {

	}
}
add_action('init', '_megaoptim_notice_user_balance', 1000);

/**
 * Notify the user about conflicting plugins
 * // TODO: Deactivate button.
 */
function _megaoptim_notice_conflicting_plugins() {
	if(!is_admin()) {
		return;
	}

	$active_plugins = megaoptim_get_conflicting_plugins();
	if(is_array($active_plugins) && count($active_plugins)) {
		$message = sprintf(
			'%s %s:',
			__('The following plugins will likely conflict with', 'megaoptim'),
			'<strong>'.__('MegaOptim Image Optimizer') . '</strong>'
		);
		$message = '<p>'.$message.'</p>';
		$message .= '<ul>';
		foreach ($active_plugins as $name => $data) {
			$message .= '<li>'.$name.'</li>';
		}
		$message .= '</ul>';
		$message .= __('Please consider deactivating those plugins to ensure better integration with MegaOptim. Only leave those plugins active if you know what you are doing.', 'megaoptim');

		MGO_Admin_Notices::instance()->warning('conflicting_plugins', $message, 1);
	}


}
add_action('init', '_megaoptim_notice_conflicting_plugins', 1000);