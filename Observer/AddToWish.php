<?php
namespace Tealium\Tags\Observer;

use Magento\Framework\Event\Observer;
use Magento\Customer\Model\Session as CustomerSession;

class AddToWish extends AbstractObserver
{

    protected $_customerSession;

    public function __construct(
        CustomerSession $customerSession
    ) {
        $this->_customerSession = $customerSession;
    }

    /**
     *
     * Add data to section array for custumer data use
     *
     */

    protected function track(Observer $observer)
    {
        
        $productItem = $observer->getData('product');

        $product_id = $productItem->getId();
        $product_quantity = 1;

        $this->_customerSession->setTealiumAddToWishQty($product_quantity);
        $this->_customerSession->setTealiumAddToWishId($product_id);
        
        return $this;
    }
}
