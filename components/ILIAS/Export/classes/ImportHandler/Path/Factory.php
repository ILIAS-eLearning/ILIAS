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

use ILIAS\Export\ImportHandler\I\Path\Comparison\FactoryInterface as PathComparisonFactoryInterface;
use ILIAS\Export\ImportHandler\I\Path\FactoryInterface as FilePAthFactoryInterface;
use ILIAS\Export\ImportHandler\I\Path\HandlerInterface as FilePathHandlerInterface;
use ILIAS\Export\ImportHandler\I\Path\Node\FactoryInterface as PathNodeFactoryInterface;
use ILIAS\Export\ImportHandler\Path\Comparison\Factory as PathComparisonFactory;
use ILIAS\Export\ImportHandler\Path\Handler as Path;
use ILIAS\Export\ImportHandler\Path\Node\Factory as PathNodeFactory;
use ilLogger;

class Factory implements FilePathFactoryInterface
{
    protected ilLogger $logger;

    public function __construct(
        ilLogger $logger
    ) {
        $this->logger = $logger;
    }

    public function handler(): FilePathHandlerInterface
    {
        return new Path();
    }

    public function node(): PathNodeFactoryInterface
    {
        return new PathNodeFactory(
            $this->logger
        );
    }

    public function comparison(): PathComparisonFactoryInterface
    {
        return new PathComparisonFactory();
    }
}
