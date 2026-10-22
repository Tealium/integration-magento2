<?php

namespace Tealium\Tags\CustomerData;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Sales\Api\OrderRepositoryInterface;
use Tealium\Tags\Helper\Product;

class JsSaveOrder extends AbstractSection
{

    protected $_customerSession;

    protected $_productHelper;

    protected $_orderRepository;

    public function __construct(
        CustomerSession $customerSession,
        Product $productHelper,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->_customerSession = $customerSession;
        $this->_productHelper = $productHelper;
        $this->_orderRepository = $orderRepository;
    }

    protected function buildSectionData()
    {
        // "order_ids" of the checkout_onepage_controller_success_action event
        $order_id = $this->_customerSession->getTealiumCheckout();
        $this->_customerSession->unsTealiumCheckout();

        $orderId = is_array($order_id) ? reset($order_id) : $order_id;
        if (!$orderId) {
            return [];
        }

        $order = $this->_orderRepository->get((int)$orderId);

        $productIds = [];
        $quantities = [];
        foreach ($order->getAllItems() as $item) {
            $productIds[] = $item->getProductId();
            $quantities[] = $item->getQtyOrdered();
        }

        $data = $this->_productHelper->getItemsData($productIds, $quantities);
        $data['cart_id'] = $order_id;
        $data['tealium_event'] = 'save_order';

        return ['data' => $data];
    }
}
