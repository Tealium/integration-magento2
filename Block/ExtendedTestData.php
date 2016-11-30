<?php
namespace Tealium\Tags\Block;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
 
class ExtendedTestData extends Udo
{
    public function __construct(
        Context $context,
        TestData $testData,
        array $data = []
    ) {   
        $this->merge($testData->getUdoData());
        
        $this->merge([
           "other_var" => "other_value"
        ]);
        
        parent::__construct($context, $data);
    }
}