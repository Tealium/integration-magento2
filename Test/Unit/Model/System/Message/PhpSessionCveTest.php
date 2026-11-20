<?php
namespace Tealium\Tags\Model\System\Message;

use Magento\Framework\Escaper;
use Magento\Framework\Notification\MessageInterface;
use PHPUnit\Framework\TestCase;
use Tealium\Tags\Model\Runtime\PhpSessionCveCheck;

class PhpSessionCveTest extends TestCase
{
    private function createMessage(bool $exposed, string $version = '8.4.27'): PhpSessionCve
    {
        $check = $this->createMock(PhpSessionCveCheck::class);
        $check->method('isRuntimeExposed')->willReturn($exposed);
        $check->method('getRuntimeVersion')->willReturn($version);

        $escaper = $this->createMock(Escaper::class);
        $escaper->method('escapeHtml')->willReturnCallback(function ($value) {
            return htmlspecialchars((string) $value, ENT_QUOTES);
        });
        $escaper->method('escapeUrl')->willReturnCallback(function ($value) {
            return htmlspecialchars((string) $value, ENT_QUOTES);
        });

        return new PhpSessionCve($check, $escaper);
    }

    public function testDisplayedOnlyWhenRuntimeExposed()
    {
        $this->assertTrue($this->createMessage(true)->isDisplayed());
        $this->assertFalse($this->createMessage(false)->isDisplayed());
    }

    public function testIsCriticalWithStableIdentity()
    {
        $message = $this->createMessage(true);
        $this->assertInstanceOf(MessageInterface::class, $message);
        $this->assertSame(MessageInterface::SEVERITY_CRITICAL, $message->getSeverity());
        $this->assertSame(PhpSessionCve::IDENTITY, $message->getIdentity());
    }

    public function testTextIncludesVersionCveLinkAndMitigation()
    {
        $text = $this->createMessage(true, '8.4.27')->getText();
        $this->assertStringContainsString('PHP 8.4.27', $text);
        $this->assertStringContainsString('href="' . PhpSessionCveCheck::CVE_URL . '"', $text);
        $this->assertStringContainsString(PhpSessionCveCheck::CVE_ID, $text);
        $this->assertStringContainsString('session.upload_progress.enabled = 0', $text);
    }

    public function testRuntimeVersionIsEscaped()
    {
        $text = $this->createMessage(true, '8.4.27<script>')->getText();
        $this->assertStringNotContainsString('<script>', $text);
    }
}
