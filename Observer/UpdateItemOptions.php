<?php
namespace Tealium\Tags\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\Request\Http;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;

class UpdateItemOptions implements ObserverInterface
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

    public function execute(Observer $observer)
    {

        $requestParamList = $this->_request->getParams();

        if ($requestParamList['id']) {
            $this->_customerSession->setTealiumQty([$requestParamList['id']]);
        }

        
        return $this;
    }
}
