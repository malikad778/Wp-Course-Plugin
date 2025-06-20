<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Course_Subscription
 * @subpackage WP_Course_Subscription/admin
 */

class WP_Course_Subscription_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name       The name of the plugin.
     * @param    string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/admin.js', array('jquery'), $this->version, false);
    }

    /**
     * Add plugin admin menu.
     *
     * @since    1.0.0
     */
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            __('Course Subscriptions', 'wp-course-subscription'),
            __('Course Subscriptions', 'wp-course-subscription'),
            'manage_options',
            'wp-course-subscription',
            array($this, 'display_plugin_admin_dashboard'),
            'dashicons-cart',
            30
        );

        // Orders submenu
        add_submenu_page(
            'wp-course-subscription',
            __('Orders', 'wp-course-subscription'),
            __('Orders', 'wp-course-subscription'),
            'manage_options',
            'wp-course-subscription-orders',
            array($this, 'display_plugin_admin_orders')
        );

        // Subscription Plans submenu
        add_submenu_page(
            'wp-course-subscription',
            __('Subscription Plans', 'wp-course-subscription'),
            __('Subscription Plans', 'wp-course-subscription'),
            'manage_options',
            'wp-course-subscription-plans',
            array($this, 'display_plugin_admin_plans')
        );

        // Settings submenu
        add_submenu_page(
            'wp-course-subscription',
            __('Settings', 'wp-course-subscription'),
            __('Settings', 'wp-course-subscription'),
            'manage_options',
            'wp-course-subscription-settings',
            array($this, 'display_plugin_admin_settings')
        );
    }

    /**
     * Display the plugin admin dashboard.
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_dashboard() {
        include_once 'partials/admin-dashboard.php';
    }

    /**
     * Display the plugin admin orders page.
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_orders() {
        // Process actions
        if (isset($_GET['action']) && isset($_GET['order_id'])) {
            $action = sanitize_text_field($_GET['action']);
            $order_id = intval($_GET['order_id']);
            
            if ($action === 'view') {
                $this->display_order_details($order_id);
                return;
            }
        }
        
        // Create orders list table
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-orders-list-table.php';
        $orders_table = new WP_Course_Subscription_Orders_List_Table();
        $orders_table->prepare_items();
        
        include_once 'partials/admin-orders.php';
    }

    /**
     * Display order details.
     *
     * @since    1.0.0
     * @param    int    $order_id    Order ID.
     */
    private function display_order_details($order_id) {
        global $wpdb;
        $orders_table = $wpdb->prefix . 'course_orders';
        
        $order = $wpdb->get_row($wpdb->prepare("
            SELECT o.*, u.user_login, u.user_email, u.display_name,
                   s.id as subscription_id, s.status as subscription_status, s.start_date, s.end_date,
                   p.id as plan_id, p.name as plan_name, p.price as plan_price, p.duration as plan_duration
            FROM $orders_table o
            LEFT JOIN {$wpdb->users} u ON o.user_id = u.ID
            LEFT JOIN {$wpdb->prefix}course_subscriptions s ON o.subscription_id = s.id
            LEFT JOIN {$wpdb->prefix}course_subscription_plans p ON s.plan_id = p.id
            WHERE o.id = %d
        ", $order_id));
        
        if (!$order) {
            wp_die(__('Order not found.', 'wp-course-subscription'));
        }
        
        include_once 'partials/admin-order-details.php';
    }

    /**
     * Display the plugin admin subscription plans page.
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_plans() {
        // Process form submissions
        if (isset($_POST['wcs_add_plan']) && check_admin_referer('wcs_add_plan_nonce')) {
            $this->process_add_plan();
        } elseif (isset($_POST['wcs_edit_plan']) && check_admin_referer('wcs_edit_plan_nonce')) {
            $this->process_edit_plan();
        } elseif (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['plan_id'])) {
            $this->process_delete_plan(intval($_GET['plan_id']));
        }
        
       if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['plan_id'])) {
    $this->display_edit_plan_form(intval($_GET['plan_id']));
} elseif (isset($_GET['action']) && $_GET['action'] === 'add') {
    // Show add form (no $plan set)
    include_once 'partials/admin-plan-form.php';
} else {
    $this->display_plans_list();
}
    }

    /**
     * Display subscription plans list.
     *
     * @since    1.0.0
     */
    private function display_plans_list() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'course_subscription_plans';
        
        $plans = $wpdb->get_results("SELECT * FROM $table_name ORDER BY price ASC");
        
        include_once 'partials/admin-plans-list.php';
    }

    /**
     * Display edit plan form.
     *
     * @since    1.0.0
     * @param    int    $plan_id    Plan ID.
     */
    private function display_edit_plan_form($plan_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'course_subscription_plans';
        
        $plan = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $plan_id));
        
        if (!$plan) {
            wp_die(__('Plan not found.', 'wp-course-subscription'));
        }
        
        include_once 'partials/admin-plan-form.php';
    }

    /**
     * Process add plan form.
     *
     * @since    1.0.0
     */
    private function process_add_plan() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'course_subscription_plans';
        
        $name = sanitize_text_field($_POST['name']);
        $description = sanitize_textarea_field($_POST['description']);
        $price = floatval($_POST['price']);
        $duration = intval($_POST['duration']);
        $status = sanitize_text_field($_POST['status']);
        
        $wpdb->insert(
            $table_name,
            array(
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'duration' => $duration,
                'stripe_price_id' => '',
                'status' => $status
            )
        );
        
        add_action('admin_notices', array($this, 'plan_added_notice'));
          // Redirect to avoid duplicate submissions
        wp_redirect(admin_url('admin.php?page=wp-course-subscription-plans'));
        exit;
    }

    /**
     * Process edit plan form.
     *
     * @since    1.0.0
     */
    private function process_edit_plan() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'course_subscription_plans';
        
        $plan_id = intval($_POST['plan_id']);
        $name = sanitize_text_field($_POST['name']);
        $description = sanitize_textarea_field($_POST['description']);
        $price = floatval($_POST['price']);
        $duration = intval($_POST['duration']);
        $status = sanitize_text_field($_POST['status']);
        
        $wpdb->update(
            $table_name,
            array(
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'duration' => $duration,
                'status' => $status
            ),
            array('id' => $plan_id)
        );
        
        add_action('admin_notices', array($this, 'plan_updated_notice'));
          // Redirect to avoid duplicate submissions
        wp_redirect(admin_url('admin.php?page=wp-course-subscription-plans&message=updated'));
        exit;
    }

    /**
     * Process delete plan.
     *
     * @since    1.0.0
     * @param    int    $plan_id    Plan ID.
     */
    private function process_delete_plan($plan_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'course_subscription_plans';
        
      $wpdb->delete(
            $table_name,
            array('id' => $plan_id)
        );
        add_action('admin_notices', array($this, 'plan_deleted_notice'));
    }

    /**
     * Display the plugin admin settings page.
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_settings() {
        // Process form submission
        if (isset($_POST['wcs_save_settings']) && check_admin_referer('wcs_settings_nonce')) {
            $this->process_save_settings();
        }
        
        // Get current settings
        $stripe_secret_key = get_option('wcs_stripe_secret_key', '');
        $stripe_publishable_key = get_option('wcs_stripe_publishable_key', '');
        $stripe_webhook_secret = get_option('wcs_stripe_webhook_secret', '');
        $stripe_test_mode = get_option('wcs_stripe_test_mode', 'yes');
        $stripe_test_secret_key = get_option('wcs_stripe_test_secret_key', '');
        $stripe_test_publishable_key = get_option('wcs_stripe_test_publishable_key', '');
        $stripe_test_webhook_secret = get_option('wcs_stripe_test_webhook_secret', '');
        
        // PayPal settings
        $paypal_client_id = get_option('wcs_paypal_client_id', '');
        $paypal_client_secret = get_option('wcs_paypal_client_secret', '');
        $paypal_webhook_id = get_option('wcs_paypal_webhook_id', '');
        $paypal_test_mode = get_option('wcs_paypal_test_mode', 'yes');
        $paypal_test_client_id = get_option('wcs_paypal_test_client_id', '');
        $paypal_test_client_secret = get_option('wcs_paypal_test_client_secret', '');
        $paypal_test_webhook_id = get_option('wcs_paypal_test_webhook_id', '');
        
        $subscription_page_id = get_option('wcs_subscription_page_id', 0);
        $checkout_page_id = get_option('wcs_checkout_page_id', 0);
        $thank_you_page_id = get_option('wcs_thank_you_page_id', 0);
        
        include_once 'partials/admin-settings.php';
    }

    /**
     * Process save settings form.
     *
     * @since    1.0.0
     */
    private function process_save_settings() {
        // Stripe API settings
        $stripe_secret_key = sanitize_text_field($_POST['stripe_secret_key']);
        $stripe_publishable_key = sanitize_text_field($_POST['stripe_publishable_key']);
        $stripe_webhook_secret = sanitize_text_field($_POST['stripe_webhook_secret']);
        $stripe_test_mode = isset($_POST['stripe_test_mode']) ? 'yes' : 'no';
        $stripe_test_secret_key = sanitize_text_field($_POST['stripe_test_secret_key']);
        $stripe_test_publishable_key = sanitize_text_field($_POST['stripe_test_publishable_key']);
        $stripe_test_webhook_secret = sanitize_text_field($_POST['stripe_test_webhook_secret']);
        
        update_option('wcs_stripe_secret_key', $stripe_secret_key);
        update_option('wcs_stripe_publishable_key', $stripe_publishable_key);
        update_option('wcs_stripe_webhook_secret', $stripe_webhook_secret);
        update_option('wcs_stripe_test_mode', $stripe_test_mode);
        update_option('wcs_stripe_test_secret_key', $stripe_test_secret_key);
        update_option('wcs_stripe_test_publishable_key', $stripe_test_publishable_key);
        update_option('wcs_stripe_test_webhook_secret', $stripe_test_webhook_secret);
        
        // PayPal API settings
        $paypal_client_id = sanitize_text_field($_POST['paypal_client_id']);
        $paypal_client_secret = sanitize_text_field($_POST['paypal_client_secret']);
        $paypal_webhook_id = sanitize_text_field($_POST['paypal_webhook_id']);
        $paypal_test_mode = isset($_POST['paypal_test_mode']) ? 'yes' : 'no';
        $paypal_test_client_id = sanitize_text_field($_POST['paypal_test_client_id']);
        $paypal_test_client_secret = sanitize_text_field($_POST['paypal_test_client_secret']);
        $paypal_test_webhook_id = sanitize_text_field($_POST['paypal_test_webhook_id']);
        
        update_option('wcs_paypal_client_id', $paypal_client_id);
        update_option('wcs_paypal_client_secret', $paypal_client_secret);
        update_option('wcs_paypal_webhook_id', $paypal_webhook_id);
        update_option('wcs_paypal_test_mode', $paypal_test_mode);
        update_option('wcs_paypal_test_client_id', $paypal_test_client_id);
        update_option('wcs_paypal_test_client_secret', $paypal_test_client_secret);
        update_option('wcs_paypal_test_webhook_id', $paypal_test_webhook_id);
        
        // Page settings
        $subscription_page_id = isset($_POST['subscription_page']) ? intval($_POST['subscription_page']) : 0;
        $checkout_page_id = isset($_POST['checkout_page']) ? intval($_POST['checkout_page']) : 0;
        $thank_you_page_id = isset($_POST['thank_you_page']) ? intval($_POST['thank_you_page']) : 0;
        
        update_option('wcs_subscription_page_id', $subscription_page_id);
        update_option('wcs_checkout_page_id', $checkout_page_id);
        update_option('wcs_thank_you_page_id', $thank_you_page_id);
        
        add_action('admin_notices', array($this, 'settings_saved_notice'));
    }

    /**
     * Display plan added notice.
     *
     * @since    1.0.0
     */
    public function plan_added_notice() {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Subscription plan added successfully.', 'wp-course-subscription'); ?></p>
        </div>
        <?php
    }

    /**
     * Display plan updated notice.
     *
     * @since    1.0.0
     */
    public function plan_updated_notice() {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Subscription plan updated successfully.', 'wp-course-subscription'); ?></p>
        </div>
        <?php
    }

    /**
     * Display plan deleted notice.
     *
     * @since    1.0.0
     */
    public function plan_deleted_notice() {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Subscription plan deleted successfully.', 'wp-course-subscription'); ?></p>
        </div>
        <?php
    }

    /**
     * Display settings saved notice.
     *
     * @since    1.0.0
     */
    public function settings_saved_notice() {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Settings saved successfully.', 'wp-course-subscription'); ?></p>
        </div>
        <?php
    }
}
