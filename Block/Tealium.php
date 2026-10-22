<?php
/**
 * Created by PhpStorm.
 * User: svatoslavzilicev
 * Date: 25.08.17
 * Time: 12:06
 */

namespace Tealium\Tags\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class Tealium extends \Magento\Framework\View\Element\Template
{
    /**
     * Flags used whenever the data layer is written into a <script> block, so
     * that no value can terminate the script or inject markup.
     */
    const JSON_FLAGS = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_PRETTY_PRINT;

    /**
     * Page type => data builder method on \Tealium\Tags\Helper\TealiumData
     */
    const PAGE_TYPE_BUILDERS = [
        'Home' => 'getHome',
        'Search' => 'getSearch',
        'Category' => 'getCategory',
        'ProductPage' => 'getProductPage',
        'Cart' => 'getCartPage',
        'Confirmation' => 'getOrderConfirmation',
        'Customer' => 'getCustomerData'
    ];

    // Declare related properties and define constructor
    private $account; // account name
    private $profile; // profile name
    private $fpurl;
    private $target;
    private $udo; // object (assoc array) of udo variables (key/val pairs)
    private $customUdo;

    protected $_helper;

    /**
     * @var SecureHtmlRenderer
     */
    protected $secureRenderer;

    /**
     * @var \Tealium\Tags\Helper\Data
     */
    protected $configHelper;

    public function __construct(
        Context $context,
        \Tealium\Tags\Helper\TealiumData $helper,
        SecureHtmlRenderer $secureRenderer,
        \Tealium\Tags\Helper\Data $configHelper,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->secureRenderer = $secureRenderer;
        $this->configHelper = $configHelper;
        parent::__construct($context, $data);
    }

    public function init(
        $accountInit = false,
        $profileInit = false,
        $fp_url = false,
        $targetInit = false,
        $pageType = "Home",
        &$data = []
    ) {
        $tealiumData = $this->_helper;
        $tealiumData->setStore($data['store']);
        $tealiumData->setPage($data['page']);

        $this->account = $accountInit;
        $this->profile = $profileInit;
        $this->fpurl = trim((string)$fp_url);
        $this->target = $targetInit;

        // only the data layer for the requested page type is built
        $this->pageType($pageType);

        return $this;
    }

    // give an object of key value pairs to update in the udo,
    // or provide a key string and value string of a single udo var to update
    public function updateUdo($objectOrKey = "", $value = "")
    {
        // get udo and put in local scope as "$udoObject"
        $udoObject = $this->udo;

        // set "$udo" depending on form of "$udoObject"
        if ($udoObject instanceof \Closure) {
            $udo = $udoObject();
        } elseif (is_array($udoObject)) {
            $udo = $udoObject;
        } else {
            $udo = [];
        }

        // "$updatedUdo" is an object of key/val pairs of vars to be updated
        // in the udo. It describes just the modifications to the current udo
        // so that it can be updated. (could probably be better named)
        if ($objectOrKey instanceof \Closure) {
            $updatedUdo = $objectOrKey();
        } elseif (is_array($objectOrKey)) {
            $updatedUdo = $objectOrKey;
        } else {
            // in string form $updateUdo indicates to use $objectOrKey
            // as a key/val pair
            $updatedUdo = null;
        }

        // if $updatedUdo is an assoc array, iterate through its key/val
        // pairs and update the udo
        if (is_array($updatedUdo)) {
            foreach ($updatedUdo as $objectKey => $objectValue) {
                $udo[$objectKey] = $objectValue;
            }
        } elseif (is_string($objectOrKey) && $objectOrKey != "") {
            // else use function params as a key/val to update
            $udo[$objectOrKey] = $value;
        }

        // set and return the updated udo
        $this->udo = $udo;

        return $this->udo;
    }

    // take a new udo and replace the old one
    public function setCustomUdo($udo)
    {
        $this->customUdo = $udo;
    }

    // set a new page type
    public function pageType($pageType = "Home")
    {
        $this->udo = null;
        if (isset(self::PAGE_TYPE_BUILDERS[$pageType])) {
            $method = self::PAGE_TYPE_BUILDERS[$pageType];
            $this->udo = $this->_helper->$method();
        }
        if (!$this->udo && $pageType != null) {
            $this->udo = ['page_type' => $pageType];
        }
    }

    /**
     * URL of utag.js for the configured account/profile/environment
     *
     * @return string
     */
    public function getUtagUrl()
    {
        if ($this->fpurl !== '') {
            return "https://{$this->fpurl}/{$this->profile}/{$this->target}/utag.js";
        }
        return "//tags.tiqcdn.com/utag/{$this->account}/{$this->profile}/{$this->target}/utag.js";
    }

    /**
     * Render the data layer.
     *
     * utag.js itself is always loaded client-side by Tealium_Tags/js/utagLib,
     * so "tag" renders nothing and "full" is the same as "udo".
     *
     * @param string|null $type "udo", "full", "json" or "tag"
     * @param bool $external load the data layer from an external script
     * @param string $sync "sync" or "async" for the external script
     * @return string
     */
    public function render($type = null, $external = false, $sync = "sync")
    {
        $isApiRequest = $this->configHelper->isApiRequest($this->_storeManager->getStore());

        // include any customizations
        if (isset($this->customUdo)) {
            $this->updateUdo($this->customUdo);
            $this->customUdo = null;
        }

        $udoObject = $this->udo instanceof \Closure ? ($this->udo)() : $this->udo;
        $udoJson = is_array($udoObject) ? json_encode($udoObject, self::JSON_FLAGS) : "{}";
        if ($udoJson === false) {
            $udoJson = "{}";
        }

        if ($type == "json") {
            return $udoJson;
        }

        if ($type == "tag") {
            return "";
        }

        if (!($this->account && $this->profile && $this->target)) {
            if ($this->udo == null) {
                // Render instructions if Tealium Object was not used correctly
                return <<<EOD
<!-- Tealium Universal Data Object / Data Layer -->
<!-- Account, profile, or environment was not declared in
    object Tealium(\$account, \$profile, \$target, \$pageType) -->
EOD;
            }
        }

        $udoJs = "var utag_jsurl = " . json_encode($this->getUtagUrl(), self::JSON_FLAGS) . ";\n"
            . "var utag_data = $udoJson;";

        // the tealium api returns just the data layer as javascript,
        // see \Tealium\Tags\Observer\Api
        if ($isApiRequest) {
            return "//TEALIUM_START\n" . $udoJs . "\n//TEALIUM_END\n";
        }

        if ($external) {
            // instead of setting utag_data inline, include an external script
            // that requests this same page with "tealium_api=true"
            $attributes = ['type' => 'text/javascript', 'src' => $this->getExternalScriptUrl()];
            if ($sync != "sync") {
                $attributes['async'] = 'async';
            }
            return $this->secureRenderer->renderTag('script', $attributes, ' ', false);
        }

        return "<!-- Tealium Universal Data Object / Data Layer -->\n"
            . $this->secureRenderer->renderTag('script', ['type' => 'text/javascript'], "\n" . $udoJs . "\n", false)
            . "\n<!-- ****************************************** -->";
    }

    /**
     * The current page url with tealium_api=true added to the query string
     *
     * @return string
     */
    private function getExternalScriptUrl()
    {
        $url = $this->_urlBuilder->getCurrentUrl();
        $separator = strpos($url, '?') === false ? '?' : '&';
        return $url . $separator . 'tealium_api=true';
    }
}
