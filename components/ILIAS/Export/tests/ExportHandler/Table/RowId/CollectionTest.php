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

use PHPUnit\Framework\TestCase;
use ILIAS\Export\ExportHandler\Table\RowId\Collection as ilExportHandlerTableRowIdCollection;
use ILIAS\Export\ExportHandler\Table\RowId\Handler as ilExportHandlerTableRowId;

class CollectionTest extends TestCase
{
    public function testExportHandlerTableRowIdCollection(): void
    {
        $table_row_id_mock_1 = $this->createMock(ilExportHandlerTableRowId::class);
        $table_row_id_mock_1->method('getFileIdentifier')->willReturn("1");
        $table_row_id_mock_1->method('getExportOptionId')->willReturn("e");
        $table_row_id_mock_1->method('getCompositId')->willReturn("e:1");
        $table_row_id_mock_2 = $this->createMock(ilExportHandlerTableRowId::class);
        $table_row_id_mock_2->method('getFileIdentifier')->willReturn("2");
        $table_row_id_mock_2->method('getExportOptionId')->willReturn("e");
        $table_row_id_mock_2->method('getCompositId')->willReturn("e:2");
        ;
        $table_row_id_mock_3 = $this->createMock(ilExportHandlerTableRowId::class);
        $table_row_id_mock_3->method('getFileIdentifier')->willReturn("3");
        $table_row_id_mock_3->method('getExportOptionId')->willReturn("e");
        $table_row_id_mock_3->method('getCompositId')->willReturn("e:3");
        $empty_collection = new ilExportHandlerTableRowIdCollection();
        $collection_with_elements = $empty_collection
            ->withElement($table_row_id_mock_1)
            ->withElement($table_row_id_mock_2)
            ->withElement($table_row_id_mock_3);
        $this->assertEquals(0, $empty_collection->count());
        $this->assertFalse($empty_collection->valid());
        $this->assertEquals(3, $collection_with_elements->count());
        $this->assertTrue($collection_with_elements->valid());
        $index = 1;
        foreach ($collection_with_elements as $element) {
            $this->assertEquals("" . $index, $element->getFileIdentifier());
            $this->assertEquals("e", $element->getExportOptionId());
            $this->assertEquals("e:" . $index, $element->getCompositId());
            $index++;
        }
    }
}
