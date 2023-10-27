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

namespace ImportHandler\File\Path;

use ilLogger;
use ImportHandler\File\Path\ComparisonOperator as ilFilePathComparisonOperator;
use ImportHandler\File\Path\ilComparison as ilFilePathComparison;
use ImportHandler\File\Path\ilHandler as ilFilePathHandler;
use ImportHandler\File\Path\Node\ilFactory as ilFilePathNodeFactory;
use ImportHandler\I\File\Path\Node\ilFactoryInterface as ilFilePathNodeFactoryInterface;
use ImportHandler\I\File\Path\ilComparisonInterface as ilFilePathComparisonInterface;
use ImportHandler\I\File\Path\ilFactoryInterface as ilFilePAthFactoryInterface;
use ImportHandler\I\File\Path\ilHandlerInterface as ilFilePathHandlerInterface;

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
