<?php
namespace Tealium\Tags\Block;

use Magento\Framework\DataObject;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Tealium\Tags\Helper\Data;
use Tealium\Tags\Helper\TealiumData;

class TealiumTest extends TestCase
{
    private $tealiumData;
    private $configHelper;

    private function createBlock()
    {
        $this->tealiumData = $this->getMockBuilder(TealiumData::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setStore', 'setPage', 'getHome', 'getSearch', 'getCategory', 'getProductPage',
                'getCartPage', 'getOrderConfirmation', 'getCustomerData'])
            ->getMock();
        $this->configHelper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isApiRequest'])
            ->getMock();

        $secureRenderer = $this->getMockBuilder(SecureHtmlRenderer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['renderTag'])
            ->getMock();
        $secureRenderer->method('renderTag')->willReturnCallback(
            function ($tag, $attributes, $content) {
                return "<$tag>$content</$tag>";
            }
        );

        $block = $this->getMockBuilder(Tealium::class)
            ->setConstructorArgs([])
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($this->getMockForAbstractClass(StoreInterface::class));

        $set = function ($name, $value) use ($block) {
            $property = new \ReflectionProperty(get_class($block), $name);
            $property->setAccessible(true);
            $property->setValue($block, $value);
        };
        $set('_helper', $this->tealiumData);
        $set('configHelper', $this->configHelper);
        $set('secureRenderer', $secureRenderer);
        $set('_storeManager', $storeManager);

        return $block;
    }

    public function testOnlyTheRequestedPageTypeIsBuilt()
    {
        $block = $this->createBlock();
        $this->tealiumData->expects($this->once())->method('getCategory')->willReturn(['page_type' => 'category']);
        foreach (['getHome', 'getSearch', 'getProductPage', 'getCartPage', 'getOrderConfirmation', 'getCustomerData'] as $m) {
            $this->tealiumData->expects($this->never())->method($m);
        }

        $data = ['store' => new DataObject(), 'page' => new DataObject()];
        $block->init('acct', 'main', '', 'prod', 'Category', $data);

        $this->assertSame('{"page_type":"category"}', str_replace([" ", "\n"], '', $block->render('json')));
    }

    public function testUnknownPageTypeFallsBackToPageType()
    {
        $block = $this->createBlock();
        $data = ['store' => new DataObject(), 'page' => new DataObject()];
        $block->init('acct', 'main', '', 'prod', 'Something', $data);

        $this->assertStringContainsString('"page_type": "Something"', $block->render('json'));
    }

    public function testDataCannotBreakOutOfTheScript()
    {
        $block = $this->createBlock();
        $this->configHelper->method('isApiRequest')->willReturn(false);
        $this->tealiumData->method('getProductPage')
            ->willReturn(['product_name' => ['</script><script>alert(1)</script>']]);

        $data = ['store' => new DataObject(), 'page' => new DataObject()];
        $block->init('acct', 'main', '', 'prod', 'ProductPage', $data);
        $html = $block->render('udo');

        $this->assertStringNotContainsString('</script><script>', $html);
        // only the real closing tag remains; the data is hex-escaped
        $this->assertSame(1, substr_count($html, '</script>'));
        $this->assertStringContainsString('u003C', $html);
        $this->assertStringContainsString('var utag_jsurl = "\/\/tags.tiqcdn.com\/utag\/acct\/main\/prod\/utag.js";', $html);
    }

    public function testFirstPartyDomain()
    {
        $block = $this->createBlock();
        $data = ['store' => new DataObject(), 'page' => new DataObject()];
        $block->init('acct', 'main', ' tags.example.com ', 'prod', 'Home', $data);

        $this->assertSame('https://tags.example.com/main/prod/utag.js', $block->getUtagUrl());
    }
}
