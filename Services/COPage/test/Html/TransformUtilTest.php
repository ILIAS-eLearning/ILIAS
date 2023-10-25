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

namespace ILIAS\COPage\Test\Html;

use PHPUnit\Framework\TestCase;
use ILIAS\COPage\Html\TransformUtil;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class TransformUtilTest extends TestCase
{
    protected TransformUtil $util;

    protected function setUp(): void
    {
        parent::setUp();
        $this->util = new TransformUtil();
    }

    public function testGetPosOfPlaceholder(): void
    {
        $html = "...{{{{{TestTag;1}}}}}...";
        $pos = $this->util->getPosOfPlaceholder($html, "TestTag");
        $this->assertEquals(
            3,
            $pos
        );

        $pos = $this->util->getPosOfPlaceholder($html, "TestMissing");
        $this->assertEquals(
            null,
            $pos
        );
    }

    public function testGetEndPosOfPlaceholder(): void
    {
        $html = "...{{{{{TestTag;1}}}}}...";
        $pos = $this->util->getEndPosOfPlaceholder($html);
        $this->assertEquals(
            22,
            $pos
        );
    }

    public function testGetPlaceholderParamString(): void
    {
        $html = "...{{{{{TestTag;1;ab;cd}}}}}...";
        $ph_string = $this->util->getPlaceholderParamString($html, "TestTag");
        $this->assertEquals(
            "TestTag;1;ab;cd",
            $ph_string
        );
    }

    public function testGetPlaceholderParams(): void
    {
        $html = "...{{{{{TestTag;1;ab;cd}}}}}...";
        $ph_string = $this->util->getPlaceholderParams($html, "TestTag");
        $this->assertEquals(
            ["TestTag", "1", "ab", "cd"],
            $ph_string
        );
    }

    public function testGetInnerContentOfPlaceholders(): void
    {
        $html = "...{{{{{TestStart;1;ab;cd}}}}}The inner content.{{{{{TestEnd;1;ab;cd}}}}}";
        $ph_string = $this->util->getInnerContentOfPlaceholders($html, "TestStart", "TestEnd");
        $this->assertEquals(
            "The inner content.",
            $ph_string
        );
    }

    public function testReplaceInnerContentAndPlaceholders(): void
    {
        $html = "...{{{{{TestStart;1;ab;cd}}}}}The inner content.{{{{{TestEnd;1;ab;cd}}}}} abc";
        $ph_string = $this->util->replaceInnerContentAndPlaceholders($html, "TestStart", "TestEnd", "The new content.");
        $this->assertEquals(
            "...The new content. abc",
            $ph_string
        );
    }

}
