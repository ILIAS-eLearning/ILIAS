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

namespace ILIAS\Export\ExportHandler\Repository\Wrapper\DB;

use ilDBInterface;
use ILIAS\Export\ExportHandler\I\FactoryInterface as ilExportHandlerFactoryInterface;
use ILIAS\Export\ExportHandler\I\Repository\Wrapper\DB\FactoryInterface as ilExportHandlerRepositoryDBWrapperFactoryInterface;
use ILIAS\Export\ExportHandler\I\Repository\Wrapper\DB\HandlerInterface as ilExportHandlerRepositoryDBWrapperInterface;
use ILIAS\Export\ExportHandler\Repository\Wrapper\DB\Handler as ilExportHandlerRepositoryDBWrapper;

class Factory implements ilExportHandlerRepositoryDBWrapperFactoryInterface
{
    protected ilExportHandlerFactoryInterface $export_handler;
    protected ilDBInterface $db;

    public function __construct(
        ilExportHandlerFactoryInterface $export_handler,
        ilDBInterface $db
    ) {
        $this->export_handler = $export_handler;
        $this->db = $db;
    }

    public function handler(): ilExportHandlerRepositoryDBWrapperInterface
    {
        return new ilExportHandlerRepositoryDBWrapper(
            $this->export_handler,
            $this->db,
            $this->export_handler->wrapper()->dataFactory()->handler()
        );
    }
}
