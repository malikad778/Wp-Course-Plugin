<?php
/**
 * Login and Registration Popup Template
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Course_Subscription
 * @subpackage WP_Course_Subscription/public/templates
 */
?>

<div id="wcs-login-popup" class="wcs-popup">
    <div class="wcs-popup-overlay"></div>
    <div class="wcs-popup-content">
        <div class="wcs-popup-header">
            <h2><?php _e('Access Restricted', 'wp-course-subscription'); ?></h2>
            <button class="wcs-popup-close">&times;</button>
        </div>
        
        <div class="wcs-popup-body">
            <div class="wcs-tabs">
                <div class="wcs-tab-nav">
                    <button class="wcs-tab-link active" data-tab="login"><?php _e('Login', 'wp-course-subscription'); ?></button>
                    <button class="wcs-tab-link" data-tab="register"><?php _e('Register', 'wp-course-subscription'); ?></button>
                </div>
                
                <div class="wcs-tab-content">
                    <div id="wcs-login-tab" class="wcs-tab-pane active">
                        <form id="wcs-login-form" class="wcs-form">
                            <div class="wcs-form-group">
                                <label for="wcs-login-username"><?php _e('Username or Email', 'wp-course-subscription'); ?></label>
                                <input type="text" id="wcs-login-username" name="username" required>
                            </div>
                            
                            <div class="wcs-form-group">
                                <label for="wcs-login-password"><?php _e('Password', 'wp-course-subscription'); ?></label>
                                <input type="password" id="wcs-login-password" name="password" required>
                            </div>
                            
                            <div class="wcs-form-group wcs-checkbox">
                                <input type="checkbox" id="wcs-login-remember" name="remember">
                                <label for="wcs-login-remember"><?php _e('Remember Me', 'wp-course-subscription'); ?></label>
                            </div>
                            
                            <div class="wcs-form-group">
                                <button type="submit" class="wcs-button wcs-button-primary"><?php _e('Login', 'wp-course-subscription'); ?></button>
                            </div>
                            
                            <div class="wcs-form-footer">
                                <a href="<?php echo esc_url(wp_lostpassword_url()); ?>" class="wcs-forgot-password"><?php _e('Forgot Password?', 'wp-course-subscription'); ?></a>
                            </div>
                        </form>
                    </div>
                    
                    <div id="wcs-register-tab" class="wcs-tab-pane">
                        <form id="wcs-register-form" class="wcs-form">
                            <div class="wcs-form-group">
                                <label for="wcs-register-username"><?php _e('Username', 'wp-course-subscription'); ?></label>
                                <input type="text" id="wcs-register-username" name="username" required>
                            </div>
                            
                            <div class="wcs-form-group">
                                <label for="wcs-register-email"><?php _e('Email', 'wp-course-subscription'); ?></label>
                                <input type="email" id="wcs-register-email" name="email" required>
                            </div>
                            
                            <div class="wcs-form-group">
                                <label for="wcs-register-password"><?php _e('Password', 'wp-course-subscription'); ?></label>
                                <input type="password" id="wcs-register-password" name="password" required>
                            </div>
                            
                            <div class="wcs-form-group">
                                <button type="submit" class="wcs-button wcs-button-primary"><?php _e('Register', 'wp-course-subscription'); ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Login Popup Styles */
    .wcs-popup {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 9999;
    }
    
    .wcs-popup.active {
        display: block;
    }
    
    .wcs-popup-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
    }
    
    .wcs-popup-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 90%;
        max-width: 500px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.2);
        overflow: hidden;
    }
    
    .wcs-popup-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        background-color: #f5f5f5;
        border-bottom: 1px solid #eee;
    }
    
    .wcs-popup-header h2 {
        margin: 0;
        font-size: 20px;
    }
    
    .wcs-popup-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #666;
    }
    
    .wcs-popup-body {
        padding: 20px;
    }
    
    .wcs-tabs {
        width: 100%;
    }
    
    .wcs-tab-nav {
        display: flex;
        margin-bottom: 20px;
        border-bottom: 1px solid #eee;
    }
    
    .wcs-tab-link {
        flex: 1;
        padding: 10px;
        background: none;
        border: none;
        border-bottom: 2px solid transparent;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        text-align: center;
        transition: all 0.3s;
    }
    
    .wcs-tab-link.active {
        border-bottom-color: #D8A83B;
        color: #D8A83B;
    }
    
    .wcs-tab-pane {
        display: none;
    }
    
    .wcs-tab-pane.active {
        display: block;
    }
    
    .wcs-form {
        width: 100%;
    }
    
    .wcs-form-group {
        margin-bottom: 15px;
    }
    
    .wcs-form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    
    .wcs-form-group input[type="text"],
    .wcs-form-group input[type="email"],
    .wcs-form-group input[type="password"] {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    
    .wcs-checkbox {
        display: flex;
        align-items: center;
    }
    
    .wcs-checkbox input {
        margin-right: 10px;
    }
    
    .wcs-checkbox label {
        margin-bottom: 0;
        font-weight: normal;
    }
    
    .wcs-form-footer {
        margin-top: 15px;
        text-align: center;
    }
    
    .wcs-forgot-password {
        color: #D8A83B;
        text-decoration: none;
    }
    
    .wcs-forgot-password:hover {
        text-decoration: underline;
    }
</style>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        // Show popup
        function showLoginPopup() {
            $('#wcs-login-popup').addClass('active');
            $('body').addClass('wcs-popup-open');
        }
        
        // Hide popup
        function hideLoginPopup() {
            $('#wcs-login-popup').removeClass('active');
            $('body').removeClass('wcs-popup-open');
        }
        
        // Switch tabs
        $('.wcs-tab-link').on('click', function() {
            var tab = $(this).data('tab');
            
            // Update tab links
            $('.wcs-tab-link').removeClass('active');
            $(this).addClass('active');
            
            // Update tab content
            $('.wcs-tab-pane').removeClass('active');
            $('#wcs-' + tab + '-tab').addClass('active');
        });
        
        // Close popup
        $('.wcs-popup-close, .wcs-popup-overlay').on('click', function() {
            hideLoginPopup();
        });
        
        // Handle login form submission
        $('#wcs-login-form').on('submit', function(e) {
            e.preventDefault();
            
            var username = $('#wcs-login-username').val();
            var password = $('#wcs-login-password').val();
            var remember = $('#wcs-login-remember').is(':checked');
            
            $.ajax({
                url: wcs_subscription.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcs_ajax_login',
                    username: username,
                    password: password,
                    remember: remember,
                    security: wcs_subscription.login_nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Reload page after successful login
                        window.location.reload();
                    } else {
                        alert(response.data.message);
                    }
                },
                error: function() {
                    alert('<?php _e('An error occurred. Please try again.', 'wp-course-subscription'); ?>');
                }
            });
        });
        
        // Handle registration form submission
        $('#wcs-register-form').on('submit', function(e) {
            e.preventDefault();
            
            var username = $('#wcs-register-username').val();
            var email = $('#wcs-register-email').val();
            var password = $('#wcs-register-password').val();
            
            $.ajax({
                url: wcs_subscription.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcs_ajax_register',
                    username: username,
                    email: email,
                    password: password,
                    security: wcs_subscription.register_nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Reload page after successful registration
                        window.location.reload();
                    } else {
                        alert(response.data.message);
                    }
                },
                error: function() {
                    alert('<?php _e('An error occurred. Please try again.', 'wp-course-subscription'); ?>');
                }
            });
        });
        
        // Show popup on restricted course links
        $('.wcs-course-restricted-link').on('click', function(e) {
            e.preventDefault();
            showLoginPopup();
        });
        
        // Show popup on page load if needed
        <?php if (isset($_GET['show_login']) && $_GET['show_login'] === '1') : ?>
        showLoginPopup();
        <?php endif; ?>
    });
</script>
