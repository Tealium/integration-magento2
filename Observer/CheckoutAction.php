<?php
namespace Tealium\Tags\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Customer\Model\Session as CustomerSession;

class AddToWish implements ObserverInterface
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

    public function execute(Observer $observer) 
    {	
        
        $order_ids = $observer->getData('order_ids');

        $this->_customerSession->setTealiumCheckout($order_ids);
        
        return $this;
    }
}