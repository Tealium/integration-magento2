<?php
/**
 * Created by PhpStorm.
 * User: svatoslavzilicev
 * Date: 25.08.17
 * Time: 12:20
 */

namespace Tealium\Tags\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\App\Bootstrap;
use \Magento\Framework\View\Layout;
 
//include('app/bootstrap.php');
//use \Magento\Checkout\Model\Cart;

class TealiumData extends AbstractHelper
{

    // Declare store and page as static vars and define setter methods
    private $store;
    private $page;
    protected $_store;
    protected $_objectManager;
	protected $_isScopePrivate;
	//protected $_cart;

    /**
     * @var \Magento\Framework\Registry
     */

    protected $_registry;

    protected $_checkoutSession;
	protected $_checkoutSessionFactory;
	protected $_productRepository;
	protected $_layout;
	protected $httpContext;
    
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Api\Data\StoreInterface $store,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        \Magento\Checkout\Model\Session $checkoutSession,
		\Magento\Checkout\Model\Cart $cart,
		\Magento\Catalog\Model\ProductRepository $productRepository,
		\Magento\Framework\View\Layout $layout,
		\Magento\Framework\App\Http\Context $httpContext
		//Cart $cart
    ) {
		 $this->_isScopePrivate = true;
        $this->_store = $store;
        $this->_objectManager = $objectManager;
        $this->_registry = $registry;
		$this->context = $httpContext;
      //  $this->_checkoutSession = $checkoutSession;
	//	$this->_checkoutSessionFactory = $_checkoutSessionFactory;
		 $this->_checkoutSession  = $checkoutSession;
		$this->_layout = $layout;
		$this->_cart = $cart;
		$this->_layout->setIsPrivate(true);
		$this->_productRepository = $productRepository;
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
        $outputArray['site_region'] =
            $this->_objectManager->get('Magento\Framework\Locale\Resolver')->getLocale() ? : "";
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
		
		if($outputArray['page_name'] == 'Home Page'){
			
			$locale = $this->_objectManager->get('Magento\Framework\Locale\Resolver')->getLocale();
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
			 $outputArray['cart_total_items'] = number_format($ItemsQty, 2, ".", "") ? : '';
			 $outputArray['cart_total_value'] = number_format($GrandTotal, 2, ".", "") ? : '';
			 $outputArray['site_section'] = "Clothing";
			 $outputArray['tealium_event'] = "page_view";
			 	
			 //$outputArray['telium_event'] = "page_view";
		}
		
        return $outputArray;
    }

    public function getSearch($productOnPage = array())
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
        $outputArray['site_region'] =
            $this->_objectManager->get('Magento\Framework\Locale\Resolver')->getLocale() ? : "";
        $outputArray['site_currency'] = $store->getCurrentCurrencyCode() ? : "";
        $outputArray['page_name'] = "search results";
        $outputArray['page_type'] = "search";
        $outputArray['search_results'] = $searchBlock->getResultCount() . "" ? : "";
        $outputArray['search_keyword'] =
            $page->helper('Magento\CatalogSearch\Helper\Data')->getEscapedQueryText() ? : "";

        $browseQuery = $_SERVER['QUERY_STRING'];
        if($browseQuery){
            parse_str($browseQuery, $get_array);
            foreach($get_array as $key => $value){
				if($key != 'q'){
					
				$_product = $this->_objectManager->create('Magento\Catalog\Model\Product');
				if($key == 'price'){
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
		
		
        if(!empty($browseRefineType)){
            $outputArray['browse_refine_type'] = $browseRefineType ? : "";
        }
        if(!empty($browseRefineValue)){
            $outputArray['browse_refine_value'] = $browseRefineValue ? : "";
        }
       
		
		$locale = $this->_objectManager->get('Magento\Framework\Locale\Resolver')->getLocale();
		$locale = explode("_", $locale);
		$ItemsQty = $this->context->getValue('ItemsQty');
		$GrandTotal = $this->context->getValue('GrandTotal');
		
        if($this->_cart->getQuote()){
         //   $ItemsQty = $this->_cart->getQuote()->getItemsQty();
        //    $GrandTotal =$this->_cart->getQuote()->getGrandTotal();
        }
        $outputArray['cart_total_items'] = number_format($ItemsQty, 2, ".", "");
        $outputArray['cart_total_value'] = number_format($GrandTotal, 2, ".", "");
		 
        $outputArray['country_code'] = strtolower($locale[1]) ? : '';
        $outputArray['language_code'] = $locale[0] ? : '';
		$outputArray['tealium_event'] = "search";
		
        if(!empty($productOnPage)){
			$outputArray['product_on_page'] = $productOnPage ? : "";
		}
        $outputArray['site_section'] = 'Clothing';
		//$outputArray['search_results'] = '';

            
        return $outputArray;
    }

    public function getCategory($productOnPage = array(), $productOnPageId = array())
    {
		$store = $this->store;
        $page = $this->page;
		
        $section = false;
        $category = false;
        $subcategory = false;
		$categoryId =  false;
		$category_name =  false;
        $catProductList = false;
        $browseRefineType = false;
        $browseRefineValue = false;
        $manufacturer = false;
        $ItemsQty = false;
        $GrandTotal = false;

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
			
			$categoryId = $_category->getEntityId();
			if( $_category->getLevel() == '3'){
				$category_name = $category.':'.$subcategory;
			}
			if( $_category->getLevel() == '4'){
				
				$categoryName = explode("/",$_category->getUrlPath());
				$cat = strtok( $_category->getUrlPath(), '/' );
				foreach($categoryName as $catName){
					$catNames[] = ucfirst(strstr($catName, "-" , true)); 
				}
				$catNames = implode(":", $catNames);
				$category_name = ucfirst($cat).$catNames;
			}
        }
    
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();   
		$categoryFactory = $objectManager->get('\Magento\Catalog\Model\CategoryFactory');
		$categoryp = $categoryFactory->create()->load($categoryId);

		$categoryProducts = $categoryp->getProductCollection()
                             ->addAttributeToSelect('*');
							 
		
         /* Getting query string for browse_refine_type*/
        $browseQuery = $_SERVER['QUERY_STRING'];
        if($browseQuery){
            parse_str($browseQuery, $get_array);
            foreach($get_array as $key => $value){
				$_product = $this->_objectManager->create('Magento\Catalog\Model\Product');
				if($key == 'price'){
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
		
        if($this->_cart->getQuote()){
         //   $ItemsQty = $this->_cart->getQuote()->getItemsQty();
        //    $GrandTotal =$this->_cart->getQuote()->getGrandTotal();
        }
        
		foreach($productOnPageId as $catProducts){
			$productCat = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($catProducts);
			$manufacturer[] = $productCat->getAttributeText('manufacturer');
			$catProductList[] = $catProducts;
						
		}
		
		$titleBlock = $page->getLayout()->getBlock('category.products.list');
		
        $outputArray = [];
        $outputArray['site_region'] =
        $this->_objectManager->get('Magento\Framework\Locale\Resolver')->getLocale() ? : "";
        $outputArray['site_currency'] = $store->getCurrentCurrencyCode() ? : "";
        $outputArray['page_name'] =
            $_category ? ($_category->getName() ? : "") : "";
        $outputArray['page_type'] = "category";
        $outputArray['page_section_name'] = $section ? : "";
        $outputArray['page_category_name'] = $category ? : "";
        $outputArray['page_subcategory_name'] = $subcategory ? : "";
						 		
        //$outputArray['brand_name'] = $manufacturer ? : "";
        if($browseRefineType){
            $outputArray['browse_refine_type'] = $browseRefineType ? : "";
        }
        if($browseRefineValue){
            $outputArray['browse_refine_value'] = $browseRefineValue ? : "";
        }
		$locale = $this->_objectManager->get('Magento\Framework\Locale\Resolver')->getLocale();
		$locale = explode("_", $locale);
		if(!empty($locale)){
			$outputArray['country_code'] = strtolower($locale[1]) ? : '';
        	$outputArray['language_code'] = $locale[0] ? : '';
		} 
		
		$outputArray['site_section'] = "Clothing";
        $outputArray['cart_total_items'] = number_format($ItemsQty, 2, ".", "") ? : "";
        $outputArray['cart_total_value'] = number_format($GrandTotal, 2, ".", "") ? : "";
		$outputArray['category_id'] = $categoryId ? : "";
		$outputArray['category_name'] = $category_name ? : $subcategory;
		$outputArray['tealium_event'] = "category_view";
		
		if(!empty($productOnPage)){
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
        $outputArray['site_region'] =
            $this->_objectManager->get('Magento\Framework\Locale\Resolver')->getLocale() ? : "";
        $outputArray['site_currency'] = $store->getCurrentCurrencyCode() ? : "";
        $outputArray['page_name'] =
            $_product ? ($_product->getName() ? : "") : "";
        $outputArray['page_type'] = "product";
        $mediaUrl = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')
            ->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/product';
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

              $productCat = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($_product->getId());
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
					 $category = $this->_objectManager->create('Magento\Catalog\Model\Category')
							->load($categoryId);
					$categoryName =  $category->getName();
					$categoryIds =  $categoryId;
					$parent =
					$this->_objectManager->create('Magento\Catalog\Model\Category')
					->load($category->getParentId());
					if($parent->getName() != "Default Category"){
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
        if($_category){
            $categoryId = $_category->getEntityId();
            $categoryFactory = $this->_objectManager->get('\Magento\Catalog\Model\CategoryFactory');
            $categoryp = $categoryFactory->create()->load($categoryId);

            $categoryProducts = $categoryp->getProductCollection()
                                ->addAttributeToSelect('*');
            
            foreach($categoryProducts as $catProducts){
                $catProductList[] = $catProducts['entity_id'];
            }
        }
		$ItemsQty = $this->context->getValue('ItemsQty');
		$GrandTotal = $this->context->getValue('GrandTotal');
        if($this->_cart->getQuote()){
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
                $_category = $this->_objectManager->create('Magento\Catalog\Model\Category')->load($firstCategoryId);
                $outputArray['product_category'] = [
                    $_category->getName()
                ];
                
            } else {
                $outputArray['product_category'] = [""];
            }
        }

      
		$outputArray['site_section'] = "Clothing";
		$outputArray['tealium_event'] = "product_view";
		$locale = $this->_objectManager->get('Magento\Framework\Locale\Resolver')->getLocale();
		$locale = explode("_", $locale);
		if(!empty($locale)){
			$outputArray['country_code'] = strtolower($locale[1]) ? : '';
        	$outputArray['language_code'] = $locale[0] ? : '';
		}
        $outputArray['cart_total_items'] = number_format($ItemsQty, 2, ".", "") ? : "";
        $outputArray['cart_total_value'] = number_format($GrandTotal, 2, ".", "") ? : "";

        return $outputArray;
    }

    public function getCartPage()
    {
        $store = $this->store;
        $page = $this->page;

        $checkout_ids = false;
        $checkout_skus = false;
        $checkout_names = false;
        $checkout_qtys = false;
        $checkout_prices = false;
        $checkout_original_prices = false;
        $checkout_brands = [];
		$categoryName = false;
		$GrandTotal = false;
		$ItemsQty = false;
		$checkout_images = false;
		$checkout_url = false;
		$checkout_catId = false;
		$parentCatName = false; 
		$itemDiscountAmmount = false;
		$product_promo_code = false;
		
		$outputArray = [];
		
		
        $mediaUrl = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')
            ->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/product';
		
        if ($this->_checkoutSession) {
		
            $quote = $this->_checkoutSession->getQuote();
			$ItemsQty = $this->context->getValue('ItemsQty');
			$GrandTotal = $this->context->getValue('GrandTotal');
			
			
			
			// https://magento.stackexchange.com/questions/230052/what-is-alternative-for-cacheable-false
			
			if(!empty($ItemsQty)){
				foreach ($quote->getAllVisibleItems() as $item) {
					$itemTotal[] = $item->getPrice();
				}
				if(!empty($itemTotal)){
					$itemTotals = array_sum($itemTotal);
				}
				
				foreach ($quote->getAllVisibleItems() as $item) {
					$checkout_ids[] = $item->getProductId();
					$checkout_skus[] = $item->getSku();
					$checkout_names[] = $item->getName();
					$checkout_catId[] = $item->getCategoryIds();
					
					
					$productRepository = $this->_objectManager->get('\Magento\Catalog\Model\ProductRepository');
 
					$productCategoryId = $productRepository->getById($item->getProductId());
					 
					$categoryIds = $productCategoryId->getCategoryIds();
					
					$productCat = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($item->getProductId());
					$manufacturer[] = $productCat->getAttributeText('manufacturer');
					
					$categoriesIds = $productCat->getCategoryIds();
					
					if($categoryIds){
						foreach($categoriesIds as $catId){
							if($catId != 2){
								$category = $this->_objectManager->create('Magento\Catalog\Model\Category')
										->load($catId);
							}
						}
						$categoryName[] =  $category->getName();
						
						$parent = $this->_objectManager->create('Magento\Catalog\Model\Category')
									->load($category->getParentId());
						if($parent->getName() != "Default Category"){
							$parentCatName[] = $parent->getName();
						}
					}
				   	$productCat = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($item->getId());
					
					$productRepository = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($item->getProductId());
					
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
					
					if(!empty($product_promo_code)){
						
						$couponRules = $this->_objectManager->get('Magento\SalesRule\Model\Coupon');
						$saleRule = $this->_objectManager->get('\Magento\SalesRule\Model\Rule');
						
						$ruleId =   $couponRules->loadByCode($product_promo_code)->getRuleId();
						$rule = $saleRule->load($ruleId);
						//by_fixed by_percent cart_fixed buy_x_get_y 
						$discountAmount = $rule->getDiscountAmount();
						$itemSku = $item->getSku();
						$itemPrice = $item->getPrice();
						if($rule->getSimpleAction() == 'by_percent'){
								$itemPrice = $item->getPrice();
								$itemQty = $item->getQty();
								//if (!in_array($itemSku, $checkout_skus)){
									$itemDiscountAmmount[] = number_format($itemPrice*$discountAmount/100*$itemQty, 2, ".", "");
								//}
						} elseif($rule->getSimpleAction() == 'by_fixed' && $rule->getSimpleAction() == 'by_fixed'){
								$itemQty = $item->getQty();
						//		if (!in_array($itemSku, $checkout_skus)){
									$itemDiscountAmmount[] = number_format($itemPrice*$discountAmount/100*$itemQty, 2, ".", "");
									
							//	}
								
						}
						
					}
					
				}
			}
        }
		
		//print_r($itemDiscountAmmount);
		//die;
        $outputArray['site_region'] =
            $this->_objectManager->get('Magento\Framework\Locale\Resolver')->getLocale() ? : "";
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
 		
 		
        if($product_promo_code){
            $outputArray['product_promo_code'] = [$product_promo_code] ? :'';
			$outputArray['product_discount_amount'] = $itemDiscountAmmount ? :'';
        }
		
        // THE FOLLOWING NEEDS TO BE MATCHED ARRAYS (SAME NUMBER OF ELEMENTS)
		$locale = $this->_objectManager->get('Magento\Framework\Locale\Resolver')->getLocale();
		$locale = explode("_", $locale);
		if(!empty($locale)){
			$outputArray['country_code'] = strtolower($locale[1]) ? : '';
        	$outputArray['language_code'] = $locale[0] ? : '';
		}
		
		//print_r($checkout_url);
		
		$outputArray['product_image_url'] = $checkout_images ? : [];
        $outputArray['product_subcategory'] = $parentCatName ? : [];
        $outputArray['product_url'] = $checkout_url ? : [];
        $outputArray['cart_total_items'] = number_format($ItemsQty, 2, ".", "") ? : [];;
        $outputArray['cart_total_value'] = number_format($GrandTotal, 2, ".", "") ? : []; 

		if (empty($manufacturer)) {
			$outputArray['product_brand'] = [""];
		} else {
			$outputArray['product_brand'] = $manufacturer ? : [];
		}
		
		
		if($outputArray['page_name'] != 'Checkout' && $outputArray['page_name'] != 'Shopping Cart'){
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
		if( $outputArray['page_name'] == 'Checkout'){
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
        $ids = false;
        $skus = false;
        $names = false;
        $brands = false;
        $prices = false;
        $original_prices = false;
        $qtys = false;
        $discounts = false;
        $discount_quantity = false;
		$productImage = false;
		$productUrl = false;
		$categoryName = false;
		$manufacturer = false;
		$parentCatName = false;
		
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
			$mediaUrl = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')
            ->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/product';
			
            foreach ($order->getAllVisibleItems() as $item) {
				
                $ids[] = $item->getProductId();
                $skus[] = $item->getSku();
				
				$productCat = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($item->getProductId());
				$manufacturer[] = $productCat->getAttributeText('manufacturer');
				
				$categoriesIds = $productCat->getCategoryIds();
				$productUrl[] = $productCat->getProductUrl();
				$productImage[] = $mediaUrl.$productCat->getImage();
				
				$productRepository = $this->_objectManager->get('\Magento\Catalog\Model\ProductRepository');
				$productCategoryId = $productRepository->getById($item->getProductId());
				$categoryIds = $productCategoryId->getCategoryIds();
				
				if($categoryIds){
					foreach($categoriesIds as $catId){
						if($catId != 2){
							$category = $this->_objectManager->create('Magento\Catalog\Model\Category')
									->load($catId);
						}
					}
					$categoryName[] =  $category->getName();
					
					$parent = $this->_objectManager->create('Magento\Catalog\Model\Category')
								->load($category->getParentId());
					if($parent->getName() != "Default Category"){
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
                $applied_rules = explode(",", $item->getAppliedRuleIds());
                $discount_object = [];
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
	
		$locale = $this->_objectManager->get('Magento\Framework\Locale\Resolver')->getLocale();
		$outputArray['order_store'] = $locale ? : '';
		$locale = explode("_", $locale);
		if(!empty($locale)){
			$outputArray['country_code'] = strtolower($locale[1]) ? : '';
        	$outputArray['language_code'] = $locale[0] ? : '';
			
		}
		$shippingData = '';
		$shippingData = $order->getShippingAddress();
				
		$customer = $this->_objectManager->get('Magento\Customer\Model\Session')->getCustomer();
		if($customer->getDefaultShippingAddress()){
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

        if ($this->_objectManager->get('Magento\Customer\Model\Session')->isLoggedIn()) {
            $customer = $this->_objectManager->get('Magento\Customer\Model\Session')->getCustomer();
			if(!empty($customer)){
				$customer_id = $customer->getEntityId();
				$customer_email = $customer->getEmail();
				
				$customer_firstname = $customer->getFirstname();
				$customer_lastname = $customer->getLastname();
				if($customer->getDefaultShippingAddress()){
					$customer_postal_code = $customer->getDefaultShippingAddress()->getPostcode();
					$country_id = $customer->getDefaultShippingAddress()->getCountryId();
					$customer_city = $customer->getDefaultShippingAddress()->getCity();
					$customer_region = $customer->getDefaultShippingAddress()->getRegion();
				}
				$groupId = $customer->getGroupId();
				$customer_type =
					$this->_objectManager->create('Magento\Customer\Model\Group')->load($groupId)->getCode();
			}
			
        }
		
        $outputArray = [];

        $outputArray['site_region'] =
            $this->_objectManager->get('Magento\Framework\Locale\Resolver')->getLocale() ? : "";
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
		
		$locale = $this->_objectManager->get('Magento\Framework\Locale\Resolver')->getLocale();
		$locale = explode("_", $locale);
		if(!empty($locale)){
			$outputArray['country_code'] = strtolower($locale[1]) ? : '';
        	$outputArray['language_code'] = $locale[0] ? : '';
		}
		
		$session =  $this->_objectManager->get("Magento\Checkout\Model\Session");
		
		$ItemsQty = $this->context->getValue('ItemsQty');
		$GrandTotal = $this->context->getValue('GrandTotal');
		
		if($session->getQuote()){
			$quote =$session->getQuote();
		//	$ItemsQty = $quote->getItemsQty();
		//	$GrandTotal = $quote->getGrandTotal();
		}
		if($ItemsQty){
			$outputArray['cart_total_items'] = number_format($ItemsQty, 2, ".", "") ? : "";
		}
		if($GrandTotal){
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
