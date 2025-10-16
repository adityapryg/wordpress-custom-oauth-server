<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php _e('Add New OAuth Client', 'custom-oauth-server'); ?>
    </h1>
    
    <a href="<?php echo esc_url(admin_url('options-general.php?page=oauth-server')); ?>" class="page-title-action">
        <?php _e('← Back to Clients', 'custom-oauth-server'); ?>
    </a>
    
    <hr class="wp-header-end">

    <div class="oauth-form-container">
        <form method="post" action="" class="oauth-client-form">
            <?php wp_nonce_field('oauth_admin_action', 'oauth_nonce'); ?>
            <input type="hidden" name="oauth_action" value="create_client">
            
            <div class="oauth-form-section">
                <h2><?php _e('Client Information', 'custom-oauth-server'); ?></h2>
                
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="client_name"><?php _e('Client Name', 'custom-oauth-server'); ?> <span class="required">*</span></label>
                            </th>
                            <td>
                                <input type="text" id="client_name" name="client_name" class="regular-text" required 
                                       placeholder="<?php esc_attr_e('e.g., Marketing Dashboard', 'custom-oauth-server'); ?>" />
                                <p class="description">
                                    <?php _e('A human-readable name for this OAuth client. This will be shown to users during authorization.', 'custom-oauth-server'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="client_id"><?php _e('Client ID', 'custom-oauth-server'); ?> <span class="required">*</span></label>
                            </th>
                            <td>
                                <input type="text" id="client_id" name="client_id" class="regular-text" required 
                                       pattern="[a-zA-Z0-9_-]+" 
                                       placeholder="<?php esc_attr_e('e.g., marketing-dashboard', 'custom-oauth-server'); ?>" />
                                <p class="description">
                                    <?php _e('Unique identifier for this client. Use only letters, numbers, hyphens, and underscores.', 'custom-oauth-server'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="client_secret"><?php _e('Client Secret', 'custom-oauth-server'); ?> <span class="required">*</span></label>
                            </th>
                            <td>
                                <div class="oauth-secret-field">
                                    <input type="text" id="client_secret" name="client_secret" class="regular-text" required 
                                           value="<?php echo esc_attr(custom_oauth_generate_token(32)); ?>" />
                                    <button type="button" id="generate_secret" class="button button-secondary">
                                        <?php _e('Generate New', 'custom-oauth-server'); ?>
                                    </button>
                                    <button type="button" id="toggle_secret" class="button button-secondary">
                                        <span class="dashicons dashicons-visibility"></span>
                                    </button>
                                </div>
                                <p class="description">
                                    <?php _e('Secret key for this client. Keep this secure and do not share it publicly.', 'custom-oauth-server'); ?>
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="oauth-form-section">
                <h2><?php _e('Redirect URIs', 'custom-oauth-server'); ?></h2>
                
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="redirect_uris"><?php _e('Allowed Redirect URIs', 'custom-oauth-server'); ?> <span class="required">*</span></label>
                            </th>
                            <td>
                                <textarea id="redirect_uris" name="redirect_uris" rows="5" class="large-text" required 
                                          placeholder="<?php esc_attr_e("https://your-app.com/auth/wordpress/callback\nhttps://localhost:8000/auth/wordpress/callback", 'custom-oauth-server'); ?>"></textarea>
                                <p class="description">
                                    <?php _e('Enter one redirect URI per line. These are the URLs where users will be redirected after authorization.', 'custom-oauth-server'); ?>
                                </p>
                                <div class="oauth-uri-examples">
                                    <strong><?php _e('Examples:', 'custom-oauth-server'); ?></strong>
                                    <ul>
                                        <li><code>https://yourapp.com/auth/wordpress/callback</code></li>
                                        <li><code>https://localhost:8000/auth/wordpress/callback</code></li>
                                        <li><code>https://staging.yourapp.com/auth/wordpress/callback</code></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="oauth-form-section">
                <h2><?php _e('Laravel Configuration', 'custom-oauth-server'); ?></h2>
                <div class="oauth-config-preview">
                    <p><?php _e('After creating this client, add these lines to your Laravel .env file:', 'custom-oauth-server'); ?></p>
                    <pre id="laravel_config_preview" class="oauth-code-block"><code># WordPress OAuth Configuration
WORDPRESS_BASE_URL=<?php echo esc_html(home_url()); ?>

WORDPRESS_CLIENT_ID=<span class="config-client-id">[will be generated]</span>
WORDPRESS_CLIENT_SECRET=<span class="config-client-secret">[will be generated]</span>
WORDPRESS_REDIRECT_URI=https://your-app.com/auth/wordpress/callback</code></pre>
                </div>
            </div>

            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary button-large" 
                       value="<?php esc_attr_e('Create OAuth Client', 'custom-oauth-server'); ?>">
                <a href="<?php echo esc_url(admin_url('options-general.php?page=oauth-server')); ?>" class="button button-secondary button-large">
                    <?php _e('Cancel', 'custom-oauth-server'); ?>
                </a>
            </p>
        </form>
    </div>
</div>
