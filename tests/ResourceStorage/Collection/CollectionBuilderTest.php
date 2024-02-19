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

use PHPUnit\Framework\TestCase;
use ILIAS\ResourceStorage\Collection\CollectionBuilder;
use ILIAS\ResourceStorage\Collection\Repository\CollectionRepository;
use ILIAS\ResourceStorage\Collection\ResourceCollection;
use ILIAS\ResourceStorage\Identification\ResourceCollectionIdentification;
use ILIAS\ResourceStorage\DummyIDGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use ILIAS\ResourceStorage\Collection\Collections;
use ILIAS\ResourceStorage\Preloader\RepositoryPreloader;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Events\Subject;

/**
 * Class CollectionBuilderTest
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class CollectionBuilderTest extends TestCase
{
    public const DUMMY_RCID = 'dummy-rcid';
    private CollectionBuilder $collection_builder;

    protected function setUp(): void
    {
        $this->collection_builder = new CollectionBuilder(
            $this->collection_repo = $this->createMock(CollectionRepository::class),
            new Subject(),
            new DummyIDGenerator(self::DUMMY_RCID)
        );
        $this->collections = new Collections(
            $this->resource_builder = $this->createMock(ResourceBuilder::class),
            $this->collection_builder,
            $this->createMock(RepositoryPreloader::class),
            new Subject()
        );
    }

    public function testGetCollectionTwice(): void
    {
        $rcid = new ResourceCollectionIdentification(self::DUMMY_RCID);

        $this->collection_repo->expects($this->once())
                              ->method('existing')
                              ->with($rcid)
                              ->willReturn(
                                  new ResourceCollection($rcid, -1, '')
                              );

        $this->collection_repo->expects($this->once())
                              ->method('getResourceIdStrings')
                              ->with($rcid)
                              ->willReturn(
                                  $this->arrayAsGenerator([
                                      'rid1',
                                      'rid2',
                                      'rid3'
                                  ])
                              );

        $this->resource_builder->expects($this->exactly(6))
                               ->method('has')
                               ->withConsecutive(
                                   [new ResourceIdentification('rid1')],
                                   [new ResourceIdentification('rid2')],
                                   [new ResourceIdentification('rid3')],
                                   [new ResourceIdentification('rid1')],
                                   [new ResourceIdentification('rid2')],
                                   [new ResourceIdentification('rid3')],
                               )
                               ->willReturn(
                                   true,
                                   true,
                                   true,
                                   true,
                                   true,
                                   true,
                               );

        $collection = $this->collections->get($rcid, null);

        $this->assertInstanceOf(ResourceCollection::class, $collection);
        $this->assertNotInstanceOf(MockObject::class, $collection);

        $this->assertEquals(3, $collection->count());

        $collection = $this->collections->get($rcid, null);

        $this->assertEquals(3, $collection->count());
    }

    protected function arrayAsGenerator(array $array): \Generator
    {
        foreach ($array as $item) {
            yield $item;
        }
    }

}
