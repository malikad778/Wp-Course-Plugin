# WordPress Course Subscription Plugin - Updated Installation Guide

## Overview

The WordPress Course Subscription Plugin is a custom solution that restricts access to course content, requiring users to register/login and purchase a subscription through Stripe before accessing courses. This plugin provides:

- Login/registration popup for non-logged-in users
- Subscription plans management
- Stripe payment integration
- Course access control based on subscription status
- Admin dashboard for managing orders and subscriptions

## Installation

1. Upload the `wp-course-subscription` folder to the `/wp-content/plugins/` directory of your WordPress installation
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin settings as described below

## Important Changes in This Version

This version includes the following fixes:
- Bundled Stripe PHP SDK to eliminate dependency errors
- Improved error handling for Stripe integration
- Added PHP session management for cart functionality
- Enhanced plugin activation reliability

## Configuration

### Stripe API Setup

1. Go to WordPress Admin → Course Subscriptions → Settings
2. Enter your Stripe API credentials:
   - Secret Key
   - Publishable Key
   - Webhook Secret

To obtain Stripe API keys:
1. Create a Stripe account at [stripe.com](https://stripe.com)
2. Navigate to Developers → API keys in your Stripe Dashboard
3. Copy the Publishable key and Secret key
4. For the Webhook Secret, go to Developers → Webhooks
5. Add a new endpoint with the URL shown in the plugin settings
6. After creating the webhook, reveal and copy the signing secret

### Page Setup

Create the following pages in WordPress:

1. **Subscription Plans Page**: A page to display available subscription plans
2. **Checkout Page**: A page for processing payments
3. **Thank You Page**: A page to redirect users after successful payment

After creating these pages, select them in the plugin settings under Course Subscriptions → Settings.

## Creating Subscription Plans

1. Go to WordPress Admin → Course Subscriptions → Subscription Plans
2. Click "Add New" to create a new subscription plan
3. Fill in the following details:
   - Name: The name of the subscription plan
   - Description: A detailed description of what the plan offers
   - Price: The cost of the subscription in USD
   - Duration: The length of the subscription in days (use 0 for unlimited)
   - Status: Active or Inactive

## Managing Orders

1. Go to WordPress Admin → Course Subscriptions → Orders
2. View all subscription orders with customer details, subscription information, and payment status
3. Click on an order to view detailed information
4. Perform actions such as marking orders as completed or processing refunds

## User Experience

### For Visitors:
1. When a non-logged-in user attempts to access a course, they will see a login/registration popup
2. After registering or logging in, if they don't have an active subscription, they will be redirected to the subscription plans page
3. They can select a plan, proceed to checkout, and complete payment through Stripe
4. Once payment is successful, they gain access to all courses

### For Subscribers:
1. Logged-in users with active subscriptions can access all course content directly
2. Their subscription status is automatically checked when accessing course content

## Troubleshooting

### Plugin Activation Issues
- If you encounter any activation errors, please check your PHP error logs
- Ensure your server meets the minimum requirements: PHP 7.2+ and WordPress 5.0+
- The plugin now includes the Stripe PHP SDK, so no additional installation is required

### Payment Issues
- Verify your Stripe API keys are correct
- Ensure the webhook is properly configured
- Check that your Stripe account is activated and can accept payments

### Access Control Issues
- Verify that your courses are using the correct post type ('course')
- Check that users have active subscriptions in the database
- Ensure the course access control hooks are not being overridden by other plugins

## Support

For additional support or customization requests, please contact the plugin developer.

## License

This plugin is licensed for your exclusive use and may not be redistributed without permission.
