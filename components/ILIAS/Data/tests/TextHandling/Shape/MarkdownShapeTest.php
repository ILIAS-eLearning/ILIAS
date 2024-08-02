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

use PHPUnit\Framework\TestCase;
use Data\src\TextHandling\Shape\Markdown;
use ILIAS\Data;
use Data\src\TextHandling\Text\HTML;
use Data\src\TextHandling\Text\PlainText;

class MarkdownShapeTest extends TestCase
{
    protected Markdown $markdown_shape;

    protected function setUp(): void
    {
        $markup = $this->createMock(\Data\src\TextHandling\Markup\Markup::class);
        $language = $this->createMock(ilLanguage::class);
        $data_factory = new Data\Factory();
        $refinery = new ILIAS\Refinery\Factory($data_factory, $language);
        $this->markdown_shape = new Markdown($refinery, $markup);
    }

    public static function stringToHTMLDataProvider(): array
    {
        return [
            ["lorem", new HTML("<p>lorem</p>\n")],
            ["lorem **ipsum**", new HTML("<p>lorem <strong>ipsum</strong></p>\n")],
            ["_lorem_ **ipsum**", new HTML("<p><em>lorem</em> <strong>ipsum</strong></p>\n")],
            ["# Headline", new HTML("<h1>Headline</h1>\n")],
            ["## Headline", new HTML("<h2>Headline</h2>\n")],
            ["### Headline", new HTML("<h3>Headline</h3>\n")],
            ["1. Lorem\n2. Ipsum", new HTML("<ol>\n<li>Lorem</li>\n<li>Ipsum</li>\n</ol>\n")],
            ["- Lorem\n- Ipsum", new HTML("<ul>\n<li>Lorem</li>\n<li>Ipsum</li>\n</ul>\n")],
            ["[Link Titel](https://www.ilias.de)", new HTML("<p><a href=\"https://www.ilias.de\">Link Titel</a></p>\n")]
        ];
    }

    public static function stringToPlainDataProvider(): array
    {
        return [
            ["lorem", new PlainText("lorem")],
            ["lorem **ipsum**", new PlainText("lorem **ipsum**")],
            ["_lorem_ **ipsum**", new PlainText("_lorem_ **ipsum**")],
            ["# Headline", new PlainText("# Headline")],
            ["## Headline", new PlainText("## Headline")],
            ["### Headline", new PlainText("### Headline")],
            ["1. Lorem\n2. Ipsum", new PlainText("1. Lorem\n2. Ipsum")],
            ["- Lorem\n- Ipsum", new PlainText("- Lorem\n- Ipsum")],
            ["[Link Titel](https://www.ilias.de)", new PlainText("[Link Titel](https://www.ilias.de)")]
        ];
    }

    /**
     * @dataProvider stringToHTMLDataProvider
     */
    public function testToHTML(string $markdown_string, HTML $expected_html): void
    {
        $this->assertEquals($expected_html, $this->markdown_shape->toHTML($markdown_string));
    }

    /**
     * @dataProvider stringToPlainDataProvider
     */
    public function testToPlainText(string $markdown_string, PlainText $expected_text): void
    {
        $this->assertEquals($expected_text, $this->markdown_shape->toPlainText($markdown_string));
    }
}
