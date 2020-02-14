<?php

namespace Tealium\Tags\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Tealium\Tags\Helper\Product;

class JsRemoveFromCart implements SectionSourceInterface
{

    protected $_checkoutSession;

    protected $_customerSession;

    protected $_productHelper;

    public function __construct(
        CustomerSession $customerSession,
        Product $productHelper
    ) {
        $this->_customerSession = $customerSession;
        $this->_productHelper = $productHelper;
    }

    public function getSectionData()
    {
        $product_id=$this->_customerSession->getTealiumRemoveProductId();
        $this->_customerSession->unsTealiumRemoveProductId();

        $qty = $this->_customerSession->getTealiumRemoveProductQty();
        $this->_customerSession->unsTealiumAddProductQty();

        $result = [];
;
        if ($this->_customerSession->getTealiumEmptyCart()) {
            $result ['cart_empty'] = true;
            $this->_customerSession->unsTealiumEmptyCart();
        }
        if ($product_id) {
            $result['data'] = $this->_productHelper->getProductData($product_id);
            $result['data']['product_quantity'] = [(string)$qty];
            $result['data']['product_id'] = [(string)$product_id];
            $result['data']['tealium_event'] = 'cart_remove';
        }

        
        return $result;
    }
}
