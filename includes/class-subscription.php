<?php
/**
 * The class responsible for integrating with Stripe payment gateway.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Course_Subscription
 * @subpackage WP_Course_Subscription/includes
 */

class WP_Course_Subscription_Subscription {

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
     * Get subscription plans
     *
     * @since    1.0.0
     * @return   array    Array of subscription plans.
     */
    public static function get_subscription_plans() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'course_subscription_plans';
        
        $plans = $wpdb->get_results("SELECT * FROM $table_name WHERE status = 'active' ORDER BY price ASC");
        
        return $plans;
    }

    /**
     * Get subscription plan by ID
     *
     * @since    1.0.0
     * @param    int       $plan_id    Plan ID.
     * @return   object                Plan object.
     */
    public static function get_plan($plan_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'course_subscription_plans';
        
        $plan = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $plan_id));
        
        return $plan;
    }

    /**
     * Get active subscription for current user
     *
     * @since    1.0.0
     * @param    int       $user_id    User ID. If not provided, current user ID is used.
     * @return   object                Subscription object or null if no active subscription.
     */
    public static function get_active_subscription($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        if (!$user_id) {
            return null;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'course_subscriptions';
        
        $subscription = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM $table_name 
            WHERE user_id = %d 
            AND status = 'active' 
            AND (end_date IS NULL OR end_date > %s)
            ORDER BY id DESC
        ", $user_id, current_time('mysql')));
        
        return $subscription;
    }
/**
 * Update order with subscription ID
 *
 * @since    1.0.0
 * @param    int       $order_id         Order ID.
 * @param    int       $subscription_id  Subscription ID.
 * @return   boolean                     True if successful, false otherwise.
 */
public function update_order_subscription($order_id, $subscription_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'course_orders';
    
    $result = $wpdb->update(
        $table_name,
        array('subscription_id' => $subscription_id),
        array('id' => $order_id)
    );
    
    return $result !== false;
}

    /**
     * Create subscription
     *
     * @since    1.0.0
     * @param    int       $user_id    User ID.
     * @param    int       $plan_id    Plan ID.
     * @param    string    $payment_id Payment ID.
     * @return   int                   Subscription ID.
     */
    public function create_subscription($user_id, $plan_id, $payment_id = '') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'course_subscriptions';
        
        $plan = self::get_plan($plan_id);
        
        if (!$plan) {
            return false;
        }
        
        // Calculate end date if plan has duration
        $end_date = null;
        if ($plan->duration > 0) {
            $end_date = date('Y-m-d H:i:s', strtotime('+' . $plan->duration . ' days'));
        }
        
        $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'plan_id' => $plan_id,
                'status' => 'active',
                'start_date' => current_time('mysql'),
                'end_date' => $end_date,
                'payment_id' => $payment_id
            )
        );
        
        return $wpdb->insert_id;
    }

    /**
     * Cancel subscription
     *
     * @since    1.0.0
     * @param    int       $subscription_id    Subscription ID.
     * @return   boolean                       True if successful, false otherwise.
     */
    public function cancel_subscription($subscription_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'course_subscriptions';
        
        $result = $wpdb->update(
            $table_name,
            array(
                'status' => 'cancelled',
                'end_date' => current_time('mysql')
            ),
            array('id' => $subscription_id)
        );
        
        return $result !== false;
    }

    /**
     * Create order
     *
     * @since    1.0.0
     * @param    int       $user_id    User ID.
     * @param    int       $plan_id    Plan ID.
     * @param    string    $payment_id Payment ID.
     * @return   int                   Order ID.
     */
    public function create_order($user_id, $plan_id, $payment_id = '') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'course_orders';
        
        $plan = self::get_plan($plan_id);
        
        if (!$plan) {
            return false;
        }
        
        $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'plan_id' => $plan_id,
                'amount' => $plan->price,
                'status' => 'pending',
                'payment_id' => $payment_id,
                'created_at' => current_time('mysql')
            )
        );
        
        return $wpdb->insert_id;
    }

    /**
     * Update order status
     *
     * @since    1.0.0
     * @param    int       $order_id    Order ID.
     * @param    string    $status      Order status.
     * @return   boolean                True if successful, false otherwise.
     */
    public function update_order_status($order_id, $status) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'course_orders';
        
        $result = $wpdb->update(
            $table_name,
            array('status' => $status),
            array('id' => $order_id)
        );
        
        return $result !== false;
    }

    /**
     * Handle AJAX add to cart request
     *
     * @since    1.0.0
     */
public function ajax_add_to_cart() {
    // Check nonce for security
    check_ajax_referer('wcs-add-to-cart-nonce', 'security');
    
    // Prepare response array
    $response = array(
        'success' => false,
        'message' => '',
        'redirect' => ''
    );
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        $response['message'] = __('You must be logged in to add a plan to cart.', 'wp-course-subscription');
        wp_send_json($response);
    }
    
    // Get plan ID from request
    $plan_id = isset($_POST['plan_id']) ? intval($_POST['plan_id']) : 0;
    
    if (!$plan_id) {
        $response['message'] = __('Invalid plan ID.', 'wp-course-subscription');
        wp_send_json($response);
    }
    
    // Get plan details
    $plan = self::get_plan($plan_id);
    
    if (!$plan) {
        $response['message'] = __('Invalid subscription plan.', 'wp-course-subscription');
        wp_send_json($response);
    }
    
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
        session_start();
    }
    
    // Add plan to session
    $_SESSION['wcs_cart_plan'] = $plan;
    
    // Force session write
    session_write_close();
    
    // Get checkout page URL
    $checkout_page_id = get_option('wcs_checkout_page_id', 0);
    $checkout_url = $checkout_page_id ? get_permalink($checkout_page_id) : home_url();
    
    // Return success response
    $response['success'] = true;
    $response['redirect'] = $checkout_url;
    
    wp_send_json($response);
}

    /**
     * Handle AJAX process checkout request
     *
     * @since    1.0.0
     */
    public function ajax_process_checkout() {
        // Check nonce for security
        check_ajax_referer('wcs-process-checkout-nonce', 'security');
        
        // Prepare response array
        $response = array(
            'success' => false,
            'message' => '',
            'redirect' => ''
        );
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            $response['message'] = __('You must be logged in to process checkout.', 'wp-course-subscription');
            wp_send_json($response);
        }
        
        // Get payment intent ID from request
        $payment_intent_id = isset($_POST['payment_intent_id']) ? sanitize_text_field($_POST['payment_intent_id']) : '';
        
        if (!$payment_intent_id) {
            $response['message'] = __('Invalid payment intent ID.', 'wp-course-subscription');
            wp_send_json($response);
        }
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Get plan from session
        $plan = isset($_SESSION['wcs_cart_plan']) ? $_SESSION['wcs_cart_plan'] : null;
        
        if (!$plan) {
            $response['message'] = __('No subscription plan selected.', 'wp-course-subscription');
            wp_send_json($response);
        }
        
        // Get user ID
        $user_id = get_current_user_id();
        
        // Create order
        $order_id = $this->create_order($user_id, $plan->id, $payment_intent_id);
        
        if (!$order_id) {
            $response['message'] = __('Failed to create order.', 'wp-course-subscription');
            wp_send_json($response);
        }
        
        // Confirm payment intent
        $stripe_gateway = new WP_Course_Subscription_Stripe_Gateway($this->plugin_name, $this->version);
        $payment_confirmed = $stripe_gateway->confirm_payment_intent($payment_intent_id);
        
        if (!$payment_confirmed) {
            $response['message'] = __('Payment verification failed.', 'wp-course-subscription');
            wp_send_json($response);
        }
        
        // Update order status
        $this->update_order_status($order_id, 'completed');
        
        // Create subscription
        $subscription_id = $this->create_subscription($user_id, $plan->id, $payment_intent_id);
        
        if (!$subscription_id) {
            $response['message'] = __('Failed to create subscription.', 'wp-course-subscription');
            wp_send_json($response);
        }
        $this->update_order_subscription($order_id, $subscription_id);

        // Clear cart
        unset($_SESSION['wcs_cart_plan']);
        
        // Get thank you page URL
        $thank_you_page_id = get_option('wcs_thank_you_page_id', 0);
        $thank_you_url = $thank_you_page_id ? get_permalink($thank_you_page_id) : home_url();
        
        // Return success response
        $response['success'] = true;
        $response['redirect'] = $thank_you_url;
        
        wp_send_json($response);
    }
}
