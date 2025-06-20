<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Course_Subscription
 * @subpackage WP_Course_Subscription/includes
 */

class WP_Course_Subscription {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      WP_Course_Subscription_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {
        if (defined('WP_COURSE_SUBSCRIPTION_VERSION')) {
            $this->version = WP_COURSE_SUBSCRIPTION_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'wp-course-subscription';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - WP_Course_Subscription_Loader. Orchestrates the hooks of the plugin.
     * - WP_Course_Subscription_i18n. Defines internationalization functionality.
     * - WP_Course_Subscription_Admin. Defines all hooks for the admin area.
     * - WP_Course_Subscription_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-public.php';

        /**
         * The class responsible for handling course access control.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-course-access.php';

        /**
         * The class responsible for handling subscriptions.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-subscription.php';

        /**
         * The class responsible for integrating with Stripe payment gateway.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-stripe-gateway.php';

        /**
         * The class responsible for integrating with PayPal payment gateway.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-paypal-gateway.php';

        /**
         * The class responsible for handling orders list table in admin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-orders-list-table.php';

        $this->loader = new WP_Course_Subscription_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the WP_Course_Subscription_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {
        $plugin_i18n = new WP_Course_Subscription_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $plugin_admin = new WP_Course_Subscription_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        
        // Add admin menu
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_admin_menu');
        
        // Register settings

        // Save settings
        $this->loader->add_action('admin_post_wcs_save_settings', $plugin_admin, 'save_settings');
        
        // AJAX handlers for admin
        $this->loader->add_action('wp_ajax_wcs_get_plan', $plugin_admin, 'ajax_get_plan');
        $this->loader->add_action('wp_ajax_wcs_save_plan', $plugin_admin, 'ajax_save_plan');
        $this->loader->add_action('wp_ajax_wcs_delete_plan', $plugin_admin, 'ajax_delete_plan');
        $this->loader->add_action('wp_ajax_wcs_get_order', $plugin_admin, 'ajax_get_order');
        $this->loader->add_action('wp_ajax_wcs_update_order', $plugin_admin, 'ajax_update_order');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
/**
 * Register all of the hooks related to the public-facing functionality
 * of the plugin.
 *
 * @since    1.0.0
 * @access   private
 */
private function define_public_hooks() {
    $plugin_public = new WP_Course_Subscription_Public($this->get_plugin_name(), $this->get_version());
    $plugin_course_access = new WP_Course_Subscription_Course_Access($this->get_plugin_name(), $this->get_version());
    $plugin_subscription = new WP_Course_Subscription_Subscription($this->get_plugin_name(), $this->get_version());
    $plugin_stripe_gateway = new WP_Course_Subscription_Stripe_Gateway($this->get_plugin_name(), $this->get_version());
    $plugin_paypal_gateway = new WP_Course_Subscription_PayPal_Gateway($this->get_plugin_name(), $this->get_version());

    // Enqueue styles and scripts
    $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
    $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
    
    // Add custom CSS
    $this->loader->add_action('wp_head', $plugin_public, 'add_custom_styles');
    
    // Course access control - CORRECTED HOOKS
    $this->loader->add_action('template_redirect', $plugin_course_access, 'restrict_course_access');
    $this->loader->add_action('template_redirect', $plugin_course_access, 'setup_content_filters');
    $this->loader->add_action('pre_get_posts', $plugin_course_access, 'filter_course_query');
    
    // Video and media protection
    $this->loader->add_filter('wp_get_attachment_url', $plugin_course_access, 'protect_course_video_files', 10, 2);
    
    // Embedded content and shortcode restrictions
    $this->loader->add_filter('embed_oembed_html', $plugin_course_access, 'restrict_embedded_content', 10, 4);
    $this->loader->add_filter('do_shortcode_tag', $plugin_course_access, 'filter_shortcode_output', 10, 4);
    
    // Search and query result filtering
    $this->loader->add_filter('the_posts', $plugin_course_access, 'restrict_courses_in_results', 10, 2);

    // REST API endpoints
    $this->loader->add_action('rest_api_init', $plugin_course_access, 'register_rest_routes');
    $this->loader->add_action('rest_api_init', $plugin_stripe_gateway, 'register_webhook_endpoint');
    $this->loader->add_action('rest_api_init', $plugin_paypal_gateway, 'register_webhook_endpoint');
    
    // AJAX handlers for public
    $this->loader->add_action('wp_ajax_wcs_add_to_cart', $plugin_subscription, 'ajax_add_to_cart');
    $this->loader->add_action('wp_ajax_nopriv_wcs_add_to_cart', $plugin_subscription, 'ajax_add_to_cart');
    
    // Stripe AJAX handlers
    $this->loader->add_action('wp_ajax_wcs_create_payment_intent', $plugin_stripe_gateway, 'ajax_create_payment_intent');
    $this->loader->add_action('wp_ajax_nopriv_wcs_create_payment_intent', $plugin_stripe_gateway, 'ajax_create_payment_intent');
    
    // PayPal AJAX handlers
    $this->loader->add_action('wp_ajax_wcs_create_paypal_order', $plugin_paypal_gateway, 'ajax_create_paypal_order');
    $this->loader->add_action('wp_ajax_nopriv_wcs_create_paypal_order', $plugin_paypal_gateway, 'ajax_create_paypal_order');
    $this->loader->add_action('wp_ajax_wcs_capture_paypal_order', $plugin_paypal_gateway, 'ajax_capture_paypal_order');
    $this->loader->add_action('wp_ajax_nopriv_wcs_capture_paypal_order', $plugin_paypal_gateway, 'ajax_capture_paypal_order');
    
    // General checkout processing
    $this->loader->add_action('wp_ajax_wcs_process_checkout', $plugin_subscription, 'ajax_process_checkout');
    $this->loader->add_action('wp_ajax_nopriv_wcs_process_checkout', $plugin_subscription, 'ajax_process_checkout');
}

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    WP_Course_Subscription_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
}
