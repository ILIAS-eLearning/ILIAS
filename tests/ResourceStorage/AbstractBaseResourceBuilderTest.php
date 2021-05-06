<?php

namespace ILIAS\ResourceStorage;

require_once('AbstractBaseTest.php');

use ILIAS\ResourceStorage\Resource\InfoResolver\UploadInfoResolver;
use ILIAS\ResourceStorage\Resource\StorableFileResource;
use ILIAS\ResourceStorage\Revision\UploadedFileRevision;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\ResourceStorage\Information\Information;
use Psr\Http\Message\UploadedFileInterface;
use ILIAS\ResourceStorage\Information\Repository\InformationRepository;
use ILIAS\ResourceStorage\Resource\Repository\ResourceRepository;
use ILIAS\ResourceStorage\Revision\Repository\RevisionRepository;
use ILIAS\ResourceStorage\StorageHandler\StorageHandler;
use ILIAS\ResourceStorage\Resource\ResourceBuilder;
use ILIAS\ResourceStorage\Stakeholder\Repository\StakeholderRepository;
use ILIAS\ResourceStorage\Lock\LockHandler;

require_once('DummyIDGenerator.php');

/**
 * Class AbstractBaseResourceBuilderTest
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractBaseResourceBuilderTest extends AbstractBaseTest
{
    /**
     * @var Revision|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $revision;
    /**
     * @var Information|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $information;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|UploadedFileInterface
     */
    protected $upload_result;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|InformationRepository
     */
    protected $information_repository;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ResourceRepository
     */
    protected $resource_repository;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|RevisionRepository
     */
    protected $revision_repository;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|StorageHandler
     */
    protected $storage_handler;
    /**
     * @var ResourceBuilder
     */
    protected $resource_builder;
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
        $this->information = $this->createMock(Information::class);
        $this->revision = $this->createMock(Revision::class);
    }

    /**
     * @param string $expected_file_name
     * @param string $expected_mime_type
     * @param int    $expected_size
     * @param int    $expected_version_number
     * @param int    $expected_owner_id
     * @return array
     * @throws \Exception
     */
    protected function mockResourceAndRevision(
        string $expected_file_name,
        string $expected_mime_type,
        int $expected_size,
        int $expected_version_number,
        int $expected_owner_id
    ) : array {
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
        return array($upload_result, $info_resolver, $identification);
    }
}

