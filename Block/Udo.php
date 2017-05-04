<?php
namespace Tealium\Tags\Block;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
 
class Udo extends Template
{
    private $udoData = [];
    
    public function __construct(Context $context, array $data = [])
    {   
        parent::__construct($context, $data);
    }
    
    protected function _prepareLayout()
    {
 
    }
    
    // values in the udo can either be a string or an array of strings
    // return true if the tested value is valid, else return false
    private function validateValue($value)
    {
        if(is_string($value)) {
            // the value is valid if it's a string
            return true;
        } elseif(is_array($value)) {
            // the value is valid if it's an array of strings
            $allElementsAreStrings = true;
            
            foreach($value as $element) {
                $allElementsAreStrings =
                    $allElementsAreStrings && is_string($element);
            }
            
            return $allElementsAreStrings;
        } else {
            // exhausted all valid forms; the value is invalid.
            return false;
        }
    }
    
    // values in the udo can either be a string or an array of strings
    // return true if the tested value is valid, else return false
    private function validateData($data)
    {
        if(is_array($data)) {
            foreach($data as $name => $value) {
                if(!$this->validateValue($value)) {
                    throw new \Exception("All data in a udo must either be a string or array of strings. This constraint was not satisfied when setting \"" . $name . "\"");
                }
            }
        } else {
            throw new \Exception("Must pass data as an array when setting the data of a udo");
        }
    }
    
    // 
    public function merge($data)
    {
        // throw an exception if the data is not formatted correctly
        $this->validateData($data);
        
        foreach($data as $name => $value) {
            $this->udoData[$name] = $value;
        }
        
        return $this;
    }
    
    // The get method for returning the udo data just returns a reference to
    // the private member variable. It's possible for the caller to modify it
    // once it gets a reference, however you're setting yourself up for bad
    // things to happen if you do.
    public function getUdoData()
    {
        return $this->udoData;
    }
}
