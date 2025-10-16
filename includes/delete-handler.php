<?php

if (!defined('ABSPATH')) {
    exit;
}

function custom_oauth_handle_delete_client() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Insufficient permissions', 'custom-oauth-server'));
    }
    
    $client_id = sanitize_text_field($_POST['client_id'] ?? $_GET['client'] ?? '');
    
    if (empty($client_id)) {
        wp_die(__('Client ID is required', 'custom-oauth-server'));
    }
    
    // Verify nonce
    $nonce = $_POST['oauth_nonce'] ?? $_GET['oauth_nonce'] ?? '';
    if (!wp_verify_nonce($nonce, 'oauth_delete_client')) {
        wp_die(__('Security check failed', 'custom-oauth-server'));
    }
    
    $clients = get_option('custom_oauth_clients', []);
    
    if (!isset($clients[$client_id])) {
        wp_die(__('Client not found', 'custom-oauth-server'));
    }
    
    // Remove client
    unset($clients[$client_id]);
    update_option('custom_oauth_clients', $clients);
    
    // Revoke all tokens for this client
    custom_oauth_revoke_client_tokens($client_id);
    
    custom_oauth_log("OAuth client '{$client_id}' deleted by user " . get_current_user_id());
    
    // Redirect with success message
    wp_redirect(add_query_arg([
        'page' => 'oauth-server',
        'message' => 'client_deleted',
        'type' => 'success'
    ], admin_url('options-general.php')));
    exit;
}

function custom_oauth_revoke_client_tokens($client_id) {
    global $wpdb;
    
    // Remove all access tokens for this client
    $wpdb->query($wpdb->prepare(
        "DELETE FROM {$wpdb->options} 
         WHERE option_name LIKE %s 
         AND option_value LIKE %s",
        '_transient_oauth_access_token_%',
        '%"client_id":"' . $client_id . '"%'
    ));
    
    // Remove all refresh tokens for this client
    $wpdb->query($wpdb->prepare(
        "DELETE FROM {$wpdb->options} 
         WHERE option_name LIKE %s 
         AND option_value LIKE %s",
        '_transient_oauth_refresh_token_%',
        '%"client_id":"' . $client_id . '"%'
    ));
    
    custom_oauth_log("All tokens revoked for client '{$client_id}'");
}

function custom_oauth_handle_bulk_delete() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Insufficient permissions', 'custom-oauth-server'));
    }
    
    $client_ids = $_POST['clients'] ?? [];
    
    if (empty($client_ids) || !is_array($client_ids)) {
        wp_redirect(add_query_arg([
            'page' => 'oauth-server',
            'message' => 'error',
            'type' => 'error'
        ], admin_url('options-general.php')));
        exit;
    }
    
    $clients = get_option('custom_oauth_clients', []);
    $deleted_count = 0;
    
    foreach($client_ids as $client_id) {
        $client_id = sanitize_text_field($client_id);
        if (isset($clients[$client_id])) {
            unset($clients[$client_id]);
            custom_oauth_revoke_client_tokens($client_id);
            $deleted_count++;
        }
    }
    
    update_option('custom_oauth_clients', $clients);
    
    custom_oauth_log("Bulk deleted {$deleted_count} OAuth clients by user " . get_current_user_id());
    
    wp_redirect(add_query_arg([
        'page' => 'oauth-server',
        'message' => 'clients_deleted',
        'type' => 'success',
        'count' => $deleted_count
    ], admin_url('options-general.php')));
    exit;
}
