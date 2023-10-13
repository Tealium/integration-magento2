<?php

namespace Tealium\Tags\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Session\SessionManagerInterface as CoreSession;
use Tealium\Tags\Helper\Data as TealiumDataHelper;

class JsCreateAccount implements SectionSourceInterface
{

    protected $_customerSession;

    protected $_coreSession;

    /**
     * @var TealiumDataHelper
     */
    protected $tealiumDataHelper;

    public function __construct(
        CustomerSession $customerSession,
        CoreSession $coreSession,
        TealiumDataHelper $tealiumDataHelper
    ) {
        $this->_customerSession = $customerSession;
        $this->_coreSession = $coreSession;
        $this->tealiumDataHelper = $tealiumDataHelper;
    }

    public function getSectionData()
    {
        $email = $this->_coreSession->getTealiumCreateAccEmail();
        $this->_coreSession->unsTealiumCreateAccEmail();

        $type = $this->_coreSession->getTealiumCreateAccType();
        $this->_coreSession->unsTealiumCreateAccType();

        $id = $this->_coreSession->getTealiumCreateAccId();
        $this->_coreSession->unsTealiumCreateAccId();

        $result = [];
        
        if ($id) {

            $store = $this->tealiumDataHelper->getStore();
            $customertxtvalue = $this->tealiumDataHelper->getCustomerTxtEmail($store);
            if ($customertxtvalue == null || $customertxtvalue == 0) {
                $email = hash('sha256', strtolower($email));
            } 
            $result['data']['customer_email'] = (string)$email;
            $result['data']['customer_id'] = (string)$id;
            $result['data']['customer_type'] = (string)$type;
            $result['data']['tealium_event'] = 'user_register';
        }
        
        return $result;
    }
}
