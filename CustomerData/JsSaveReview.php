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
        $nickName = $this->_coreSession->getTealiumReviewerNickname();
        $this->_coreSession->unsTealiumReviewerNickname();

        $title = $this->_coreSession->getTealiumReviewTitle();
        $this->_coreSession->unsTealiumReviewTitle();

        $detail = $this->_coreSession->getTealiumReviewDetail();
        $this->_coreSession->unsTealiumReviewDetail();

        $rating = $this->_coreSession->getTealiumReviewRating();
        $this->_coreSession->unsTealiumReviewRating();

        $productId = $this->_coreSession->getTealiumReviewProductId();
        $this->_coreSession->unsTealiumReviewProductId();

        $result = [];
        
        if ($productId) {
            $result = [ 
                'data' => [
                    'review_title' => $title,
                    'review_nickname' => $nickName,
                    'review_detail' => $detail,
                    'review_rating' => $rating,
                    'review_productid' => $productId,
                    'tealium_event' => 'save_review'
                ]
            ];
        }
        
        return $result;
    }
}
