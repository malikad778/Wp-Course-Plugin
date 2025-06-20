<?php
/**
 * Class responsible for activating the plugin
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Course_Subscription
 * @subpackage WP_Course_Subscription/includes
 */

class WP_Course_Subscription_Activator {

    /**
     * Create necessary database tables on plugin activation
     *
     * @since    1.0.0
     */
    public static function activate() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Create subscription plans table
        $table_name = $wpdb->prefix . 'course_subscription_plans';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text NOT NULL,
            price decimal(10,2) NOT NULL,
            duration int NOT NULL,
            stripe_price_id varchar(255) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        // Create subscriptions table
        $table_name = $wpdb->prefix . 'course_subscriptions';
        $sql .= "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            plan_id mediumint(9) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'active',
            start_date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            end_date datetime NULL,
            payment_id varchar(255) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY plan_id (plan_id)
        ) $charset_collate;";

        // Create orders table
        $table_name = $wpdb->prefix . 'course_orders';
        $sql .= "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            plan_id mediumint(9) NOT NULL AFTER user_id,
            subscription_id mediumint(9) NULL,
            amount decimal(10,2) NOT NULL,
            currency varchar(3) NOT NULL DEFAULT 'USD',
            status varchar(20) NOT NULL DEFAULT 'pending',
            payment_method varchar(50) NOT NULL DEFAULT 'stripe',
            payment_id varchar(255) NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY subscription_id (subscription_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Create default subscription plan
        $default_plan = array(
            'name' => 'Monthly Subscription',
            'description' => 'Access to all courses for one month',
            'price' => 29.99,
            'duration' => 30,
            'stripe_price_id' => '',
            'status' => 'active'
        );

        $table_name = $wpdb->prefix . 'course_subscription_plans';
        $wpdb->insert($table_name, $default_plan);

        // Add plugin version to options
        add_option('wp_course_subscription_version', WP_COURSE_SUBSCRIPTION_VERSION);
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}
