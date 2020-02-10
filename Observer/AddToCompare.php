<?php
namespace Tealium\Tags\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Catalog\Model\ProductFactory;

class AddToCompare implements ObserverInterface
{


    protected $_customerSession;

    protected $_productFactory;

    public function __construct(
        CustomerSession $customerSession,
        ProductFactory $productFactory
    ) {
        $this->_customerSession = $customerSession;
        $this->_productFactory = $productFactory;
    }

    /**
     *
     * Add data to section array for custumer data use
     *
     */

    public function execute(Observer $observer)
    {
        
        $quoteItem = $observer->getData('product');
        $product_quantity = $quoteItem->getQty();
        if (is_null($product_quantity)) {
            $product_quantity = 1;
        }

        $product = $this->_productFactory->create();
        $product_id = $product->getIdBySku($quoteItem->getSku());

        $this->_customerSession->setTealiumCompareProductId($product_id);
        $this->_customerSession->setTealiumCompareProductQty($product_quantity);
        
        return $this;
    }
}
