<?php
namespace Tealium\Tags\Observer;

use Magento\Framework\Event\Observer;
use Magento\Customer\Model\Session as CustomerSession;

class CheckoutAction extends AbstractObserver
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
        
        $order_ids = $observer->getData('order_ids');

        $this->_customerSession->setTealiumCheckout($order_ids);
        return $this;
    }
}
