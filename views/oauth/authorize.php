<?php
if (!defined('ABSPATH')) {
    exit;
}

// This view is shown to users during OAuth authorization
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php _e('Authorize Application', 'custom-oauth-server'); ?> - <?php bloginfo('name'); ?></title>
    <?php wp_head(); ?>
</head>
<body class="oauth-authorize-page">
    <div class="oauth-authorize-container">
        <div class="oauth-authorize-header">
            <div class="oauth-site-branding">
                <?php if (has_custom_logo()): ?>
                    <?php the_custom_logo(); ?>
                <?php else: ?>
                    <h1><?php bloginfo('name'); ?></h1>
                <?php endif; ?>
            </div>
        </div>

        <div class="oauth-authorize-main">
            <div class="oauth-authorize-card">
                <div class="oauth-authorize-icon">
                    <span class="dashicons dashicons-admin-network"></span>
                </div>

                <h2 class="oauth-authorize-title">
                    <?php _e('Authorize Application', 'custom-oauth-server'); ?>
                </h2>

                <div class="oauth-app-info">
                    <div class="oauth-app-name">
                        <strong><?php echo esc_html($oauth_data['client_name']); ?></strong>
                    </div>
                    <p class="oauth-app-description">
                        <?php _e('wants to access your account', 'custom-oauth-server'); ?>
                    </p>
                </div>

                <div class="oauth-user-info">
                    <div class="oauth-user-avatar">
                        <?php echo get_avatar($current_user->ID, 48); ?>
                    </div>
                    <div class="oauth-user-details">
                        <div class="oauth-user-name">
                            <strong><?php echo esc_html($current_user->display_name); ?></strong>
                        </div>
                        <div class="oauth-user-email">
                            <?php echo esc_html($current_user->user_email); ?>
                        </div>
                    </div>
                </div>

                <div class="oauth-permissions">
                    <h3><?php _e('This application will be able to:', 'custom-oauth-server'); ?></h3>
                    <ul class="oauth-permissions-list">
                        <li>
                            <span class="oauth-permission-icon dashicons dashicons-admin-users"></span>
                            <?php _e('Read your basic profile information', 'custom-oauth-server'); ?>
                        </li>
                        <li>
                            <span class="oauth-permission-icon dashicons dashicons-email"></span>
                            <?php _e('Access your email address', 'custom-oauth-server'); ?>
                        </li>
                        <li>
                            <span class="oauth-permission-icon dashicons dashicons-admin-users"></span>
                            <?php _e('View your WordPress username', 'custom-oauth-server'); ?>
                        </li>
                    </ul>
                </div>

                <div class="oauth-security-notice">
                    <span class="dashicons dashicons-shield"></span>
                    <p><?php _e('This application will not be able to access your password or modify your account.', 'custom-oauth-server'); ?></p>
                </div>

                <form method="post" action="<?php echo esc_url(home_url('/oauth/callback')); ?>" class="oauth-authorize-form">
                    <input type="hidden" name="action" value="authorize">
                    
                    <div class="oauth-authorize-buttons">
                        <button type="submit" name="authorize" value="yes" class="oauth-btn oauth-btn-primary">
                            <span class="dashicons dashicons-yes"></span>
                            <?php _e('Authorize', 'custom-oauth-server'); ?>
                        </button>
                        
                        <button type="submit" name="authorize" value="no" class="oauth-btn oauth-btn-secondary">
                            <span class="dashicons dashicons-no"></span>
                            <?php _e('Cancel', 'custom-oauth-server'); ?>
                        </button>
                    </div>
                </form>

                <div class="oauth-footer-info">
                    <p class="oauth-powered-by">
                        <?php printf(
                            __('Powered by %s', 'custom-oauth-server'),
                            '<strong>' . get_bloginfo('name') . '</strong>'
                        ); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <?php wp_footer(); ?>
</body>
</html>
