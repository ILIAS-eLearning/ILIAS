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

require_once(__DIR__ . '/../AbstractBaseResourceBuilderTest.php');

use ILIAS\ResourceStorage\AbstractBaseResourceBuilderTest;
use ILIAS\ResourceStorage\Collection\CollectionBuilder;
use ILIAS\ResourceStorage\Collection\Collections;
use ILIAS\ResourceStorage\Collection\ResourceCollection;
use ILIAS\ResourceStorage\DummyIDGenerator;
use ILIAS\ResourceStorage\Identification\CollectionIdentificationGenerator;
use ILIAS\ResourceStorage\Identification\ResourceCollectionIdentification;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Preloader\RepositoryPreloader;
use PHPUnit\Framework\MockObject\MockObject;
use ILIAS\ResourceStorage\Events\Subject;

/**
 * Class CollectionTest
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class CollectionTest extends AbstractBaseResourceBuilderTest
{
    /**
     * @var \ILIAS\ResourceStorage\Collection\CollectionBuilder|mixed
     */
    public $collection_builder;
    /**
     * @var \ILIAS\ResourceStorage\Preloader\RepositoryPreloader&\PHPUnit\Framework\MockObject\MockObject|mixed
     */
    public $preloader;
    /**
     * @var \ILIAS\ResourceStorage\Collection\Collections|mixed
     */
    public $collections;
    public const DUMMY_RCID = 'dummy-rcid';

    protected CollectionIdentificationGenerator $rcid_generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rcid_generator = new DummyIDGenerator(self::DUMMY_RCID);

        $this->collection_builder = new CollectionBuilder(
            $this->collection_repository,
            new Subject(),
            $this->rcid_generator
        );

        $this->preloader = $this->getMockBuilder(RepositoryPreloader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resource_builder = new ResourceBuilder(
            $this->storage_handler_factory,
            $this->repositories,
            $this->locking,
            $this->stream_access
        );

        $this->collections = new Collections(
            $this->resource_builder,
            $this->collection_builder,
            $this->preloader,
            new Subject()
        );
    }

    public function testCreateCollection(): void
    {
        $identifiation = $this->rcid_generator->getUniqueResourceCollectionIdentification();
        $this->collection_repository->method('blank')->with($identifiation)->willReturn(
            new ResourceCollection($identifiation, -1, '')
        );

        $id = $this->collections->id();

        $this->assertInstanceOf(ResourceCollectionIdentification::class, $id);
        $this->assertNotInstanceOf(MockObject::class, $id);
        $this->assertEquals(self::DUMMY_RCID, $id->serialize());
    }

    public function testGetCollectionOfUser(): void
    {
        $identifiation = $this->rcid_generator->getUniqueResourceCollectionIdentification();
        $this->collection_repository->method('blank')->with($identifiation)->willReturn(
            new ResourceCollection($identifiation, 42, '')
        );

        $id = $this->collections->id($identifiation->serialize());

        $this->collection_repository->method('getResourceIdStrings')->with($identifiation)->willReturn(
            $this->arrayAsGenerator([])
        );

        $collection = $this->collections->get($id, 42);

        $this->assertInstanceOf(ResourceCollection::class, $collection);
        $this->assertNotInstanceOf(MockObject::class, $collection);
        $this->assertEquals(self::DUMMY_RCID, $collection->getIdentification()->serialize());
        $this->assertEquals([], $collection->getResourceIdentifications());
    }

    public function testGetCollectionOfWrongUser(): void
    {
        $identifiation = new ResourceCollectionIdentification(self::DUMMY_RCID);

        $this->collection_repository->method('existing')->with($identifiation)->willReturn(
            new ResourceCollection($identifiation, 42, '')
        );

        $this->collection_repository->method('getResourceIdStrings')->with($identifiation)->willReturn(
            $this->arrayAsGenerator([])
        );

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Invalid owner of collection');
        $collection = $this->collections->get($identifiation, 84);
    }

    public function testIsIn(): void
    {
        $collection = new ResourceCollection(new ResourceCollectionIdentification(self::DUMMY_RCID), 42, '');

        $this->assertEquals(self::DUMMY_RCID, $collection->getIdentification()->serialize());
        $this->assertEquals(42, $collection->getOwner());
        $this->assertTrue($collection->hasSpecificOwner());
        $this->assertEquals('default', $collection->getTitle());

        $rid_one = new ResourceIdentification('rid_one');
        $rid_two = new ResourceIdentification('rid_two');
        $rid_three = new ResourceIdentification('rid_three');

        $this->assertEquals(0, $collection->count());

        $collection->add($rid_one);
        $this->assertTrue($collection->isIn($rid_one));
        $this->assertFalse($collection->isIn($rid_two));
        $this->assertFalse($collection->isIn($rid_three));
        $this->assertEquals(1, $collection->count());

        $collection->add($rid_two);
        $this->assertTrue($collection->isIn($rid_one));
        $this->assertTrue($collection->isIn($rid_two));
        $this->assertFalse($collection->isIn($rid_three));
        $this->assertEquals(2, $collection->count());

        $collection->add($rid_three);
        $this->assertTrue($collection->isIn($rid_one));
        $this->assertTrue($collection->isIn($rid_two));
        $this->assertTrue($collection->isIn($rid_three));
        $this->assertEquals(3, $collection->count());

        $collection->clear();
        $this->assertFalse($collection->isIn($rid_one));
        $this->assertFalse($collection->isIn($rid_two));
        $this->assertFalse($collection->isIn($rid_three));
        $this->assertEquals(0, $collection->count());
    }

    public function testAddAndRemove(): void
    {
        $collection = new ResourceCollection(
            new ResourceCollectionIdentification(self::DUMMY_RCID),
            ResourceCollection::NO_SPECIFIC_OWNER,
            ''
        );

        $this->assertEquals(self::DUMMY_RCID, $collection->getIdentification()->serialize());
        $this->assertEquals(ResourceCollection::NO_SPECIFIC_OWNER, $collection->getOwner());
        $this->assertFalse($collection->hasSpecificOwner());
        $this->assertEquals('default', $collection->getTitle());

        $rid_one = new ResourceIdentification('rid_one');
        $rid_two = new ResourceIdentification('rid_two');

        $this->assertEquals(0, $collection->count());

        $collection->add($rid_one);
        $this->assertTrue($collection->isIn($rid_one));
        $this->assertEquals(1, $collection->count());

        $collection->add($rid_two);
        $this->assertTrue($collection->isIn($rid_one));
        $this->assertTrue($collection->isIn($rid_two));
        $this->assertEquals(2, $collection->count());

        $collection->remove($rid_one);
        $this->assertFalse($collection->isIn($rid_one));
        $this->assertTrue($collection->isIn($rid_two));
        $this->assertEquals(1, $collection->count());

        $collection->remove($rid_two);
        $this->assertFalse($collection->isIn($rid_one));
        $this->assertFalse($collection->isIn($rid_two));
        $this->assertEquals(0, $collection->count());

        $collection->clear();
        $this->assertFalse($collection->isIn($rid_one));
        $this->assertFalse($collection->isIn($rid_two));
        $this->assertEquals(0, $collection->count());
    }

    public function testDuplicates(): void
    {
        $rid = new ResourceIdentification('rid');
        $same_rid = new ResourceIdentification('rid');

        $collection = new ResourceCollection(
            new ResourceCollectionIdentification(self::DUMMY_RCID),
            ResourceCollection::NO_SPECIFIC_OWNER,
            ''
        );
        $collection->add($rid);
        $collection->add($same_rid);

        $this->assertEquals(1, count($collection->getResourceIdentifications()));
    }


    public function testRidCache(): void
    {
        $resource_collection_identification = new ResourceCollectionIdentification(self::DUMMY_RCID);
        $rid_one = new ResourceIdentification('rid_one');
        $rid_two = new ResourceIdentification('rid_two');
        $rid_three = new ResourceIdentification('rid_three');

        $collections_service = new Collections(
            $this->resource_builder,
            $this->collection_builder,
            $this->preloader,
            new Subject()
        );

        $this->collection_repository
            ->expects($this->once())
            ->method('existing')
            ->with($resource_collection_identification)
            ->willReturn(
                new ResourceCollection($resource_collection_identification, -1, 'title')
            );

        $collection = $collections_service->get(
            $resource_collection_identification
        );
        $this->assertCount(0, $collection->getResourceIdentifications());

        $collection->add($rid_one);
        $collection->add($rid_two);
        $collection->add($rid_three);

        $this->assertCount(3, $collection->getResourceIdentifications());

        $collections_service->store($collection);

        $this->resource_repository->expects($this->exactly(3))
                               ->method('has')
                               ->withConsecutive(
                                   [$rid_one],
                                   [$rid_two],
                                   [$rid_three]
                               )
                               ->willReturn(true);

        $collection = $collections_service->get(
            $resource_collection_identification
        );

        $this->assertCount(3, $collection->getResourceIdentifications());
    }


    protected function arrayAsGenerator(array $array): \Generator
    {
        foreach ($array as $item) {
            yield $item;
        }
    }
}
