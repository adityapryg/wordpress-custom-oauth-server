<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Generate secure random string
 */
function custom_oauth_generate_token($length = 32) {
    return wp_generate_password($length, false);
}

/**
 * Validate OAuth client
 */
function custom_oauth_validate_client($client_id, $client_secret = null) {
    $clients = get_option('custom_oauth_clients', []);
    
    if (!isset($clients[$client_id])) {
        return false;
    }
    
    if ($client_secret !== null && $clients[$client_id]['secret'] !== $client_secret) {
        return false;
    }
    
    return $clients[$client_id];
}

/**
 * Get OAuth client by ID
 */
function custom_oauth_get_client($client_id) {
    $clients = get_option('custom_oauth_clients', []);
    return isset($clients[$client_id]) ? $clients[$client_id] : false;
}

/**
 * Store OAuth authorization code
 */
function custom_oauth_store_auth_code($code, $data) {
    return set_transient('oauth_code_' . $code, $data, 600); // 10 minutes
}

/**
 * Get OAuth authorization code data
 */
function custom_oauth_get_auth_code($code) {
    return get_transient('oauth_code_' . $code);
}

/**
 * Delete OAuth authorization code
 */
function custom_oauth_delete_auth_code($code) {
    return delete_transient('oauth_code_' . $code);
}

/**
 * Store OAuth access token
 */
function custom_oauth_store_access_token($token, $data, $expires = 3600) {
    return set_transient('oauth_access_token_' . $token, $data, $expires);
}

/**
 * Get OAuth access token data
 */
function custom_oauth_get_access_token($token) {
    return get_transient('oauth_access_token_' . $token);
}

/**
 * Store OAuth refresh token
 */
function custom_oauth_store_refresh_token($token, $data, $expires = 2592000) {
    return set_transient('oauth_refresh_token_' . $token, $data, $expires);
}

/**
 * Send JSON error response
 */
function custom_oauth_send_error($message, $status = 400) {
    http_response_code($status);
    header('Content-Type: application/json');
    wp_send_json_error(['error' => $message]);
}

/**
 * Send JSON success response
 */
function custom_oauth_send_success($data) {
    header('Content-Type: application/json');
    wp_send_json_success($data);
}

/**
 * Validate redirect URI
 */
function custom_oauth_validate_redirect_uri($client_id, $redirect_uri) {
    $client = custom_oauth_get_client($client_id);
    if (!$client) {
        return false;
    }
    
    return in_array($redirect_uri, $client['redirect_uris']);
}

/**
 * Log OAuth activity
 */
function custom_oauth_log($message, $level = 'info') {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log(sprintf('[Custom OAuth] %s: %s', strtoupper($level), $message));
    }
}

/**
 * Get plugin template
 */
function custom_oauth_get_template($template, $args = []) {
    $template_path = CUSTOM_OAUTH_VIEWS_DIR . $template . '.php';
    
    if (!file_exists($template_path)) {
        return false;
    }
    
    extract($args);
    include $template_path;
    return true;
}

/**
 * Sanitize OAuth scope
 */
function custom_oauth_sanitize_scope($scope) {
    $allowed_scopes = ['basic', 'profile', 'email'];
    $scopes = explode(' ', $scope);
    $scopes = array_intersect($scopes, $allowed_scopes);
    return implode(' ', $scopes);
}
