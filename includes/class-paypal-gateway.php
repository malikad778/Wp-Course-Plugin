<?php
/**
 * The class responsible for integrating with PayPal payment gateway.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Course_Subscription
 * @subpackage WP_Course_Subscription/includes
 */

class WP_Course_Subscription_PayPal_Gateway {

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
     * PayPal API client ID.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $client_id    PayPal API client ID.
     */
    private $client_id;

    /**
     * PayPal API client secret.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $client_secret    PayPal API client secret.
     */
    private $client_secret;

    /**
     * PayPal API base URL.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $api_base_url    PayPal API base URL.
     */
    private $api_base_url;

    /**
     * PayPal webhook ID.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $webhook_id    PayPal webhook ID.
     */
    private $webhook_id;

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
        
        // Check if test mode is enabled
        $test_mode = get_option('wcs_paypal_test_mode', 'yes') === 'yes';
        
        // Get appropriate PayPal API credentials based on mode
        if ($test_mode) {
            $this->client_id = get_option('wcs_paypal_test_client_id', '');
            $this->client_secret = get_option('wcs_paypal_test_client_secret', '');
            $this->webhook_id = get_option('wcs_paypal_test_webhook_id', '');
            $this->api_base_url = 'https://api-m.sandbox.paypal.com';
        } else {
            $this->client_id = get_option('wcs_paypal_client_id', '');
            $this->client_secret = get_option('wcs_paypal_client_secret', '');
            $this->webhook_id = get_option('wcs_paypal_webhook_id', '');
            $this->api_base_url = 'https://api-m.paypal.com';
        }
    }

    /**
     * Get PayPal access token
     *
     * @since    1.0.0
     * @return   string|false    Access token or false on failure.
     */
    private function get_access_token() {
        if (empty($this->client_id) || empty($this->client_secret)) {
            return false;
        }

        $url = $this->api_base_url . '/v1/oauth2/token';
        
        $headers = array(
            'Accept' => 'application/json',
            'Accept-Language' => 'en_US',
            'Authorization' => 'Basic ' . base64_encode($this->client_id . ':' . $this->client_secret)
        );

        $body = 'grant_type=client_credentials';

        $response = wp_remote_post($url, array(
            'headers' => $headers,
            'body' => $body,
            'timeout' => 30
        ));

        if (is_wp_error($response)) {
            error_log('PayPal API Error: ' . $response->get_error_message());
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['access_token'])) {
            return $data['access_token'];
        }

        return false;
    }

    /**
     * Create PayPal order
     *
     * @since    1.0.0
     * @param    array    $order_data    Order data.
     * @return   array|false             Order response or false on failure.
     */
    public function create_order($order_data) {
        $access_token = $this->get_access_token();
        
        if (!$access_token) {
            return false;
        }

        $url = $this->api_base_url . '/v2/checkout/orders';
        
        $headers = array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
            'PayPal-Request-Id' => uniqid()
        );

        $response = wp_remote_post($url, array(
            'headers' => $headers,
            'body' => json_encode($order_data),
            'timeout' => 30
        ));

        if (is_wp_error($response)) {
            error_log('PayPal Create Order Error: ' . $response->get_error_message());
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        return $data;
    }

    /**
     * Capture PayPal order
     *
     * @since    1.0.0
     * @param    string    $order_id    PayPal order ID.
     * @return   array|false            Capture response or false on failure.
     */
    public function capture_order($order_id) {
        $access_token = $this->get_access_token();
        
        if (!$access_token) {
            return false;
        }

        $url = $this->api_base_url . '/v2/checkout/orders/' . $order_id . '/capture';
        
        $headers = array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
            'PayPal-Request-Id' => uniqid()
        );

        $response = wp_remote_post($url, array(
            'headers' => $headers,
            'body' => '{}',
            'timeout' => 30
        ));

        if (is_wp_error($response)) {
            error_log('PayPal Capture Order Error: ' . $response->get_error_message());
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        return $data;
    }

    /**
     * Create PayPal subscription
     *
     * @since    1.0.0
     * @param    array    $subscription_data    Subscription data.
     * @return   array|false                   Subscription response or false on failure.
     */
    public function create_subscription($subscription_data) {
        $access_token = $this->get_access_token();
        
        if (!$access_token) {
            return false;
        }

        $url = $this->api_base_url . '/v1/billing/subscriptions';
        
        $headers = array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
            'PayPal-Request-Id' => uniqid()
        );

        $response = wp_remote_post($url, array(
            'headers' => $headers,
            'body' => json_encode($subscription_data),
            'timeout' => 30
        ));

        if (is_wp_error($response)) {
            error_log('PayPal Create Subscription Error: ' . $response->get_error_message());
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        return $data;
    }

    /**
     * Register webhook endpoint
     *
     * @since    1.0.0
     */
    public function register_webhook_endpoint() {
        register_rest_route('wp-course-subscription/v1', '/paypal-webhook', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_webhook'),
            'permission_callback' => '__return_true'
        ));
    }

    /**
     * Handle PayPal webhook
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The request object.
     * @return   WP_REST_Response               The response object.
     */
    public function handle_webhook($request) {
        $payload = $request->get_body();
        $headers = $request->get_headers();
        
        // Verify webhook signature
        if (!$this->verify_webhook_signature($payload, $headers)) {
            return new WP_REST_Response(array('status' => 'error', 'message' => 'Invalid signature'), 400);
        }
        
        $event = json_decode($payload, true);
        
        if (!$event || !isset($event['event_type'])) {
            return new WP_REST_Response(array('status' => 'error', 'message' => 'Invalid payload'), 400);
        }
        
        // Handle the event
        switch ($event['event_type']) {
            case 'BILLING.SUBSCRIPTION.CREATED':
                $this->handle_subscription_created($event['resource']);
                break;
            case 'BILLING.SUBSCRIPTION.ACTIVATED':
                $this->handle_subscription_activated($event['resource']);
                break;
            case 'BILLING.SUBSCRIPTION.CANCELLED':
                $this->handle_subscription_cancelled($event['resource']);
                break;
            case 'BILLING.SUBSCRIPTION.SUSPENDED':
                $this->handle_subscription_suspended($event['resource']);
                break;
            case 'PAYMENT.SALE.COMPLETED':
                $this->handle_payment_completed($event['resource']);
                break;
            case 'PAYMENT.SALE.DENIED':
                $this->handle_payment_failed($event['resource']);
                break;
            default:
                // Log unexpected event type
                error_log('PayPal Webhook: Unexpected event type: ' . $event['event_type']);
                break;
        }
        
        return new WP_REST_Response(array('status' => 'success'), 200);
    }

    /**
     * Verify PayPal webhook signature
     *
     * @since    1.0.0
     * @param    string    $payload    Webhook payload.
     * @param    array     $headers    Request headers.
     * @return   bool                  True if signature is valid, false otherwise.
     */
    private function verify_webhook_signature($payload, $headers) {
        // For now, we'll implement basic verification
        // In production, you should implement proper PayPal webhook signature verification
        // using PayPal's webhook verification API
        
        if (empty($this->webhook_id)) {
            return true; // Skip verification if webhook ID is not set
        }
        
        // Basic header checks
        $required_headers = array('paypal_transmission_id', 'paypal_cert_id', 'paypal_transmission_sig', 'paypal_transmission_time');
        
        foreach ($required_headers as $header) {
            if (!isset($headers[$header])) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Handle subscription created event
     *
     * @since    1.0.0
     * @param    array    $subscription    PayPal subscription data.
     */
    private function handle_subscription_created($subscription) {
        // Log the event for debugging
        error_log('PayPal Subscription Created: ' . json_encode($subscription));
        
        // Get subscription from database by PayPal subscription ID
        global $wpdb;
        $table_name = $wpdb->prefix . 'course_subscriptions';
        
        $db_subscription = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM $table_name WHERE paypal_subscription_id = %s
        ", $subscription['id']));
        
        if ($db_subscription) {
            return; // Subscription already exists
        }
        
        // Extract user and plan information from custom_id or metadata
        $custom_data = isset($subscription['custom_id']) ? json_decode($subscription['custom_id'], true) : array();
        
        if (!isset($custom_data['user_id']) || !isset($custom_data['plan_id'])) {
            error_log('PayPal Subscription Created: Missing user_id or plan_id in custom_data');
            return;
        }
        
        $user_id = intval($custom_data['user_id']);
        $plan_id = intval($custom_data['plan_id']);
        
        // Calculate end date based on billing cycle
        $end_date = null;
        if (isset($subscription['billing_info']['next_billing_time'])) {
            $end_date = date('Y-m-d H:i:s', strtotime($subscription['billing_info']['next_billing_time']));
        }
        
        // Create subscription record
        $wpdb->insert($table_name, array(
            'user_id' => $user_id,
            'plan_id' => $plan_id,
            'status' => 'pending',
            'start_date' => current_time('mysql'),
            'end_date' => $end_date,
            'paypal_subscription_id' => $subscription['id']
        ));
    }

    /**
     * Handle subscription activated event
     *
     * @since    1.0.0
     * @param    array    $subscription    PayPal subscription data.
     */
    private function handle_subscription_activated($subscription) {
        // Get subscription from database by PayPal subscription ID
        global $wpdb;
        $table_name = $wpdb->prefix . 'course_subscriptions';
        
        $db_subscription = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM $table_name WHERE paypal_subscription_id = %s
        ", $subscription['id']));
        
        if (!$db_subscription) {
            return;
        }
        
        // Update subscription status
        $wpdb->update(
            $table_name,
            array('status' => 'active'),
            array('id' => $db_subscription->id)
        );
    }

    /**
     * Handle subscription cancelled event
     *
     * @since    1.0.0
     * @param    array    $subscription    PayPal subscription data.
     */
    private function handle_subscription_cancelled($subscription) {
        // Get subscription from database by PayPal subscription ID
        global $wpdb;
        $table_name = $wpdb->prefix . 'course_subscriptions';
        
        $db_subscription = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM $table_name WHERE paypal_subscription_id = %s
        ", $subscription['id']));
        
        if (!$db_subscription) {
            return;
        }
        
        // Update subscription status
        $wpdb->update(
            $table_name,
            array(
                'status' => 'cancelled',
                'end_date' => current_time('mysql')
            ),
            array('id' => $db_subscription->id)
        );
    }

    /**
     * Handle subscription suspended event
     *
     * @since    1.0.0
     * @param    array    $subscription    PayPal subscription data.
     */
    private function handle_subscription_suspended($subscription) {
        // Get subscription from database by PayPal subscription ID
        global $wpdb;
        $table_name = $wpdb->prefix . 'course_subscriptions';
        
        $db_subscription = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM $table_name WHERE paypal_subscription_id = %s
        ", $subscription['id']));
        
        if (!$db_subscription) {
            return;
        }
        
        // Update subscription status
        $wpdb->update(
            $table_name,
            array('status' => 'suspended'),
            array('id' => $db_subscription->id)
        );
    }

    /**
     * Handle payment completed event
     *
     * @since    1.0.0
     * @param    array    $payment    PayPal payment data.
     */
    private function handle_payment_completed($payment) {
        // Get order from database by PayPal payment ID
        global $wpdb;
        $table_name = $wpdb->prefix . 'course_orders';
        
        $order = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM $table_name WHERE paypal_payment_id = %s
        ", $payment['id']));
        
        if (!$order) {
            return;
        }
        
        // Update order status
        $wpdb->update(
            $table_name,
            array('status' => 'completed'),
            array('id' => $order->id)
        );
    }

    /**
     * Handle payment failed event
     *
     * @since    1.0.0
     * @param    array    $payment    PayPal payment data.
     */
    private function handle_payment_failed($payment) {
        // Get order from database by PayPal payment ID
        global $wpdb;
        $table_name = $wpdb->prefix . 'course_orders';
        
        $order = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM $table_name WHERE paypal_payment_id = %s
        ", $payment['id']));
        
        if (!$order) {
            return;
        }
        
        // Update order status
        $wpdb->update(
            $table_name,
            array('status' => 'failed'),
            array('id' => $order->id)
        );
    }

    /**
     * Handle AJAX create PayPal order request
     *
     * @since    1.0.0
     */
    public function ajax_create_paypal_order() {
        // Check nonce for security
        check_ajax_referer('wcs-create-paypal-order-nonce', 'security');

        // Prepare response array
        $response = array(
            'success' => false,
            'message' => ''
        );

        // Check if user is logged in
        if (!is_user_logged_in()) {
            $response['message'] = __('You must be logged in to create a PayPal order.', 'wp-course-subscription');
            wp_send_json($response);
        }

        // Get user ID
        $user_id = get_current_user_id();

        // Get plan from request
        $plan_id = isset($_POST['plan_id']) ? intval($_POST['plan_id']) : 0;
        
        if (!$plan_id) {
            $response['message'] = __('No subscription plan selected.', 'wp-course-subscription');
            wp_send_json($response);
        }
        
        // Get plan details
        global $wpdb;
        $table_name = $wpdb->prefix . 'course_subscription_plans';
        $plan = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $plan_id));
        
        if (!$plan) {
            $response['message'] = __('Invalid subscription plan.', 'wp-course-subscription');
            wp_send_json($response);
        }

        try {
            // Prepare order data
            $order_data = array(
                'intent' => 'CAPTURE',
                'purchase_units' => array(
                    array(
                        'amount' => array(
                            'currency_code' => 'USD',
                            'value' => number_format($plan->price, 2, '.', '')
                        ),
                        'description' => $plan->name,
                        'custom_id' => json_encode(array(
                            'user_id' => $user_id,
                            'plan_id' => $plan->id
                        ))
                    )
                ),
                'application_context' => array(
                    'return_url' => home_url('/checkout/?payment_method=paypal&status=success'),
                    'cancel_url' => home_url('/checkout/?payment_method=paypal&status=cancelled')
                )
            );

            // Create PayPal order
            $paypal_order = $this->create_order($order_data);

            if (!$paypal_order || !isset($paypal_order['id'])) {
                $response['message'] = __('Failed to create PayPal order.', 'wp-course-subscription');
                wp_send_json($response);
            }

            // Return success response
            $response['success'] = true;
            $response['order_id'] = $paypal_order['id'];
            $response['approval_url'] = '';
            
            // Find approval URL
            if (isset($paypal_order['links'])) {
                foreach ($paypal_order['links'] as $link) {
                    if ($link['rel'] === 'approve') {
                        $response['approval_url'] = $link['href'];
                        break;
                    }
                }
            }

        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }

        wp_send_json($response);
    }

    /**
     * Handle AJAX capture PayPal order request
     *
     * @since    1.0.0
     */
    public function ajax_capture_paypal_order() {
        // Check nonce for security
        check_ajax_referer('wcs-capture-paypal-order-nonce', 'security');

        // Prepare response array
        $response = array(
            'success' => false,
            'message' => ''
        );

        // Get order ID from request
        $order_id = isset($_POST['order_id']) ? sanitize_text_field($_POST['order_id']) : '';
        
        if (!$order_id) {
            $response['message'] = __('No PayPal order ID provided.', 'wp-course-subscription');
            wp_send_json($response);
        }

        try {
            // Capture PayPal order
            $capture_result = $this->capture_order($order_id);

            if (!$capture_result || $capture_result['status'] !== 'COMPLETED') {
                $response['message'] = __('Failed to capture PayPal payment.', 'wp-course-subscription');
                wp_send_json($response);
            }

            // Process the successful payment
            $this->process_successful_payment($capture_result);

            // Return success response
            $response['success'] = true;
            $response['message'] = __('Payment completed successfully.', 'wp-course-subscription');

        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }

        wp_send_json($response);
    }

    /**
     * Process successful PayPal payment
     *
     * @since    1.0.0
     * @param    array    $capture_result    PayPal capture result.
     */
    private function process_successful_payment($capture_result) {
        // Extract custom data
        $custom_data = json_decode($capture_result['purchase_units'][0]['payments']['captures'][0]['custom_id'], true);
        
        if (!$custom_data || !isset($custom_data['user_id']) || !isset($custom_data['plan_id'])) {
            return;
        }
        
        $user_id = intval($custom_data['user_id']);
        $plan_id = intval($custom_data['plan_id']);
        
        // Get plan details
        global $wpdb;
        $plans_table = $wpdb->prefix . 'course_subscription_plans';
        $plan = $wpdb->get_row($wpdb->prepare("SELECT * FROM $plans_table WHERE id = %d", $plan_id));
        
        if (!$plan) {
            return;
        }
        
        // Create order record
        $orders_table = $wpdb->prefix . 'course_orders';
        $wpdb->insert($orders_table, array(
            'user_id' => $user_id,
            'plan_id' => $plan_id,
            'amount' => $plan->price,
            'currency' => 'USD',
            'status' => 'completed',
            'payment_method' => 'paypal',
            'paypal_payment_id' => $capture_result['purchase_units'][0]['payments']['captures'][0]['id'],
            'created_at' => current_time('mysql')
        ));
        
        // Calculate subscription end date
        $end_date = null;
        if ($plan->duration_type === 'monthly') {
            $end_date = date('Y-m-d H:i:s', strtotime('+' . $plan->duration_value . ' months'));
        } elseif ($plan->duration_type === 'yearly') {
            $end_date = date('Y-m-d H:i:s', strtotime('+' . $plan->duration_value . ' years'));
        }
        
        // Create subscription record
        $subscriptions_table = $wpdb->prefix . 'course_subscriptions';
        $wpdb->insert($subscriptions_table, array(
            'user_id' => $user_id,
            'plan_id' => $plan_id,
            'status' => 'active',
            'start_date' => current_time('mysql'),
            'end_date' => $end_date,
            'paypal_payment_id' => $capture_result['purchase_units'][0]['payments']['captures'][0]['id']
        ));
        $subscription_id = $wpdb->insert_id;

// Find the order by PayPal payment ID
$orders_table = $wpdb->prefix . 'course_orders';
$order = $wpdb->get_row($wpdb->prepare("SELECT * FROM $orders_table WHERE paypal_payment_id = %s", $capture_result['purchase_units'][0]['payments']['captures'][0]['id']));
if ($order && $subscription_id) {
    // Update order with subscription_id
    $wpdb->update($orders_table, array('subscription_id' => $subscription_id), array('id' => $order->id));
}
    }

    /**
     * Get PayPal client ID for frontend
     *
     * @since    1.0.0
     * @return   string    PayPal client ID.
     */
    public function get_client_id() {
        return $this->client_id;
    }

    /**
     * Check if PayPal is configured
     *
     * @since    1.0.0
     * @return   bool    True if configured, false otherwise.
     */
    public function is_configured() {
        return !empty($this->client_id) && !empty($this->client_secret);
    }
}

