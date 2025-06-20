<?php
/**
 * Admin Orders Template
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Course_Subscription
 * @subpackage WP_Course_Subscription/admin/partials
 */
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Orders', 'wp-course-subscription'); ?></h1>
    
    <?php
    // Display messages
    if (isset($_GET['message'])) {
        if ($_GET['message'] === 'deleted') {
            echo '<div class="notice notice-success is-dismissible"><p>' . __('Orders deleted successfully.', 'wp-course-subscription') . '</p></div>';
        }
    }
    ?>
    
    <div class="wcs-admin-filters">
        <form method="get">
            <input type="hidden" name="page" value="wp-course-subscription-orders">
            
            <div class="wcs-filter-row d-flex">
                <div class="wcs-filter-item">
                    <label for="filter-status"><?php _e('Status', 'wp-course-subscription'); ?></label>
                    <select name="status" id="filter-status">
                        <option value=""><?php _e('All Statuses', 'wp-course-subscription'); ?></option>
                        <option value="pending" <?php selected(isset($_GET['status']) && $_GET['status'] === 'pending'); ?>><?php _e('Pending', 'wp-course-subscription'); ?></option>
                        <option value="completed" <?php selected(isset($_GET['status']) && $_GET['status'] === 'completed'); ?>><?php _e('Completed', 'wp-course-subscription'); ?></option>
                        <option value="failed" <?php selected(isset($_GET['status']) && $_GET['status'] === 'failed'); ?>><?php _e('Failed', 'wp-course-subscription'); ?></option>
                    </select>
                </div>
                
                <div class="wcs-filter-item">
                    <label for="filter-date"><?php _e('Date', 'wp-course-subscription'); ?></label>
                    <select name="date" id="filter-date">
                        <option value=""><?php _e('All Dates', 'wp-course-subscription'); ?></option>
                        <option value="today" <?php selected(isset($_GET['date']) && $_GET['date'] === 'today'); ?>><?php _e('Today', 'wp-course-subscription'); ?></option>
                        <option value="yesterday" <?php selected(isset($_GET['date']) && $_GET['date'] === 'yesterday'); ?>><?php _e('Yesterday', 'wp-course-subscription'); ?></option>
                        <option value="this-week" <?php selected(isset($_GET['date']) && $_GET['date'] === 'this-week'); ?>><?php _e('This Week', 'wp-course-subscription'); ?></option>
                        <option value="this-month" <?php selected(isset($_GET['date']) && $_GET['date'] === 'this-month'); ?>><?php _e('This Month', 'wp-course-subscription'); ?></option>
                    </select>
                </div>
                
                <div class="wcs-filter-item">
                    <label for="filter-search"><?php _e('Search', 'wp-course-subscription'); ?></label>
                    <input type="text" name="s" id="filter-search" value="<?php echo isset($_GET['s']) ? esc_attr($_GET['s']) : ''; ?>" placeholder="<?php _e('Search orders...', 'wp-course-subscription'); ?>">
                </div>
                
                <div class="wcs-filter-item">
                    <button type="submit" class="button"><?php _e('Filter', 'wp-course-subscription'); ?></button>
                </div>
            </div>
        </form>
    </div>
    
    <form method="post">
        <?php
        $orders_table->display();
        ?>
    </form>
</div>
