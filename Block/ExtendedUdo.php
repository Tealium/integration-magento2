<?php
namespace Tealium\Tags\Block;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class ExtendedUdo extends Udo
{
    public function __construct(
        Context $context,
        DefaultUdo $defaultUdo,
        array $data = []
    ) {
        $this->merge($defaultUdo->getUdoData());

        $this->merge([
           "other_var" => "other_value",
           "page_type" => "override_page_type"
        ]);

        parent::__construct($context, $data);
    }
}
