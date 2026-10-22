<?php
namespace Tealium\Tags\Observer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

/**
 * Base for the Tealium tracking observers.
 *
 * Tracking must never break the business action it observes (add to cart,
 * login, review, ...), so any error is logged and swallowed.
 */
abstract class AbstractObserver implements ObserverInterface
{
    /**
     * @var LoggerInterface|null
     */
    private $logger;

    public function execute(Observer $observer)
    {
        try {
            $this->track($observer);
        } catch (\Throwable $e) {
            $this->getLogger()->error(
                'Tealium observer ' . static::class . ' failed: ' . $e->getMessage(),
                ['exception' => $e]
            );
        }

        return $this;
    }

    /**
     * Record the data for the Tealium event
     *
     * @param Observer $observer
     * @return void
     */
    abstract protected function track(Observer $observer);

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
