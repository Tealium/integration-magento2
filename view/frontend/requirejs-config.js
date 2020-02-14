var config = {
    map: {
        '*': {
			'TealiumTagsUtagLib': 'Tealium_Tags/js/utagLib',
            'TealiumTagsUtagJs': 'Tealium_Tags/js/myUtag',
            'TealiumTagsMiniCart': 'Tealium_Tags/js/miniCart',
            'TealiumTagsPaymentMethod': 'Tealium_Tags/js/extendPaymentMethod',
            'TealiumTagsUserLogout': 'Tealium_Tags/js/userLogout'
        }
    },
	/*
	 paths: {
    'utagLib1': 'http://tags.tiqcdn.com/utag/jaredtest/main/dev/utag'
  },
  */
	 shim: {
		 'Tealium_Tags/js/myUtag': {
          /*  deps: ['Tealium_Tags/js/utagLib', 'utagLib1'] */
			 deps: ['Tealium_Tags/js/utagLib']
        }
		 /*
		 ,
		'utagLib1': {
            deps: ['Tealium_Tags/js/utagLib'],
			exports: 'utagLib1'
        }
		*/
    },
	/*
	
	 shim: {
		 'utagLib': {
            deps: ['Tealium_Tags/js/utagLib'],
			exports: 'utagLib'
        }
    }
	*/
};
