<?php
$page        = isset( $_GET['page'] ) ? $_GET['page'] : null;
$section     = isset( $_GET['section'] ) ? $_GET['section'] : null;
$module      = isset( $_GET['module'] ) ? $_GET['module'] : null;
$menu        = isset( $menu ) ? $menu : false;
$home_url = $menu === 'settings' ? admin_url('options-general.php?page=megaoptim_settings') : admin_url( "admin.php?page=megaoptim_bulk_optimizer" );
if ( ! is_null( $module ) ) {
	$home_url = add_query_arg( 'module', $module, $home_url );
	$home_url = add_query_arg( 'switch', 1, $home_url );
}
?>
<div class="megaoptim wrap">
    <div class="megaoptim-container">
        <div class="megaoptim-header megaoptim-bg-primary">
            <div class="megaoptim-header-logo">
                <a class="navbar-brand" href="<?php echo $home_url; ?>">
                    <img src="<?php echo WP_MEGAOPTIM_URL . '/assets/img/logo-white.png'; ?>"/>
                </a>
            </div>
			<?php if ( $menu === 'settings' ): ?>
                <div class="megaoptim-header-menu">
                    <ul class="megaoptim-menu">
                        <li class="megaoptim-menu-item <?php echo ( is_null( $section ) && $page === 'megaoptim_settings' ) ? 'active' : ''; ?>">
                            <a href="<?php echo admin_url( "options-general.php?page=megaoptim_settings" ); ?>">
								<?php _e( 'General', 'megaoptim' ); ?>
                            </a>
                        </li>
                        <li class="megaoptim-menu-item <?php echo ( $page === 'megaoptim_settings' && $section === 'advanced' ) ? 'active' : ''; ?>">
                            <a href="<?php echo admin_url( "options-general.php?page=megaoptim_settings&section=advanced" ); ?>">
								<?php _e( 'Advanced', 'megaoptim' ); ?>
                            </a>
                        </li>
                        <li class="megaoptim-menu-item <?php echo ( $page === 'megaoptim_settings' && $section === 'status' ) ? 'active' : ''; ?>">
                            <a href="<?php echo admin_url( "options-general.php?page=megaoptim_settings&section=status" ); ?>">
								<?php _e( 'Debug', 'megaoptim' ); ?>
                            </a>
                        </li>
                    </ul>
                </div>
			<?php elseif ( $menu === 'optimizer' ): ?>
                <div class="megaoptim-header-menu">
                    <form class="optimizer-switcher" method="GET" action="<?php echo admin_url( "upload.php" ); ?>">
                        <input type="hidden" name="page" value="megaoptim_bulk_optimizer"/>
                        <label for="module">Select optimizer</label>
                        <select name="module" id="module">
                            <option <?php echo ! isset( $_GET['module'] ) || ( isset( $_GET['module'] ) && $_GET['module'] === 'wp-media-library' ) ? 'selected' : ''; ?> value="wp-media-library">WP Media Library</option>
                            <option <?php echo ( isset( $_GET['module'] ) && $_GET['module'] === 'folders' ) ? 'selected' : ''; ?> value="folders">Custom Folders</option>
                            <option <?php echo ! megaoptim_is_nextgen_active() ? 'disabled' : ''; ?> <?php echo ( isset( $_GET['module'] ) && $_GET['module'] === 'nextgen' ) ? 'selected' : ''; ?> value="nextgen">NextGen Galleries</option>
                        </select>
                        <button type="submit" class="button-primary" name="switch" value="1">Switch</button>
                    </form>
                </div>
			<?php endif; ?>
        </div>

        <div class="megaoptim-content">