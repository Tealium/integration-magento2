<?php

namespace Tealium\Tags\Helper;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Catalog\Api\CategoryRepositoryInterface;

class Product extends AbstractHelper
{

    protected $_storeManager;

    protected $_productRepository;

    protected  $_categoryCollectionFactory;

    protected $_categoryRepository;

    public function __construct(
        StoreManagerInterface $storeManager, 
        ProductRepositoryInterface $productRepository,
        CollectionFactory $categoryCollectionFactory,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->_storeManager = $storeManager;
        $this->_productRepository = $productRepository;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_categoryRepository = $categoryRepository;
    }

    public function getProductData($product_id, $array = true) {
        $result = [];
        $product = $this->_productRepository->getById($product_id);

        $result['product_name'] = [(string)$product->getName()];
        $result['product_list_price'] = [(string)number_format((float)$product->getPrice(), 2, '.', '')];
        $result['product_sku'] = [(string)$product->getSku()];
        $result['product_unit_price'] = [(string)number_format((float)$product->getPrice(), 2, '.', '')];
        if ($product->getSpecialPrice()) {
            $result['product_unit_price']  = [(string)number_format((float)$product->getSpecialPrice(), 2, '.', '')];
        }
        $result['product_category'] = [''];
        $result['product_subcategory'] = [''];
        
        $product_discount = 0;
        if (
            $result['product_list_price'][0] != 0 && 
            $result['product_unit_price'][0] != 0 && 
            $result['product_list_price'][0] != $result['product_unit_price'][0]
        ) {
            $product_discount = $result['product_list_price'][0] - $result['product_unit_price'][0];
        }
        
        $result['product_discount'] = [(string)number_format((float)$product_discount, 2, '.', '')];
        if ($result['product_list_price'][0] == 0) {
            $children = $product->getTypeInstance()->getUsedProducts($product);
            foreach ($children as $child) {
                if ($result['product_list_price'][0] < $child->getPrice()) {
                    $result['product_list_price'][0] = (string)number_format((float)$child->getPrice(), 2, '.', '');
                }
            }
        }

        $categoryIds = $product->getCategoryIds(); 
        
        $mainCategory = false;
        $subCategory = false;

        // get main and subcategory from all category of the product
        
        foreach ($categoryIds as $index => $id) {
            $category = $this->_categoryRepository->get($id, $this->_storeManager->getStore()->getId());
            if ($index == 0) {
                $result['product_category'][0] = $category->getName();
            }
            if ($index == 1) {
                $result['product_subcategory'][0] = $category->getName();
            }
            if ($index != 0 && $index != 1) {
                $result['product_subcategory_'.$index][0] = $category->getName();
            }
        }
        return $result;
    }

}