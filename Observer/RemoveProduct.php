<?php
namespace Tealium\Tags\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Catalog\Model\ProductFactory;
use Magento\Checkout\Model\Cart;

class RemoveProduct implements ObserverInterface
{

    //protected $_request;

    protected $_customerSession;

    protected $_productFactory;

    protected $_cart;

	public function __construct(
        //Http $request,
        ProductFactory $productFactory,
        CustomerSession $customerSession,
        Cart $cart
    ) {
        $this->_customerSession = $customerSession;
        $this->_productFactory = $productFactory;
        $this->_cart = $cart;
       // $this->_request = $request;
	}

    /**
     *
     * Add data to section array for custumer data use
     *
     */

    public function execute(Observer $observer) 
    {	
        
        $quoteItem = $observer->getData('quote_item');
        $product_quantity = $quoteItem->getQty();

        $product = $this->_productFactory->create();
        $product_id = $product->getIdBySku($quoteItem->getSku());

        $this->_customerSession->setTealiumRemoveProductQty($product_quantity);
        $this->_customerSession->setTealiumRemoveProductId($product_id);

        //check cart - if empty then set the flag to send the event

        $itemArray = $this->_cart->getQuote()->getAllVisibleItems();

        if (empty($itemArray)) {
            $this->_customerSession->setTealiumEmptyCart(1);
        }
        
        return $this;
    }
}