<?php
/**
 * Created by PhpStorm.
 * User: svatoslavzilicev
 * Date: 25.08.17
 * Time: 12:20
 */

namespace Tealium\Tags\Helper;
use \Magento\Framework\App\Helper\AbstractHelper;

class TealiumData extends AbstractHelper{

    // Declare store and page as static vars and define setter methods
    private $store;
    private $page;
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

    public function setStore($store)
    {
       $this->store = $store;
    }

    public function setPage($page)
    {
        $this->page = $page;
    }

    // Define methods for getting udo to output for each page type
    public function getHome()
    {
        $store = $this->store;
        $page = $this->page;

        $outputArray = array();
        $outputArray['site_region'] =
            $this->_objectManager->get('Magento\Framework\Locale\Resolver')->getLocale() ? : "";
        $outputArray['site_currency'] = $store->getCurrentCurrencyCode() ? : "";
        $titleBlock = $page->getLayout()->getBlock('page.main.title');
        if ($titleBlock){
            $outputArray['page_name'] =
                $page->getLayout()->getBlock('page.main.title')->getPageTitle() ? : "";
            $outputArray['page_type'] = $page->getTealiumType() ? : "";
        } else {
            $outputArray['page_name'] = "not supported by extension";
            $outputArray['page_type'] = "not supported by extension";
        }

        return $outputArray;
    }

    public function getSearch()
    {
        $store = $this->store;
        $page = $this->page;
        $searchBlock = $page->getLayout()->getBlock('search.result');
        $outputArray = array();

        if ($searchBlock === false){
            return $outputArray;
        }
        $outputArray['site_region'] =
            $this->_objectManager->get('Magento\Framework\Locale\Resolver')->getLocale() ? : "";
        $outputArray['site_currency'] = $store->getCurrentCurrencyCode() ? : "";
        $outputArray['page_name'] = "search results";
        $outputArray['page_type'] = "search";
        $outputArray['search_results'] = $searchBlock->getResultCount() . "" ? : "";
        $outputArray['search_keyword'] =
            $page->helper('Magento\CatalogSearch\Helper\Data')->getEscapedQueryText() ? : "";

        return $outputArray;
    }

    public function getCategory()
    {
        $store = $this->store;
        $page = $this->page;

        $section = false;
        $category = false;
        $subcategory = false;

        if ($_category = $this->_registry->registry('current_category')) {
//            $_category = $page->getCurrentCategory();
            $parent = false;
            $grandparent = false;

            // check for parent and grandparent
            if ($_category->getParentId()) {
                $parent =
                    $this->_objectManager->create('Magento\Catalog\Model\Category')
                        ->load($_category->getParentId());

                if ($parent->getParentId()) {
                    $grandparent =
                        $this->_objectManager->create('Magento\Catalog\Model\Category')
                            ->load($parent->getParentId());
                }
            }

            // Set the section and subcategory with parent and grandparent
            if ($grandparent) {
                $section = $grandparent->getName();
                $category = $parent->getName();
                $subcategory = $_category->getName();
            } elseif ($parent) {
                $section = $parent->getName();
                $category = $_category->getName();
            } else {
                $category = $_category->getName();
            }
        }

        $outputArray = array();
        $outputArray['site_region'] =
            $this->_objectManager->get('Magento\Framework\Locale\Resolver')->getLocale() ? : "";
        $outputArray['site_currency'] = $store->getCurrentCurrencyCode() ? : "";
        $outputArray['page_name'] =
            $_category ? ($_category->getName() ? : "") : "";
        $outputArray['page_type'] = "category";
        $outputArray['page_section_name'] = $section ? : "";
        $outputArray['page_category_name'] = $category ? : "";
        $outputArray['page_subcategory_name'] = $subcategory ? : "";

        return $outputArray;
    }

    public function getProductPage()
    {
        $store = $this->store;
        $page = $this->page;
        $_product = $this->_registry->registry('current_product');

        $outputArray = array();
        $outputArray['site_region'] =
            $this->_objectManager->get('Magento\Framework\Locale\Resolver')->getLocale() ? : "";
        $outputArray['site_currency'] = $store->getCurrentCurrencyCode() ? : "";
        $outputArray['page_name'] =
            $_product ? ($_product->getName() ? : "") : "";
        $outputArray['page_type'] = "product";

        // THE FOLLOWING NEEDS TO BE MATCHED ARRAYS (SAME NUMBER OF ELEMENTS)
        if ($_product) {
            if (!(
            $outputArray['product_id'] = array($_product->getId())
            )) {
                $outputArray['product_id'] = array();
            }

            if (!(
            $outputArray['product_sku'] = array(
                $_product->getSku()
            )
            )) {
                $outputArray['product_sku'] = array();
            }

            if (!(
            $outputArray['product_name'] = array(
                $_product->getName()
            )
            )) {
                $outputArray['product_name'] = array();
            }

            $manufacturer = $_product->getAttributeText('manufacturer');
            if ($manufacturer === false){
                $outputArray['product_brand'] = array("");
            } else {
                $outputArray['product_brand'] = array($manufacturer);
            }

            if (!(
            $outputArray['product_unit_price'] = array(
                number_format($_product->getFinalPrice(), 2,'.','')
            )
            )) {
                $outputArray['product_unit_price'] = array();
            }

            if (!(
            $outputArray['product_list_price'] = array(
                number_format($_product->getData('price'), 2,'.','')
            )
            )) {
                $outputArray['product_list_price'] = array();
            }
        } else {
            $outputArray['product_id'] = array();
            $outputArray['product_sku'] = array();
            $outputArray['product_name'] = array();
            $outputArray['product_brand'] = array();
            $outputArray['product_unit_price'] = array();
            $outputArray['product_list_price'] = array();
        }

        $outputArray['product_price'] = $outputArray['product_unit_price'];
        $outputArray['product_original_price'] =
            $outputArray['product_list_price'];

        if ($this->_registry->registry('current_category')) {
            if ($this->_registry->registry('current_category')->getName()) {
                $outputArray['product_category'] = array(
                    $this->_registry->registry('current_category')->getName()
                );
            } else {
                $outputArray['product_category'] = array("");
            }
        } elseif($_product) {
            $cats = $_product->getCategoryIds();
            if(count($cats) ){
                $firstCategoryId = $cats[0];
                $_category = $this->_objectManager->create('Magento\Catalog\Model\Category')->load($firstCategoryId);
                $outputArray['product_category'] = array(
                    $_category->getName()
                );
            } else {
                $outputArray['product_category'] = array("");
            }
        }

        return $outputArray;
    }

    public function getCartPage()
    {
        $store = $this->store;
        $page = $this->page;

        $checkout_ids =
        $checkout_skus =
        $checkout_names =
        $checkout_qtys =
        $checkout_prices =
        $checkout_original_prices =
        $checkout_brands =
            array();

        if ($this->_checkoutSession) {
            $quote = $this->_checkoutSession->getQuote();
            foreach ($quote->getAllVisibleItems() as $item) {
                $checkout_ids[] = $item->getProductId();
                $checkout_skus[] = $item->getSku();
                $checkout_names[] = $item->getName();
                $checkout_qtys[] = number_format($item->getQty(), 0, ".", "");
                $checkout_prices[] =
                    number_format($item->getPrice(), 2, ".", "");
                $checkout_original_prices[] =
                    number_format($item->getProduct()->getPrice(), 2, ".", "");
                $checkout_brands[] = $item->getProduct()->getBrand();
            }
        }

        $outputArray = array();
        $outputArray['site_region'] =
            $this->_objectManager->get('Magento\Framework\Locale\Resolver')->getLocale() ? : "";
        $outputArray['site_currency'] = $store->getCurrentCurrencyCode() ? : "";

        $titleBlock = $page->getLayout()->getBlock('page.main.title');
        if ($titleBlock){
            $outputArray['page_name'] =
                $page->getLayout()->getBlock('page.main.title')->getPageTitle() ? : "";
            $outputArray['page_type'] = "cart";
        } else {
            $outputArray['page_name'] = "Cart";
            $outputArray['page_type'] = "cart";
        }

        // THE FOLLOWING NEEDS TO BE MATCHED ARRAYS (SAME NUMBER OF ELEMENTS)
        $outputArray['product_id'] = $checkout_ids ? : array();
        $outputArray['product_sku'] = $checkout_skus ? : array();
        $outputArray['product_name'] = $checkout_names ? : array();
        $outputArray['product_brand'] = $checkout_brands ? : array();
        $outputArray['product_category'] = array();
        $outputArray['product_quantity'] = $checkout_qtys ? : array();
        $outputArray['product_unit_price'] = $checkout_prices ? : array();
        $outputArray['product_list_price'] =
            $checkout_original_prices ? : array();

        $outputArray['product_price'] = $outputArray['product_unit_price'];
        $outputArray['product_original_price'] =
            $outputArray['product_list_price'];

        return $outputArray;
    }

    public function getOrderConfirmation()
    {
        $store = $this->store;
        $page = $this->page;

        $customer_id = false;
        $ids = false;
        $skus = false;
        $names = false;
        $brands = false;
        $prices = false;
        $original_prices = false;
        $qtys = false;
        $discounts = false;
        $discount_quantity = false;

        if ($this->_objectManager->get('Magento\Customer\Model\Session')->isLoggedIn()) {
            $customer = $this->_objectManager->get('Magento\Customer\Model\Session')->getCustomer();
            $customer_id = $customer->getEntityId();
            $customer_email = $customer->getEmail();
            $groupId = $customer->getGroupId();
            $customer_type =
                $this->_objectManager->create('Magento\Customer\Model\Group')->load($groupId)->getCode();
        }

        if ($this->_objectManager->create('Magento\Sales\Model\Order')) {
            /** @var \Magento\Checkout\Model\Session $checkoutSession */
            $checkoutSession = $this->_objectManager->get('Magento\Checkout\Model\Session');
//            $order = $this->_objectManager->create('Magento\Sales\Model\Order')
//                ->loadByIncrementId($checkoutSession->getLastOrderId());
            $order = $checkoutSession->getLastRealOrder();

            foreach ($order->getAllVisibleItems() as $item) {
                $ids[] = $item->getProductId();
                $skus[] = $item->getSku();
                $names[] = $item->getName();
                $qtys[] = number_format($item->getQtyOrdered(), 0, ".", "");
                $prices[] = number_format($item->getPrice(), 2, ".", "");
                $original_prices[] =
                    number_format($item->getProduct()->getPrice(), 2, ".", "");
                $discount =
                    number_format($item->getDiscountAmount(), 2, ".", "");
                $discounts[] = $discount;
                $applied_rules = explode(",", $item->getAppliedRuleIds());
                $discount_object = array();
                $brands[] = $item->getProduct()->getBrand();
                foreach ($applied_rules as $rule) {
                    $quantity = number_format(
                        $this->_objectManager->create('Magento\SalesRule\Model\Rule')
                            ->load($rule)
                            ->getDiscountQty(),
                        0,
                        ".",
                        ""
                    );

                    $amount = number_format(
                        $this->_objectManager->create('Magento\SalesRule\Model\Rule')
                            ->load($rule)
                            ->getDiscountAmount(),
                        2,
                        ".",
                        ""
                    );

                    $type = $this->_objectManager->create('Magento\SalesRule\Model\Rule')
                        ->load($rule)
                        ->getSimpleAction();

                    $discount_object[] = array(
                        "rule" => $rule,
                        "quantity" => $quantity,
                        "amount" => $amount,
                        "type" => $type
                    );
                }
                $discount_quantity[] = array(
                    "product_id" => $item->getProductId(),
                    "total_discount" => $discount,
                    "discounts" => $discount_object
                );
            }
        }

        $outputArray = array();

        $outputArray['site_region'] =
            $this->_objectManager->get('Magento\Framework\Locale\Resolver')->getLocale() ? : "";
        $outputArray['site_currency'] = $store->getCurrentCurrencyCode() ? : "";
        $outputArray['page_name'] = "cart success";
        $outputArray['page_type'] = "order";
        $outputArray['order_id'] = $order->getIncrementId() ? : "";
        $outputArray['order_discount'] =
            number_format($order->getDiscountAmount(), 2, ".", "") ? : "";
        $outputArray['order_subtotal'] =
            number_format($order->getSubtotal(), 2, ".", "") ? : "";
        $outputArray['order_shipping'] =
            number_format($order->getShippingAmount(), 2, ".", "") ? : "";
        $outputArray['order_tax'] =
            number_format($order->getTaxAmount(), 2, ".", "") ? : "";
        $outputArray['order_payment_type'] =
            $order->getPayment()
                ? $order->getPayment()->getMethodInstance()->getTitle()
                : 'unknown';
        $outputArray['order_total'] =
            number_format($order->getGrandTotal(), 2, ".", "") ? : "";
        $outputArray['order_currency'] = $order->getOrderCurrencyCode() ? : "";
        $outputArray['customer_id'] = $customer_id ? : "";
        $outputArray['customer_email'] = $order->getCustomerEmail() ? : "";
        $outputArray['product_id'] = $ids ? : array();
        $outputArray['product_sku'] = $skus ? : array();
        $outputArray['product_name'] = $names ? : array();
        $outputArray['product_brand'] = $brands ? : array();
        $outputArray['product_category'] = array();
        $outputArray['product_unit_price'] = $prices ? : array();
        $outputArray['product_list_price'] = $original_prices ? : array();
        $outputArray['product_price'] = $outputArray['product_unit_price'];
        $outputArray['product_original_price'] =
            $outputArray['product_list_price'];
        $outputArray['product_quantity'] = $qtys ? : array();
        $outputArray['product_discount'] = $discounts ? : array();
        $outputArray['product_discounts'] = $discount_quantity ? : array();

        return $outputArray;
    }

    public function getCustomerData()
    {
        $store = $this->store;
        $page = $this->page;

        $customer_id = false;
        $customer_email = false;
        $customer_type = false;

        if ($this->_objectManager->get('Magento\Customer\Model\Session')->isLoggedIn()) {
            $customer = $this->_objectManager->get('Magento\Customer\Model\Session')->getCustomer();
            $customer_id = $customer->getEntityId();
            $customer_email = $customer->getEmail();
            $groupId = $customer->getGroupId();
            $customer_type =
                $this->_objectManager->create('Magento\Customer\Model\Group')->load($groupId)->getCode();
        }

        $outputArray = array();

        $outputArray['site_region'] =
            $this->_objectManager->get('Magento\Framework\Locale\Resolver')->getLocale() ? : "";
        $outputArray['site_currency'] = $store->getCurrentCurrencyCode() ? : "";
        $titleBlock = $page->getLayout()->getBlock('page.main.title');
        if ($titleBlock){
            $outputArray['page_name'] =
                $page->getLayout()->getBlock('page.main.title')->getPageTitle() ? : "";
            $outputArray['page_type'] = $page->getTealiumType() ? : "";
        } else {
            $outputArray['page_name'] = "Customer Data";
            $outputArray['page_type'] = "customer_data";
        }
        $outputArray['customer_id'] = $customer_id ? : "";
        $outputArray['customer_email'] = $customer_email ? : "";
        $outputArray['customer_type'] = $customer_type ? : "";

        return $outputArray;
    }
}