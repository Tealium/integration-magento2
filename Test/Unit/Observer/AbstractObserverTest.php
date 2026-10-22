<?php
namespace Tealium\Tags\Observer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class AbstractObserverTest extends TestCase
{
    public function testErrorsAreLoggedNotThrown()
    {
        $logger = $this->getMockForAbstractClass(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('broken tracking'));

        $objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $objectManager->method('get')->with(LoggerInterface::class)->willReturn($logger);
        ObjectManager::setInstance($objectManager);

        $observer = new class extends AbstractObserver {
            protected function track(Observer $observer)
            {
                throw new \TypeError('broken tracking');
            }
        };

        $this->assertSame($observer, $observer->execute(new Observer()));
    }
}
