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

namespace ILIAS\Export\Test\ExportHandler\Repository\Stakeholder;

use Exception;
use ILIAS\Export\ExportHandler\I\Repository\Stakeholder\HandlerInterface as ilExportHandlerRepositoryStakeholderInterface;
use ILIAS\Export\ExportHandler\Repository\Stakeholder\Handler as ilExportHandlerRepositoryStakeholder;
use PHPUnit\Framework\TestCase;

class HandlerTest extends TestCase
{
    public function testExportHandlerRepositoryStakeholder(): void
    {
        try {
            $stakeholder01 = new ilExportHandlerRepositoryStakeholder(10);
            $stakeholder02 = $stakeholder01->withOwnerId(6);
            $stakeholder03 = new ilExportHandlerRepositoryStakeholder();
            self::assertEquals(10, $stakeholder01->getOwnerId());
            self::assertEquals(6, $stakeholder02->getOwnerId());
            self::assertEquals(ilExportHandlerRepositoryStakeholderInterface::DEFAULT_OWNER_ID, $stakeholder03->getOwnerId());
        } catch (Exception $exception) {
            self::fail($exception->getMessage());
        }
    }
}
