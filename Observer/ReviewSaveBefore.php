<?php

namespace Tealium\Tags\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Review\Model\Review;
use Psr\Log\LoggerInterface;
use Magento\Framework\Session\SessionManagerInterface as CoreSession;
use Magento\Framework\Registry;

class ReviewSaveBefore implements ObserverInterface
{
    /** @var \Magento\Framework\Logger\Monolog */
    protected $_logger;

    protected $_coreSession;

    protected $_coreRegistry;

    public function __construct(
        LoggerInterface $loggerInterface,
        CoreSession $coreSession,
        Registry $coreRegistry
    ) {
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
        // /** @var Review $review */
        $review = $observer->getDataByKey('object');
        $reviewerNickname = $review->getNickname();
        $reviewTitle = $review->getTitle();
        $reivewDetail = $review->getDetail();
        $reviewRating = $review->getRatings()[4] - 15;

        $product = $this->_coreRegistry->registry('current_product');
        $productId = $product->getId();

        $this->_coreSession->setTealiumReviewerNickname($reviewerNickname);
        $this->_coreSession->setTealiumReviewTitle($reviewTitle);
        $this->_coreSession->setTealiumReviewDetail($reivewDetail);
        $this->_coreSession->setTealiumReviewRating($reviewRating);
        $this->_coreSession->setTealiumReviewProductId($productId);
    }
}
