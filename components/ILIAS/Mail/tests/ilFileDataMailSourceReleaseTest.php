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

declare(strict_types=1);

use ILIAS\ResourceStorage\Manager\Manager;
use ILIAS\ResourceStorage\Services as IRSS;
use PHPUnit\Framework\MockObject\MockObject;
use ILIAS\ResourceStorage\Collection\Collections;
use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Identification\ResourceCollectionIdentification;

class ilFileDataMailSourceReleaseTest extends ilMailBaseTestCase
{
    private Manager&MockObject $manage;
    private ilDBInterface&MockObject $db;

    public function testKeepsReferencedCollection(): void
    {
        $rcid = new ResourceCollectionIdentification('assigned-rcid');
        $sut = $this->createSut(referenced: true);
        $sut->expects($this->never())->method('removeCollection');
        $this->manage->expects($this->never())->method('remove');

        $sut->releaseSourceCollectionAfterDelivery($rcid);
    }

    public function testKeepsUnknownCollection(): void
    {
        $rcid = new ResourceCollectionIdentification('unknown-rcid');
        $sut = $this->createSut(collection_exists: false);
        $sut->expects($this->never())->method('removeCollection');

        $sut->releaseSourceCollectionAfterDelivery($rcid);
    }

    public function testKeepsForeignStakeholderCollection(): void
    {
        $rcid = new ResourceCollectionIdentification('exercise-rcid');
        $sut = $this->createSut(stakeholders: [$this->foreignStakeholder()], rids: ['exercise-rid']);
        $sut->expects($this->never())->method('removeCollection');
        $this->manage->expects($this->never())->method('remove');

        $sut->releaseSourceCollectionAfterDelivery($rcid);
    }

    public function testRemovesCollectionAndUnsharedResources(): void
    {
        $rcid = new ResourceCollectionIdentification('calendar-rcid');
        $sut = $this->createSut(stakeholders: [new ilMailAttachmentStakeholder()], rids: ['ics-rid']);
        $this->db->method('numRows')->willReturn(0);
        $sut->expects($this->once())->method('removeCollection')->with($rcid);
        $this->manage->expects($this->once())->method('remove');

        $sut->releaseSourceCollectionAfterDelivery($rcid);
    }

    public function testResourcesStillInOtherCollectionsSurvive(): void
    {
        $rcid = new ResourceCollectionIdentification('stage-rcid');
        $sut = $this->createSut(stakeholders: [new ilMailAttachmentStakeholder()], rids: ['pool-rid']);
        // The resource is still assigned to e.g. the user's pool
        $this->db->method('numRows')->willReturn(1);
        $sut->expects($this->once())->method('removeCollection')->with($rcid);
        $this->manage->expects($this->never())->method('remove');

        $sut->releaseSourceCollectionAfterDelivery($rcid);
    }

    public function testIgnoresSkippedMarker(): void
    {
        $sut = $this->createSut();
        $sut->expects($this->never())->method('isCollectionReferenced');
        $sut->expects($this->never())->method('removeCollection');

        $sut->releaseCollectionIfUnreferenced(new ResourceCollectionIdentification('-'));
    }

    /**
     * @param list<ResourceStakeholder> $stakeholders
     * @param list<string>              $rids
     */
    private function createSut(
        bool $collection_exists = true,
        array $stakeholders = [],
        bool $referenced = false,
        array $rids = []
    ): ilFileDataMail&MockObject {
        $this->manage = $this->createMock(Manager::class);
        $this->db = $this->createMock(ilDBInterface::class);
        $this->db->method('queryF')->willReturn($this->createStub(ilDBStatement::class));
        $this->db->method('fetchAssoc')->willReturn(null);

        $resource = $this->createStub(StorableResource::class);
        $resource->method('getStakeholders')->willReturn($stakeholders);
        $this->manage->method('getResource')->willReturn($resource);
        $this->manage->method('find')->willReturnCallback(
            static fn(string $rid): ResourceIdentification => new ResourceIdentification($rid)
        );

        $collections = $this->createStub(Collections::class);
        $collections->method('exists')->willReturn($collection_exists);

        $irss = $this->createStub(IRSS::class);
        $irss->method('collection')->willReturn($collections);
        $irss->method('manage')->willReturn($this->manage);

        $sut = $this->getMockBuilder(ilFileDataMail::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'isCollectionReferenced',
                'getRidsFromCollection',
                'removeCollection',
            ])
            ->getMock();
        $sut->method('isCollectionReferenced')->willReturn($referenced);
        $sut->method('getRidsFromCollection')->willReturn($rids);

        $reflection = new ReflectionClass(ilFileDataMail::class);
        $reflection->getProperty('irss')->setValue($sut, $irss);
        $reflection->getProperty('db')->setValue($sut, $this->db);
        $reflection->getProperty('stakeholder')->setValue($sut, new ilMailAttachmentStakeholder());

        return $sut;
    }

    private function foreignStakeholder(): ResourceStakeholder
    {
        $stakeholder = $this->createStub(ResourceStakeholder::class);
        $stakeholder->method('getId')->willReturn('exc_instruction_files');

        return $stakeholder;
    }
}
