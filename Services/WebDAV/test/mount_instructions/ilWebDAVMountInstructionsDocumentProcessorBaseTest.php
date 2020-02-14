<?php

use \PHPUnit\Framework\TestCase;
use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class ilWebDAVMountInstructionsDocumentProcessorBaseTest extends TestCase
{

    private function createDocumentProcessorBaseObject()
    {
        return new class extends ilWebDAVMountInstructionsDocumentProcessorBase
        {
            public function processMountInstructions(string $a_raw_mount_instructions) : array
            {
                return null;
            }
        };
    }

    /**
     * @test
     * @small
     */
    public function parseInstructionsToAssocArray_noOpenNoCloseTags_returnArrayOnlyWithInputString()
    {
        // Arrange
        $instructions = 'hello world';
        $doc_processor = $this->createDocumentProcessorBaseObject();

        // Act
        $parsed_instructions = $doc_processor->parseInstructionsToAssocArray($instructions);

        // Assert
        $this->assertEquals($instructions, $parsed_instructions[0]);
    }

    /**
     * @test
     * @small
     */
    public function parseInstructionsToAssocArray_onlyOpenNoCloseTag_returnArrayOnlyWithInputString()
    {
        // Arrange
        $instructions = 'This is a start [tag] with no end tag';
        $doc_processor = $this->createDocumentProcessorBaseObject();

        // Act
        $parsed_instructions = $doc_processor->parseInstructionsToAssocArray($instructions);

        // Assert
        $this->assertEquals($instructions, $parsed_instructions[0]);
    }

    /**
     * @test
     * @small
     */
    public function parseInstructionsToAssocArray_noOpenOnlyCloseTag_returnArrayOnlyWithInputString()
    {
        // Arrange
        $instructions = 'There is no start tag but an end [/tag] in the string';
        $doc_processor = $this->createDocumentProcessorBaseObject();

        // Act
        $parsed_instructions = $doc_processor->parseInstructionsToAssocArray($instructions);

        // Assert
        $this->assertEquals($instructions, $parsed_instructions[0]);
    }

    /**
     * @test
     * @small
     */
    public function parseInstructionsToAssocArray_openTagAtStartCloseTagAtEnd_returnArrayOnlyWithInputString()
    {
        // Arrange
        $instrunction_text = 'This are the mount Instructions';
        $tag_title = 'tag';
        $start_tag = "[$tag_title]";
        $end_tag = "[/$tag_title]";
        $instructions = $start_tag . $instrunction_text . $end_tag;
        $doc_processor = $this->createDocumentProcessorBaseObject();

        // Act
        $parsed_instructions = $doc_processor->parseInstructionsToAssocArray($instructions);

        // Assert
        $this->assertEquals($instrunction_text, $parsed_instructions[$tag_title]);
    }

    /**
     * @test
     * @small
     */
    public function parseInstructionsToAssocArray_tagsContainSpaces_returnArrayOnlyWithInputString()
    {
        // Arrange
        $instrunction_text = 'This are the mount Instructions';
        $tag_title = 'tag with spaces';
        $start_tag = "[$tag_title]";
        $end_tag = "[/$tag_title]";
        $instructions = $start_tag . $instrunction_text . $end_tag;
        $doc_processor = $this->createDocumentProcessorBaseObject();

        // Act
        $parsed_instructions = $doc_processor->parseInstructionsToAssocArray($instructions);

        // Assert
        $this->assertEquals($instrunction_text, $parsed_instructions[$tag_title]);
    }

    /**
     * @test
     * @small
     */
    public function parseInstructionsToAssocArray_tagsContainSpecialChars_returnArrayOnlyWithInputString()
    {
        // Arrange
        $instrunction_text = 'This are the mount Instructions';
        $tag_title = 'tag_w!th$pecial"chars?';
        $start_tag = "[$tag_title]";
        $end_tag = "[/$tag_title]";
        $instructions = $start_tag . $instrunction_text . $end_tag;
        $doc_processor = $this->createDocumentProcessorBaseObject();

        // Act
        $parsed_instructions = $doc_processor->parseInstructionsToAssocArray($instructions);

        // Assert
        $this->assertEquals($instrunction_text, $parsed_instructions[$tag_title]);
    }

    /**
     * @test
     * @small
     */
    public function parseInstructionsToAssocArray_beforeStartTagAndAfterEndTagIsText_returnArrayOnlyWithStringBetweenTags()
    {
        // Arrange
        $instrunction_text = 'This are the mount Instructions';
        $tag_title = 'tag';
        $start_tag = "[$tag_title]";
        $end_tag = "[/$tag_title]";
        $instructions = 'This will be cut off' . $start_tag . $instrunction_text . $end_tag . 'and this of will be cut off as well';
        $doc_processor = $this->createDocumentProcessorBaseObject();

        // Act
        $parsed_instructions = $doc_processor->parseInstructionsToAssocArray($instructions);

        // Assert
        $this->assertEquals($instrunction_text, $parsed_instructions[$tag_title]);
    }

    /**
     * @test
     * @small
     */
    public function parseInstructionsToAssocArray_placeholderBeforeStartTag_returnArrayOnlyWithStringBetweenTags()
    {
        // Arrange
        $instruction_text = 'This are the mount Instructions';
        $tag_title = 'tag';
        $start_tag = "[$tag_title]";
        $end_tag = "[/$tag_title]";
        $instructions = 'Here is a [placeholder] hidden before the start tag' . $start_tag . $instruction_text . $end_tag;
        $doc_processor = $this->createDocumentProcessorBaseObject();

        // Act
        $parsed_instructions = $doc_processor->parseInstructionsToAssocArray($instructions);

        // Assert
        $this->assertEquals($instruction_text, $parsed_instructions[$tag_title]);
    }

    /**
     * @test
     * @small
     */
    public function parseInstructionsToAssocArray_withTwoOpenAndCloseTags_returnArrayWithBothInstructions()
    {
        // Arrange
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

        // Act
        $parsed_instructions = $doc_processor->parseInstructionsToAssocArray($instructions);

        // Assert
        $this->assertEquals($instruction_text1, $parsed_instructions[$tag_title1]);
        $this->assertEquals($instruction_text2, $parsed_instructions[$tag_title2]);
    }
}
