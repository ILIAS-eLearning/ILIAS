<?php

namespace ILIAS\ResourceStorage;

/** @noRector  */
require_once('AbstractBaseTest.php');
/** @noRector  */
require_once('DummyIDGenerator.php');

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
use ILIAS\ResourceStorage\StorageHandler\StorageHandlerFactory;

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
    protected \ILIAS\ResourceStorage\Resource\ResourceBuilder $resource_builder;
    /**
     * @var StakeholderRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $stakeholder_repository;
    /**
     * @var LockHandler|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $locking;
    /**
     * @var StorageHandlerFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storage_handler_factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->storage_handler = $this->createMock(StorageHandler::class);
        $this->storage_handler_factory = $this->createMock(StorageHandlerFactory::class);
        $this->storage_handler_factory->method('getPrimary')->willReturn($this->storage_handler);
        $this->revision_repository = $this->createMock(RevisionRepository::class);
        $this->resource_repository = $this->createMock(ResourceRepository::class);
        $this->information_repository = $this->createMock(InformationRepository::class);
        $this->stakeholder_repository = $this->createMock(StakeholderRepository::class);
        $this->locking = $this->createMock(LockHandler::class);
        $this->information = $this->createMock(Information::class);
        $this->revision = $this->createMock(Revision::class);
    }

    /**
     * @throws \Exception
     */
    protected function mockResourceAndRevision(
        string $expected_file_name,
        string $expected_mime_type,
        int $expected_size,
        int $expected_version_number,
        int $expected_owner_id
    ): array {
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
