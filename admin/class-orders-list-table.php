<?php
/**
 * Orders List Table Class
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Course_Subscription
 * @subpackage WP_Course_Subscription/admin
 */

// If WP_List_Table is not loaded, load it
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class WP_Course_Subscription_Orders_List_Table extends WP_List_Table {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(array(
            'singular' => 'order',
            'plural'   => 'orders',
            'ajax'     => false
        ));
    }

    /**
     * Get columns
     *
     * @return array
     */
    public function get_columns() {
        return array(
            'cb'              => '<input type="checkbox" />',
            'id'              => __('ID', 'wp-course-subscription'),
            'customer'        => __('Customer', 'wp-course-subscription'),
            'subscription'    => __('Subscription', 'wp-course-subscription'),
            'amount'          => __('Amount', 'wp-course-subscription'),
            'status'          => __('Status', 'wp-course-subscription'),
            'date'            => __('Date', 'wp-course-subscription'),
            'actions'         => __('Actions', 'wp-course-subscription')
        );
    }

    /**
     * Get sortable columns
     *
     * @return array
     */
    public function get_sortable_columns() {
        return array(
            'id'     => array('id', true),
            'amount' => array('amount', false),
            'status' => array('status', false),
            'date'   => array('created_at', false)
        );
    }

    /**
     * Column default
     *
     * @param object $item
     * @param string $column_name
     * @return string
     */
    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'id':
                return $item->id;
            case 'amount':
                return '$' . number_format($item->amount, 2);
            case 'status':
                return $this->get_status_badge($item->status);
            case 'date':
                return date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($item->created_at));
            default:
                return print_r($item, true);
        }
    }

    /**
     * Get status badge
     *
     * @param string $status
     * @return string
     */
    private function get_status_badge($status) {
        $badges = array(
            'pending'   => '<span class="wcs-badge wcs-badge-warning">Pending</span>',
            'completed' => '<span class="wcs-badge wcs-badge-success">Completed</span>',
            'failed'    => '<span class="wcs-badge wcs-badge-danger">Failed</span>',
            'refunded'  => '<span class="wcs-badge wcs-badge-info">Refunded</span>'
        );

        return isset($badges[$status]) ? $badges[$status] : $status;
    }

    /**
     * Column customer
     *
     * @param object $item
     * @return string
     */
    public function column_customer($item) {
        $user = get_user_by('id', $item->user_id);
        
        if (!$user) {
            return __('Unknown', 'wp-course-subscription');
        }
        
        $html = '<strong>' . esc_html($user->display_name) . '</strong><br>';
        $html .= '<small>' . esc_html($user->user_email) . '</small>';
        
        return $html;
    }

    /**
     * Column subscription
     *
     * @param object $item
     * @return string
     */
    public function column_subscription($item) {
        if (!$item->subscription_id) {
            return __('N/A', 'wp-course-subscription');
        }
        
        global $wpdb;
        $subscription = $wpdb->get_row($wpdb->prepare("
            SELECT s.*, p.name as plan_name
            FROM {$wpdb->prefix}course_subscriptions s
            JOIN {$wpdb->prefix}course_subscription_plans p ON s.plan_id = p.id
            WHERE s.id = %d
        ", $item->subscription_id));
        
        if (!$subscription) {
            return __('Unknown', 'wp-course-subscription');
        }
        
        $html = '<strong>' . esc_html($subscription->plan_name) . '</strong><br>';
        $html .= '<small>' . $this->get_status_badge($subscription->status) . '</small>';
        
        return $html;
    }

    /**
     * Column actions
     *
     * @param object $item
     * @return string
     */
    public function column_actions($item) {
        $actions = array(
            'view' => sprintf(
                '<a href="%s" class="button button-small">%s</a>',
                admin_url('admin.php?page=wp-course-subscription-orders&action=view&order_id=' . $item->id),
                __('View', 'wp-course-subscription')
            )
        );
        
        return implode(' ', $actions);
    }

    /**
     * Column cb
     *
     * @param object $item
     * @return string
     */
    public function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="order_id[]" value="%s" />',
            $item->id
        );
    }

    /**
     * Get bulk actions
     *
     * @return array
     */
    public function get_bulk_actions() {
        return array(
            'delete' => __('Delete', 'wp-course-subscription')
        );
    }

    /**
     * Process bulk actions
     */
    public function process_bulk_actions() {
        if ('delete' === $this->current_action() && isset($_POST['order_id'])) {
            $order_ids = array_map('intval', $_POST['order_id']);
            
            global $wpdb;
            $table_name = $wpdb->prefix . 'course_orders';
            
            foreach ($order_ids as $order_id) {
                $wpdb->delete($table_name, array('id' => $order_id));
            }
            
            wp_redirect(admin_url('admin.php?page=wp-course-subscription-orders&message=deleted'));
            exit;
        }
    }

    /**
     * Prepare items
     */
    public function prepare_items() {
        $this->process_bulk_actions();
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        $per_page = 20;
        $current_page = $this->get_pagenum();
        $offset = ($current_page - 1) * $per_page;
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'course_orders';
        
        // Get total items
        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");
        
        // Get order by
        $orderby = isset($_REQUEST['orderby']) ? sanitize_text_field($_REQUEST['orderby']) : 'id';
        $order = isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc')) ? sanitize_text_field($_REQUEST['order']) : 'desc';
        
        // Get items
        $this->items = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM $table_name
            ORDER BY $orderby $order
            LIMIT %d OFFSET %d
        ", $per_page, $offset));
        
        // Set pagination args
        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ));
    }
}
