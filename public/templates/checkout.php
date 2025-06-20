<?php
 /*
 Template Name: WP Course Checkout
*/
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

// Check if user is logged in
if (!is_user_logged_in()) {
    wp_redirect(home_url('/subscription-plans/'));
    exit;
}

// Check if plan is in session
$plan = isset($_SESSION['wcs_cart_plan']) ? $_SESSION['wcs_cart_plan'] : null;
if (!$plan) {
    $subscription_page_id = get_option('wcs_subscription_page_id', 0);
    $subscription_url = $subscription_page_id ? get_permalink($subscription_page_id) : home_url();
    wp_redirect($subscription_url);
    exit;
}

// Get payment gateway configurations
$stripe_gateway = new WP_Course_Subscription_Stripe_Gateway('wp-course-subscription', '1.0.0');
$paypal_gateway = new WP_Course_Subscription_PayPal_Gateway('wp-course-subscription', '1.0.0');

$stripe_configured = !empty(get_option('wcs_stripe_test_mode', 'yes') === 'yes' ? get_option('wcs_stripe_test_publishable_key', '') : get_option('wcs_stripe_publishable_key', ''));
$paypal_configured = $paypal_gateway->is_configured();


get_header();

echo do_shortcode('[elementor-template id="4345"]');
?>

<div class="wcs-checkout">
    <div class="wcs-container">
        <h1 class="wcs-page-title"><?php _e('Checkout', 'wp-course-subscription'); ?></h1>
        
        <div class="wcs-checkout-content">
            <div class="wcs-checkout-summary">
                <h2><?php _e('Order Summary', 'wp-course-subscription'); ?></h2>
                
                <div class="wcs-checkout-plan">
                    <div class="wcs-plan-name"><?php echo esc_html($plan->name); ?></div>
                    <div class="wcs-plan-price">$<?php echo number_format($plan->price, 2); ?></div>
                </div>
                
                <div class="wcs-checkout-details">
                    <div class="wcs-checkout-detail">
                        <span class="wcs-detail-label"><?php _e('Duration:', 'wp-course-subscription'); ?></span>
                        <span class="wcs-detail-value">
                            <?php 
                            if ($plan->duration > 0) {
                                echo sprintf(_n('%d day', '%d days', $plan->duration, 'wp-course-subscription'), $plan->duration);
                            } else {
                                _e('Unlimited', 'wp-course-subscription');
                            }
                            ?>
                        </span>
                    </div>
                    
                    <div class="wcs-checkout-detail">
                        <span class="wcs-detail-label"><?php _e('Total:', 'wp-course-subscription'); ?></span>
                        <span class="wcs-detail-value wcs-total-price">$<?php echo number_format($plan->price, 2); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="wcs-checkout-payment">
                <h2><?php _e('Payment Method', 'wp-course-subscription'); ?></h2>
                
                <?php if ($stripe_configured || $paypal_configured): ?>
                    <div class="wcs-payment-methods">
                        <?php if ($stripe_configured): ?>
                            <div class="wcs-payment-method">
                                <input type="radio" id="payment-stripe" name="payment_method" value="stripe" checked>
                                <label for="payment-stripe">
                                    <span class="wcs-payment-icon">💳</span>
                                    <?php _e('Credit/Debit Card (Stripe)', 'wp-course-subscription'); ?>
                                </label>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($paypal_configured): ?>
                            <div class="wcs-payment-method">
                                <input type="radio" id="payment-paypal" name="payment_method" value="paypal" <?php echo !$stripe_configured ? 'checked' : ''; ?>>
                                <label for="payment-paypal">
                                    <span class="wcs-payment-icon">🅿️</span>
                                    <?php _e('PayPal', 'wp-course-subscription'); ?>
                                </label>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($stripe_configured): ?>
                    <div id="wcs-stripe-payment" class="wcs-payment-form" style="<?php echo !$stripe_configured || !$paypal_configured ? '' : 'display: block;'; ?>">
                        <h3><?php _e('Credit Card Information', 'wp-course-subscription'); ?></h3>
                        <div class="wcs-form-row">
                            <label for="card-element"><?php _e('Credit or debit card', 'wp-course-subscription'); ?></label>
                            <div id="card-element"></div>
                            <div id="card-errors" role="alert"></div>
                        </div>
                        
                        <button id="wcs-submit-stripe-payment" class="wcs-button wcs-button-primary"><?php _e('Pay with Card', 'wp-course-subscription'); ?></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($paypal_configured): ?>
                    <div id="wcs-paypal-payment" class="wcs-payment-form" style="<?php echo $stripe_configured ? 'display: none;' : 'display: block;'; ?>">
                        <h3><?php _e('PayPal Payment', 'wp-course-subscription'); ?></h3>
                        <p><?php _e('You will be redirected to PayPal to complete your payment.', 'wp-course-subscription'); ?></p>
                        
                        <div id="paypal-button-container"></div>
                        <div id="paypal-errors" role="alert"></div>
                    </div>
                <?php endif; ?>
                
                <?php if (!$stripe_configured && !$paypal_configured): ?>
                    <div class="wcs-no-payment-methods">
                        <p><?php _e('No payment methods are currently configured. Please contact the administrator.', 'wp-course-subscription'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.wcs-page-title{
    display:  none;
}
.wcs-payment-methods {
    margin-bottom: 20px;
}

.wcs-payment-method {
    margin-bottom: 10px;
    padding: 15px;
    border: 2px solid #ddd;
    border-radius: 5px;
    cursor: pointer;
    transition: border-color 0.3s;
}

.wcs-payment-method:hover {
    border-color: #007cba;
}

.wcs-payment-method input[type="radio"] {
    margin-right: 10px;
}

.wcs-payment-method input[type="radio"]:checked + label {
    font-weight: bold;
}

.wcs-payment-method label {
    cursor: pointer;
    display: flex;
    align-items: center;
    margin: 0;
}

.wcs-payment-icon {
    font-size: 20px;
    margin-right: 10px;
}

.wcs-payment-form {
    margin-top: 20px;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background-color: #f9f9f9;
}

.wcs-payment-form h3 {
    margin-top: 0;
}

#paypal-button-container {
    margin-top: 15px;
}

.wcs-no-payment-methods {
    text-align: center;
    padding: 40px;
    background-color: #f9f9f9;
    border-radius: 5px;
}
</style>

<?php if ($stripe_configured): ?>
<script src="https://js.stripe.com/v3/"></script>
<?php endif; ?>

<?php if ($paypal_configured): ?>
<script src="https://www.paypal.com/sdk/js?client-id=<?php echo esc_attr($paypal_gateway->get_client_id()); ?>&currency=USD"></script>
<?php endif; ?>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        // Payment method switching
        $('input[name="payment_method"]').on('change', function() {
            var selectedMethod = $(this).val();
            
            $('.wcs-payment-form').hide();
            
            if (selectedMethod === 'stripe') {
                $('#wcs-stripe-payment').show();
            } else if (selectedMethod === 'paypal') {
                $('#wcs-paypal-payment').show();
            }
        });
        
        <?php if ($stripe_configured): ?>
        // Initialize Stripe
        var stripe = Stripe('<?php echo esc_js(get_option('wcs_stripe_test_mode', 'yes') === 'yes' ? get_option('wcs_stripe_test_publishable_key', '') : get_option('wcs_stripe_publishable_key', '')); ?>');
        var elements = stripe.elements();
        
        // Create card Element
        var card = elements.create('card');
        card.mount('#card-element');
        
        // Handle real-time validation errors
        card.addEventListener('change', function(event) {
            var displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });
        
        // Handle Stripe form submission
        $('#wcs-submit-stripe-payment').on('click', function(event) {
            event.preventDefault();
            
            var submitButton = $(this);
            submitButton.prop('disabled', true);
            submitButton.text('<?php _e('Processing...', 'wp-course-subscription'); ?>');
            
            // Create payment intent
            $.ajax({
                url: wcs_subscription.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcs_create_payment_intent',
                    plan_id: <?php echo esc_js($plan->id); ?>,
                    security: wcs_subscription.create_payment_intent_nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Confirm card payment
                        stripe.confirmCardPayment(response.client_secret, {
                            payment_method: {
                                card: card,
                                billing_details: {
                                    name: '<?php echo esc_js(wp_get_current_user()->display_name); ?>'
                                }
                            }
                        }).then(function(result) {
                            if (result.error) {
                                // Show error to customer
                                $('#card-errors').text(result.error.message);
                                submitButton.prop('disabled', false);
                                submitButton.text('<?php _e('Pay with Card', 'wp-course-subscription'); ?>');
                            } else {
                                // Payment succeeded, process order
                                processStripeOrder(response.payment_intent_id);
                            }
                        });
                    } else {
                        // Show error to customer
                        $('#card-errors').text(response.message);
                        submitButton.prop('disabled', false);
                        submitButton.text('<?php _e('Pay with Card', 'wp-course-subscription'); ?>');
                    }
                },
                error: function() {
                    $('#card-errors').text('<?php _e('An error occurred. Please try again.', 'wp-course-subscription'); ?>');
                    submitButton.prop('disabled', false);
                    submitButton.text('<?php _e('Pay with Card', 'wp-course-subscription'); ?>');
                }
            });
        });
        
        // Process Stripe order after payment
        function processStripeOrder(paymentIntentId) {
            $.ajax({
                url: wcs_subscription.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcs_process_checkout',
                    payment_intent_id: paymentIntentId,
                    payment_method: 'stripe',
                    security: wcs_subscription.process_checkout_nonce
                },
                success: function(response) {
                    if (response.success) {
                        window.location.href = response.redirect;
                    } else {
                        $('#card-errors').text(response.message);
                        $('#wcs-submit-stripe-payment').prop('disabled', false);
                        $('#wcs-submit-stripe-payment').text('<?php _e('Pay with Card', 'wp-course-subscription'); ?>');
                    }
                },
                error: function() {
                    $('#card-errors').text('<?php _e('An error occurred. Please try again.', 'wp-course-subscription'); ?>');
                    $('#wcs-submit-stripe-payment').prop('disabled', false);
                    $('#wcs-submit-stripe-payment').text('<?php _e('Pay with Card', 'wp-course-subscription'); ?>');
                }
            });
        }
        <?php endif; ?>
        
        <?php if ($paypal_configured): ?>
        // Initialize PayPal
        paypal.Buttons({
            createOrder: function(data, actions) {
                return new Promise(function(resolve, reject) {
                    $.ajax({
                        url: wcs_subscription.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'wcs_create_paypal_order',
                            plan_id: <?php echo esc_js($plan->id); ?>,
                            security: wcs_subscription.create_paypal_order_nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                resolve(response.order_id);
                            } else {
                                $('#paypal-errors').text(response.message);
                                reject(new Error(response.message));
                            }
                        },
                        error: function() {
                            var errorMsg = '<?php _e('An error occurred. Please try again.', 'wp-course-subscription'); ?>';
                            $('#paypal-errors').text(errorMsg);
                            reject(new Error(errorMsg));
                        }
                    });
                });
            },
            onApprove: function(data, actions) {
                return new Promise(function(resolve, reject) {
                    $.ajax({
                        url: wcs_subscription.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'wcs_capture_paypal_order',
                            order_id: data.orderID,
                            security: wcs_subscription.capture_paypal_order_nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                // Redirect to thank you page
                                var thankYouPageId = <?php echo intval(get_option('wcs_thank_you_page_id', 0)); ?>;
                                if (thankYouPageId > 0) {
                                    window.location.href = '<?php echo esc_url(get_permalink(get_option('wcs_thank_you_page_id', 0))); ?>';
                                } else {
                                    window.location.href = '<?php echo esc_url(home_url()); ?>';
                                }
                            } else {
                                $('#paypal-errors').text(response.message);
                            }
                        },
                        error: function() {
                            $('#paypal-errors').text('<?php _e('An error occurred. Please try again.', 'wp-course-subscription'); ?>');
                        }
                    });
                });
            },
            onError: function(err) {
                $('#paypal-errors').text('<?php _e('PayPal error occurred. Please try again.', 'wp-course-subscription'); ?>');
                console.error('PayPal error:', err);
            }
        }).render('#paypal-button-container');
        <?php endif; ?>
    });
</script>

<?php
get_footer();
?>