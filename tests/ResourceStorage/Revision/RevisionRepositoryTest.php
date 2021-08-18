<?php

namespace ILIAS\ResourceStorage\Revision;

use ILIAS\ResourceStorage\AbstractBaseTest;
use ILIAS\ResourceStorage\Revision\Repository\RevisionARRepository;
use ILIAS\ResourceStorage\Resource\InfoResolver\InfoResolver;
use ILIAS\ResourceStorage\Resource\StorableFileResource;

/**
 * Class ResourceBuilderTest
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class RevisionRepositoryTest extends AbstractBaseTest
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|InfoResolver
     */
    private $info_resolver;

    protected function setUp() : void
    {
        parent::setUp();
        $this->info_resolver = $this->createMock(InfoResolver::class);
        $this->resource = new StorableFileResource($this->id_generator->getUniqueResourceIdentification());
    }

    public function testUpload() : void
    {
        $upload_result = $this->getDummyUploadResult(
            'info.xml',
            'text/xml',
            128
        );

        $this->info_resolver->expects($this->once())
                            ->method('getNextVersionNumber')
                            ->willReturn(100);

        $ar_revision_repo = new RevisionARRepository();
        $revision = $ar_revision_repo->blankFromUpload(
            $this->info_resolver,
            $this->resource,
            $upload_result
        );

        $this->assertEquals(100, $revision->getVersionNumber());
    }

    public function testStream() : void
    {
        $stream = $this->getDummyStream();
        $i = rand();

        $this->info_resolver->expects($this->once())
                            ->method('getNextVersionNumber')
                            ->willReturn($i);

        $ar_revision_repo = new RevisionARRepository();
        $revision = $ar_revision_repo->blankFromStream(
            $this->info_resolver,
            $this->resource,
            $stream
        );

        $this->assertEquals($i, $revision->getVersionNumber());
    }

    public function testClone() : void
    {
        $revision = $this->getDummyFileRevision($this->id_generator->getUniqueResourceIdentification());
        $old_revisions_id = 99;
        $revision->setVersionNumber($old_revisions_id);

        $i = 50;
        $this->info_resolver->expects($this->once())
                            ->method('getNextVersionNumber')
                            ->willReturn($i);

        $ar_revision_repo = new RevisionARRepository();
        $revision = $ar_revision_repo->blankFromClone(
            $this->info_resolver,
            $this->resource,
            $revision
        );

        $this->assertEquals($i, $revision->getVersionNumber());
        $this->assertNotEquals($old_revisions_id, $revision->getVersionNumber());
    }
}

