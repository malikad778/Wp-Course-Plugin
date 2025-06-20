<?php
/**
 * The class responsible for course access control.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Course_Subscription
 * @subpackage WP_Course_Subscription/includes
 */

class WP_Course_Subscription_Course_Access {

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
     * Setup content filters only for course content
     * This method should be hooked to template_redirect
     *
     * @since    1.0.0
     */
    public function setup_content_filters() {
        global $post;
        
        // Only apply filters if we're viewing a course post
        if (is_singular('course') && $post && get_post_type($post->ID) === 'course') {
            // Check if user has access
            if (!$this->has_access($post->ID)) {
                add_filter('the_content', array($this, 'replace_course_content'), 99);
                add_filter('the_excerpt', array($this, 'replace_course_content'), 99);
            }
        }
    }
    
    /**
     * Replace course content with restriction message
     *
     * @since    1.0.0
     * @param    string    $content    Original content.
     * @return   string                The restriction message.
     */
    public function replace_course_content($content) {
        // Only replace content if we're in the main query and viewing a course
        if (is_main_query() && is_singular('course')) {
            return $this->get_restriction_message($content);
        }
        return $content;
    }

    /**
     * Check if user has access to course content.
     *
     * @since    1.0.0
     * @param    int       $post_id    Post ID.
     * @return   boolean               True if user has access, false otherwise.
     */
    public function has_access($post_id = null) {
        // If no post ID is provided, use current post
        if (!$post_id) {
            global $post;
            if (!$post) {
                return true; // If no post context, allow access
            }
            $post_id = $post->ID;
        }

        // Check if post is a course - if not, always allow access
        if (get_post_type($post_id) !== 'course') {
            return true;
        }

        // Check if user is logged in
        if (!is_user_logged_in()) {
            return false;
        }

        // Get current user ID
        $user_id = get_current_user_id();
        
        // Direct database check for active subscription
        global $wpdb;
        $table_name = $wpdb->prefix . 'course_subscriptions';
        
        // Check if table exists first
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
            // Table doesn't exist, fallback to other method
            if (class_exists('WP_Course_Subscription_Subscription')) {
                $subscription = WP_Course_Subscription_Subscription::get_active_subscription();
                return $subscription !== null;
            }
            return false;
        }
        
        $subscription = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM $table_name 
            WHERE user_id = %d 
            AND status = 'active' 
            AND (end_date IS NULL OR end_date > %s)
            ORDER BY id DESC
            LIMIT 1
        ", $user_id, current_time('mysql')));
        
        // If we found an active subscription directly from the database
        if ($subscription) {
            return true;
        }
        
        // Fallback to the original method if class exists
        if (class_exists('WP_Course_Subscription_Subscription')) {
            $subscription = WP_Course_Subscription_Subscription::get_active_subscription();
            return $subscription !== null;
        }
        
        return false;
    }

    /**
     * Restrict access to course content.
     * This should only run on course pages, not all pages.
     *
     * @since    1.0.0
     */
    public function restrict_course_access() {
        // Only run on frontend
        if (is_admin()) {
            return;
        }
        
        // Only process if we're viewing course content
        if (!$this->is_course_page()) {
            return;
        }
        
        global $post;
        
        // Double-check we have a course post
        if (!$post || get_post_type($post->ID) !== 'course') {
            return;
        }

        // Process the restriction
        $this->process_course_access_restriction();
    }
    
    /**
     * Check if current page is a course-related page
     *
     * @since    1.0.0
     * @return   bool    True if course page, false otherwise.
     */
    private function is_course_page() {
        return is_singular('course') || is_post_type_archive('course') || is_tax('course_category');
    }
    
    /**
     * Process course access restriction logic.
     * This is a helper method for restrict_course_access().
     *
     * @since    1.0.0
     */
    private function process_course_access_restriction() {
        // If user is not logged in, redirect to login page
        if (!is_user_logged_in()) {
            // Get subscription page ID for redirect after login
            $subscription_page_id = get_option('wcs_subscription_page_id', 0);
            $redirect_url = $subscription_page_id ? get_permalink($subscription_page_id) : home_url();
            
            // Redirect to login page with redirect parameter
            wp_redirect(wp_login_url($redirect_url));
            exit;
        } 
        // If user is logged in but doesn't have an active subscription
        elseif (!$this->has_access()) {
            // Redirect to subscription page
            $subscription_page_id = get_option('wcs_subscription_page_id', 0);
            
            if ($subscription_page_id) {
                wp_redirect(get_permalink($subscription_page_id));
                exit;
            }
            // No subscription page set, content will be replaced by filters
        }
    }

    /**
     * Check course access (alias for backward compatibility)
     *
     * @since    1.0.0
     */
    public function check_course_access() {
        $this->restrict_course_access();
    }

    /**
     * Get appropriate restriction message based on user status.
     * 
     * @since    1.0.0
     * @param    string    $content    Original content.
     * @return   string                The appropriate restriction message.
     */
    public function get_restriction_message($content = '') {
        // If user is not logged in, show login/register message
        if (!is_user_logged_in()) {
            // Get login and register URLs
            $login_url = wp_login_url(get_permalink());
            $register_url = wp_registration_url();
            
            $message = '<div class="wcs-course-access-restricted">';
            $message .= '<h2>' . __('Course Access Restricted', 'wp-course-subscription') . '</h2>';
            $message .= '<p>' . __('You need to log in or create an account to access our courses.', 'wp-course-subscription') . '</p>';
            $message .= '<div class="wcs-course-access-buttons">';
            $message .= '<a href="' . esc_url($login_url) . '" class="wcs-login-button">' . __('Login', 'wp-course-subscription') . '</a>';
            if (get_option('users_can_register')) {
                $message .= '<a href="' . esc_url($register_url) . '" class="wcs-register-button">' . __('Register', 'wp-course-subscription') . '</a>';
            }
            $message .= '</div>';
            $message .= '</div>';
            
            return $message;
        }
        // If user is logged in but doesn't have an active subscription
        else {
            return $this->subscription_required_message('');
        }
    }

    /**
     * Display subscription required message.
     *
     * @since    1.0.0
     * @param    string    $content    Post content.
     * @return   string                Modified content.
     */
    public function subscription_required_message($content) {
        $subscription_page_id = get_option('wcs_subscription_page_id', 0);
        $subscription_url = $subscription_page_id ? get_permalink($subscription_page_id) : home_url();
        
        $message = '<div class="wcs-subscription-required">';
        $message .= '<h2>' . __('Subscribe to Access Courses', 'wp-course-subscription') . '</h2>';
        $message .= '<p>' . __('You need an active subscription to access our course library.', 'wp-course-subscription') . '</p>';
        $message .= '<p><a href="' . esc_url($subscription_url) . '" class="wcs-button wcs-button-primary">' . __('View Subscription Plans', 'wp-course-subscription') . '</a></p>';
        $message .= '</div>';
        
        return $message;
    }

    /**
     * Filter course query to hide courses from non-subscribers.
     *
     * @since    1.0.0
     * @param    WP_Query    $query    The query object.
     */
    public function filter_course_query($query) {
        // Only modify frontend, non-admin, main queries
        if (is_admin() || !$query->is_main_query()) {
            return;
        }

        // Check if we're specifically querying courses
        if (isset($query->query_vars['post_type']) && $query->query_vars['post_type'] === 'course') {
            // If user is not logged in or doesn't have access, hide all courses
            if (!is_user_logged_in() || !$this->has_subscription()) {
                // This will return no results
                $query->set('post__in', array(0));
            }
        }
    }
    
    /**
     * Check if user has an active subscription (helper method)
     *
     * @since    1.0.0
     * @return   bool    True if user has active subscription, false otherwise.
     */
    private function has_subscription() {
        if (!is_user_logged_in()) {
            return false;
        }
        
        $user_id = get_current_user_id();
        
        // Direct database check for active subscription
        global $wpdb;
        $table_name = $wpdb->prefix . 'course_subscriptions';
        
        // Check if table exists first
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
            // Table doesn't exist, fallback to other method
            if (class_exists('WP_Course_Subscription_Subscription')) {
                $subscription = WP_Course_Subscription_Subscription::get_active_subscription();
                return $subscription !== null;
            }
            return false;
        }
        
        $subscription = $wpdb->get_row($wpdb->prepare("
            SELECT id FROM $table_name 
            WHERE user_id = %d 
            AND status = 'active' 
            AND (end_date IS NULL OR end_date > %s)
            ORDER BY id DESC
            LIMIT 1
        ", $user_id, current_time('mysql')));
        
        return $subscription !== null;
    }

    /**
     * Protect course video files for non-subscribers.
     *
     * @since    1.0.0
     * @param    string    $url        The attachment URL.
     * @param    int       $post_id    The attachment post ID.
     * @return   string                The filtered URL.
     */
    public function protect_course_video_files($url, $post_id) {
        // Get the post that this attachment belongs to
        $post_parent = get_post_parent($post_id);
        
        // If this is a course post or attachment to a course post
        if ($post_parent && get_post_type($post_parent->ID) === 'course') {
            // Check if user has access to this specific course
            if (!$this->has_access($post_parent->ID)) {
                return '#'; // Return placeholder URL
            }
        }
        
        // Check if this is a video file and might be used in courses
        $file_info = pathinfo($url);
        $video_extensions = array('mp4', 'webm', 'ogg', 'mov', 'avi', 'wmv', 'flv', 'mkv');
        
        if (isset($file_info['extension']) && in_array(strtolower($file_info['extension']), $video_extensions)) {
            // Only restrict if we're in a course context
            global $post;
            if ($post && get_post_type($post->ID) === 'course' && !$this->has_access($post->ID)) {
                return '#'; // Return placeholder URL
            }
        }
        
        return $url;
    }

    /**
     * Register REST API endpoints for course access.
     *
     * @since    1.0.0
     */
    public function register_rest_routes() {
        register_rest_route('wp-course-subscription/v1', '/check-access/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'check_access_endpoint'),
            'permission_callback' => '__return_true'
        ));
    }

    /**
     * REST API endpoint to check course access.
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Request object.
     * @return   WP_REST_Response               Response object.
     */
    public function check_access_endpoint($request) {
        $post_id = $request->get_param('id');
        
        // Validate post ID and ensure it's a course
        if (!$post_id || get_post_type($post_id) !== 'course') {
            return new WP_REST_Response(array(
                'error' => 'Invalid course ID'
            ), 400);
        }
        
        $has_access = $this->has_access($post_id);
        
        return new WP_REST_Response(array(
            'has_access' => $has_access,
            'post_id' => $post_id
        ));
    }
    
    /**
     * Restrict access to courses in search results and other queries
     *
     * @since    1.0.0
     * @param    array     $posts    Array of post objects.
     * @param    WP_Query  $query    The WP_Query instance.
     * @return   array               Filtered array of post objects.
     */
    public function restrict_courses_in_results($posts, $query) {
        // Don't filter in admin or if no posts
        if (is_admin() || empty($posts)) {
            return $posts;
        }
        
        $filtered_posts = array();
        
        foreach ($posts as $post) {
            // If not a course post type, include it
            if ($post->post_type !== 'course') {
                $filtered_posts[] = $post;
                continue;
            }
            
            // If user has access to this specific course, include it
            if ($this->has_access($post->ID)) {
                $filtered_posts[] = $post;
                continue;
            }
            
            // Otherwise, exclude it (by not adding to filtered_posts)
        }
        
        return $filtered_posts;
    }
    
    /**
     * Restrict embedded content in courses
     *
     * @since    1.0.0
     * @param    string    $html      The embed HTML.
     * @param    string    $url       The embed URL.
     * @param    array     $attr      The embed attributes.
     * @param    int       $post_id   The post ID.
     * @return   string               The filtered embed HTML.
     */
    public function restrict_embedded_content($html, $url, $attr, $post_id) {
        // Only restrict if this is course content and user doesn't have access
        if (get_post_type($post_id) === 'course' && !$this->has_access($post_id)) {
            return '<div class="wcs-restricted-embed">' . __('This content is available to subscribers only.', 'wp-course-subscription') . '</div>';
        }
        return $html;
    }
    
    /**
     * Filter shortcode output for course content
     *
     * @since    1.0.0
     * @param    string    $output     Shortcode output.
     * @param    string    $tag        Shortcode name.
     * @param    array     $attr       Shortcode attributes.
     * @param    array     $m          Regular expression match array.
     * @return   string                Filtered shortcode output.
     */
    public function filter_shortcode_output($output, $tag, $attr, $m) {
        global $post;
        
        // If not in a course context, return original output
        if (!$post || get_post_type($post->ID) !== 'course') {
            return $output;
        }
        
        // If user doesn't have access, restrict certain shortcodes
        if (!$this->has_access($post->ID)) {
            // Only restrict certain shortcodes that might display course content
            $restricted_shortcodes = array('video', 'embed', 'playlist', 'wp_video', 'course_content');
            if (in_array($tag, $restricted_shortcodes)) {
                return '<div class="wcs-restricted-shortcode">' . __('This content is available to subscribers only.', 'wp-course-subscription') . '</div>';
            }
        }
        
        return $output;
    }
}