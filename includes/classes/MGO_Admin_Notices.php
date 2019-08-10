<?php

class MGO_Admin_Notices {

    private $title = 'MegaOptim Image Optimizer';

	private $prefix = 'megaoptim_';

	private $expiry = 8640000;

	private static $_instance;
	private $admin_notices;
	const TYPES = 'error,warning,info,success';

	/**
	 * MGO_Admin_Notices constructor.
	 */
	private function __construct() {
		$this->admin_notices = new stdClass();
		foreach ( explode( ',', self::TYPES ) as $type ) {
			$this->admin_notices->{$type} = array();
		}
		add_action( 'admin_init', array( &$this, 'admin_init' ) );
		add_action( 'admin_notices', array( &$this, 'admin_notices' ) );
		add_action( 'admin_footer', array( &$this, 'admin_footer' ), PHP_INT_MAX - 1000 );
	}

	/**
	 * Return the current instance
	 * @return MGO_Admin_Notices
	 */
	public static function instance() {
		if ( ! ( self::$_instance instanceof self ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Admin init
	 */
	public function admin_init() {
		$dismiss_option = filter_input( INPUT_GET, $this->prefix . 'dismiss', FILTER_SANITIZE_STRING );
		if ( is_string( $dismiss_option ) ) {
			set_transient( $this->prefix . "dismissed_$dismiss_option", true, $this->expiry );
			wp_die();
		}
	}

	/**
	 * Enqueue the admin scripts
	 */
	public function admin_footer() {
		?>
        <script>
            /**
             * Admin code for dismissing notifications.
             *
             */
            (function ($) {
                'use strict';
                $(function () {
                    $('.<?php echo $this->prefix; ?>notice').on('click', '.notice-dismiss', function (event, el) {
                        var $notice = $(this).parent('.notice.is-dismissible');
                        var dismiss_url = $notice.attr('data-dismiss-url');
                        if (dismiss_url) {
                            $.get(dismiss_url);
                        }
                    });
                });
            })(jQuery);
        </script>
		<?php
	}

	/**
	 * Show admin notices
	 */
	public function admin_notices() {
		foreach ( explode( ',', self::TYPES ) as $type ) {
			foreach ( $this->admin_notices->{$type} as $admin_notice ) {
				$dismiss_url = add_query_arg( array(
					$this->prefix . 'dismiss' => $admin_notice->dismiss_option
				), admin_url() );

				if ( ! get_transient( $this->prefix . "dismissed_{$admin_notice->dismiss_option}" ) ) {
					?>
                    <div class="notice <?php echo $this->prefix; ?>notice notice-<?php echo $type;
						if ( $admin_notice->dismiss_option ) {
							echo ' is-dismissible" data-dismiss-url="' . esc_url( $dismiss_url );
						} ?>">
                    <h2><?php echo "$this->title $type"; ?></h2>
                    <p><?php echo $admin_notice->message; ?></p>
                    </div>
                    <?php
				}
			}
		}
	}

	/**
	 * Add error notification message.
	 *
	 * @param $message
	 * @param bool $dismiss_option
	 */
	public function error( $message, $dismiss_option = false ) {
		$this->notice( 'error', $message, $dismiss_option );
	}

	/**
	 * Add warning notification message.
	 *
	 * @param $message
	 * @param bool $dismiss_option
	 */
	public function warning( $message, $dismiss_option = false ) {
		$this->notice( 'warning', $message, $dismiss_option );
	}

	/**
	 * Add success notification message.
	 *
	 * @param $message
	 * @param bool $dismiss_option
	 */
	public function success( $message, $dismiss_option = false ) {
		$this->notice( 'success', $message, $dismiss_option );
	}

	/**
	 * Add info notification message.
	 *
	 * @param $message
	 * @param bool $dismiss_option
	 */
	public function info( $message, $dismiss_option = false ) {
		$this->notice( 'info', $message, $dismiss_option );
	}

	/**
	 * Add notice
	 *
	 * @param $type
	 * @param $message
	 * @param $dismiss_option
	 */
	private function notice( $type, $message, $dismiss_option ) {
		$notice                 = new stdClass();
		$notice->message        = $message;
		$notice->dismiss_option = $dismiss_option;

		$this->admin_notices->{$type}[] = $notice;
	}
}