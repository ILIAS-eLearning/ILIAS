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

use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Paths\Navigator\NavigatorFactoryInterface;
use ILIAS\MetaData\Paths\Navigator\NavigatorInterface;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Paths\Steps\StepInterface;

class PathConditionsChecker implements PathConditionsCheckerInterface
{
    protected PathConditionsCollectionInterface $path_conditions_collection;
    protected NavigatorFactoryInterface $navigator_factory;

    public function __construct(
        PathConditionsCollectionInterface $path_conditions_collection,
        NavigatorFactoryInterface $navigator_factory
    ) {
        $this->path_conditions_collection = $path_conditions_collection;
        $this->navigator_factory = $navigator_factory;
    }

    public function getRootsThatMeetPathCondition(StepInterface $step, ElementInterface ...$roots): \Generator
    {
        /**
         * @var ElementInterface[] $elements
         */
        $elements = [];
        foreach ($roots as $root) {
            if ($this->isPathConditionMet($step, $root)) {
                $elements[] = $root;
            }
        }
        yield from $elements;
    }

    public function allPathConditionsAreMet(StepInterface $step, ElementInterface ...$roots): bool
    {
        foreach ($roots as $root) {
            if (!$this->isPathConditionMet($step, $root)) {
                return false;
            }
        }
        return true;
    }

    public function atLeastOnePathConditionIsMet(StepInterface $step, ElementInterface ...$roots): bool
    {
        foreach ($roots as $root) {
            if ($this->isPathConditionMet($step, $root)) {
                return true;
            }
        }
        return false;
    }

    public function isPathConditionMet(StepInterface $step, ElementInterface $root): bool
    {
        $navigator = $this->navigator_factory->navigator(
            $condition_path = $this->path_conditions_collection->getConditionPathByStepName($step->name()),
            $root
        );
        while (!is_null($navigator)) {
            if (!$navigator->hasElements()) {
                return false;
            }
            $navigator = $navigator->nextStep();
        }
        return true;
    }
}
