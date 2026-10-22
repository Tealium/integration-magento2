<?php

namespace Tealium\Tags\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Framework\App\ObjectManager;
use Psr\Log\LoggerInterface;

/**
 * Base for the Tealium private content sections.
 *
 * All sections are loaded by one customer/section/load request, so an error
 * in one of them would break every section on the page (minicart, customer
 * name, messages, ...). Errors are logged and an empty section is returned.
 */
abstract class AbstractSection implements SectionSourceInterface
{
    /**
     * @var LoggerInterface|null
     */
    private $logger;

    public function getSectionData()
    {
        try {
            return $this->buildSectionData();
        } catch (\Throwable $e) {
            $this->getLogger()->error(
                'Tealium section ' . static::class . ' failed: ' . $e->getMessage(),
                ['exception' => $e]
            );
            return [];
        }
    }

    /**
     * @return array
     */
    abstract protected function buildSectionData();

    /**
     * @return LoggerInterface
     */
    private function getLogger()
    {
        if ($this->logger === null) {
            $this->logger = ObjectManager::getInstance()->get(LoggerInterface::class);
        }
        return $this->logger;
    }
}
