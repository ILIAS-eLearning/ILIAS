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

namespace ILIAS\Export\Test\ExportHandler\Repository\Key;

use ILIAS\Data\ObjectId;
use PHPUnit\Framework\TestCase;
use ILIAS\Export\ExportHandler\Repository\Key\Handler as ilExportHandlerRepositoryKey;
use ILIAS\Export\ExportHandler\Repository\Key\Collection as ilExportHandlerRepositoryKeyCollection;

class CollectionTest extends TestCase
{
    public function testExportHandlerRepositoryKeyCollection(): void
    {
        $object_id_1 = $this->createMock(ObjectId::class);
        $object_id_1->method('toInt')->willReturn(1);
        $object_id_2 = $this->createMock(ObjectId::class);
        $object_id_2->method('toInt')->willReturn(2);
        $object_id_3 = $this->createMock(ObjectId::class);
        $object_id_3->method('toInt')->willReturn(3);
        $element_1 = $this->createMock(ilExportHandlerRepositoryKey::class);
        $element_1->method('getResourceIdSerialized')->willReturn('r1');
        $element_1->method('getObjectId')->willReturn($object_id_1);
        $element_2 = $this->createMock(ilExportHandlerRepositoryKey::class);
        $element_2->method('getResourceIdSerialized')->willReturn('r2');
        $element_2->method('getObjectId')->willReturn($object_id_2);
        $element_3 = $this->createMock(ilExportHandlerRepositoryKey::class);
        $element_3->method('getResourceIdSerialized')->willReturn('r3');
        $element_3->method('getObjectId')->willReturn($object_id_3);
        $empty_collection = new ilExportHandlerRepositoryKeyCollection();
        $collection_with_elements = $empty_collection
            ->withElement($element_1)
            ->withElement($element_2)
            ->withElement($element_3);
        $this->assertEquals(3, $collection_with_elements->count());
        $this->assertTrue($collection_with_elements->valid());
        $index = 1;
        foreach ($collection_with_elements as $element) {
            $this->assertEquals($index, $element->getObjectId()->toInt());
            $this->assertEquals('r' . $index, $element->getResourceIdSerialized());
            $index++;
        }
        $this->assertEquals(0, $empty_collection->count());
        $this->assertFalse($empty_collection->valid());
    }
}
