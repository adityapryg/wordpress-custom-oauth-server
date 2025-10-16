<?php
/**
 * Plugin Name: Custom OAuth Server
 * Plugin URI: https://github.com/adityapryg/wordpress-custom-oauth-server
 * Description: Professional OAuth 2.0 server for multiple web applications
 * Version: 1.0.0
 * Author: Aditya Prayoga
 * Author URI: https://adityapryg.my.id
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: custom-oauth-server
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('CUSTOM_OAUTH_VERSION', '1.0.0');
define('CUSTOM_OAUTH_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CUSTOM_OAUTH_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CUSTOM_OAUTH_INCLUDES_DIR', CUSTOM_OAUTH_PLUGIN_DIR . 'includes/');
define('CUSTOM_OAUTH_VIEWS_DIR', CUSTOM_OAUTH_PLUGIN_DIR . 'views/');

// Autoload classes
spl_autoload_register('custom_oauth_autoloader');

function custom_oauth_autoloader($class_name) {
    if (strpos($class_name, 'Custom_OAuth_') !== 0) {
        return;
    }
    
    // Map class names to actual file names
    $class_files = [
        'Custom_OAuth_Server' => 'class-oauth-server.php',
        'Custom_OAuth_Admin' => 'class-oauth-admin.php',
        'Custom_OAuth_Api' => 'class-oauth-api.php',
        'Custom_OAuth_List_Table' => 'class-oauth-list-table.php'
    ];
    
    if (isset($class_files[$class_name])) {
        $file_name = $class_files[$class_name];
    } else {
        $file_name = 'class-' . strtolower(str_replace('_', '-', $class_name)) . '.php';
    }
    
    $file_path = CUSTOM_OAUTH_INCLUDES_DIR . $file_name;
    
    if (file_exists($file_path)) {
        require_once $file_path;
    }
}

// Include core functions
require_once CUSTOM_OAUTH_INCLUDES_DIR . 'functions.php';

// Initialize the plugin
function custom_oauth_init() {
    new Custom_OAuth_Server();
}
add_action('plugins_loaded', 'custom_oauth_init');

// Activation hook
register_activation_hook(__FILE__, 'custom_oauth_activate');
function custom_oauth_activate() {
    // Manually load required classes during activation
    require_once CUSTOM_OAUTH_INCLUDES_DIR . 'functions.php';
    require_once CUSTOM_OAUTH_INCLUDES_DIR . 'class-oauth-server.php';
    
    // Load dependencies that Custom_OAuth_Server might need
    require_once CUSTOM_OAUTH_INCLUDES_DIR . 'class-oauth-admin.php';
    require_once CUSTOM_OAUTH_INCLUDES_DIR . 'class-oauth-api.php';
    
    // Now we can safely call the static activation method
    Custom_OAuth_Server::activate();
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'custom_oauth_deactivate');
function custom_oauth_deactivate() {
    if (class_exists('Custom_OAuth_Server')) {
        Custom_OAuth_Server::deactivate();
    }
}

// Uninstall hook
register_uninstall_hook(__FILE__, 'custom_oauth_uninstall');
function custom_oauth_uninstall() {
    // Load the class if needed for uninstall
    if (!class_exists('Custom_OAuth_Server')) {
        require_once CUSTOM_OAUTH_INCLUDES_DIR . 'class-oauth-server.php';
    }
    Custom_OAuth_Server::uninstall();
}
