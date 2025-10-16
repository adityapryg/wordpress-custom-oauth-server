<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Custom_OAuth_List_Table extends WP_List_Table {
    
    public function __construct() {
        parent::__construct([
            'singular' => 'oauth_client',
            'plural' => 'oauth_clients',
            'ajax' => false
        ]);
    }
    
    public function get_columns() {
        return [
            'cb' => '<input type="checkbox" />',
            'name' => __('Client Name', 'custom-oauth-server'),
            'client_id' => __('Client ID', 'custom-oauth-server'),
            'redirect_uris' => __('Redirect URIs', 'custom-oauth-server'),
            'created' => __('Created', 'custom-oauth-server'),
            'actions' => __('Actions', 'custom-oauth-server')
        ];
    }
    
    public function get_sortable_columns() {
        return [
            'name' => ['name', false],
            'created' => ['created', false]
        ];
    }
    
    public function column_default($item, $column_name) {
        switch($column_name) {
            case 'name':
                return esc_html($item['name']);
            case 'client_id':
                return '<code>' . esc_html($item['client_id']) . '</code>';
            case 'redirect_uris':
                return esc_html(implode(', ', array_slice($item['redirect_uris'], 0, 2))) . 
                       (count($item['redirect_uris']) > 2 ? '...' : '');
            case 'created':
                return esc_html($item['created'] ?? __('Unknown', 'custom-oauth-server'));
            default:
                return '';
        }
    }
    
    public function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="clients[]" value="%s" />',
            esc_attr($item['client_id'])
        );
    }
    
    public function column_name($item) {
        $actions = [
            'view' => sprintf(
                '<a href="?page=%s&action=view&client=%s">%s</a>',
                esc_attr($_REQUEST['page']),
                esc_attr($item['client_id']),
                __('View', 'custom-oauth-server')
            ),
            'edit' => sprintf(
                '<a href="?page=%s&action=edit&client=%s">%s</a>',
                esc_attr($_REQUEST['page']),
                esc_attr($item['client_id']),
                __('Edit', 'custom-oauth-server')
            ),
            'delete' => sprintf(
                '<a href="?page=%s&action=delete&client=%s&oauth_nonce=%s" onclick="return confirm(\'%s\')">%s</a>',
                esc_attr($_REQUEST['page']),
                esc_attr($item['client_id']),
                wp_create_nonce('oauth_delete_client'),
                esc_js(__('Are you sure you want to delete this client?', 'custom-oauth-server')),
                __('Delete', 'custom-oauth-server')
            )
        ];
        
        return sprintf(
            '<strong><a href="?page=%s&action=view&client=%s">%s</a></strong>%s',
            esc_attr($_REQUEST['page']),
            esc_attr($item['client_id']),
            esc_html($item['name']),
            $this->row_actions($actions)
        );
    }
    
    public function column_actions($item) {
        return sprintf(
            '<a href="?page=%s&action=edit&client=%s" class="button button-small">%s</a> ' .
            '<a href="?page=%s&action=delete&client=%s&oauth_nonce=%s" class="button button-small" onclick="return confirm(\'%s\')">%s</a>',
            esc_attr($_REQUEST['page']),
            esc_attr($item['client_id']),
            __('Edit', 'custom-oauth-server'),
            esc_attr($_REQUEST['page']),
            esc_attr($item['client_id']),
            wp_create_nonce('oauth_delete_client'),
            esc_js(__('Are you sure?', 'custom-oauth-server')),
            __('Delete', 'custom-oauth-server')
        );
    }
    
    public function get_bulk_actions() {
        return [
            'delete' => __('Delete', 'custom-oauth-server')
        ];
    }
    
    public function prepare_items() {
        $columns = $this->get_columns();
        $hidden = [];
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = [$columns, $hidden, $sortable];
        
        $clients = get_option('custom_oauth_clients', []);
        $data = [];
        
        foreach($clients as $client_id => $client) {
            $data[] = array_merge($client, ['client_id' => $client_id]);
        }
        
        // Sort data
        $orderby = $_GET['orderby'] ?? 'name';
        $order = $_GET['order'] ?? 'asc';
        
        usort($data, function($a, $b) use ($orderby, $order) {
            $result = strcmp($a[$orderby] ?? '', $b[$orderby] ?? '');
            return $order === 'desc' ? -$result : $result;
        });
        
        // Pagination
        $per_page = 20;
        $current_page = $this->get_pagenum();
        $total_items = count($data);
        
        $data = array_slice($data, ($current_page - 1) * $per_page, $per_page);
        
        $this->items = $data;
        
        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ]);
    }
}
