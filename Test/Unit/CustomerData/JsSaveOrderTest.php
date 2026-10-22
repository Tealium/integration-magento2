<?php
namespace Tealium\Tags\CustomerData;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\DataObject;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\TestCase;
use Tealium\Tags\Helper\Product;

class JsSaveOrderTest extends TestCase
{
    /**
     * The success event stores "order_ids" (an array); the order is loaded
     * by its id and every product is loaded in one batch.
     */
    public function testOrderIdsFromTheSuccessEvent()
    {
        $session = $this->getMockBuilder(CustomerSession::class)
            ->disableOriginalConstructor()
            ->addMethods(['getTealiumCheckout', 'unsTealiumCheckout'])
            ->getMock();
        $session->method('getTealiumCheckout')->willReturn([42]);

        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAllItems'])
            ->getMock();
        $order->method('getAllItems')->willReturn([
            new DataObject(['product_id' => 5, 'qty_ordered' => 2]),
            new DataObject(['product_id' => 6, 'qty_ordered' => 1])
        ]);
        $repository = $this->getMockForAbstractClass(OrderRepositoryInterface::class);
        $repository->expects($this->once())->method('get')->with(42)->willReturn($order);

        $helper = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getItemsData'])
            ->getMock();
        $helper->expects($this->once())
            ->method('getItemsData')
            ->with([5, 6], [2, 1])
            ->willReturn(['product_id' => ['5', '6']]);

        $data = (new JsSaveOrder($session, $helper, $repository))->getSectionData();

        $this->assertSame(['5', '6'], $data['data']['product_id']);
        $this->assertSame([42], $data['data']['cart_id']);
        $this->assertSame('save_order', $data['data']['tealium_event']);
    }
}
