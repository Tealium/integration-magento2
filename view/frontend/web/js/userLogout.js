define([
    'jquery',
    'Magento_Customer/js/customer-data'
], function ($, customerData) {
    'use strict';

    var COPIED_FIELDS = ['page_type', 'page_name', 'site_currency', 'site_region'];

    return function () {
        // the customer details come from a private section that is already in
        // local storage, so nothing is requested from the server per page
        $(document).on('click', 'a[href*="customer/account/logout"]', function () {
            var customer = customerData.get('tealium-tags-customer')(),
                utag = window.utag,
                data;

            if (!customer || !customer.customer_id || !utag || typeof utag.link !== 'function') {
                return;
            }

            data = {
                'customer_id': customer.customer_id,
                'customer_type': customer.customer_type,
                'customer_email': customer.customer_email,
                'tealium_event': 'user_logout'
            };

            $.each(COPIED_FIELDS, function (i, field) {
                if (window.utag_data && typeof window.utag_data[field] !== 'undefined') {
                    data[field] = window.utag_data[field];
                }
            });

            utag.link(data);
        });
    };
});
