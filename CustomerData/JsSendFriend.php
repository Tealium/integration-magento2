<?php

namespace Tealium\Tags\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Tealium\Tags\Helper\Product;

class JsSendFriend implements SectionSourceInterface
{

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
        $product_id=$this->_customerSession->getTealiumSendFriend();
        $this->_customerSession->unsTealiumSendFriend();

        $result = [];

        if ($product_id) {
            $result = ['data'=>$this->_productHelper->getProductData($product_id)];
            $result['data']['product_quantity'] = [1];
            $result['data']['product_id'] = [$product_id];
            $result['data']['tealium_event'] = 'add_to_wishlist';
        }
        
        return $result;
    }
}


