<?php

namespace ILIAS\ResourceStorage;

require_once('DummyIDGenerator.php');

use PHPUnit\Framework\TestCase;
use ILIAS\ResourceStorage\Identification\IdentificationGenerator;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\Collection\EntryLockingStringMap;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\ResourceStorage\Revision\FileRevision;

/**
 * Class ResourceBuilderTest
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractBaseTest extends TestCase
{
    /**
     * @var IdentificationGenerator
     */
    protected $id_generator;

    /**
     * @inheritDoc
     */
    protected function setUp() : void
    {
        parent::setUp();
        $this->id_generator = new DummyIDGenerator();
    }

    /**
     * @return UploadResult
     */
    protected function getDummyUploadResult(string $file_name, string $mime_type, int $size) : UploadResult
    {
        return new UploadResult(
            $file_name,
            $size,
            $mime_type,
            new EntryLockingStringMap(),
            new ProcessingStatus(ProcessingStatus::OK, 'No processors were registered.'),
            'dummy/path'
        );
    }

    public function getDummyStream() : FileStream
    {
        return Streams::ofString('dummy_content');
    }

    protected function getDummyFileRevision(ResourceIdentification $id) : Revision
    {
        return new FileRevision($id);
    }

}

