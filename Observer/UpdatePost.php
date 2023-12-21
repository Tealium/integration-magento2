<?php
namespace Tealium\Tags\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\Request\Http;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;

class UpdatePost implements ObserverInterface
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

        $requestParamList=$this->_request->getParams();
        $quoteList=$this->_checkoutSession->getQuote()->getAllVisibleItems();
        $result = [];

        //product search whose quantity has changed

        foreach ($requestParamList['cart'] as $id => $itemData) {
            foreach ($quoteList as $quoteItem) {
                if ($quoteItem->getItemId() == $id and $quoteItem->getQty() != $itemData['qty']) {
                    array_push($result, $id);
                }
            }
        }

        $this->_customerSession->setTealiumQty($result);
        
        return $this;
    }
}
