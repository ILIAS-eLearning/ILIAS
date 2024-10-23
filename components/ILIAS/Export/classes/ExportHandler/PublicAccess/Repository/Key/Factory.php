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

namespace ILIAS\Export\ExportHandler\PublicAccess\Repository\Key;

use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Key\CollectionInterface as ilExportHandlerPublicAccessRepositoryKeyCollectionInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Key\FactoryInterface as ilExportHandlerPublicAccessRepositoryKeyFactoryInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Key\HandlerInterface as ilExportHandlerPublicAccessRepositoryKeyInterface;
use ILIAS\Export\ExportHandler\I\Wrapper\DataFactory\HandlerInterface as ilExportHandlerDataFactoryWrapperInterface;
use ILIAS\Export\ExportHandler\PublicAccess\Repository\Key\Collection as ilExportHandlerPublicAccessRepositoryKeyCollection;
use ILIAS\Export\ExportHandler\PublicAccess\Repository\Key\Handler as ilExportHandlerPublicAccessRepositoryKey;

class Factory implements ilExportHandlerPublicAccessRepositoryKeyFactoryInterface
{
    protected ilExportHandlerDataFactoryWrapperInterface $data_factory_wrapper;

    public function __construct(
        ilExportHandlerDataFactoryWrapperInterface $data_factory_wrapper
    ) {
        $this->data_factory_wrapper = $data_factory_wrapper;
    }

    public function handler(): ilExportHandlerPublicAccessRepositoryKeyInterface
    {
        return new ilExportHandlerPublicAccessRepositoryKey(
            $this->data_factory_wrapper
        );
    }

    public function collection(): ilExportHandlerPublicAccessRepositoryKeyCollectionInterface
    {
        return new ilExportHandlerPublicAccessRepositoryKeyCollection();
    }
}
