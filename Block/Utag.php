<?php
namespace Tealium\Tags\Block;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Utag extends Template
{   
    public function __construct(
        Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
    }
    
    protected function _prepareLayout()
    {
 
    }
    
    public function getAccount()
    {
        return $this->_scopeConfig->getValue('tags/general/account', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function getProfile()
    {
        return $this->_scopeConfig->getValue('tags/general/profile', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function getEnvironment()
    {
        return $this->_scopeConfig->getValue('tags/general/environment', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}