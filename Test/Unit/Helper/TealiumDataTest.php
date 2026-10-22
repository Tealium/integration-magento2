<?php
namespace Tealium\Tags\Helper;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\DataObject;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Tealium\Tags\Helper\Product as ProductHelper;

class TealiumDataTest extends TestCase
{
    private $scopeConfig;
    private $checkoutSession;
    private $productHelper;
    private $request;
    private $eavConfig;
    private $registry;

    private function createHelper()
    {
        $this->scopeConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getQueryValue'])
            ->getMock();
        $context = $this->getMockBuilder(Context::class)->disableOriginalConstructor()->getMock();
        $context->method('getScopeConfig')->willReturn($this->scopeConfig);
        $context->method('getRequest')->willReturn($this->request);

        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId', 'getBaseUrl', 'getCurrentCurrencyCode'])
            ->getMock();
        $store->method('getId')->willReturn(1);
        $store->method('getBaseUrl')->willReturn('https://shop.test/media/');
        $store->method('getCurrentCurrencyCode')->willReturn('USD');
        $storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($store);

        $this->checkoutSession = $this->getMockBuilder(CheckoutSession::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getQuote'])
            ->getMock();
        $locale = $this->getMockBuilder(Resolver::class)->disableOriginalConstructor()->getMock();
        $locale->method('getLocale')->willReturn('en_US');

        $this->productHelper = $this->getMockBuilder(ProductHelper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['loadProducts', 'loadCategories', 'getManufacturer'])
            ->getMock();
        $this->productHelper->method('getManufacturer')->willReturn(false);
        $categories = [
            2 => ['name' => 'Default Category', 'path' => '1/2', 'parent_id' => 1],
            10 => ['name' => 'Women', 'path' => '1/2/10', 'parent_id' => 2],
            11 => ['name' => 'Tops', 'path' => '1/2/10/11', 'parent_id' => 10],
        ];
        $this->productHelper->method('loadCategories')->willReturnCallback(function ($ids) use ($categories) {
            $result = [];
            foreach ($ids as $id) {
                if (isset($categories[(int)$id])) {
                    $result[(int)$id] = $categories[(int)$id];
                }
            }
            return $result;
        });

        $this->eavConfig = $this->getMockBuilder(EavConfig::class)->disableOriginalConstructor()->getMock();
        $this->registry = new Registry();

        $helper = new TealiumData(
            $context,
            $storeManager,
            $this->registry,
            $this->checkoutSession,
            $this->getMockBuilder(CustomerSession::class)->disableOriginalConstructor()->getMock(),
            $locale,
            $this->productHelper,
            $this->getMockForAbstractClass(CategoryRepositoryInterface::class),
            $this->getMockForAbstractClass(GroupRepositoryInterface::class),
            $this->eavConfig
        );
        $helper->setStore($store);

        $layout = $this->getMockForAbstractClass(LayoutInterface::class);
        $layout->method('getBlock')->willReturn(new DataObject(['page_title' => 'Shopping Cart']));
        $helper->setPage(new DataObject(['layout' => $layout]));

        return $helper;
    }

    private function quoteItem($productId, $sku, $qty, $price, $discount)
    {
        return new DataObject([
            'product_id' => $productId,
            'sku' => $sku,
            'name' => "Product $productId",
            'qty' => $qty,
            'price' => $price,
            'discount_amount' => $discount,
            'product' => new DataObject(['price' => $price])
        ]);
    }

    /**
     * The quote item id used to be passed to productRepository->getById(),
     * which throws for any real store; every product is now loaded once.
     */
    public function testCartPage()
    {
        $helper = $this->createHelper();
        $quote = new DataObject([
            'all_visible_items' => [
                $this->quoteItem(5, 'jacket', 1, 80, 8),
                $this->quoteItem(6, 'shirt', 2, 20, 4),
            ],
            'coupon_code' => 'SAVE10',
            'items_qty' => 3,
            'grand_total' => 112
        ]);
        $this->checkoutSession->method('getQuote')->willReturn($quote);
        $this->productHelper->expects($this->once())
            ->method('loadProducts')
            ->with([5, 6])
            ->willReturn([
                5 => new DataObject(['category_ids' => [2, 11], 'product_url' => 'u5', 'image' => '/j.jpg']),
                // product 6 was deleted since it was added to the cart
            ]);

        $udo = $helper->getCartPage();

        $this->assertSame([5, 6], $udo['product_id']);
        $this->assertSame(['1', '2'], $udo['product_quantity']);
        $this->assertSame(['Tops', ''], $udo['product_category']);
        $this->assertSame(['Women', ''], $udo['product_subcategory']);
        $this->assertSame(['https://shop.test/media/catalog/product/j.jpg', ''], $udo['product_image_url']);
        $this->assertSame(['8.00', '4.00'], $udo['product_discount_amount']);
        $this->assertSame(['SAVE10'], $udo['product_promo_code']);
        $this->assertSame('3.00', $udo['cart_total_items']);
        $this->assertSame('112.00', $udo['cart_total_value']);
        $this->assertSame('cart_view', $udo['tealium_event']);
        $this->assertArrayNotHasKey('site_section', $udo);
    }

    public function testEmailIsHashedUnlessPlainTextIsAllowed()
    {
        $helper = $this->createHelper();
        $this->scopeConfig->method('getValue')->willReturnOnConsecutiveCalls(null, '1');

        $this->assertSame(hash('sha256', 'jane@example.com'), $helper->formatEmail('Jane@Example.com'));
        $this->assertSame('Jane@Example.com', $helper->formatEmail('Jane@Example.com'));
        $this->assertSame('', $helper->formatEmail(null));
    }

    /**
     * Only layered navigation filters are refinements, not paging, sorting
     * or tracking parameters.
     */
    public function testBrowseRefinementsIgnoreTrackingParameters()
    {
        $helper = $this->createHelper();
        $this->request->method('getQueryValue')->willReturn([
            'price' => '10-20',
            'utm_source' => 'mail',
            'p' => '2',
            'color' => '52',
            'colors' => ['x']
        ]);
        $color = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId', 'usesSource', 'getSource', 'getIsFilterable', 'getIsFilterableInSearch'])
            ->getMock();
        $color->method('getId')->willReturn(93);
        $color->method('getIsFilterable')->willReturn(1);
        $color->method('usesSource')->willReturn(true);
        $source = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\Source\Table::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getOptionText'])
            ->getMock();
        $source->method('getOptionText')->with('52')->willReturn('Red');
        $color->method('getSource')->willReturn($source);
        $notAnAttribute = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId'])
            ->getMock();
        $this->eavConfig->method('getAttribute')->willReturnCallback(
            function ($entity, $code) use ($color, $notAnAttribute) {
                return $code === 'color' ? $color : $notAnAttribute;
            }
        );

        $udo = $helper->getCategory();

        $this->assertSame(['price', 'color'], $udo['browse_refine_type']);
        $this->assertSame(['10-20', 'Red'], $udo['browse_refine_value']);
    }
}
