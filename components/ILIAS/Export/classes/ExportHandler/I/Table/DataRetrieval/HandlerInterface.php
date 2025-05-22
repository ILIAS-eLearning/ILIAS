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

namespace ILIAS\Export\ExportHandler\I\Table\DataRetrieval;

use ilExportGUI;
use ILIAS\Export\ExportHandler\I\Consumer\ExportOption\CollectionInterface as ilExportHandlerConsumerExportOptionCollectionInterface;
use ILIAS\UI\Component\Table\DataRetrieval as ilTableDataRetrievalInterface;
use ilObject;

interface HandlerInterface extends ilTableDataRetrievalInterface
{
    public function withExportOptions(
        ilExportHandlerConsumerExportOptionCollectionInterface $export_options
    ): HandlerInterface;

    public function withExportObject(
        ilObject $export_object
    ): HandlerInterface;

    public function withExportGUI(
        ilExportGUI $export_gui
    ): HandlerInterface;
}
