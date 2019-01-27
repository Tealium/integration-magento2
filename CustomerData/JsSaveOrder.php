<?php

namespace Tealium\Tags\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Sales\Api\OrderRepositoryInterface;
use Tealium\Tags\Helper\Product;

class JsSaveOrder implements SectionSourceInterface
{

    protected $_customerSession;

    protected $_productHelper;

    protected $_orderRepository;

    public function __construct(
        CustomerSession $customerSession,
        Product $productHelper,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->_customerSession = $customerSession;
        $this->_productHelper = $productHelper;
        $this->_orderRepository = $orderRepository;
    }

    public function getSectionData()
    {
        $order_id=$this->_customerSession->getTealiumCheckout();
        $this->_customerSession->unsTealiumCheckout();

        $result = [];

        if ($order_id) {

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
                    'cart_id'=>$order_id,
                    'tealium_event'=>'save_order'
                ]
            ];

            $order = $this->_orderRepository->get($order_id);

            foreach ($order->getAllItems() as $item) {
                $productData = $this->_productHelper->getProductData($item->getProductId());
                array_push($result['data']['product_category'], $productData['product_category'][0]);
                array_push($result['data']['product_discount'], $productData['product_discount'][0]);
                array_push($result['data']['product_name'], $productData['product_name'][0]);
                array_push($result['data']['product_id'], $item->getProductId());
                array_push($result['data']['product_list_price'], $productData['product_list_price'][0]);
                array_push($result['data']['product_quantity'], (string)$item->getQty());
                array_push($result['data']['product_sku'], $productData['product_sku'][0]);
                array_push($result['data']['product_subcategory'], $productData['product_subcategory'][0]);
                array_push($result['data']['product_unit_price'], $productData['product_unit_price'][0]);
            }
        }
        
        return $result;
    }
}


