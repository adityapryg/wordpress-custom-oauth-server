<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php _e('OAuth Clients', 'custom-oauth-server'); ?>
    </h1>
    
    <a href="<?php echo esc_url(add_query_arg(['action' => 'new'], admin_url('options-general.php?page=oauth-server'))); ?>" class="page-title-action">
        <?php _e('Add New Client', 'custom-oauth-server'); ?>
    </a>
    
    <hr class="wp-header-end">

    <div class="oauth-server-dashboard">
        <div class="oauth-stats">
            <div class="oauth-stat-box">
                <h3><?php echo count(get_option('custom_oauth_clients', [])); ?></h3>
                <p><?php _e('Total Clients', 'custom-oauth-server'); ?></p>
            </div>
            <div class="oauth-stat-box">
                <h3><?php echo custom_oauth_get_active_tokens_count(); ?></h3>
                <p><?php _e('Active Tokens', 'custom-oauth-server'); ?></p>
            </div>
        </div>
    </div>

    <?php if (empty($list_table->items)): ?>
        <div class="oauth-empty-state">
            <div class="oauth-empty-content">
                <div class="oauth-empty-icon">
                    <span class="dashicons dashicons-admin-network"></span>
                </div>
                <h2><?php _e('No OAuth clients found', 'custom-oauth-server'); ?></h2>
                <p><?php _e('Create your first OAuth client to start authenticating applications.', 'custom-oauth-server'); ?></p>
                <a href="<?php echo esc_url(add_query_arg(['action' => 'new'], admin_url('options-general.php?page=oauth-server'))); ?>" class="button button-primary button-large">
                    <?php _e('Add First Client', 'custom-oauth-server'); ?>
                </a>
            </div>
        </div>
    <?php else: ?>
        <form method="post" id="oauth-clients-filter">
            <input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']); ?>" />
            <?php wp_nonce_field('oauth_admin_action', 'oauth_nonce'); ?>
            <input type="hidden" name="oauth_action" value="bulk_delete" />
            
            <?php $list_table->display(); ?>
        </form>
    <?php endif; ?>

    <div class="oauth-help-section">
        <h2><?php _e('Quick Start Guide', 'custom-oauth-server'); ?></h2>
        <div class="oauth-help-cards">
            <div class="oauth-help-card">
                <h3><?php _e('1. Create Client', 'custom-oauth-server'); ?></h3>
                <p><?php _e('Add a new OAuth client for your Laravel application with a unique client ID and secret.', 'custom-oauth-server'); ?></p>
            </div>
            <div class="oauth-help-card">
                <h3><?php _e('2. Configure Laravel', 'custom-oauth-server'); ?></h3>
                <p><?php _e('Add the client credentials to your Laravel .env file and configure the OAuth provider.', 'custom-oauth-server'); ?></p>
            </div>
            <div class="oauth-help-card">
                <h3><?php _e('3. Test Integration', 'custom-oauth-server'); ?></h3>
                <p><?php _e('Test the OAuth flow by attempting to login from your Laravel application.', 'custom-oauth-server'); ?></p>
            </div>
        </div>
    </div>

    <div class="oauth-endpoints-info">
        <h3><?php _e('OAuth Endpoints', 'custom-oauth-server'); ?></h3>
        <table class="widefat">
            <tbody>
                <tr>
                    <td><strong><?php _e('Authorization URL', 'custom-oauth-server'); ?></strong></td>
                    <td><code><?php echo esc_html(home_url('/oauth/authorize')); ?></code></td>
                </tr>
                <tr>
                    <td><strong><?php _e('Token URL', 'custom-oauth-server'); ?></strong></td>
                    <td><code><?php echo esc_html(home_url('/oauth/token')); ?></code></td>
                </tr>
                <tr>
                    <td><strong><?php _e('User Info URL', 'custom-oauth-server'); ?></strong></td>
                    <td><code><?php echo esc_html(home_url('/wp-json/oauth/v1/me')); ?></code></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php
// Helper function for stats
function custom_oauth_get_active_tokens_count() {
    global $wpdb;
    $count = $wpdb->get_var(
        "SELECT COUNT(*) FROM {$wpdb->options} 
         WHERE option_name LIKE '_transient_oauth_access_token_%'"
    );
    return (int) $count;
}
?>
