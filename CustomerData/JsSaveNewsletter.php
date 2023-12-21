<?php

namespace Tealium\Tags\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Framework\Session\SessionManagerInterface as CoreSession;
use Tealium\Tags\Helper\Data as TealiumDataHelper;

class JsSaveNewsletter implements SectionSourceInterface
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

    public function getSectionData()
    {

        if (isset($_COOKIE['email'])) {
            $email = $_COOKIE['email'];
            unset($_COOKIE['email']);
        }

        if (isset($_COOKIE['type'])) {
            $type = $_COOKIE['type'];
            unset($_COOKIE['type']);
        }

        if (isset($_COOKIE['id'])) {
            $id = $_COOKIE['id'];
            unset($_COOKIE['id']);
        }

        $id = $this->_coreSession->getTealiumNewsletterId();
        $this->_coreSession->unsTealiumNewsletterId();

        $email = $this->_coreSession->getTealiumNewsletterEmail();
        $this->_coreSession->unsTealiumNewsletterEmail();

        

        $result = [];
        
        if ($id) {

            $store = $this->tealiumDataHelper->getStore();
            $customertxtvalue = $this->tealiumDataHelper->getCustomerTxtEmail($store);
            if ($customertxtvalue == null || $customertxtvalue == 0) {
                $email = hash('sha256', strtolower($email));
            }
            $result['data']['customer_email'] = $email;
            
            $result['data']['newsletter_id'] = $id;
            $result['data']['tealium_event'] = 'email_signup';
        }
        
        return $result;
    }
}
