define([
    'jquery',
    'underscore'
], function($, _){

    return function () {
        if (document.querySelectorAll('body > div.page-wrapper > header > div.panel.wrapper > div > ul > li.customer-welcome > div > ul > li.authorization-link > a').length != 0) {
            $.get(location.protocol + '//' + location.host+'/tealium/cart/user', function(dataJson) {
            	$($('body > div.page-wrapper > header > div.panel.wrapper > div > ul > li.customer-welcome > div > ul > li.authorization-link > a')[0]).on('click', function() {
                    var data = JSON.parse(dataJson);
                    if (data['customer_id'][0]) {
                        var tealiumTag = window.utag;
                        data['tealium_event'] = 'user_logout';
                        tealiumTag.link(data);
                    }
                });
            });
        }

    }

});