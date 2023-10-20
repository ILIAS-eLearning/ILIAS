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

namespace ILIAS\MetaData\Manipulator\Path;

use ILIAS\MetaData\Paths\Services\Services as PathServices;
use ILIAS\MetaData\Paths\PathInterface;

class PathUtilitiesFactory implements PathUtilitiesFactoryInterface
{
    protected PathServices $path_services;

    public function __construct(
        PathServices $path_services,
    ) {
        $this->path_services = $path_services;
    }

    public function pathConditionChecker(
        PathConditionsCollectionInterface $path_conditions_collection
    ): PathConditionsCheckerInterface {
        return new PathConditionsChecker(
            $path_conditions_collection,
            $this->path_services->navigatorFactory()
        );
    }

    public function pathConditionsCollection(
        PathInterface $path
    ): PathConditionsCollectionInterface {
        return new PathConditionsCollection(
            $this->path_services->pathFactory(),
            $path
        );
    }
}
