<?php

namespace Tealium\Tags\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Session\SessionManagerInterface as CoreSession;

class JsSaveReview implements SectionSourceInterface
{

    protected $_customerSession;

    protected $_coreSession;

    public function __construct(
        CustomerSession $customerSession,
        CoreSession $coreSession
    ) {
        $this->_customerSession = $customerSession;
        $this->_coreSession = $coreSession;
    }

    public function getSectionData()
    {
        $nickName = $this->_coreSession->getTealiumReviewerNickname($reviewerNickname);
        $this->_coreSession->unsTealiumReviewerNickname($reviewerNickname);

        $title = $this->_coreSession->getTealiumReviewTitle($reviewTitle);
        $this->_coreSession->unsTealiumReviewTitle($reviewTitle);

        $detail = $this->_coreSession->getTealiumReviewDetail($reivewDetail);
        $this->_coreSession->unsTealiumReviewDetail($reivewDetail);

        $rating = $this->_coreSession->getTealiumReviewRating($reviewRating);
        $this->_coreSession->unsTealiumReviewRating($reviewRating);

        $productId = $this->_coreSession->getTealiumReviewProductId($productId);
        $this->_coreSession->unsTealiumReviewProductId($productId);

        $result = [];
        
        if ($productId) {
            $result = [ 
                'data' => [
                    'review_title' => $reviewTitle,
                    'review_nickname' => $reviewerNickname,
                    'review_detail' => $reivewDetail,
                    'review_rating' => $reviewRating,
                    'review_productid' => $productId,
                    'tealium_event' => 'save_review'
                ]
            ];
        }
        
        return $result;
    }
}
