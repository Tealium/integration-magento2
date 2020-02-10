<?php
namespace Tealium\Tags\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Session\SessionManagerInterface as CoreSession;
use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Api\GroupRepositoryInterface;

class CreateAccount implements ObserverInterface
{

    //protected $_request;

    protected $_customerSession;

    protected $_productFactory;

    protected $_groupFactory;

    protected $_coreSession;

	public function __construct(
        //Http $request,
        ProductFactory $productFactory,
        CustomerSession $customerSession,
        CoreSession $coreSession,
        GroupRepositoryInterface $groupFactory
    ) {
        $this->_customerSession = $customerSession;
        $this->_productFactory = $productFactory;
        $this->_groupFactory = $groupFactory;
        $this->_coreSession = $coreSession;
       // $this->_request = $request;
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

        $this->_coreSession->setTealiumCreateAccEmail($email);
        $this->_coreSession->setTealiumCreateAccType($groupName);
        $this->_coreSession->setTealiumCreateAccId($id);

        return $this;
    }
}