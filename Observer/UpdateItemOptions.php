<?php
namespace Tealium\Tags\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\App\Request\Http;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;

class UpdateItemOptions extends AbstractObserver
{

    protected $_request;

    protected $_customerSession;

    protected $_checkoutSession;

    public function __construct(
        Http $request,
        CustomerSession $customerSession,
        CheckoutSession $_checkoutSession
    ) {
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $_checkoutSession;
        $this->_request = $request;
    }

    /**
     *
     * Add data to section array for custumer data use
     *
     */

    protected function track(Observer $observer)
    {

        $id = $this->_request->getParam('id');
        if ($id) {
            $this->_customerSession->setTealiumQty([$id]);
        }

        
        return $this;
    }
}
