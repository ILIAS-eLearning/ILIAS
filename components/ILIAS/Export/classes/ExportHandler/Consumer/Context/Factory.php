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

namespace ILIAS\Export\ExportHandler\Consumer\Context;

use ilExportGUI;
use ILIAS\Export\ExportHandler\Consumer\Context\Handler as ilExportHandlerConsumerContext;
use ILIAS\Export\ExportHandler\I\Consumer\Context\FactoryInterface as ilExportHandlerConsumerContextFactoryInterface;
use ILIAS\Export\ExportHandler\I\Consumer\Context\HandlerInterface as ilExportHandlerConsumerContextInterface;
use ILIAS\Export\ExportHandler\I\FactoryInterface as ilExportHandlerFactoryInterface;
use ilObject;

class Factory implements ilExportHandlerConsumerContextFactoryInterface
{
    protected ilExportHandlerFactoryInterface $export_handler;

    public function __construct(
        ilExportHandlerFactoryInterface $export_handler
    ) {
        $this->export_handler = $export_handler;
    }

    public function handler(
        ilExportGUI $export_gui,
        ilObject $export_object
    ): ilExportHandlerConsumerContextInterface {
        return new ilExportHandlerConsumerContext(
            $export_gui,
            $export_object,
            $this->export_handler->consumer()->file()
        );
    }
}
