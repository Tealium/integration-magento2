<?php

namespace Tealium\Tags\CustomerData;

use Magento\Customer\Model\Session as CustomerSession;
use Tealium\Tags\Helper\Product;
use Magento\Checkout\Model\Session as CheckoutSession;

class JsUpdateQty extends AbstractSection
{

    protected $_customerSession;

    protected $_productHelper;

    protected $_checkoutSession;

    public function __construct(
        CustomerSession $customerSession,
        Product $productHelper,
        CheckoutSession $checkoutSession
    ) {
        $this->_customerSession = $customerSession;
        $this->_productHelper = $productHelper;
        $this->_checkoutSession = $checkoutSession;
    }

    protected function buildSectionData()
    {
        // quote item ids whose quantity changed
        $itemIdList = $this->_customerSession->getTealiumQty();
        $this->_customerSession->unsTealiumQty();

        if (!$itemIdList) {
            return [];
        }

        $productIds = [];
        $quantities = [];
        foreach ($this->_checkoutSession->getQuote()->getAllVisibleItems() as $quoteItem) {
            if (in_array($quoteItem->getItemId(), (array)$itemIdList)) {
                // the simple product (configurable child) is reported
                $simple = $quoteItem->getOptionByCode('simple_product');
                $productIds[] = $simple && $simple->getProduct()
                    ? $simple->getProduct()->getId()
                    : $quoteItem->getProductId();
                $quantities[] = $quoteItem->getQty();
            }
        }

        $data = $this->_productHelper->getItemsData($productIds, $quantities);
        $data['tealium_event'] = 'cart_update_item_quanity';

        return ['data' => $data];
    }
}
