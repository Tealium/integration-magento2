<?php
namespace Tealium\Tags\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Session\SessionManagerInterface as CoreSession;
use Magento\Customer\Api\GroupRepositoryInterface;

class LoginAccount implements ObserverInterface
{

    protected $_customerSession;

    protected $_groupFactory;

    protected $_coreSession;

    public function __construct(
        CustomerSession $customerSession,
        CoreSession $coreSession,
        GroupRepositoryInterface $groupFactory
    ) {
        $this->_customerSession = $customerSession;
        $this->_groupFactory = $groupFactory;
        $this->_coreSession = $coreSession;
    }

    /**
     *
     * Add data to section array for custumer data use
     *
     */

    public function execute(Observer $observer)
    {
        $customerObject = $observer->getData('customer');
        $email = $customerObject->getEmail();
        $id = $customerObject->getId();

        //get user type

        $groupId = $customerObject->getGroupId();
        $groupObject = $this->_groupFactory->getById($groupId);
        $groupName = $groupObject->getCode();

        $this->_coreSession->setTealiumLoginEmail($email);
        $this->_coreSession->setTealiumLoginType($groupName);
        $this->_coreSession->setTealiumLoginId($id);

        return $this;
    }
}
