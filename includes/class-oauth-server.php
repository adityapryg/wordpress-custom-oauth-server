<?php

if (!defined('ABSPATH')) {
    exit;
}

class Custom_OAuth_Server {
    
    private $admin;
    private $api;
    
    public function __construct() {
        $this->init_hooks();
        $this->load_dependencies();
    }
    
    private function init_hooks() {
        add_action('init', [$this, 'init_rewrite_rules']);
        add_action('template_redirect', [$this, 'handle_oauth_requests']);
        add_filter('query_vars', [$this, 'add_query_vars']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
    }
    
    private function load_dependencies() {
        if (is_admin()) {
            $this->admin = new Custom_OAuth_Admin();
        }
        $this->api = new Custom_OAuth_Api();
    }
    
    public function init_rewrite_rules() {
        add_rewrite_rule('^oauth/authorize/?', 'index.php?oauth_action=authorize', 'top');
        add_rewrite_rule('^oauth/token/?', 'index.php?oauth_action=token', 'top');
        add_rewrite_rule('^oauth/callback/?', 'index.php?oauth_action=callback', 'top');
    }
    
    public function add_query_vars($vars) {
        $vars[] = 'oauth_action';
        return $vars;
    }
    
    public function handle_oauth_requests() {
        $oauth_action = get_query_var('oauth_action');
        
        if (!$oauth_action) {
            return;
        }
        
        custom_oauth_log("Handling OAuth action: {$oauth_action}");
        
        switch($oauth_action) {
            case 'authorize':
                $this->handle_authorize();
                break;
            case 'token':
                $this->handle_token();
                break;
            case 'callback':
                $this->handle_callback();
                break;
            default:
                wp_die('Invalid OAuth action', 'OAuth Error', ['response' => 400]);
        }
    }
    
    public function handle_authorize() {
        if (!session_id()) {
            session_start();
        }
        
        $client_id = sanitize_text_field($_GET['client_id'] ?? '');
        $redirect_uri = esc_url_raw($_GET['redirect_uri'] ?? '');
        $state = sanitize_text_field($_GET['state'] ?? '');
        $scope = custom_oauth_sanitize_scope($_GET['scope'] ?? 'basic');
        
        // Validate required parameters
        if (empty($client_id) || empty($redirect_uri)) {
            wp_die('Missing required parameters', 'OAuth Error', ['response' => 400]);
        }
        
        // Validate client
        $client = custom_oauth_validate_client($client_id);
        if (!$client) {
            wp_die('Invalid client_id', 'OAuth Error', ['response' => 401]);
        }
        
        // Validate redirect URI
        if (!custom_oauth_validate_redirect_uri($client_id, $redirect_uri)) {
            wp_die('Invalid redirect_uri', 'OAuth Error', ['response' => 400]);
        }
        
        // Store OAuth data in session
        $_SESSION['custom_oauth_data'] = [
            'client_id' => $client_id,
            'redirect_uri' => $redirect_uri,
            'state' => $state,
            'scope' => $scope,
            'client_name' => $client['name']
        ];
        
        // If user not logged in, redirect to login
        if (!is_user_logged_in()) {
            $login_url = wp_login_url(home_url('/oauth/callback'));
            wp_redirect($login_url);
            exit;
        }
        
        // Show authorization page
        $this->show_authorization_page();
    }
    
    private function show_authorization_page() {
        $current_user = wp_get_current_user();
        $oauth_data = $_SESSION['custom_oauth_data'] ?? [];
        
        if (empty($oauth_data)) {
            wp_die('Invalid OAuth session', 'OAuth Error', ['response' => 400]);
        }
        
        // Load authorization template
        custom_oauth_get_template('oauth/authorize', [
            'current_user' => $current_user,
            'oauth_data' => $oauth_data
        ]);
        
        exit;
    }
    
    public function handle_callback() {
        if (!session_id()) {
            session_start();
        }
        
        if (!isset($_SESSION['custom_oauth_data'])) {
            wp_die('Invalid OAuth session', 'OAuth Error', ['response' => 400]);
        }
        
        $oauth_data = $_SESSION['custom_oauth_data'];
        
        // Check authorization
        if (($_POST['authorize'] ?? '') !== 'yes') {
            $redirect_url = add_query_arg([
                'error' => 'access_denied',
                'state' => $oauth_data['state']
            ], $oauth_data['redirect_uri']);
            wp_redirect($redirect_url);
            exit;
        }
        
        // Generate authorization code
        $code = custom_oauth_generate_token(32);
        $current_user = wp_get_current_user();
        
        // Store authorization code
        custom_oauth_store_auth_code($code, [
            'user_id' => $current_user->ID,
            'client_id' => $oauth_data['client_id'],
            'redirect_uri' => $oauth_data['redirect_uri'],
            'scope' => $oauth_data['scope'],
            'expires' => time() + 600
        ]);
        
        // Clean up session
        unset($_SESSION['custom_oauth_data']);
        
        custom_oauth_log("Authorization code generated for user {$current_user->ID}");
        
        // Redirect with authorization code
        $redirect_url = add_query_arg([
            'code' => $code,
            'state' => $oauth_data['state']
        ], $oauth_data['redirect_uri']);
        
        wp_redirect($redirect_url);
        exit;
    }
    
    public function handle_token() {
        // This is handled by the API class
        $this->api->handle_token_request();
    }
    
    public function enqueue_scripts() {
        $oauth_action = get_query_var('oauth_action');
        if ($oauth_action === 'authorize') {
            wp_enqueue_style(
                'custom-oauth-authorize',
                CUSTOM_OAUTH_PLUGIN_URL . 'assets/css/authorize.css',
                [],
                CUSTOM_OAUTH_VERSION
            );
        }
    }
    
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'oauth-server') === false) {
            return;
        }
        
        wp_enqueue_style(
            'custom-oauth-admin',
            CUSTOM_OAUTH_PLUGIN_URL . 'assets/css/admin.css',
            [],
            CUSTOM_OAUTH_VERSION
        );
        
        wp_enqueue_script(
            'custom-oauth-admin',
            CUSTOM_OAUTH_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            CUSTOM_OAUTH_VERSION,
            true
        );
    }
    
    public static function activate() {
        // Create database tables if needed
        self::create_tables();
        
        // Set default options
        if (!get_option('custom_oauth_clients')) {
            update_option('custom_oauth_clients', []);
        }
        
        // Flush rewrite rules
        add_rewrite_rule('^oauth/authorize/?', 'index.php?oauth_action=authorize', 'top');
        add_rewrite_rule('^oauth/token/?', 'index.php?oauth_action=token', 'top');
        add_rewrite_rule('^oauth/callback/?', 'index.php?oauth_action=callback', 'top');
        flush_rewrite_rules();
        
        custom_oauth_log('Plugin activated');
    }
    
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
        custom_oauth_log('Plugin deactivated');
    }
    
    public static function uninstall() {
        // Remove all plugin data
        delete_option('custom_oauth_clients');
        delete_option('custom_oauth_settings');
        
        // Remove transients
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '%oauth_code_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '%oauth_access_token_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '%oauth_refresh_token_%'");
        
        custom_oauth_log('Plugin uninstalled');
    }
    
    private static function create_tables() {
        // Future: Create custom tables for better performance
        // For now, we use WordPress options and transients
    }
}
