<?php

namespace Tealium\Tags\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Session\SessionManagerInterface as CoreSession;
use Magento\Framework\Registry;
use Magento\Review\Model\Rating\OptionFactory as RatingOptionFactory;

class ReviewSaveBefore extends AbstractObserver
{
    protected $_coreSession;

    protected $_coreRegistry;

    protected $_ratingOptionFactory;

    public function __construct(
        CoreSession $coreSession,
        Registry $coreRegistry,
        RatingOptionFactory $ratingOptionFactory
    ) {
        $this->_coreSession = $coreSession;
        $this->_coreRegistry = $coreRegistry;
        $this->_ratingOptionFactory = $ratingOptionFactory;
    }

    /**
     * This is the method that fires when the event runs.
     *
     * @param Observer $observer
     */
    protected function track(Observer $observer)
    {
        /** @var \Magento\Review\Model\Review $review */
        $review = $observer->getDataByKey('object');
        $product = $this->_coreRegistry->registry('current_product');
        if (!$review || !$product) {
            return;
        }

        $this->_coreSession->setTealiumReviewerNickname($review->getNickname());
        $this->_coreSession->setTealiumReviewTitle($review->getTitle());
        $this->_coreSession->setTealiumReviewDetail($review->getDetail());
        $this->_coreSession->setTealiumReviewRating($this->getRatingValue($review->getRatings()));
        $this->_coreSession->setTealiumReviewProductId($product->getId());
    }

    /**
     * The star value (1-5) of the first rating given, or "" when the review
     * has no rating. Ratings are submitted as rating id => option id.
     *
     * @param array|null $ratings
     * @return int|string
     */
    private function getRatingValue($ratings)
    {
        if (!is_array($ratings) || empty($ratings)) {
            return "";
        }

        $option = $this->_ratingOptionFactory->create()->load((int)reset($ratings));
        return $option->getId() ? (int)$option->getValue() : "";
    }
}
