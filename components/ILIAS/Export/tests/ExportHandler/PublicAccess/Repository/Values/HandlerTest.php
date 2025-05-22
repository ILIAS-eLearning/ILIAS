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

namespace ILIAS\Export\Test\ExportHandler\PublicAccess\Repository\Values;

use Exception;
use ILIAS\Export\ExportHandler\PublicAccess\Repository\Values\Handler as ilExportHandlerPublicAccessRepositoryValues;
use PHPUnit\Framework\TestCase;

class HandlerTest extends TestCase
{
    public function testExportHandlerPublicAccessRepositoryValues(): void
    {
        $identification = "xmlxml";
        $export_option = "exportoption1";
        try {
            $repository_values = new ilExportHandlerPublicAccessRepositoryValues();
            $repository_values_with_identification = $repository_values
                ->withIdentification($identification);
            $repository_values_with_export_option = $repository_values
                ->withExportOptionId($export_option);
            $repository_values_complete = $repository_values
                ->withExportOptionId($export_option)
                ->withIdentification($identification);
            self::assertFalse($repository_values->isValid());
            self::assertFalse($repository_values_with_identification->isValid());
            self::assertFalse($repository_values_with_export_option->isValid());
            self::assertTrue($repository_values_complete->isValid());
            self::assertEquals($identification, $repository_values_with_identification->getIdentification());
            self::assertEquals($identification, $repository_values_complete->getIdentification());
            self::assertEquals($export_option, $repository_values_complete->getExportOptionId());
            self::assertEquals($export_option, $repository_values_with_export_option->getExportOptionId());
        } catch (Exception $exception) {
            self::fail($exception->getMessage());
        }
    }
}
