<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with
 * the source code, too.
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
use ILIAS\Language\ComponentTranslation\DuplicateIdentifierMessage;
use ILIAS\Language\ComponentTranslation\PlainLogText;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use PHPUnit\Framework\TestCase;

class DuplicateIdentifierMessageTest extends TestCase
{
    private function lng(string $text): Language
    {
        $lng = $this->createStub(Language::class);
        $lng->method('txt')->willReturn($text);

        return $lng;
    }

    private function refinery(): Refinery
    {
        return new Refinery($this->createStub(DataFactory::class), $this->createStub(Language::class));
    }

    public function testAnEntryNamesItsKeyAndWhatItWouldDuplicate(): void
    {
        $this->assertSame(
            'tos#:#x (common: x, mme: X)',
            DuplicateIdentifierMessage::entry('tos#:#x', ['common: x', 'mme: X'])
        );
    }

    public function testTheMessageEscapesTheKeysAndFillsThePlaceholder(): void
    {
        $message = DuplicateIdentifierMessage::of(
            $this->lng('Not unique: %s'),
            $this->refinery(),
            ['a#:#<b> (x: "<b>")', 'c#:#d (e: d)']
        );

        $this->assertSame('Not unique: a#:#&lt;b&gt; (x: &quot;&lt;b&gt;&quot;); c#:#d (e: d)', $message);
    }

    /** A translation without the placeholder, or with another "%", still names the entries and does not fail. */
    public function testATextWithoutPlaceholderGetsTheListAppendedAndAFurtherPercentSignIsKept(): void
    {
        $this->assertSame(
            'Not unique 100% right. a (b: a)',
            DuplicateIdentifierMessage::of($this->lng('Not unique 100% right.'), $this->refinery(), ['a (b: a)'])
        );
        $this->assertSame(
            '100% unique: a (b: a)',
            DuplicateIdentifierMessage::of($this->lng('100% unique: %s'), $this->refinery(), ['a (b: a)'])
        );
    }

    /** The keys come from an uploaded file: one entry is cut to 200 characters, the list to the first entries of PlainLogText. */
    public function testEntriesAreShortenedAndTheListIsLimited(): void
    {
        $long = str_repeat('k', 300);
        $entries = [$long];
        for ($i = 1; $i <= PlainLogText::MAX_LISTED_KEYS + 4; $i++) {
            $entries[] = 'e' . $i;
        }

        $message = DuplicateIdentifierMessage::of($this->lng('%s'), $this->refinery(), $entries);

        $this->assertStringStartsWith(str_repeat('k', 199) . '…; e1; ', $message);
        $this->assertStringNotContainsString(str_repeat('k', 200), $message);
        $this->assertStringContainsString('… (+5)', $message);
        $this->assertStringNotContainsString('e' . (PlainLogText::MAX_LISTED_KEYS + 4), $message);
    }

    public function testTheLogLineHoldsAtMostTwoHundredShortenedEntriesAndCountsTheRest(): void
    {
        $entries = [str_repeat('k', 300)];
        for ($i = 1; $i < 250; $i++) {
            $entries[] = 'e' . $i;
        }

        $line = DuplicateIdentifierMessage::forLog($entries);

        $this->assertStringContainsString(str_repeat('k', 199) . '…; e1; ', $line);
        $this->assertStringNotContainsString(str_repeat('k', 200), $line);
        $this->assertStringContainsString('e199', $line);
        $this->assertStringNotContainsString('e200;', $line);
        $this->assertStringEndsWith(' … (+50)', $line);
    }

    public function testTheLogLineWithFewEntriesHasNoRemainderAndNoControlCharacters(): void
    {
        $line = DuplicateIdentifierMessage::forLog(["a#:#b\x1b[31m (c: b)"]);

        $this->assertStringContainsString('a#:#b?[31m (c: b)', $line);
        $this->assertStringNotContainsString('(+', $line);
        $this->assertStringNotContainsString("\x1b", $line);
    }
}
