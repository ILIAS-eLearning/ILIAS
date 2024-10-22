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

namespace ILIAS\Export\ExportHandler\Manager;

use ilAccessHandler;
use ILIAS\Export\ExportHandler\I\FactoryInterface as ilExportHandlerFactoryInterface;
use ILIAS\Export\ExportHandler\I\Manager\FactoryInterface as ilExportHandlerManagerFactoryInterface;
use ILIAS\Export\ExportHandler\I\Manager\HandlerInterface as ilExportHandlerManagerInterface;
use ILIAS\Export\ExportHandler\I\Wrapper\DataFactory\HandlerInterface as ilExportHandlerDataFactoryWrapperInterface;
use ILIAS\Export\ExportHandler\Manager\Handler as ilExportHandlerManager;
use ilObjectDefinition;
use ilTree;

class Factory implements ilExportHandlerManagerFactoryInterface
{
    protected ilExportHandlerFactoryInterface $export_handler;
    protected ilTree $tree;
    protected ilObjectDefinition $obj_definition;
    protected ilAccessHandler $access;
    protected ilExportHandlerDataFactoryWrapperInterface $data_factory_wrapper;

    public function __construct(
        ilExportHandlerFactoryInterface $export_handler,
        ilObjectDefinition $obj_definition,
        ilTree $tree,
        ilAccessHandler $access,
        ilExportHandlerDataFactoryWrapperInterface $data_factory_wrapper
    ) {
        $this->export_handler = $export_handler;
        $this->obj_definition = $obj_definition;
        $this->tree = $tree;
        $this->access = $access;
        $this->data_factory_wrapper = $data_factory_wrapper;
    }

    public function handler(): ilExportHandlerManagerInterface
    {
        return new ilExportHandlerManager(
            $this->export_handler,
            $this->obj_definition,
            $this->tree,
            $this->access,
            $this->data_factory_wrapper
        );
    }
}
