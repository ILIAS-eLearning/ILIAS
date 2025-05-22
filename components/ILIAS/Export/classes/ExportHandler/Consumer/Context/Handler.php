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
use ILIAS\Export\ExportHandler\I\Consumer\Context\HandlerInterface as ilExportHandlerConsumerContextInterface;
use ILIAS\Export\ExportHandler\I\Consumer\File\CollectionBuilderInterface as ilExportHandlerConsumerFileCollectionBuilderInterface;
use ILIAS\Export\ExportHandler\I\Consumer\File\FactoryInterface as ilExportHandlerConsumerFileFactoryInterface;
use ilObject;

class Handler implements ilExportHandlerConsumerContextInterface
{
    protected ilExportGUI $export_gui;
    protected ilObject $export_object;
    protected ilExportHandlerConsumerFileFactoryInterface $consumer_file_factory;

    public function __construct(
        ilExportGUI $export_gui,
        ilObject $export_object,
        ilExportHandlerConsumerFileFactoryInterface $consumer_file_factory
    ) {
        $this->export_gui = $export_gui;
        $this->export_object = $export_object;
        $this->consumer_file_factory = $consumer_file_factory;
    }

    public function exportGUIObject(): ilExportGUI
    {
        return $this->export_gui;
    }

    public function exportObject(): ilObject
    {
        return $this->export_object;
    }

    public function fileCollectionBuilder(): ilExportHandlerConsumerFileCollectionBuilderInterface
    {
        return $this->consumer_file_factory->collectionBuilder();
    }
}
