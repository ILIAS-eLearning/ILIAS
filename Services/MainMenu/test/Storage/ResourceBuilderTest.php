<?php

namespace ILIAS\MainMenu\Tests;

use ILIAS\FileUpload\Collection\EntryLockingStringMap;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\ResourceStorage\Identification\IdentificationGenerator;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Information\Information;
use ILIAS\ResourceStorage\Information\Repository\InformationRepository;
use ILIAS\ResourceStorage\Resource\Repository\ResourceRepository;
use ILIAS\ResourceStorage\Resource\ResourceBuilder;
use ILIAS\ResourceStorage\Resource\StorableFileResource;
use ILIAS\ResourceStorage\Revision\Repository\RevisionRepository;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\ResourceStorage\Revision\UploadedFileRevision;
use ILIAS\ResourceStorage\StorageHandler\StorageHandler;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UploadedFileInterface;
use ILIAS\ResourceStorage\Stakeholder\Repository\StakeholderRepository;
use ILIAS\ResourceStorage\Lock\LockHandler;
use ILIAS\ResourceStorage\Lock\LockHandlerResult;

class DummyIDGenerator implements IdentificationGenerator
{
    private $id = 'dummy';

    /**
     * DummyIDGenerator constructor.
     * @param string $id
     */
    public function __construct(string $id)
    {
        $this->id = $id;
    }

    /**
     * @inheritDoc
     */
    public function getUniqueResourceIdentification() : ResourceIdentification
    {
        return new ResourceIdentification($this->id);
    }
}

/**
 * Class ResourceBuilderTest
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ResourceBuilderTest extends TestCase
{

    /**
     * @var Revision|\PHPUnit\Framework\MockObject\MockObject
     */
    private $revision;
    /**
     * @var Information|\PHPUnit\Framework\MockObject\MockObject
     */
    private $information;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|UploadedFileInterface
     */
    private $upload_result;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|InformationRepository
     */
    private $information_repository;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ResourceRepository
     */
    private $resource_repository;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|RevisionRepository
     */
    private $revision_repository;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|StorageHandler
     */
    private $storage_handler;
    /**
     * @var ResourceBuilder
     */
    private $resource_builder;
    /**
     * @var StakeholderRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $stakeholder_repository;
    /**
     * @var LockHandler|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $locking;

    /**
     * @inheritDoc
     */
    protected function setUp() : void
    {
        parent::setUp();

        $this->storage_handler = $this->createMock(StorageHandler::class);
        $this->revision_repository = $this->createMock(RevisionRepository::class);
        $this->resource_repository = $this->createMock(ResourceRepository::class);
        $this->information_repository = $this->createMock(InformationRepository::class);
        $this->stakeholder_repository = $this->createMock(StakeholderRepository::class);
        $this->locking = $this->createMock(LockHandler::class);
        $this->resource_builder = new ResourceBuilder(
            $this->storage_handler,
            $this->revision_repository,
            $this->resource_repository,
            $this->information_repository,
            $this->stakeholder_repository,
            $this->locking
        );
        $this->information = $this->createMock(Information::class);
        $this->revision = $this->createMock(Revision::class);
    }

    public function testNewResource() : void
    {
        $file_id = 'my_file_id';
        $file_name = 'testfile.txt';
        $file_mime_type = 'application/base64';
        $file_size = 256;

        $r = new DummyIDGenerator($file_id);
        $identification = $r->getUniqueResourceIdentification();
        $result = $this->getUploadResult($file_name, $file_mime_type, $file_size);

        $this->storage_handler->expects($this->once())
                              ->method('getIdentificationGenerator')
                              ->willReturn($r);

        $this->resource_repository->expects($this->once())
                                  ->method('blank')
                                  ->with($identification)
                                  ->willReturn(new StorableFileResource($identification));

        $this->revision_repository->expects($this->once())
                                  ->method('blank')
                                  ->willReturn(new UploadedFileRevision($identification, $result));

        $resource = $this->resource_builder->new($result);
        $revision = $resource->getCurrentRevision();

        $this->assertEquals($identification->serialize(), $resource->getIdentification()->serialize());
        $this->assertEquals($file_id, $resource->getIdentification()->serialize());
        $this->assertEquals($file_name, $revision->getInformation()->getTitle());
        $this->assertEquals($file_mime_type, $revision->getInformation()->getMimeType());
        $this->assertEquals($file_size, $revision->getInformation()->getSize());

        // Store it
        // $this->resource_repository->expects($this->once())->method('store')->with($resource);
        // $this->storage_handler->expects($this->once())->method('storeUpload')->with($revision);
        // $this->revision_repository->expects($this->once())->method('store')->with($revision);
        // $this->information_repository->expects($this->once())->method('store')->with($revision->getInformation(), $revision);

        $locking_result = $this->createMock(LockHandlerResult::class);
        $locking_result->expects($this->once())->method('runAndUnlock');

        $this->locking->expects($this->once())->method('lockTables')->willReturn($locking_result);
        $this->resource_builder->store($resource);
    }

    /**
     * @return UploadResult
     */
    private function getUploadResult(string $file_name, string $mime_type, int $size) : UploadResult
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
}
