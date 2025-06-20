<?php
/**
 * The class responsible for handling user authentication.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Course_Subscription
 * @subpackage WP_Course_Subscription/includes
 */

class WP_Course_Subscription_Authentication {

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
     * Handle AJAX login request
     *
     * @since    1.0.0
     */
    public function ajax_login() {
        // Check nonce for security
        check_ajax_referer('wcs-login-nonce', 'security');

        // Prepare response array
        $response = array(
            'success' => false,
            'message' => ''
        );

        // Get login credentials
        $credentials = array(
            'user_login'    => sanitize_text_field($_POST['username']),
            'user_password' => $_POST['password'],
            'remember'      => isset($_POST['remember']) ? true : false
        );

        // Attempt to sign the user in
        $user = wp_signon($credentials, is_ssl());

        // Check if login was successful
        if (is_wp_error($user)) {
            $response['message'] = $user->get_error_message();
        } else {
            $response['success'] = true;
            $response['message'] = __('Login successful, redirecting...', 'wp-course-subscription');
            $response['redirect'] = $_POST['redirect'] ? esc_url($_POST['redirect']) : home_url();
        }

        // Return response as JSON
        wp_send_json($response);
    }

    /**
     * Handle AJAX registration request
     *
     * @since    1.0.0
     */
    public function ajax_register() {
        // Check nonce for security
        check_ajax_referer('wcs-register-nonce', 'security');

        // Prepare response array
        $response = array(
            'success' => false,
            'message' => ''
        );

        // Get registration data
        $username = sanitize_user($_POST['username']);
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Validate username
        if (empty($username)) {
            $response['message'] = __('Username is required.', 'wp-course-subscription');
            wp_send_json($response);
        }

        // Validate email
        if (empty($email) || !is_email($email)) {
            $response['message'] = __('Valid email is required.', 'wp-course-subscription');
            wp_send_json($response);
        }

        // Validate password
        if (empty($password)) {
            $response['message'] = __('Password is required.', 'wp-course-subscription');
            wp_send_json($response);
        }

        // Check if passwords match
        if ($password !== $confirm_password) {
            $response['message'] = __('Passwords do not match.', 'wp-course-subscription');
            wp_send_json($response);
        }

        // Check if username already exists
        if (username_exists($username)) {
            $response['message'] = __('Username already exists.', 'wp-course-subscription');
            wp_send_json($response);
        }

        // Check if email already exists
        if (email_exists($email)) {
            $response['message'] = __('Email already exists.', 'wp-course-subscription');
            wp_send_json($response);
        }

        // Create new user
        $user_id = wp_create_user($username, $password, $email);

        // Check if user was created successfully
        if (is_wp_error($user_id)) {
            $response['message'] = $user_id->get_error_message();
            wp_send_json($response);
        }

        // Set user role
        $user = new WP_User($user_id);
        $user->set_role('subscriber');

        // Log the user in
        $credentials = array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => true
        );

        $user_login = wp_signon($credentials, is_ssl());

        if (is_wp_error($user_login)) {
            $response['message'] = $user_login->get_error_message();
            wp_send_json($response);
        }

        // Return success response
        $response['success'] = true;
        $response['message'] = __('Registration successful, redirecting...', 'wp-course-subscription');
        $response['redirect'] = $_POST['redirect'] ? esc_url($_POST['redirect']) : home_url();

        wp_send_json($response);
    }

    /**
     * Handle AJAX logout request
     *
     * @since    1.0.0
     */
    public function ajax_logout() {
        // Check nonce for security
        check_ajax_referer('wcs-logout-nonce', 'security');

        // Prepare response array
        $response = array(
            'success' => false,
            'message' => ''
        );

        // Log the user out
        wp_logout();

        // Return success response
        $response['success'] = true;
        $response['message'] = __('Logout successful, redirecting...', 'wp-course-subscription');
        $response['redirect'] = home_url();

        wp_send_json($response);
    }

    /**
     * Check if user is logged in
     *
     * @since    1.0.0
     * @return   boolean    True if user is logged in, false otherwise.
     */
    public static function is_user_logged_in() {
        return is_user_logged_in();
    }

    /**
     * Check if user has active subscription
     *
     * @since    1.0.0
     * @param    int       $user_id    User ID to check.
     * @return   boolean               True if user has active subscription, false otherwise.
     */
    public static function has_active_subscription($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        if (!$user_id) {
            return false;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'course_subscriptions';
        
        $subscription = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE user_id = %d AND status = 'active' AND (end_date IS NULL OR end_date > NOW())",
                $user_id
            )
        );

        return !empty($subscription);
    }
}
