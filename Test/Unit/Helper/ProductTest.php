<?php
namespace Tealium\Tags\Helper;

use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    /**
     * Categories: 1 Root Catalog / 2 Default Category / 10 Women / 11 Tops / 12 Jackets
     */
    const CATEGORIES = [
        1 => ['name' => 'Root Catalog', 'path' => '1', 'parent_id' => 0],
        2 => ['name' => 'Default Category', 'path' => '1/2', 'parent_id' => 1],
        10 => ['name' => 'Women', 'path' => '1/2/10', 'parent_id' => 2],
        11 => ['name' => 'Tops', 'path' => '1/2/10/11', 'parent_id' => 10],
        12 => ['name' => 'Jackets', 'path' => '1/2/10/11/12', 'parent_id' => 11],
    ];

    private $products = [];
    private $productCollectionCreated = 0;
    private $categoryQueries = 0;

    private function createHelper()
    {
        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId', 'getRootCategoryId'])
            ->getMock();
        $store->method('getId')->willReturn(1);
        $store->method('getRootCategoryId')->willReturn(2);
        $storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($store);

        $productFactory = $this->getMockBuilder(ProductCollectionFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $productFactory->method('create')->willReturnCallback(function () {
            $this->productCollectionCreated++;
            $collection = $this->getMockBuilder(ProductCollection::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['setStoreId', 'addAttributeToSelect', 'addIdFilter', 'addUrlRewrite', 'load',
                    'addCategoryIds', 'getIterator'])
                ->getMock();
            $ids = [];
            foreach (['setStoreId', 'addAttributeToSelect', 'addUrlRewrite', 'load', 'addCategoryIds'] as $method) {
                $collection->method($method)->willReturnSelf();
            }
            $collection->method('addIdFilter')->willReturnCallback(function ($filter) use (&$ids, $collection) {
                $ids = $filter;
                return $collection;
            });
            $collection->method('getIterator')->willReturnCallback(function () use (&$ids) {
                return new \ArrayIterator(array_values(array_intersect_key($this->products, array_flip($ids))));
            });
            return $collection;
        });

        $categoryFactory = $this->getMockBuilder(CategoryCollectionFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $categoryFactory->method('create')->willReturnCallback(function () {
            $collection = $this->getMockBuilder(CategoryCollection::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['setStoreId', 'addAttributeToSelect', 'addIdFilter', 'getIterator'])
                ->getMock();
            $ids = [];
            $collection->method('setStoreId')->willReturnSelf();
            $collection->method('addAttributeToSelect')->willReturnSelf();
            $collection->method('addIdFilter')->willReturnCallback(function ($filter) use (&$ids, $collection) {
                $ids = $filter;
                return $collection;
            });
            $collection->method('getIterator')->willReturnCallback(function () use (&$ids) {
                $this->categoryQueries++;
                $items = [];
                foreach ($ids as $id) {
                    if (isset(self::CATEGORIES[$id])) {
                        $items[] = new DataObject(['id' => $id] + self::CATEGORIES[$id]);
                    }
                }
                return new \ArrayIterator($items);
            });
            return $collection;
        });

        $context = $this->getMockBuilder(Context::class)->disableOriginalConstructor()->getMock();

        return new Product($context, $storeManager, $productFactory, $categoryFactory);
    }

    private function addProduct($id, $sku, $price, $special, array $categoryIds, $typeId = 'simple')
    {
        $product = $this->getMockBuilder(ProductModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId', 'getName', 'getSku', 'getPrice', 'getSpecialPrice', 'getCategoryIds',
                'getTypeId', 'getTypeInstance'])
            ->getMock();
        $product->method('getId')->willReturn($id);
        $product->method('getName')->willReturn("Product $id");
        $product->method('getSku')->willReturn($sku);
        $product->method('getPrice')->willReturn($price);
        $product->method('getSpecialPrice')->willReturn($special);
        $product->method('getCategoryIds')->willReturn($categoryIds);
        $product->method('getTypeId')->willReturn($typeId);
        $product->method('getTypeInstance')->willReturn(new DataObject());
        $this->products[$id] = $product;
    }

    public function testSeveralProductsAreLoadedInOneQueryAndLineUp()
    {
        $this->addProduct(5, 'jacket', 100, 80, [12]);
        $this->addProduct(6, 'shirt', 50, null, [10]);
        $helper = $this->createHelper();

        $data = $helper->getProductData([5, 6]);

        $this->assertSame(1, $this->productCollectionCreated);
        // assigned categories, then their ancestors; trails then hit the cache
        $this->assertSame(2, $this->categoryQueries);
        $this->assertSame(['jacket', 'shirt'], $data['product_sku']);
        $this->assertSame(['100.00', '50.00'], $data['product_list_price']);
        $this->assertSame(['80.00', '50.00'], $data['product_unit_price']);
        // used to be an array minus array TypeError for grouped products
        $this->assertSame(['20.00', '0.00'], $data['product_discount']);
        $this->assertSame(['Women', 'Women'], $data['product_category']);
        $this->assertSame(['Tops', ''], $data['product_subcategory']);
        $this->assertSame(['Jackets', ''], $data['product_subcategory_2']);
    }

    public function testMissingProductKeepsArraysAligned()
    {
        $this->addProduct(6, 'shirt', 50, null, []);
        $helper = $this->createHelper();

        $data = $helper->getProductData([999, 6]);

        $this->assertSame(['', 'shirt'], $data['product_sku']);
        $this->assertSame(['', ''], $data['product_category']);
    }

    /**
     * A dynamic-price bundle has no price; only configurable and grouped
     * products have children to take one from. getUsedProducts() used to be
     * called on any zero-price product, which fails for bundles.
     */
    public function testZeroPriceBundleDoesNotFail()
    {
        $this->addProduct(7, 'bundle', null, null, [], 'bundle');
        $helper = $this->createHelper();

        $data = $helper->getProductData(7);

        $this->assertSame(['0.00'], $data['product_list_price']);
    }
}
