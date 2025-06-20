<?php
/**
 * Plugin Name: WP Course Subscription
 * Description: A WordPress plugin that restricts access to courses, requiring users to register/login and purchase a subscription through Stripe or PayPal.
 * Version: 1.1.0
 * Author: WebWhizy
 * Text Domain: wp-course-subscription
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('WP_COURSE_SUBSCRIPTION_VERSION', '1.1.0');
define('WP_COURSE_SUBSCRIPTION_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_COURSE_SUBSCRIPTION_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP_COURSE_SUBSCRIPTION_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * The code that runs during plugin activation.
 */
function activate_wp_course_subscription() {
    require_once WP_COURSE_SUBSCRIPTION_PLUGIN_DIR . 'includes/class-activator.php';
    WP_Course_Subscription_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_wp_course_subscription() {
    require_once WP_COURSE_SUBSCRIPTION_PLUGIN_DIR . 'includes/class-deactivator.php';
    WP_Course_Subscription_Deactivator::deactivate();
}
// Add this function to the main plugin file
function wcs_start_session() {
    if (!is_admin() && session_status() === PHP_SESSION_NONE && !headers_sent()) {
        session_start();
    }
}

// Add this to the 'init' hook with priority 1 to ensure it runs early
add_action('init', 'wcs_start_session', 1);
// Add this to your main plugin file or a utility file
function wcs_debug_session() {
    if (current_user_can('administrator') && isset($_GET['wcs_debug'])) {
        echo '<pre>';
        echo 'Session Status: ' . session_status() . "\n";
        echo 'Session ID: ' . session_id() . "\n";
        echo 'Session Data: ';
        print_r($_SESSION);
        echo '</pre>';
        exit;
    }
}
add_action('wp', 'wcs_debug_session');

register_activation_hook(__FILE__, 'activate_wp_course_subscription');
register_deactivation_hook(__FILE__, 'deactivate_wp_course_subscription');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require WP_COURSE_SUBSCRIPTION_PLUGIN_DIR . 'includes/class-wp-course-subscription.php';

/**
 * Begins execution of the plugin.
 */
function run_wp_course_subscription() {
    $plugin = new WP_Course_Subscription();
    $plugin->run();
}
run_wp_course_subscription();
