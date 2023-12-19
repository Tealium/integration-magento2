<?php

namespace Tealium\Tags\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Tealium\Tags\Helper\Product;
use Magento\Checkout\Model\Cart;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

class JsAddToCart implements SectionSourceInterface
{

    protected $_customerSession;
    protected $_productHelper;
    protected $_cart;
    protected $_storeManager;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;

    public function __construct(
        CustomerSession $customerSession,
        Product $productHelper,
        Cart $cart,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository
    ) {
        $this->_customerSession = $customerSession;
        $this->_productHelper = $productHelper;
        $this->_cart = $cart;
        $this->_storeManager = $storeManager;
        $this->_productRepository = $productRepository;
    }

    public function getSectionData()
    {
        $productImage = [];
        $product_id=$this->_customerSession->getTealiumAddProductId();
        $this->_customerSession->unsTealiumAddProductId();

        $qty = $this->_customerSession->getTealiumAddProductQty();
        $this->_customerSession->unsTealiumAddProductQty();

        $result = [];
    
        if ($product_id) {
            $result = ['data'=>$this->_productHelper->getProductData($product_id)];
            
            
            $items = $this->_cart->getQuote()->getAllVisibleItems();
            $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);            
            foreach ($items as $item) {
                $CartId[] =     $item->getProductId();
                $CartSku[] =     $item->getSku();
                $CartQty[] =     $item->getQty();
                $CartPrice[] =    number_format($item->getPrice(), 2, '.', '');
                $productCat = $this->_productRepository->getById($item->getProductId());
                $productImage[] = $mediaUrl . 'catalog/product' . $productCat->getImage();
            }
        
            
            $result['data']['cart_product_id'] = $CartId;
            $result['data']['cart_product_price'] = $CartPrice;
            $result['data']['cart_product_quantity'] = $CartQty;
            $result['data']['cart_product_sku'] = $CartSku;
            $result['data']['product_image_url'] = $productImage;
            
            if (is_array($product_id)) {
                $result['data']['product_quantity'] = $qty;
                $result['data']['product_id'] = $product_id;
            } else {
                $result['data']['product_quantity'] = [(string)$qty];
                $result['data']['product_id'] = [(string)$product_id];
            }

            $result['data']['tealium_event'] = 'cart_add';
        }

        return $result;
    }
}
