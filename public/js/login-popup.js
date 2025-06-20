/**
 * Login Popup JavaScript
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Course_Subscription
 */

(function($) {
    'use strict';

    // Initialize login popup functionality
    $(document).ready(function() {
        // Show login popup
        window.wcsShowLoginPopup = function() {
            $('#wcs-login-popup').addClass('active');
            $('body').addClass('wcs-popup-open');
        };

        // Close login popup
        $('.wcs-popup-close, .wcs-popup-overlay').on('click', function() {
            $('#wcs-login-popup').removeClass('active');
            $('body').removeClass('wcs-popup-open');
        });

        // Switch between login and register tabs
        $('.wcs-tab-link').on('click', function() {
            var tab = $(this).data('tab');
            
            // Update active tab link
            $('.wcs-tab-link').removeClass('active');
            $(this).addClass('active');
            
            // Update active tab content
            $('.wcs-tab-pane').removeClass('active');
            $('#wcs-' + tab + '-tab').addClass('active');
        });

        // Set nonce values from localized script
        $('#wcs-login-form input[name="security"]').val(wcs_ajax.login_nonce);
        $('#wcs-register-form input[name="security"]').val(wcs_ajax.register_nonce);
        
        // Set redirect URL if available
        if (wcs_ajax.redirect_to) {
            $('#wcs-login-form input[name="redirect"]').val(wcs_ajax.redirect_to);
            $('#wcs-register-form input[name="redirect"]').val(wcs_ajax.redirect_to);
        }

        // Handle login form submission
        $('#wcs-login-form').on('submit', function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $message = $form.find('.wcs-form-message');
            
            // Clear previous messages
            $message.removeClass('error success').html('');
            
            // Disable form during submission
            $form.find('button').prop('disabled', true).html('Logging in...');
            
            // Send AJAX request
            $.ajax({
                url: wcs_ajax.ajax_url,
                type: 'POST',
                data: $form.serialize(),
                success: function(response) {
                    if (response.success) {
                        $message.addClass('success').html(response.message);
                        
                        // Redirect after successful login
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        } else {
                            window.location.reload();
                        }
                    } else {
                        $message.addClass('error').html(response.message);
                        $form.find('button').prop('disabled', false).html('Login');
                    }
                },
                error: function() {
                    $message.addClass('error').html('An error occurred. Please try again.');
                    $form.find('button').prop('disabled', false).html('Login');
                }
            });
        });

        // Handle registration form submission
        $('#wcs-register-form').on('submit', function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $message = $form.find('.wcs-form-message');
            
            // Clear previous messages
            $message.removeClass('error success').html('');
            
            // Validate password match
            var password = $form.find('#wcs-register-password').val();
            var confirmPassword = $form.find('#wcs-register-confirm-password').val();
            
            if (password !== confirmPassword) {
                $message.addClass('error').html('Passwords do not match.');
                return;
            }
            
            // Disable form during submission
            $form.find('button').prop('disabled', true).html('Registering...');
            
            // Send AJAX request
            $.ajax({
                url: wcs_ajax.ajax_url,
                type: 'POST',
                data: $form.serialize(),
                success: function(response) {
                    if (response.success) {
                        $message.addClass('success').html(response.message);
                        
                        // Redirect after successful registration
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        } else {
                            window.location.reload();
                        }
                    } else {
                        $message.addClass('error').html(response.message);
                        $form.find('button').prop('disabled', false).html('Register');
                    }
                },
                error: function() {
                    $message.addClass('error').html('An error occurred. Please try again.');
                    $form.find('button').prop('disabled', false).html('Register');
                }
            });
        });

        // Show login popup when login/register button is clicked
        $('.wcs-login-button').on('click', function(e) {
            e.preventDefault();
            wcsShowLoginPopup();
        });
    });

})(jQuery);
