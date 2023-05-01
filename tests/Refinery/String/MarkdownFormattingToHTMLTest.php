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

namespace ILIAS\src\Refinery\String;

use ILIAS\Data\Factory;
use ILIAS\Refinery\String\Group;
use ILIAS\Tests\Refinery\TestCase;
use ilLanguage;
use InvalidArgumentException;
use ILIAS\Refinery\String\Transformation\UTFNormalTransformation;
use ILIAS\Refinery\Transformation;

class MarkdownFormattingToHTMLTest extends TestCase
{
    private Transformation $markdown;

    protected function setUp(): void
    {
        $language = $this->getMockBuilder(ilLanguage::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $group = new Group(new Factory(), $language);

        $this->markdown = $group->markdown()->toHTML();
    }

    public function stringProvider(): array
    {
        return [
            ["lorem", "<p>lorem</p>\n"],
            ["lorem **ipsum**", "<p>lorem <strong>ipsum</strong></p>\n"],
            ["_lorem_ **ipsum**", "<p><em>lorem</em> <strong>ipsum</strong></p>\n"],
            ["# Headline", "<h1>Headline</h1>\n"],
            ["## Headline", "<h2>Headline</h2>\n"],
            ["### Headline", "<h3>Headline</h3>\n"],
            ["1. Lorem\n2. Ipsum", "<ol>\n<li>Lorem</li>\n<li>Ipsum</li>\n</ol>\n"],
            ["- Lorem\n- Ipsum", "<ul>\n<li>Lorem</li>\n<li>Ipsum</li>\n</ul>\n"],
            ["[Link Titel](https://www.ilias.de)", "<p><a href=\"https://www.ilias.de\">Link Titel</a></p>\n"],
        ];
    }

    /**
     * @dataProvider stringProvider
     */
    public function testTransformationToHTML(
        string $markdown_string,
        string $expected_html,
    ): void {
        $this->assertEquals($expected_html, $this->markdown->transform($markdown_string));
    }
}
