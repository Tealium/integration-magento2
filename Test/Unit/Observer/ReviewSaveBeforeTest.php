<?php
namespace Tealium\Tags\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Registry;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Review\Model\Rating\Option;
use Magento\Review\Model\Rating\OptionFactory;
use PHPUnit\Framework\TestCase;

class ReviewSaveBeforeTest extends TestCase
{
    private $session;
    private $registry;
    private $optionFactory;
    private $observer;

    protected function setUp(): void
    {
        $this->session = $this->getMockBuilder(SessionManagerInterface::class)
            ->addMethods([
                'setTealiumReviewerNickname',
                'setTealiumReviewTitle',
                'setTealiumReviewDetail',
                'setTealiumReviewRating',
                'setTealiumReviewProductId'
            ])
            ->getMockForAbstractClass();
        $this->registry = new Registry();
        $this->optionFactory = $this->getMockBuilder(OptionFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->observer = new ReviewSaveBefore($this->session, $this->registry, $this->optionFactory);
        $this->registry->register('current_product', new DataObject(['id' => 7]));
    }

    private function event(array $reviewData)
    {
        return new Observer(['object' => new DataObject($reviewData)]);
    }

    /**
     * Ratings are inactive by default, so most reviews have none. That used
     * to raise "Trying to access array offset on null" and lose the review.
     */
    public function testReviewWithoutRatings()
    {
        $this->optionFactory->expects($this->never())->method('create');
        $this->session->expects($this->once())->method('setTealiumReviewRating')->with('');
        $this->session->expects($this->once())->method('setTealiumReviewProductId')->with(7);

        $this->observer->execute($this->event(['nickname' => 'n', 'title' => 't', 'detail' => 'd']));
    }

    public function testRatingIsTheOptionValueWhateverTheRatingId()
    {
        $option = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load', 'getId'])
            ->addMethods(['getValue'])
            ->getMock();
        $option->method('load')->with(33)->willReturnSelf();
        $option->method('getId')->willReturn(33);
        $option->method('getValue')->willReturn('4');
        $this->optionFactory->method('create')->willReturn($option);

        $this->session->expects($this->once())->method('setTealiumReviewRating')->with(4);

        $this->observer->execute($this->event(['ratings' => [9 => 33]]));
    }
}
