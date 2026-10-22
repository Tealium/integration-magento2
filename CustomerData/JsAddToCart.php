<?php

namespace Tealium\Tags\CustomerData;

use Magento\Customer\Model\Session as CustomerSession;
use Tealium\Tags\Helper\Product;
use Magento\Checkout\Model\Cart;
use Magento\Store\Model\StoreManagerInterface;

class JsAddToCart extends AbstractSection
{

    protected $_customerSession;
    protected $_productHelper;
    protected $_cart;
    protected $_storeManager;

    public function __construct(
        CustomerSession $customerSession,
        Product $productHelper,
        Cart $cart,
        StoreManagerInterface $storeManager
    ) {
        $this->_customerSession = $customerSession;
        $this->_productHelper = $productHelper;
        $this->_cart = $cart;
        $this->_storeManager = $storeManager;
    }

    protected function buildSectionData()
    {
        $product_id = $this->_customerSession->getTealiumAddProductId();
        $this->_customerSession->unsTealiumAddProductId();

        $qty = $this->_customerSession->getTealiumAddProductQty();
        $this->_customerSession->unsTealiumAddProductQty();

        if (!$product_id) {
            return [];
        }

        $result = ['data' => $this->_productHelper->getProductData($product_id)];

        $items = $this->_cart->getQuote()->getAllVisibleItems();
        $itemProductIds = [];
        foreach ($items as $item) {
            $itemProductIds[] = $item->getProductId();
        }
        $products = $this->_productHelper->loadProducts($itemProductIds);
        $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        $cartId = [];
        $cartSku = [];
        $cartQty = [];
        $cartPrice = [];
        $productImage = [];
        foreach ($items as $item) {
            $product = isset($products[(int)$item->getProductId()]) ? $products[(int)$item->getProductId()] : null;
            $cartId[] = $item->getProductId();
            $cartSku[] = $item->getSku();
            $cartQty[] = $item->getQty();
            $cartPrice[] = number_format((float)$item->getPrice(), 2, '.', '');
            $productImage[] = $product ? $mediaUrl . 'catalog/product' . $product->getImage() : '';
        }

        $result['data']['cart_product_id'] = $cartId;
        $result['data']['cart_product_price'] = $cartPrice;
        $result['data']['cart_product_quantity'] = $cartQty;
        $result['data']['cart_product_sku'] = $cartSku;
        $result['data']['product_image_url'] = $productImage;

        if (is_array($product_id)) {
            $result['data']['product_quantity'] = $qty;
            $result['data']['product_id'] = $product_id;
        } else {
            $result['data']['product_quantity'] = [(string)$qty];
            $result['data']['product_id'] = [(string)$product_id];
        }

        $result['data']['tealium_event'] = 'cart_add';

        return $result;
    }
}
