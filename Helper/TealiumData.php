<?php

namespace Tealium\Tags\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\App\Bootstrap;
use \Magento\Framework\View\Layout;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\UrlInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\SalesRule\Model\RuleFactory;
use Magento\SalesRule\Model\CouponFactory;



class TealiumData extends AbstractHelper
{

    
    private $store;
    private $page;
    protected $_store;
    protected $_objectManager;
    protected $_isScopePrivate;

    /**
     * @var \Magento\Framework\Registry
    */

    protected $_registry;
    protected $_checkoutSession;
    protected $_layout;
    protected $httpContext;
    protected $customerSession;

    /**
     * @var \Magento\Framework\Locale\Resolver
     */
    protected $_localeResolver;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    protected $groupRepository;
    protected $orderFactory;
    protected $ruleFactory;
    protected $couponFactory;

    
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Api\Data\StoreInterface $store,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        \Magento\Checkout\Model\Session $checkoutSession,
        CustomerSession $customerSession,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\View\Layout $layout,
        \Magento\Framework\App\Http\Context $httpContext,
        Resolver $localeResolver,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        GroupRepositoryInterface $groupRepository,
        OrderFactory $orderFactory,
        CouponFactory $couponFactory,
        RuleFactory $ruleFactory
        
    ) {
        $this->_isScopePrivate = true;
        $this->_store = $store;
        $this->_objectManager = $objectManager;
        $this->_registry = $registry;
        $this->context = $httpContext;
        $this->_checkoutSession  = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->_layout = $layout;
        $this->_cart = $cart;
        $this->_layout->setIsPrivate(true);
        $this->_localeResolver = $localeResolver;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->categoryFactory = $categoryFactory;
        $this->groupRepository = $groupRepository;
        $this->orderFactory = $orderFactory;
        $this->couponFactory = $couponFactory;
        $this->ruleFactory = $ruleFactory;
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

        $outputArray = [];
        //$outputArray['site_region'] =
            //$this->_objectManager->get('Magento\Framework\Locale\Resolver')->getLocale() ? : "";

        $outputArray['site_region'] =   $this->_localeResolver->getLocale() ?: "";
        $outputArray['site_currency'] = $store->getCurrentCurrencyCode() ? : "";
        $titleBlock = $page->getLayout()->getBlock('page.main.title');
        if ($titleBlock) {
            $outputArray['page_name'] =
                $page->getLayout()->getBlock('page.main.title')->getPageTitle() ? : "";
            $outputArray['page_type'] = $page->getTealiumType() ? : "";
        } else {
            $outputArray['page_name'] = "not supported by extension";
            $outputArray['page_type'] = "not supported by extension";
        }
        
        if ($outputArray['page_name'] == 'Home Page') {
            
            //$locale = $this->_objectManager->get('Magento\Framework\Locale\Resolver')->getLocale();
            $locale = $this->_localeResolver->getLocale();
            $locale = explode("_", $locale);
            $outputArray['country_code'] = strtolower($locale[1]) ? : '';
            $outputArray['language_code'] = $locale[0] ? : '';
                
            $ItemsQty = $this->_cart->getQuote()->getItemsQty();
            $GrandTotal =$this->_cart->getQuote()->getGrandTotal();
            
            $ItemsQty = $this->context->getValue('ItemsQty');
            $GrandTotal = $this->context->getValue('GrandTotal');
        
            if ($this->_cart) {
                $quote = $this->_cart->getQuote();
                $quote = $this->_checkoutSession->getQuote();
                //   $ItemsQty = $quote->getItemsQty();
              //     $GrandTotal = $quote->getGrandTotal();
                
            }
            
             $outputArray['page_type'] = "home";

             $outputArray['cart_total_items'] = "";
             $outputArray['cart_total_value'] = "";


            if ($ItemsQty !== null) {
                $outputArray['cart_total_items'] = number_format($ItemsQty, 2, ".", "");
            }

            if ($GrandTotal !== null) {
                $outputArray['cart_total_value'] = number_format($GrandTotal, 2, ".", "");
            }
             
             
             $outputArray['site_section'] = "Clothing";
             $outputArray['tealium_event'] = "page_view";
                 
             //$outputArray['telium_event'] = "page_view";
        }
        
        return $outputArray;
    }

    public function getSearch($productOnPage = [])
    {
        $store = $this->store;
        $page = $this->page;
        $searchBlock = $page->getLayout()->getBlock('search.result');
        $outputArray = [];
        $ItemsQty = false;
        $GrandTotal = false;
        
        /* echo '<pre>';
        print_r($searchBlock->getTerms());
        die;*/
        if ($searchBlock === false) {
            return $outputArray;
        }
        //$outputArray['site_region'] =
            //$this->_objectManager->get('Magento\Framework\Locale\Resolver')->getLocale() ? : "";


        $outputArray['site_region'] =   $this->_localeResolver->getLocale() ?: "";
        $outputArray['site_currency'] = $store->getCurrentCurrencyCode() ? : "";
        $outputArray['page_name'] = "search results";
        $outputArray['page_type'] = "search";
        $outputArray['search_results'] = $searchBlock->getResultCount() . "" ? : "";
        $outputArray['search_keyword'] =
            $page->helper('Magento\CatalogSearch\Helper\Data')->getEscapedQueryText() ? : "";

        $browseQuery = $_SERVER['QUERY_STRING'];
        $browseRefineValue = array();
        $browseRefineType = array();
        if ($browseQuery) {
            parse_str($browseQuery, $get_array);
            foreach ($get_array as $key => $value) {
                if ($key != 'q') {
                    
                    //$_product = $this->_objectManager->create('Magento\Catalog\Model\Product');

                    $_product = $this->productFactory->create();
                    if ($key == 'price') {
                        $browseRefineValue[] = $value;
                    }
                    $_attributeId = $_product->getResource()->getAttribute($key);
                    if (!empty($_attributeId)) {
                        $browseRefineValue[] = $_attributeId->getSource()->getOptionText($value);
                    }
                    $browseRefineType[] =  $key;
                }
            }
        }

        
        
        if (!empty($browseRefineType)) {
            $outputArray['browse_refine_type'] = $browseRefineType ? : "";
        }
        if (!empty($browseRefineValue)) {
            $outputArray['browse_refine_value'] = $browseRefineValue ? : "";
        }
       
        
        //$locale = $this->_objectManager->get('Magento\Framework\Locale\Resolver')->getLocale();

        $locale = $this->_localeResolver->getLocale();
        $locale = explode("_", $locale);
        $ItemsQty = $this->context->getValue('ItemsQty');
        $GrandTotal = $this->context->getValue('GrandTotal');
        
        if ($this->_cart->getQuote()) {
         //   $ItemsQty = $this->_cart->getQuote()->getItemsQty();
        //    $GrandTotal =$this->_cart->getQuote()->getGrandTotal();
        }
        //$outputArray['cart_total_items'] = number_format($ItemsQty, 2, ".", "");
        //$outputArray['cart_total_value'] = number_format($GrandTotal, 2, ".", "");

        if (is_numeric($ItemsQty)) {
            $outputArray['cart_total_items'] = number_format($ItemsQty, 2, ".", "");
        } else {
            $outputArray['cart_total_items'] = "0.00"; // Default value or error handling
        }
        
        if (is_numeric($GrandTotal)) {
            $outputArray['cart_total_value'] = number_format($GrandTotal, 2, ".", "");
        } else {
            $outputArray['cart_total_value'] = "0.00"; // Default value or error handling
        }
        
         
        $outputArray['country_code'] = strtolower($locale[1]) ? : '';
        $outputArray['language_code'] = $locale[0] ? : '';
        $outputArray['tealium_event'] = "search";
        
        if (!empty($productOnPage)) {
            $outputArray['product_on_page'] = $productOnPage ? : "";
        }
        $outputArray['site_section'] = 'Clothing';
        //$outputArray['search_results'] = '';

            
        return $outputArray;
    }

    public function getCategory($productOnPage = [], $productOnPageId = [])
    {
        $store = $this->store;
        $page = $this->page;
        $section = false;
        $category = false;
        $subcategory = false;
        $categoryId =  false;
        $category_name =  false;
        $catProductList = [];
        $browseRefineType = [];
        $browseRefineValue = [];
        $manufacturer = [];
        $ItemsQty = false;
        $GrandTotal = false;

        if ($_category = $this->_registry->registry('current_category')) {

            $parent = false;
            $grandparent = false;

            // check for parent and grandparent
            if ($_category->getParentId()) {
                
                /*$parent =
                    $this->_objectManager->create('Magento\Catalog\Model\Category')
                        ->load($_category->getParentId());*/

                $parent = $this->categoryRepository->get($_category->getParentId());

                if ($parent->getParentId()) {
                    /*$grandparent =
                        $this->_objectManager->create('Magento\Catalog\Model\Category')
                            ->load($parent->getParentId());*/
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
                $category_name = $category.':'.$subcategory;
            }
            if ($_category->getLevel() == '4') {
                
                $categoryName = explode("/", $_category->getUrlPath());
                $cat = strtok($_category->getUrlPath(), '/');
                foreach ($categoryName as $catName) {
                    $catNames[] = ucfirst(strstr($catName, "-", true));
                }
                $catNames = implode(":", $catNames);
                $category_name = ucfirst($cat).$catNames;
            }
        }
    
        /*$objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $categoryFactory = $objectManager->get('\Magento\Catalog\Model\CategoryFactory');
        $categoryp = $categoryFactory->create()->load($categoryId);

        $categoryProducts = $categoryp->getProductCollection()
                             ->addAttributeToSelect('*');*/


        $categoryp = $this->categoryFactory->create()->load($categoryId);

        $categoryProducts = $categoryp->getProductCollection()->addAttributeToSelect('*');
                             
        
         /* Getting query string for browse_refine_type*/
        $browseQuery = $_SERVER['QUERY_STRING'];
        if ($browseQuery) {
            parse_str($browseQuery, $get_array);
            foreach ($get_array as $key => $value) {
                //$_product = $this->_objectManager->create('Magento\Catalog\Model\Product');
                $_product = $this->productFactory->create();


                if ($key == 'price') {
                    $browseRefineValue[] = $value;
                }
                $_attributeId = $_product->getResource()->getAttribute($key);
                if (!empty($_attributeId)) {
                    $browseRefineValue[] = $_attributeId->getSource()->getOptionText($value);
                }
                $browseRefineType[] =  $key;
            }
        }
        $ItemsQty = $this->context->getValue('ItemsQty');
        $GrandTotal = $this->context->getValue('GrandTotal');
        
        if ($this->_cart->getQuote()) {
         //   $ItemsQty = $this->_cart->getQuote()->getItemsQty();
        //    $GrandTotal =$this->_cart->getQuote()->getGrandTotal();
        }
        
        foreach ($productOnPageId as $catProducts) {

            
            
            //$productCat = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($catProducts);
            
            $productCat = $this->productRepository->getById($catProducts);

            $manufacturerValue = $productCat->getAttributeText('manufacturer');
            
            if ($manufacturerValue !== false) {
                $manufacturer[] = $manufacturerValue;
            }
            

            if (is_array($catProducts) && count($catProducts) > 0) {
                $catProductList[] = $catProducts;
            }
            
            
                        
        }
        
        $titleBlock = $page->getLayout()->getBlock('category.products.list');
        
        $outputArray = [];

        $outputArray['site_region'] =   $this->_localeResolver->getLocale() ?: "";

        $outputArray['site_currency'] = $store->getCurrentCurrencyCode() ? : "";
        $outputArray['page_name'] =
            $_category ? ($_category->getName() ? : "") : "";
        $outputArray['page_type'] = "category";
        $outputArray['page_section_name'] = $section ? : "";
        $outputArray['page_category_name'] = $category ? : "";
        $outputArray['page_subcategory_name'] = $subcategory ? : "";

        if ($browseRefineType) {
            $outputArray['browse_refine_type'] = $browseRefineType ? : "";
        }
        if ($browseRefineValue) {
            $outputArray['browse_refine_value'] = $browseRefineValue ? : "";
        }
        
       

        $locale = $this->_localeResolver->getLocale();
        $locale = explode("_", $locale);
        if (!empty($locale)) {
            $outputArray['country_code'] = strtolower($locale[1]) ? : '';
            $outputArray['language_code'] = $locale[0] ? : '';
        }
        
        $outputArray['site_section'] = "Clothing";
        //$outputArray['cart_total_items'] = number_format($ItemsQty, 2, ".", "") ? : "";
        //$outputArray['cart_total_value'] = number_format($GrandTotal, 2, ".", "") ? : "";

        // Check if $ItemsQty and $GrandTotal are not null before formatting them
        if ($ItemsQty !== null) {
            $outputArray['cart_total_items'] = number_format($ItemsQty, 2, ".", "");
        } else {
            $outputArray['cart_total_items'] = '';
        }

        if ($GrandTotal !== null) {
            $outputArray['cart_total_value'] = number_format($GrandTotal, 2, ".", "");
        } else {
            $outputArray['cart_total_value'] = '';
        }


        $outputArray['category_id'] = $categoryId ? : "";
        $outputArray['category_name'] = $category_name ? : $subcategory;
        $outputArray['tealium_event'] = "category_view";
        
        if (!empty($productOnPage)) {
            $outputArray['product_on_page'] = $productOnPage ? : "";
        }
        return $outputArray;
    }

    public function getProductPage()
    {
        $categoryId =  false;
        $category_name =  false;
        $catProductList = false;
        $ItemsQty = false;
        $GrandTotal = false;
        $store = $this->store;
        $page = $this->page;
        $subcategory = false;
        $parentCatName = false;
        $categoryName = false;
        
        $_product = $this->_registry->registry('current_product');

        $outputArray = [];
        //$outputArray['site_region'] =
            //$this->_objectManager->get('Magento\Framework\Locale\Resolver')->getLocale() ? : "";

        $outputArray['site_region'] =   $this->_localeResolver->getLocale() ?: "";
        $outputArray['site_currency'] = $store->getCurrentCurrencyCode() ? : "";
        $outputArray['page_name'] =
            $_product ? ($_product->getName() ? : "") : "";
        $outputArray['page_type'] = "product";

        $mediaUrl = $this->_store
        ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . 'catalog/product';
        


        /*echo $mediaUrl = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')
            ->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/product';*/

       
        // THE FOLLOWING NEEDS TO BE MATCHED ARRAYS (SAME NUMBER OF ELEMENTS)
        if ($_product) {
           
            if (!(
            $outputArray['product_id'] = [$_product->getId()]
            )) {
                $outputArray['product_id'] = [];
            }

            if (!(
            $outputArray['product_sku'] = [
                $_product->getSku()
            ]
            )) {
                $outputArray['product_sku'] = [];
            }

            if (!(
                $outputArray['product_url'] = [
                    $_product->getProductUrl()
                ]
                )) {
                    $outputArray['product_url'] = [];
            }


            if (!(
                $outputArray['product_on_page'] = [
                    $_product->getSku()
                ]
                )) {
                    $outputArray['product_on_page'] = [];
            }

            if (!(
                $outputArray['product_image_url'] = [
                    $mediaUrl.$_product->getImage()
                ]
                )) {
                    $outputArray['product_image_url'] = [];
            }

            if (!(
            $outputArray['product_name'] = [
                $_product->getName()
            ]
            )) {
                $outputArray['product_name'] = [];
            }

            //$productCat = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($_product->getId());

            $productCat = $this->productRepository->getById($_product->getId());

            $manufacturer = $productCat->getAttributeText('manufacturer');
            if ($manufacturer === false) {
                $outputArray['product_brand'] = [""];
            } else {
                $outputArray['product_brand'] = [$manufacturer];
            }

            if (!(
            $outputArray['product_unit_price'] = [
                number_format($_product->getFinalPrice(), 2, '.', '')
            ]
            )) {
                $outputArray['product_unit_price'] = [];
            }

            if (!(
            $outputArray['product_list_price'] = [
                number_format($_product->getData('price'), 2, '.', '')
            ]
            )) {
                $outputArray['product_list_price'] = [];
            }
            
            $categoryId = $_product->getCategoryIds();
            
            $categoriesIds = $_product->getCategoryIds();
            if ($categoriesIds) {
                foreach ($categoriesIds as $categoryId) {
                    //$category = $this->_objectManager->create('Magento\Catalog\Model\Category')
                            //->load($categoryId);

                    $category = $this->categoryFactory->create()->load($categoryId);
                    $categoryName =  $category->getName();
                    $categoryIds =  $categoryId;
                    
                    
                    /*$parent =
                    $this->_objectManager->create('Magento\Catalog\Model\Category')
                    ->load($category->getParentId());*/

                    $parent = $this->categoryFactory->create()->load($category->getParentId());


                    if ($parent->getName() != "Default Category") {
                        $parentCatName = $parent->getName();
                    }
                }
            }
            
            
            $outputArray['category_id'] = $categoryId ? : "";
            $outputArray['category_name'] = $parentCatName ? : "";
            $outputArray['product_subcategory'] = [$categoryName] ? : "";
                        
        } else {
            $outputArray['product_id'] = [];
            $outputArray['product_sku'] = [];
            $outputArray['product_name'] = [];
            $outputArray['product_brand'] = [];
            $outputArray['product_unit_price'] = [];
            $outputArray['product_list_price'] = [];
        }
        $_category = $this->_registry->registry('current_category');
        if ($_category) {
            $categoryId = $_category->getEntityId();
            
            //$categoryFactory = $this->_objectManager->get('\Magento\Catalog\Model\CategoryFactory');
            //$categoryp = $categoryFactory->create()->load($categoryId);

            $categoryp = $this->categoryFactory->create()->load($categoryId);

            $categoryProducts = $categoryp->getProductCollection()
                                ->addAttributeToSelect('*');
            
            /*foreach($categoryProducts as $catProducts){
                $catProductList[] = $catProducts['entity_id'];
            }*/

            $catProductList = [];
            foreach ($categoryProducts as $catProduct) {
                $catProductList[] = $catProduct['entity_id'];
            }
            
        }
        $ItemsQty = $this->context->getValue('ItemsQty');
        $GrandTotal = $this->context->getValue('GrandTotal');
        if ($this->_cart->getQuote()) {
         //   $ItemsQty = $this->_cart->getQuote()->getItemsQty();
        //    $GrandTotal =$this->_cart->getQuote()->getGrandTotal();
        }

        $outputArray['product_price'] = $outputArray['product_unit_price'];
        $outputArray['product_original_price'] =
            $outputArray['product_list_price'];

        if ($this->_registry->registry('current_category')) {
            if ($this->_registry->registry('current_category')->getName()) {
                $outputArray['product_category'] = [$this->_registry->registry('current_category')->getName()];
                $outputArray['product_subcategory'] = [$this->_registry->registry('current_category')->getName()];
            } else {
                $outputArray['product_category'] = [""];
            }
        } elseif ($_product) {
            $cats = $_product->getCategoryIds();
            if (count($cats)) {
                $firstCategoryId = $cats[0];
                //$_category = $this->_objectManager->create('Magento\Catalog\Model\Category')->load($firstCategoryId);

                $_category = $this->categoryFactory->create()->load($firstCategoryId);

                $outputArray['product_category'] = [
                    $_category->getName()
                ];
                
            } else {
                $outputArray['product_category'] = [""];
            }
        }

      
        $outputArray['site_section'] = "Clothing";
        $outputArray['tealium_event'] = "product_view";
        //$locale = $this->_objectManager->get('Magento\Framework\Locale\Resolver')->getLocale();

        $locale = $this->_localeResolver->getLocale();
        $locale = explode("_", $locale);
        if (!empty($locale)) {
            $outputArray['country_code'] = strtolower($locale[1]) ? : '';
            $outputArray['language_code'] = $locale[0] ? : '';
        }
        //$outputArray['cart_total_items'] = number_format($ItemsQty, 2, ".", "") ? : "";
        //$outputArray['cart_total_value'] = number_format($GrandTotal, 2, ".", "") ? : "";

        // Check if $ItemsQty and $GrandTotal are not null before formatting them
        if ($ItemsQty !== null) {
            $outputArray['cart_total_items'] = number_format($ItemsQty, 2, ".", "");
        } else {
            $outputArray['cart_total_items'] = '';
        }

        if ($GrandTotal !== null) {
            $outputArray['cart_total_value'] = number_format($GrandTotal, 2, ".", "");
        } else {
            $outputArray['cart_total_value'] = '';
        }

        return $outputArray;
    }

    public function getCartPage()
    {
        $store = $this->store;
        $page = $this->page;

        $checkout_ids = [];
        $checkout_skus = [];
        $checkout_names = [];
        $checkout_qtys = [];
        $checkout_prices = [];
        $checkout_original_prices = [];
        $checkout_brands = [];
        $categoryName = [];
        $GrandTotal = false;
        $ItemsQty = false;
        $checkout_images = [];
        $checkout_url = [];
        $checkout_catId = [];
        $parentCatName = [];
        $itemDiscountAmmount = [];
        $product_promo_code = false;
        
        $outputArray = [];
        
        
        /*$mediaUrl = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')
            ->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/product';*/

        $mediaUrl = $this->_store
            ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . 'catalog/product';
        
        if ($this->_checkoutSession) {
        
            $quote = $this->_checkoutSession->getQuote();
            $ItemsQty = $this->context->getValue('ItemsQty');
            $GrandTotal = $this->context->getValue('GrandTotal');
            
            
            
            // https://magento.stackexchange.com/questions/230052/what-is-alternative-for-cacheable-false
            
            if (!empty($ItemsQty)) {
                foreach ($quote->getAllVisibleItems() as $item) {
                    $itemTotal[] = $item->getPrice();
                }
                if (!empty($itemTotal)) {
                    $itemTotals = array_sum($itemTotal);
                }
                
                foreach ($quote->getAllVisibleItems() as $item) {
                    $checkout_ids[] = $item->getProductId();
                    $checkout_skus[] = $item->getSku();
                    $checkout_names[] = $item->getName();
                    $checkout_catId[] = $item->getCategoryIds();
                    
                    
                    //$productRepository = $this->_objectManager->get('\Magento\Catalog\Model\ProductRepository');

                    $productRepository = $this->productRepository;
 
                    $productCategoryId = $productRepository->getById($item->getProductId());
                     
                    $categoryIds = $productCategoryId->getCategoryIds();
                    
                    //$productCat = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($item->getProductId());

                    $productCat = $this->productRepository->getById($item->getProductId());
                    $manufacturer[] = $productCat->getAttributeText('manufacturer');
                    
                    $categoriesIds = $productCat->getCategoryIds();
                    
                    if ($categoryIds) {
                        foreach ($categoriesIds as $catId) {
                            if ($catId != 2) {
                                //$category = $this->_objectManager->create('Magento\Catalog\Model\Category')
                                        //->load($catId);

                                $category = $this->categoryFactory->create()->load($catId);
                            }
                        }
                        $categoryName[] =  $category->getName();
                        
                        //$parent = $this->_objectManager->create('Magento\Catalog\Model\Category')
                                    //->load($category->getParentId());

                        $parent = $this->categoryFactory->create()->load($category->getParentId());
                        
                        if ($parent->getName() != "Default Category") {
                            $parentCatName[] = $parent->getName();
                        }
                    }
                    
                    

                    //$productCat = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($item->getId());
                    //$productRepository = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($item->getProductId());


                    $productCat = $this->productRepository->getById($item->getId());
                    $productRepository = $this->productRepository->getById($item->getProductId());


                    
                    
                    $checkout_url[] = $productRepository->getProductUrl();
                    $checkout_images[] = $mediaUrl.$productRepository->getImage();
                    
                    //$checkout_images[] = $mediaUrl.$item->getImage();
                    $checkout_qtys[] = number_format($item->getQty(), 0, ".", "");
                    $checkout_prices[] =
                        number_format($item->getPrice(), 2, ".", "");
                    $checkout_original_prices[] =
                        number_format($item->getProduct()->getPrice(), 2, ".", "");
                    $checkout_brands[] = $item->getProduct()->getBrand();
                    
                    $getCoupon = $this->_objectManager->get('Magento\Checkout\Block\Cart\Coupon');
            
                    $product_promo_code = $getCoupon->getCouponCode();
                    
                    if (!empty($product_promo_code)) {
                        
                        
                        
                        
                        //$couponRules = $this->_objectManager->get('Magento\SalesRule\Model\Coupon');
                        //$saleRule = $this->_objectManager->get('\Magento\SalesRule\Model\Rule');
                        
                        //$ruleId =   $couponRules->loadByCode($product_promo_code)->getRuleId();
                        //$rule = $saleRule->load($ruleId);

                        $couponInstance = $this->couponFactory->create();
                        $ruleId = $couponInstance->loadByCode($product_promo_code)->getRuleId();
        
                        $ruleInstance = $this->ruleFactory->create();
                        $rule = $ruleInstance->load($ruleId);




                        //by_fixed by_percent cart_fixed buy_x_get_y
                        $discountAmount = $rule->getDiscountAmount();
                        $itemSku = $item->getSku();
                        $itemPrice = $item->getPrice();
                        if ($rule->getSimpleAction() == 'by_percent') {
                                $itemPrice = $item->getPrice();
                                $itemQty = $item->getQty();
                                //if (!in_array($itemSku, $checkout_skus)){
                                    $itemDiscountAmmount[] = number_format($itemPrice*$discountAmount/100*$itemQty, 2, ".", "");
                                //}
                        } elseif ($rule->getSimpleAction() == 'by_fixed' && $rule->getSimpleAction() == 'by_fixed') {
                                $itemQty = $item->getQty();
                        //        if (!in_array($itemSku, $checkout_skus)){
                                    $itemDiscountAmmount[] = number_format($itemPrice*$discountAmount/100*$itemQty, 2, ".", "");
                                    
                            //    }
                                
                        }
                        
                    }
                    
                }
            }
        }
        
        //print_r($itemDiscountAmmount);
        //die;
        //$outputArray['site_region'] =
            //$this->_objectManager->get('Magento\Framework\Locale\Resolver')->getLocale() ? : "";

        $outputArray['site_region'] =   $this->_localeResolver->getLocale() ?: "";
        $outputArray['site_currency'] = $store->getCurrentCurrencyCode() ? : "";

        $titleBlock = $page->getLayout()->getBlock('page.main.title');
        if ($titleBlock) {
            $outputArray['page_name'] =
                $page->getLayout()->getBlock('page.main.title')->getPageTitle() ? : "";
            $outputArray['page_type'] = "cart";
        } else {
            $outputArray['page_name'] = "Cart";
            $outputArray['page_type'] = "cart";
        }
         
         
        if ($product_promo_code) {
            $outputArray['product_promo_code'] = [$product_promo_code] ? :'';
            $outputArray['product_discount_amount'] = $itemDiscountAmmount ? :'';
        }
        
        // THE FOLLOWING NEEDS TO BE MATCHED ARRAYS (SAME NUMBER OF ELEMENTS)
        //$locale = $this->_objectManager->get('Magento\Framework\Locale\Resolver')->getLocale();

        $locale = $this->_localeResolver->getLocale();
        $locale = explode("_", $locale);
        if (!empty($locale)) {
            $outputArray['country_code'] = strtolower($locale[1]) ? : '';
            $outputArray['language_code'] = $locale[0] ? : '';
        }
        
        //print_r($checkout_url);
        
        $outputArray['product_image_url'] = $checkout_images ? : [];
        $outputArray['product_subcategory'] = $parentCatName ? : [];
        $outputArray['product_url'] = $checkout_url ? : [];
        //$outputArray['cart_total_items'] = number_format($ItemsQty, 2, ".", "") ? : [];;
        //$outputArray['cart_total_value'] = number_format($GrandTotal, 2, ".", "") ? : [];

        // Check if $ItemsQty and $GrandTotal are not null before formatting them
        if ($ItemsQty !== null) {
            $outputArray['cart_total_items'] = number_format($ItemsQty, 2, ".", "");
        } else {
            $outputArray['cart_total_items'] = '';
        }

        if ($GrandTotal !== null) {
            $outputArray['cart_total_value'] = number_format($GrandTotal, 2, ".", "");
        } else {
            $outputArray['cart_total_value'] = '';
        }

        if (empty($manufacturer)) {
            $outputArray['product_brand'] = [""];
        } else {
            $outputArray['product_brand'] = $manufacturer ? : [];
        }
        
        
        if ($outputArray['page_name'] != 'Checkout' && $outputArray['page_name'] != 'Shopping Cart') {
            $outputArray['category_id'] = $checkout_catId ? : "";
            $outputArray['category_name'] = $categoryName ? : "";
            $outputArray['product_subcategory'] = $parentCatName ? : "";
            $outputArray['site_section'] = "Clothing";
        
        }
        $outputArray['product_id'] = $checkout_ids ? : [];
        $outputArray['product_sku'] = $checkout_skus ? : [];
        $outputArray['product_name'] = $checkout_names ? : [];
        $outputArray['product_category'] = $categoryName ? : [];
        $outputArray['product_quantity'] = $checkout_qtys ? : [];
        $outputArray['product_unit_price'] = $checkout_prices ? : [];
        $outputArray['product_list_price'] = $checkout_original_prices ? : [];
            
        $outputArray['tealium_event'] = "cart_view";
        if ($outputArray['page_name'] == 'Checkout') {
            $outputArray['tealium_event'] = "checkout";
        }
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
        $ids = [];
        $skus = [];
        $names = [];
        $brands = [];
        $prices = [];
        $original_prices = [];
        $qtys = [];
        $discounts = [];
        $discount_quantity = [];
        $productImage = [];
        $productUrl = [];
        $categoryName = [];
        $manufacturer = [];
        $parentCatName = [];

        if ($this->customerSession->isLoggedIn()) {
            $customer = $this->customerSession->getCustomer();
        
        //if ($this->_objectManager->get('Magento\Customer\Model\Session')->isLoggedIn()) {
            //$customer = $this->_objectManager->get('Magento\Customer\Model\Session')->getCustomer();
            $customer_id = $customer->getEntityId();
            $customer_email = $customer->getEmail();
            $groupId = $customer->getGroupId();
            //$customer_type =
                //$this->_objectManager->create('Magento\Customer\Model\Group')->load($groupId)->getCode();


            $group = $this->groupRepository->getById($groupId);
            $customer_type = $group->getCode();
        }

        $orderInstance = $this->orderFactory->create();

        //if ($this->_objectManager->create('Magento\Sales\Model\Order')) {
        if ($orderInstance) {

            
            /** @var \Magento\Checkout\Model\Session $checkoutSession */
            //$checkoutSession = $this->_objectManager->get('Magento\Checkout\Model\Session');

            $checkoutSession = $this->_checkoutSession;

            //$order = $this->_objectManager->create('Magento\Sales\Model\Order')
            //->loadByIncrementId($checkoutSession->getLastOrderId());
            $order = $checkoutSession->getLastRealOrder();
            //$mediaUrl = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')
            //->getStore()
            //->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/product';

            $mediaUrl = $this->_store
                ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . 'catalog/product';
            
            foreach ($order->getAllVisibleItems() as $item) {
                
                $ids[] = $item->getProductId();
                $skus[] = $item->getSku();
                
                //$productCat = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($item->getProductId());

                

                $productCat = $this->productFactory->create()->load($item->getProductId());
                $manufacturer[] = $productCat->getAttributeText('manufacturer');
                
                $categoriesIds = $productCat->getCategoryIds();
                $productUrl[] = $productCat->getProductUrl();
                $productImage[] = $mediaUrl.$productCat->getImage();
                
                //$productRepository = $this->_objectManager->get('\Magento\Catalog\Model\ProductRepository');

                $productRepository = $this->productRepository;
                $productCategoryId = $productRepository->getById($item->getProductId());
                $categoryIds = $productCategoryId->getCategoryIds();
                
                if ($categoryIds) {
                    foreach ($categoriesIds as $catId) {
                        if ($catId != 2) {
                            //$category = $this->_objectManager->create('Magento\Catalog\Model\Category')
                                    //->load($catId);


                            $category = $this->categoryRepository->get($catId);
                        }
                    }
                    $categoryName[] =  $category->getName();
                    
                    //$parent = $this->_objectManager->create('Magento\Catalog\Model\Category')
                                //->load($category->getParentId());

                    $parent = $this->categoryRepository->get($category->getParentId());
                    if ($parent->getName() != "Default Category") {
                        $parentCatName[] = $parent->getName();
                    }
                }
                
                $names[] = $item->getName();
                
                $qtys[] = number_format($item->getQtyOrdered(), 0, ".", "");
                $prices[] = number_format($item->getPrice(), 2, ".", "");
                $original_prices[] =
                    number_format($item->getProduct()->getPrice(), 2, ".", "");
                $discount =
                    number_format($item->getDiscountAmount(), 2, ".", "");
                $discounts[] = $discount;
                $applied_rules = explode(",", $item->getAppliedRuleIds() ?? '');
                $discount_object = [];
                $brands[] = $item->getProduct()->getBrand();
                foreach ($applied_rules as $rule) {

                    $quantity = "";

                    $ruleInstance = $this->ruleFactory->create();
                    $discountQty = $ruleInstance->load($rule)->getDiscountQty();
                    
                    //$discountQty = $this->_objectManager->create('Magento\SalesRule\Model\Rule')
                    //->load($rule)
                    //->getDiscountQty();
                
                    if ($discountQty !== null) {
                        $quantity = number_format($discountQty, 0, ".", "");
                    } else {
                        // Handle the case where $discountQty is null, e.g., provide a default value or log an error.
                    }
                
                    $amount = "";
                    //$discountAmount = $this->_objectManager->create('Magento\SalesRule\Model\Rule')
                    //->load($rule)
                    //->getDiscountAmount();

                    $discountAmount = $this->ruleFactory->create()->load($rule)->getDiscountAmount();
                
                    if ($discountAmount !== null) {
                        $amount = number_format($discountAmount, 2, ".", "");
                    } else {
                        // Handle the case where $discountAmount is null, e.g., provide a default value or log an error.
                    }
                


                    $type = $this->ruleFactory->create()->load($rule)->getSimpleAction();

                    //$type = $this->_objectManager->create('Magento\SalesRule\Model\Rule')
                        //->load($rule)
                        //->getSimpleAction();

                    $discount_object[] = [
                        "rule" => $rule,
                        "quantity" => $quantity,
                        "amount" => $amount,
                        "type" => $type
                    ];
                }
                $discount_quantity[] = [
                    "product_id" => $item->getProductId(),
                    "total_discount" => $discount,
                    "discounts" => $discount_object
                ];
            }
        }
        
        
        $outputArray = [];

        //$outputArray['site_region'] =
            //$this->_objectManager->get('Magento\Framework\Locale\Resolver')->getLocale() ? : "";

        $outputArray['site_region'] =   $this->_localeResolver->getLocale() ?: "";
        $outputArray['site_currency'] = $store->getCurrentCurrencyCode() ? : "";
        $outputArray['page_name'] = "cart success";
        $outputArray['page_type'] = "order";
        $outputArray['order_id'] = $order->getIncrementId() ? : "";
        




        $outputArray['order_discount'] =
        $order->getDiscountAmount() !== null ? number_format($order->getDiscountAmount(), 2, ".", "") : "";

        $outputArray['order_subtotal'] =
            $order->getSubtotal() !== null ? number_format($order->getSubtotal(), 2, ".", "") : "";

        $outputArray['order_shipping'] =
            $order->getShippingAmount() !== null ? number_format($order->getShippingAmount(), 2, ".", "") : "";

        $outputArray['order_tax'] =
            $order->getTaxAmount() !== null ? number_format($order->getTaxAmount(), 2, ".", "") : "";

        $outputArray['order_payment_type'] =
            $order->getPayment()
                ? $order->getPayment()->getMethodInstance()->getTitle()
                : 'unknown';

        $outputArray['order_total'] =
            $order->getGrandTotal() !== null ? number_format($order->getGrandTotal(), 2, ".", "") : "";


            
            
        $outputArray['order_currency'] = $order->getOrderCurrencyCode() ? : "";
        $outputArray['customer_id'] = $customer_id ? : "";
        $outputArray['customer_email'] = $order->getCustomerEmail() ? : "";
        $outputArray['product_id'] = $ids ? : [];
        $outputArray['product_sku'] = $skus ? : [];
        $outputArray['product_name'] = $names ? : [];
        $outputArray['product_brand'] = $manufacturer ? : [];
        $outputArray['product_category'] = $categoryName ? : [];
        $outputArray['product_unit_price'] = $prices ? : [];
        $outputArray['product_list_price'] = $original_prices ? : [];
        $outputArray['product_price'] = $outputArray['product_unit_price'];
        $outputArray['product_original_price'] =
            $outputArray['product_list_price'];
        $outputArray['product_quantity'] = $qtys ? : [];
        $outputArray['product_discount'] = $discounts ? : [];
        //$outputArray['product_discounts'] = $discount_quantity ? : [];
        
        $product_promo_code = $order->getCouponCode();
    
        //$locale = $this->_objectManager->get('Magento\Framework\Locale\Resolver')->getLocale();

        $locale = $this->_localeResolver->getLocale();
        $outputArray['order_store'] = $locale ? : '';
        $locale = explode("_", $locale);
        if (!empty($locale)) {
            $outputArray['country_code'] = strtolower($locale[1]) ? : '';
            $outputArray['language_code'] = $locale[0] ? : '';
            
        }
        $shippingData = '';
        $shippingData = $order->getShippingAddress();
                
        //$customer = $this->_objectManager->get('Magento\Customer\Model\Session')->getCustomer();
        $customer = $this->customerSession->getCustomer();

        if ($customer->getDefaultShippingAddress()) {
            $outputArray['customer_city'] = $customer->getDefaultShippingAddress()->getCity() ? : [];
            $outputArray['customer_country'] = $customer->getDefaultShippingAddress()->getCountryId() ? : [];
            $outputArray['customer_first_name'] = $customer->getFirstname() ? : [];
            $outputArray['customer_last_name'] = $customer->getLastname() ? : [];
            $outputArray['customer_postal_code'] = $customer->getDefaultShippingAddress()->getPostcode() ? : [];
            $outputArray['customer_state'] = $customer->getDefaultShippingAddress()->getRegion() ? : [];
        }
        $outputArray['order_promo_code'] = $product_promo_code ? : [];
        $outputArray['order_shipping_amount'] = $order->getShippingAmount() ? : [];
        $outputArray['order_shipping_type'] = $order->getShippingMethod() ? : [];
        $outputArray['page_name'] = "Order Confirmation - Thank You";
        $outputArray['product_image_url'] = $productImage ? : [];
        $outputArray['product_promo_code'] = $product_promo_code ? : [];
        $outputArray['product_subcategory'] = $parentCatName ? : [];
        $outputArray['product_url'] = $productUrl ? : [];
        $outputArray['tealium_event'] = "purchase";
        
        
        $shippingMethod = $order->getShippingDescription();
        $order->getShippingMethod();
                
        return $outputArray;
    }

    public function getCustomerData()
    {
        $store = $this->store;
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

        /*if ($this->_objectManager->get('Magento\Customer\Model\Session')->isLoggedIn()) {
            $customer = $this->_objectManager->get('Magento\Customer\Model\Session')->getCustomer();*/

        if ($this->customerSession->isLoggedIn()) {
            $customer = $this->customerSession->getCustomer();
            if (!empty($customer)) {
                $customer_id = $customer->getEntityId();
                $customer_email = $customer->getEmail();
                
                $customer_firstname = $customer->getFirstname();
                $customer_lastname = $customer->getLastname();
                if ($customer->getDefaultShippingAddress()) {
                    $customer_postal_code = $customer->getDefaultShippingAddress()->getPostcode();
                    $country_id = $customer->getDefaultShippingAddress()->getCountryId();
                    $customer_city = $customer->getDefaultShippingAddress()->getCity();
                    $customer_region = $customer->getDefaultShippingAddress()->getRegion();
                }
                $groupId = $customer->getGroupId();
                //$customer_type =
                    //$this->_objectManager->create('Magento\Customer\Model\Group')->load($groupId)->getCode();


                $group = $this->groupRepository->getById($groupId);
                $customer_type = $group->getCode();
            }
            
        }
        
        $outputArray = [];

        //$outputArray['site_region'] =
            //$this->_objectManager->get('Magento\Framework\Locale\Resolver')->getLocale() ? : "";

        $outputArray['site_region'] =   $this->_localeResolver->getLocale() ?: "";
        $outputArray['site_currency'] = $store->getCurrentCurrencyCode() ? : "";
        $titleBlock = $page->getLayout()->getBlock('page.main.title');
        if ($titleBlock) {
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
        
        //$locale = $this->_objectManager->get('Magento\Framework\Locale\Resolver')->getLocale();

        $locale = $this->_localeResolver->getLocale();
        $locale = explode("_", $locale);
        if (!empty($locale)) {
            $outputArray['country_code'] = strtolower($locale[1]) ? : '';
            $outputArray['language_code'] = $locale[0] ? : '';
        }
        
        //$session =  $this->_objectManager->get("Magento\Checkout\Model\Session");

        $session =  $this->_checkoutSession;
        
        $ItemsQty = $this->context->getValue('ItemsQty');
        $GrandTotal = $this->context->getValue('GrandTotal');
        
        if ($session->getQuote()) {
            $quote =$session->getQuote();
        //    $ItemsQty = $quote->getItemsQty();
        //    $GrandTotal = $quote->getGrandTotal();
        }
        if ($ItemsQty) {
            $outputArray['cart_total_items'] = number_format($ItemsQty, 2, ".", "") ? : "";
        }
        if ($GrandTotal) {
            $outputArray['cart_total_value'] = number_format($GrandTotal, 2, ".", "") ? : "";
        }
        
        $outputArray['customer_city'] = $customer_city ? : "";
        $outputArray['customer_country'] = $country_id ? : "";
        $outputArray['customer_first_name'] = $customer_firstname ? : "";
        $outputArray['customer_last_name'] = $customer_lastname ? : "";
        $outputArray['customer_postal_code'] = $customer_postal_code ? : "";
        $outputArray['customer_state'] = $customer_region ? : "";
        $outputArray['tealium_event'] = "page_view";

        return $outputArray;
    }
}
