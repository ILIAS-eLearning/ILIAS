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

namespace ILIAS\Export\Test\ExportHandler\Table\RowId;

use Exception;
use ILIAS\Export\ExportHandler\I\Table\RowId\HandlerInterface as ilExportHandlerTableRowIdInterface;
use ILIAS\Export\ExportHandler\Table\RowId\Collection as ilExportHandlerTableRowIdCollection;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    public function testExportHandlerTableRowIdCollection(): void
    {
        $table_row_id_mock_1 = $this->createMock(ilExportHandlerTableRowIdInterface::class);
        $table_row_id_mock_1->method('getFileIdentifier')->willReturn("1");
        $table_row_id_mock_1->method('getExportOptionId')->willReturn("e");
        $table_row_id_mock_1->method('getCompositId')->willReturn("e:1");
        $table_row_id_mock_2 = $this->createMock(ilExportHandlerTableRowIdInterface::class);
        $table_row_id_mock_2->method('getFileIdentifier')->willReturn("2");
        $table_row_id_mock_2->method('getExportOptionId')->willReturn("e");
        $table_row_id_mock_2->method('getCompositId')->willReturn("e:2");
        $table_row_id_mock_3 = $this->createMock(ilExportHandlerTableRowIdInterface::class);
        $table_row_id_mock_3->method('getFileIdentifier')->willReturn("3");
        $table_row_id_mock_3->method('getExportOptionId')->willReturn("e");
        $table_row_id_mock_3->method('getCompositId')->willReturn("e:3");
        try {
            $empty_collection = new ilExportHandlerTableRowIdCollection();
            $collection_with_elements = $empty_collection
                ->withElement($table_row_id_mock_1)
                ->withElement($table_row_id_mock_2)
                ->withElement($table_row_id_mock_3);
            self::assertEquals(0, $empty_collection->count());
            self::assertFalse($empty_collection->valid());
            self::assertEquals(3, $collection_with_elements->count());
            self::assertTrue($collection_with_elements->valid());
            $index = 1;
            foreach ($collection_with_elements as $element) {
                self::assertEquals("" . $index, $element->getFileIdentifier());
                self::assertEquals("e", $element->getExportOptionId());
                self::assertEquals("e:" . $index, $element->getCompositId());
                $index++;
            }
        } catch (Exception $exception) {
            self::fail($exception->getMessage());
        }
    }
}
