<?php
/**
 * Created by PhpStorm.
 * User: svatoslavzilicev
 * Date: 25.08.17
 * Time: 12:06
 */

namespace Tealium\Tags\Block;

class Tealium extends \Magento\Framework\View\Element\Template
{
    // Declare related properties and define constructor
    private $account; // account name
    private $profile; // profile name
    private $target;
    private $udo; // object (assoc array) of udo variables (key/val pairs)
    private $udoElements;
    private $customUdo;

    protected $_helper;
    protected $_request;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Tealium\Tags\Helper\TealiumData $helper,
        \Magento\Framework\App\Request\Http $request,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->_request = $request;
        parent::__construct($context, $data);
    }

    public function init(
        $accountInit = false,
        $profileInit = false,
        $targetInit = false,
        $pageType = "Home",
        &$data = []
    ) {

        $tealiumData = $this->_helper;
        $tealiumData->setStore($data['store']);
        $tealiumData->setPage($data['page']);

		$productOnPage = array();
		$productOnPageId = array();
		
		
		$q = $this->getRequest()->getParam('q');
		if($q){
			$categoryProductListBlock = $this->_layout->getBlock('search_result_list');
		} else {
			$categoryProductListBlock = $this->_layout->getBlock('category.products.list');
		}
		 // Fetch the current collection from the block and set pagination
		if(!empty($categoryProductListBlock)){
			$collections = $categoryProductListBlock->getLoadedProductCollection();
			$collections->setCurPage(1)->setPageSize(4);
			
			foreach($collections as $product){
				$productOnPage[] = $product->getSku();
				$productOnPageId[] = $product->getId();
			}
			
		}
        $udoElements = [
            'Home' => $tealiumData->getHome(),
            'Search' =>$tealiumData->getSearch($productOnPage),
            'Category' =>$tealiumData->getCategory($productOnPage, $productOnPageId),
            'ProductPage' => $tealiumData->getProductPage(),
            'Cart' => $tealiumData->getCartPage(),
            'Confirmation' => $tealiumData->getOrderConfirmation(),
            'Customer' =>$tealiumData->getCustomerData()
        ];

        $this->udoElements = $udoElements;
        $this->account = $accountInit;
        $this->profile = $profileInit;
        $this->target = $targetInit;

        if (!($this->udo = $this->udoElements[$pageType])
            && $pageType != null
        ) {
            $this->udo = ['page_type' => $pageType];
        }

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
            $udo = "{}";
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
            $updatedUdo = "{}";
        }
	
        // if $updatedUdo is an assoc array, iterate through its key/val
        // pairs and update the udo
        if (is_array($updatedUdo)) {
            foreach ($updatedUdo as $objectKey => $objectValue) {
                $udo[$objectKey] = $objectValue;
            }
        } elseif ($objectOrKey != "") {
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
        $this->udo = $this->udoElements[$pageType];
        if (!$this->udo && $pageType != null) {
            $this->udo = ['page_type' => $pageType];
        }
    }

    public function render($type = null, $external = false, $sync = "sync")
    {
		
        // check if the tealium api is being used and render just the data layer
        if ($this->_request->getParam('tealium_api') != "true" && $external) {
            // not using the api, and the script is an external script
            // instead of setting utag_data with a udo object, include
            // the external script instead
            $type = "udo";
            $is_async = ($sync == "sync") ? "" : "async";
            $udo = "<script type=\"text/javascript\" src=\"";
            $udo .= $_SERVER["REQUEST_URI"];

            if ($_SERVER["QUERY_STRING"]) {
                // append more query params with '&' if query params exist
                $udo .= "&";
            } else {
                // else start query params with a '?'
                $udo .= "?";
            }

            // append the "tealium_api=true" query param to the url
            $udo .= "tealium_api=true\" $is_async></script>";
        } else {
            // Either using the api, the udo is not an external script, or both.
            // Therefore the udo object must be generated as javascript code.
				
            // include any customizations
            if (isset($this->customUdo)) {
                $this->updateUdo($this->customUdo);
            }

            $udoObject = $this->udo;

            // determine the udo obj's type and convert to JSON
            if ($udoObject instanceof \Closure) {
                // pretty print in versions of php that support it
                if (defined('JSON_PRETTY_PRINT')) {
                    $udoJson = json_encode($udoObject(), JSON_PRETTY_PRINT);
                } else {
                    $udoJson = json_encode($udoObject());
                }
            } elseif (is_array($udoObject)) {
                // pretty print in versions of php that support it
                if (defined('JSON_PRETTY_PRINT')) {
                    $udoJson = json_encode($udoObject, JSON_PRETTY_PRINT);
                } else {
                    $udoJson = json_encode($udoObject);
                }
            } else {
                $udoJson = "{}";
            }
/* echo "test<pre>";
			print_r($udoObject);
			die; */
            // create the javascript for utag_data
            $udoJs = "var utag_data = $udoJson;";

            // create the entire script tag to render for utag_data
            $udo = <<<EOD
<!-- Tealium Universal Data Object / Data Layer -->
<div class="utagLib" style="display:none;">//tags.tiqcdn.com/utag/$this->account/$this->profile/$this->target/utag.js</div>
<script type="text/javascript">
$udoJs
console.log(window);
</script>
<!-- ****************************************** -->
EOD;
        }

        // Render Tealium tag in javaScript
        $insert_tag = <<<EOD
(function(a,b,c,d){
    a='//tags.tiqcdn.com/utag/$this->account/$this->profile/$this->target/utag.js';
    b=document;c='script';d=b.createElement(c);d.src=a;d.type='text/java'+c; 
    d.async=true;
    a=b.getElementsByTagName(c)[0];a.parentNode.insertBefore(d,a);
})();
EOD;

        // enclose the tealium tag js in a <script></script> tag
        $tag = <<<EOD
<!-- Async Load of Tealium utag.js library -->
<script type="text/javascript">
$insert_tag
</script>
<!-- ************************************* -->
EOD;

        // if using the tealium_api, return a page with only the javascript
        if ($this->_request->getParam('tealium_api') == "true") {
            $tag = "\n\n" . $insert_tag . "\n//TEALIUM_END\n";
            $udo = "//TEALIUM_START\n" . "\n" . $udoJs;
        }

        // Determine what code to return
        if ($this->account && $this->profile && $this->target) {
            if ($type == "tag") {
                $renderedCode = $tag;
				 $renderedCode = "";
            } elseif ($type == "udo") {
                // starts with "var utag_data = " followed by json
                $renderedCode = $udo;
            } elseif ($type == "json") {
                // just the json object
                $renderedCode = $udoJson;
            } else {
                $renderedCode = $udo . "\n" . $tag;
            }
        } else {
            if ($this->udo != null) {
                $renderedCode = $udo;
            } else {
                // Render instructions if Tealium Object was not used correctly
                $renderedCode = <<<EOD
<!-- Tealium Universal Data Object / Data Layer -->
<!-- Account, profile, or environment was not declared in 
    object Tealium(\$account, \$profile, \$target, \$pageType) -->
EOD;
            }
        }

        return $renderedCode;
    }
}
