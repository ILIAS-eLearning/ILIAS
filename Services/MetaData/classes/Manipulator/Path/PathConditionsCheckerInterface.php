<?php

namespace ILIAS\MetaData\Manipulator\Path;

use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Paths\Steps\StepInterface;

interface PathConditionsCheckerInterface
{
    public function getRootsThatMeetPathCondition(StepInterface $step, ElementInterface ...$roots): \Generator;

    public function allPathConditionsAreMet(StepInterface $step, ElementInterface ...$roots): bool;

    public function atLeastOnePathConditionIsMet(StepInterface $step, ElementInterface ...$roots): bool;

    public function isPathConditionMet(StepInterface $step, ElementInterface $root): bool;
}
