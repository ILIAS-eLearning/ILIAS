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

namespace ILIAS\Tests\FileDelivery\Delivery;

use ILIAS\FileDelivery\Delivery\ContentDispositionHeader;
use PHPUnit\Framework\TestCase;

class ContentDispositionHeaderTest extends TestCase
{
    public function testAsciiFallbackContainsNoPercent(): void
    {
        $header = (new ContentDispositionHeader())->build('attachment', 'a%2Eb');

        preg_match('/; filename="([^"]*)"/', $header, $m);
        $this->assertStringNotContainsString('%', $m[1]);
        $this->assertStringContainsString("filename*=UTF-8''a%252Eb", $header);
    }

    public function testKeepsAPlainName(): void
    {
        $header = (new ContentDispositionHeader())->build('attachment', 'report.pdf');

        $this->assertStringContainsString('filename="report.pdf"', $header);
        $this->assertStringContainsString("filename*=UTF-8''report.pdf", $header);
    }

    public function testNonAsciiNameGetsAnAsciiFallbackAndAnExtendedParameter(): void
    {
        $header = (new ContentDispositionHeader())->build('inline', 'Prüfung.pdf');

        $this->assertStringStartsWith('inline; ', $header);
        preg_match('/; filename="([^"]*)"/', $header, $m);
        $this->assertSame(1, preg_match('/^[\x20-\x7e]+$/', $m[1]));
        $this->assertStringContainsString("filename*=UTF-8''Pr%C3%BCfung.pdf", $header);
    }

    public function injectionProvider(): array
    {
        return [
            'crlf' => ["a\r\nSet-Cookie: x=1.pdf"],
            'quote' => ['a".pdf'],
            'slash' => ['../../etc/passwd'],
            'backslash' => ['a\\b.pdf'],
            'nul' => ["a\0.pdf"],
        ];
    }

    /**
     * @dataProvider injectionProvider
     */
    public function testHeaderCannotBeBrokenOutOf(string $filename): void
    {
        $header = (new ContentDispositionHeader())->build('attachment', $filename);

        foreach (["\r", "\n", "\0"] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $header);
        }
        $this->assertSame(1, preg_match('/; filename="[^"]*"/', $header));
        $this->assertStringNotContainsString('/', explode('filename*', $header)[0]);
    }

    public function testEmptyNameFallsBackToAPlaceholder(): void
    {
        $header = (new ContentDispositionHeader())->build('attachment', '');

        $this->assertStringContainsString('filename="file"', $header);
    }
}
