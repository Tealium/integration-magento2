define([
    'jquery',
    'underscore'
], function($, _){
	var new_url = BASE_URL;
    return function () {
        if (document.querySelectorAll('body > div.page-wrapper > header > div.panel.wrapper > div > ul > li.customer-welcome > div > ul > li.authorization-link > a').length != 0) {
			var linkUrl = new_url.replace(/\/$/, "");
            $.get(linkUrl+'/tealium/cart/user', function(dataJson) { 
            	$($('body > div.page-wrapper > header > div.panel.wrapper > div > ul > li.customer-welcome > div > ul > li.authorization-link > a')[0]).on('click', function() {
                    var data = dataJson;
                    if (data['customer_id'][0]) {
                        var tealiumTag = window.utag;
                        data['tealium_event'] = 'user_logout';
						if(typeof(utag_data['page_type']) != 'undefined')
						{
							data['page_type'] = utag_data['page_type'];
						}
						if(typeof(utag_data['page_name']) != 'undefined')
						{
							data['page_name'] = utag_data['page_name'];
						}
						if(typeof(utag_data['site_currency']) != 'undefined')
						{
							data['site_currency'] = utag_data['site_currency'];
						}
						if(typeof(utag_data['site_region']) != 'undefined')
						{
							data['site_region'] = utag_data['site_region'];
						}
                        tealiumTag.link(data);
                    }
                });
            });
        }

    }
});