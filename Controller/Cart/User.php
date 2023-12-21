<?php
namespace Tealium\Tags\Controller\Cart;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\JsonFactory;

class User extends Action
{
    protected $_pageFactory;
    protected $_cart;
    protected $_customerSession;
    protected $_groupFactory;
    protected $_resultJsonFactory;

    public function __construct(
        PageFactory $pageFactory,
        Cart $cart,
        Context $context,
        Session $customerSession,
        GroupRepositoryInterface $groupFactory,
        JsonFactory $resultJsonFactory
    ) {
        $this->_pageFactory = $pageFactory;
        $this->_cart = $cart;
        $this->_customerSession = $customerSession;
        $this->_groupFactory = $groupFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $customerObject = $this->_customerSession->getCustomerData();

        $email = $customerObject->getEmail();
        $id = $customerObject->getId();

        // Get user type
        $groupId = $customerObject->getGroupId();
        $groupObject = $this->_groupFactory->getById($groupId);
        $groupName = $groupObject->getCode();

        $result = ['customer_id' => $id, 'customer_type' => $groupName, 'customer_email' => $email];
        $resultJson = $this->_resultJsonFactory->create();
        $resultJson->setData($result);

        return $resultJson;
    }
}
