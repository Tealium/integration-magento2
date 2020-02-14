<?php
namespace Tealium\Tags\Controller\Cart;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Checkout\Model\Cart;
use Tealium\Tags\Helper\Product;
use Magento\Framework\Controller\ResultFactory;

class Quote extends Action
{
    protected $_pageFactory;

    protected $_cart;

    protected $_productHelper;

    protected $_resultJsonFactory;

    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        Cart $cart,
        Product $productHelper
    ) {
        $this->_pageFactory = $pageFactory;
        $this->_cart = $cart;
        $this->_productHelper = $productHelper;
        return parent::__construct($context);
    }

    public function execute()
    {
		$ItemsQty = $this->_cart->getQuote()->getItemsQty();
		$GrandTotal =$this->_cart->getQuote()->getGrandTotal();
      
       
		 $result['cart_total_items'] = number_format($ItemsQty, 2, ".", "") ? : '';
		 $result['cart_total_value'] = number_format($GrandTotal, 2, ".", "") ? : '';
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result);
        return $resultJson;
    }
}
