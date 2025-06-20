<?php
/**
 * Admin Plans List Template
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Course_Subscription
 * @subpackage WP_Course_Subscription/admin/partials
 */
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Subscription Plans', 'wp-course-subscription'); ?></h1>
    <a href="<?php echo admin_url('admin.php?page=wp-course-subscription-plans&action=add'); ?>" class="page-title-action"><?php _e('Add New', 'wp-course-subscription'); ?></a>
    
    <?php
    // Display messages
    if (isset($_GET['message'])) {
        if ($_GET['message'] === 'added') {
            echo '<div class="notice notice-success is-dismissible"><p>' . __('Subscription plan added successfully.', 'wp-course-subscription') . '</p></div>';
        } elseif ($_GET['message'] === 'updated') {
            echo '<div class="notice notice-success is-dismissible"><p>' . __('Subscription plan updated successfully.', 'wp-course-subscription') . '</p></div>';
        } elseif ($_GET['message'] === 'deleted') {
            echo '<div class="notice notice-success is-dismissible"><p>' . __('Subscription plan deleted successfully.', 'wp-course-subscription') . '</p></div>';
        }
    }
    ?>
    
    <div class="wcs-admin-content">
        <?php if (empty($plans)): ?>
            <div class="wcs-empty-state">
                <p><?php _e('No subscription plans found.', 'wp-course-subscription'); ?></p>
                <a href="<?php echo admin_url('admin.php?page=wp-course-subscription-plans&action=add'); ?>" class="button button-primary"><?php _e('Add New Plan', 'wp-course-subscription'); ?></a>
            </div>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('ID', 'wp-course-subscription'); ?></th>
                        <th><?php _e('Name', 'wp-course-subscription'); ?></th>
                        <th><?php _e('Description', 'wp-course-subscription'); ?></th>
                        <th><?php _e('Price', 'wp-course-subscription'); ?></th>
                        <th><?php _e('Duration', 'wp-course-subscription'); ?></th>
                        <th><?php _e('Status', 'wp-course-subscription'); ?></th>
                        <th><?php _e('Actions', 'wp-course-subscription'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($plans as $plan): ?>
                        <tr>
                            <td><?php echo esc_html($plan->id); ?></td>
                            <td><?php echo esc_html($plan->name); ?></td>
                            <td><?php echo wp_trim_words(esc_html($plan->description), 10); ?></td>
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
                            <td>
                                <?php
                                $status_classes = array(
                                    'active' => 'wcs-badge-success',
                                    'inactive' => 'wcs-badge-danger'
                                );
                                $status_class = isset($status_classes[$plan->status]) ? $status_classes[$plan->status] : '';
                                ?>
                                <span class="wcs-badge <?php echo esc_attr($status_class); ?>"><?php echo esc_html(ucfirst($plan->status)); ?></span>
                            </td>
                            <td>
                                <a href="<?php echo admin_url('admin.php?page=wp-course-subscription-plans&action=edit&plan_id=' . $plan->id); ?>" class="button button-small"><?php _e('Edit', 'wp-course-subscription'); ?></a>
                                <a href="<?php echo admin_url('admin.php?page=wp-course-subscription-plans&action=delete&plan_id=' . $plan->id); ?>" class="button button-small button-link-delete" onclick="return confirm('<?php _e('Are you sure you want to delete this plan?', 'wp-course-subscription'); ?>');"><?php _e('Delete', 'wp-course-subscription'); ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
