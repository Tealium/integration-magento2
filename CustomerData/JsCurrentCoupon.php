<?php

namespace Tealium\Tags\CustomerData;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Tealium\Tags\Helper\Product;
use Magento\SalesRule\Model\CouponFactory;
use Magento\SalesRule\Model\RuleFactory;

class JsCurrentCoupon extends AbstractSection
{

    protected $_checkoutSession;

    protected $_rule;

    protected $_coupon;

    public function __construct(
        CheckoutSession $checkoutSession,
        CouponFactory $coupon,
        RuleFactory $rule
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_coupon = $coupon;
        $this->_rule = $rule;
    }

    protected function buildSectionData()
    {
        
        $quote = $this->_checkoutSession->getQuote();
        $couponName = $quote->getCouponCode();

        $result = [];

        if ($couponName) {
            $ruleId = $this->_coupon->create()->loadByCode($couponName)->getRuleId();
            $rule = $this->_rule->create()->load($ruleId);
            $discountAmount = $rule->getDiscountAmount();

            $result['data'] = [];
            $result['data']['coupon_name'] = [$couponName];
            $result['data']['coupon_amount'] = [(string)number_format((float)$discountAmount, 2, '.', '')];
            $result['data']['tealium_event'] = 'submit_coupon';
        }

        return $result;
    }
}
