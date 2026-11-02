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

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ilICalWriterTest extends TestCase
{
    public static function textValues(): array
    {
        return [
            'empty text' => ['', ''],
            'plain text and colon' => ['Agenda: review', 'Agenda: review'],
            'LF' => ["First\nSecond", 'First\\nSecond'],
            'CRLF' => ["First\r\nSecond", 'First\\nSecond'],
            'CR' => ["First\rSecond", 'First\\nSecond'],
            'mixed and blank lines' => ["First\r\n\nSecond\rThird\n", 'First\\n\\nSecond\\nThird\\n'],
            'literal backslash-n' => ['First\\nSecond', 'First\\\\nSecond'],
            'text delimiters' => ['Path\\file; next, item', 'Path\\\\file\\; next\\, item'],
            'backslash before newline' => ["First\\\nSecond", 'First\\\\\\nSecond'],
        ];
    }

    #[DataProvider('textValues')]
    public function testEscapeText(string $text, string $expected): void
    {
        $this->assertSame($expected, ilICalWriter::escapeText($text));
    }

    public function testMultilineDescriptionRemainsOnePropertyAfterFolding(): void
    {
        $first_line = str_repeat('Meeting agenda ', 10);
        $writer = new ilICalWriter();
        $writer->addLine('DESCRIPTION:' . ilICalWriter::escapeText($first_line . "\nSecond item\r\nThird item\rFourth item"));
        $writer->addLine('LOCATION:Room A');

        $this->assertSame(
            'DESCRIPTION:' . $first_line . '\\nSecond item\\nThird item\\nFourth item' . "\r\nLOCATION:Room A\r\n",
            str_replace("\r\n ", '', (string) $writer)
        );
    }
}
