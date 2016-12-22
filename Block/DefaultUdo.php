<?php
namespace Tealium\Tags\Block;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class DefaultUdo extends Udo
{
    public function __construct(Context $context, array $data = [])
    {
        $this->merge([
            "page_type" => "home",
            "page_name" => "Home",
            "site_currency" => "USD",
            "site_region" => "us"
        ]);

        parent::__construct($context, $data);
    }
}
