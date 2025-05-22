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

namespace ILIAS\Export\ExportHandler\I\Table;

use ILIAS\Export\ExportHandler\I\Table\DataRetrieval\HandlerInterface as ilExportHandlerTableDataRetrievalFactoryInterface;
use ILIAS\Export\ExportHandler\I\Table\HandlerInterface as ilExportHandlerTableInterface;
use ILIAS\Export\ExportHandler\I\Table\RowId\FactoryInterface as ilExportHandlerTableRowIdFactoryInterface;

interface FactoryInterface
{
    public function handler(): ilExportHandlerTableInterface;

    public function rowId(): ilExportHandlerTableRowIdFactoryInterface;

    public function dataRetrieval(): ilExportHandlerTableDataRetrievalFactoryInterface;
}
