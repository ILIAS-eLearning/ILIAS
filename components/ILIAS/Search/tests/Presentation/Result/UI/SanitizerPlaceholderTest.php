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

namespace ILIAS\Search\Presentation\Result\UI;

use PHPUnit\Framework\TestCase;
use ILIAS\Refinery\Factory as Refinery;

class SanitizerPlaceholderTest extends TestCase
{
    protected const string HIGHLIGHT_START = '<span class="ilSearchHighlight">';
    protected const string HIGHLIGHT_END = '</span>';
    protected const string PLACEHOLDER_START = '[PLACEHOLDER_START]';
    protected const string PLACEHOLDER_END = '[PLACEHOLDER_END]';

    public function getSanitizerWithoutSanitization(): SanitizerImpl
    {
        return new class () extends SanitizerImpl {
            public function __construct()
            {
            }

            public function sanitize(string $text): string
            {
                return $text;
            }
        };
    }

    public static function replacementMapProvider(
        string $search_start,
        string $search_end,
        string $replace_start,
        string $replace_end
    ): array {
        return [
            ['', ''],
            ['some text', 'some text'],
            [
                $search_start . 'some text' . $search_end,
                $replace_start . 'some text' . $replace_end
            ],
            [
                'text' . $search_start . 'some text' . $search_end
                . 'some more text' . $search_start . 'final text' . $search_end,
                'text' . $replace_start . 'some text' . $replace_end .
                'some more text' . $replace_start . 'final text' . $replace_end
            ],
            ['text' . $search_start . 'some text', 'text' . $search_start . 'some text'],
            ['text' . $search_end . 'some text', 'text' . $search_end . 'some text'],
            [
                $search_start . 'some text' . $search_end . 'text' . $search_end,
                $replace_start . 'some text' . $replace_end . 'text' . $search_end
            ],
            [
                $search_start . 'text' . $search_start . 'some text' . $search_end,
                $replace_start . 'text' . $search_start . 'some text' . $replace_end
            ],
            [
                $search_start . 'text' . $search_start . 'some text' . $search_end,
                $replace_start . 'text' . $search_start . 'some text' . $replace_end
            ],
            [
                $search_start . 'text' . $search_start . 'some text' . $search_end . 'more text' . $search_end,
                $replace_start . 'text' . $search_start . 'some text' . $replace_end . 'more text' . $search_end
            ]
        ];
    }

    public static function textWithInitialPlaceholdersProvider(): array
    {
        return [
            ['text' . self::PLACEHOLDER_START . 'more', 'textmore'],
            ['text' . self::PLACEHOLDER_END . 'more', 'textmore'],
            [
                'text' . self::PLACEHOLDER_START . self::HIGHLIGHT_START . 'more' . self::HIGHLIGHT_END,
                'text' . self::PLACEHOLDER_START . 'more' . self::PLACEHOLDER_END
            ],
        ];
    }

    public static function setUpPlaceholdersProvider(): array
    {
        return array_merge(
            self::replacementMapProvider(
                self::HIGHLIGHT_START,
                self::HIGHLIGHT_END,
                self::PLACEHOLDER_START,
                self::PLACEHOLDER_END
            ),
            self::textWithInitialPlaceholdersProvider()
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('setUpPlaceholdersProvider')]
    public function testSetUpPlaceholders(
        string $input,
        string $expected_output
    ): void {
        $sanitizer = $this->getSanitizerWithoutSanitization();
        $output = $sanitizer->sanitizeAndSetUpPlaceholders($input);
        $this->assertSame($expected_output, $output);
    }

    public static function replacePlaceholdersProvider(): array
    {
        return self::replacementMapProvider(
            self::PLACEHOLDER_START,
            self::PLACEHOLDER_END,
            self::HIGHLIGHT_START,
            self::HIGHLIGHT_END
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('replacePlaceholdersProvider')]
    public function testReplacePlaceholders(
        string $input,
        string $expected_output
    ): void {
        $sanitizer = $this->getSanitizerWithoutSanitization();
        $output = $sanitizer->replacePlaceholders($input);
        $this->assertSame($expected_output, $output);
    }
}
