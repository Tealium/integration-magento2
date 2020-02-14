<?php
/**
 * Created by PhpStorm.
 * User: svatoslavzilicev
 * Date: 18.08.17
 * Time: 16:40
 */

namespace Tealium\Tags\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use \Tealium\Tags\lib\Tealium\TealiumData;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\TemplatePathStack;
use Zend\View\Model\ViewModel;

class Data extends AbstractHelper
{
    protected $tealium;
    protected $store;
    protected $page;

    protected $_store;
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Registry
     */

    protected $_registry;

    protected $_checkoutSession;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Api\Data\StoreInterface $store,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_store = $store;
        $this->_objectManager = $objectManager;
        $this->_registry = $registry;
        $this->_checkoutSession = $checkoutSession;
        parent::__construct(
            $context
        );
    }

    public function init(&$store, &$page = [], $pageType)
    {
        // initialize basic profile settings
        $account = $this->getAccount($store);
        $profile = $this->getProfile($store);
        $env = $this->getEnv($store);

        $data = [
            "store" => $store,
            "page" => $page
        ];

        $this->store = $store;
        $this->page = $page;
        $this->tealium = $this->_objectManager->create('Tealium\Tags\Block\Tealium')->init($account, $profile, $env, $pageType, $data);

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

        if ($this->scopeConfig->getValue('tealium_tags/general/custom_udo_enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store->getId())) {
            // To define a custom udo, define the "$udoElements" variable, which
            // is an associative array with page types as keys and functions
            // that return a udo for the page types as the value.

            // One way to define a custom udo is to include an external file
            // that defines "$udoElements"
		
			$filePath = $this->scopeConfig->getValue(
                'tealium_tags/general/udo',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store->getId()
            );
			$fileName = basename($filePath);         // $file is set to "index.php"
			$filePath = dirname($filePath);
			if($fileName != "" && $filePath != "" && file_exists($filePath))
			{
				$resolver = $this->_objectManager->create('Zend\View\Resolver\TemplatePathStack');
				$resolver->addPath($filePath);
				$viewApp = $this->_objectManager->create('Zend\View\Renderer\PhpRenderer');
				$viewApp->setResolver($resolver, $data);
				$viewApp->setVars($data);
				$viewApp->render($fileName);
				/*
				$viewModel = $this->_objectManager->create('Zend\View\Model\ViewModel');
				$viewModel->setVariable('foo', 'bar');
				$viewModel->setTemplate($fileName);
				$viewApp->render($viewModel);
				*/
				$udoElements = $viewApp->vars('udoElements');

			}
			
       		/*
            include_once($this->scopeConfig->getValue(
                'tealium_tags/general/udo',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store->getId()
            ));
			*/

            // Another way to define a custom udo is to define a "getCustomUdo"
            // method, which is used to set "$udoElements"
            if (method_exists($this, "getCustomUdo")) {
                $customUdoElements = getCustomUdo();
                if (is_array($customUdoElements) &&
                    self::isAssocArray($customUdoElements)
                ) {
                    $udoElements = $customUdoElements;
                }
            } elseif (!isset($udoElements)
                || (
                    isset($udoElements)
                    && !self::isAssocArray($udoElements)
                )
            ) {
                $udoElements = [];
            }
		
            // if a custom udo is defined for the page type, set the udo
            if (isset($udoElements[$pageType])) {
                $this->tealium->setCustomUdo($udoElements[$pageType]);
            }
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

    /*
     * Check if the udo is enabled. Used to determine if udo javascript should
     * be rendered.
     */
    public function isEnabled($store)
    {
        return $this->scopeConfig->getValue('tealium_tags/general/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store->getId());
    }

    /*
     * One Page Checkout should be explicitly enabled to render a udo on
     * the page
     */
    public function enableOnePageCheckout($store)
    {
        return $this->scopeConfig->getValue('tealium_tags/general/onepage', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store->getId());
    }

    /*
     * Returns true if an external udo is enabled. Used to override the default
     * udo and allow for a customized udo.
     */
    public function externalUdoEnabled($store)
    {
        return $this->scopeConfig->getValue('tealium_tags/general/custom_udo_enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store->getId());
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
        return "//tags.tiqcdn.com/utag/$account/$profile/$env/utag.js";
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
        return $this->scopeConfig->getValue('tealium_tags/general/account', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store->getId());
    }

    /*
     * Return the profile name. Typically "main", or often the site name
     * if there are multiple profiles.
     */
    public function getProfile($store)
    {
        return $this->scopeConfig->getValue('tealium_tags/general/profile', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store->getId());
    }

    /*
     * Return the environment. Typically "dev", "qa", or "prod".
     */
    public function getEnv($store)
    {
        return $this->scopeConfig->getValue('tealium_tags/general/env', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store->getId());
    }

    /*
     * When overriding the default udo with a custom one, the code that
     * overrides the default must live somewhere. This function returns
     * the path to the file on the server in which a custom udo is defined.
     */
    public function getUDOPath($store)
    {
        return $this->scopeConfig->getValue('tealium_tags/general/udo', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store->getId());
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
        return $this->scopeConfig->getValue('tealium_tags/general/api_enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store->getId());
    }

    /*
     * Place Tealium code in external javaScript file
     * NOTE Order confirmation page will always load script on page
     */
    public function getIsExternalScript($store)
    {
        return $this->scopeConfig->getValue(
            'tealium_tags/general/external_script',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store->getId()
        );
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
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store->getId()
        );
        return $async ? "async" : "sync";
    }

    /*
     * Sometimes it's useful to send the entire udo to a server for diagnostics.
     * This function returns a tag in the form of an html <img> element that
     * will send the url encoded udo to a specified server if the feature is
     * enabled in the config.
     */
    public function getDiagnosticTag($store)
    {
        if ($this->scopeConfig->getValue(
            'tealium_tags/general/diagnostic_enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store->getId()
        )
        ) {
            $utag_data = urlencode($this->tealium->render("json"));
            $url = $this->scopeConfig->getValue(
                'tealium_tags/general/diagnostic_tag',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store->getId()
            )
                . '?origin=server&user_agent='
                . $_SERVER ['HTTP_USER_AGENT']
                . '&data='
                . $utag_data;
            return '<img src="' . $url . '" style="display:none"/>';
        } else {
            return "";
        }
    }
}
