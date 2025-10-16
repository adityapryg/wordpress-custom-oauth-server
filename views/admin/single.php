<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php echo esc_html($client['name']); ?>
    </h1>
    
    <a href="<?php echo esc_url(admin_url('options-general.php?page=oauth-server')); ?>" class="page-title-action">
        <?php _e('← Back to Clients', 'custom-oauth-server'); ?>
    </a>
    
    <a href="<?php echo esc_url(add_query_arg(['action' => 'edit', 'client' => $client_id], admin_url('options-general.php?page=oauth-server'))); ?>" class="page-title-action">
        <?php _e('Edit Client', 'custom-oauth-server'); ?>
    </a>
    
    <hr class="wp-header-end">

    <div class="oauth-client-details">
        <div class="oauth-details-grid">
            <div class="oauth-details-main">
                <div class="oauth-detail-section">
                    <h2><?php _e('Client Information', 'custom-oauth-server'); ?></h2>
                    <table class="oauth-details-table">
                        <tbody>
                            <tr>
                                <td><strong><?php _e('Client ID', 'custom-oauth-server'); ?></strong></td>
                                <td>
                                    <code class="oauth-copyable" data-copy="<?php echo esc_attr($client_id); ?>">
                                        <?php echo esc_html($client_id); ?>
                                        <span class="copy-button" title="<?php esc_attr_e('Copy to clipboard', 'custom-oauth-server'); ?>">
                                            <span class="dashicons dashicons-admin-page"></span>
                                        </span>
                                    </code>
                                </td>
                            </tr>
                            <tr>
                                <td><strong><?php _e('Client Secret', 'custom-oauth-server'); ?></strong></td>
                                <td>
                                    <code class="oauth-copyable oauth-secret" data-copy="<?php echo esc_attr($client['secret']); ?>">
                                        <span class="secret-hidden">••••••••••••••••</span>
                                        <span class="secret-visible" style="display: none;"><?php echo esc_html($client['secret']); ?></span>
                                        <span class="toggle-secret" title="<?php esc_attr_e('Toggle visibility', 'custom-oauth-server'); ?>">
                                            <span class="dashicons dashicons-visibility"></span>
                                        </span>
                                        <span class="copy-button" title="<?php esc_attr_e('Copy to clipboard', 'custom-oauth-server'); ?>">
                                            <span class="dashicons dashicons-admin-page"></span>
                                        </span>
                                    </code>
                                </td>
                            </tr>
                            <tr>
                                <td><strong><?php _e('Created', 'custom-oauth-server'); ?></strong></td>
                                <td><?php echo esc_html($client['created'] ?? __('Unknown', 'custom-oauth-server')); ?></td>
                            </tr>
                            <?php if (isset($client['updated'])): ?>
                            <tr>
                                <td><strong><?php _e('Last Updated', 'custom-oauth-server'); ?></strong></td>
                                <td><?php echo esc_html($client['updated']); ?></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="oauth-detail-section">
                    <h2><?php _e('Redirect URIs', 'custom-oauth-server'); ?></h2>
                    <div class="oauth-uri-list">
                        <?php foreach($client['redirect_uris'] as $uri): ?>
                            <div class="oauth-uri-item">
                                <code class="oauth-copyable" data-copy="<?php echo esc_attr($uri); ?>">
                                    <?php echo esc_html($uri); ?>
                                    <span class="copy-button" title="<?php esc_attr_e('Copy to clipboard', 'custom-oauth-server'); ?>">
                                        <span class="dashicons dashicons-admin-page"></span>
                                    </span>
                                </code>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="oauth-detail-section">
                    <h2><?php _e('OAuth Endpoints', 'custom-oauth-server'); ?></h2>
                    <table class="oauth-details-table">
                        <tbody>
                            <tr>
                                <td><strong><?php _e('Authorization URL', 'custom-oauth-server'); ?></strong></td>
                                <td>
                                    <code class="oauth-copyable" data-copy="<?php echo esc_attr(home_url('/oauth/authorize')); ?>">
                                        <?php echo esc_html(home_url('/oauth/authorize')); ?>
                                        <span class="copy-button" title="<?php esc_attr_e('Copy to clipboard', 'custom-oauth-server'); ?>">
                                            <span class="dashicons dashicons-admin-page"></span>
                                        </span>
                                    </code>
                                </td>
                            </tr>
                            <tr>
                                <td><strong><?php _e('Token URL', 'custom-oauth-server'); ?></strong></td>
                                <td>
                                    <code class="oauth-copyable" data-copy="<?php echo esc_attr(home_url('/oauth/token')); ?>">
                                        <?php echo esc_html(home_url('/oauth/token')); ?>
                                        <span class="copy-button" title="<?php esc_attr_e('Copy to clipboard', 'custom-oauth-server'); ?>">
                                            <span class="dashicons dashicons-admin-page"></span>
                                        </span>
                                    </code>
                                </td>
                            </tr>
                            <tr>
                                <td><strong><?php _e('User Info URL', 'custom-oauth-server'); ?></strong></td>
                                <td>
                                    <code class="oauth-copyable" data-copy="<?php echo esc_attr(home_url('/wp-json/oauth/v1/me')); ?>">
                                        <?php echo esc_html(home_url('/wp-json/oauth/v1/me')); ?>
                                        <span class="copy-button" title="<?php esc_attr_e('Copy to clipboard', 'custom-oauth-server'); ?>">
                                            <span class="dashicons dashicons-admin-page"></span>
                                        </span>
                                    </code>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="oauth-details-sidebar">
                <div class="oauth-sidebar-section">
                    <h3><?php _e('Laravel Configuration', 'custom-oauth-server'); ?></h3>
                    <p><?php _e('Add these to your Laravel .env file:', 'custom-oauth-server'); ?></p>
                    <pre class="oauth-code-block oauth-copyable" data-copy="WORDPRESS_BASE_URL=<?php echo esc_attr(home_url()); ?>
WORDPRESS_CLIENT_ID=<?php echo esc_attr($client_id); ?>
WORDPRESS_CLIENT_SECRET=<?php echo esc_attr($client['secret']); ?>
WORDPRESS_REDIRECT_URI=<?php echo esc_attr($client['redirect_uris'][0] ?? ''); ?>"><code>WORDPRESS_BASE_URL=<?php echo esc_html(home_url()); ?>
WORDPRESS_CLIENT_ID=<?php echo esc_html($client_id); ?>
WORDPRESS_CLIENT_SECRET=<?php echo esc_html($client['secret']); ?>
WORDPRESS_REDIRECT_URI=<?php echo esc_html($client['redirect_uris'][0] ?? ''); ?></code><span class="copy-button" title="<?php esc_attr_e('Copy configuration', 'custom-oauth-server'); ?>">
                        <span class="dashicons dashicons-admin-page"></span>
                    </span></pre>
                </div>

                <div class="oauth-sidebar-section">
                    <h3><?php _e('Quick Actions', 'custom-oauth-server'); ?></h3>
                    <div class="oauth-quick-actions">
                        <a href="<?php echo esc_url(add_query_arg(['action' => 'edit', 'client' => $client_id], admin_url('options-general.php?page=oauth-server'))); ?>" class="button button-secondary button-large">
                            <?php _e('Edit Client', 'custom-oauth-server'); ?>
                        </a>
                        <button type="button" id="test_oauth_flow" class="button button-secondary button-large">
                            <?php _e('Test OAuth Flow', 'custom-oauth-server'); ?>
                        </button>
                        <button type="button" id="revoke_all_tokens" class="button button-secondary">
                            <?php _e('Revoke All Tokens', 'custom-oauth-server'); ?>
                        </button>
                    </div>
                </div>

                <div class="oauth-sidebar-section">
                    <h3><?php _e('Statistics', 'custom-oauth-server'); ?></h3>
                    <div class="oauth-stats-list">
                        <div class="oauth-stat-item">
                            <span class="oauth-stat-label"><?php _e('Active Tokens', 'custom-oauth-server'); ?></span>
                            <span class="oauth-stat-value"><?php echo custom_oauth_get_client_token_count($client_id); ?></span>
                        </div>
                        <div class="oauth-stat-item">
                            <span class="oauth-stat-label"><?php _e('Redirect URIs', 'custom-oauth-server'); ?></span>
                            <span class="oauth-stat-value"><?php echo count($client['redirect_uris']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Test OAuth Flow Modal -->
<div id="oauth-test-modal" class="oauth-modal" style="display: none;">
    <div class="oauth-modal-content">
        <div class="oauth-modal-header">
            <h2><?php _e('Test OAuth Flow', 'custom-oauth-server'); ?></h2>
            <button type="button" class="oauth-modal-close">&times;</button>
        </div>
        <div class="oauth-modal-body">
            <p><?php _e('This will open a new window to test the OAuth authorization flow.', 'custom-oauth-server'); ?></p>
            <div class="oauth-test-form">
                <label for="test_redirect_uri"><?php _e('Select Redirect URI:', 'custom-oauth-server'); ?></label>
                <select id="test_redirect_uri">
                    <?php foreach($client['redirect_uris'] as $uri): ?>
                        <option value="<?php echo esc_attr($uri); ?>"><?php echo esc_html($uri); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="oauth-modal-footer">
            <button type="button" id="start_oauth_test" class="button button-primary">
                <?php _e('Start Test', 'custom-oauth-server'); ?>
            </button>
            <button type="button" class="button button-secondary oauth-modal-close">
                <?php _e('Cancel', 'custom-oauth-server'); ?>
            </button>
        </div>
    </div>
</div>
