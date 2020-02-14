

define([
    'jquery',
    'underscore'
], function($, _){

    return function () {
        $("#payment-methods .item-title input").on('change', function() {
            var title = $(this).attr('title');
            var data  = {'tealium_event':'selected_payment_method', 'payment_method':title};
            var tealiumTag = window.utag;
            tealiumTag.link(data);
        });
    }
    
});