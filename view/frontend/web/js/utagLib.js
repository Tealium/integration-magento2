
define([
    'jquery',
    'underscore',
	'require',
	'domReady!'
], function($, _, require){
	
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

					
					
				  	
				  if(typeof window.utag == 'undefined')
					{
						
						var LibURL = utag_jsurl;
						(function(a,b,c,d){
								a=LibURL;
								b=document;c='script';d=b.createElement(c);d.src=a;d.type='text/java'+c; 
								d.async=true;
								a=b.getElementsByTagName(c)[0];a.parentNode.insertBefore(d,a);
							})();

							
					}
			  }
					
			});
	
		
   
		 
	
    }
	
});