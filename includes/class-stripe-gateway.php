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

class WP_Course_Subscription_Stripe_Gateway {

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
     * Stripe API secret key.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $secret_key    Stripe API secret key.
     */
    private $secret_key;

    /**
     * Stripe API publishable key.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $publishable_key    Stripe API publishable key.
     */
    private $publishable_key;

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
        $test_mode = get_option('wcs_stripe_test_mode', 'yes') === 'yes';
        
        // Get appropriate Stripe API keys based on mode
        if ($test_mode) {
            $this->secret_key = get_option('wcs_stripe_test_secret_key', '');
            $this->publishable_key = get_option('wcs_stripe_test_publishable_key', '');
        } else {
            $this->secret_key = get_option('wcs_stripe_secret_key', '');
            $this->publishable_key = get_option('wcs_stripe_publishable_key', '');
        }
        
        // Load Stripe PHP SDK if not already loaded
        if (!class_exists('\Stripe\Stripe')) {
            // Check if vendor/autoload.php exists
            $autoload_path = plugin_dir_path(dirname(__FILE__)) . 'vendor/autoload.php';
            if (file_exists($autoload_path)) {
                require_once $autoload_path;
            } else {
                // Add admin notice if Stripe SDK is missing
                add_action('admin_notices', array($this, 'stripe_sdk_missing_notice'));
                return;
            }
        }
        
        // Set Stripe API key if available
        if (!empty($this->secret_key) && class_exists('\Stripe\Stripe')) {
            \Stripe\Stripe::setApiKey($this->secret_key);
        }
    }

    /**
     * Display admin notice if Stripe SDK is missing
     *
     * @since    1.0.0
     */
    public function stripe_sdk_missing_notice() {
        ?>
        <div class="notice notice-error">
            <p><?php _e('WP Course Subscription Plugin: Stripe PHP SDK is missing. Please contact the plugin developer for support.', 'wp-course-subscription'); ?></p>
        </div>
        <?php
    }

    /**
     * Register webhook endpoint
     *
     * @since    1.0.0
     */
    public function register_webhook_endpoint() {
        if (!class_exists('\Stripe\Stripe')) {
            return;
        }
        
        register_rest_route('wp-course-subscription/v1', '/webhook', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_webhook'),
            'permission_callback' => '__return_true'
        ));
    }

    /**
     * Handle Stripe webhook
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The request object.
     * @return   WP_REST_Response               The response object.
     */
    public function handle_webhook($request) {
        if (!class_exists('\Stripe\Stripe')) {
            return new WP_REST_Response(array('status' => 'error', 'message' => 'Stripe SDK not available'), 500);
        }
        
        $payload = $request->get_body();
        $sig_header = $request->get_header('stripe-signature');
        
        // Check if test mode is enabled
        $test_mode = get_option('wcs_stripe_test_mode', 'yes') === 'yes';
        
        // Get appropriate webhook secret based on mode
        if ($test_mode) {
            $webhook_secret = get_option('wcs_stripe_test_webhook_secret', '');
        } else {
            $webhook_secret = get_option('wcs_stripe_webhook_secret', '');
        }
        
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $webhook_secret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            return new WP_REST_Response(array('status' => 'error', 'message' => 'Invalid payload'), 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return new WP_REST_Response(array('status' => 'error', 'message' => 'Invalid signature'), 400);
        }
        
        // Handle the event
        switch ($event->type) {
            case 'customer.subscription.created':
                $this->handle_subscription_created($event->data->object);
                break;
            case 'customer.subscription.updated':
                $this->handle_subscription_updated($event->data->object);
                break;
            case 'customer.subscription.deleted':
                $this->handle_subscription_deleted($event->data->object);
                break;
            case 'invoice.payment_succeeded':
                $this->handle_payment_succeeded($event->data->object);
                break;
            case 'invoice.payment_failed':
                $this->handle_payment_failed($event->data->object);
                break;
            default:
                // Unexpected event type
                return new WP_REST_Response(array('status' => 'error', 'message' => 'Unexpected event type'), 400);
        }
        
        return new WP_REST_Response(array('status' => 'success'), 200);
    }

    /**
     * Handle subscription created event
     *
     * @since    1.0.0
     * @param    object    $subscription    Stripe subscription object.
     */
    private function handle_subscription_created($subscription) {
        if (!class_exists('\Stripe\Stripe')) {
            return;
        }
        
        // Get subscription from database by Stripe subscription ID
        global $wpdb;
        $table_name = $wpdb->prefix . 'course_subscriptions';
        
        $db_subscription = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM $table_name WHERE stripe_subscription_id = %s
        ", $subscription->id));
        
        if (!$db_subscription) {
            // Subscription not found in database, create it
            try {
                $customer = \Stripe\Customer::retrieve($subscription->customer);
                $user = get_user_by('email', $customer->email);
                
                if (!$user) {
                    return;
                }
                
                $user_id = $user->ID;
                
                // Get plan ID from metadata
                $plan_id = isset($subscription->metadata->plan_id) ? $subscription->metadata->plan_id : 0;
                
                if (!$plan_id) {
                    return;
                }
                
                // Calculate end date
                $end_date = null;
                if ($subscription->current_period_end) {
                    $end_date = date('Y-m-d H:i:s', $subscription->current_period_end);
                }
                
                // Create subscription record
                $wpdb->insert($table_name, array(
                    'user_id' => $user_id,
                    'plan_id' => $plan_id,
                    'status' => 'active',
                    'start_date' => date('Y-m-d H:i:s', $subscription->current_period_start),
                    'end_date' => $end_date,
                    'stripe_subscription_id' => $subscription->id
                ));
            } catch (\Exception $e) {
                // Log error
                error_log('WP Course Subscription: Error handling subscription created: ' . $e->getMessage());
            }
        }
    }

    /**
     * Handle subscription updated event
     *
     * @since    1.0.0
     * @param    object    $subscription    Stripe subscription object.
     */
    private function handle_subscription_updated($subscription) {
        // Get subscription from database by Stripe subscription ID
        global $wpdb;
        $table_name = $wpdb->prefix . 'course_subscriptions';
        
        $db_subscription = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM $table_name WHERE stripe_subscription_id = %s
        ", $subscription->id));
        
        if (!$db_subscription) {
            return;
        }
        
        // Update subscription status
        $status = 'active';
        if ($subscription->status === 'canceled' || $subscription->status === 'unpaid') {
            $status = 'cancelled';
        } else if ($subscription->status === 'past_due') {
            $status = 'past_due';
        }
        
        // Calculate end date
        $end_date = null;
        if ($subscription->current_period_end) {
            $end_date = date('Y-m-d H:i:s', $subscription->current_period_end);
        }
        
        // Update subscription record
        $wpdb->update(
            $table_name,
            array(
                'status' => $status,
                'end_date' => $end_date
            ),
            array('id' => $db_subscription->id)
        );
    }

    /**
     * Handle subscription deleted event
     *
     * @since    1.0.0
     * @param    object    $subscription    Stripe subscription object.
     */
    private function handle_subscription_deleted($subscription) {
        // Get subscription from database by Stripe subscription ID
        global $wpdb;
        $table_name = $wpdb->prefix . 'course_subscriptions';
        
        $db_subscription = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM $table_name WHERE stripe_subscription_id = %s
        ", $subscription->id));
        
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
     * Handle payment succeeded event
     *
     * @since    1.0.0
     * @param    object    $invoice    Stripe invoice object.
     */
    private function handle_payment_succeeded($invoice) {
        // Get order from database by Stripe payment intent ID
        global $wpdb;
        $table_name = $wpdb->prefix . 'course_orders';
        
        $order = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM $table_name WHERE stripe_payment_intent_id = %s
        ", $invoice->payment_intent));
        
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
     * @param    object    $invoice    Stripe invoice object.
     */
    private function handle_payment_failed($invoice) {
        // Get order from database by Stripe payment intent ID
        global $wpdb;
        $table_name = $wpdb->prefix . 'course_orders';
        
        $order = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM $table_name WHERE stripe_payment_intent_id = %s
        ", $invoice->payment_intent));
        
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
     * Handle AJAX create payment intent request
     *
     * @since    1.0.0
     */
    public function ajax_create_payment_intent() {
        // Check if Stripe SDK is available
        if (!class_exists('\Stripe\Stripe')) {
            wp_send_json(array(
                'success' => false,
                'message' => __('Stripe SDK not available. Please contact the administrator.', 'wp-course-subscription')
            ));
            return;
        }
        
        // Check nonce for security
        check_ajax_referer('wcs-create-payment-intent-nonce', 'security');

        // Prepare response array
        $response = array(
            'success' => false,
            'message' => ''
        );

        // Check if user is logged in
        if (!is_user_logged_in()) {
            $response['message'] = __('You must be logged in to create a payment intent.', 'wp-course-subscription');
            wp_send_json($response);
        }

        // Get user ID
        $user_id = get_current_user_id();

        // Get plan from session
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
            // Get or create customer
            $customer = $this->get_or_create_customer($user_id);

            if (!$customer) {
                $response['message'] = __('Failed to create customer.', 'wp-course-subscription');
                wp_send_json($response);
            }

            // Create payment intent
            $payment_intent = \Stripe\PaymentIntent::create([
                'amount' => $plan->price * 100, // Amount in cents
                'currency' => 'usd',
                'customer' => $customer->id,
                'metadata' => [
                    'user_id' => $user_id,
                    'plan_id' => $plan->id,
                    'plan_name' => $plan->name
                ]
            ]);

            // Return success response
            $response['success'] = true;
            $response['client_secret'] = $payment_intent->client_secret;
            $response['payment_intent_id'] = $payment_intent->id;
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }

        wp_send_json($response);
    }

    /**
     * Get or create Stripe customer
     *
     * @since    1.0.0
     * @param    int       $user_id    User ID.
     * @return   object                Stripe customer object.
     */
    public function get_or_create_customer($user_id) {
        if (!class_exists('\Stripe\Stripe')) {
            return null;
        }
        
        // Get user data
        $user = get_userdata($user_id);

        if (!$user) {
            return null;
        }

        // Check if test mode is enabled
        $test_mode = get_option('wcs_stripe_test_mode', 'yes') === 'yes';
        
        // Get customer ID from user meta with appropriate key based on mode
        $customer_meta_key = $test_mode ? 'wcs_stripe_test_customer_id' : 'wcs_stripe_customer_id';
        $customer_id = get_user_meta($user_id, $customer_meta_key, true);

        try {
            if ($customer_id) {
                // Retrieve existing customer
                $customer = \Stripe\Customer::retrieve($customer_id);
                
                // Update customer if email has changed
                if ($customer->email !== $user->user_email) {
                    $customer = \Stripe\Customer::update($customer_id, [
                        'email' => $user->user_email
                    ]);
                }
            } else {
                // Create new customer
                $customer = \Stripe\Customer::create([
                    'email' => $user->user_email,
                    'name' => $user->display_name,
                    'metadata' => [
                        'user_id' => $user_id,
                        'test_mode' => $test_mode ? 'yes' : 'no'
                    ]
                ]);

                // Save customer ID to user meta
                update_user_meta($user_id, $customer_meta_key, $customer->id);
            }

            return $customer;
        } catch (\Exception $e) {
    // Enhanced error logging
    error_log('WP Course Subscription: Error creating/retrieving customer: ' . $e->getMessage());
    error_log('WP Course Subscription: API Key Status: ' . (!empty($this->secret_key) ? 'Present' : 'Empty'));
    error_log('WP Course Subscription: Test Mode: ' . (get_option('wcs_stripe_test_mode', 'yes') === 'yes' ? 'Yes' : 'No'));
    return null;
}
    }

    /**
     * Confirm payment intent
     *
     * @since    1.0.0
     * @param    string    $payment_intent_id    Payment intent ID.
     * @return   boolean                         True if successful, false otherwise.
     */
    public function confirm_payment_intent($payment_intent_id) {
        if (!class_exists('\Stripe\Stripe')) {
            return false;
        }
        
        try {
            $payment_intent = \Stripe\PaymentIntent::retrieve($payment_intent_id);
            
            if ($payment_intent->status === 'succeeded') {
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            error_log('WP Course Subscription: Error confirming payment intent: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create subscription in Stripe
     *
     * @since    1.0.0
     * @param    int       $user_id    User ID.
     * @param    object    $plan       Subscription plan object.
     * @return   object                Stripe subscription object.
     */
    public function create_subscription($user_id, $plan) {
        if (!class_exists('\Stripe\Stripe')) {
            return null;
        }
        
        try {
            // Get or create customer
            $customer = $this->get_or_create_customer($user_id);

            if (!$customer) {
                return null;
            }

            // Get or create price
            $price = $this->get_or_create_price($plan);

            if (!$price) {
                return null;
            }

            // Create subscription
            $subscription = \Stripe\Subscription::create([
                'customer' => $customer->id,
                'items' => [
                    ['price' => $price->id]
                ],
                'metadata' => [
                    'user_id' => $user_id,
                    'plan_id' => $plan->id,
                    'plan_name' => $plan->name,
                    'test_mode' => get_option('wcs_stripe_test_mode', 'yes') === 'yes' ? 'yes' : 'no'
                ]
            ]);

            return $subscription;
        } catch (\Exception $e) {
            error_log('WP Course Subscription: Error creating subscription: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get or create Stripe price
     *
     * @since    1.0.0
     * @param    object    $plan    Subscription plan object.
     * @return   object             Stripe price object.
     */
    private function get_or_create_price($plan) {
        if (!class_exists('\Stripe\Stripe')) {
            return null;
        }
        
        // Check if test mode is enabled
        $test_mode = get_option('wcs_stripe_test_mode', 'yes') === 'yes';
        
        // Use appropriate price ID field based on mode
        $price_id_field = $test_mode ? 'stripe_test_price_id' : 'stripe_price_id';
        
        try {
            // Check if plan has Stripe price ID
            if (!empty($plan->$price_id_field)) {
                // Retrieve existing price
                $price = \Stripe\Price::retrieve($plan->$price_id_field);
                
                // Check if price amount matches plan price
                if ($price->unit_amount === intval($plan->price * 100)) {
                    return $price;
                }
            }

            // Create new price
            $price = \Stripe\Price::create([
                'unit_amount' => intval($plan->price * 100),
                'currency' => 'usd',
                'recurring' => [
                    'interval' => 'month',
                    'interval_count' => 1
                ],
                'product_data' => [
                    'name' => $plan->name,
                    'metadata' => [
                        'plan_id' => $plan->id,
                        'test_mode' => $test_mode ? 'yes' : 'no'
                    ]
                ],
                'metadata' => [
                    'plan_id' => $plan->id,
                    'test_mode' => $test_mode ? 'yes' : 'no'
                ]
            ]);

            // Update plan with Stripe price ID
            global $wpdb;
            $table_name = $wpdb->prefix . 'course_subscription_plans';
            
            $wpdb->update(
                $table_name,
                array($price_id_field => $price->id),
                array('id' => $plan->id)
            );

            return $price;
        } catch (\Exception $e) {
            error_log('WP Course Subscription: Error creating price: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Cancel subscription in Stripe
     *
     * @since    1.0.0
     * @param    string    $subscription_id    Stripe subscription ID.
     * @return   boolean                       True if successful, false otherwise.
     */
    public function cancel_subscription($subscription_id) {
        if (!class_exists('\Stripe\Stripe')) {
            return false;
        }
        
        try {
            $subscription = \Stripe\Subscription::retrieve($subscription_id);
            $subscription->cancel();
            
            return true;
        } catch (\Exception $e) {
            error_log('WP Course Subscription: Error cancelling subscription: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get Stripe publishable key
     *
     * @since    1.0.0
     * @return   string    Stripe publishable key.
     */
    public static function get_publishable_key() {
        // Check if test mode is enabled
        $test_mode = get_option('wcs_stripe_test_mode', 'yes') === 'yes';
        
        // Return appropriate key based on mode
        if ($test_mode) {
            return get_option('wcs_stripe_test_publishable_key', '');
        } else {
            return get_option('wcs_stripe_publishable_key', '');
        }
    }
}
