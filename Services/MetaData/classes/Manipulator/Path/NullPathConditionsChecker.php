<?php

namespace ILIAS\MetaData\Manipulator\Path;

use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Paths\Steps\StepInterface;

class NullPathConditionsChecker implements PathConditionsCheckerInterface
{
    public function getRootsThatMeetPathCondition(StepInterface $step, ElementInterface ...$roots): \Generator
    {
        yield from [];
    }

    public function allPathConditionsAreMet(StepInterface $step, ElementInterface ...$roots): bool
    {
        return false;
    }

    public function atLeastOnePathConditionIsMet(StepInterface $step, ElementInterface ...$roots): bool
    {
        return false;
    }

    public function isPathConditionMet(StepInterface $step, ElementInterface $root): bool
    {
        return false;
    }
}
