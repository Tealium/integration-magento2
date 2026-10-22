<?php
namespace Tealium\Tags\Observer;

use Magento\Framework\App\Response\Http as Response;
use Magento\Framework\Event\Observer;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Tealium\Tags\Helper\Data;

class ApiTest extends TestCase
{
    private $helper;
    private $observer;
    private $response;

    protected function setUp(): void
    {
        $this->helper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isApiRequest'])
            ->getMock();
        $storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($this->getMockForAbstractClass(StoreInterface::class));
        $this->response = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getBody', 'setBody', 'setHeader'])
            ->getMock();

        $this->observer = new Api($this->helper, $storeManager);
    }

    public function testNotAnApiRequest()
    {
        $this->helper->method('isApiRequest')->willReturn(false);
        $this->response->expects($this->never())->method('setBody');

        $this->observer->execute(new Observer(['response' => $this->response]));
    }

    public function testPageWithoutDataLayerIsLeftAlone()
    {
        $this->helper->method('isApiRequest')->willReturn(true);
        $this->response->method('getBody')->willReturn('{"json":"from another controller"}');
        $this->response->expects($this->never())->method('setBody');

        $this->observer->execute(new Observer(['response' => $this->response]));
    }

    public function testDataLayerIsServedAsJavascript()
    {
        $this->helper->method('isApiRequest')->willReturn(true);
        $this->response->method('getBody')
            ->willReturn("<html>//TEALIUM_START\nvar utag_data = {};\n//TEALIUM_END\n</html>");
        $this->response->expects($this->once())->method('setBody')->with("\nvar utag_data = {};\n");
        $this->response->expects($this->once())
            ->method('setHeader')
            ->with('Content-Type', 'application/javascript; charset=UTF-8', true);

        $this->observer->execute(new Observer(['response' => $this->response]));
    }
}
