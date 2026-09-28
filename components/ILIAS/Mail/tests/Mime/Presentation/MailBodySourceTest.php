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

use ILIAS\Mail\Mime\Presentation\MailBodySource;
use PHPUnit\Framework\TestCase;

final class MailBodySourceTest extends TestCase
{
    private const string BODY = 'Hello <b>Mail</b>';

    public function testRawReturnsTheUnchangedBody(): void
    {
        $source = new MailBodySource(self::BODY);

        $this->assertSame(self::BODY, $source->raw());
    }

    public function testTransformedToHtmlKeepsANonEmptyBodyWhenNoClosureIsGiven(): void
    {
        $source = new MailBodySource(self::BODY);

        $this->assertSame(self::BODY, $source->transformedToHtml());
    }

    public function testTransformedToHtmlReplacesAnEmptyBodyWithASpace(): void
    {
        $source = new MailBodySource('');

        $this->assertSame(' ', $source->transformedToHtml());
        $this->assertSame('', $source->raw());
    }

    public function testTransformedToHtmlAppliesTheOptionalClosure(): void
    {
        $source = new MailBodySource(
            self::BODY,
            static fn(string $body): string => "wrapped:$body"
        );

        $this->assertSame('wrapped:' . self::BODY, $source->transformedToHtml());
        $this->assertSame(self::BODY, $source->raw());
    }
}
