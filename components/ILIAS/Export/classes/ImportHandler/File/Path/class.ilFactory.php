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

namespace ILIAS\Export\ImportHandler\File\Path;

use ilLogger;
use ILIAS\Export\ImportHandler\File\Path\Comparison\ilHandler as ilFilePathComparison;
use ILIAS\Export\ImportHandler\File\Path\Comparison\Operator as ilFilePathComparisonOperator;
use ILIAS\Export\ImportHandler\File\Path\ilHandler as ilFilePathHandler;
use ILIAS\Export\ImportHandler\File\Path\Node\ilFactory as ilFilePathNodeFactory;
use ILIAS\Export\ImportHandler\I\File\Path\Comparison\ilHandlerInterface as ilFilePathComparisonInterface;
use ILIAS\Export\ImportHandler\I\File\Path\ilFactoryInterface as ilFilePAthFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\Path\ilHandlerInterface as ilFilePathHandlerInterface;
use ILIAS\Export\ImportHandler\I\File\Path\Node\ilFactoryInterface as ilFilePathNodeFactoryInterface;

class ilFactory implements ilFilePathFactoryInterface
{
    protected ilLogger $logger;

    public function __construct(ilLogger $logger)
    {
        $this->logger = $logger;
    }

    public function handler(): ilFilePathHandlerInterface
    {
        return new ilFilePathHandler();
    }

    public function node(): ilFilePathNodeFactoryInterface
    {
        return new ilFilePathNodeFactory($this->logger);
    }

    public function comparison(ilFilePathComparisonOperator $operator, string $value): ilFilePathComparisonInterface
    {
        return new ilFilePathComparison($operator, $value);
    }
}
