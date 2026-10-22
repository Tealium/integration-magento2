define([
    'Magento_Customer/js/customer-data',
    'underscore',
    'Tealium_Tags/js/utagLib'
], function (customerData, _) {
    'use strict';

    /**
     * Private sections that carry one-shot Tealium events
     */
    var SECTIONS = [
            'tealium-tags-add-to-cart',
            'tealium-tags-remove-from-cart',
            'tealium-tags-add-to-compare',
            'tealium-tags-customer-account-create',
            'tealium-tags-customer-login',
            'tealium-tags-newsletter-signup',
            'tealium-tags-add-to-wish',
            'tealium-tags-coupons',
            'tealium-tags-save-order',
            'tealium-tags-update-qty',
            'tealium-tags-save-review'
        ],
        STORAGE_KEY = 'tealium-tags-sent-events',
        QUEUE_TIMEOUT = 30000,
        queue = [],
        timer = null,
        sent = null,
        firstRun = false;

    /**
     * Send every queued event if utag.js has loaded
     *
     * @return {Boolean}
     */
    function flush() {
        var utag = window.utag;

        if (!utag || typeof utag.link !== 'function') {
            return false;
        }

        while (queue.length) {
            utag.link(queue.shift());
        }

        return true;
    }

    /**
     * utag.js loads asynchronously, so events are queued until it is ready
     *
     * @param {Object} data
     */
    function send(data) {
        var started = Date.now();

        queue.push(data);

        if (flush() || timer) {
            return;
        }

        timer = setInterval(function () {
            if (flush() || Date.now() - started > QUEUE_TIMEOUT) {
                clearInterval(timer);
                timer = null;
            }
        }, 200);
    }

    /**
     * Events already sent, per section. Section data stays in local storage
     * after it has been sent, so without this every page view would send it
     * again.
     *
     * @return {Object}
     */
    function getSent() {
        if (sent === null) {
            try {
                sent = JSON.parse(window.localStorage.getItem(STORAGE_KEY));
            } catch (e) {
                sent = null;
            }
            firstRun = !_.isObject(sent);
            sent = firstRun ? {} : sent;
        }

        return sent;
    }

    /**
     * @param {String} sectionName
     * @param {String} signature
     */
    function markSent(sectionName, signature) {
        getSent()[sectionName] = signature;

        try {
            window.localStorage.setItem(STORAGE_KEY, JSON.stringify(sent));
        } catch (e) {
            // storage unavailable, events are still de-duplicated for this page
        }
    }

    /**
     * Send the event held by a section, once
     *
     * @param {String} sectionName
     * @param {Object} sectionData
     * @param {Boolean} isStored data was already in local storage on page load
     */
    function handle(sectionName, sectionData, isStored) {
        var signature;

        if (!_.isObject(sectionData) || !(_.has(sectionData, 'data') || _.has(sectionData, 'cart_empty'))) {
            return;
        }

        signature = String(sectionData['data_id']) + ':' + JSON.stringify(_.omit(sectionData, 'data_id'));

        if (getSent()[sectionName] === signature) {
            return;
        }
        markSent(sectionName, signature);

        // the first time this runs, stored data may be from events sent long
        // ago (before de-duplication existed), so it is only remembered
        if (isStored && firstRun) {
            return;
        }

        if (sectionName === 'tealium-tags-remove-from-cart' && sectionData['cart_empty']) {
            send({'tealium_event': 'cart_remove'});
        }

        if (_.isObject(sectionData.data)) {
            send(sectionData.data);
        }
    }

    return function () {
        _.each(SECTIONS, function (sectionName) {
            var section = customerData.get(sectionName);

            section.subscribe(function (sectionData) {
                handle(sectionName, sectionData, false);
            });

            // data that arrived before this module subscribed
            handle(sectionName, section(), true);
        });
    };
});
