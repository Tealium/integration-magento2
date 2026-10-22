<?php
namespace Tealium\Tags\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Tealium\Tags\Helper\Data as TealiumDataHelper;

class Api implements ObserverInterface
{
    protected $_helper;

    protected $_storeManager;

    public function __construct(
        TealiumDataHelper $helper,
        StoreManagerInterface $storeManager
    ) {
        $this->_helper = $helper;
        $this->_storeManager = $storeManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // If the "tealium_api" parameter is set to true, set the response
        // to only contain relevant Tealium logic.
        if (!$this->_helper->isApiRequest($this->_storeManager->getStore())) {
            return $this;
        }

        $response = $observer->getData('response');
        if (!$response || !method_exists($response, 'getBody')) {
            return $this;
        }

        $html = (string)$response->getBody();
        if (!preg_match('/\/\/TEALIUM_START(.*)\/\/TEALIUM_END/is', $html, $matches)) {
            // not a page with a Tealium data layer, leave the response alone
            return $this;
        }

        $response->setBody($matches[1]);
        // served as a script; browsers refuse text/html scripts under "nosniff"
        $response->setHeader('Content-Type', 'application/javascript; charset=UTF-8', true);

        return $this;
    }
}
