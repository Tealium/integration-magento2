<?php

namespace Tealium\Tags\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Framework\Session\SessionManagerInterface as CoreSession;

class JsLoginAccount implements SectionSourceInterface
{

    protected $_coreSession;

    public function __construct(
        CoreSession $coreSession
    ) {
        $this->_coreSession = $coreSession;
    }

    public function getSectionData()
    {
        $email = $this->_coreSession->getTealiumLoginEmail();
        $this->_coreSession->unsTealiumLoginEmail();

        $type = $this->_coreSession->getTealiumLoginType();
        $this->_coreSession->unsTealiumLoginType();

        $id = $this->_coreSession->getTealiumLoginId();
        $this->_coreSession->unsTealiumLoginId();

        $result = [];
        
        if ($id) {
            $result['data']['customer_email'] = $email;
            $result['data']['customer_id'] = $id;
            $result['data']['customer_type'] = $type;
            $result['data']['tealium_event'] = 'user_login';
        }
        
        return $result;
    }
}
