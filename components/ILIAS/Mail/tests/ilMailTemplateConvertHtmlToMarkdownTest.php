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

namespace ILIAS\Mail\tests;

use PHPUnit\Framework\TestCase;
use ilMailTemplateConvertHtmlToMarkdown;

class ilMailTemplateConvertHtmlToMarkdownTest extends TestCase
{
    private ilMailTemplateConvertHtmlToMarkdown $markdown_formatting_to_html;
    protected function setUp(): void
    {
        $this->markdown_formatting_to_html = new \ilMailTemplateConvertHtmlToMarkdown();
    }

    public static function provideTags(): array
    {
        return [
            ['<b>Ilias</b>', "**Ilias**"],
            ['<strong>Ilias</strong>', "**Ilias**"],
            ['<i>Ilias</i>', "_Ilias_"],
            ['<em>Ilias</em>', "_Ilias_"],
            ['<u>Ilias</u>', "--Ilias--"],
            ['<h1>Headline 01</h1>', "\n# Headline 01\n"],
            ['<h2>Headline 02</h2>', "\n## Headline 02\n"],
            ['<h3>Headline 03</h3>', "\n### Headline 03\n"],
            ['<h4>Headline 04</h4>', "\n#### Headline 04\n"],
            ['<h5>Headline 05</h5>', "\n##### Headline 05\n"],
            ['<h6>Headline 06</h6>', "\n###### Headline 06\n"],
            ['<ul><li>Ilias</li></ul>', "\n- Ilias\n\n"],
            ['<ol><li>Ilias</li></ol>', "\n1. Ilias\n\n"],
            ['<a href="https://ilias.de">Ilias</a>', "[Ilias](https://ilias.de)"],
            ['<img src="https://ilias.de" alt="Ilias">', "![Ilias](https://ilias.de)"],
            ['<br>', "\n"],
            ['<br />', "\n"],
            ['<br/>', "\n"],
            ['<p>Ilias Text</p>', "\nIlias Text\n"]
        ];
    }

    /**
     * @test
     * @dataProvider provideTags
     */
    public function testConvert(
        string $html_string,
        string $expected_markdown
    ): void {
        $this->assertEquals($expected_markdown, $this->markdown_formatting_to_html->convert($html_string));
    }
}
