<?php

namespace ILIAS\MetaData\Manipulator\Path;

use ILIAS\MetaData\Paths\PathInterface;

interface PathConditionsCollectionInterface
{
    public function getConditionPathByStepName(string $name): PathInterface;

    public function getPathWithoutConditions(): PathInterface;
}
