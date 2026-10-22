<?php

namespace Tealium\Tags\CustomerData;

use Magento\Customer\Model\Session as CustomerSession;
use Tealium\Tags\Helper\TealiumData;

/**
 * Details of the logged in customer, used by the user_logout event
 */
class JsCustomer extends AbstractSection
{
    protected $_customerSession;

    protected $_tealiumData;

    public function __construct(
        CustomerSession $customerSession,
        TealiumData $tealiumData
    ) {
        $this->_customerSession = $customerSession;
        $this->_tealiumData = $tealiumData;
    }

    protected function buildSectionData()
    {
        if (!$this->_customerSession->isLoggedIn()) {
            return [];
        }

        $customer = $this->_customerSession->getCustomerData();

        return [
            'customer_id' => (string)$customer->getId(),
            'customer_type' => $this->_tealiumData->getCustomerGroupCode($customer->getGroupId()),
            'customer_email' => $this->_tealiumData->formatEmail($customer->getEmail())
        ];
    }
}
