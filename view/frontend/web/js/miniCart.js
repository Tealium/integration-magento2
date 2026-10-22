define([
    'jquery'
], function ($) {
    'use strict';

    return function () {
        $(document).on('click', '.action.showcart', function () {
            if (!$(this).hasClass('active')) {
                return;
            }
            $.get(BASE_URL + 'tealium/cart/index', function (dataObject) {
                var utag = window.utag;

                if (!dataObject || !dataObject.data || !dataObject.data.product_id ||
                    !dataObject.data.product_id.length || !utag || typeof utag.link !== 'function') {
                    return;
                }
                dataObject.data.tealium_event = 'cart_quickview';
                utag.link(dataObject.data);
            });
        });
    };
});
