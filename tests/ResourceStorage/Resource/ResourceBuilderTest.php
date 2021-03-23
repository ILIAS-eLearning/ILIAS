<?php

namespace ILIAS\ResourceStorage\Resource;

use ILIAS\ResourceStorage\AbstractBaseTest;
use ILIAS\ResourceStorage\StorageHandler\StorageHandler;
use ILIAS\ResourceStorage\Revision\Repository\RevisionRepository;
use ILIAS\ResourceStorage\Resource\Repository\ResourceRepository;
use ILIAS\ResourceStorage\Information\Repository\InformationRepository;
use ILIAS\ResourceStorage\Stakeholder\Repository\StakeholderRepository;
use ILIAS\ResourceStorage\Lock\LockHandler;
use ILIAS\ResourceStorage\Information\Information;
use ILIAS\ResourceStorage\Revision\Revision;
use Psr\Http\Message\UploadedFileInterface;
use ILIAS\ResourceStorage\Resource\InfoResolver\UploadInfoResolver;
use ILIAS\ResourceStorage\Revision\UploadedFileRevision;
use ILIAS\MainMenu\Tests\DummyIDGenerator;
use ILIAS\ResourceStorage\Lock\LockHandlerResult;
use ILIAS\ResourceStorage\Revision\FileStreamRevision;

/**
 * Class ResourceBuilderTest
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ResourceBuilderTest extends AbstractBaseTest
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

    public function testNewUpload() : void
    {
        // EXPECTED VALUES
        $expected_file_name = 'info.xml';
        $expected_owner_id = 6;
        $expected_version_number = 99;
        $expected_mime_type = 'text/xml';
        $expected_size = 128;

        // PRECONDITIONS
        $identification = $this->id_generator->getUniqueResourceIdentification();

        $upload_result = $this->getDummyUploadResult(
            $expected_file_name,
            $expected_mime_type,
            $expected_size
        );

        $info_resolver = new UploadInfoResolver(
            $upload_result,
            $expected_version_number,
            $expected_owner_id,
            $upload_result->getName()
        );

        // MOCKS
        $blank_resource = new StorableFileResource($identification);
        $this->resource_repository->expects($this->once())
                                  ->method('blank')
                                  ->willReturn($blank_resource);

        $blank_revision = new UploadedFileRevision($blank_resource->getIdentification(), $upload_result);
        $blank_revision->setVersionNumber($info_resolver->getNextVersionNumber());
        $this->revision_repository->expects($this->once())
                                  ->method('blankFromUpload')
                                  ->with(
                                      $info_resolver,
                                      $blank_resource,
                                      $upload_result
                                  )
                                  ->willReturn($blank_revision);

        // RUN
        $resource = $this->resource_builder->new(
            $upload_result,
            $info_resolver
        );

        $this->assertEquals($identification->serialize(), $resource->getIdentification()->serialize());
        $this->assertEquals($expected_version_number, $resource->getCurrentRevision()->getVersionNumber());
        $this->assertEquals($expected_version_number, $resource->getMaxRevision());
        $this->assertEquals($expected_file_name, $resource->getCurrentRevision()->getTitle());
        $this->assertEquals($expected_owner_id, $resource->getCurrentRevision()->getOwnerId());
        $this->assertEquals($expected_file_name, $resource->getCurrentRevision()->getInformation()->getTitle());
        $this->assertEquals($expected_mime_type, $resource->getCurrentRevision()->getInformation()->getMimeType());
        $this->assertEquals($expected_size, $resource->getCurrentRevision()->getInformation()->getSize());

//        $resource->addStakeholder($stakeholder);
//        $this->resource_builder->store($resource);
        // END RUN

    }

    private function prepareNewResource() : void
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
        $this->resource_repository->expects($this->once())->method('store')->with($resource);
        $this->storage_handler->expects($this->once())->method('storeUpload')->with($revision);
        $this->revision_repository->expects($this->once())->method('store')->with($revision);
        $this->information_repository->expects($this->once())->method('store')->with($revision->getInformation(),
            $revision);

        $locking_result = $this->createMock(LockHandlerResult::class);
        $locking_result->expects($this->once())->method('runAndUnlock')->willReturnCallback(
            function () use ($resource) {
                $this->resource_repository->store($resource);

                foreach ($resource->getAllRevisions() as $revision) {
                    if ($revision instanceof UploadedFileRevision) {
                        $this->storage_handler->storeUpload($revision);
                    }
                    if ($revision instanceof FileStreamRevision) {
                        $this->storage_handler->storeStream($revision);
                    }
                    $this->revision_repository->store($revision);
                    $this->information_repository->store($revision->getInformation(), $revision);
                }

                foreach ($resource->getStakeholders() as $stakeholder) {
                    $this->stakeholder_repository->register($resource->getIdentification(), $stakeholder);
                }
            }
        );

        $this->locking->expects($this->once())->method('lockTables')->willReturn($locking_result);
        $this->resource_builder->store($resource);
    }
}

