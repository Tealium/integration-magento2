<?php
namespace Tealium\Tags\Model\Runtime;

/**
 * Detects PHP runtimes affected by CVE-2026-115560 (session serializer
 * mismatch, possible remote code execution).
 *
 * This is a PHP runtime vulnerability, not a vulnerability in this module.
 * The module does not change session.serialize_handler. Composer's "php"
 * constraint is only checked against the PHP that runs composer, and
 * platform_check.php enforces only the lower bound. So we also check the
 * actual runtime and warn admins.
 *
 * Kept free of Magento dependencies so it can be unit tested on its own.
 *
 * @see https://www.cve.org/CVERecord?id=CVE-2026-115560
 */
class PhpSessionCveCheck
{
    const CVE_ID = 'CVE-2026-115560';
    const CVE_URL = 'https://www.cve.org/CVERecord?id=CVE-2026-115560';

    /**
     * First fixed release per supported PHP 8.x minor branch.
     * Every branch older than 8.2 is affected and will not receive a fix.
     */
    const FIXED_RELEASES = [
        2 => 36,
        3 => 37,
        4 => 28,
        5 => 13,
    ];

    /**
     * Unparseable versions are treated as not affected so we don't show false alarms.
     */
    public function isAffectedVersion(string $version): bool
    {
        if (!preg_match('/^(\d+)\.(\d+)\.(\d+)/', $version, $m)) {
            return false;
        }

        $major = (int) $m[1];
        $minor = (int) $m[2];
        $release = (int) $m[3];

        if ($major < 8 || ($major === 8 && $minor < 2)) {
            return true;
        }

        if ($major === 8 && isset(self::FIXED_RELEASES[$minor])) {
            return $release < self::FIXED_RELEASES[$minor];
        }

        return false;
    }

    public function isUploadProgressEnabled(): bool
    {
        return (bool) filter_var(ini_get('session.upload_progress.enabled'), FILTER_VALIDATE_BOOLEAN);
    }

    public function getRuntimeVersion(): string
    {
        return PHP_VERSION;
    }

    /**
     * True when the running PHP is affected AND the upload-progress vector is enabled.
     */
    public function isRuntimeExposed(): bool
    {
        return $this->isAffectedVersion($this->getRuntimeVersion()) && $this->isUploadProgressEnabled();
    }
}
