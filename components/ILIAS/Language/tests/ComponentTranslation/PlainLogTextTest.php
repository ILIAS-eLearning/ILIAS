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

namespace ILIAS\Language\ComponentTranslation;

use PHPUnit\Framework\TestCase;

/**
 * PlainLogText: control-character stripping and key-list truncation for log/terminal-bound text taken
 * from language files or entries (keys, values, file names).
 */
class PlainLogTextTest extends TestCase
{
    public function testOfLeavesOrdinaryTextUntouched(): void
    {
        $this->assertSame('Hallo Welt, äöü', PlainLogText::of('Hallo Welt, äöü'));
    }

    public function testOfReplacesAnAnsiEscapeSequence(): void
    {
        $result = PlainLogText::of("bad\x1bkey");

        $this->assertStringNotContainsString("\x1b", $result);
        $this->assertSame('bad?key', $result);
    }

    public function testOfReplacesABidirectionalOverrideCharacter(): void
    {
        // U+202E RIGHT-TO-LEFT OVERRIDE - a format character (\p{Cf}), not a C0 control
        $result = PlainLogText::of("bad\u{202E}key");

        $this->assertStringNotContainsString("\u{202E}", $result);
        $this->assertSame('bad?key', $result);
    }

    public function testOfReplacesEveryOccurrenceNotJustTheFirst(): void
    {
        $this->assertSame('a?b?c', PlainLogText::of("a\x01b\x02c"));
    }

    public function testKeyListJoinsUpToTheLimitWithoutTruncationMarker(): void
    {
        $keys = array_map(static fn(int $i): string => 'key' . $i, range(1, PlainLogText::MAX_LISTED_KEYS));

        $result = PlainLogText::keyList($keys);

        $this->assertSame(implode(', ', $keys), $result);
        $this->assertStringNotContainsString('…', $result);
    }

    /** One key over the limit: the boundary where truncation starts. */
    public function testKeyListTruncatesAtOneOverTheLimit(): void
    {
        $keys = array_map(static fn(int $i): string => 'key' . $i, range(1, PlainLogText::MAX_LISTED_KEYS + 1));
        $expected_listed = implode(', ', array_slice($keys, 0, PlainLogText::MAX_LISTED_KEYS));

        $result = PlainLogText::keyList($keys);

        $this->assertSame($expected_listed . ' … (+1)', $result);
    }

    public function testKeyListReportsTheExactCountLeftOut(): void
    {
        $keys = array_map(static fn(int $i): string => 'key' . $i, range(1, PlainLogText::MAX_LISTED_KEYS + 5));

        $this->assertStringContainsString('… (+5)', PlainLogText::keyList($keys));
    }

    public function testKeyListWithNoKeysIsAnEmptyString(): void
    {
        $this->assertSame('', PlainLogText::keyList([]));
    }
}
