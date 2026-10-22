<?php

namespace Tealium\Tags\CustomerData;

use Magento\Framework\Session\SessionManagerInterface as CoreSession;
use Tealium\Tags\Helper\Data as TealiumDataHelper;

class JsLoginAccount extends AbstractSection
{

    protected $_coreSession;
    /**
     * @var TealiumDataHelper
     */
    protected $tealiumDataHelper;

    public function __construct(
        CoreSession $coreSession,
        TealiumDataHelper $tealiumDataHelper
    ) {
        $this->_coreSession = $coreSession;
        $this->tealiumDataHelper = $tealiumDataHelper;
    }

    protected function buildSectionData()
    {
        $email = $this->_coreSession->getTealiumLoginEmail();
        $this->_coreSession->unsTealiumLoginEmail();

        $type = $this->_coreSession->getTealiumLoginType();
        $this->_coreSession->unsTealiumLoginType();

        $id = $this->_coreSession->getTealiumLoginId();
        $this->_coreSession->unsTealiumLoginId();

        $result = [];
        
        if ($id) {

            $store = $this->tealiumDataHelper->getStore();
            $customertxtvalue = $this->tealiumDataHelper->getCustomerTxtEmail($store);
            if ($customertxtvalue == null || $customertxtvalue == 0) {
                $email = hash('sha256', strtolower((string)$email));
            }
            
            $result['data']['customer_email_txt'] = $customertxtvalue;

            

            
            $result['data']['customer_email'] = $email;
            $result['data']['customer_id'] = $id;
            $result['data']['customer_type'] = $type;
            $result['data']['tealium_event'] = 'user_login';
        }
        
        return $result;
    }
}
