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

namespace ILIAS\WebDAV\Tests\Mount;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Small;
use ILIAS\WebDAV\Mount\DocumentProcessorBase;

#[Small]
final class DocumentProcessorBaseTest extends TestCase
{
    private function createDocumentProcessorBaseObject(): DocumentProcessorBase
    {
        return new class () extends DocumentProcessorBase {
            public function processMountInstructions(string $a_raw_mount_instructions): array
            {
                return [];
            }
        };
    }

    #[Test]
    public function parseInstructionsToAssocArray_noOpenNoCloseTags_returnArrayOnlyWithInputString(): void
    {
        $instructions = 'hello world';
        $doc_processor = $this->createDocumentProcessorBaseObject();

        $parsed_instructions = $doc_processor->parseInstructionsToAssocArray($instructions);

        $this->assertEquals($instructions, $parsed_instructions[0]);
    }

    #[Test]
    public function parseInstructionsToAssocArray_onlyOpenNoCloseTag_returnArrayOnlyWithInputString(): void
    {
        $instructions = 'This is a start [tag] with no end tag';
        $doc_processor = $this->createDocumentProcessorBaseObject();

        $parsed_instructions = $doc_processor->parseInstructionsToAssocArray($instructions);

        $this->assertEquals($instructions, $parsed_instructions[0]);
    }

    #[Test]
    public function parseInstructionsToAssocArray_noOpenOnlyCloseTag_returnArrayOnlyWithInputString(): void
    {
        $instructions = 'There is no start tag but an end [/tag] in the string';
        $doc_processor = $this->createDocumentProcessorBaseObject();

        $parsed_instructions = $doc_processor->parseInstructionsToAssocArray($instructions);

        $this->assertEquals($instructions, $parsed_instructions[0]);
    }

    #[Test]
    public function parseInstructionsToAssocArray_openTagAtStartCloseTagAtEnd_returnArrayOnlyWithInputString(): void
    {
        $instruction_text = 'This are the mount Instructions';
        $tag_title = 'tag';
        $start_tag = "[$tag_title]";
        $end_tag = "[/$tag_title]";
        $instructions = $start_tag . $instruction_text . $end_tag;
        $doc_processor = $this->createDocumentProcessorBaseObject();

        $parsed_instructions = $doc_processor->parseInstructionsToAssocArray($instructions);

        $this->assertEquals($instruction_text, $parsed_instructions[$tag_title]);
    }

    #[Test]
    public function parseInstructionsToAssocArray_tagsContainSpaces_returnArrayOnlyWithInputString(): void
    {
        $instruction_text = 'This are the mount Instructions';
        $tag_title = 'tag with spaces';
        $start_tag = "[$tag_title]";
        $end_tag = "[/$tag_title]";
        $instructions = $start_tag . $instruction_text . $end_tag;
        $doc_processor = $this->createDocumentProcessorBaseObject();

        $parsed_instructions = $doc_processor->parseInstructionsToAssocArray($instructions);

        $this->assertEquals($instruction_text, $parsed_instructions[$tag_title]);
    }

    #[Test]
    public function parseInstructionsToAssocArray_tagsContainSpecialChars_returnArrayOnlyWithInputString(): void
    {
        $instruction_text = 'This are the mount Instructions';
        $tag_title = 'tag_w!th$pecial"chars?';
        $start_tag = "[$tag_title]";
        $end_tag = "[/$tag_title]";
        $instructions = $start_tag . $instruction_text . $end_tag;
        $doc_processor = $this->createDocumentProcessorBaseObject();

        $parsed_instructions = $doc_processor->parseInstructionsToAssocArray($instructions);

        $this->assertEquals($instruction_text, $parsed_instructions[$tag_title]);
    }

    #[Test]
    public function parseInstructionsToAssocArray_beforeStartTagAndAfterEndTagIsText_returnArrayOnlyWithStringBetweenTags(
    ): void {
        $instruction_text = 'This are the mount Instructions';
        $tag_title = 'tag';
        $start_tag = "[$tag_title]";
        $end_tag = "[/$tag_title]";
        $instructions = 'This will be cut off' . $start_tag . $instruction_text . $end_tag . 'and this of will be cut off as well';
        $doc_processor = $this->createDocumentProcessorBaseObject();

        $parsed_instructions = $doc_processor->parseInstructionsToAssocArray($instructions);

        $this->assertEquals($instruction_text, $parsed_instructions[$tag_title]);
    }

    #[Test]
    public function parseInstructionsToAssocArray_placeholderBeforeStartTag_returnArrayOnlyWithStringBetweenTags(): void
    {
        $instruction_text = 'This are the mount Instructions';
        $tag_title = 'tag';
        $start_tag = "[$tag_title]";
        $end_tag = "[/$tag_title]";
        $instructions = 'Here is a [placeholder] hidden before the start tag' . $start_tag . $instruction_text . $end_tag;
        $doc_processor = $this->createDocumentProcessorBaseObject();

        $parsed_instructions = $doc_processor->parseInstructionsToAssocArray($instructions);

        $this->assertEquals($instruction_text, $parsed_instructions[$tag_title]);
    }

    #[Test]
    public function parseInstructionsToAssocArray_withTwoOpenAndCloseTags_returnArrayWithBothInstructions(): void
    {
        $instruction_text1 = 'This are the first instructions';
        $instruction_text2 = 'This are the second instructions\'';
        $tag_title1 = 'tag1';
        $start_tag1 = "[$tag_title1]";
        $end_tag1 = "[/$tag_title1]";
        $tag_title2 = 'tag2';
        $start_tag2 = "[$tag_title2]";
        $end_tag2 = "[/$tag_title2]";
        $instructions = $start_tag1 . $instruction_text1 . $end_tag1 . $start_tag2 . $instruction_text2 . $end_tag2;
        $doc_processor = $this->createDocumentProcessorBaseObject();

        $parsed_instructions = $doc_processor->parseInstructionsToAssocArray($instructions);

        $this->assertEquals($instruction_text1, $parsed_instructions[$tag_title1]);
        $this->assertEquals($instruction_text2, $parsed_instructions[$tag_title2]);
    }
}
