<?php

if (!defined('ABSPATH')) {
    exit;
}

function custom_oauth_handle_create_client() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Insufficient permissions', 'custom-oauth-server'));
    }
    
    $name = sanitize_text_field($_POST['client_name'] ?? '');
    $client_id = sanitize_text_field($_POST['client_id'] ?? '');
    $client_secret = sanitize_text_field($_POST['client_secret'] ?? '');
    $redirect_uris = sanitize_textarea_field($_POST['redirect_uris'] ?? '');
    
    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = __('Client name is required', 'custom-oauth-server');
    }
    
    if (empty($client_id)) {
        $errors[] = __('Client ID is required', 'custom-oauth-server');
    } elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', $client_id)) {
        $errors[] = __('Client ID can only contain letters, numbers, hyphens, and underscores', 'custom-oauth-server');
    }
    
    if (empty($client_secret)) {
        $errors[] = __('Client secret is required', 'custom-oauth-server');
    } elseif (strlen($client_secret) < 16) {
        $errors[] = __('Client secret must be at least 16 characters long', 'custom-oauth-server');
    }
    
    if (empty($redirect_uris)) {
        $errors[] = __('At least one redirect URI is required', 'custom-oauth-server');
    }
    
    // Check if client ID already exists
    $clients = get_option('custom_oauth_clients', []);
    if (isset($clients[$client_id])) {
        $errors[] = __('Client ID already exists', 'custom-oauth-server');
    }
    
    if (!empty($errors)) {
        $error_message = implode('<br>', $errors);
        wp_die($error_message, __('Validation Error', 'custom-oauth-server'));
    }
    
    // Process redirect URIs
    $redirect_uris = array_map('trim', explode("\n", $redirect_uris));
    $redirect_uris = array_filter($redirect_uris);
    $redirect_uris = array_map('esc_url_raw', $redirect_uris);
    
    // Validate redirect URIs
    foreach($redirect_uris as $uri) {
        if (!filter_var($uri, FILTER_VALIDATE_URL)) {
            wp_die(__('Invalid redirect URI: ', 'custom-oauth-server') . esc_html($uri));
        }
    }
    
    // Save client
    $clients[$client_id] = [
        'name' => $name,
        'secret' => $client_secret,
        'redirect_uris' => $redirect_uris,
        'created' => current_time('mysql'),
        'created_by' => get_current_user_id()
    ];
    
    update_option('custom_oauth_clients', $clients);
    
    custom_oauth_log("OAuth client '{$client_id}' created by user " . get_current_user_id());
    
    // Redirect with success message
    wp_redirect(add_query_arg([
        'page' => 'oauth-server',
        'message' => 'client_created',
        'type' => 'success'
    ], admin_url('options-general.php')));
    exit;
}

function custom_oauth_handle_update_client() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Insufficient permissions', 'custom-oauth-server'));
    }
    
    $client_id = sanitize_text_field($_POST['client_id'] ?? '');
    $name = sanitize_text_field($_POST['client_name'] ?? '');
    $client_secret = sanitize_text_field($_POST['client_secret'] ?? '');
    $redirect_uris = sanitize_textarea_field($_POST['redirect_uris'] ?? '');
    
    if (empty($client_id)) {
        wp_die(__('Client ID is required', 'custom-oauth-server'));
    }
    
    $clients = get_option('custom_oauth_clients', []);
    
    if (!isset($clients[$client_id])) {
        wp_die(__('Client not found', 'custom-oauth-server'));
    }
    
    // Validation (similar to create)
    $errors = [];
    
    if (empty($name)) {
        $errors[] = __('Client name is required', 'custom-oauth-server');
    }
    
    if (empty($client_secret)) {
        $errors[] = __('Client secret is required', 'custom-oauth-server');
    } elseif (strlen($client_secret) < 16) {
        $errors[] = __('Client secret must be at least 16 characters long', 'custom-oauth-server');
    }
    
    if (empty($redirect_uris)) {
        $errors[] = __('At least one redirect URI is required', 'custom-oauth-server');
    }
    
    if (!empty($errors)) {
        $error_message = implode('<br>', $errors);
        wp_die($error_message, __('Validation Error', 'custom-oauth-server'));
    }
    
    // Process redirect URIs
    $redirect_uris = array_map('trim', explode("\n", $redirect_uris));
    $redirect_uris = array_filter($redirect_uris);
    $redirect_uris = array_map('esc_url_raw', $redirect_uris);
    
    // Update client
    $clients[$client_id] = array_merge($clients[$client_id], [
        'name' => $name,
        'secret' => $client_secret,
        'redirect_uris' => $redirect_uris,
        'updated' => current_time('mysql'),
        'updated_by' => get_current_user_id()
    ]);
    
    update_option('custom_oauth_clients', $clients);
    
    custom_oauth_log("OAuth client '{$client_id}' updated by user " . get_current_user_id());
    
    // Redirect with success message
    wp_redirect(add_query_arg([
        'page' => 'oauth-server',
        'action' => 'edit',
        'client' => $client_id,
        'message' => 'client_updated',
        'type' => 'success'
    ], admin_url('options-general.php')));
    exit;
}
