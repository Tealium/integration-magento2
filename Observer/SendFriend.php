<?php
namespace Tealium\Tags\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Request\Http;

class SendFriend implements ObserverInterface
{

    protected $_customerSession;

    protected $_request;

	public function __construct(
        CustomerSession $customerSession,
        Http $request
    ) {
        $this->_customerSession = $customerSession;
        $this->_request = $request;
	}

    /**
     *
     * Add data to section array for custumer data use
     *
     */

    public function execute(Observer $observer) 
    {	
        $request = $this->_request->getParams();
        $product_id = $request['id'];

        $this->_customerSession->setTealiumSendFriend($product_id);

        return $this;
    }
}