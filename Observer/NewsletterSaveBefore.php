<?php

namespace Tealium\Tags\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Session\SessionManagerInterface as CoreSession;

class NewsletterSaveBefore extends AbstractObserver
{
    protected $_coreSession;

    public function __construct(
        CoreSession $coreSession
    ) {
        $this->_coreSession = $coreSession;
    }

    /**
     * This is the method that fires when the event runs.
     *
     * @param Observer $observer
     */
    protected function track(Observer $observer)
    {
        $subscriber = $observer->getEvent()->getSubscriber();

        // only new subscriptions are tracked
        if ($subscriber && $subscriber->isObjectNew()) {
            $this->_coreSession->setTealiumNewsletterEmail($subscriber->getEmail());
            $this->_coreSession->setTealiumNewsletterId($subscriber->getId());
        }
    }
}
