<?php
namespace Tealium\Tags\Observer;

use Tealium\Tags\Observer\AddToCompare as Observer;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Event;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Request\Http;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Catalog\Model\ProductFactory;

class AddToCompareTest extends TestCase
{
    /**
     * @var Observer
     */
    protected $observer;

    /**
     * @var \Magento\Customer\Model\Session|MockObject
     */
    protected $customerSession;

    protected $productFactory;
    
 
    protected function setUp(): void
    {

        $this->customerSession = $this->getMockBuilder(CustomerSession::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'setTealiumCompareProductId',
                'setTealiumCompareProductQty'
            ])
            ->getMock();

        $this->productFactory = $this->getMockBuilder(ProductFactory::class)
            ->setMethods([
                'create',
                'getIdBySku'                
            ])        
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->observer = new Observer(
            $this->customerSession,
            $this->productFactory
        );
    }

    public function testExecute()
    {
        $productId = 4;        

        $eventObserver = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event = $this->getMockBuilder(Event::class)
            ->setMethods(['getRequest', 'getResponse', 'getData'])
            ->disableOriginalConstructor()
            ->getMock();

        $eventObserver->expects($this->any())->method('getData')->willReturn($event);        

        $this->productFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->productFactory);

        $this->productFactory->expects($this->any())
            ->method('getIdBySku');            
        

        $this->customerSession->expects($this->once())
            ->method('setTealiumCompareProductId')
            ->willReturn(true);
        $this->customerSession->expects($this->once())
            ->method('setTealiumCompareProductQty')
            ->willReturn(true);

        /** @var $eventObserver \Magento\Framework\Event\Observer */
        $this->observer->execute($eventObserver);
    }
}