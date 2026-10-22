<?php

namespace Tealium\Tags\Plugin\CustomerData;

use Magento\Checkout\CustomerData\Cart;
use Magento\Checkout\Model\Session as CheckoutSession;
use Psr\Log\LoggerInterface;

/**
 * Adds the cart totals used by the data layer to Magento's private "cart"
 * section, so that they reach the browser together with the minicart data
 * instead of through an extra request on every page view.
 */
class CartPlugin
{
    protected $_checkoutSession;

    protected $_logger;

    public function __construct(
        CheckoutSession $checkoutSession,
        LoggerInterface $logger
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_logger = $logger;
    }

    /**
     * @param Cart $subject
     * @param array $result
     * @return array
     */
    public function afterGetSectionData(Cart $subject, $result)
    {
        try {
            $quote = $this->_checkoutSession->getQuote();
            $itemsQty = $quote->getItemsQty();
            $grandTotal = $quote->getGrandTotal();

            $result['tealium'] = [
                'cart_total_items' => $itemsQty !== null ? number_format((float)$itemsQty, 2, ".", "") : "",
                'cart_total_value' => $grandTotal !== null ? number_format((float)$grandTotal, 2, ".", "") : ""
            ];
        } catch (\Throwable $e) {
            $this->_logger->error('Tealium cart totals failed: ' . $e->getMessage());
        }

        return $result;
    }
}
