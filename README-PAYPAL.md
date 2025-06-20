# WP Course Subscription Plugin - PayPal Integration

## Overview
This WordPress plugin now supports both Stripe and PayPal payment processing for course subscriptions. Users can choose between credit card payments (via Stripe) and PayPal payments during checkout.

## New Features Added

### PayPal Payment Gateway
- Complete PayPal REST API integration
- Support for both sandbox (test) and live environments
- PayPal webhook handling for payment events
- Seamless integration with existing subscription system

### Enhanced Admin Settings
- PayPal API configuration section
- Separate test/live credential management
- PayPal webhook endpoint configuration
- Real-time payment method status indicators

### Improved Checkout Experience
- Payment method selection (Stripe vs PayPal)
- Dynamic payment form switching
- PayPal button integration
- Enhanced error handling and user feedback

## Installation & Configuration

### 1. PayPal API Setup
1. Create a PayPal Developer account at https://developer.paypal.com
2. Create a new application to get your Client ID and Client Secret
3. Configure webhook endpoints for payment notifications

### 2. Plugin Configuration
1. Navigate to **Course Subscriptions > Settings** in WordPress admin
2. Scroll to the **PayPal API Settings** section
3. Configure your PayPal credentials:

#### For Testing (Sandbox):
- Check "Enable PayPal Test Mode"
- Enter your Sandbox Client ID
- Enter your Sandbox Client Secret
- Enter your Sandbox Webhook ID (optional)

#### For Production (Live):
- Uncheck "Enable PayPal Test Mode"
- Enter your Live Client ID
- Enter your Live Client Secret
- Enter your Live Webhook ID (optional)

### 3. Webhook Configuration
Set up webhooks in your PayPal Developer Dashboard:
- **Webhook URL**: `https://yoursite.com/wp-json/wp-course-subscription/v1/paypal-webhook`
- **Events to Subscribe**:
  - `BILLING.SUBSCRIPTION.CREATED`
  - `BILLING.SUBSCRIPTION.ACTIVATED`
  - `BILLING.SUBSCRIPTION.CANCELLED`
  - `BILLING.SUBSCRIPTION.SUSPENDED`
  - `PAYMENT.SALE.COMPLETED`
  - `PAYMENT.SALE.DENIED`

## Database Schema Updates

The plugin automatically handles PayPal payment tracking using existing database tables:

### Orders Table
- `paypal_payment_id` - Stores PayPal payment/capture ID
- `payment_method` - Identifies payment processor ('stripe' or 'paypal')

### Subscriptions Table
- `paypal_subscription_id` - Stores PayPal subscription ID (for recurring payments)
- `paypal_payment_id` - Links to initial PayPal payment

## Payment Flow

### PayPal One-Time Payments
1. User selects PayPal payment method
2. PayPal order is created via REST API
3. User is redirected to PayPal for approval
4. Payment is captured upon approval
5. Subscription is activated in WordPress

### PayPal Recurring Subscriptions
1. PayPal subscription plan is created
2. User approves subscription on PayPal
3. Webhook notifications handle subscription events
4. WordPress subscription status is synchronized

## API Endpoints

### PayPal Webhook
- **URL**: `/wp-json/wp-course-subscription/v1/paypal-webhook`
- **Method**: POST
- **Purpose**: Receives PayPal event notifications

### AJAX Endpoints
- `wcs_create_paypal_order` - Creates PayPal order
- `wcs_capture_paypal_order` - Captures approved PayPal payment

## Security Features

- CSRF protection via WordPress nonces
- PayPal webhook signature verification
- Secure API credential storage
- Input sanitization and validation

## Error Handling

The integration includes comprehensive error handling:
- PayPal API connection errors
- Payment authorization failures
- Webhook processing errors
- User-friendly error messages

## Testing

### Sandbox Testing
1. Enable PayPal test mode in settings
2. Use PayPal sandbox credentials
3. Test with PayPal sandbox accounts
4. Verify webhook notifications

### Test Scenarios
- Successful payment completion
- Payment cancellation
- Payment failure
- Webhook event processing
- Subscription management

## Troubleshooting

### Common Issues

#### PayPal Buttons Not Appearing
- Verify PayPal Client ID is configured
- Check browser console for JavaScript errors
- Ensure PayPal SDK is loading correctly

#### Webhook Events Not Processing
- Verify webhook URL is accessible
- Check PayPal webhook configuration
- Review server error logs

#### Payment Failures
- Verify API credentials are correct
- Check PayPal account status
- Review PayPal transaction logs

### Debug Mode
Enable WordPress debug mode to see detailed error messages:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## File Structure

### New Files Added
- `includes/class-paypal-gateway.php` - PayPal payment gateway implementation

### Modified Files
- `includes/class-wp-course-subscription.php` - Added PayPal gateway integration
- `admin/class-admin.php` - Added PayPal settings handling
- `admin/partials/admin-settings.php` - Added PayPal configuration UI
- `public/class-public.php` - Added PayPal AJAX nonces
- `public/templates/checkout.php` - Added PayPal payment options

## Support

For technical support or questions about the PayPal integration:
1. Check the troubleshooting section above
2. Review WordPress and PayPal error logs
3. Verify API credentials and webhook configuration
4. Test in sandbox environment first

## Version History

### v1.1.0 - PayPal Integration
- Added PayPal REST API integration
- Enhanced checkout with payment method selection
- Added PayPal webhook handling
- Updated admin settings interface
- Improved error handling and user experience

### v1.0.0 - Initial Release
- Stripe payment integration
- Course access control
- Subscription management
- Admin dashboard

