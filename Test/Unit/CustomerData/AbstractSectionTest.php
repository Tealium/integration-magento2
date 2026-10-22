<?php
namespace Tealium\Tags\CustomerData;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class AbstractSectionTest extends TestCase
{
    /**
     * All sections are returned by one request, so one failing Tealium
     * section must not take the minicart and the others down with it.
     */
    public function testFailingSectionReturnsEmptyData()
    {
        $logger = $this->getMockForAbstractClass(LoggerInterface::class);
        $logger->expects($this->once())->method('error');
        $objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $objectManager->method('get')->willReturn($logger);
        ObjectManager::setInstance($objectManager);

        $section = new class extends AbstractSection {
            protected function buildSectionData()
            {
                return [] - [];
            }
        };

        $this->assertSame([], $section->getSectionData());
    }
}
