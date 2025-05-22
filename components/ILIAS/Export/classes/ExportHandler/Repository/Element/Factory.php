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

namespace ILIAS\Export\ExportHandler\Repository\Element;

use ILIAS\Export\ExportHandler\I\FactoryInterface as ilExportHandlerFactoryInterface;
use ILIAS\Export\ExportHandler\I\Repository\Element\CollectionInterface as ilExportHandlerRepositoryElementCollectionInterface;
use ILIAS\Export\ExportHandler\I\Repository\Element\FactoryInterface as ilExportHanlderRepositoryElementFactoryInterface;
use ILIAS\Export\ExportHandler\I\Repository\Element\HandlerInterface as ilExportHandlerRepositoryElementInterface;
use ILIAS\Export\ExportHandler\I\Repository\Element\Wrapper\FactoryInterface as ilExportHandlerRepositoryElementWrapperFactoryInterface;
use ILIAS\Export\ExportHandler\Repository\Element\Collection as ilExportHandlerRepositoryElementCollection;
use ILIAS\Export\ExportHandler\Repository\Element\Handler as ilExportHandlerRepositoryElement;
use ILIAS\Export\ExportHandler\Repository\Element\Wrapper\Factory as ilExportHandlerRepositoryElementWrapperFactory;
use ILIAS\ResourceStorage\Services as ResourcesStorageService;

class Factory implements ilExportHanlderRepositoryElementFactoryInterface
{
    protected ilExportHandlerFactoryInterface $export_handler;
    protected ResourcesStorageService $irss;

    public function __construct(
        ilExportHandlerFactoryInterface $export_handler,
        ResourcesStorageService $irss
    ) {
        $this->export_handler = $export_handler;
        $this->irss = $irss;
    }

    public function handler(): ilExportHandlerRepositoryElementInterface
    {
        return new ilExportHandlerRepositoryElement(
            $this->export_handler->repository()->element()->wrapper()->irss(),
            $this->export_handler->repository()->element()->wrapper()->irssInfo(),
        );
    }

    public function collection(): ilExportHandlerRepositoryElementCollectionInterface
    {
        return new ilExportHandlerRepositoryElementCollection();
    }

    public function wrapper(): ilExportHandlerRepositoryElementWrapperFactoryInterface
    {
        return new ilExportHandlerRepositoryElementWrapperFactory(
            $this->export_handler,
            $this->irss
        );
    }
}
