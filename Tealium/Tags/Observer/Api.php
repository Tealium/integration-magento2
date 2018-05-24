<?php
/**
 * Created by PhpStorm.
 * User: svatoslavzilicev
 * Date: 18.08.17
 * Time: 16:50
 */

namespace Tealium\Tags\Observer;

use Magento\Framework\Event\ObserverInterface;

class Api implements ObserverInterface
{

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->_objectManager = $objectManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // if the "tealium_api" parameter is set to true, set the response
        // to only contain relevant Tealium logic.
        if (
            isset($_REQUEST["tealium_api"])
            && $_REQUEST["tealium_api"] == "true"
        ) {
            $response = $observer->getData('response');
            $html = $response->getBody();
            preg_match('/\/\/TEALIUM_START(.*)\/\/TEALIUM_END/is', $html, $matches);
            $javaScript = "// Tealium Magento Callback API";
            $javaScript .= $matches[1];
            $response->setBody($javaScript);
        }

        return $this;
    }

}