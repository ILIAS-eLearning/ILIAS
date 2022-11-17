<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

namespace ILIAS\ResourceStorage\Revision;

use ILIAS\ResourceStorage\AbstractBaseTest;
use ILIAS\ResourceStorage\Resource\InfoResolver\InfoResolver;
use ILIAS\ResourceStorage\Resource\StorableFileResource;
use ILIAS\ResourceStorage\Revision\Repository\RevisionDBRepository;

/**
 * Class ResourceBuilderTest
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class RevisionRepositoryTest extends AbstractBaseTest
{
    /**
     * @var \ILIAS\ResourceStorage\Resource\StorableFileResource|mixed
     */
    public $resource;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\ILIAS\ResourceStorage\Resource\InfoResolver\InfoResolver
     */
    private \PHPUnit\Framework\MockObject\MockObject $info_resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->info_resolver = $this->createMock(InfoResolver::class);
        $this->resource = new StorableFileResource($this->id_generator->getUniqueResourceIdentification());
    }

    public function testUpload(): void
    {
        $upload_result = $this->getDummyUploadResult(
            'info.xml',
            'text/xml',
            128
        );

        $this->info_resolver->expects($this->once())
            ->method('getNextVersionNumber')
            ->willReturn(100);

        $ar_revision_repo = new RevisionDBRepository($this->db_mock);
        $revision = $ar_revision_repo->blankFromUpload(
            $this->info_resolver,
            $this->resource,
            $upload_result
        );

        $this->assertEquals(100, $revision->getVersionNumber());
    }

    public function testStream(): void
    {
        $stream = $this->getDummyStream();
        $i = random_int(0, mt_getrandmax());

        $this->info_resolver->expects($this->once())
            ->method('getNextVersionNumber')
            ->willReturn($i);

        $ar_revision_repo = new RevisionDBRepository($this->db_mock);
        $revision = $ar_revision_repo->blankFromStream(
            $this->info_resolver,
            $this->resource,
            $stream
        );

        $this->assertEquals($i, $revision->getVersionNumber());
    }

    public function testClone(): void
    {
        $revision = $this->getDummyFileRevision($this->id_generator->getUniqueResourceIdentification());
        $old_revisions_id = 99;
        $revision->setVersionNumber($old_revisions_id);

        $i = 50;
        $this->info_resolver->expects($this->once())
            ->method('getNextVersionNumber')
            ->willReturn($i);

        $ar_revision_repo = new RevisionDBRepository($this->db_mock);
        $revision = $ar_revision_repo->blankFromClone(
            $this->info_resolver,
            $this->resource,
            $revision
        );

        $this->assertEquals($i, $revision->getVersionNumber());
        $this->assertNotEquals($old_revisions_id, $revision->getVersionNumber());
    }
}
