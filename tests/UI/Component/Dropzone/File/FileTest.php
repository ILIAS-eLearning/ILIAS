<?php declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\UI\Component\Dropzone\File;

use ILIAS\UI\Implementation\Component\Dropzone\File\File;

/**
 * @author  Thibeau Fuhrer <thibeau@sr.solutions>
 */
class FileTest extends FileTestBase
{
    protected File $dropzone;

    public function setUp() : void
    {
        $this->dropzone = new class($this->getInputFactory(), $this->getLanguage(), $this->getUploadHandlerMock(), self::FILE_DROPZONE_POST_URL) extends File {
        };

        parent::setUp();
    }

    public function testModifiers() : void
    {
        $title = 'some_title';
        $max_files = 10;
        $max_file_size = 20000;
        $mime_types = ['pdf', 'docx'];

        $dropzone = $this
            ->dropzone
            ->withTitle($title)
            ->withMaxFiles($max_files)
            ->withMaxFileSize($max_file_size)
            ->withAcceptedMimeTypes($mime_types);

        $this->assertEquals($title, $dropzone->getTitle());
        $this->assertEquals($max_files, $dropzone->getMaxFiles());
        $this->assertEquals($max_file_size, $dropzone->getMaxFileSize());
        $this->assertEquals($mime_types, $dropzone->getAcceptedMimeTypes());
    }

    public function testFormGeneration() : void
    {
        $dropzone_form = $this
            ->dropzone
            ->getForm();

        $this->assertEquals(self::FILE_DROPZONE_POST_URL, $dropzone_form->getPostURL());
        $this->assertCount(1, $dropzone_form->getInputs());
        $this->assertInstanceOf(
            \ILIAS\UI\Implementation\Component\Input\Field\File::class,
            $dropzone_form->getInputs()[File::FILE_INPUT_KEY]
        );
    }

    public function testFormGenerationWithMetadataFields() : void
    {
        $dropzone_form = (new class($this->getInputFactory(), $this->getLanguage(), $this->getUploadHandlerMock(), self::FILE_DROPZONE_POST_URL, $this->getFieldFactory()->text('test_input_1')) extends File {
        })->getForm();

        $this->assertEquals(self::FILE_DROPZONE_POST_URL, $dropzone_form->getPostURL());
        $this->assertCount(1, $dropzone_form->getInputs());

        $file_input = $dropzone_form->getInputs()[File::FILE_INPUT_KEY];
        $this->assertInstanceOf(
            \ILIAS\UI\Implementation\Component\Input\Field\File::class,
            $file_input
        );

        $dynamic_inputs = $file_input->getTemplateForDynamicInputs()->getInputs();
        $this->assertInstanceOf(
            \ILIAS\UI\Implementation\Component\Input\Field\Text::class,
            $dynamic_inputs[0]
        );
    }
}
