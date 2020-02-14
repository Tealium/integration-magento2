

define([
    'Magento_Customer/js/customer-data',
    'underscore',
	'Tealium_Tags/js/utagLib'
/*	'utagLib1' */
], function(customerData, _){
    'use strict';

    function sendData(tealiumTag, _dataObject) {
        if(_.isObject(_dataObject) && _.isObject(tealiumTag) && _.has(_dataObject, 'data')){
            tealiumTag.link(_dataObject.data);
        }
    }

    return function (options) {
	//	alert(window.utag);
//	alert('test');
		console.log('window.utag');
		console.log(window.utag);
        var tealiumTag = window.utag;

        var dataObjectAdd = customerData.get("tealium-tags-add-to-cart");

        dataObjectAdd.subscribe(function (_dataObject) {
            sendData(tealiumTag, _dataObject);
        }, this);

        if(!_.contains(customerData.getExpiredKeys(), "tealium-tags-add-to-cart")){
            sendData(tealiumTag, dataObjectAdd);
        }

        var dataObjectRemove = customerData.get("tealium-tags-remove-from-cart");

        dataObjectRemove.subscribe(function (_dataObject) {
            if ('cart_empty' in _dataObject ) {
                sendData(tealiumTag, {'data':{'tealium_event':"cart_empty"}});
            }
            sendData(tealiumTag, _dataObject);
        }, this);

        if(!_.contains(customerData.getExpiredKeys(), "tealium-tags-remove-from-cart")){
            if ('cart_empty' in dataObjectRemove ) {
                sendData(tealiumTag, {'data':{'tealium_event':"cart_empty"}});
            }
            sendData(tealiumTag, dataObjectRemove);
        }

        var dataObjectCompare = customerData.get("tealium-tags-add-to-compare");

        dataObjectCompare.subscribe(function (_dataObject) {
            sendData(tealiumTag, _dataObject);
        }, this);

        if(!_.contains(customerData.getExpiredKeys(), "tealium-tags-add-to-compare")){
            sendData(tealiumTag, dataObjectCompare);
        }

        var dataObjectCreate = customerData.get("tealium-tags-customer-account-create");

        dataObjectCreate.subscribe(function (_dataObject) {
            sendData(tealiumTag, _dataObject);
        }, this);

        if(!_.contains(customerData.getExpiredKeys(), "tealium-tags-customer-account-create")){
            sendData(tealiumTag, dataObjectCreate);
        }

        var dataObjectLogout = customerData.get("tealium-tags-customer-logout");

        dataObjectLogout.subscribe(function (_dataObject) {
            sendData(tealiumTag, _dataObject);
        }, this);

        if(!_.contains(customerData.getExpiredKeys(), "tealium-tags-customer-logout")){
            sendData(tealiumTag, dataObjectLogout);
        }

        var dataObjectLogin = customerData.get("tealium-tags-customer-login");

        dataObjectLogin.subscribe(function (_dataObject) {
            sendData(tealiumTag, _dataObject);
        }, this);

        if(!_.contains(customerData.getExpiredKeys(), "tealium-tags-customer-login")){
            sendData(tealiumTag, dataObjectLogin);
        }

        var dataObjectWish = customerData.get("tealium-tags-add-to-wish");

        dataObjectWish.subscribe(function (_dataObject) {
            sendData(tealiumTag, _dataObject);
        }, this);

        if(!_.contains(customerData.getExpiredKeys(), "tealium-tags-add-to-wish")){
            sendData(tealiumTag, dataObjectWish);
        }

        var dataObjectCoupon = customerData.get("tealium-tags-coupons");

        dataObjectCoupon.subscribe(function (_dataObject) {
            sendData(tealiumTag, _dataObject);
        }, this);

        if(!_.contains(customerData.getExpiredKeys(), "tealium-tags-coupons")){
            sendData(tealiumTag, dataObjectCoupon);
        }

        var dataObjectOrder = customerData.get("tealium-tags-save-order");

        dataObjectOrder.subscribe(function (_dataObject) {
            sendData(tealiumTag, _dataObject);
        }, this);

        if(!_.contains(customerData.getExpiredKeys(), "tealium-tags-save-order")){
            sendData(tealiumTag, dataObjectOrder);
        }

        var dataObjectQty = customerData.get("tealium-tags-update-qty");

        dataObjectQty.subscribe(function (_dataObject) {
            sendData(tealiumTag, _dataObject);
        }, this);

        if(!_.contains(customerData.getExpiredKeys(), "tealium-tags-update-qty")){
            sendData(tealiumTag, dataObjectQty);
        }
    }

});