

define([
    'jquery',
    'underscore'
], function($, _){

    return function () {
        $(".action.showcart").on('click', function() {
            if ($(this).hasClass('active')) {
            	$.get(location.protocol + '//' + location.host+'/tealium/cart/index', function(data) {
            		var dataObject = JSON.parse(data);
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