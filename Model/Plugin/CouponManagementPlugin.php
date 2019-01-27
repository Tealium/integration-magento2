<?php 

namespace Tealium\Tags\Model\Plugin; 

use Magento\Sales\Model\Order;
use Magento\Quote\Model\CouponManagement;
use Magento\Framework\App\Request\Http;

class CouponManagementPlugin { 

    protected $_request;


    public function __construct(
        Http $request
    ) {
        $this->_request = $request;
    }

    /** 
    * Order success action. * 
    * @return bool */ 
    public function aroundSet(
        CouponManagement $subject, 
        callable $proceed
    ) { 
        echo 'json_encode($data)';
        exit;
        $result = $proceed();
        return $result;
    }
}