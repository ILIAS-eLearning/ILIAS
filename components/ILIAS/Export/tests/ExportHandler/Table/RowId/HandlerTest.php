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
use ILIAS\Export\ExportHandler\Table\RowId\Handler as ilExportHandlerTableRowId;
use PHPUnit\Framework\TestCase;

class HandlerTest extends TestCase
{
    public function testExportHandlerTableRowId(): void
    {
        $file_identifier = "super_xml:something";
        $export_option_id = "super_export_option";
        $composit_id = $export_option_id . ':' . $file_identifier;
        try {
            $table_row_id = new ilExportHandlerTableRowId();
            $table_row_id_1 = $table_row_id
                ->withFileIdentifier($file_identifier)
                ->withExportOptionId($export_option_id);
            $table_row_id_2 = $table_row_id->withCompositId($composit_id);
            self::assertEquals($file_identifier, $table_row_id_1->getFileIdentifier());
            self::assertEquals($export_option_id, $table_row_id_1->getExportOptionId());
            self::assertEquals($composit_id, $table_row_id_1->getCompositId());
            self::assertEquals($file_identifier, $table_row_id_2->getFileIdentifier());
            self::assertEquals($export_option_id, $table_row_id_2->getExportOptionId());
            self::assertEquals($composit_id, $table_row_id_2->getCompositId());
        } catch (Exception $exception) {
            self::fail($exception->getMessage());
        }
    }
}
