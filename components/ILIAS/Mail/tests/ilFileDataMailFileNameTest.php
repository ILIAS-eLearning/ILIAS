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
use ILIAS\ResourceStorage\Collection\Collections;
use ILIAS\ResourceStorage\Collection\ResourceCollection;
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
        $revision_a->method('getTitle')->willReturn(md5('report.pdf'));
        $revision_b = $this->createStub(Revision::class);
        $revision_b->method('getInformation')->willReturn($info_b);
        $revision_b->method('getTitle')->willReturn(md5('report.pdf'));

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
        $this->assertSame([md5('rid-a'), md5('rid-b')], array_column($listing, 'md5'));
        $this->assertSame(['report.pdf', 'report.pdf'], array_column($listing, 'name'));
    }

    public function testEqualFileNamesResolveToTheirOwnResource(): void
    {
        $sut = $this->createSutWithEqualTitles(['rid-a', 'rid-b'], md5('report.pdf'));

        $this->assertSame(
            'rid-b',
            $sut->getResourceIdByHash(new ResourceCollectionIdentification('rcid'), md5('rid-b'))?->serialize()
        );
    }

    public function testLegacyNameHashStillResolvesTheFirstMatchingResource(): void
    {
        $sut = $this->createSutWithEqualTitles(['rid-a', 'rid-b'], md5('report.pdf'));

        $this->assertSame(
            'rid-a',
            $sut->getResourceIdByHash(new ResourceCollectionIdentification('rcid'), md5('report.pdf'))?->serialize()
        );
    }

    /**
     * @param list<string> $rids
     */
    private function createSutWithEqualTitles(array $rids, string $revision_title): ilFileDataMail
    {
        $revision = $this->createStub(Revision::class);
        $revision->method('getTitle')->willReturn($revision_title);

        $manage = $this->createStub(Manager::class);
        $manage->method('find')->willReturnCallback(
            static fn(string $rid): ResourceIdentification => new ResourceIdentification($rid)
        );
        $manage->method('getCurrentRevision')->willReturn($revision);

        $collections = $this->createStub(Collections::class);
        $collections->method('exists')->willReturn(true);

        $irss = $this->createStub(IRSS::class);
        $irss->method('manage')->willReturn($manage);
        $irss->method('collection')->willReturn($collections);

        $rows = array_map(
            static fn(string $rid): array => [CollectionDBRepository::R_IDENTIFICATION => $rid],
            $rids
        );
        $db = $this->createStub(ilDBInterface::class);
        $db->method('queryF')->willReturn($this->createStub(ilDBStatement::class));
        $db->method('fetchAssoc')->willReturnOnConsecutiveCalls(...[...$rows, null]);

        $reflection = new ReflectionClass(ilFileDataMail::class);
        $sut = $reflection->newInstanceWithoutConstructor();
        $reflection->getProperty('irss')->setValue($sut, $irss);
        $reflection->getProperty('db')->setValue($sut, $db);

        return $sut;
    }

    public function testAdoptedPoolFileKeepsTheNameWithoutTheUserPrefix(): void
    {
        $dir = sys_get_temp_dir() . '/mail-pool-name-' . uniqid('', true);
        mkdir($dir);
        file_put_contents($dir . '/6_report.pdf', '%PDF');

        $rid = new ResourceIdentification('pool-rid');
        $info = new FileInformation();
        $revision = $this->createMock(Revision::class);
        $revision->method('getInformation')->willReturn($info);
        $revision->expects($this->once())->method('setInformation')->with($info);

        $manage = $this->createMock(Manager::class);
        $manage->expects($this->once())->method('stream')->willReturnCallback(
            function (mixed $stream, mixed $stakeholder, ?string $title) use ($rid): ResourceIdentification {
                $this->assertSame(md5('report.pdf'), $title);

                return $rid;
            }
        );
        $manage->expects($this->once())->method('getCurrentRevision')->with($rid)->willReturn($revision);
        $manage->expects($this->once())->method('updateRevision')->with($revision);

        $collection = $this->createMock(ResourceCollection::class);
        $collection->method('getResourceIdentifications')->willReturn([]);
        $collection->expects($this->once())->method('add')->with($rid);
        $collection->method('getIdentification')->willReturn(new ResourceCollectionIdentification('pool-rcid'));
        $collections = $this->createMock(Collections::class);
        $collections->method('id')->willReturn(new ResourceCollectionIdentification('pool-rcid'));
        $collections->method('get')->willReturn($collection);
        $collections->expects($this->once())->method('store')->willReturn(true);

        $irss = $this->createStub(IRSS::class);
        $irss->method('manage')->willReturn($manage);
        $irss->method('collection')->willReturn($collections);

        $reflection = new ReflectionClass(ilFileDataMail::class);
        $sut = $reflection->newInstanceWithoutConstructor();
        $sut->user_id = 6;
        $sut->mail_path = $dir;
        $reflection->getProperty('irss')->setValue($sut, $irss);
        $reflection->getProperty('stakeholder')->setValue($sut, new ilMailAttachmentStakeholder(6));

        $stored = $sut->adoptPoolFilenamesToCollection(null, ['report.pdf']);

        $this->assertNotNull($stored);
        $this->assertSame('report.pdf', $info->getTitle());
        $this->assertFileExists($dir . '/6_report.pdf');
        unlink($dir . '/6_report.pdf');
        rmdir($dir);
    }
}
