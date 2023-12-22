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

namespace Test\ImportHandler\File\Namespace;

use PHPUnit\Framework\TestCase;
use ILIAS\Export\ImportHandler\I\File\Namespace\ilCollectionInterface as ilFileNamespaceCollectionInterface;
use ILIAS\Export\ImportHandler\File\Namespace\ilHandler as ilFileNamespaceHandler;
use ILIAS\Export\ImportHandler\File\Namespace\ilCollection as ilFileNamespaceCollection;

class ilCollectionTest extends TestCase
{
    protected function setUp(): void
    {
        $namespace_1 = $this->createMock(ilFileNamespaceHandler::class);
    }

    /**
     * @param ilFileNamespaceHandler[] $expected_elements
     */
    protected function checkCollection(
        ilFileNamespaceCollectionInterface $collection,
        array $expected_elements
    ) {
        $this->assertSameSize($expected_elements, $collection);
        $collection->rewind();
        $this->assertEquals(0, $collection->key());
        for ($i = 0; $i < $collection->count(); $i++) {
            $current = $collection->current();
            $this->assertTrue($collection->valid());
            $this->assertEquals($i, $collection->key());
            $this->assertEquals($expected_elements[$i], $current);
            $collection->next();
        }
        $collection->rewind();
        $this->assertEquals(0, $collection->key());
    }

    public function testCollection(): void
    {
        $namespace_1 = $this->createMock(ilFileNamespaceHandler::class);
        $namespace_1->expects($this->any())->method('getNamespace')->willReturn('namespace_1');
        $namespace_1->expects($this->any())->method('getPrefix')->willReturn('prefix_1');

        $namespace_2 = $this->createMock(ilFileNamespaceHandler::class);
        $namespace_2->expects($this->any())->method('getNamespace')->willReturn('namespace_2');
        $namespace_2->expects($this->any())->method('getPrefix')->willReturn('prefix_2');

        $namespace_3 = $this->createMock(ilFileNamespaceHandler::class);
        $namespace_3->expects($this->any())->method('getNamespace')->willReturn('namespace_3');
        $namespace_3->expects($this->any())->method('getPrefix')->willReturn('prefix_3');

        $collection_one_element = (new ilFileNamespaceCollection())
            ->withElement($namespace_1);
        $collection_two_elements = (new ilFileNamespaceCollection())
            ->withElement($namespace_1)
            ->withElement($namespace_2);
        $collection_three_elements = (new ilFileNamespaceCollection())
            ->withElement($namespace_1)
            ->withElement($namespace_2)
            ->withElement($namespace_3);
        $merged_collection = $collection_three_elements->withMerged($collection_two_elements);

        $this->checkCollection($collection_one_element, [$namespace_1]);
        $this->checkCollection($collection_two_elements, [$namespace_1, $namespace_2]);
        $this->checkCollection($collection_three_elements, [$namespace_1, $namespace_2, $namespace_3]);
        $this->checkCollection($merged_collection, [$namespace_1, $namespace_2, $namespace_3, $namespace_1, $namespace_2]);
    }
}
