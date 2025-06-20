<?php
/**
 * Admin Order Details Template
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Course_Subscription
 * @subpackage WP_Course_Subscription/admin/partials
 */
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Order Details', 'wp-course-subscription'); ?></h1>
    <a href="<?php echo admin_url('admin.php?page=wp-course-subscription-orders'); ?>" class="page-title-action"><?php _e('Back to Orders', 'wp-course-subscription'); ?></a>
    
    <div class="wcs-order-details">
        <div class="wcs-order-header">
            <h2><?php printf(__('Order #%d', 'wp-course-subscription'), $order->id); ?></h2>
            <span class="wcs-order-date"><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($order->created_at)); ?></span>
        </div>
        
        <div class="wcs-order-meta">
            <div class="wcs-order-status">
                <?php
                $status_classes = array(
                    'pending' => 'wcs-badge-warning',
                    'completed' => 'wcs-badge-success',
                    'failed' => 'wcs-badge-danger'
                );
                $status_class = isset($status_classes[$order->status]) ? $status_classes[$order->status] : '';
                ?>
                <span class="wcs-badge <?php echo esc_attr($status_class); ?>"><?php echo esc_html(ucfirst($order->status)); ?></span>
            </div>
            
            <div class="wcs-order-amount">
                <strong><?php _e('Amount:', 'wp-course-subscription'); ?></strong>
                $<?php echo number_format($order->amount, 2); ?>
            </div>
            
            <div class="wcs-order-payment">
                <strong><?php _e('Payment Method:', 'wp-course-subscription'); ?></strong>
                <?php echo esc_html(ucfirst($order->payment_method)); ?>
            </div>
            
            <?php if ($order->stripe_payment_intent_id): ?>
                <div class="wcs-order-payment-id">
                    <strong><?php _e('Payment ID:', 'wp-course-subscription'); ?></strong>
                    <?php echo esc_html($order->stripe_payment_intent_id); ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="wcs-order-sections">
            <div class="wcs-order-section">
                <h3><?php _e('Customer Information', 'wp-course-subscription'); ?></h3>
                
                <table class="wcs-order-info-table">
                    <tr>
                        <th><?php _e('Name:', 'wp-course-subscription'); ?></th>
                        <td><?php echo esc_html($order->display_name); ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('Email:', 'wp-course-subscription'); ?></th>
                        <td><?php echo esc_html($order->user_email); ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('Username:', 'wp-course-subscription'); ?></th>
                        <td><?php echo esc_html($order->user_login); ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('User ID:', 'wp-course-subscription'); ?></th>
                        <td><?php echo esc_html($order->user_id); ?></td>
                    </tr>
                </table>
                
                <a href="<?php echo admin_url('user-edit.php?user_id=' . $order->user_id); ?>" class="button"><?php _e('Edit User', 'wp-course-subscription'); ?></a>
            </div>
            
            <div class="wcs-order-section">
                <h3><?php _e('Subscription Details', 'wp-course-subscription'); ?></h3>
                
                <?php if ($order->subscription_id): ?>
                    <table class="wcs-order-info-table">
                        <tr>
                            <th><?php _e('Plan:', 'wp-course-subscription'); ?></th>
                            <td><?php echo esc_html($order->plan_name); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('Price:', 'wp-course-subscription'); ?></th>
                            <td>$<?php echo number_format($order->plan_price, 2); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('Duration:', 'wp-course-subscription'); ?></th>
                            <td>
                                <?php 
                                if ($order->plan_duration > 0) {
                                    echo sprintf(_n('%d day', '%d days', $order->plan_duration, 'wp-course-subscription'), $order->plan_duration);
                                } else {
                                    _e('Unlimited', 'wp-course-subscription');
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Status:', 'wp-course-subscription'); ?></th>
                            <td>
                                <?php
                                $status_classes = array(
                                    'active' => 'wcs-badge-success',
                                    'cancelled' => 'wcs-badge-danger',
                                    'expired' => 'wcs-badge-warning'
                                );
                                $status_class = isset($status_classes[$order->subscription_status]) ? $status_classes[$order->subscription_status] : '';
                                ?>
                                <span class="wcs-badge <?php echo esc_attr($status_class); ?>"><?php echo esc_html(ucfirst($order->subscription_status)); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Start Date:', 'wp-course-subscription'); ?></th>
                            <td><?php echo date_i18n(get_option('date_format'), strtotime($order->start_date)); ?></td>
                        </tr>
                        <?php if ($order->end_date): ?>
                            <tr>
                                <th><?php _e('End Date:', 'wp-course-subscription'); ?></th>
                                <td><?php echo date_i18n(get_option('date_format'), strtotime($order->end_date)); ?></td>
                            </tr>
                        <?php endif; ?>
                    </table>
                <?php else: ?>
                    <p><?php _e('No subscription associated with this order.', 'wp-course-subscription'); ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="wcs-order-actions">
            <?php if ($order->status === 'pending'): ?>
                <a href="<?php echo admin_url('admin.php?page=wp-course-subscription-orders&action=complete&order_id=' . $order->id); ?>" class="button button-primary"><?php _e('Mark as Completed', 'wp-course-subscription'); ?></a>
            <?php endif; ?>
            
            <?php if ($order->status === 'completed'): ?>
                <a href="<?php echo admin_url('admin.php?page=wp-course-subscription-orders&action=refund&order_id=' . $order->id); ?>" class="button"><?php _e('Refund Order', 'wp-course-subscription'); ?></a>
            <?php endif; ?>
            
            <a href="<?php echo admin_url('admin.php?page=wp-course-subscription-orders&action=delete&order_id=' . $order->id); ?>" class="button button-link-delete" onclick="return confirm('<?php _e('Are you sure you want to delete this order?', 'wp-course-subscription'); ?>');"><?php _e('Delete Order', 'wp-course-subscription'); ?></a>
        </div>
    </div>
</div>
