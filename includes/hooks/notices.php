<?php

function _megaoptim_notice_user_balance() {
	if(!is_admin()) {
		return;
	}
	try {
		$profile = new MGO_Profile();
		$tokens_count = $profile->get_tokens_count();
		$tokens_count = 0;
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
add_action('init', '_megaoptim_notice_user_balance');