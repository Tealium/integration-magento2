<?php
namespace Tealium\Tags\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\Request\Http;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;

class AddProduct implements ObserverInterface
{

    protected $_request;

    protected $_customerSession;

    protected $_checkoutSession;

    protected $_productRepository;

    protected $_objectManager;

    public function __construct(
        Http $request,
        CustomerSession $customerSession,
        ProductRepositoryInterface $productRepository,
        ObjectManagerInterface $objectManager,
        CheckoutSession $_checkoutSession
    ) {
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $_checkoutSession;
        $this->_request = $request;
        $this->_productRepository = $productRepository;
        $this->_objectManager = $objectManager;
    }

    /**
     *
     * Add data to section array for custumer data use
     *
     */

    public function execute(Observer $observer)
    {
        //get product from session
        $product_id=$this->_checkoutSession->getLastAddedProductId(true);
        $requestParamList = $this->_request->getParams();
        if (isset($requestParamList['super_attribute'])) {
            $product = $this->_productRepository->getById($product_id);
            $myProduct = $this->_objectManager->get('Magento\ConfigurableProduct\Model\Product\Type\Configurable')->getProductByAttributes($requestParamList['super_attribute'], $product);
            $product_id = $myProduct->getId();
        }

        $product_quantity = 1;
        if (isset($requestParamList['qty'])) {
            $product_quantity = $requestParamList['qty'];
        }
        //echo $requestParamList['qty']; exit;
        $this->_customerSession->setTealiumAddProductId($product_id);
        $this->_customerSession->setTealiumAddProductQty($product_quantity);
        
        return $this;
    }
}
