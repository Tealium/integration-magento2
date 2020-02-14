<?php

namespace Tealium\Tags\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Tealium\Tags\Helper\Product;
use Magento\Checkout\Model\Cart;

class JsAddToCart implements SectionSourceInterface
{

    protected $_customerSession;

    protected $_productHelper;
	
	protected $_cart;

    public function __construct(
        CustomerSession $customerSession,
        Product $productHelper,
		Cart $cart
    ) {
        $this->_customerSession = $customerSession;
        $this->_productHelper = $productHelper;
		$this->_cart = $cart;
    }

    public function getSectionData()
    {
		$productImage = false;
        $product_id=$this->_customerSession->getTealiumAddProductId();
        $this->_customerSession->unsTealiumAddProductId();

        $qty = $this->_customerSession->getTealiumAddProductQty();
        $this->_customerSession->unsTealiumAddProductQty();

        $result = [];
	
        if ($product_id) {
            $result = ['data'=>$this->_productHelper->getProductData($product_id)];
			
			//$data = $this->getCartDataCustom();
			$items = $this->_cart->getQuote()->getAllVisibleItems();
			$objectManager =  \Magento\Framework\App\ObjectManager::getInstance();  
			$mediaUrl = $objectManager->get('Magento\Store\Model\StoreManagerInterface')
            ->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
			
			foreach($items as $item) {
				$CartId[] = 	$item->getProductId();
				$CartSku[] = 	$item->getSku();
				$CartQty[] = 	$item->getQty();
				$CartPrice[] =	number_format($item->getPrice(), 2, '.', '');
				
				$productCat = $objectManager->create('Magento\Catalog\Model\Product')->load($item->getProductId());
				$productImage[] = $mediaUrl.$productCat->getImage();
			}
		
			
			$result['data']['cart_product_id'] = $CartId;
			$result['data']['cart_product_price'] = $CartPrice;
			$result['data']['cart_product_quantity'] = $CartQty;
			$result['data']['cart_product_sku'] = $CartSku;
			$result['data']['product_image_url'] = $productImage;
            $result['data']['product_quantity'] = [(string)$qty];
            $result['data']['product_id'] = [(string)$product_id];
            $result['data']['tealium_event'] = 'cart_add';
        }

        return $result;
    }
	
	
}
