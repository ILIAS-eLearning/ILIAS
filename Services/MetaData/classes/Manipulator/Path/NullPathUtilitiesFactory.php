<?php

namespace ILIAS\MetaData\Manipulator\Path;

use ILIAS\MetaData\Paths\PathInterface;

class NullPathUtilitiesFactory implements PathUtilitiesFactoryInterface
{
    public function pathConditionChecker(PathConditionsCollectionInterface $path_conditions_collection): PathConditionsCheckerInterface
    {
        return new NullPathConditionsChecker();
    }

    public function pathConditionsCollection(PathInterface $path): PathConditionsCollectionInterface
    {
        return new NullPathConditionsCollection();
    }

    public function navigatorManager(): NavigatorManagerInterface
    {
        return new NullNavigatorManager();
    }
}
