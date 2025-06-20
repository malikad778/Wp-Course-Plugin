<?php
/**
 * Admin Plan Form Template
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Course_Subscription
 * @subpackage WP_Course_Subscription/admin/partials
 */
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php echo isset($plan) ? __('Edit Subscription Plan', 'wp-course-subscription') : __('Add New Subscription Plan', 'wp-course-subscription'); ?>
    </h1>
    <a href="<?php echo admin_url('admin.php?page=wp-course-subscription-plans'); ?>" class="page-title-action"><?php _e('Back to Plans', 'wp-course-subscription'); ?></a>
    
    <div class="wcs-admin-content">
        <form method="post" action="">
            <?php if (isset($plan)): ?>
                <?php wp_nonce_field('wcs_edit_plan_nonce'); ?>
                <input type="hidden" name="wcs_edit_plan" value="1">
                <input type="hidden" name="plan_id" value="<?php echo esc_attr($plan->id); ?>">
            <?php else: ?>
                <?php wp_nonce_field('wcs_add_plan_nonce'); ?>
                <input type="hidden" name="wcs_add_plan" value="1">
            <?php endif; ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="name"><?php _e('Name', 'wp-course-subscription'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="name" id="name" class="regular-text" value="<?php echo isset($plan) ? esc_attr($plan->name) : ''; ?>" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="description"><?php _e('Description', 'wp-course-subscription'); ?></label>
                    </th>
                    <td>
                        <textarea name="description" id="description" class="large-text" rows="5"><?php echo isset($plan) ? esc_textarea($plan->description) : ''; ?></textarea>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="price"><?php _e('Price', 'wp-course-subscription'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="price" id="price" class="regular-text" value="<?php echo isset($plan) ? esc_attr($plan->price) : ''; ?>" step="0.01" min="0" required>
                        <p class="description"><?php _e('Price in USD.', 'wp-course-subscription'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="duration"><?php _e('Duration', 'wp-course-subscription'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="duration" id="duration" class="regular-text" value="<?php echo isset($plan) ? esc_attr($plan->duration) : '30'; ?>" min="0" required>
                        <p class="description"><?php _e('Duration in days. Use 0 for unlimited.', 'wp-course-subscription'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="status"><?php _e('Status', 'wp-course-subscription'); ?></label>
                    </th>
                    <td>
                        <select name="status" id="status">
                            <option value="active" <?php echo isset($plan) && $plan->status === 'active' ? 'selected' : ''; ?>><?php _e('Active', 'wp-course-subscription'); ?></option>
                            <option value="inactive" <?php echo isset($plan) && $plan->status === 'inactive' ? 'selected' : ''; ?>><?php _e('Inactive', 'wp-course-subscription'); ?></option>
                        </select>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo isset($plan) ? __('Update Plan', 'wp-course-subscription') : __('Add Plan', 'wp-course-subscription'); ?>">
            </p>
        </form>
    </div>
</div>
