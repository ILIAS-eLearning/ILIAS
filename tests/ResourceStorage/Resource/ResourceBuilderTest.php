<?php

namespace ILIAS\ResourceStorage\Resource;

use ILIAS\ResourceStorage\Revision\UploadedFileRevision;
use ILIAS\MainMenu\Tests\DummyIDGenerator;
use ILIAS\ResourceStorage\Lock\LockHandlerResult;
use ILIAS\ResourceStorage\Revision\FileStreamRevision;
use ILIAS\ResourceStorage\AbstractBaseResourceBuilderTest;

/**
 * Class ResourceBuilderTest
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ResourceBuilderTest extends AbstractBaseResourceBuilderTest
{

    public function testNewUpload() : void
    {
        // EXPECTED VALUES
        $expected_file_name = 'info.xml';
        $expected_owner_id = 6;
        $expected_version_number = 99;
        $expected_mime_type = 'text/xml';
        $expected_size = 128;

        $resource_builder = new ResourceBuilder(
            $this->storage_handler,
            $this->revision_repository,
            $this->resource_repository,
            $this->information_repository,
            $this->stakeholder_repository,
            $this->locking
        );

        // MOCK
        list($upload_result, $info_resolver, $identification) = $this->mockResourceAndRevision(
            $expected_file_name,
            $expected_mime_type,
            $expected_size, $expected_version_number, $expected_owner_id
        );

        // RUN
        $resource = $resource_builder->new(
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

