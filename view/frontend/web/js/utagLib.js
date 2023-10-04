
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

					
					console.log(utag_data);
					console.log('Before utag lib');
				  	
				  if(typeof window.utag == 'undefined')
					{
						var LibURL = jQuery.trim(jQuery('.utagLib').html());
						(function(a,b,c,d){
								a=LibURL;
								b=document;c='script';d=b.createElement(c);d.src=a;d.type='text/java'+c; 
								d.async=true;
								a=b.getElementsByTagName(c)[0];a.parentNode.insertBefore(d,a);
							})();


							/*
							
							
							var LibURL = jQuery.trim(jQuery('.utagLib').html());

							var LibURL = "magento.opensourcebrokers.ca/server_utag.js";
							console.log(LibURL);

							// Get the Magento form key
							var formKey = jQuery("[name='form_key']").val();

							console.log(formKey);

							// Create a script element with the form key in the URL
							const script = document.createElement('script');
							script.src = LibURL + '?form_key=' + formKey;
							script.type = 'text/javascript';
							document.head.append(script);*/
							
							





							
					}
			  }
					
			});
	
		console.log(utag_data);
		console.log('After utag lib');
   
		 
	
    }
	
});