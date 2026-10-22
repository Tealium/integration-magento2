<?php
namespace Tealium\Tags\Observer;

use Magento\Framework\Event\Observer;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Request\Http;

class SendFriend extends AbstractObserver
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

    protected function track(Observer $observer)
    {
        $product_id = $this->_request->getParam('id');
        if ($product_id) {
            $this->_customerSession->setTealiumSendFriend($product_id);
        }

        return $this;
    }
}
