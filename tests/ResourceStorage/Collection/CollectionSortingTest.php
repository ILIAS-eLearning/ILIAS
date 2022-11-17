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

use ILIAS\ResourceStorage\AbstractBaseResourceBuilderTest;
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
class CollectionSortingTest extends AbstractBaseResourceBuilderTest
{
    public const DUMMY_RCID = 'dummy-rcid';

    protected CollectionIdentificationGenerator $rcid_generator;
    private CollectionBuilder $collection_builder;
    private ResourceCollectionIdentification $rcid;
    private ResourceCollection $collection;
    private ResourceIdentification $rid_one;
    private StorableFileResource $resource_one;
    private Revision $revision_one;
    private ResourceIdentification $rid_two;
    private StorableFileResource $resource_two;
    private Revision $revision_two;
    private Sorter $sorter;
    private ResourceIdentification $rid_three;
    private StorableFileResource $resource_three;
    private Revision $revision_three;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rcid_generator = new DummyIDGenerator(self::DUMMY_RCID);
        $this->resource_builder = $this->createMock(ResourceBuilder::class);
        $this->collection_builder = $this->createMock(CollectionBuilder::class);
        $this->rcid = new ResourceCollectionIdentification(self::DUMMY_RCID);
        $this->collection = new ResourceCollection(
            $this->rcid,
            ResourceCollection::NO_SPECIFIC_OWNER,
            ''
        );
        $this->rid_one = new ResourceIdentification('rid_one');
        $this->resource_one = $this->createMock(StorableFileResource::class);
        $this->revision_one = $this->createMock(Revision::class);

        $this->rid_two = new ResourceIdentification('rid_two');
        $this->resource_two = $this->createMock(StorableFileResource::class);
        $this->revision_two = $this->createMock(Revision::class);

        $this->rid_three = new ResourceIdentification('rid_three');
        $this->resource_three = $this->createMock(StorableFileResource::class);
        $this->revision_three = $this->createMock(Revision::class);

        $this->sorter = new Sorter(
            $this->resource_builder,
            $this->collection_builder,
            $this->collection
        );

        // RESOURCES
        $this->collection->add($this->rid_one);
        $this->collection->add($this->rid_two);
        $this->collection->add($this->rid_three);

        // EXPECTATIONS
        $this->resource_one->expects($this->atLeastOnce())
                           ->method('getCurrentRevision')
                           ->willReturn($this->revision_one);

        $this->resource_two->expects($this->atLeastOnce())
                           ->method('getCurrentRevision')
                           ->willReturn($this->revision_two);

        $this->resource_three->expects($this->atLeastOnce())
                             ->method('getCurrentRevision')
                             ->willReturn($this->revision_three);

        $this->resource_builder->expects($this->atLeastOnce())
                               ->method('get')
                               ->withConsecutive(
                                   [$this->rid_one],
                                   [$this->rid_two],
                                   [$this->rid_two],
                                   [$this->rid_three]
                               )
                               ->will(
                                   $this->onConsecutiveCalls(
                                       $this->resource_one,
                                       $this->resource_two,
                                       $this->resource_two,
                                       $this->resource_three
                                   )
                               );
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
        $this->assertEquals(
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
        $this->assertEquals(
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
        $this->assertEquals(
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
        $this->assertEquals(
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
        $this->assertEquals(
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
        $this->assertEquals(
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
        $this->assertEquals(
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
        $this->assertEquals(
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
        $this->assertEquals(
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
        return array_map(function (ResourceIdentification $rid): string {
            return $rid->serialize();
        }, $collection->getResourceIdentifications());
    }
}
