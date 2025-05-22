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

namespace ILIAS\Export\ExportHandler\Info\Export\Container\ObjectId;

use ILIAS\Export\ExportHandler\I\FactoryInterface as ilExportHandlerFactoryInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\Container\ObjectId\CollectionBuilderInterface as ilExportHandlerContainerExportInfoObjectIdCollectionBuilderInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\Container\ObjectId\CollectionInterface as ilExportHandlerContainerExportInfoObjectIdCollectionInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\Container\ObjectId\FactoryInterface as ilExportHandlerContainerExportInfoObjectIdFactoryInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\Container\ObjectId\HandlerInterface as ilExportHandlerContainerExportInfoObjectIdInterface;
use ILIAS\Export\ExportHandler\Info\Export\Container\ObjectId\Collection as ilExportHandlerContainerExportInfoObjectIdCollection;
use ILIAS\Export\ExportHandler\Info\Export\Container\ObjectId\CollectionBuilder as ilExportHandlerContainerExportInfoObjectIdCollectionBuilder;
use ILIAS\Export\ExportHandler\Info\Export\Container\ObjectId\Handler as ilExportHandlerContainerExportInfoObjectId;

class Factory implements ilExportHandlerContainerExportInfoObjectIdFactoryInterface
{
    protected ilExportHandlerFactoryInterface $export_handler;

    public function __construct(ilExportHandlerFactoryInterface $export_handler)
    {
        $this->export_handler = $export_handler;
    }

    public function handler(): ilExportHandlerContainerExportInfoObjectIdInterface
    {
        return new ilExportHandlerContainerExportInfoObjectId();
    }

    public function collection(): ilExportHandlerContainerExportInfoObjectIdCollectionInterface
    {
        return new ilExportHandlerContainerExportInfoObjectIdCollection();
    }

    public function collectionBuilder(): ilExportHandlerContainerExportInfoObjectIdCollectionBuilderInterface
    {
        return new ilExportHandlerContainerExportInfoObjectIdCollectionBuilder($this);
    }
}
