(function($) {
    // Custom code here
    $(function () {
        if(TSCheckoutConfig.verification_method==="modal") {
            $(document.body).on('checkout_error', function () {
                var error_text = $('.woocommerce-error').find('li');
                error_text.each(function (idx, li) {
                    let eText = $(li).text().trim();
                    if (eText === 'Please complete the verification requirements prior to paying') {
                        $('.trust_btn').trigger('click');
                    }
                    // and the rest of your code
                });
            });
        }
    });
})(jQuery);