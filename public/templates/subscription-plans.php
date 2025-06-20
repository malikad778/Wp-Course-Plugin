<?php
/**
 * Template for subscription plans page
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Course_Subscription
 * @subpackage WP_Course_Subscription/public/templates
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

get_header();
?>

<div class="wcs-subscription-plans">
    <div class="wcs-container">
        <h1 class="wcs-page-title"><?php _e('Subscription Plans', 'wp-course-subscription'); ?></h1>
        
        <?php
        // Get subscription plans
        $plans = WP_Course_Subscription_Subscription::get_subscription_plans();
        
        if (empty($plans)) {
            echo '<p>' . __('No subscription plans available.', 'wp-course-subscription') . '</p>';
        } else {
        ?>
            <div class="wcs-plans-grid">
                <?php foreach ($plans as $plan) { ?>
                    <div class="wcs-plan-card">
                        <div class="wcs-plan-header">
                            <h2 class="wcs-plan-name"><?php echo esc_html($plan->name); ?></h2>
                            <div class="wcs-plan-price">
                                <span class="wcs-price-amount">$<?php echo number_format($plan->price, 2); ?></span>
                                <?php if ($plan->duration > 0) { ?>
                                    <span class="wcs-price-period">/ <?php echo sprintf(_n('%d day', '%d days', $plan->duration, 'wp-course-subscription'), $plan->duration); ?></span>
                                <?php } ?>
                            </div>
                        </div>
                        
                        <div class="wcs-plan-content">
                            <div class="wcs-plan-description">
                                <?php echo wpautop(esc_html($plan->description)); ?>
                            </div>
                            
                            <div class="wcs-plan-features">
                                <ul>
                                    <li><?php _e('Access to all courses', 'wp-course-subscription'); ?></li>
                                    <li><?php _e('HD video quality', 'wp-course-subscription'); ?></li>
                                    <li><?php _e('Downloadable resources', 'wp-course-subscription'); ?></li>
                                    <?php if ($plan->duration === 0) { ?>
                                        <li><?php _e('Lifetime access', 'wp-course-subscription'); ?></li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="wcs-plan-footer">
                            <?php if (is_user_logged_in()) { ?>
                                <button class="wcs-select-plan-button" data-plan-id="<?php echo esc_attr($plan->id); ?>"><?php _e('Select Plan', 'wp-course-subscription'); ?></button>
                            <?php } else { ?>
                                <button class="wcs-login-button"><?php _e('Login to Subscribe', 'wp-course-subscription'); ?></button>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
            
          
        <?php } ?>
    </div>
</div>

<?php
get_footer();
