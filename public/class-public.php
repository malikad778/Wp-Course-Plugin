<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Course_Subscription
 * @subpackage WP_Course_Subscription/public
 */

class WP_Course_Subscription_Public {

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
        add_filter('template_include', array($this, 'maybe_load_checkout_template'));
        add_filter('template_include', array($this, 'maybe_load_thankyou_template'));
        // Register shortcodes
        add_shortcode('subscription_plans', array($this, 'subscription_plans_shortcode'));
        add_shortcode('courses_displays', array($this, 'courses_display_shortcode'));
        
            add_action('wp', array($this, 'initialize_session'));

    }
// Add this new method to the class
public function initialize_session() {
    if (!is_admin() && session_status() === PHP_SESSION_NONE && !headers_sent()) {
        session_start();
    }
    
    // Initialize cart if not exists
    if (!isset($_SESSION['wcs_cart_plan'])) {
        $_SESSION['wcs_cart_plan'] = null;
    }
}
    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
         wp_enqueue_script("jquery");
wp_enqueue_script(
    $this->plugin_name,
    plugin_dir_url(__FILE__) . 'js/wcs-public.js',
    array('jquery'),
    $this->version,
    true
);
        // Add AJAX URL and nonces
        wp_localize_script($this->plugin_name, 'wcs_subscription', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'add_to_cart_nonce' => wp_create_nonce('wcs-add-to-cart-nonce'),
            'create_payment_intent_nonce' => wp_create_nonce('wcs-create-payment-intent-nonce'),
            'create_paypal_order_nonce' => wp_create_nonce('wcs-create-paypal-order-nonce'),
            'capture_paypal_order_nonce' => wp_create_nonce('wcs-capture-paypal-order-nonce'),
            'process_checkout_nonce' => wp_create_nonce('wcs-process-checkout-nonce')
        ));
        
        // Load Stripe JS if on checkout page
        if (is_page(get_option('wcs_checkout_page_id', 0))) {
            wp_enqueue_script('stripe-js', 'https://js.stripe.com/v3/', array(), null, false);
        }
        
    }

    /**
     * Prevent WooCommerce from redirecting our checkout page
     *
     * @since    1.0.0
     */
    public function prevent_woocommerce_redirect() {
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            return;
        }
        
        // Get our checkout page ID
        $checkout_page_id = get_option('wcs_checkout_page_id', 0);
        
        // If we're on our checkout page, remove WooCommerce redirects
        if ($checkout_page_id && is_page($checkout_page_id)) {
            // Remove WooCommerce template_redirect actions that might cause redirects
            remove_action('template_redirect', array('WC_Form_Handler', 'redirect_to_cart'));
            remove_action('template_redirect', array('WC_Form_Handler', 'save_address'));
            remove_action('template_redirect', array('WC_Form_Handler', 'save_account_details'));
            
            // Also remove the cart redirect filter
            remove_filter('template_redirect', 'wc_checkout_redirect_empty_cart');
            
            // Remove any other WooCommerce redirects
            remove_action('template_redirect', 'wc_redirect_to_checkout');
            
            // Disable WooCommerce cart fragments on our checkout page
            add_filter('woocommerce_cart_needs_payment', '__return_false');
            add_filter('woocommerce_cart_needs_shipping', '__return_false');
            
            // Disable WooCommerce checkout validation
            add_filter('woocommerce_checkout_update_order_review_expired', '__return_false');
        }
    }

    /**
     * Subscription plans shortcode
     *
     * @since    1.0.0
     * @param    array     $atts    Shortcode attributes.
     * @return   string             Shortcode output.
     */
public function subscription_plans_shortcode($atts) {
        ob_start();
        
        // Get subscription plans
        $plans = WP_Course_Subscription_Subscription::get_subscription_plans();
        
        if (empty($plans)) {
            echo '<p>' . __('لا توجد خطط اشتراك متاحة.', 'wp-course-subscription') . '</p>';
        } else {
            ?>
            <div class="wcs-subscription-plans">
                <div class="wcs-plans-grid">
                    <?php foreach ($plans as $plan) { ?>
                        <div class="wcs-plan-card">
                            <div class="wcs-plan-header">
                                <h2 class="wcs-plan-name"><?php echo esc_html($plan->name); ?></h2>
                                <div class="wcs-plan-price">
                                    <span class="wcs-price-amount">$<?php echo number_format($plan->price, 2); ?></span>
                                    <?php if ($plan->duration > 0) { ?>
                                        <span class="wcs-price-period">/ <?php echo sprintf(_n('%d يوم', '%d أيام', $plan->duration, 'wp-course-subscription'), $plan->duration); ?></span>
                                    <?php } ?>
                                </div>
                            </div>
                            
                            <div class="wcs-plan-content">
                                <div class="wcs-plan-description">
                                    <?php echo wpautop(esc_html($plan->description)); ?>
                                </div>
                                
                                <div class="wcs-plan-features">
                                    <ul>
                                        <li><?php _e('الوصول إلى جميع الدورات', 'wp-course-subscription'); ?></li>
                                        <li><?php _e('جودة فيديو عالية الدقة', 'wp-course-subscription'); ?></li>
                                        <li><?php _e('موارد قابلة للتحميل', 'wp-course-subscription'); ?></li>
                                        <?php if ($plan->duration === 0) { ?>
                                            <li><?php _e('وصول مدى الحياة', 'wp-course-subscription'); ?></li>
                                        <?php } ?>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="wcs-plan-footer">
                                <?php if (is_user_logged_in()) { ?>
                                    <button class="wcs-select-plan-button" data-plan-id="<?php echo esc_attr($plan->id); ?>"><?php _e('اختر الخطة', 'wp-course-subscription'); ?></button>
                                <?php } else { ?>
                                    <a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>" class="wcs-button"><?php _e('سجل دخولك للاشتراك', 'wp-course-subscription'); ?></a>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                
                <?php if (is_user_logged_in()) { ?>
                    <script type="text/javascript">
                        jQuery(document).ready(function($) {
                            $('.wcs-select-plan-button').on('click', function() {
                                var planId = $(this).data('plan-id');
                                var $button = $(this);
                                
                                $button.prop('disabled', true).text('<?php _e('جاري المعالجة...', 'wp-course-subscription'); ?>');
                                
                                $.ajax({
                                    url: wcs_subscription.ajax_url,
                                    type: 'POST',
                                    data: {
                                        action: 'wcs_add_to_cart',
                                        plan_id: planId,
                                        security: wcs_subscription.add_to_cart_nonce
                                    },
                                    success: function(response) {
                                        if (response.success) {
                                            window.location.href = response.redirect;
                                        } else {
                                            alert(response.message);
                                            $button.prop('disabled', false).text('<?php _e('اختر الخطة', 'wp-course-subscription'); ?>');
                                        }
                                    },
                                    error: function() {
                                        alert('<?php _e('حدث خطأ. يرجى المحاولة مرة أخرى.', 'wp-course-subscription'); ?>');
                                        $button.prop('disabled', false).text('<?php _e('اختر الخطة', 'wp-course-subscription'); ?>');
                                    }
                                });
                            });
                        });
                    </script>
                <?php } ?>
            </div>
            <?php
        }
        
        return ob_get_clean();
    }
    
    
public function maybe_load_checkout_template($template) {
    $checkout_page_id = get_option('wcs_checkout_page_id');
    if (is_page($checkout_page_id)) {
        $custom_template = plugin_dir_path(__FILE__) . 'templates/checkout.php';
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }
    return $template;
}
public function maybe_load_thankyou_template($template) {
    $thankyou_page_id = get_option('wcs_thankyou_page_id');
    if ($thankyou_page_id && is_page($thankyou_page_id)) {
        $custom_template = plugin_dir_path(__FILE__) . 'templates/thank-you.php';
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }
    return $template;
}
 
/**
     * Courses display shortcode
     *
     * @since    1.0.0
     * @param    array     $atts    Shortcode attributes.
     * @return   string             Shortcode output.
     */
public function courses_display_shortcode($atts) {
    ob_start();
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        // Get subscription page ID for redirect after login
        $subscription_page_id = get_option('wcs_subscription_page_id', 0);
        $redirect_url = $subscription_page_id ? get_permalink($subscription_page_id) : home_url();
        
        echo '<div class="wcs-course-access-restricted">';
        echo '<h2>' . __('الوصول للدورات محدود', 'wp-course-subscription') . '</h2>';
        echo '<p>' . __('تحتاج إلى تسجيل الدخول أو إنشاء حساب للوصول إلى دوراتنا.', 'wp-course-subscription') . '</p>';
        echo '<div class="wcs-course-access-buttons">';
        echo '<a href="' . esc_url(wp_login_url($redirect_url)) . '" class="wcs-login-button">' . __('تسجيل الدخول', 'wp-course-subscription') . '</a>';
        echo '<a href="' . esc_url(wp_registration_url()) . '" class="wcs-register-button">' . __('إنشاء حساب', 'wp-course-subscription') . '</a>';
        echo '</div>';
        echo '</div>';
        
        return ob_get_clean();
    }
    
    // Check if user has active subscription
    $subscription = WP_Course_Subscription_Subscription::get_active_subscription();
    
    if (!$subscription) {
        $subscription_page_id = get_option('wcs_subscription_page_id', 0);
        $subscription_url = $subscription_page_id ? get_permalink($subscription_page_id) : home_url();
        
        echo '<div class="wcs-subscription-required">';
        echo '<h2>' . __('اشترك للوصول إلى الدورات', 'wp-course-subscription') . '</h2>';
        echo '<p>' . __('تحتاج إلى اشتراك نشط للوصول إلى مكتبة الدورات الخاصة بنا.', 'wp-course-subscription') . '</p>';
        echo '<p><a href="' . esc_url($subscription_url) . '" class="wcs-button wcs-button-primary">' . __('عرض خطط الاشتراك', 'wp-course-subscription') . '</a></p>';
        echo '</div>';
        
        return ob_get_clean();
    }
    
    // User is logged in and has active subscription, display courses
    $args = array(
        'post_type' => 'course',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'order' => 'DESC'
    );
    
    $query = new WP_Query($args);
    $i = 0;
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $i++;
            $video_url = get_field('video'); // ACF field (File URL)
            
            // Get PDF files from custom meta field
            $pdf_files = Course_PDF_Manager::get_course_pdfs(get_the_ID());
            
            // Debug information
            error_log('=== POST ID: ' . get_the_ID() . ' ===');
            error_log('PDF files from custom meta: ' . print_r($pdf_files, true));
            error_log('PDF files count: ' . count($pdf_files));
            
            $valid_pdfs = $pdf_files; // Custom manager already returns clean data
            
            error_log('Valid PDF files count: ' . count($valid_pdfs));
            
            echo '<div class="CourseMainBox">';
            echo '<div class="InnerBoxCourse">';
            echo '<div class="CourseBox">';
            
            // Even posts: Video Left, Text Right
            if ($i % 2 == 0) {
                echo '<div class="CourseVideoSide">';
                if ($video_url) {
                    echo '<video controls width="100%">';
                    echo '<source src="' . esc_url($video_url) . '" type="video/mp4">';
                    echo 'متصفحك لا يدعم تشغيل الفيديو.';
                    echo '</video>';
                } else {
                    echo '<p>لا يوجد فيديو متاح</p>';
                }
                echo '</div>';
                
                echo '<div class="CourseTextSide">';
                echo '<h2>' . get_the_title() . '</h2>';
                echo '<div class="CusExcpt"><p>' . get_the_excerpt() . '</p></div>';
                
                // Display PDF files from ACF field
                if (!empty($valid_pdfs)) {
                    echo '<div class="course-attachments">';
                    echo '<h3>ملفات PDF:</h3>';
                    echo '<ul class="attachment-list">';
                    
                    foreach ($valid_pdfs as $pdf_file) {
                        $file_size = isset($pdf_file['filesize']) ? size_format($pdf_file['filesize']) : 'غير محدد';
                        
                        // Use custom title if available, otherwise use file title or filename
                        $file_title = '';
                        if (isset($pdf_file['custom_title']) && !empty($pdf_file['custom_title'])) {
                            $file_title = $pdf_file['custom_title'];
                        } elseif (isset($pdf_file['title']) && !empty($pdf_file['title'])) {
                            $file_title = $pdf_file['title'];
                        } else {
                            $file_title = isset($pdf_file['filename']) ? $pdf_file['filename'] : 'PDF File';
                        }
                        
                        echo '<li class="attachment-item">';
                        echo '<a href="' . esc_url($pdf_file['url']) . '" target="_blank" class="attachment-link">';
                        echo '<span class="file-icon pdf-icon">📄</span>';
                        echo '<span class="file-info">';
                        echo '<span class="file-name">' . esc_html($file_title) . '</span>';
                        echo '<span class="file-meta">(PDF - ' . $file_size . ')</span>';
                        echo '</span>';
                        echo '</a>';
                        echo '</li>';
                    }
                    
                    echo '</ul>';
                    echo '</div>';
                } 
                
                echo '</div>';
            }
            // Odd posts: Text Left, Video Right
            else {
                echo '<div class="CourseTextSide">';
                echo '<h2>' . get_the_title() . '</h2>';
                echo '<div class="CusExcpt"><p>' . get_the_excerpt() . '</p></div>';
                
                // Display PDF files from ACF field
                if (!empty($valid_pdfs)) {
                    echo '<div class="course-attachments">';
                    echo '<h3>ملفات PDF:</h3>';
                    echo '<ul class="attachment-list">';
                    
                    foreach ($valid_pdfs as $pdf_file) {
                        $file_size = isset($pdf_file['filesize']) ? size_format($pdf_file['filesize']) : 'غير محدد';
                        $file_title = isset($pdf_file['title']) && !empty($pdf_file['title']) ? $pdf_file['title'] : $pdf_file['filename'];
                        
                        echo '<li class="attachment-item">';
                        echo '<a href="' . esc_url($pdf_file['url']) . '" target="_blank" class="attachment-link">';
                        echo '<span class="file-icon pdf-icon">📄</span>';
                        echo '<span class="file-info">';
                        echo '<span class="file-name">' . esc_html($file_title) . '</span>';
                        echo '<span class="file-meta">(PDF - ' . $file_size . ')</span>';
                        echo '</span>';
                        echo '</a>';
                        echo '</li>';
                    }
                    
                    echo '</ul>';
                    echo '</div>';
                }
                
                echo '</div>';
                
                echo '<div class="CourseVideoSide">';
                if ($video_url) {
                    echo '<video controls width="100%">';
                    echo '<source src="' . esc_url($video_url) . '" type="video/mp4">';
                    echo 'متصفحك لا يدعم تشغيل الفيديو.';
                    echo '</video>';
                } else {
                    echo '<p>لا يوجد فيديو متاح</p>';
                }
                echo '</div>';
            }
            
            echo '</div>'; // CourseBox
            echo '</div>'; // InnerBoxCourse
            echo '</div>'; // CourseMainBox
        }
        
        wp_reset_postdata();
    } else {
        echo '<p>لم يتم العثور على دورات.</p>';
    }
    
    return ob_get_clean();
}
    /**
     * Add custom CSS for subscription and login pages
     *
     * @since    1.0.0
     */
    public function add_custom_styles() {
        ?>
        <style>
        .course-attachments {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #eee;
}

.course-attachments h3 {
    margin-bottom: 10px;
    font-size: 16px;
}

.attachment-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.attachment-item {
    margin-bottom: 8px;
}

.attachment-link {
    display: flex;
    align-items: center;
    text-decoration: none;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    transition: background-color 0.3s;
}

.attachment-link:hover {
    background-color: #f5f5f5;
}

.file-icon {
    margin-left: 10px;
    font-size: 18px;
}

.file-info {
    display: flex;
    flex-direction: column;
}

.file-name {
    font-weight: bold;
    margin-bottom: 2px;
}

.file-meta {
    font-size: 12px;
    color: #666;
}
            /* Course Access Restriction */
            .wcs-course-access-restricted,
            .wcs-login-required,
            .wcs-no-plan,
            .wcs-subscription-required {
                max-width: 600px;
                margin: 40px auto;
                padding: 30px;
                background: #f9f9f9;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                text-align: center;
            }
            .CourseMainBox{
                min-height: 600px;
            }
            .wcs-course-access-buttons {
                margin-top: 20px;
                display: flex;
                justify-content: center;
                gap: 20px;
            }
            
            .wcs-login-button,
            .wcs-register-button,
            .wcs-button {
                display: inline-block;
                padding: 10px 20px;
                background-color: #A5772B;
                color: white;
                text-decoration: none;
                border-radius: 4px;
                font-weight: bold;
                transition: background-color 0.3s;
                border: none;
                cursor: pointer;
            }
            
            .wcs-login-button:hover,
            .wcs-register-button:hover,
            .wcs-button:hover {
                background-color: #005177;
                color: white;
            }
            
            /* Subscription Plans */
            .wcs-subscription-plans {
                max-width: 1200px;
                margin: 40px auto;
            }
            
            .wcs-plans-grid {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                gap: 30px;
                margin-top: 30px;
            }
            
            .wcs-plan-card {
                flex: 1;
                min-width: 280px;
                max-width: 350px;
                background: white;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                overflow: hidden;
                transition: transform 0.3s;
            }
            
            .wcs-plan-card:hover {
                transform: translateY(-5px);
            }
            
            .wcs-plan-header {
                padding: 20px;
                background: #f5f5f5;
                text-align: center;
                border-bottom: 1px solid #eee;
            }
            
            .wcs-plan-name {
                margin-top: 0;
                margin-bottom: 10px;
                font-size: 24px;
            }
            
            .wcs-plan-price {
                font-size: 28px;
                font-weight: bold;
                color: #A5772B;
            }
            
            .wcs-price-period {
                font-size: 16px;
                color: #666;
            }
            
            .wcs-plan-content {
                padding: 20px;
            }
            
            .wcs-plan-description {
                margin-bottom: 20px;
                color: #666;
            }
            
            .wcs-plan-features ul {
                list-style-type: none;
                padding: 0;
                margin: 0;
            }
            
            .wcs-plan-features li {
                padding: 8px 0;
                border-bottom: 1px solid #eee;
            }
            
            .wcs-plan-features li:before {
                content: "✓";
                color: #4CAF50;
                margin-right: 10px;
            }
            
            .wcs-plan-footer {
                padding: 20px;
                text-align: center;
                background: #f9f9f9;
                border-top: 1px solid #eee;
            }
            
            /* Checkout */
            .wcs-checkout {
                max-width: 800px;
                margin: 40px auto;
            }
            
            .wcs-checkout-content {
                display: flex;
                flex-wrap: wrap;
                gap: 30px;
            }
            
            .wcs-checkout-summary,
            .wcs-checkout-payment {
                flex: 1;
                min-width: 300px;
                padding: 20px;
                background: white;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            
            .wcs-checkout-plan {
                display: flex;
                justify-content: space-between;
                padding: 15px 0;
                border-bottom: 1px solid #eee;
            }
            
            .wcs-checkout-details {
                margin-top: 20px;
            }
            
            .wcs-checkout-detail {
                display: flex;
                justify-content: space-between;
                padding: 10px 0;
            }
            
            .wcs-total-price {
                font-weight: bold;
                color: #A5772B;
            }
            
            #card-element {
                padding: 12px;
                border: 1px solid #ddd;
                border-radius: 4px;
                background: #f9f9f9;
                margin-bottom: 20px;
            }
            
            #card-errors {
                color: #e53935;
                margin-bottom: 20px;
            }
            
            /* Thank You */
            .wcs-thank-you {
                max-width: 600px;
                margin: 40px auto;
            }
            
            .wcs-thank-you-content {
                padding: 30px;
                background: white;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                text-align: center;
            }
            
            .wcs-thank-you-icon {
                margin-bottom: 20px;
            }
            
            .wcs-thank-you-message {
                margin-bottom: 30px;
            }
            
            /* Courses Display */
            .wcs-course-main-box {
                margin-bottom: 40px;
            }
            
            .wcs-course-inner-box {
                background: white;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                overflow: hidden;
            }
            
            .wcs-course-box {
                display: flex;
                flex-wrap: wrap;
            }
            
            .wcs-course-video-side,
            .wcs-course-text-side {
                flex: 1;
                min-width: 300px;
                padding: 20px;
            }
            
            .wcs-course-text-side h2 {
                margin-top: 0;
            }
            
            .wcs-course-excerpt {
                margin-bottom: 20px;
            }
            
            /* Responsive */
            @media (max-width: 768px) {
                .wcs-checkout-content,
                .wcs-course-box {
                    flex-direction: column;
                }
                
                .wcs-course-video-side,
                .wcs-course-text-side {
                    width: 100%;
                }
            }
        </style>
        <?php
    }
}
