<?php

namespace Tealium\Tags\Model\Checkout;

use Magento\Sales\Model\Order;
use Magento\Quote\Model\CouponManagement;
use Magento\Framework\App\Request\Http;

class Context
{
	public $_checkoutSession;
    protected  $_request;
	const CONTEXT_QUOTE = 'quote';
	
    public function __construct(
        Http $request,
		\Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_request = $request;
		$this->_checkoutSession = $checkoutSession;
    }
}
