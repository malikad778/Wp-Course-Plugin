jQuery(document).ready(function($) {
    $('.wcs-select-plan-button').on('click', function() {
        var planId = $(this).data('plan-id');
        var $button = $(this);

        $button.prop('disabled', true).text('جاري المعالجة...');

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
                    $button.prop('disabled', false).text('اختر الخطة');
                }
            },
            error: function() {
                alert('حدث خطأ. يرجى المحاولة مرة أخرى.');
                $button.prop('disabled', false).text('اختر الخطة');
            }
        });
    });
});