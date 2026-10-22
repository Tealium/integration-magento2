<?php

namespace Tealium\Tags\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Tealium\Tags\Helper\Product as ProductHelper;

class TealiumData extends AbstractHelper
{
    const XML_PATH_PLAIN_TXT_EMAIL = 'tealium_tags/general/plain_txt_email';

    /**
     * Query parameters that are never treated as browse refinements
     */
    const NON_REFINEMENT_PARAMS = ['q', 'p', 'product_list_order', 'product_list_dir', 'product_list_limit',
        'product_list_mode', 'tealium_api'];

    private $store;
    private $page;

    protected $_storeManager;
    protected $_registry;
    protected $_checkoutSession;
    protected $customerSession;

    /**
     * @var \Magento\Framework\Locale\Resolver
     */
    protected $_localeResolver;

    /**
     * @var ProductHelper
     */
    protected $productHelper;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    protected $groupRepository;
    protected $eavConfig;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Registry $registry,
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        Resolver $localeResolver,
        ProductHelper $productHelper,
        CategoryRepositoryInterface $categoryRepository,
        GroupRepositoryInterface $groupRepository,
        EavConfig $eavConfig
    ) {
        $this->_storeManager = $storeManager;
        $this->_registry = $registry;
        $this->_checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->_localeResolver = $localeResolver;
        $this->productHelper = $productHelper;
        $this->categoryRepository = $categoryRepository;
        $this->groupRepository = $groupRepository;
        $this->eavConfig = $eavConfig;
        parent::__construct($context);
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
        $page = $this->page;

        $outputArray = $this->getSiteData();
        $titleBlock = $page->getLayout()->getBlock('page.main.title');
        if ($titleBlock) {
            $outputArray['page_name'] = $titleBlock->getPageTitle() ? : "";
            $outputArray['page_type'] = $page->getTealiumType() ? : "";
        } else {
            $outputArray['page_name'] = "not supported by extension";
            $outputArray['page_type'] = "not supported by extension";
        }

        if ($outputArray['page_name'] == 'Home Page') {
            $outputArray = array_merge($outputArray, $this->getLocaleCodes());
            $outputArray['page_type'] = "home";
            // cart totals are filled in client-side from the private "cart" section
            $outputArray['cart_total_items'] = "";
            $outputArray['cart_total_value'] = "";
            $outputArray['tealium_event'] = "page_view";
        }

        return $outputArray;
    }

    public function getSearch()
    {
        $page = $this->page;
        $searchBlock = $page->getLayout()->getBlock('search.result');

        if ($searchBlock === false) {
            return [];
        }

        $outputArray = $this->getSiteData();
        $outputArray['page_name'] = "search results";
        $outputArray['page_type'] = "search";
        $outputArray['search_results'] = (string)$searchBlock->getResultCount();
        $outputArray['search_keyword'] =
            $page->helper('Magento\CatalogSearch\Helper\Data')->getEscapedQueryText() ? : "";

        $outputArray = array_merge($outputArray, $this->getBrowseRefinements());

        // cart totals are filled in client-side from the private "cart" section
        $outputArray['cart_total_items'] = "0.00";
        $outputArray['cart_total_value'] = "0.00";

        $outputArray = array_merge($outputArray, $this->getLocaleCodes());
        $outputArray['tealium_event'] = "search";

        return $outputArray;
    }

    public function getCategory()
    {
        $page = $this->page;
        $section = false;
        $category = false;
        $subcategory = false;
        $categoryId = false;
        $category_name = false;

        $_category = $this->_registry->registry('current_category');
        if ($_category) {
            $parent = false;
            $grandparent = false;

            // check for parent and grandparent
            if ($_category->getParentId()) {
                $parent = $this->categoryRepository->get($_category->getParentId());

                if ($parent->getParentId()) {
                    $grandparent = $this->categoryRepository->get($parent->getParentId());
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

            $categoryId = $_category->getEntityId();
            if ($_category->getLevel() == '3') {
                $category_name = $category . ':' . $subcategory;
            }
            if ($_category->getLevel() == '4') {
                $urlPath = (string)$_category->getUrlPath();
                $catNames = [];
                foreach (explode("/", $urlPath) as $catName) {
                    $catNames[] = ucfirst((string)strstr($catName, "-", true));
                }
                $category_name = ucfirst((string)strtok($urlPath, '/')) . implode(":", $catNames);
            }
        }

        $outputArray = $this->getSiteData();
        $outputArray['page_name'] = $_category ? ($_category->getName() ? : "") : "";
        $outputArray['page_type'] = "category";
        $outputArray['page_section_name'] = $section ? : "";
        $outputArray['page_category_name'] = $category ? : "";
        $outputArray['page_subcategory_name'] = $subcategory ? : "";

        $outputArray = array_merge($outputArray, $this->getBrowseRefinements());
        $outputArray = array_merge($outputArray, $this->getLocaleCodes());

        // cart totals are filled in client-side from the private "cart" section
        $outputArray['cart_total_items'] = '';
        $outputArray['cart_total_value'] = '';

        $outputArray['category_id'] = $categoryId ? : "";
        $outputArray['category_name'] = $category_name ? : $subcategory;
        $outputArray['tealium_event'] = "category_view";

        return $outputArray;
    }

    public function getProductPage()
    {
        $categoryName = false;
        $parentCatName = false;

        $_product = $this->_registry->registry('current_product');
        $_category = $this->_registry->registry('current_category');

        $outputArray = $this->getSiteData();
        $outputArray['page_name'] = $_product ? ($_product->getName() ? : "") : "";
        $outputArray['page_type'] = "product";

        // THE FOLLOWING NEEDS TO BE MATCHED ARRAYS (SAME NUMBER OF ELEMENTS)
        if ($_product) {
            $outputArray['product_id'] = [$_product->getId()];
            $outputArray['product_sku'] = [$_product->getSku()];
            $outputArray['product_url'] = [$_product->getProductUrl()];
            $outputArray['product_on_page'] = [$_product->getSku()];
            $outputArray['product_image_url'] = [$this->getProductMediaBaseUrl() . $_product->getImage()];
            $outputArray['product_name'] = [$_product->getName()];

            $manufacturer = $this->productHelper->getManufacturer($_product);
            $outputArray['product_brand'] = [$manufacturer === false ? "" : $manufacturer];

            $outputArray['product_unit_price'] = [$this->productHelper->formatPrice($_product->getFinalPrice())];
            $outputArray['product_list_price'] = [$this->productHelper->formatPrice($_product->getData('price'))];

            // the last assigned category wins, as does the last parent that is
            // not the store root
            $categoryIds = array_values((array)$_product->getCategoryIds());
            $lastCategoryId = end($categoryIds);
            $categories = $this->productHelper->loadCategories($categoryIds);
            $parentIds = [];
            foreach ($categories as $category) {
                $parentIds[] = $category['parent_id'];
            }
            $parents = $this->productHelper->loadCategories($parentIds);
            foreach ($categoryIds as $id) {
                if (!isset($categories[(int)$id])) {
                    continue;
                }
                $categoryName = $categories[(int)$id]['name'];
                $parentId = $categories[(int)$id]['parent_id'];
                if (isset($parents[$parentId]) && $parents[$parentId]['name'] != "Default Category") {
                    $parentCatName = $parents[$parentId]['name'];
                }
            }

            $outputArray['category_id'] = $lastCategoryId ? : "";
            $outputArray['category_name'] = $parentCatName ? : "";
            $outputArray['product_subcategory'] = [$categoryName];
        } else {
            $outputArray['product_id'] = [];
            $outputArray['product_sku'] = [];
            $outputArray['product_name'] = [];
            $outputArray['product_brand'] = [];
            $outputArray['product_unit_price'] = [];
            $outputArray['product_list_price'] = [];
        }

        $outputArray['product_price'] = $outputArray['product_unit_price'];
        $outputArray['product_original_price'] = $outputArray['product_list_price'];

        if ($_category) {
            if ($_category->getName()) {
                $outputArray['product_category'] = [$_category->getName()];
                $outputArray['product_subcategory'] = [$_category->getName()];
            } else {
                $outputArray['product_category'] = [""];
            }
        } elseif ($_product) {
            $cats = array_values((array)$_product->getCategoryIds());
            if (count($cats)) {
                $first = $this->productHelper->loadCategories([$cats[0]]);
                $outputArray['product_category'] = [isset($first[(int)$cats[0]]) ? $first[(int)$cats[0]]['name'] : ""];
            } else {
                $outputArray['product_category'] = [""];
            }
        }

        $outputArray['tealium_event'] = "product_view";
        $outputArray = array_merge($outputArray, $this->getLocaleCodes());

        // cart totals are filled in client-side from the private "cart" section
        $outputArray['cart_total_items'] = '';
        $outputArray['cart_total_value'] = '';

        return $outputArray;
    }

    public function getCartPage()
    {
        $page = $this->page;

        $checkout_ids = [];
        $checkout_skus = [];
        $checkout_names = [];
        $checkout_qtys = [];
        $checkout_prices = [];
        $checkout_original_prices = [];
        $checkout_images = [];
        $checkout_url = [];
        $checkout_catId = [];
        $categoryName = [];
        $parentCatName = [];
        $manufacturer = [];
        $itemDiscountAmount = [];

        $quote = $this->_checkoutSession->getQuote();
        $items = $quote->getAllVisibleItems();
        $product_promo_code = $quote->getCouponCode();

        $productIds = [];
        foreach ($items as $item) {
            $productIds[] = $item->getProductId();
        }
        // also preloads every assigned category and its ancestors
        $products = $this->productHelper->loadProducts($productIds);

        $mediaUrl = $this->getProductMediaBaseUrl();

        foreach ($items as $item) {
            $product = isset($products[(int)$item->getProductId()]) ? $products[(int)$item->getProductId()] : null;

            $checkout_ids[] = $item->getProductId();
            $checkout_skus[] = $item->getSku();
            $checkout_names[] = $item->getName();
            $checkout_catId[] = $item->getCategoryIds();

            $brand = $product ? $this->productHelper->getManufacturer($product) : false;
            $manufacturer[] = $brand;

            // the last assigned category (other than the default root) is used
            $itemCategory = null;
            $categoryIds = $product ? (array)$product->getCategoryIds() : [];
            foreach ($this->productHelper->loadCategories($categoryIds) as $id => $category) {
                if ($id != 2) {
                    $itemCategory = $category;
                }
            }
            $parentName = '';
            if ($itemCategory) {
                $parent = $this->productHelper->loadCategories([$itemCategory['parent_id']]);
                $parent = reset($parent);
                if ($parent && $parent['name'] != "Default Category") {
                    $parentName = $parent['name'];
                }
            }
            // keep the arrays lined up with the product arrays
            $categoryName[] = $itemCategory ? $itemCategory['name'] : '';
            $parentCatName[] = $parentName;

            $checkout_url[] = $product ? $product->getProductUrl() : '';
            $checkout_images[] = $product ? $mediaUrl . $product->getImage() : '';
            $checkout_qtys[] = number_format((float)$item->getQty(), 0, ".", "");
            $checkout_prices[] = number_format((float)$item->getPrice(), 2, ".", "");
            $checkout_original_prices[] = number_format((float)$item->getProduct()->getPrice(), 2, ".", "");

            if (!empty($product_promo_code)) {
                $itemDiscountAmount[] = number_format((float)$item->getDiscountAmount(), 2, ".", "");
            }
        }

        $outputArray = $this->getSiteData();

        $titleBlock = $page->getLayout()->getBlock('page.main.title');
        if ($titleBlock) {
            $outputArray['page_name'] = $titleBlock->getPageTitle() ? : "";
            $outputArray['page_type'] = "cart";
        } else {
            $outputArray['page_name'] = "Cart";
            $outputArray['page_type'] = "cart";
        }

        if ($product_promo_code) {
            $outputArray['product_promo_code'] = [$product_promo_code];
            $outputArray['product_discount_amount'] = $itemDiscountAmount ? : '';
        }

        // THE FOLLOWING NEEDS TO BE MATCHED ARRAYS (SAME NUMBER OF ELEMENTS)
        $outputArray = array_merge($outputArray, $this->getLocaleCodes());

        $outputArray['product_image_url'] = $checkout_images;
        $outputArray['product_subcategory'] = $parentCatName;
        $outputArray['product_url'] = $checkout_url;

        // the cart and checkout pages are not cached, so the live quote is used
        $outputArray['cart_total_items'] = $quote->getItemsQty() !== null
            ? number_format((float)$quote->getItemsQty(), 2, ".", "") : '';
        $outputArray['cart_total_value'] = $quote->getGrandTotal() !== null
            ? number_format((float)$quote->getGrandTotal(), 2, ".", "") : '';

        $outputArray['product_brand'] = empty($manufacturer) ? [""] : $manufacturer;

        if ($outputArray['page_name'] != 'Checkout' && $outputArray['page_name'] != 'Shopping Cart') {
            $outputArray['category_id'] = $checkout_catId ? : "";
            $outputArray['category_name'] = $categoryName ? : "";
            $outputArray['product_subcategory'] = $parentCatName ? : "";
        }
        $outputArray['product_id'] = $checkout_ids;
        $outputArray['product_sku'] = $checkout_skus;
        $outputArray['product_name'] = $checkout_names;
        $outputArray['product_category'] = $categoryName;
        $outputArray['product_quantity'] = $checkout_qtys;
        $outputArray['product_unit_price'] = $checkout_prices;
        $outputArray['product_list_price'] = $checkout_original_prices;

        $outputArray['tealium_event'] = "cart_view";
        if ($outputArray['page_name'] == 'Checkout') {
            $outputArray['tealium_event'] = "checkout";
        }
        $outputArray['product_price'] = $outputArray['product_unit_price'];
        $outputArray['product_original_price'] = $outputArray['product_list_price'];

        return $outputArray;
    }

    public function getOrderConfirmation()
    {
        $customer_id = false;
        $ids = [];
        $skus = [];
        $names = [];
        $prices = [];
        $original_prices = [];
        $qtys = [];
        $discounts = [];
        $productImage = [];
        $productUrl = [];
        $categoryName = [];
        $manufacturer = [];
        $parentCatName = [];

        if ($this->customerSession->isLoggedIn()) {
            $customer_id = $this->customerSession->getCustomerId();
        }

        $order = $this->_checkoutSession->getLastRealOrder();
        $items = $order->getAllVisibleItems();

        $productIds = [];
        foreach ($items as $item) {
            $productIds[] = $item->getProductId();
        }
        $products = $this->productHelper->loadProducts($productIds);
        $mediaUrl = $this->getProductMediaBaseUrl();

        foreach ($items as $item) {
            $product = isset($products[(int)$item->getProductId()]) ? $products[(int)$item->getProductId()] : null;

            $ids[] = $item->getProductId();
            $skus[] = $item->getSku();
            $manufacturer[] = $product ? $this->productHelper->getManufacturer($product) : false;
            $productUrl[] = $product ? $product->getProductUrl() : '';
            $productImage[] = $product ? $mediaUrl . $product->getImage() : '';

            // the last assigned category (other than the default root) is used
            $itemCategory = null;
            $categoryIds = $product ? (array)$product->getCategoryIds() : [];
            foreach ($this->productHelper->loadCategories($categoryIds) as $id => $category) {
                if ($id != 2) {
                    $itemCategory = $category;
                }
            }
            $parentName = '';
            if ($itemCategory) {
                $parent = $this->productHelper->loadCategories([$itemCategory['parent_id']]);
                $parent = reset($parent);
                if ($parent && $parent['name'] != "Default Category") {
                    $parentName = $parent['name'];
                }
            }
            // keep the arrays lined up with the product arrays
            $categoryName[] = $itemCategory ? $itemCategory['name'] : '';
            $parentCatName[] = $parentName;

            $names[] = $item->getName();
            $qtys[] = number_format((float)$item->getQtyOrdered(), 0, ".", "");
            $prices[] = number_format((float)$item->getPrice(), 2, ".", "");
            $original_prices[] = number_format((float)($product ? $product->getPrice() : 0), 2, ".", "");
            $discounts[] = number_format((float)$item->getDiscountAmount(), 2, ".", "");
        }

        $outputArray = $this->getSiteData();
        $outputArray['page_name'] = "cart success";
        $outputArray['page_type'] = "order";
        $outputArray['order_id'] = $order->getIncrementId() ? : "";
        $outputArray['order_discount'] = $this->formatNullable($order->getDiscountAmount());
        $outputArray['order_subtotal'] = $this->formatNullable($order->getSubtotal());
        $outputArray['order_shipping'] = $this->formatNullable($order->getShippingAmount());
        $outputArray['order_tax'] = $this->formatNullable($order->getTaxAmount());
        $outputArray['order_payment_type'] = $this->getPaymentTitle($order);
        $outputArray['order_total'] = $this->formatNullable($order->getGrandTotal());
        $outputArray['order_currency'] = $order->getOrderCurrencyCode() ? : "";
        $outputArray['customer_id'] = $customer_id ? : "";
        $outputArray['customer_email'] = $this->formatEmail($order->getCustomerEmail());
        $outputArray['product_id'] = $ids;
        $outputArray['product_sku'] = $skus;
        $outputArray['product_name'] = $names;
        $outputArray['product_brand'] = $manufacturer;
        $outputArray['product_category'] = $categoryName;
        $outputArray['product_unit_price'] = $prices;
        $outputArray['product_list_price'] = $original_prices;
        $outputArray['product_price'] = $outputArray['product_unit_price'];
        $outputArray['product_original_price'] = $outputArray['product_list_price'];
        $outputArray['product_quantity'] = $qtys;
        $outputArray['product_discount'] = $discounts;

        $product_promo_code = $order->getCouponCode();

        $outputArray['order_store'] = $this->_localeResolver->getLocale() ? : '';
        $outputArray = array_merge($outputArray, $this->getLocaleCodes());

        if ($this->customerSession->isLoggedIn()) {
            $customer = $this->customerSession->getCustomer();
            $address = $customer->getDefaultShippingAddress();
            if ($address) {
                $outputArray['customer_city'] = $address->getCity() ? : [];
                $outputArray['customer_country'] = $address->getCountryId() ? : [];
                $outputArray['customer_first_name'] = $customer->getFirstname() ? : [];
                $outputArray['customer_last_name'] = $customer->getLastname() ? : [];
                $outputArray['customer_postal_code'] = $address->getPostcode() ? : [];
                $outputArray['customer_state'] = $address->getRegion() ? : [];
            }
        }
        $outputArray['order_promo_code'] = $product_promo_code ? : [];
        $outputArray['order_shipping_amount'] = $order->getShippingAmount() ? : [];
        $outputArray['order_shipping_type'] = $order->getShippingMethod() ? : [];
        $outputArray['page_name'] = "Order Confirmation - Thank You";
        $outputArray['product_image_url'] = $productImage;
        $outputArray['product_promo_code'] = $product_promo_code ? : [];
        $outputArray['product_subcategory'] = $parentCatName;
        $outputArray['product_url'] = $productUrl;
        $outputArray['tealium_event'] = "purchase";

        return $outputArray;
    }

    public function getCustomerData()
    {
        $page = $this->page;

        $customer_id = false;
        $customer_email = false;
        $customer_type = false;
        $customer_firstname = false;
        $customer_lastname = false;
        $customer_postal_code = false;
        $country_id = false;
        $customer_city = false;
        $customer_region = false;

        if ($this->customerSession->isLoggedIn()) {
            $customer = $this->customerSession->getCustomer();
            if (!empty($customer)) {
                $customer_id = $customer->getEntityId();
                $customer_email = $customer->getEmail();
                $customer_firstname = $customer->getFirstname();
                $customer_lastname = $customer->getLastname();
                $address = $customer->getDefaultShippingAddress();
                if ($address) {
                    $customer_postal_code = $address->getPostcode();
                    $country_id = $address->getCountryId();
                    $customer_city = $address->getCity();
                    $customer_region = $address->getRegion();
                }
                $customer_type = $this->getCustomerGroupCode($customer->getGroupId());
            }
        }

        $outputArray = $this->getSiteData();
        $titleBlock = $page->getLayout()->getBlock('page.main.title');
        if ($titleBlock) {
            $outputArray['page_name'] = $titleBlock->getPageTitle() ? : "";
            $outputArray['page_type'] = $page->getTealiumType() ? : "";
        } else {
            $outputArray['page_name'] = "Customer Data";
            $outputArray['page_type'] = "customer_data";
        }
        $outputArray['customer_id'] = $customer_id ? : "";
        $outputArray['customer_email'] = $this->formatEmail($customer_email);
        $outputArray['customer_type'] = $customer_type ? : "";

        $outputArray = array_merge($outputArray, $this->getLocaleCodes());

        $outputArray['customer_city'] = $customer_city ? : "";
        $outputArray['customer_country'] = $country_id ? : "";
        $outputArray['customer_first_name'] = $customer_firstname ? : "";
        $outputArray['customer_last_name'] = $customer_lastname ? : "";
        $outputArray['customer_postal_code'] = $customer_postal_code ? : "";
        $outputArray['customer_state'] = $customer_region ? : "";
        $outputArray['tealium_event'] = "page_view";

        return $outputArray;
    }

    /**
     * Apply the "Allow plain text customer email" setting: unless plain text
     * is explicitly allowed, emails are SHA-256 hashed.
     *
     * @param string|null|false $email
     * @return string
     */
    public function formatEmail($email)
    {
        if (!$email) {
            return "";
        }
        $plain = $this->scopeConfig->getValue(
            self::XML_PATH_PLAIN_TXT_EMAIL,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        return $plain ? (string)$email : hash('sha256', strtolower((string)$email));
    }

    /**
     * @param int|null $groupId
     * @return string
     */
    public function getCustomerGroupCode($groupId)
    {
        if ($groupId === null || $groupId === '') {
            return "";
        }
        try {
            return (string)$this->groupRepository->getById($groupId)->getCode();
        } catch (\Exception $e) {
            return "";
        }
    }

    /**
     * site_region and site_currency, shared by every page type
     *
     * @return array
     */
    private function getSiteData()
    {
        $store = $this->store ? : $this->_storeManager->getStore();
        return [
            'site_region' => $this->_localeResolver->getLocale() ? : "",
            'site_currency' => $store->getCurrentCurrencyCode() ? : ""
        ];
    }

    /**
     * country_code and language_code derived from the locale, e.g. en_US
     *
     * @return array
     */
    private function getLocaleCodes()
    {
        $locale = explode("_", (string)$this->_localeResolver->getLocale());
        return [
            'country_code' => isset($locale[1]) ? strtolower($locale[1]) : '',
            'language_code' => isset($locale[0]) ? $locale[0] : ''
        ];
    }

    /**
     * Layered navigation filters applied through the query string. Only
     * price, category and filterable product attributes count as refinements;
     * paging, sorting and tracking parameters (utm_*, gclid, ...) are ignored.
     *
     * @return array browse_refine_type / browse_refine_value, when present
     */
    private function getBrowseRefinements()
    {
        $types = [];
        $values = [];

        $query = method_exists($this->_request, 'getQueryValue') ? $this->_request->getQueryValue() : [];
        foreach (is_array($query) ? $query : [] as $key => $value) {
            if (in_array($key, self::NON_REFINEMENT_PARAMS, true) || !is_scalar($value) || $value === '') {
                continue;
            }

            if ($key === 'price' || $key === 'cat') {
                $types[] = $key;
                $values[] = (string)$value;
                continue;
            }

            try {
                $attribute = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $key);
            } catch (\Exception $e) {
                continue;
            }
            if (!$attribute || !$attribute->getId()
                || !($attribute->getIsFilterable() || $attribute->getIsFilterableInSearch())
            ) {
                continue;
            }

            $text = $attribute->usesSource() ? $attribute->getSource()->getOptionText($value) : $value;
            if (is_array($text)) {
                $text = implode(',', $text);
            }
            $types[] = $key;
            $values[] = (string)$text;
        }

        $result = [];
        if (!empty($types)) {
            $result['browse_refine_type'] = $types;
            $result['browse_refine_value'] = $values;
        }
        return $result;
    }

    /**
     * @return string base url of product images for the current store
     */
    private function getProductMediaBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . 'catalog/product';
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    private function getPaymentTitle($order)
    {
        $payment = $order->getPayment();
        if (!$payment) {
            return 'unknown';
        }
        try {
            return (string)$payment->getMethodInstance()->getTitle();
        } catch (\Exception $e) {
            return (string)$payment->getMethod();
        }
    }

    /**
     * @param float|string|null $value
     * @return string
     */
    private function formatNullable($value)
    {
        return $value !== null ? number_format((float)$value, 2, ".", "") : "";
    }

    /**
     * @return int|null
     */
    private function getStoreId()
    {
        $store = $this->store ? : $this->_storeManager->getStore();
        return $store ? $store->getId() : null;
    }
}
