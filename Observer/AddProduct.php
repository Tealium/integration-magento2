<?php
namespace Tealium\Tags\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\App\Request\Http;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class AddProduct extends AbstractObserver
{
    protected $request;
    protected $customerSession;
    protected $checkoutSession;
    protected $productRepository;
    protected $configurableProduct;

    public function __construct(
        Http $request,
        CustomerSession $customerSession,
        ProductRepositoryInterface $productRepository,
        Configurable $configurableProduct,
        CheckoutSession $checkoutSession
    ) {
        $this->request = $request;
        $this->customerSession = $customerSession;
        $this->productRepository = $productRepository;
        $this->configurableProduct = $configurableProduct;
        $this->checkoutSession = $checkoutSession;
    }

    protected function track(Observer $observer)
    {
        $product_id = $this->checkoutSession->getLastAddedProductId(true);
        $requestParamList = $this->request->getParams();

        if ($product_id && isset($requestParamList['super_attribute']) && is_array($requestParamList['super_attribute'])) {
            $product = $this->productRepository->getById($product_id);
            $myProduct = $this->configurableProduct->getProductByAttributes($requestParamList['super_attribute'], $product);
            // keep the configurable product id when the child can't be resolved
            if ($myProduct && $myProduct->getId()) {
                $product_id = $myProduct->getId();
            }
        }

        $product_quantity = 1;
        if (isset($requestParamList['qty'])) {
            $product_quantity = $requestParamList['qty'];
        }

        if (isset($requestParamList['super_group']) && is_array($requestParamList['super_group'])) {
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
