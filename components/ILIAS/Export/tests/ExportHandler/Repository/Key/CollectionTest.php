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

use Exception;
use ILIAS\Data\ObjectId;
use ILIAS\Export\ExportHandler\I\Repository\Key\HandlerInterface as ilExportHandlerRepositoryKeyInterface;
use ILIAS\Export\ExportHandler\Repository\Key\Collection as ilExportHandlerRepositoryKeyCollection;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    public function testExportHandlerRepositoryKeyCollection(): void
    {
        $object_id_mock_01 = $this->createMock(ObjectId::class);
        $object_id_mock_01->method('toInt')->willReturn(1);
        $object_id_mock_02 = $this->createMock(ObjectId::class);
        $object_id_mock_02->method('toInt')->willReturn(2);
        $object_id_mock_03 = $this->createMock(ObjectId::class);
        $object_id_mock_03->method('toInt')->willReturn(3);
        $element_1_mock = $this->createMock(ilExportHandlerRepositoryKeyInterface::class);
        $element_1_mock->method('getResourceIdSerialized')->willReturn('r1');
        $element_1_mock->method('getObjectId')->willReturn($object_id_mock_01);
        $element_2_mock = $this->createMock(ilExportHandlerRepositoryKeyInterface::class);
        $element_2_mock->method('getResourceIdSerialized')->willReturn('r2');
        $element_2_mock->method('getObjectId')->willReturn($object_id_mock_02);
        $element_3_mock = $this->createMock(ilExportHandlerRepositoryKeyInterface::class);
        $element_3_mock->method('getResourceIdSerialized')->willReturn('r3');
        $element_3_mock->method('getObjectId')->willReturn($object_id_mock_03);
        $empty_collection = new ilExportHandlerRepositoryKeyCollection();
        try {
            $collection_with_elements = $empty_collection
                ->withElement($element_1_mock)
                ->withElement($element_2_mock)
                ->withElement($element_3_mock);
            self::assertEquals(3, $collection_with_elements->count());
            self::assertTrue($collection_with_elements->valid());
            $index = 1;
            foreach ($collection_with_elements as $element) {
                self::assertEquals($index, $element->getObjectId()->toInt());
                self::assertEquals('r' . $index, $element->getResourceIdSerialized());
                $index++;
            }
            self::assertEquals(0, $empty_collection->count());
            self::assertFalse($empty_collection->valid());
        } catch (Exception $exception) {
            self::fail($exception->getMessage());
        }
    }
}
