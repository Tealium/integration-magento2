<?php

namespace Tealium\Tags\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Framework\Session\SessionManagerInterface as CoreSession;

class JsSaveNewsletter implements SectionSourceInterface
{

    protected $_coreSession;

    public function __construct(
        CoreSession $coreSession
    ) {
        $this->_coreSession = $coreSession;
    }

    public function getSectionData()
    {

        $id = $this->_coreSession->getTealiumNewsletterId();
        $this->_coreSession->unsTealiumNewsletterId();

        $result = [];
        
        if ($id) {
            
            $result['data']['newsletter_id'] = $id;
            $result['data']['tealium_event'] = 'email_signup';
        }
        
        return $result;
    }
}
