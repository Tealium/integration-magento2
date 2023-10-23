<?php
namespace Tealium\Tags\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\Request\Http;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable; // Import the Configurable class

class AddProduct implements ObserverInterface
{
    protected $request;
    protected $customerSession;
    protected $checkoutSession;
    protected $productRepository;
    protected $configurableProduct; // Add a property for the Configurable class

    public function __construct(
        Http $request,
        CustomerSession $customerSession,
        ProductRepositoryInterface $productRepository,
        Configurable $configurableProduct, // Inject the Configurable class
        CheckoutSession $checkoutSession
    ) {
        $this->request = $request;
        $this->customerSession = $customerSession;
        $this->productRepository = $productRepository;
        $this->configurableProduct = $configurableProduct; // Assign the injected instance
        $this->checkoutSession = $checkoutSession;
    }

    public function execute(Observer $observer)
    {
        $product_id = $this->checkoutSession->getLastAddedProductId(true);
        $requestParamList = $this->request->getParams();

        if (isset($requestParamList['super_attribute'])) {
            $product = $this->productRepository->getById($product_id);
            $myProduct = $this->configurableProduct->getProductByAttributes($requestParamList['super_attribute'], $product); // Use the injected instance
            $product_id = $myProduct->getId();
        }

        $product_quantity = 1;
        if (isset($requestParamList['qty'])) {
            $product_quantity = $requestParamList['qty'];
        }

        if (isset($requestParamList['super_group'])) {
            $product_quantity = [];
            $product_id = [];
            foreach ($requestParamList['super_group'] as $pid => $qty) {
                if (intval($qty) > 0) {
                    $product_quantity[] = $qty;
                    $product_id[] = $pid;
                }
            }
        }

        $this->customerSession->setTealiumAddProductId($product_id);
        $this->customerSession->setTealiumAddProductQty($product_quantity);

        return $this;
    }
}
