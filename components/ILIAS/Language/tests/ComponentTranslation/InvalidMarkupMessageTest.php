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

namespace ILIAS\Language\Tests\ComponentTranslation;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Language\ComponentTranslation\InvalidMarkupMessage;
use ILIAS\Language\ComponentTranslation\PlainLogText;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class InvalidMarkupMessageTest extends TestCase
{
    private function lng(string $text): Language&Stub
    {
        $lng = $this->createStub(Language::class);
        $lng->method('txt')->willReturn($text);

        return $lng;
    }

    private function refinery(): Refinery
    {
        return new Refinery($this->createStub(DataFactory::class), $this->createStub(Language::class));
    }

    public function testListsTheEntriesWithTheirMarkupEscapedAndSeparatedBySemicolons(): void
    {
        $message = InvalidMarkupMessage::of($this->lng('Not allowed: %s'), $this->refinery(), [
            'tos#:#a' => ['tag <script>', 'end tag </form> of a tag that is not allowed', 'tag <script>'],
            'x<b>#:#y' => [],
        ]);

        $this->assertSame('Not allowed: tos#:#a: &lt;script&gt;, &lt;/form&gt;; x&lt;b&gt;#:#y', $message);
    }

    public function testNamesAtMostTheFirstEntriesAndCountsTheRest(): void
    {
        $entries = [];
        for ($i = 0; $i < PlainLogText::MAX_LISTED_KEYS + 3; $i++) {
            $entries['m#:#k' . $i] = [];
        }

        $message = InvalidMarkupMessage::of($this->lng('%s'), $this->refinery(), $entries);

        $this->assertStringContainsString('m#:#k' . (PlainLogText::MAX_LISTED_KEYS - 1), $message);
        $this->assertStringNotContainsString('m#:#k' . PlainLogText::MAX_LISTED_KEYS . ';', $message);
        $this->assertStringContainsString('(+3)', $message);
    }

    /**
     * A text without the placeholder (or the key of a missing text) still names the entries.
     */
    public function testAppendsTheListWhenTheTextHasNoPlaceholder(): void
    {
        $message = InvalidMarkupMessage::of($this->lng('lng_markup_not_allowed'), $this->refinery(), ['m#:#k' => ['tag <blink>']]);

        $this->assertSame('lng_markup_not_allowed m#:#k: &lt;blink&gt;', $message);
    }
}
