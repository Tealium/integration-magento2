<?php
namespace Tealium\Tags\Block;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
 
class Utag extends Template
{
    private $account;
    private $profile;
    private $environment;
    
    public function __construct(Context $context, array $data = [])
    {
        $this->account = "test_account";
        $this->profile = "main";
        $this->environment = "prod";
        parent::__construct($context, $data);
    }
    
    protected function _prepareLayout()
    {
 
    }
    
    public function getAccount()
    {
        return $this->account;
    }
    
    public function getProfile()
    {
        return $this->profile;
    }
    
    public function getEnvironment()
    {
        return $this->environment;
    }
}