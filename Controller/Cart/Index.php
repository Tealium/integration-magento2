<?php
namespace Tealium\Tags\Controller\Cart;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Checkout\Model\Cart;
use Tealium\Tags\Helper\Product;

class Index extends Action
{
	protected $_pageFactory;

	protected $_cart;

	protected $_productHelper;

	public function __construct(
		Context $context,
		PageFactory $pageFactory,
		Cart $cart,
		Product $productHelper
	){
		$this->_pageFactory = $pageFactory;
		$this->_cart = $cart;
		$this->_productHelper = $productHelper;
		return parent::__construct($context);
	}

	public function execute()
	{
		//echo json_encode(phpinfo()); exit;
		$cartData = $this->_cart->getQuote()->getAllVisibleItems();

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
				'product_unit_price'=>[]
			]
		];

		foreach ($cartData as $key => $value) {

			$product = $this->_objectManager->get('Magento\Catalog\Model\Product')->load($value->getProductId());
			foreach ($value->getOptions() as $option) {
				if ($option) {
					$optionStr = $option->getValue();
					if ($optionStr && (strpos($optionStr, 'super_attribute') !== false)) {
						$option_list = json_decode($optionStr);
						$product = $this->_objectManager->get('Magento\ConfigurableProduct\Model\Product\Type\Configurable')->getProductByAttributes((array)$option_list->super_attribute,$product);
					}
				}
			}
			$productData = $this->_productHelper->getProductData($product->getId());

			array_push($result['data']['product_category'], $productData['product_category'][0]);
			array_push($result['data']['product_discount'], $productData['product_discount'][0]);
			array_push($result['data']['product_name'], $productData['product_name'][0]);
			array_push($result['data']['product_id'], (string)$value->getProductId());
			array_push($result['data']['product_list_price'], $productData['product_list_price'][0]);
			array_push($result['data']['product_quantity'], (string)$value->getQty());
			array_push($result['data']['product_sku'], $productData['product_sku'][0]);
			array_push($result['data']['product_subcategory'], $productData['product_subcategory'][0]);
			array_push($result['data']['product_unit_price'], $productData['product_unit_price'][0]);
			for ($index = 2; $index <= 10; $index++) {
				if (isset($productData['product_subcategory_'.$index])) {
					if(!isset($result['data']['product_subcategory_'.$index])) {
						$result['data']['product_subcategory_'.$index] = array();
					}
					$count = count($result['data']['product_id'])-1;
					while (count($result['data']['product_subcategory_'.$index]) < $count) {
						array_push($result['data']['product_subcategory_'.$index], '');
					}
					array_push($result['data']['product_subcategory_'.$index], $productData['product_subcategory_'.$index][0]);
				}
			}
		}
		
		echo json_encode($result);
		exit;
	}
}
