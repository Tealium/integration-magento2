<?php
namespace Tealium\Tags\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Request\Http;

class MultishippingQtyUpdate implements ObserverInterface
{

    protected $_customerSession;

    protected $_request;

    protected $_checkoutSession;

    public function __construct(
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        Http $request
    ) {
        $this->_customerSession = $customerSession;
        $this->_request = $request;
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     *
     * Add data to section array for custumer data use
     *
     */

    public function execute(Observer $observer)
    {

        $requestParamList=$this->_request->getParams();
        $dataArray = [];
        $result = [];

        //prepere data for checking

        foreach ($requestParamList['ship'] as $shipItem) {
            foreach ($shipItem as $id => $itemData) {
                if (array_key_exists($id, $dataArray)) {
                    $dataArray[$id]['qty'] = $dataArray[$id]['qty']+$itemData['qty'];
                } else {
                    $dataArray[$id] = $itemData;
                }
            }
        }

        //Product search with unchanged quantity

        $quoteList=$this->_checkoutSession->getQuote()->getAllVisibleItems();
        foreach ($dataArray as $id => $itemData) {
            foreach ($quoteList as $quoteItem) {
                //echo $quoteItem->getItemId().'  '.$quoteItem->getQty().'   ';
                if ($quoteItem->getItemId() == $id and $quoteItem->getQty() != $itemData['qty']) {
                    array_push($result, $id);
                }
            }
        }
        
        $this->_customerSession->setTealiumQty($result);
        
        return $this;
    }
}
