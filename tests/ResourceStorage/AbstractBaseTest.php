<?php

namespace ILIAS\ResourceStorage;

/** @noRector */
require_once('DummyIDGenerator.php');

use PHPUnit\Framework\TestCase;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\Collection\EntryLockingStringMap;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Revision\FileRevision;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * Class ResourceBuilderTest
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractBaseTest extends TestCase
{
    protected \ILIAS\ResourceStorage\DummyIDGenerator $id_generator;
    /**
     * @var \ilDBInterface|mixed|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $db_mock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->id_generator = new DummyIDGenerator();
        $this->db_mock = $this->getMockBuilder(\ilDBInterface::class)->getMock();
    }

    protected function getDummyUploadResult(string $file_name, string $mime_type, int $size): UploadResult
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

    public function getDummyStream(): FileStream
    {
        return Streams::ofString('dummy_content');
    }

    protected function getDummyFileRevision(ResourceIdentification $id): \ILIAS\ResourceStorage\Revision\FileRevision
    {
        return new FileRevision($id);
    }
}
