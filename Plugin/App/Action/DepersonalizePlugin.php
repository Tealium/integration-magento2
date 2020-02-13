<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Tealium\Tags\Plugin\App\Action;

use Magento\PageCache\Model\DepersonalizeChecker;

/**
 * Class DepersonalizePlugin
 */
class DepersonalizePlugin
{
    /**
     * @var DepersonalizeChecker
     */
    protected $depersonalizeChecker;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
	protected $httpContext;
	protected $cacheable;
    /**
     * @param DepersonalizeChecker $depersonalizeChecker
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @codeCoverageIgnore
     */
    public function __construct(
        DepersonalizeChecker $depersonalizeChecker,
        \Magento\Checkout\Model\Session $checkoutSession,
		\Magento\Framework\App\Http\Context $httpContext
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->depersonalizeChecker = $depersonalizeChecker;
		$this->httpContext = $httpContext;
		$this->cacheable = false;
    }

    /**
     * After generate Xml
     *
     * @param \Magento\Framework\View\LayoutInterface $subject
     * @param \Magento\Framework\View\LayoutInterface $result
     * @return \Magento\Framework\View\LayoutInterface
     */
	 public function beforeGenerateXml(\Magento\Framework\View\LayoutInterface $subject)
    {
		$quote = $this->checkoutSession->getQuote();
		$ItemsQty = $quote->getItemsQty();
		$GrandTotal = $quote->getGrandTotal();
		 $this->httpContext->setValue(
           'ItemsQty',
           $ItemsQty,
            $ItemsQty
        );
		 $this->httpContext->setValue(
           'GrandTotal',
           $GrandTotal,
            $GrandTotal
        );
        return [];
    }
   
}
