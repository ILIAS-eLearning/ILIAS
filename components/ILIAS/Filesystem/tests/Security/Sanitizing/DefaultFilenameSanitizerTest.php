<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Filesystem\Security\Sanitizing;

use ILIAS\Filesystem\Configuration\FilesystemConfig;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class DefaultFilenameSanitizerTest extends TestCase
{
    private DefaultFilenameSanitizer $sanitizer;

    protected function setUp(): void
    {
        $settings = $this->createStub(FilesystemConfig::class);
        $settings->method('getWhiteListedSuffixes')->willReturn(['pdf', 'png', 'jpg', 'txt']);
        $settings->method('getBlackListedSuffixes')->willReturn([]);
        $settings->method('isByPassAllowedForCurrentUser')->willReturn(false);

        $this->sanitizer = new DefaultFilenameSanitizer($settings);
    }

    public static function cleanProvider(): \Iterator
    {
        yield 'plain whitelisted' => ['report.pdf'];
        yield 'upper case suffix' => ['REPORT.PDF'];
        yield 'the secure suffix itself' => ['blocked.sec'];
        yield 'percent elsewhere, real suffix' => ['50%20percent.pdf'];
    }

    #[DataProvider('cleanProvider')]
    public function testCleanNamesStayClean(string $filename): void
    {
        $this->assertTrue($this->sanitizer->isClean($filename));
    }

    public static function dirtyProvider(): \Iterator
    {
        yield 'prohibited suffix' => ['evil.exe'];
        yield 'php' => ['shell.php'];
        yield 'no suffix' => ['evil%2Eexe'];
        yield 'percent-encoded suffix char' => ['evil.e%78e'];
        yield 'percent-encoded dot' => ['shell%2Ephp'];
    }

    #[DataProvider('dirtyProvider')]
    public function testDirtyNamesAreNotClean(string $filename): void
    {
        $this->assertFalse($this->sanitizer->isClean($filename));
    }

    public function testEncodedProhibitedSuffixIsRenamedToSecureSuffix(): void
    {
        $sanitized = $this->sanitizer->sanitize('evil%2Eexe');

        $this->assertStringEndsWith('.' . FilenameSanitizer::CLEAN_FILE_SUFFIX, $sanitized);
    }
}
