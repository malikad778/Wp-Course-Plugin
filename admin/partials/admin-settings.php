<?php
/**
 * The admin settings template
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Course_Subscription
 * @subpackage WP_Course_Subscription/admin/partials
 */
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php
    // Display settings saved message if applicable
    if (isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true') {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Settings saved successfully.', 'wp-course-subscription'); ?></p>
        </div>
        <?php
    }
    ?>
    
    <form method="post" action="">
        <?php wp_nonce_field('wcs_settings_nonce'); ?>
        <input type="hidden" name="wcs_save_settings" value="1">
        
        <h2 class="title"><?php _e('Stripe API Settings', 'wp-course-subscription'); ?></h2>
        <p><?php _e('Enter your Stripe API credentials to enable payment processing.', 'wp-course-subscription'); ?></p>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="stripe_test_mode"><?php _e('Test Mode', 'wp-course-subscription'); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" name="stripe_test_mode" id="stripe_test_mode" value="yes" <?php checked($stripe_test_mode, 'yes'); ?>>
                        <?php _e('Enable Test Mode', 'wp-course-subscription'); ?>
                    </label>
                    <p class="description"><?php _e('Check this box to use Stripe test credentials instead of live credentials.', 'wp-course-subscription'); ?></p>
                </td>
            </tr>
        </table>
        
        <div id="stripe-live-credentials" <?php echo $stripe_test_mode === 'yes' ? 'style="display:none;"' : ''; ?>>
            <h3><?php _e('Live Credentials', 'wp-course-subscription'); ?></h3>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="stripe_secret_key"><?php _e('Secret Key', 'wp-course-subscription'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="stripe_secret_key" id="stripe_secret_key" class="regular-text" value="<?php echo esc_attr($stripe_secret_key); ?>">
                        <p class="description"><?php _e('Enter your Stripe Secret Key. This is required for payment processing.', 'wp-course-subscription'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="stripe_publishable_key"><?php _e('Publishable Key', 'wp-course-subscription'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="stripe_publishable_key" id="stripe_publishable_key" class="regular-text" value="<?php echo esc_attr($stripe_publishable_key); ?>">
                        <p class="description"><?php _e('Enter your Stripe Publishable Key. This is required for payment form display.', 'wp-course-subscription'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="stripe_webhook_secret"><?php _e('Webhook Secret', 'wp-course-subscription'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="stripe_webhook_secret" id="stripe_webhook_secret" class="regular-text" value="<?php echo esc_attr($stripe_webhook_secret); ?>">
                        <p class="description">
                            <?php _e('Enter your Stripe Webhook Secret. This is required for handling subscription events.', 'wp-course-subscription'); ?><br>
                            <?php _e('Your webhook endpoint URL is:', 'wp-course-subscription'); ?> <code><?php echo esc_url(rest_url('wp-course-subscription/v1/webhook')); ?></code>
                        </p>
                    </td>
                </tr>
            </table>
        </div>
        
        <div id="stripe-test-credentials" <?php echo $stripe_test_mode !== 'yes' ? 'style="display:none;"' : ''; ?>>
            <h3><?php _e('Test Credentials', 'wp-course-subscription'); ?></h3>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="stripe_test_secret_key"><?php _e('Test Secret Key', 'wp-course-subscription'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="stripe_test_secret_key" id="stripe_test_secret_key" class="regular-text" value="<?php echo esc_attr($stripe_test_secret_key); ?>">
                        <p class="description"><?php _e('Enter your Stripe Test Secret Key. This is required for test payment processing.', 'wp-course-subscription'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="stripe_test_publishable_key"><?php _e('Test Publishable Key', 'wp-course-subscription'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="stripe_test_publishable_key" id="stripe_test_publishable_key" class="regular-text" value="<?php echo esc_attr($stripe_test_publishable_key); ?>">
                        <p class="description"><?php _e('Enter your Stripe Test Publishable Key. This is required for test payment form display.', 'wp-course-subscription'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="stripe_test_webhook_secret"><?php _e('Test Webhook Secret', 'wp-course-subscription'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="stripe_test_webhook_secret" id="stripe_test_webhook_secret" class="regular-text" value="<?php echo esc_attr($stripe_test_webhook_secret); ?>">
                        <p class="description">
                            <?php _e('Enter your Stripe Test Webhook Secret. This is required for handling test subscription events.', 'wp-course-subscription'); ?><br>
                            <?php _e('Your webhook endpoint URL is:', 'wp-course-subscription'); ?> <code><?php echo esc_url(rest_url('wp-course-subscription/v1/webhook')); ?></code>
                        </p>
                    </td>
                </tr>
            </table>
        </div>
        
        <h2 class="title"><?php _e('PayPal API Settings', 'wp-course-subscription'); ?></h2>
        <p><?php _e('Enter your PayPal API credentials to enable PayPal payment processing.', 'wp-course-subscription'); ?></p>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="paypal_test_mode"><?php _e('PayPal Test Mode', 'wp-course-subscription'); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" name="paypal_test_mode" id="paypal_test_mode" value="yes" <?php checked($paypal_test_mode, 'yes'); ?>>
                        <?php _e('Enable PayPal Test Mode', 'wp-course-subscription'); ?>
                    </label>
                    <p class="description"><?php _e('Check this box to use PayPal sandbox credentials instead of live credentials.', 'wp-course-subscription'); ?></p>
                </td>
            </tr>
        </table>
        
        <div id="paypal-live-credentials" <?php echo $paypal_test_mode === 'yes' ? 'style="display:none;"' : ''; ?>>
            <h3><?php _e('PayPal Live Credentials', 'wp-course-subscription'); ?></h3>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="paypal_client_id"><?php _e('Client ID', 'wp-course-subscription'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="paypal_client_id" id="paypal_client_id" class="regular-text" value="<?php echo esc_attr($paypal_client_id); ?>">
                        <p class="description"><?php _e('Enter your PayPal Live Client ID. This is required for PayPal payment processing.', 'wp-course-subscription'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="paypal_client_secret"><?php _e('Client Secret', 'wp-course-subscription'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="paypal_client_secret" id="paypal_client_secret" class="regular-text" value="<?php echo esc_attr($paypal_client_secret); ?>">
                        <p class="description"><?php _e('Enter your PayPal Live Client Secret. This is required for PayPal payment processing.', 'wp-course-subscription'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="paypal_webhook_id"><?php _e('Webhook ID', 'wp-course-subscription'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="paypal_webhook_id" id="paypal_webhook_id" class="regular-text" value="<?php echo esc_attr($paypal_webhook_id); ?>">
                        <p class="description">
                            <?php _e('Enter your PayPal Webhook ID. This is required for handling PayPal events.', 'wp-course-subscription'); ?><br>
                            <?php _e('Your PayPal webhook endpoint URL is:', 'wp-course-subscription'); ?> <code><?php echo esc_url(rest_url('wp-course-subscription/v1/paypal-webhook')); ?></code>
                        </p>
                    </td>
                </tr>
            </table>
        </div>
        
        <div id="paypal-test-credentials" <?php echo $paypal_test_mode !== 'yes' ? 'style="display:none;"' : ''; ?>>
            <h3><?php _e('PayPal Test Credentials', 'wp-course-subscription'); ?></h3>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="paypal_test_client_id"><?php _e('Test Client ID', 'wp-course-subscription'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="paypal_test_client_id" id="paypal_test_client_id" class="regular-text" value="<?php echo esc_attr($paypal_test_client_id); ?>">
                        <p class="description"><?php _e('Enter your PayPal Sandbox Client ID. This is required for test PayPal payment processing.', 'wp-course-subscription'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="paypal_test_client_secret"><?php _e('Test Client Secret', 'wp-course-subscription'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="paypal_test_client_secret" id="paypal_test_client_secret" class="regular-text" value="<?php echo esc_attr($paypal_test_client_secret); ?>">
                        <p class="description"><?php _e('Enter your PayPal Sandbox Client Secret. This is required for test PayPal payment processing.', 'wp-course-subscription'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="paypal_test_webhook_id"><?php _e('Test Webhook ID', 'wp-course-subscription'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="paypal_test_webhook_id" id="paypal_test_webhook_id" class="regular-text" value="<?php echo esc_attr($paypal_test_webhook_id); ?>">
                        <p class="description">
                            <?php _e('Enter your PayPal Test Webhook ID. This is required for handling test PayPal events.', 'wp-course-subscription'); ?><br>
                            <?php _e('Your PayPal webhook endpoint URL is:', 'wp-course-subscription'); ?> <code><?php echo esc_url(rest_url('wp-course-subscription/v1/paypal-webhook')); ?></code>
                        </p>
                    </td>
                </tr>
            </table>
        </div>
        
        <h2 class="title"><?php _e('General Settings', 'wp-course-subscription'); ?></h2>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="subscription_page"><?php _e('Subscription Plans Page', 'wp-course-subscription'); ?></label>
                </th>
                <td>
                    <?php
                    wp_dropdown_pages(array(
                        'name' => 'subscription_page',
                        'id' => 'subscription_page',
                        'selected' => $subscription_page_id,
                        'show_option_none' => __('Select a page', 'wp-course-subscription'),
                        'option_none_value' => '0'
                    ));
                    ?>
                    <p class="description"><?php _e('Select the page where subscription plans will be displayed.', 'wp-course-subscription'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="checkout_page"><?php _e('Checkout Page', 'wp-course-subscription'); ?></label>
                </th>
                <td>
                    <?php
                    wp_dropdown_pages(array(
                        'name' => 'checkout_page',
                        'id' => 'checkout_page',
                        'selected' => $checkout_page_id,
                        'show_option_none' => __('Select a page', 'wp-course-subscription'),
                        'option_none_value' => '0'
                    ));
                    ?>
                    <p class="description"><?php _e('Select the page where checkout will be processed.', 'wp-course-subscription'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="thank_you_page"><?php _e('Thank You Page', 'wp-course-subscription'); ?></label>
                </th>
                <td>
                    <?php
                    wp_dropdown_pages(array(
                        'name' => 'thank_you_page',
                        'id' => 'thank_you_page',
                        'selected' => $thank_you_page_id,
                        'show_option_none' => __('Select a page', 'wp-course-subscription'),
                        'option_none_value' => '0'
                    ));
                    ?>
                    <p class="description"><?php _e('Select the page where users will be redirected after successful payment.', 'wp-course-subscription'); ?></p>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Settings', 'wp-course-subscription'); ?>">
        </p>
    </form>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        // Toggle between test and live credentials for Stripe
        $('#stripe_test_mode').on('change', function() {
            if ($(this).is(':checked')) {
                $('#stripe-live-credentials').hide();
                $('#stripe-test-credentials').show();
            } else {
                $('#stripe-live-credentials').show();
                $('#stripe-test-credentials').hide();
            }
        });
        
        // Toggle between test and live credentials for PayPal
        $('#paypal_test_mode').on('change', function() {
            if ($(this).is(':checked')) {
                $('#paypal-live-credentials').hide();
                $('#paypal-test-credentials').show();
            } else {
                $('#paypal-live-credentials').show();
                $('#paypal-test-credentials').hide();
            }
        });
    });
</script>
