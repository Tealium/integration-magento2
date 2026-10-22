<?php
namespace Tealium\Tags\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Tealium\Tags\Block\TealiumFactory;

class Data extends AbstractHelper
{
    /**
     * Event dispatched while building the data layer. Observers receive
     * "page_type" and a "transport" DataObject; any data set on the transport
     * is merged into the data layer.
     */
    const EVENT_CUSTOM_UDO = 'tealium_tags_custom_udo';

    protected $tealium;
    protected $store;
    protected $page;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var TealiumFactory
     */
    protected $tealiumFactory;

    /**
     * @var SecureHtmlRenderer
     */
    protected $secureRenderer;

    /**
     * @var array file path => udo elements defined by that file
     */
    private $customUdoFiles = [];

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        TealiumFactory $tealiumFactory,
        SecureHtmlRenderer $secureRenderer
    ) {
        $this->_storeManager = $storeManager;
        $this->tealiumFactory = $tealiumFactory;
        $this->secureRenderer = $secureRenderer;
        parent::__construct(
            $context
        );
    }

    public function init(&$store, &$page, $pageType)
    {
        $account = $this->getAccount($store);
        $profile = $this->getProfile($store);
        $env = $this->getEnv($store);

        // added first party url domain
        $fp_url = $this->getFirstPartyDomain($store);

        $data = [
            "store" => $store,
            "page" => $page
        ];

        $this->store = $store;
        $this->page = $page;
        $this->tealium = $this->tealiumFactory->create()->init($account, $profile, $fp_url, $env, $pageType, $data);

        return $this;
    }

    public function addCustomDataFromSetup(&$store, $pageType)
    {
        /***** #mynotes *****
         * the "$data" variable is referenced in the custom UDO
         * definition file
         */
        $data = [
            "store" => $this->store,
            "page" => $this->page
        ];

        if ($this->externalUdoEnabled($store)) {
            // To define a custom udo, define the "$udoElements" variable, which
            // is an associative array with page types as keys and functions
            // that return a udo for the page types as the value.
            $udoElements = $this->loadUdoElementsFromFile((string)$this->getUDOPath($store), $data);

            if (!is_array($udoElements) || !self::isAssocArray($udoElements)) {
                $udoElements = [];
            }

            // if a custom udo is defined for the page type, set the udo
            if (isset($udoElements[$pageType])) {
                $this->tealium->setCustomUdo($udoElements[$pageType]);
            }
        }

        // modules can extend the data layer by observing this event
        $transport = new DataObject();
        $this->_eventManager->dispatch(self::EVENT_CUSTOM_UDO, [
            'page_type' => $pageType,
            'store' => $this->store,
            'page' => $this->page,
            'transport' => $transport
        ]);
        if ($transport->getData()) {
            $this->tealium->updateUdo($transport->getData());
        }

        return $this;
    }

    /*
     * Set custom data by updating the udo of the Tealium object belonging to
     * "this" helper
     */
    public function addCustomDataFromObject($udoObject)
    {
        if (is_array($udoObject) && self::isAssocArray($udoObject)) {
            $this->tealium->updateUdo($udoObject);
        }

        return $this;
    }

    /*
     * Determine if an array is an associative array
     */
    protected static function isAssocArray($array)
    {
        $keys = array_keys($array);
        return array_keys($keys) !== $keys;
    }

    /**
     * Include the custom UDO definition file and return the "$udoElements"
     * it defines.
     *
     * Only .php files inside the Magento installation are included, and each
     * file is included at most once per request.
     *
     * @param string $filePath
     * @param array $data available to the file as "$data"
     * @return array|null
     */
    private function loadUdoElementsFromFile($filePath, array $data)
    {
        if ($filePath === '') {
            return null;
        }

        $realPath = realpath($filePath);
        $root = realpath(BP);
        if ($realPath === false
            || !is_file($realPath)
            || strtolower(pathinfo($realPath, PATHINFO_EXTENSION)) !== 'php'
            || $root === false
            || strpos($realPath, $root . DIRECTORY_SEPARATOR) !== 0
        ) {
            $this->_logger->warning(
                'Tealium custom UDO file was not loaded. It must be an existing .php file inside the Magento root.',
                ['path' => $filePath]
            );
            return null;
        }

        if (!array_key_exists($realPath, $this->customUdoFiles)) {
            try {
                $this->customUdoFiles[$realPath] = (static function ($__tealiumUdoFile, $data) {
                    include $__tealiumUdoFile;
                    return isset($udoElements) ? $udoElements : null;
                })($realPath, $data);
            } catch (\Throwable $e) {
                $this->_logger->error('Tealium custom UDO file failed: ' . $e->getMessage(), ['path' => $realPath]);
                $this->customUdoFiles[$realPath] = null;
            }
        }

        return $this->customUdoFiles[$realPath];
    }

    /*
     * Check if the udo is enabled. Used to determine if udo javascript should
     * be rendered.
     */
    public function isEnabled($store)
    {
        return $this->scopeConfig->getValue('tealium_tags/general/enable', ScopeInterface::SCOPE_STORE, $store->getId());
    }

    /*
     * One Page Checkout should be explicitly enabled to render a udo on
     * the page
     */
    public function enableOnePageCheckout($store)
    {
        return $this->scopeConfig->getValue('tealium_tags/general/onepage', ScopeInterface::SCOPE_STORE, $store->getId());
    }

    /*
     * Returns true if an external udo is enabled. Used to override the default
     * udo and allow for a customized udo.
     */
    public function externalUdoEnabled($store)
    {
        return $this->scopeConfig->getValue('tealium_tags/general/custom_udo_enable', ScopeInterface::SCOPE_STORE, $store->getId());
    }

    /*
     * Return the url used to download the tag config. Rendered as part of the
     * universal code snippet.
     */
    public function getTealiumBaseUrl($store)
    {
        $account = $this->getAccount($store);
        $profile = $this->getProfile($store);
        $env = $this->getEnv($store);

        $fp_url = trim((string)$this->getFirstPartyDomain($store));
        if ($fp_url !== "") {
            return "https://". $fp_url ."/$profile/$env/utag.js";
        } else {
            return "//tags.tiqcdn.com/utag/$account/$profile/$env/utag.js";
        }
    }

    /* Get Customer plain text email setting */
    public function getCustomerTxtEmail($store)
    {
        return $this->scopeConfig->getValue('tealium_tags/general/plain_txt_email', ScopeInterface::SCOPE_STORE, $store->getId());
    }

    /* return the current store */
    public function getStore()
    {
        return $this->_storeManager->getStore();
    }

    /*
     * While "this" helper provides a single interface to utility functions,
     * the "tealium" object manages udo operations. This function returns
     * the tealium object for times when it's useful to work with the tealium
     * object directly.
     */
    public function getTealiumObject()
    {
        return $this->tealium;
    }

    /*
     * Return the account name, typically the client company name.
     */
    public function getAccount($store)
    {
        return $this->scopeConfig->getValue('tealium_tags/general/account', ScopeInterface::SCOPE_STORE, $store->getId());
    }

    /*
     * Return the profile name. Typically "main", or often the site name
     * if there are multiple profiles.
     */
    public function getProfile($store)
    {
        return $this->scopeConfig->getValue('tealium_tags/general/profile', ScopeInterface::SCOPE_STORE, $store->getId());
    }

    /*
     * Return the environment. Typically "dev", "qa", or "prod".
     */
    public function getEnv($store)
    {
        return $this->scopeConfig->getValue('tealium_tags/general/env', ScopeInterface::SCOPE_STORE, $store->getId());
    }

    /*
     * When overriding the default udo with a custom one, the code that
     * overrides the default must live somewhere. This function returns
     * the path to the file on the server in which a custom udo is defined.
     */
    public function getUDOPath($store)
    {
        return $this->scopeConfig->getValue('tealium_tags/general/udo', ScopeInterface::SCOPE_STORE, $store->getId());
    }

    /*
     * When developing, it's sometimes useful to only view the rendered
     * universal code snippet and udo instead of the entire page. This can
     * be done by appending the query param "?tealium_api=true" to the end
     * of the url in the browser. However this feature is only enabled if
     * the "api_enable" config is set to true.
     *
     * This function returns true when the api is enabled.
     */
    public function getAPIEnabled($store)
    {
        return $this->scopeConfig->getValue('tealium_tags/general/api_enable', ScopeInterface::SCOPE_STORE, $store->getId());
    }

    /*
     * True when this request should be answered with just the data layer
     * javascript (see \Tealium\Tags\Observer\Api). The api is used by the
     * "external script" mode, and can also be turned on on its own with
     * tealium_tags/general/api_enable.
     */
    public function isApiRequest($store)
    {
        return $this->_getRequest()->getParam('tealium_api') == "true"
            && $this->isEnabled($store)
            && ($this->getIsExternalScript($store) || $this->getAPIEnabled($store));
    }

    /*
     * Place Tealium code in external javaScript file
     * NOTE Order confirmation page will always load script on page
     */
    public function getIsExternalScript($store)
    {
        return $this->scopeConfig->getValue(
            'tealium_tags/general/external_script',
            ScopeInterface::SCOPE_STORE,
            $store->getId()
        );
    }

    /* Get First party domain URL */
    public function getFirstPartyDomain($store)
    {
        return $this->scopeConfig->getValue('tealium_tags/general/first_party_domain', ScopeInterface::SCOPE_STORE, $store->getId());
    }

    /*
     * When placing the Tealium code in an external javaScript file, it's
     * either loaded syncronously or asyncronously.
     *
     * This function returns either "async" or "sync" depending on the config.
     */
    public function getExternalScriptType($store)
    {
        $async = $this->scopeConfig->getValue(
            'tealium_tags/general/external_script_type',
            ScopeInterface::SCOPE_STORE,
            $store->getId()
        );
        return $async ? "async" : "sync";
    }

    /*
     * Sometimes it's useful to send the entire udo to a server for diagnostics.
     * This function returns a script that will send the url encoded udo to a
     * specified server if the feature is enabled in the config.
     *
     * The user agent is read in the browser: the page may be served from the
     * full page cache, so request headers must never be written into it.
     */
    public function getDiagnosticTag($store)
    {
        if (!$this->scopeConfig->getValue(
            'tealium_tags/general/diagnostic_enable',
            ScopeInterface::SCOPE_STORE,
            $store->getId()
        )) {
            return "";
        }

        $url = (string)$this->scopeConfig->getValue(
            'tealium_tags/general/diagnostic_tag',
            ScopeInterface::SCOPE_STORE,
            $store->getId()
        );
        $flags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
        $script = '(function(){var i=new Image();i.src=' . json_encode($url, $flags)
            . "+'?origin=server&user_agent='+encodeURIComponent(navigator.userAgent)"
            . "+'&data='+" . json_encode(urlencode($this->tealium->render("json")), $flags) . ';})();';

        return $this->secureRenderer->renderTag('script', ['type' => 'text/javascript'], $script, false);
    }
}
