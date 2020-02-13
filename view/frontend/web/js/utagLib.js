
define([
    'jquery',
    'underscore',
	'require',
	'domReady!'
], function($, _, require){
	/*
		$.get(BASE_URL+'tealium/cart/quote', function(data) {
            		var dataObject = data;
            		if (dataObject['cart_total_items'].length > 0) {
						utag_data.cart_total_items = dataObject['cart_total_items'];
            		}
					if (dataObject['cart_total_value'].length > 0) {
						utag_data.cart_total_value = dataObject['cart_total_value'];
            		}
		});
		*/
	
    return function (options) {
		$.ajax({
			  url: BASE_URL+'tealium/cart/quote',
			  dataType: 'json',
			  'async': false,
			  success: function(data){
			  		var dataObject = data;
            		if (dataObject['cart_total_items'].length > 0) {
						utag_data.cart_total_items = dataObject['cart_total_items'];
            		}
					if (dataObject['cart_total_value'].length > 0) {
						utag_data.cart_total_value = dataObject['cart_total_value'];
            		}
					console.log(utag_data);
					console.log('bbb utag lib');
				  	var LibURL = $.trim($('.utagLib').html());
				  if(typeof window.utag == 'undefined')
					{
						(function(a,b,c,d){
								a=LibURL;
								b=document;c='script';d=b.createElement(c);d.src=a;d.type='text/java'+c; 
								d.async=true;
								a=b.getElementsByTagName(c)[0];a.parentNode.insertBefore(d,a);
							})();
					}
			  }
					
			});
	/*
      	$.get(BASE_URL+'tealium/cart/quote', function(data) {
            		var dataObject = data;
            		if (dataObject['cart_total_items'].length > 0) {
						utag_data.cart_total_items = dataObject['cart_total_items'];
            		}
					if (dataObject['cart_total_value'].length > 0) {
						utag_data.cart_total_value = dataObject['cart_total_value'];
            		}
					console.log(utag_data);
		console.log('bbb utag lib');
		});
		*/
		console.log(utag_data);
		console.log('aaaaa utag lib');
   
		 
	//	alert('hello');
    }
	
});