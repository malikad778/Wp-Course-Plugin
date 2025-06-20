# WordPress Course Subscription Plugin - Documentation

## Overview

The WordPress Course Subscription Plugin provides a complete solution for restricting course access to subscribers, managing subscription plans, and processing payments through Stripe.

## Features

- Course access restriction for non-logged-in users
- Login/registration popup for accessing restricted content
- Subscription plans management
- Stripe payment integration with test mode support
- Admin dashboard for managing orders and subscriptions
- Shortcodes for easy integration with your pages

## Installation

1. Upload the `wp-course-subscription` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin settings as described below

## Configuration

### 1. Plugin Settings

Navigate to **Course Subscriptions → Settings** in your WordPress admin to configure:

- **Stripe API Keys**: Enter your Stripe API credentials (both live and test)
- **Test Mode**: Toggle between test and live modes for Stripe
- **Page Selection**: Choose pages for subscription plans, checkout, and thank you

### 2. Create Pages with Shortcodes

Create the following pages and add the corresponding shortcodes:

1. **Subscription Plans Page**: Add the shortcode `[subscription_plans]`
2. **Checkout Page**: Add the shortcode `[subscription_checkout]`
3. **Thank You Page**: Add the shortcode `[subscription_thankyou]`

### 3. Create Subscription Plans

Navigate to **Course Subscriptions → Plans** to create subscription plans:

- Set plan name, description, price, and duration
- Plans will automatically appear on your subscription plans page

### 4. Display Courses

To display courses with access control, use the shortcode `[courses_display]` on any page.

## Shortcodes Reference

| Shortcode | Description |
|-----------|-------------|
| `[subscription_plans]` | Displays available subscription plans with pricing |
| `[subscription_checkout]` | Displays the checkout form with Stripe integration |
| `[subscription_thankyou]` | Displays the thank you message after successful payment |
| `[courses_display]` | Displays courses with access control |

## Course Access Control

The plugin automatically restricts access to:

- Individual course pages
- Course archive pages
- Course content displayed via shortcodes

Non-logged-in users will see a login popup, while logged-in users without an active subscription will be redirected to the subscription plans page.

## Stripe Integration

### Test Mode

1. Enable test mode in the plugin settings
2. Enter your Stripe test API keys
3. Use test card numbers for testing:
   - Success: 4242 4242 4242 4242
   - Decline: 4000 0000 0000 0002

### Webhook Setup

For subscription management, set up a webhook in your Stripe dashboard pointing to:
`https://your-site.com/wp-json/wp-course-subscription/v1/webhook`

## Troubleshooting

### Common Issues

1. **Pages not saving**: Make sure you click the "Save Settings" button after selecting pages
2. **Courses still visible**: Check that your courses are using the 'course' post type
3. **Payment not processing**: Verify your Stripe API keys are correct

### Debug Mode

To enable debug mode, add the following to your wp-config.php:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Support

For additional support or customization requests, please contact the plugin developer.
