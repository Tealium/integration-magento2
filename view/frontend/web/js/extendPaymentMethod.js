define([
    'jquery',
    'require'
], function ($, require) {
    'use strict';

    return function () {
        // the checkout is rendered by knockout after page load, so the
        // selected payment method is read from the quote model
        if (!window.checkoutConfig) {
            return;
        }

        require(['Magento_Checkout/js/model/quote'], function (quote) {
            quote.paymentMethod.subscribe(function (method) {
                var title, utag = window.utag;

                if (!method || !method.method || !utag || typeof utag.link !== 'function') {
                    return;
                }

                title = $('input[name="payment[method]"][value="' + method.method + '"]').attr('title') || method.method;
                utag.link({'tealium_event': 'selected_payment_method', 'payment_method': title});
            });
        });
    };
});
