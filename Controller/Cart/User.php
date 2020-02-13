<?php
namespace Tealium\Tags\Controller\Cart;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Controller\ResultFactory;

class User extends Action
{
    protected $_pageFactory;

    protected $_cart;
    
    protected $_customerSession;

    protected $_groupFactory;

    public function __construct(
        PageFactory $pageFactory,
        Cart $cart,
        Context $context
    ) {
        $this->_pageFactory = $pageFactory;
        $this->_cart = $cart;
        return parent::__construct($context);
    }

    public function execute()
    {
        $this->_customerSession = $this->_objectManager->create('\Magento\Customer\Model\Session');
        $this->_groupFactory = $this->_objectManager->create('Magento\Customer\Api\GroupRepositoryInterface');

        $customerObject = $this->_customerSession->getCustomerData();

        $email = $customerObject->getEmail();
        $id = $customerObject->getId();

        //get user type

        $groupId = $customerObject->getGroupId();
        $groupObject = $this->_groupFactory->getById($groupId);
        $groupName = $groupObject->getCode();

        // echo json_encode(['id' => [$id], 'type' => [$groupName], 'email' => [$email] ]);

        // exit;
        $result = ['customer_id' => $id, 'customer_type' => $groupName, 'customer_email' => $email ];
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result);
        return $resultJson;
    }
}
