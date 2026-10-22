<?php
namespace Tealium\Tags\Controller\Cart;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Cart;
use Tealium\Tags\Helper\Product;
use Magento\Framework\Controller\ResultFactory;

/**
 * Cart contents for the cart_quickview event (see js/miniCart.js)
 */
class Index extends Action
{
    protected $_cart;

    protected $_productHelper;

    public function __construct(
        Context $context,
        Cart $cart,
        Product $productHelper
    ) {
        $this->_cart = $cart;
        $this->_productHelper = $productHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $productIds = [];
        $quantities = [];
        foreach ($this->_cart->getQuote()->getAllVisibleItems() as $item) {
            $productIds[] = $item->getProductId();
            $quantities[] = $item->getQty();
        }

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData(['data' => $this->_productHelper->getItemsData($productIds, $quantities)]);
        return $resultJson;
    }
}
