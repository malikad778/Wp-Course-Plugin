# WordPress Course Subscription Plugin Architecture

## Plugin Overview
This plugin restricts access to course post types in WordPress, requiring users to register/login and purchase a subscription through Stripe before accessing course content.

## Plugin Structure

```
wp-course-subscription/
├── wp-course-subscription.php           # Main plugin file
├── includes/                            # Core functionality
│   ├── class-wp-course-subscription.php # Main plugin class
│   ├── class-activator.php              # Plugin activation hooks
│   ├── class-deactivator.php            # Plugin deactivation hooks
│   ├── class-course-access.php          # Course access control
│   ├── class-authentication.php         # User authentication handling
│   ├── class-subscription.php           # Subscription management
│   ├── class-stripe-gateway.php         # Stripe payment integration
│   └── class-admin.php                  # Admin functionality
├── admin/                               # Admin-specific code
│   ├── class-admin-orders.php           # Admin orders page
│   ├── class-admin-settings.php         # Plugin settings page
│   ├── js/                              # Admin JavaScript files
│   └── css/                             # Admin CSS files
├── public/                              # Public-facing code
│   ├── class-public.php                 # Public-facing functionality
│   ├── js/                              # Public JavaScript files
│   │   ├── login-popup.js               # Login/registration popup
│   │   ├── subscription.js              # Subscription handling
│   │   └── stripe-integration.js        # Stripe payment handling
│   ├── css/                             # Public CSS files
│   │   ├── login-popup.css              # Login popup styles
│   │   └── subscription.css             # Subscription page styles
│   └── templates/                       # Template files
│       ├── login-popup.php              # Login/registration popup template
│       ├── subscription-plans.php       # Subscription plans template
│       └── checkout.php                 # Checkout page template
└── assets/                              # Images and other assets
```

## Database Schema

### Custom Tables

1. **wp_course_subscriptions**
   - `id` (int, primary key, auto-increment)
   - `user_id` (int, foreign key to wp_users)
   - `plan_id` (int, foreign key to wp_course_subscription_plans)
   - `status` (varchar: active, cancelled, expired)
   - `start_date` (datetime)
   - `end_date` (datetime)
   - `stripe_subscription_id` (varchar)
   - `created_at` (datetime)
   - `updated_at` (datetime)

2. **wp_course_subscription_plans**
   - `id` (int, primary key, auto-increment)
   - `name` (varchar)
   - `description` (text)
   - `price` (decimal)
   - `duration` (int, in days)
   - `stripe_price_id` (varchar)
   - `status` (varchar: active, inactive)
   - `created_at` (datetime)
   - `updated_at` (datetime)

3. **wp_course_orders**
   - `id` (int, primary key, auto-increment)
   - `user_id` (int, foreign key to wp_users)
   - `subscription_id` (int, foreign key to wp_course_subscriptions)
   - `amount` (decimal)
   - `currency` (varchar)
   - `status` (varchar: pending, completed, failed)
   - `payment_method` (varchar)
   - `stripe_payment_intent_id` (varchar)
   - `created_at` (datetime)
   - `updated_at` (datetime)

## Core Components

### 1. Authentication System
- Intercepts requests to course post types
- Displays login/registration popup for non-authenticated users
- Handles user registration and login processes
- Manages user sessions and authentication state

### 2. Course Access Control
- Checks if user has an active subscription
- Restricts access to course content based on subscription status
- Redirects non-subscribed users to subscription plans page

### 3. Subscription Management
- Manages subscription plans (creation, editing, deletion)
- Handles user subscription status
- Processes subscription renewals and cancellations
- Integrates with Stripe for payment processing

### 4. Stripe Integration
- Connects to Stripe API for payment processing
- Handles payment intents and checkout sessions
- Processes webhooks for subscription events
- Manages customer and subscription data in Stripe

### 5. Admin Interface
- Provides orders management page
- Displays customer details, subscription plans, and payment status
- Allows subscription plan management
- Provides plugin settings configuration

## User Flow

1. **Course Access Attempt**
   - User attempts to access a course post type
   - Plugin checks if user is logged in
   - If not logged in, display login/registration popup

2. **Authentication**
   - User registers or logs in through the popup
   - Plugin authenticates user and creates/retrieves user session

3. **Subscription Check**
   - Plugin checks if authenticated user has an active subscription
   - If no active subscription, redirect to subscription plans page

4. **Subscription Purchase**
   - User selects a subscription plan
   - Plan is added to cart
   - User proceeds to checkout

5. **Payment Processing**
   - User enters payment details
   - Stripe processes payment
   - Plugin receives payment confirmation

6. **Access Granted**
   - Plugin activates user subscription
   - User gains access to all course content
   - On subsequent visits, user can directly access courses

## Stripe Integration Details

### API Integration
- Uses Stripe PHP SDK for server-side integration
- Implements Stripe Elements for secure payment form
- Utilizes Stripe Checkout for simplified payment process
- Implements webhooks for subscription event handling

### Payment Flow
1. Create Stripe Customer (if new)
2. Create Payment Intent or Checkout Session
3. Process payment through Stripe
4. Handle successful payment callback
5. Create subscription record in database
6. Grant access to courses

### Webhook Handling
- Subscription created
- Subscription updated
- Subscription cancelled
- Payment succeeded
- Payment failed

## Security Considerations
- WordPress nonces for form submissions
- Input sanitization and validation
- Secure storage of Stripe API keys
- HTTPS requirement for payment processing
- User capability checks for admin functions
- Data encryption for sensitive information

## Performance Considerations
- Efficient database queries
- Caching of subscription status
- Asynchronous processing of non-critical tasks
- Minimized JavaScript and CSS assets
