<?php
namespace Tealium\Tags\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\App\Request\Http;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;

class UpdatePost extends AbstractObserver
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

        $requestParamList=$this->_request->getParams();
        $quoteList=$this->_checkoutSession->getQuote()->getAllVisibleItems();
        $result = [];

        //product search whose quantity has changed

        $cart = isset($requestParamList['cart']) && is_array($requestParamList['cart']) ? $requestParamList['cart'] : [];
        foreach ($cart as $id => $itemData) {
            if (!is_array($itemData) || !isset($itemData['qty'])) {
                continue;
            }
            foreach ($quoteList as $quoteItem) {
                if ($quoteItem->getItemId() == $id && $quoteItem->getQty() != $itemData['qty']) {
                    array_push($result, $id);
                }
            }
        }

        $this->_customerSession->setTealiumQty($result);
        
        return $this;
    }
}
