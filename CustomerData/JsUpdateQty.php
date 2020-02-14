<?php

namespace Tealium\Tags\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Tealium\Tags\Helper\Product;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Catalog\Api\ProductRepositoryInterface;

class JsUpdateQty implements SectionSourceInterface
{

    protected $_customerSession;

    protected $_productHelper;

    protected $_checkoutSession;

    protected $_productRepository;

    public function __construct(
        CustomerSession $customerSession,
        Product $productHelper,
        ProductRepositoryInterface $productRepository,
        CheckoutSession $checkoutSession
    ) {
        $this->_customerSession = $customerSession;
        $this->_productHelper = $productHelper;
        $this->_checkoutSession = $checkoutSession;
        $this->_productRepository = $productRepository;
    }
    
    public function getSectionData()
    {
        $productIdList=$this->_customerSession->getTealiumQty();
        $this->_customerSession->unsTealiumQty();

        $productRealIdList = $this->_customerSession->getTealiumQtyReal();
        $this->_customerSession->unsTealiumQtyReal();
        
        $result = [];
        if ($productIdList) {
            $result = [
                'data'=>[
                    'product_category'=>[],
                    'product_discount'=>[],
                    'product_id'=>[],
                    'product_list_price'=>[],
                    'product_name'=>[],
                    'product_quantity'=>[],
                    'product_sku'=>[],
                    'product_subcategory'=>[],
                    'product_unit_price'=>[],
                    'tealium_event'=>'cart_update_item_quanity'
                ]
            ];

            $quoteList=$this->_checkoutSession->getQuote()->getAllVisibleItems();

            foreach ($quoteList as $quoteItem) {
                if (in_array($quoteItem->getItemId(), $productIdList)) {
                    $product = $this->_productRepository->get($quoteItem->getSku());
                    $productData = $this->_productHelper->getProductData($product->getId());
                    array_push($result['data']['product_category'], $productData['product_category'][0]);
                    array_push($result['data']['product_discount'], $productData['product_discount'][0]);
                    array_push($result['data']['product_name'], $productData['product_name'][0]);
                    array_push($result['data']['product_id'], $product->getId());
                    array_push($result['data']['product_list_price'], $productData['product_list_price'][0]);
                    array_push($result['data']['product_quantity'], (string)$quoteItem->getQty());
                    array_push($result['data']['product_sku'], $productData['product_sku'][0]);
                    array_push($result['data']['product_subcategory'], $productData['product_subcategory'][0]);
                    array_push($result['data']['product_unit_price'], $productData['product_unit_price'][0]);
                    //if (isset($productData['product_subcategory_1'])
                    for ($index = 2; $index <= 10; $index++) {
                        if (isset($productData['product_subcategory_'.$index])) {
                            if (!isset($result['data']['product_subcategory_'.$index])) {
                                $result['data']['product_subcategory_'.$index] = [];
                            }
                            $count = count($result['data']['product_id'])-1;
                            while (count($result['data']['product_subcategory_'.$index]) < $count) {
                                array_push($result['data']['product_subcategory_'.$index], '');
                            }
                            array_push($result['data']['product_subcategory_'.$index], $productData['product_subcategory_'.$index][0]);
                        }
                    }
                }
            }
        }
        return $result;
    }
}
