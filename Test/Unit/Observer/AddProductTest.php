<?php
namespace Tealium\Tags\Observer;

use Tealium\Tags\Observer\AddProduct as Observer;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Event;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Request\Http;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;

class AddProductTest extends TestCase
{
    /**
     * @var Observer
     */
    protected $observer;

     /**
     * @var Session|MockObject
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session|MockObject
     */
    protected $customerSession;

    protected $productRepository;

    protected $request;

    protected $objectManager;

    
 
    protected function setUp(): void
    {
        $this->checkoutSession = $this->getMockBuilder(
                CheckoutSession::class
            )
            ->setMethods(
                [
                    'getLastAddedProductId'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->customerSession = $this->getMockBuilder(CustomerSession::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'setTealiumAddProductId',
                'setTealiumAddProductQty'
            ])
            ->getMock();

        $this->productRepository = $this->getMockBuilder(ProductRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getById'
            ])
            ->getMockForAbstractClass();
        $this->request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManager = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->observer = new Observer(
            $this->request,
            $this->customerSession,
            $this->productRepository,
            $this->objectManager,
            $this->checkoutSession
        );
    }

    public function testExecute()
    {
        $productId = 4;        

        $eventObserver = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event = $this->getMockBuilder(Event::class)
            ->setMethods(['getRequest', 'getResponse'])
            ->disableOriginalConstructor()
            ->getMock();
        $request = $this->getMockBuilder(RequestInterface::class)
            ->getMock();
        $response = $this->getMockBuilder(ResponseInterface::class)
            ->setMethods(['setRedirect'])
            ->getMockForAbstractClass();

        $eventObserver->expects($this->any())->method('getEvent')->willReturn($event);        

        $this->checkoutSession->expects($this->once())
            ->method('getLastAddedProductId')
            ->willReturn($productId);

        $this->request->expects($this->once())->method('getParams');

        $this->productRepository->expects($this->any())->method('getById');

        $this->customerSession->expects($this->once())
            ->method('setTealiumAddProductId')
            ->willReturn(true);
        $this->customerSession->expects($this->once())
            ->method('setTealiumAddProductQty')
            ->willReturn(true);

        

        /** @var $eventObserver \Magento\Framework\Event\Observer */
        $this->observer->execute($eventObserver);
    }
}