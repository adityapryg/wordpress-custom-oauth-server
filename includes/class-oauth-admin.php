<?php

if (!defined('ABSPATH')) {
    exit;
}

class Custom_OAuth_Admin {
    
    private $list_table;
    
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'handle_admin_actions']);
        add_action('admin_notices', [$this, 'admin_notices']);
    }
    
    public function add_admin_menu() {
        add_options_page(
            __('OAuth Server Settings', 'custom-oauth-server'),
            __('OAuth Server', 'custom-oauth-server'),
            'manage_options',
            'oauth-server',
            [$this, 'admin_page']
        );
    }
    
    public function admin_page() {
        $action = $_GET['action'] ?? 'list';
        $client_id = $_GET['client'] ?? '';
        
        switch($action) {
            case 'new':
                $this->show_new_client_page();
                break;
            case 'edit':
                $this->show_edit_client_page($client_id);
                break;
            case 'view':
                $this->show_single_client_page($client_id);
                break;
            default:
                $this->show_clients_list_page();
        }
    }
    
    private function show_clients_list_page() {
        if (!$this->list_table) {
            $this->list_table = new Custom_OAuth_List_Table();
        }
        
        $this->list_table->prepare_items();
        
        custom_oauth_get_template('admin/list', [
            'list_table' => $this->list_table
        ]);
    }
    
    private function show_new_client_page() {
        custom_oauth_get_template('admin/new');
    }
    
    private function show_edit_client_page($client_id) {
        $client = custom_oauth_get_client($client_id);
        if (!$client) {
            wp_die(__('Client not found', 'custom-oauth-server'));
        }
        
        custom_oauth_get_template('admin/edit', [
            'client_id' => $client_id,
            'client' => $client
        ]);
    }
    
    private function show_single_client_page($client_id) {
        $client = custom_oauth_get_client($client_id);
        if (!$client) {
            wp_die(__('Client not found', 'custom-oauth-server'));
        }
        
        custom_oauth_get_template('admin/single', [
            'client_id' => $client_id,
            'client' => $client
        ]);
    }
    
    public function handle_admin_actions() {
        if (!isset($_POST['oauth_action']) || !wp_verify_nonce($_POST['oauth_nonce'], 'oauth_admin_action')) {
            return;
        }
        
        $action = $_POST['oauth_action'];
        
        switch($action) {
            case 'create_client':
                require_once CUSTOM_OAUTH_INCLUDES_DIR . 'form-handler.php';
                custom_oauth_handle_create_client();
                break;
            case 'update_client':
                require_once CUSTOM_OAUTH_INCLUDES_DIR . 'form-handler.php';
                custom_oauth_handle_update_client();
                break;
            case 'delete_client':
                require_once CUSTOM_OAUTH_INCLUDES_DIR . 'delete-handler.php';
                custom_oauth_handle_delete_client();
                break;
        }
    }
    
    public function admin_notices() {
        $message = $_GET['message'] ?? '';
        $type = $_GET['type'] ?? 'success';
        
        if (empty($message)) {
            return;
        }
        
        $messages = [
            'client_created' => __('OAuth client created successfully.', 'custom-oauth-server'),
            'client_updated' => __('OAuth client updated successfully.', 'custom-oauth-server'),
            'client_deleted' => __('OAuth client deleted successfully.', 'custom-oauth-server'),
            'error' => __('An error occurred. Please try again.', 'custom-oauth-server'),
        ];
        
        if (isset($messages[$message])) {
            printf(
                '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
                esc_attr($type),
                esc_html($messages[$message])
            );
        }
    }
}
