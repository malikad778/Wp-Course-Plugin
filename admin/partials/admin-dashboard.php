<?php
/**
 * Admin Dashboard Template
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
    
    <div class="wcs-admin-dashboard">
        <div class="wcs-admin-card">
            <h2><?php _e('Orders Overview', 'wp-course-subscription'); ?></h2>
            
            <?php
            global $wpdb;
            $orders_table = $wpdb->prefix . 'course_orders';
            
            // Get order stats
            $total_orders = $wpdb->get_var("SELECT COUNT(id) FROM $orders_table");
            $completed_orders = $wpdb->get_var("SELECT COUNT(id) FROM $orders_table WHERE status = 'completed'");
            $pending_orders = $wpdb->get_var("SELECT COUNT(id) FROM $orders_table WHERE status = 'pending'");
            $failed_orders = $wpdb->get_var("SELECT COUNT(id) FROM $orders_table WHERE status = 'failed'");
            
            // Get total revenue
            $total_revenue = $wpdb->get_var("SELECT SUM(amount) FROM $orders_table WHERE status = 'completed'");
            $total_revenue = $total_revenue ? $total_revenue : 0;
            ?>
            
            <div class="wcs-stats-grid">
                <div class="wcs-stat-box">
                    <span class="wcs-stat-value"><?php echo esc_html($total_orders); ?></span>
                    <span class="wcs-stat-label"><?php _e('Total Orders', 'wp-course-subscription'); ?></span>
                </div>
                
                <div class="wcs-stat-box">
                    <span class="wcs-stat-value"><?php echo esc_html($completed_orders); ?></span>
                    <span class="wcs-stat-label"><?php _e('Completed Orders', 'wp-course-subscription'); ?></span>
                </div>
                
                <div class="wcs-stat-box">
                    <span class="wcs-stat-value"><?php echo esc_html($pending_orders); ?></span>
                    <span class="wcs-stat-label"><?php _e('Pending Orders', 'wp-course-subscription'); ?></span>
                </div>
                
                <div class="wcs-stat-box">
                    <span class="wcs-stat-value"><?php echo esc_html($failed_orders); ?></span>
                    <span class="wcs-stat-label"><?php _e('Failed Orders', 'wp-course-subscription'); ?></span>
                </div>
                
                <div class="wcs-stat-box wcs-stat-box-large">
                    <span class="wcs-stat-value">$<?php echo number_format($total_revenue, 2); ?></span>
                    <span class="wcs-stat-label"><?php _e('Total Revenue', 'wp-course-subscription'); ?></span>
                </div>
            </div>
            
            <a href="<?php echo admin_url('admin.php?page=wp-course-subscription-orders'); ?>" class="button button-primary"><?php _e('View All Orders', 'wp-course-subscription'); ?></a>
        </div>
        
        <div class="wcs-admin-card">
            <h2><?php _e('Subscription Plans', 'wp-course-subscription'); ?></h2>
            
            <?php
            $plans_table = $wpdb->prefix . 'course_subscription_plans';
            $active_plans = $wpdb->get_var("SELECT COUNT(id) FROM $plans_table WHERE status = 'active'");
            $plans = $wpdb->get_results("SELECT * FROM $plans_table WHERE status = 'active' ORDER BY price ASC LIMIT 5");
            ?>
            
            <p><?php printf(_n('You have %d active subscription plan.', 'You have %d active subscription plans.', $active_plans, 'wp-course-subscription'), $active_plans); ?></p>
            
            <?php if ($plans): ?>
                <table class="wcs-admin-table">
                    <thead>
                        <tr>
                            <th><?php _e('Name', 'wp-course-subscription'); ?></th>
                            <th><?php _e('Price', 'wp-course-subscription'); ?></th>
                            <th><?php _e('Duration', 'wp-course-subscription'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($plans as $plan): ?>
                            <tr>
                                <td><?php echo esc_html($plan->name); ?></td>
                                <td>$<?php echo number_format($plan->price, 2); ?></td>
                                <td>
                                    <?php 
                                    if ($plan->duration > 0) {
                                        echo sprintf(_n('%d day', '%d days', $plan->duration, 'wp-course-subscription'), $plan->duration);
                                    } else {
                                        _e('Unlimited', 'wp-course-subscription');
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p><?php _e('No active subscription plans found.', 'wp-course-subscription'); ?></p>
            <?php endif; ?>
            
            <a href="<?php echo admin_url('admin.php?page=wp-course-subscription-plans'); ?>" class="button button-primary"><?php _e('Manage Plans', 'wp-course-subscription'); ?></a>
        </div>
        
        <div class="wcs-admin-card">
            <h2><?php _e('Recent Orders', 'wp-course-subscription'); ?></h2>
            
            <?php
            $recent_orders = $wpdb->get_results("
                SELECT o.*, u.display_name, u.user_email
                FROM $orders_table o
                LEFT JOIN {$wpdb->users} u ON o.user_id = u.ID
                ORDER BY o.created_at DESC
                LIMIT 5
            ");
            ?>
            
            <?php if ($recent_orders): ?>
                <table class="wcs-admin-table">
                    <thead>
                        <tr>
                            <th><?php _e('ID', 'wp-course-subscription'); ?></th>
                            <th><?php _e('Customer', 'wp-course-subscription'); ?></th>
                            <th><?php _e('Amount', 'wp-course-subscription'); ?></th>
                            <th><?php _e('Status', 'wp-course-subscription'); ?></th>
                            <th><?php _e('Date', 'wp-course-subscription'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td><?php echo esc_html($order->id); ?></td>
                                <td>
                                    <?php if ($order->display_name): ?>
                                        <?php echo esc_html($order->display_name); ?><br>
                                        <small><?php echo esc_html($order->user_email); ?></small>
                                    <?php else: ?>
                                        <?php _e('Unknown', 'wp-course-subscription'); ?>
                                    <?php endif; ?>
                                </td>
                                <td>$<?php echo number_format($order->amount, 2); ?></td>
                                <td>
                                    <?php
                                    $status_classes = array(
                                        'pending' => 'wcs-badge-warning',
                                        'completed' => 'wcs-badge-success',
                                        'failed' => 'wcs-badge-danger'
                                    );
                                    $status_class = isset($status_classes[$order->status]) ? $status_classes[$order->status] : '';
                                    ?>
                                    <span class="wcs-badge <?php echo esc_attr($status_class); ?>"><?php echo esc_html(ucfirst($order->status)); ?></span>
                                </td>
                                <td><?php echo date_i18n(get_option('date_format'), strtotime($order->created_at)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p><?php _e('No orders found.', 'wp-course-subscription'); ?></p>
            <?php endif; ?>
            
            <a href="<?php echo admin_url('admin.php?page=wp-course-subscription-orders'); ?>" class="button button-primary"><?php _e('View All Orders', 'wp-course-subscription'); ?></a>
        </div>
    </div>
</div>
