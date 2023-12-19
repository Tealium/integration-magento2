<?php
namespace Tealium\Tags\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Request\Http;

class Api implements ObserverInterface
{
    protected $_request;

    public function __construct(
        Http $request
    ) {
        $this->_request = $request;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // If the "tealium_api" parameter is set to true, set the response
        // to only contain relevant Tealium logic.
        if ($this->_request->getParam('tealium_api') == "true") {
            $response = $observer->getData('response');
            $html = $response->getBody();
            preg_match('/\/\/TEALIUM_START(.*)\/\/TEALIUM_END/is', $html, $matches);
            //$javaScript = "// Tealium Magento Callback API";
            $javaScript = "";
            $javaScript .= $matches[1];

            $response->setBody($javaScript);
        }

        return $this;
    }
}
