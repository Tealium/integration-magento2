define([
    'jquery',
    'underscore',
    'Magento_Customer/js/customer-data'
], function ($, _, customerData) {
    'use strict';

    /**
     * How long to wait for fresh cart data or for the data layer of an async
     * external script before utag.js is loaded anyway.
     */
    var WAIT_TIMEOUT = 2000,
        requested = false;

    /**
     * Copy the cart totals from the private "cart" section into utag_data.
     * The page itself may come from the full page cache, so they cannot be
     * rendered server-side.
     *
     * @param {Object} cart
     */
    function applyCartTotals(cart) {
        var totals = cart && cart.tealium;

        if (!totals || !window.utag_data) {
            return;
        }

        if (totals.cart_total_items) {
            window.utag_data.cart_total_items = totals.cart_total_items;
        }

        if (totals.cart_total_value) {
            window.utag_data.cart_total_value = totals.cart_total_value;
        }
    }

    /**
     * Inject utag.js once, asynchronously.
     *
     * @param {String} url
     */
    function injectUtag(url) {
        var script, first;

        if (requested || typeof window.utag !== 'undefined' || !url) {
            return;
        }
        requested = true;

        script = document.createElement('script');
        script.src = url;
        script.type = 'text/javascript';
        script.async = true;
        first = document.getElementsByTagName('script')[0];
        first.parentNode.insertBefore(script, first);
    }

    /**
     * Call back once utag_data exists (it is defined later than this module
     * when an async external script is used), or after the timeout.
     *
     * @param {Function} callback
     */
    function whenDataLayerReady(callback) {
        var started = Date.now(),
            timer;

        if (window.utag_data) {
            callback();

            return;
        }

        timer = setInterval(function () {
            if (window.utag_data || Date.now() - started > WAIT_TIMEOUT) {
                clearInterval(timer);
                callback();
            }
        }, 50);
    }

    /**
     * @param {String} sectionName
     * @return {Boolean}
     */
    function isExpired(sectionName) {
        try {
            return _.contains(customerData.getExpiredSectionNames(), sectionName);
        } catch (e) {
            return false;
        }
    }

    return function (options) {
        var url = options && options.utagUrl || window.utag_jsurl,
            started = false;

        /**
         * Start at most once
         */
        function start() {
            if (!started) {
                started = true;
                whenDataLayerReady(loadWithCart);
            }
        }

        // customer data must be initialised before its expiry can be checked
        $.when(
            customerData.getInitCustomerData ? customerData.getInitCustomerData() : true
        ).done(start);
        setTimeout(start, WAIT_TIMEOUT);

        /**
         * Load utag.js once the cart totals are known
         */
        function loadWithCart() {
            var cart = customerData.get('cart'),
                done = false,
                subscription,

                /**
                 * @param {Object} data
                 */
                finish = function (data) {
                    if (done) {
                        return;
                    }
                    done = true;

                    if (subscription) {
                        subscription.dispose();
                    }
                    applyCartTotals(data);
                    injectUtag(url || window.utag_jsurl);
                };

            if (!isExpired('cart')) {
                // stored cart data is current (or there is no cart at all)
                finish(cart());

                return;
            }

            // the cart section is being reloaded, wait for it, but never
            // hold back utag.js for long
            subscription = cart.subscribe(finish);
            setTimeout(function () {
                finish(cart());
            }, WAIT_TIMEOUT);
        }
    };
});
