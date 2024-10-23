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

namespace ILIAS\Export\ExportHandler\PublicAccess\Repository;

use ilDBInterface;
use ILIAS\Export\ExportHandler\I\FactoryInterface as ilExportHandlerFactoryInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Element\FactoryInterface as ilExportHandlerPublicAccessRepositoryElementFactoryInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\FactoryInterface as ilExportHandlerPublicAccessRepositoryFactoryInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\HandlerInterface as ilExportHandlerPublicAccessRepositoryInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Key\FactoryInterface as ilExportHandlerPublicAccessRepositoryKeyFactoryInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Values\FactoryInterface as ilExportHandlerPublicAccessRepositoryValuesFactoryInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Wrapper\FactoryInterface as ilExportHandlerPublicAccessRepositoryWrapperFactoryInterface;
use ILIAS\Export\ExportHandler\PublicAccess\Repository\Element\Factory as ilExportHandlerPublicAccessRepositoryElementFactory;
use ILIAS\Export\ExportHandler\PublicAccess\Repository\Handler as ilExportHandlerPublicAccessRepository;
use ILIAS\Export\ExportHandler\PublicAccess\Repository\Key\Factory as ilExportHandlerPublicAccessRepositoryKeyFactory;
use ILIAS\Export\ExportHandler\PublicAccess\Repository\Values\Factory as ilExportHandlerPublicAccessRepositoryValuesFactory;
use ILIAS\Export\ExportHandler\PublicAccess\Repository\Wrapper\Factory as ilExportHandlerPublicAccessRepositoryWrapperFactory;

class Factory implements ilExportHandlerPublicAccessRepositoryFactoryInterface
{
    protected ilExportHandlerFactoryInterface $export_handler;
    protected ilDBInterface $db;

    public function __construct(
        ilExportHandlerFactoryInterface $export_handler,
        ilDBInterface $db
    ) {
        $this->export_handler = $export_handler;
        $this->db = $db;
    }

    public function element(): ilExportHandlerPublicAccessRepositoryElementFactoryInterface
    {
        return new ilExportHandlerPublicAccessRepositoryElementFactory(
            $this->export_handler
        );
    }

    public function handler(): ilExportHandlerPublicAccessRepositoryInterface
    {
        return new ilExportHandlerPublicAccessRepository(
            $this->export_handler->publicAccess()->repository()->wrapper()->db()->handler(),
            $this->export_handler->publicAccess()->repository()->key()
        );
    }

    public function key(): ilExportHandlerPublicAccessRepositoryKeyFactoryInterface
    {
        return new ilExportHandlerPublicAccessRepositoryKeyFactory(
            $this->export_handler->wrapper()->dataFactory()->handler()
        );
    }

    public function values(): ilExportHandlerPublicAccessRepositoryValuesFactoryInterface
    {
        return new ilExportHandlerPublicAccessRepositoryValuesFactory(
            $this->export_handler
        );
    }

    public function wrapper(): ilExportHandlerPublicAccessRepositoryWrapperFactoryInterface
    {
        return new ilExportHandlerPublicAccessRepositoryWrapperFactory(
            $this->export_handler,
            $this->db
        );
    }
}
