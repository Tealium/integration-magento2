<?php
namespace Tealium\Tags\Model\Runtime;

use PHPUnit\Framework\TestCase;

class PhpSessionCveCheckTest extends TestCase
{
    public static function versionProvider(): array
    {
        return [
            // EOL branches: affected, no fix exists.
            '7.4.33' => ['7.4.33', true],
            '8.0.30' => ['8.0.30', true],
            '8.1.99' => ['8.1.99', true],
            // 8.2: fixed in 8.2.36.
            '8.2.35' => ['8.2.35', true],
            '8.2.36' => ['8.2.36', false],
            // 8.3: fixed in 8.3.37.
            '8.3.36' => ['8.3.36', true],
            '8.3.37' => ['8.3.37', false],
            // 8.4: fixed in 8.4.28.
            '8.4.0' => ['8.4.0', true],
            '8.4.27' => ['8.4.27', true],
            '8.4.28' => ['8.4.28', false],
            // 8.5: fixed in 8.5.13.
            '8.5.0' => ['8.5.0', true],
            '8.5.12' => ['8.5.12', true],
            '8.5.13' => ['8.5.13', false],
            // Later branches.
            '8.6.0' => ['8.6.0', false],
            '9.0.0' => ['9.0.0', false],
            // Distro suffixes.
            '8.4.27-1ubuntu1' => ['8.4.27-1ubuntu1', true],
            '8.4.28-1+deb12u1' => ['8.4.28-1+deb12u1', false],
            // Unparseable input is not flagged.
            'empty' => ['', false],
            'two-part' => ['8.4', false],
            'garbage' => ['not-a-version', false],
        ];
    }

    /**
     * @dataProvider versionProvider
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('versionProvider')]
    public function testIsAffectedVersion(string $version, bool $expected)
    {
        $this->assertSame($expected, (new PhpSessionCveCheck())->isAffectedVersion($version));
    }

    public function testRuntimeVersionIsPhpVersion()
    {
        $this->assertSame(PHP_VERSION, (new PhpSessionCveCheck())->getRuntimeVersion());
    }

    public static function exposureProvider(): array
    {
        return [
            'affected + upload progress on' => ['8.4.27', true, true],
            'affected + upload progress off' => ['8.4.27', false, false],
            'patched + upload progress on' => ['8.4.28', true, false],
            'patched + upload progress off' => ['8.4.28', false, false],
        ];
    }

    /**
     * @dataProvider exposureProvider
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('exposureProvider')]
    public function testIsRuntimeExposed(string $version, bool $uploadProgress, bool $expected)
    {
        $check = $this->getMockBuilder(PhpSessionCveCheck::class)
            ->onlyMethods(['getRuntimeVersion', 'isUploadProgressEnabled'])
            ->getMock();
        $check->method('getRuntimeVersion')->willReturn($version);
        $check->method('isUploadProgressEnabled')->willReturn($uploadProgress);

        $this->assertSame($expected, $check->isRuntimeExposed());
    }
}
