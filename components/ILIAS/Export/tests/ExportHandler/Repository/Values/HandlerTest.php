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

namespace ILIAS\Export\Test\ExportHandler\Repository\Values;

use DateTimeImmutable;
use Exception;
use ILIAS\Export\ExportHandler\Repository\Values\Handler as ilExportHandlerRepositoryValues;
use PHPUnit\Framework\TestCase;

class HandlerTest extends TestCase
{
    public function testExportHandlerRepositoryValues()
    {
        $owner_id = 10;
        try {
            $datetime = new DateTimeImmutable();
            $values = new ilExportHandlerRepositoryValues();
            $values_with_owner_id = $values
                ->withOwnerId($owner_id);
            $values_with_datetime = $values
                ->withCreationDate($datetime);
            $values_complete = $values
                ->withOwnerId($owner_id)
                ->withCreationDate($datetime);
            self::assertFalse($values->isValid());
            self::assertFalse($values_with_owner_id->isValid());
            self::assertFalse($values_with_datetime->isValid());
            self::assertTrue($values_complete->isValid());
            self::assertEquals($owner_id, $values_with_owner_id->getOwnerId());
            self::assertEquals($owner_id, $values_complete->getOwnerId());
            self::assertEquals($datetime, $values_with_datetime->getCreationDate());
            self::assertEquals($datetime, $values_complete->getCreationDate());
            self::assertTrue($values->equals($values));
            self::assertTrue($values_with_owner_id->equals($values_with_owner_id));
            self::assertTrue($values_with_datetime->equals($values_with_datetime));
            self::assertTrue($values_complete->equals($values_complete));
            self::assertFalse($values->equals($values_with_owner_id));
            self::assertFalse($values->equals($values_complete));
            self::assertFalse($values->equals($values_with_datetime));
        } catch (Exception $exception) {
            self::fail($exception->getMessage());
        }
    }
}
