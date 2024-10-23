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

namespace ILIAS\Export\ImportHandler\Schema\Info;

use ILIAS\Export\ImportHandler\I\Schema\Info\CollectionInterface as ilImportHandlerSchemaInfoCollectionInterface;
use ILIAS\Export\ImportHandler\I\Schema\Info\FactoryInterface as ilImportHandlerSchemaInfoFactoryInterface;
use ILIAS\Export\ImportHandler\I\Schema\Info\HandlerInterface as ilImportHandlerSchemaInfoInterface;
use ILIAS\Export\ImportHandler\Schema\Info\Collection as ilImportHandlerSchemaInfoCollection;
use ILIAS\Export\ImportHandler\Schema\Info\Handler as ilImportHandlerSchemaInfo;
use ilLogger;

class Factory implements ilImportHandlerSchemaInfoFactoryInterface
{
    protected ilLogger $logger;

    public function __construct(
        ilLogger $logger
    ) {
        $this->logger = $logger;
    }

    public function handler(): ilImportHandlerSchemaInfoInterface
    {
        return new ilImportHandlerSchemaInfo();
    }

    public function collection(): ilImportHandlerSchemaInfoCollectionInterface
    {
        return new ilImportHandlerSchemaInfoCollection(
            $this->logger
        );
    }
}
