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

namespace ILIAS\Export\ExportHandler\Repository\Wrapper\IRSS;

use ILIAS\Export\ExportHandler\I\FactoryInterface as ilExportHandlerFactoryInterface;
use ILIAS\Export\ExportHandler\I\Repository\Wrapper\IRSS\FactoryInterface as ilExportHandlerRepositoryIRSSWrapperFactoryInterface;
use ILIAS\Export\ExportHandler\I\Repository\Wrapper\IRSS\HandlerInterface as ilExportHandlerRepositoryIRSSWrapperInterface;
use ILIAS\Export\ExportHandler\Repository\Wrapper\IRSS\Handler as ilExportHandlerRepositoryIRSSWrapper;
use ILIAS\Filesystem\Filesystems;
use ILIAS\ResourceStorage\Services as ResourcesStorageService;

class Factory implements ilExportHandlerRepositoryIRSSWrapperFactoryInterface
{
    protected ilExportHandlerFactoryInterface $export_handler;
    protected ResourcesStorageService $irss;
    protected Filesystems $filesystems;

    public function __construct(
        ilExportHandlerFactoryInterface $export_handler,
        ResourcesStorageService $irss,
        Filesystems $filesystems
    ) {
        $this->export_handler = $export_handler;
        $this->irss = $irss;
        $this->filesystems = $filesystems;
    }

    public function handler(): ilExportHandlerRepositoryIRSSWrapperInterface
    {
        return new ilExportHandlerRepositoryIRSSWrapper(
            $this->irss,
            $this->filesystems
        );
    }
}
