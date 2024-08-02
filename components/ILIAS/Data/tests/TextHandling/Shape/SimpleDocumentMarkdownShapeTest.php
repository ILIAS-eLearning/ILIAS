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
use Data\src\TextHandling\Shape\SimpleDocumentMarkdown;
use Data\src\TextHandling\Structure;
use ILIAS\Data;
use ILIAS\Refinery\Factory as Refinery;
use PHPUnit\Framework\MockObject\MockObject;

class SimpleDocumentMarkdownShapeTest extends TestCase
{
    protected function setUp(): void
    {
        $markup = $this->createMock(\Data\src\TextHandling\Markup\Markup::class);
        $language = $this->createMock(ilLanguage::class);
        $data_factory = new Data\Factory();
        $refinery = new ILIAS\Refinery\Factory($data_factory, $language);
        $this->simple_doc_markdown_shape = new SimpleDocumentMarkdown($refinery, $markup);
    }

    public static function constructDataProvider(): array
    {
        return [
            [
                Structure::BOLD,
                Structure::ITALIC,
                Structure::HEADING_1,
                Structure::HEADING_2,
                Structure::HEADING_3,
                Structure::HEADING_4,
                Structure::HEADING_5,
                Structure::HEADING_6,
                Structure::UNORDERED_LIST,
                Structure::ORDERED_LIST,
                Structure::PARAGRAPH,
                Structure::LINK,
                Structure::BLOCKQUOTE,
                Structure::CODE
            ]
        ];
    }

    public static function stringComplianceDataProvider(): array
    {
        return [
            ["### Heading 3", true],
            ["> Quote block", true],
            ["![Image text](https://www.ilias.de)", false]
        ];
    }

    /**
     * @dataProvider constructDataProvider
     */
    public function testGetSupportedStructure(
        Structure $dp_bold,
        Structure $dp_italic,
        Structure $dp_heading_1,
        Structure $dp_heading_2,
        Structure $dp_heading_3,
        Structure $dp_heading_4,
        Structure $dp_heading_5,
        Structure $dp_heading_6,
        Structure $dp_unordered_list,
        Structure $dp_ordered_list,
        Structure $dp_paragraph,
        Structure $dp_link,
        Structure $dp_blockquote,
        Structure $dp_code
    ): void {
        $supported_structure = $this->simple_doc_markdown_shape->getSupportedStructure();

        $expected = [
            $dp_bold,
            $dp_italic,
            $dp_heading_1,
            $dp_heading_2,
            $dp_heading_3,
            $dp_heading_4,
            $dp_heading_5,
            $dp_heading_6,
            $dp_unordered_list,
            $dp_ordered_list,
            $dp_paragraph,
            $dp_link,
            $dp_blockquote,
            $dp_code
        ];

        $this->assertEquals($expected, $supported_structure);
    }

    /**
     * @dataProvider stringComplianceDataProvider
     */
    public function testIsRawStringCompliant(string $markdown_string, bool $compliance): void
    {
        $this->assertEquals($this->simple_doc_markdown_shape->isRawStringCompliant($markdown_string), $compliance);
    }
}
