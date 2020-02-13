<?php

namespace Tealium\Tags\Plugin\App\Action;
use Tealium\Tags\Model\Checkout\Context as CheckoutSessionContext;

class Context
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;
	
	protected $_checkoutSession;

    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Http\Context $httpContext
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Http\Context $httpContext,
		\Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->customerSession = $customerSession;
        $this->httpContext = $httpContext;
		$this->_checkoutSession = $checkoutSession;
    }

    /**
     * @param \Magento\Framework\App\ActionInterface $subject
     * @param callable $proceed
     * @param \Magento\Framework\App\RequestInterface $request
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDispatch(
        \Magento\Framework\App\ActionInterface $subject,
        \Closure $proceed,
        \Magento\Framework\App\RequestInterface $request
    ) {
		
        $customerId = $this->customerSession->getCustomerId();
        if(!$customerId) {
            $customerId = 0;
        }
		$quote = $this->_checkoutSession->getQuote();
		$ItemsQty = $quote->getItemsQty();
		
        $this->httpContext->setValue(
           'quote',
            $this->_checkoutSession,
            false
        );
		
        return $proceed($request);
    }

}
