<?php
namespace Tealium\Tags\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Request\Http;
use Magento\Checkout\Model\Cart;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Quote\Model\Quote\Item;
use Magento\Catalog\Model\ProductRepository;

class QtyUpdate implements ObserverInterface
{

    protected $_customerSession;

    protected $_request;

    protected $_product;

    protected $_productConfigurable;

    protected $_quoteItem;

    protected $_productRepository;

    public function __construct(
        CustomerSession $customerSession,
        Cart $cart,
        Product $product,
        Configurable $productConfigurable,
        Item $quoteItem,
        Http $request,
        ProductRepository $productRepository
    ) {
        $this->_customerSession = $customerSession;
        $this->_request = $request;
        $this->_product = $product;
        $this->_productConfigurable = $productConfigurable;
        $this->_quoteItem = $quoteItem;
        $this->_cart = $cart;
        $this->_productRepository = $productRepository;
    }

    /**
     *
     * Add data to section array for custumer data use
     *
     */

    public function execute(Observer $observer)
    {
        
        $requestParamList = $this->_request->getParams();
        if (array_key_exists('item_id', $requestParamList)) {
            $product = $this->_quoteItem->load($requestParamList['item_id']);
            foreach ($product->getOptions() as $option) {
                if ($option) {
                    $optionStr = $option->getValue();
                    if ($optionStr && (strpos($optionStr, 'super_attribute') !== false)) {
                        $option_list = json_decode($optionStr);
                        $product = $this->_productConfigurable->getProductByAttributes((array)$option_list->super_attribute, $product);
                    }
                }
            }
            //$realProduct = $this->_productRepository->get($product->getSku());
            $this->_customerSession->setTealiumQty([$product->getId()]);
            //$this->_customerSession->setTealiumQtyReal([$realProduct->getId()]);
        }
        
        return $this;
    }
}
