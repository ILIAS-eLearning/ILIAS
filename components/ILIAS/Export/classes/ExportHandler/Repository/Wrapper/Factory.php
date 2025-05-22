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

namespace ILIAS\Export\ExportHandler\Repository\Wrapper;

use ilDBInterface;
use ILIAS\Export\ExportHandler\I\FactoryInterface as ilExportHandlerFactoryInterface;
use ILIAS\Export\ExportHandler\I\Repository\Wrapper\DB\FactoryInterface as ilExportHandlerRepositoryDBWrapperFactoryInterface;
use ILIAS\Export\ExportHandler\I\Repository\Wrapper\FactoryInterface as ilExportHandlerRepositoryWrapperFactoryInterface;
use ILIAS\Export\ExportHandler\I\Repository\Wrapper\IRSS\FactoryInterface as ilExportHandlerRepositoryIRSSWrapperFactoryInterface;
use ILIAS\Export\ExportHandler\Repository\Wrapper\DB\Factory as ilExportHandlerRepositoryDBWrapperFactory;
use ILIAS\Export\ExportHandler\Repository\Wrapper\IRSS\Factory as ilExportHandlerRepositoryIRSSWrapperFactory;
use ILIAS\Filesystem\Filesystems;
use ILIAS\ResourceStorage\Services as ResourcesStorageService;

class Factory implements ilExportHandlerRepositoryWrapperFactoryInterface
{
    protected ilExportHandlerFactoryInterface $export_handler;
    protected ResourcesStorageService $irss;
    protected ilDBInterface $db;
    protected Filesystems $filesystems;

    public function __construct(
        ilExportHandlerFactoryInterface $export_handler,
        ResourcesStorageService $irss,
        ilDBInterface $db,
        Filesystems $filesystems
    ) {
        $this->export_handler = $export_handler;
        $this->irss = $irss;
        $this->db = $db;
        $this->filesystems = $filesystems;
    }

    public function db(): ilExportHandlerRepositoryDBWrapperFactoryInterface
    {
        return new ilExportHandlerRepositoryDBWrapperFactory(
            $this->export_handler,
            $this->db
        );
    }

    public function irss(): ilExportHandlerRepositoryIRSSWrapperFactoryInterface
    {
        return new ilExportHandlerRepositoryIRSSWrapperFactory(
            $this->export_handler,
            $this->irss,
            $this->filesystems
        );
    }
}
