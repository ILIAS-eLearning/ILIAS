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

namespace ILIAS\Export\ImportHandler\Path;

use ILIAS\Export\ImportHandler\I\Path\Comparison\FactoryInterface as ilImportHandlerPathComparisonFactoryInterface;
use ILIAS\Export\ImportHandler\I\Path\FactoryInterface as ilFilePAthFactoryInterface;
use ILIAS\Export\ImportHandler\I\Path\HandlerInterface as ilFilePathHandlerInterface;
use ILIAS\Export\ImportHandler\I\Path\Node\FactoryInterface as ilImportHandlerPathNodeFactoryInterface;
use ILIAS\Export\ImportHandler\Path\Comparison\Factory as ilImportHandlerPathComparisonFactory;
use ILIAS\Export\ImportHandler\Path\Handler as ilImportHandlerPath;
use ILIAS\Export\ImportHandler\Path\Node\Factory as ilImportHandlerPathNodeFactory;
use ilLogger;

class Factory implements ilFilePathFactoryInterface
{
    protected ilLogger $logger;

    public function __construct(
        ilLogger $logger
    ) {
        $this->logger = $logger;
    }

    public function handler(): ilFilePathHandlerInterface
    {
        return new ilImportHandlerPath();
    }

    public function node(): ilImportHandlerPathNodeFactoryInterface
    {
        return new ilImportHandlerPathNodeFactory(
            $this->logger
        );
    }

    public function comparison(): ilImportHandlerPathComparisonFactoryInterface
    {
        return new ilImportHandlerPathComparisonFactory();
    }
}
