<?php
namespace Tealium\Tags\Model\System\Message;

use Magento\Framework\Escaper;
use Magento\Framework\Notification\MessageInterface;
use Tealium\Tags\Model\Runtime\PhpSessionCveCheck;

/**
 * Admin system message shown when the PHP runtime is exposed to CVE-2026-115560.
 */
class PhpSessionCve implements MessageInterface
{
    const IDENTITY = 'tealium_tags_php_session_cve_2026_115560';

    /** @var PhpSessionCveCheck */
    private $check;

    /** @var Escaper */
    private $escaper;

    public function __construct(PhpSessionCveCheck $check, Escaper $escaper)
    {
        $this->check = $check;
        $this->escaper = $escaper;
    }

    public function getIdentity()
    {
        return self::IDENTITY;
    }

    public function isDisplayed()
    {
        return $this->check->isRuntimeExposed();
    }

    public function getText()
    {
        $link = sprintf(
            '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
            $this->escaper->escapeUrl(PhpSessionCveCheck::CVE_URL),
            $this->escaper->escapeHtml(PhpSessionCveCheck::CVE_ID)
        );

        return (string) __(
            'Tealium: this server runs PHP %1, which is affected by %2 (PHP session serializer mismatch, possible remote code execution). '
            . 'This is a PHP vulnerability, not a Tealium extension vulnerability. Upgrade PHP to 8.4.28, 8.5.13 or later. '
            . 'Until then, set session.upload_progress.enabled = 0 in php.ini / the PHP-FPM pool, and make sure every application '
            . 'sharing your session storage (Redis, Memcached, files) uses the same session.serialize_handler.',
            $this->escaper->escapeHtml($this->check->getRuntimeVersion()),
            $link
        );
    }

    public function getSeverity()
    {
        return self::SEVERITY_CRITICAL;
    }
}
