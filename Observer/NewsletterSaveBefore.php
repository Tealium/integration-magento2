<?php

namespace Tealium\Tags\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Review\Model\Review;
use Psr\Log\LoggerInterface;
use Magento\Framework\Session\SessionManagerInterface as CoreSession;
use Magento\Framework\Registry;

class NewsletterSaveBefore implements ObserverInterface
{
    /** @var \Magento\Framework\Logger\Monolog */
    protected $_logger;

    protected $_coreSession;

    public function __construct(
        LoggerInterface $loggerInterface,
        CoreSession $coreSession,
        Registry $coreRegistry
    )
    {
        $this->_logger = $loggerInterface;
        $this->_coreSession = $coreSession;
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * This is the method that fires when the event runs.
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {

        // Get the newsletter subscriber object
        $subscriber = $observer->getEvent()->getSubscriber();

        // Get the subscription ID
        $subscriptionId = $subscriber->getId();

        if ($subscriber->isObjectNew()) {
            // This is a new subscription
            $email = $subscriber->getEmail();
            
            $this->_logger->notice('Email: ' . $email);
            $this->_logger->notice('subscriber ID: ' . $subscriptionId);

            $this->_coreSession->setTealiumNewsletterId($subscriptionId);
        } else {
            // This is an update to an existing subscription
            // You can perform custom logic for updates here
            $this->_logger->notice('Existing subscriber ID: ' . $subscriptionId);
        }
        
        
        
        
        
    }
}