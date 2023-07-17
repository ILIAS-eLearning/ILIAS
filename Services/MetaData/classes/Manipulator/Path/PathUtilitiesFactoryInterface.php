<?php

namespace ILIAS\MetaData\Manipulator\Path;

use ILIAS\MetaData\Paths\PathInterface;

interface PathUtilitiesFactoryInterface
{
    public function pathConditionChecker(
        PathConditionsCollectionInterface $path_conditions_collection
    ): PathConditionsCheckerInterface;

    public function pathConditionsCollection(PathInterface $path): PathConditionsCollectionInterface ;
}
