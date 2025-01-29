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
use ILIAS\Data\Text\Shape\WordOnlyMarkdown;
use ILIAS\Data;
use ILIAS\Refinery\Factory as Refinery;
use PHPUnit\Framework\MockObject\MockObject;
use ILIAS\Data\Text\Structure;

class WordOnlyMarkdownShapeTest extends TestCase
{
    protected WordOnlyMarkdown $word_only_markdown_shape;

    protected function setUp(): void
    {
        $markup = $this->createMock(Data\Text\Markup::class);
        $language = $this->createMock(ilLanguage::class);
        $data_factory = new Data\Factory();
        $refinery = new ILIAS\Refinery\Factory($data_factory, $language);
        $this->word_only_markdown_shape = new WordOnlyMarkdown(new \ILIAS\Refinery\String\MarkdownFormattingToHTML());
    }

    public static function constructDataProvider(): array
    {
        return [
                [Structure::BOLD, Structure::ITALIC]
        ];
    }

    public static function stringComplianceDataProvider(): array
    {
        return [
            ["This text has **bold** and _italic_ content", true],
            ["> Quote block is not allowed", false],
            ["Paragraphs\n\nare not allowed.", false],
            ["Line breaks\\\nare not allowed.", false],
            ["Also these  \nline breaks are not allowed.", false]
        ];
    }

    /**
     * @dataProvider constructDataProvider
     */
    public function testGetSupportedStructure(Structure $dp_bold, Structure $dp_italic): void
    {
        $supported_structure = $this->word_only_markdown_shape->getSupportedStructure();
        $exptected = [
            $dp_bold,
            $dp_italic
        ];

        $this->assertEquals($exptected, $supported_structure);
    }

    /**
     * @dataProvider stringComplianceDataProvider
     */
    public function testIsRawStringCompliant(string $markdown_string, bool $compliance): void
    {
        $this->assertEquals($compliance, $this->word_only_markdown_shape->isRawStringCompliant($markdown_string));
    }
}
