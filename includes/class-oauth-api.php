<?php

if (!defined('ABSPATH')) {
    exit;
}

class Custom_OAuth_Api {
    
    public function __construct() {
        add_action('rest_api_init', [$this, 'register_rest_routes']);
    }
    
    public function register_rest_routes() {
        register_rest_route('oauth/v1', '/me', [
            'methods' => 'GET',
            'callback' => [$this, 'get_current_user'],
            'permission_callback' => [$this, 'validate_token']
        ]);
        
        register_rest_route('oauth/v1', '/userinfo', [
            'methods' => 'GET',
            'callback' => [$this, 'get_user_info'],
            'permission_callback' => [$this, 'validate_token']
        ]);
    }
    
    public function handle_token_request() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            custom_oauth_send_error('Method not allowed', 405);
        }
        
        $grant_type = $_POST['grant_type'] ?? '';
        
        switch($grant_type) {
            case 'authorization_code':
                $this->handle_authorization_code_grant();
                break;
            case 'refresh_token':
                $this->handle_refresh_token_grant();
                break;
            default:
                custom_oauth_send_error('Unsupported grant type', 400);
        }
    }
    
    private function handle_authorization_code_grant() {
        $code = sanitize_text_field($_POST['code'] ?? '');
        $client_id = sanitize_text_field($_POST['client_id'] ?? '');
        $client_secret = sanitize_text_field($_POST['client_secret'] ?? '');
        $redirect_uri = esc_url_raw($_POST['redirect_uri'] ?? '');
        
        // Validate client credentials
        if (!custom_oauth_validate_client($client_id, $client_secret)) {
            custom_oauth_send_error('Invalid client credentials', 401);
        }
        
        // Validate authorization code
        $code_data = custom_oauth_get_auth_code($code);
        if (!$code_data || $code_data['expires'] < time()) {
            custom_oauth_send_error('Invalid or expired authorization code', 400);
        }
        
        // Validate redirect URI
        if ($code_data['redirect_uri'] !== $redirect_uri) {
            custom_oauth_send_error('Invalid redirect URI', 400);
        }
        
        // Delete the authorization code (one-time use)
        custom_oauth_delete_auth_code($code);
        
        // Generate tokens
        $access_token = custom_oauth_generate_token(64);
        $refresh_token = custom_oauth_generate_token(64);
        $expires_in = 3600; // 1 hour
        
        $token_data = [
            'user_id' => $code_data['user_id'],
            'client_id' => $client_id,
            'scope' => $code_data['scope'],
            'expires' => time() + $expires_in
        ];
        
        // Store tokens
        custom_oauth_store_access_token($access_token, $token_data, $expires_in);
        custom_oauth_store_refresh_token($refresh_token, $token_data, 2592000); // 30 days
        
        custom_oauth_log("Access token generated for user {$code_data['user_id']}");
        
        // Send response
        custom_oauth_send_success([
            'access_token' => $access_token,
            'token_type' => 'Bearer',
            'expires_in' => $expires_in,
            'refresh_token' => $refresh_token,
            'scope' => $code_data['scope']
        ]);
    }
    
    private function handle_refresh_token_grant() {
        $refresh_token = sanitize_text_field($_POST['refresh_token'] ?? '');
        $client_id = sanitize_text_field($_POST['client_id'] ?? '');
        $client_secret = sanitize_text_field($_POST['client_secret'] ?? '');
        
        // Validate client credentials
        if (!custom_oauth_validate_client($client_id, $client_secret)) {
            custom_oauth_send_error('Invalid client credentials', 401);
        }
        
        // Validate refresh token
        $token_data = get_transient('oauth_refresh_token_' . $refresh_token);
        if (!$token_data || $token_data['client_id'] !== $client_id) {
            custom_oauth_send_error('Invalid refresh token', 400);
        }
        
        // Generate new access token
        $access_token = custom_oauth_generate_token(64);
        $expires_in = 3600;
        
        $new_token_data = array_merge($token_data, [
            'expires' => time() + $expires_in
        ]);
        
        custom_oauth_store_access_token($access_token, $new_token_data, $expires_in);
        
        custom_oauth_log("Access token refreshed for user {$token_data['user_id']}");
        
        custom_oauth_send_success([
            'access_token' => $access_token,
            'token_type' => 'Bearer',
            'expires_in' => $expires_in,
            'scope' => $token_data['scope']
        ]);
    }
    
    public function validate_token($request) {
        $auth_header = $request->get_header('authorization');
        
        if (!$auth_header || !preg_match('/Bearer\s+(.+)/', $auth_header, $matches)) {
            return new WP_Error('missing_token', 'Authorization token required', ['status' => 401]);
        }
        
        $token = $matches[1];
        $token_data = custom_oauth_get_access_token($token);
        
        if (!$token_data || $token_data['expires'] < time()) {
            return new WP_Error('invalid_token', 'Invalid or expired token', ['status' => 401]);
        }
        
        // Store user ID for the callback
        $request->set_param('oauth_user_id', $token_data['user_id']);
        $request->set_param('oauth_scope', $token_data['scope']);
        
        return true;
    }
    
    public function get_current_user($request) {
        $user_id = $request->get_param('oauth_user_id');
        $scope = $request->get_param('oauth_scope');
        
        $user = get_user_by('ID', $user_id);
        
        if (!$user) {
            return new WP_Error('user_not_found', 'User not found', ['status' => 404]);
        }
        
        $response = [
            'ID' => $user->ID,
            'user_login' => $user->user_login,
        ];
        
        // Add data based on scope
        $scopes = explode(' ', $scope);
        
        if (in_array('profile', $scopes) || in_array('basic', $scopes)) {
            $response['display_name'] = $user->display_name;
            $response['avatar_url'] = get_avatar_url($user->ID);
        }
        
        if (in_array('email', $scopes) || in_array('basic', $scopes)) {
            $response['user_email'] = $user->user_email;
        }
        
        return $response;
    }
    
    public function get_user_info($request) {
        // Alias for get_current_user for OpenID Connect compatibility
        return $this->get_current_user($request);
    }
}
