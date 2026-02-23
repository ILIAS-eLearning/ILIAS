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

namespace ILIAS\ResourceStorage\Resource;

use PHPUnit\Framework\MockObject\MockObject;
use ILIAS\ResourceStorage\AbstractBaseResourceBuilderTestCase;
use ILIAS\ResourceStorage\Collection\CollectionBuilder;
use ILIAS\ResourceStorage\Collection\ResourceCollection;
use ILIAS\ResourceStorage\Collection\Sorter\Sorter;
use ILIAS\ResourceStorage\DummyIDGenerator;
use ILIAS\ResourceStorage\Identification\CollectionIdentificationGenerator;
use ILIAS\ResourceStorage\Identification\ResourceCollectionIdentification;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Information\FileInformation;
use ILIAS\ResourceStorage\Revision\Revision;

/**
 * Class CollectionSortingTest
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class CollectionSortingTest extends AbstractBaseResourceBuilderTestCase
{
    public const DUMMY_RCID = 'dummy-rcid';

    protected CollectionIdentificationGenerator $rcid_generator;
    private ResourceIdentification $rid_one;
    private MockObject $revision_one;
    private ResourceIdentification $rid_two;
    private MockObject $revision_two;
    private Sorter $sorter;
    private ResourceIdentification $rid_three;
    private MockObject $revision_three;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->rcid_generator = new DummyIDGenerator(self::DUMMY_RCID);
        $this->resource_builder = $this->createMock(ResourceBuilder::class);
        $collection_builder = $this->createMock(CollectionBuilder::class);
        $rcid = new ResourceCollectionIdentification(self::DUMMY_RCID);
        $collection = new ResourceCollection(
            $rcid,
            ResourceCollection::NO_SPECIFIC_OWNER,
            ''
        );
        $this->rid_one = new ResourceIdentification('rid_one');
        $resource_one = $this->createMock(StorableFileResource::class);
        $this->revision_one = $this->createMock(Revision::class);

        $this->rid_two = new ResourceIdentification('rid_two');
        $resource_two = $this->createMock(StorableFileResource::class);
        $this->revision_two = $this->createMock(Revision::class);

        $this->rid_three = new ResourceIdentification('rid_three');
        $resource_three = $this->createMock(StorableFileResource::class);
        $this->revision_three = $this->createMock(Revision::class);

        $this->sorter = new Sorter(
            $this->resource_builder,
            $collection_builder,
            $collection
        );

        // RESOURCES
        $collection->add($this->rid_one);
        $collection->add($this->rid_two);
        $collection->add($this->rid_three);

        // EXPECTATIONS
        $resource_one->expects($this->atLeastOnce())
            ->method('getCurrentRevision')
            ->willReturn($this->revision_one);

        $resource_two->expects($this->atLeastOnce())
            ->method('getCurrentRevision')
            ->willReturn($this->revision_two);

        $resource_three->expects($this->atLeastOnce())
            ->method('getCurrentRevision')
            ->willReturn($this->revision_three);

        $map = [
            [$this->rid_one, $resource_one],
            [$this->rid_two, $resource_two],
            [$this->rid_two, $resource_two],
            [$this->rid_three, $resource_three],
        ];
        $this->resource_builder->expects($this->atLeastOnce())
            ->method('get')
            ->willReturnMap($map);
    }

    private function setUpRevisionExpectations(FileInformation $one, FileInformation $two, FileInformation $three): void
    {
        $this->revision_one->expects($this->atLeastOnce())
            ->method('getInformation')
            ->willReturn($one);
        $this->revision_two->expects($this->atLeastOnce())
            ->method('getInformation')
            ->willReturn($two);
        $this->revision_three->expects($this->atLeastOnce())
            ->method('getInformation')
            ->willReturn($three);
    }

    public function testBySizeDescSorting(): void
    {
        // SORTING
        $this->setUpRevisionExpectations(
            (new FileInformation())->setSize(10),
            (new FileInformation())->setSize(20),
            (new FileInformation())->setSize(30)
        );
        $sorted_collection = $this->sorter->desc()->bySize();
        $this->assertSame(
            [
                $this->rid_three->serialize(),
                $this->rid_two->serialize(),
                $this->rid_one->serialize()
            ],
            $this->getFlatOrder($sorted_collection)
        );
    }

    public function testBySizeAscSorting(): void
    {
        // SORTING
        $this->setUpRevisionExpectations(
            (new FileInformation())->setSize(10),
            (new FileInformation())->setSize(20),
            (new FileInformation())->setSize(30)
        );
        $sorted_collection = $this->sorter->asc()->bySize();
        $this->assertSame(
            [
                $this->rid_one->serialize(),
                $this->rid_two->serialize(),
                $this->rid_three->serialize(),
            ],
            $this->getFlatOrder($sorted_collection)
        );
    }

    public function testBySizeDefaultSorting(): void
    {
        // SORTING
        $this->setUpRevisionExpectations(
            (new FileInformation())->setSize(10),
            (new FileInformation())->setSize(20),
            (new FileInformation())->setSize(30)
        );
        $sorted_collection = $this->sorter->bySize();
        $this->assertSame(
            [
                $this->rid_one->serialize(),
                $this->rid_two->serialize(),
                $this->rid_three->serialize(),
            ],
            $this->getFlatOrder($sorted_collection)
        );
    }

    public function testByCreationDateDefaultSorting(): void
    {
        // SORTING
        $this->setUpRevisionExpectations(
            (new FileInformation())->setCreationDate(new \DateTimeImmutable('2020-01-01')),
            (new FileInformation())->setCreationDate(new \DateTimeImmutable('2020-02-02')),
            (new FileInformation())->setCreationDate(new \DateTimeImmutable('2020-03-03'))
        );
        $sorted_collection = $this->sorter->byCreationDate();
        $this->assertSame(
            [
                $this->rid_one->serialize(),
                $this->rid_two->serialize(),
                $this->rid_three->serialize(),
            ],
            $this->getFlatOrder($sorted_collection)
        );
    }

    public function testByCreationDateAscSorting(): void
    {
        // SORTING
        $this->setUpRevisionExpectations(
            (new FileInformation())->setCreationDate(new \DateTimeImmutable('2020-01-01')),
            (new FileInformation())->setCreationDate(new \DateTimeImmutable('2020-02-02')),
            (new FileInformation())->setCreationDate(new \DateTimeImmutable('2020-03-03'))
        );
        $sorted_collection = $this->sorter->asc()->byCreationDate();
        $this->assertSame(
            [
                $this->rid_one->serialize(),
                $this->rid_two->serialize(),
                $this->rid_three->serialize(),
            ],
            $this->getFlatOrder($sorted_collection)
        );
    }

    public function testByCreationDateDescSorting(): void
    {
        // SORTING
        $this->setUpRevisionExpectations(
            (new FileInformation())->setCreationDate(new \DateTimeImmutable('2020-01-01')),
            (new FileInformation())->setCreationDate(new \DateTimeImmutable('2020-02-02')),
            (new FileInformation())->setCreationDate(new \DateTimeImmutable('2020-03-03'))
        );
        $sorted_collection = $this->sorter->desc()->byCreationDate();
        $this->assertSame(
            [
                $this->rid_three->serialize(),
                $this->rid_two->serialize(),
                $this->rid_one->serialize(),
            ],
            $this->getFlatOrder($sorted_collection)
        );
    }

    public function testByTitleDefaultSorting(): void
    {
        // SORTING
        $this->setUpRevisionExpectations(
            (new FileInformation())->setTitle('1_one.jpg'),
            (new FileInformation())->setTitle('2_two.jpg'),
            (new FileInformation())->setTitle('3_three.jpg')
        );
        $sorted_collection = $this->sorter->byTitle();
        $this->assertSame(
            [
                $this->rid_one->serialize(),
                $this->rid_two->serialize(),
                $this->rid_three->serialize(),
            ],
            $this->getFlatOrder($sorted_collection)
        );
    }

    public function testByTitleAscSorting(): void
    {
        // SORTING
        $this->setUpRevisionExpectations(
            (new FileInformation())->setTitle('1_one.jpg'),
            (new FileInformation())->setTitle('2_two.jpg'),
            (new FileInformation())->setTitle('3_three.jpg')
        );
        $sorted_collection = $this->sorter->asc()->byTitle();
        $this->assertSame(
            [
                $this->rid_one->serialize(),
                $this->rid_two->serialize(),
                $this->rid_three->serialize(),
            ],
            $this->getFlatOrder($sorted_collection)
        );
    }

    public function testByTitleDescSorting(): void
    {
        // SORTING
        $this->setUpRevisionExpectations(
            (new FileInformation())->setTitle('1_one.jpg'),
            (new FileInformation())->setTitle('2_two.jpg'),
            (new FileInformation())->setTitle('3_three.jpg')
        );
        $sorted_collection = $this->sorter->desc()->byTitle();
        $this->assertSame(
            [
                $this->rid_three->serialize(),
                $this->rid_two->serialize(),
                $this->rid_one->serialize(),
            ],
            $this->getFlatOrder($sorted_collection)
        );
    }

    private function getFlatOrder(ResourceCollection $collection): array
    {
        return array_map(
            fn(ResourceIdentification $rid): string => $rid->serialize(),
            $collection->getResourceIdentifications()
        );
    }
}
