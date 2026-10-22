<?php

namespace Tealium\Tags\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;

/**
 * Loads product and category data for the Tealium data layer in batches,
 * so that building a data layer for N products costs a constant number of
 * queries instead of several queries per product.
 */
class Product extends AbstractHelper
{
    /**
     * Attributes needed by the data layer builders
     */
    const PRODUCT_ATTRIBUTES = [
        'name',
        'sku',
        'price',
        'special_price',
        'image',
        'manufacturer',
        'url_key'
    ];

    protected $_storeManager;

    protected $_productCollectionFactory;

    protected $_categoryCollectionFactory;

    /**
     * @var array category id => ['name' => string, 'path' => string, 'parent_id' => int]
     */
    private $categoryCache = [];

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ProductCollectionFactory $productCollectionFactory,
        CategoryCollectionFactory $categoryCollectionFactory
    ) {
        $this->_storeManager = $storeManager;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        parent::__construct($context);
    }

    /**
     * Load products by id with one query, keyed by product id.
     *
     * @param array $productIds
     * @return \Magento\Catalog\Model\Product[]
     */
    public function loadProducts(array $productIds)
    {
        $productIds = array_values(array_unique(array_filter(array_map('intval', $productIds))));
        if (empty($productIds)) {
            return [];
        }

        $collection = $this->_productCollectionFactory->create();
        $collection->setStoreId($this->_storeManager->getStore()->getId())
            ->addAttributeToSelect(self::PRODUCT_ATTRIBUTES)
            ->addIdFilter($productIds)
            ->addUrlRewrite();
        // category ids can only be attached once the items are loaded
        $collection->load();
        $collection->addCategoryIds();

        $products = [];
        foreach ($collection as $product) {
            $products[(int)$product->getId()] = $product;
        }
        $this->warmCategories($products);
        return $products;
    }

    /**
     * Preload the assigned categories of the given products and all of their
     * ancestors, so that building category trails needs no further queries.
     *
     * @param \Magento\Catalog\Model\Product[] $products
     * @return void
     */
    public function warmCategories(array $products)
    {
        $categoryIds = [];
        foreach ($products as $product) {
            foreach ((array)$product->getCategoryIds() as $id) {
                $categoryIds[] = $id;
            }
        }
        $pathIds = [];
        foreach ($this->loadCategories($categoryIds) as $category) {
            foreach (explode('/', $category['path']) as $id) {
                $pathIds[] = $id;
            }
        }
        $this->loadCategories($pathIds);
    }

    /**
     * Load category data for the given ids (and nothing else) with one query.
     *
     * @param array $categoryIds
     * @return array category id => ['name' => string, 'path' => string, 'parent_id' => int]
     */
    public function loadCategories(array $categoryIds)
    {
        $categoryIds = array_values(array_unique(array_filter(array_map('intval', $categoryIds))));
        $missing = array_diff($categoryIds, array_keys($this->categoryCache));

        if (!empty($missing)) {
            $collection = $this->_categoryCollectionFactory->create();
            $collection->setStoreId($this->_storeManager->getStore()->getId())
                ->addAttributeToSelect('name')
                ->addIdFilter($missing);
            foreach ($collection as $category) {
                $this->categoryCache[(int)$category->getId()] = [
                    'name' => (string)$category->getName(),
                    'path' => (string)$category->getPath(),
                    'parent_id' => (int)$category->getParentId()
                ];
            }
            // remember misses so they are not queried again
            foreach ($missing as $id) {
                if (!isset($this->categoryCache[$id])) {
                    $this->categoryCache[$id] = null;
                }
            }
        }

        $result = [];
        foreach ($categoryIds as $id) {
            if (!empty($this->categoryCache[$id])) {
                $result[$id] = $this->categoryCache[$id];
            }
        }
        return $result;
    }

    /**
     * Return the category names from the top-level category down to the
     * deepest category the product is assigned to in the current store's tree.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string[]
     */
    public function getCategoryTrail($product)
    {
        $categoryIds = (array)$product->getCategoryIds();
        if (empty($categoryIds)) {
            return [];
        }

        $rootId = (int)$this->_storeManager->getStore()->getRootCategoryId();
        $assigned = $this->loadCategories($categoryIds);

        // pick the deepest category inside the current store root
        $bestTrail = [];
        foreach ($assigned as $category) {
            $pathIds = array_map('intval', explode('/', $category['path']));
            $rootPosition = array_search($rootId, $pathIds, true);
            if ($rootPosition === false) {
                continue;
            }
            $trail = array_slice($pathIds, $rootPosition + 1);
            if (count($trail) > count($bestTrail)) {
                $bestTrail = $trail;
            }
        }

        if (empty($bestTrail)) {
            return [];
        }

        $names = $this->loadCategories($bestTrail);
        $result = [];
        foreach ($bestTrail as $id) {
            $result[] = isset($names[$id]) ? $names[$id]['name'] : '';
        }
        return $result;
    }

    /**
     * Manufacturer label for a product, or false if not set / not available
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string|false
     */
    public function getManufacturer($product)
    {
        $attribute = $product->getResource()->getAttribute('manufacturer');
        if (!$attribute) {
            return false;
        }
        $value = $product->getAttributeText('manufacturer');
        return is_array($value) ? implode(',', $value) : $value;
    }

    /**
     * Build the product data used by Tealium events.
     *
     * Every returned key holds an array with one entry per requested product,
     * in the same order as the request, so the arrays always line up.
     *
     * @param int|int[] $product_id
     * @param bool $array kept for backwards compatibility
     * @return array
     */
    public function getProductData($product_id, $array = true)
    {
        $ids = is_array($product_id) ? $product_id : [$product_id];
        $products = $this->loadProducts($ids);

        $result = [
            'product_name' => [],
            'product_list_price' => [],
            'product_sku' => [],
            'product_unit_price' => [],
            'product_category' => [],
            'product_subcategory' => [],
            'product_discount' => []
        ];
        $deeperLevels = [];

        foreach (array_values($ids) as $position => $pid) {
            $product = isset($products[(int)$pid]) ? $products[(int)$pid] : null;

            if ($product === null) {
                foreach (array_keys($result) as $key) {
                    $result[$key][$position] = '';
                }
                $result['product_discount'][$position] = '0.00';
                continue;
            }

            $listPrice = (float)$product->getPrice();
            $unitPrice = $product->getSpecialPrice() ? (float)$product->getSpecialPrice() : $listPrice;

            if ($listPrice == 0) {
                $listPrice = $this->getHighestChildPrice($product);
            }

            $discount = 0;
            if ($listPrice != 0 && $unitPrice != 0 && $listPrice != $unitPrice) {
                $discount = $listPrice - $unitPrice;
            }

            $trail = $this->getCategoryTrail($product);

            $result['product_name'][$position] = (string)$product->getName();
            $result['product_sku'][$position] = (string)$product->getSku();
            $result['product_list_price'][$position] = $this->formatPrice($listPrice);
            $result['product_unit_price'][$position] = $this->formatPrice($unitPrice);
            $result['product_discount'][$position] = $this->formatPrice($discount);
            $result['product_category'][$position] = isset($trail[0]) ? $trail[0] : '';
            $result['product_subcategory'][$position] = isset($trail[1]) ? $trail[1] : '';

            // deeper levels are exposed as product_subcategory_2, product_subcategory_3, ...
            for ($level = 2; $level < count($trail); $level++) {
                $deeperLevels[$level][$position] = $trail[$level];
            }
        }

        // pad deeper levels so they line up with the other arrays
        $count = count($ids);
        foreach ($deeperLevels as $level => $values) {
            $padded = [];
            for ($position = 0; $position < $count; $position++) {
                $padded[] = isset($values[$position]) ? $values[$position] : '';
            }
            $result['product_subcategory_' . $level] = $padded;
        }

        return $result;
    }

    /**
     * Product data for a list of line items (cart or order), with one entry
     * per item in every array.
     *
     * @param array $productIds
     * @param array $quantities same order as $productIds
     * @return array
     */
    public function getItemsData(array $productIds, array $quantities)
    {
        $productIds = array_values($productIds);
        $data = $this->getProductData($productIds);

        $result = [
            'product_category' => $data['product_category'],
            'product_discount' => $data['product_discount'],
            'product_id' => array_map('strval', $productIds),
            'product_list_price' => $data['product_list_price'],
            'product_name' => $data['product_name'],
            'product_quantity' => array_map('strval', array_values($quantities)),
            'product_sku' => $data['product_sku'],
            'product_subcategory' => $data['product_subcategory'],
            'product_unit_price' => $data['product_unit_price']
        ];
        foreach ($data as $key => $values) {
            if (strpos($key, 'product_subcategory_') === 0) {
                $result[$key] = $values;
            }
        }
        return $result;
    }

    /**
     * Configurable and grouped products have no price of their own, so the
     * list price is taken from the most expensive child.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return float
     */
    private function getHighestChildPrice($product)
    {
        $typeInstance = $product->getTypeInstance();
        if ($product->getTypeId() === 'grouped' && method_exists($typeInstance, 'getAssociatedProducts')) {
            $children = $typeInstance->getAssociatedProducts($product);
        } elseif ($product->getTypeId() === 'configurable' && method_exists($typeInstance, 'getUsedProducts')) {
            $children = $typeInstance->getUsedProducts($product);
        } else {
            return 0.0;
        }

        $highest = 0.0;
        foreach ($children as $child) {
            $highest = max($highest, (float)$child->getPrice());
        }
        return $highest;
    }

    /**
     * @param float|int|string|null $value
     * @return string
     */
    public function formatPrice($value)
    {
        return number_format((float)$value, 2, '.', '');
    }
}
