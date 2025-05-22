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

namespace ILIAS\Export\ImportHandler\SchemaFolder\Info;

use ILIAS\Export\ImportHandler\I\SchemaFolder\Info\CollectionInterface as SchemaInfoCollectionInterface;
use ILIAS\Export\ImportHandler\I\SchemaFolder\Info\FactoryInterface as SchemaInfoFactoryInterface;
use ILIAS\Export\ImportHandler\I\SchemaFolder\Info\HandlerInterface as SchemaInfoInterface;
use ILIAS\Export\ImportHandler\SchemaFolder\Info\Collection as SchemaInfoCollection;
use ILIAS\Export\ImportHandler\SchemaFolder\Info\Handler as SchemaInfo;
use ilLogger;

class Factory implements SchemaInfoFactoryInterface
{
    protected ilLogger $logger;

    public function __construct(
        ilLogger $logger
    ) {
        $this->logger = $logger;
    }

    public function handler(): SchemaInfoInterface
    {
        return new SchemaInfo();
    }

    public function collection(): SchemaInfoCollectionInterface
    {
        return new SchemaInfoCollection(
            $this->logger
        );
    }
}
