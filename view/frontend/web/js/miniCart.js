

define([
    'jquery',
    'underscore'
], function($, _){

    return function () {
        $(".action.showcart").on('click', function() {
            if ($(this).hasClass('active')) {
            	$.get(BASE_URL+'tealium/cart/index', function(data) {
            		var dataObject = data;
            		if (dataObject['data']['product_id'].length > 0) {
            			var tealiumTag = window.utag;
            			dataObject['data']['tealium_event'] = 'cart_quickview';
            			 tealiumTag.link(dataObject['data']);
            		}
            	});
            }
        });

    }

});