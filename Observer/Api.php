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
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->_objectManager = $objectManager;
        $this->_request = $request;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // if the "tealium_api" parameter is set to true, set the response
        // to only contain relevant Tealium logic.
        if ($this->_request->getParam('tealium_api') == "true") {
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
