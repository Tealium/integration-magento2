<?php
namespace Tealium\Tags\Plugin\CustomerData;

use Magento\Checkout\CustomerData\Cart;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class CartPluginTest extends TestCase
{
    public function testCartTotalsAreAddedToTheCartSection()
    {
        $quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getItemsQty'])
            ->addMethods(['getGrandTotal'])
            ->getMock();
        $quote->method('getItemsQty')->willReturn(3);
        $quote->method('getGrandTotal')->willReturn('59.5000');

        $session = $this->getMockBuilder(CheckoutSession::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getQuote'])
            ->getMock();
        $session->method('getQuote')->willReturn($quote);

        $plugin = new CartPlugin($session, $this->getMockForAbstractClass(LoggerInterface::class));
        $cart = $this->getMockBuilder(Cart::class)->disableOriginalConstructor()->getMock();

        $result = $plugin->afterGetSectionData($cart, ['summary_count' => 3]);

        $this->assertSame(3, $result['summary_count']);
        $this->assertSame(
            ['cart_total_items' => '3.00', 'cart_total_value' => '59.50'],
            $result['tealium']
        );
    }
}
