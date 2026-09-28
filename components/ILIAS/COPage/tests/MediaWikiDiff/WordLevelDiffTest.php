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

namespace ILIAS\COPage\Test\MediaWikiDiff;

use PHPUnit\Framework\TestCase;

class WordLevelDiffTest extends TestCase
{
    public function testSplitEmptyInput(): void
    {
        $diff = new \WordLevelDiff([], []);

        $this->assertSame([[], []], $diff->_split([]));
        $this->assertSame(['&#160;'], $diff->orig());
        $this->assertSame(['&#160;'], $diff->closing());
    }

    public function testSplitPreservesWhitespaceAndSeparatesPunctuation(): void
    {
        $diff = new \WordLevelDiff([], []);

        $this->assertSame(
            [
                ['Hello', ', ', ' ', 'world', '!'],
                ['Hello', ',', ' ', 'world', '!']
            ],
            $diff->_split(['Hello,  world!'])
        );
    }

    public function testSplitAddsNewlineTokensBetweenLinesIncludingEmptyLines(): void
    {
        $diff = new \WordLevelDiff([], []);

        $this->assertSame(
            [
                ['first', "\n", "\n", 'third'],
                ['first', "\n", "\n", 'third']
            ],
            $diff->_split(['first', '', 'third'])
        );
    }

    public function testSplitTreatsLinesAtAndAboveMaximumLengthAsSingleWord(): void
    {
        $diff = new \WordLevelDiff([], []);
        $at_limit = str_repeat('x', \WordLevelDiff::MAX_LINE_LENGTH);
        $over_limit = $at_limit . 'x';

        $this->assertSame([[$at_limit], [$at_limit]], $diff->_split([$at_limit]));
        $this->assertSame([[$over_limit], [$over_limit]], $diff->_split([$over_limit]));
    }

    public function testUnchangedLinesAreReconstructedAndHtmlEscaped(): void
    {
        $diff = new \WordLevelDiff(['<tag> &'], ['<tag> &']);

        $this->assertSame(['&lt;tag&gt; &amp;'], $diff->orig());
        $this->assertSame(['&lt;tag&gt; &amp;'], $diff->closing());
    }

    public function testUnchangedMultipleLinesPreserveEmptyLines(): void
    {
        $lines = ['first line', '', 'third line'];
        $diff = new \WordLevelDiff($lines, $lines);

        $this->assertSame(['first line', '&#160;', 'third line'], $diff->orig());
        $this->assertSame(['first line', '&#160;', 'third line'], $diff->closing());
    }

    public function testInsertionIsHighlightedOnlyInClosingLines(): void
    {
        $diff = new \WordLevelDiff(['Hello world'], ['Hello there world']);

        $this->assertSame(['Hello world'], $diff->orig());
        $this->assertSame(
            ['Hello [ilDiffInsStart]there [ilDiffInsEnd]world'],
            $diff->closing()
        );
    }

    public function testDeletionIsHighlightedOnlyInOriginalLines(): void
    {
        $diff = new \WordLevelDiff(['Hello there world'], ['Hello world']);

        $this->assertSame(
            ['Hello [ilDiffDelStart]there [ilDiffDelEnd]world'],
            $diff->orig()
        );
        $this->assertSame(['Hello world'], $diff->closing());
    }

    public function testChangedTextIsHighlightedOnBothSides(): void
    {
        $diff = new \WordLevelDiff(['A red car'], ['A blue car']);

        $this->assertSame(
            ['A [ilDiffDelStart]red [ilDiffDelEnd]car'],
            $diff->orig()
        );
        $this->assertSame(
            ['A [ilDiffInsStart]blue [ilDiffInsEnd]car'],
            $diff->closing()
        );
    }

    public function testEmptyLinesAreRenderedWhenBothInputsAreEmpty(): void
    {
        $diff = new \WordLevelDiff([''], ['']);

        $this->assertSame(['&#160;'], $diff->orig());
        $this->assertSame(['&#160;'], $diff->closing());
    }
}
