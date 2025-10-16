<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php _e('Edit OAuth Client', 'custom-oauth-server'); ?>
    </h1>
    
    <a href="<?php echo esc_url(admin_url('options-general.php?page=oauth-server')); ?>" class="page-title-action">
        <?php _e('← Back to Clients', 'custom-oauth-server'); ?>
    </a>
    
    <a href="<?php echo esc_url(add_query_arg(['action' => 'view', 'client' => $client_id], admin_url('options-general.php?page=oauth-server'))); ?>" class="page-title-action">
        <?php _e('View Details', 'custom-oauth-server'); ?>
    </a>
    
    <hr class="wp-header-end">

    <div class="oauth-form-container">
        <div class="oauth-client-header">
            <h2><?php echo esc_html($client['name']); ?></h2>
            <p class="oauth-client-id">Client ID: <code><?php echo esc_html($client_id); ?></code></p>
        </div>

        <form method="post" action="" class="oauth-client-form">
            <?php wp_nonce_field('oauth_admin_action', 'oauth_nonce'); ?>
            <input type="hidden" name="oauth_action" value="update_client">
            <input type="hidden" name="client_id" value="<?php echo esc_attr($client_id); ?>">
            
            <div class="oauth-form-section">
                <h3><?php _e('Client Information', 'custom-oauth-server'); ?></h3>
                
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="client_name"><?php _e('Client Name', 'custom-oauth-server'); ?> <span class="required">*</span></label>
                            </th>
                            <td>
                                <input type="text" id="client_name" name="client_name" class="regular-text" required 
                                       value="<?php echo esc_attr($client['name']); ?>" />
                                <p class="description">
                                    <?php _e('A human-readable name for this OAuth client.', 'custom-oauth-server'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="client_secret"><?php _e('Client Secret', 'custom-oauth-server'); ?> <span class="required">*</span></label>
                            </th>
                            <td>
                                <div class="oauth-secret-field">
                                    <input type="password" id="client_secret" name="client_secret" class="regular-text" required 
                                           value="<?php echo esc_attr($client['secret']); ?>" />
                                    <button type="button" id="generate_secret" class="button button-secondary">
                                        <?php _e('Generate New', 'custom-oauth-server'); ?>
                                    </button>
                                    <button type="button" id="toggle_secret" class="button button-secondary">
                                        <span class="dashicons dashicons-visibility"></span>
                                    </button>
                                </div>
                                <p class="description">
                                    <?php _e('Changing the secret will invalidate all existing tokens for this client.', 'custom-oauth-server'); ?>
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="oauth-form-section">
                <h3><?php _e('Redirect URIs', 'custom-oauth-server'); ?></h3>
                
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="redirect_uris"><?php _e('Allowed Redirect URIs', 'custom-oauth-server'); ?> <span class="required">*</span></label>
                            </th>
                            <td>
                                <textarea id="redirect_uris" name="redirect_uris" rows="5" class="large-text" required><?php echo esc_textarea(implode("\n", $client['redirect_uris'])); ?></textarea>
                                <p class="description">
                                    <?php _e('Enter one redirect URI per line.', 'custom-oauth-server'); ?>
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="oauth-form-section">
                <h3><?php _e('Client Statistics', 'custom-oauth-server'); ?></h3>
                <div class="oauth-client-stats">
                    <div class="oauth-stat-item">
                        <strong><?php _e('Created:', 'custom-oauth-server'); ?></strong> 
                        <?php echo esc_html($client['created'] ?? __('Unknown', 'custom-oauth-server')); ?>
                    </div>
                    <?php if (isset($client['updated'])): ?>
                    <div class="oauth-stat-item">
                        <strong><?php _e('Last Updated:', 'custom-oauth-server'); ?></strong> 
                        <?php echo esc_html($client['updated']); ?>
                    </div>
                    <?php endif; ?>
                    <div class="oauth-stat-item">
                        <strong><?php _e('Active Tokens:', 'custom-oauth-server'); ?></strong> 
                        <?php echo custom_oauth_get_client_token_count($client_id); ?>
                    </div>
                </div>
            </div>

            <div class="oauth-danger-zone">
                <h3><?php _e('Danger Zone', 'custom-oauth-server'); ?></h3>
                <div class="oauth-danger-actions">
                    <button type="button" id="revoke_all_tokens" class="button button-secondary">
                        <?php _e('Revoke All Tokens', 'custom-oauth-server'); ?>
                    </button>
                    <a href="<?php echo esc_url(wp_nonce_url(
                        add_query_arg(['action' => 'delete', 'client' => $client_id], admin_url('options-general.php?page=oauth-server')),
                        'oauth_delete_client',
                        'oauth_nonce'
                    )); ?>" class="button button-link-delete" onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this client? This action cannot be undone.', 'custom-oauth-server'); ?>')">
                        <?php _e('Delete Client', 'custom-oauth-server'); ?>
                    </a>
                </div>
                <p class="description">
                    <?php _e('These actions are permanent and cannot be undone.', 'custom-oauth-server'); ?>
                </p>
            </div>

            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary button-large" 
                       value="<?php esc_attr_e('Update OAuth Client', 'custom-oauth-server'); ?>">
                <a href="<?php echo esc_url(admin_url('options-general.php?page=oauth-server')); ?>" class="button button-secondary button-large">
                    <?php _e('Cancel', 'custom-oauth-server'); ?>
                </a>
            </p>
        </form>
    </div>
</div>

<?php
function custom_oauth_get_client_token_count($client_id) {
    global $wpdb;
    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->options} 
         WHERE option_name LIKE %s 
         AND option_value LIKE %s",
        '_transient_oauth_access_token_%',
        '%"client_id":"' . $client_id . '"%'
    ));
    return (int) $count;
}
?>
