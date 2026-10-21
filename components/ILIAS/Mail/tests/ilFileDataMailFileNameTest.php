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
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\ResourceStorage\Information\FileInformation;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Identification\ResourceCollectionIdentification;
use ILIAS\ResourceStorage\Resource\Repository\CollectionDBRepository;

class ilFileDataMailFileNameTest extends ilMailBaseTestCase
{
    public function testApplyResourceFileNameSetsTitleAndSuffix(): void
    {
        $rid = new ResourceIdentification('ics-rid');
        $information = new FileInformation();
        $information->setTitle('abcdef');

        $revision = $this->createMock(Revision::class);
        $revision->expects($this->once())->method('getInformation')->willReturn($information);
        $revision->expects($this->once())->method('setInformation')->with($information);

        $manage = $this->createMock(Manager::class);
        $manage->expects($this->once())->method('getCurrentRevision')->with($rid)->willReturn($revision);
        $manage->expects($this->once())->method('updateRevision')->with($revision);

        $irss = $this->createStub(IRSS::class);
        $irss->method('manage')->willReturn($manage);

        $reflection = new ReflectionClass(ilFileDataMail::class);
        $sut = $reflection->newInstanceWithoutConstructor();
        $reflection->getProperty('irss')->setValue($sut, $irss);

        $reflection->getMethod('applyResourceFileName')->invoke($sut, $rid, 'appointment_20261008.ics');

        $this->assertSame('appointment_20261008.ics', $information->getTitle());
        $this->assertSame('ics', $information->getSuffix());
    }

    public function testApplyResourceFileNameIgnoresEmptyName(): void
    {
        $manage = $this->createMock(Manager::class);
        $manage->expects($this->never())->method('getCurrentRevision');

        $irss = $this->createStub(IRSS::class);
        $irss->method('manage')->willReturn($manage);

        $reflection = new ReflectionClass(ilFileDataMail::class);
        $sut = $reflection->newInstanceWithoutConstructor();
        $reflection->getProperty('irss')->setValue($sut, $irss);

        $reflection->getMethod('applyResourceFileName')->invoke($sut, new ResourceIdentification('ics-rid'), '');
    }

    public function testAttachmentListingKeepsEqualDisplayTitles(): void
    {
        $info_a = new FileInformation();
        $info_a->setTitle('report.pdf');
        $info_b = new FileInformation();
        $info_b->setTitle('report.pdf');

        $revision_a = $this->createStub(Revision::class);
        $revision_a->method('getInformation')->willReturn($info_a);
        $revision_a->method('getTitle')->willReturn('hash-a');
        $revision_b = $this->createStub(Revision::class);
        $revision_b->method('getInformation')->willReturn($info_b);
        $revision_b->method('getTitle')->willReturn('hash-b');

        $manage = $this->createStub(Manager::class);
        $manage->method('find')->willReturnCallback(
            static fn(string $rid): ResourceIdentification => new ResourceIdentification($rid)
        );
        $manage->method('getCurrentRevision')->willReturnCallback(
            static fn(ResourceIdentification $identification): Revision => $identification->serialize() === 'rid-a'
                ? $revision_a
                : $revision_b
        );

        $irss = $this->createStub(IRSS::class);
        $irss->method('manage')->willReturn($manage);

        $db = $this->createStub(ilDBInterface::class);
        $db->method('queryF')->willReturn($this->createStub(ilDBStatement::class));
        $db->method('fetchAssoc')->willReturnOnConsecutiveCalls(
            [CollectionDBRepository::R_IDENTIFICATION => 'rid-a'],
            [CollectionDBRepository::R_IDENTIFICATION => 'rid-b'],
            null
        );

        $reflection = new ReflectionClass(ilFileDataMail::class);
        $sut = $reflection->newInstanceWithoutConstructor();
        $reflection->getProperty('irss')->setValue($sut, $irss);
        $reflection->getProperty('db')->setValue($sut, $db);

        $listing = $sut->getAttachmentListing(new ResourceCollectionIdentification('rcid'));

        $this->assertSame(['rid-a', 'rid-b'], array_keys($listing));
        $this->assertSame(['hash-a', 'hash-b'], array_column($listing, 'md5'));
        $this->assertSame(['report.pdf', 'report.pdf'], array_column($listing, 'name'));
    }
}
