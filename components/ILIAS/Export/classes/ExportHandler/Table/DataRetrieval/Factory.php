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

namespace ILIAS\Export\ExportHandler\Table\DataRetrieval;

use ilExportGUI;
use ILIAS\DI\UIServices as ilUIServices;
use ILIAS\Export\ExportHandler\I\FactoryInterface as ilExportHandlerFactoryInterface;
use ILIAS\Export\ExportHandler\I\Table\DataRetrieval\FactoryInterface as ilExportHandlerTableDataRetrievalFactoryInterface;
use ILIAS\Export\ExportHandler\I\Table\DataRetrieval\HandlerInterface as ilExportHandlerTableDataRetrievalInterface;
use ILIAS\Export\ExportHandler\Table\DataRetrieval\Handler as ilExportHandlerTableDataRetrieval;
use ilLanguage;
use ilObject;

class Factory implements ilExportHandlerTableDataRetrievalFactoryInterface
{
    public function __construct(
        protected ilExportHandlerFactoryInterface $export_handler,
        protected ilUIServices $ui_services,
        protected ilExportGUI $export_gui,
        protected ilObject $export_object,
        protected ilLanguage $lng
    ) {
    }

    public function handler(): ilExportHandlerTableDataRetrievalInterface
    {
        return new ilExportHandlerTableDataRetrieval(
            $this->lng,
            $this->export_handler
        );
    }
}
