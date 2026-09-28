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

namespace ILIAS\COPage\Test\WordDiff;

use ILIAS\COPage\WordDiff\WordDiff;
use PHPUnit\Framework\TestCase;

class WordDiffTest extends TestCase
{
    public function testUnchangedTextIsEscaped(): void
    {
        $diff = new WordDiff(['<tag> &'], ['<tag> &']);

        $this->assertSame(['&lt;tag&gt; &amp;'], $diff->orig());
        $this->assertSame(['&lt;tag&gt; &amp;'], $diff->closing());
    }

    public function testInsertionAndDeletionAreMarkedOnTheirOwnSides(): void
    {
        $insertion = new WordDiff(['Hello world'], ['Hello there world']);
        $deletion = new WordDiff(['Hello there world'], ['Hello world']);

        $this->assertSame(['Hello world'], $insertion->orig());
        $this->assertSame(['Hello [ilDiffInsStart]there [ilDiffInsEnd]world'], $insertion->closing());
        $this->assertSame(['Hello [ilDiffDelStart]there [ilDiffDelEnd]world'], $deletion->orig());
        $this->assertSame(['Hello world'], $deletion->closing());
    }

    public function testChangedTextIsMarkedOnBothSides(): void
    {
        $diff = new WordDiff(['A red car'], ['A blue car']);

        $this->assertSame(['A [ilDiffDelStart]red [ilDiffDelEnd]car'], $diff->orig());
        $this->assertSame(['A [ilDiffInsStart]blue [ilDiffInsEnd]car'], $diff->closing());
    }

    public function testEmptyAndMultilineInputsRemainVisible(): void
    {
        $empty = new WordDiff([], []);
        $lines = new WordDiff(['first line', '', 'third line'], ['first line', '', 'third line']);

        $this->assertSame(['&#160;'], $empty->orig());
        $this->assertSame(['&#160;'], $empty->closing());
        $this->assertSame(['first line', '&#160;', 'third line'], $lines->orig());
        $this->assertSame(['first line', '&#160;', 'third line'], $lines->closing());
    }

    public function testLongLineIsTreatedAsOneWord(): void
    {
        $original = str_repeat('a', 10001);
        $closing = str_repeat('b', 10001);
        $diff = new WordDiff([$original], [$closing]);

        $this->assertSame(['[ilDiffDelStart]' . $original . '[ilDiffDelEnd]'], $diff->orig());
        $this->assertSame(['[ilDiffInsStart]' . $closing . '[ilDiffInsEnd]'], $diff->closing());
    }
}
