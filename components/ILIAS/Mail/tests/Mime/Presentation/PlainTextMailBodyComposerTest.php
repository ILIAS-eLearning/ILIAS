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
use ILIAS\Mail\Mime\Presentation\PlainTextMailBodyComposer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PlainTextMailBodyComposerTest extends TestCase
{
    private PlainTextMailBodyComposer $composer;

    protected function setUp(): void
    {
        $this->composer = new PlainTextMailBodyComposer();
    }

    /**
     * @return Generator<string, array{0: string, 1: string}>
     */
    public static function composeTurnsMarkupIntoPlainTextProvider(): Generator
    {
        $first_line = 'Hello';
        $second_line = 'Mail';
        $expected_lines = "$first_line\n$second_line";

        yield from [
            '<br> becomes a newline' => [
                "$first_line<br>$second_line",
                $expected_lines,
            ],
            '<br/> becomes a newline' => [
                "$first_line<br/>$second_line",
                $expected_lines,
            ],
            '<br /> becomes a newline' => [
                "$first_line<br />$second_line",
                $expected_lines,
            ],
            'uppercase <BR> becomes a newline' => [
                "$first_line<BR>$second_line",
                $expected_lines,
            ],
            'uppercase <BR/> becomes a newline' => [
                "$first_line<BR/>$second_line",
                $expected_lines,
            ],
            'uppercase <BR /> becomes a newline' => [
                "$first_line<BR />$second_line",
                $expected_lines,
            ],
            'mixed-case <Br /> becomes a newline' => [
                "$first_line<Br />$second_line",
                $expected_lines,
            ],
            'markup tags are stripped' => [
                'Hello <b>Mail</b> and <em>more</em>',
                'Hello Mail and more',
            ],
            'html entities are decoded' => [
                'Mail &amp; Forum &quot;quoted&quot; &#039;ticked&#039;',
                'Mail & Forum "quoted" \'ticked\'',
            ],
            'breaks, tags and entities are combined' => [
                'Hello <b>Mail</b><br />Mail &amp; Forum',
                "Hello Mail\nMail & Forum",
            ],
        ];
    }

    #[DataProvider('composeTurnsMarkupIntoPlainTextProvider')]
    public function testComposeTurnsMarkupIntoPlainText(string $body, string $expected_plain_text): void
    {
        $composed = $this->composer->compose(new MailBodySource($body));

        $this->assertSame($expected_plain_text, $composed->body());
    }

    public function testComposeHasNeitherAlternativeBodyNorImages(): void
    {
        $composed = $this->composer->compose(new MailBodySource('Hello <b>Mail</b>'));

        $this->assertNull($composed->alternativeBody());
        $this->assertTrue($composed->images()->isEmpty());
    }
}
