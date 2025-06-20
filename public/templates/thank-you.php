<?php
/**
 * Template for thank you page
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
// Check if user is logged in
if (!is_user_logged_in()) {
    wp_redirect(home_url('/'));
    exit;
}
        
get_header();
echo do_shortcode('[elementor-template id="4345"]');

        ?>
        <div class="wcs-thank-you">
            <div class="wcs-thank-you-content">
                <div class="wcs-thank-you-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="64" height="64">
                        <circle cx="12" cy="12" r="11" fill="#4CAF50"/>
                        <path d="M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z" fill="#fff"/>
                    </svg>
                </div>
                
                <h2><?php _e('شكراً لك!', 'wp-course-subscription'); ?></h2>
                
                <div class="wcs-thank-you-message">
                    <p><?php _e('تم معالجة دفعتك بنجاح.', 'wp-course-subscription'); ?></p>
                    <p><?php _e('اشتراكك الآن نشط ولديك إمكانية الوصول إلى جميع الدورات.', 'wp-course-subscription'); ?></p>
                </div>
                
                <div class="wcs-thank-you-actions">
                    <a href="<?php echo esc_url(home_url()); ?>" class="wcs-button wcs-button-primary"><?php _e('ابدأ التعلم', 'wp-course-subscription'); ?></a>
                </div>
            </div>
        </div>

<?php
get_footer();
