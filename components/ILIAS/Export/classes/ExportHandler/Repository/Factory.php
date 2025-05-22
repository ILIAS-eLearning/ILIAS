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

namespace ILIAS\Export\ExportHandler\Repository;

use ilDBInterface;
use ILIAS\Export\ExportHandler\I\FactoryInterface as ilExportHandlerFactoryInterface;
use ILIAS\Export\ExportHandler\I\Repository\Element\FactoryInterface as ilExportHandlerRepositoryElementFactoryInterface;
use ILIAS\Export\ExportHandler\I\Repository\FactoryInterface as ilExportHandlerRepositoryFactoryInterface;
use ILIAS\Export\ExportHandler\I\Repository\HandlerInterface as ilExportHandlerRepositoryInterface;
use ILIAS\Export\ExportHandler\I\Repository\Key\FactoryInterface as ilExportHandlerRepositoryKeyFactoryInterface;
use ILIAS\Export\ExportHandler\I\Repository\Stakeholder\FactoryInterface as ilExportHandlerRepositoryStakeholderFactoryInterface;
use ILIAS\Export\ExportHandler\I\Repository\Values\FactoryInterface as ilExportHandlerRepositoryValuesFactoryInterface;
use ILIAS\Export\ExportHandler\I\Repository\Wrapper\FactoryInterface as ilExportHandlerRepositoryWrapperFactoryInterface;
use ILIAS\Export\ExportHandler\Repository\Element\Factory as ilExportHandlerRepositoryElementFactory;
use ILIAS\Export\ExportHandler\Repository\Handler as ilExportHandlerRepository;
use ILIAS\Export\ExportHandler\Repository\Key\Factory as ilExportHandlerRepositoryKeyFactory;
use ILIAS\Export\ExportHandler\Repository\Stakeholder\Factory as ilExportHandlerRepositoryStakeholderFactory;
use ILIAS\Export\ExportHandler\Repository\Values\Factory as ilExportHandlerRepositoryValuesFactory;
use ILIAS\Export\ExportHandler\Repository\Wrapper\Factory as ilExportHandlerRepositoryWrapperFactory;
use ILIAS\Filesystem\Filesystems;
use ILIAS\ResourceStorage\Services as ResourcesStorageService;

class Factory implements ilExportHandlerRepositoryFactoryInterface
{
    protected ilExportHandlerFactoryInterface $export_handler;
    protected ResourcesStorageService $irss;
    protected ilDBInterface $db;
    protected Filesystems $filesystems;

    public function __construct(
        ilExportHandlerFactoryInterface $export_handler,
        ilDBInterface $db,
        ResourcesStorageService $irss,
        Filesystems $filesystems
    ) {
        $this->export_handler = $export_handler;
        $this->db = $db;
        $this->irss = $irss;
        $this->filesystems = $filesystems;
    }

    public function handler(): ilExportHandlerRepositoryInterface
    {
        return new ilExportHandlerRepository(
            $this->export_handler->repository()->key(),
            $this->export_handler->repository()->values(),
            $this->export_handler->repository()->element(),
            $this->export_handler->repository()->wrapper()->db()->handler(),
            $this->export_handler->repository()->wrapper()->irss()->handler()
        );
    }

    public function element(): ilExportHandlerRepositoryElementFactoryInterface
    {
        return new ilExportHandlerRepositoryElementFactory(
            $this->export_handler,
            $this->irss
        );
    }

    public function stakeholder(): ilExportHandlerRepositoryStakeholderFactoryInterface
    {
        return new ilExportHandlerRepositoryStakeholderFactory();
    }

    public function key(): ilExportHandlerRepositoryKeyFactoryInterface
    {
        return new ilExportHandlerRepositoryKeyFactory(
            $this->export_handler
        );
    }

    public function values(): ilExportHandlerRepositoryValuesFactoryInterface
    {
        return new ilExportHandlerRepositoryValuesFactory(
            $this->export_handler
        );
    }

    public function wrapper(): ilExportHandlerRepositoryWrapperFactoryInterface
    {
        return new ilExportHandlerRepositoryWrapperFactory(
            $this->export_handler,
            $this->irss,
            $this->db,
            $this->filesystems
        );
    }
}
