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

namespace ILIAS\MetaData\Manipulator;

use ILIAS\MetaData\Manipulator\Path\PathConditionsCheckerInterface;
use ILIAS\MetaData\Manipulator\Path\PathConditionsCollectionInterface;
use ILIAS\MetaData\Manipulator\Path\PathUtilitiesFactoryInterface;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactoryInterface;
use ILIAS\MetaData\Paths\Filters\FilterType;
use ILIAS\MetaData\Paths\Navigator\NavigatorInterface;
use ILIAS\MetaData\Paths\Steps\StepInterface;
use ILIAS\MetaData\Paths\Steps\StepToken;
use ILIAS\MetaData\Repository\RepositoryInterface;
use ILIAS\MetaData\Elements\Markers\MarkerFactoryInterface;
use ILIAS\MetaData\Paths\Navigator\NavigatorFactoryInterface;
use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Elements\Markers\MarkableInterface;
use ILIAS\MetaData\Elements\Markers\Action;
use ilMDPathException;

use function Symfony\Component\DependencyInjection\Loader\Configurator\iterator;

class Manipulator implements ManipulatorInterface
{
    protected RepositoryInterface $repository;
    protected MarkerFactoryInterface $marker_factory;
    protected NavigatorFactoryInterface $navigator_factory;
    protected PathFactoryInterface $path_factory;
    protected PathUtilitiesFactoryInterface $path_utilities_factory;

    public function __construct(
        RepositoryInterface $repository,
        MarkerFactoryInterface $marker_factory,
        NavigatorFactoryInterface $navigator_factory,
        PathFactoryInterface $path_factory,
        PathUtilitiesFactoryInterface $path_utilities_factory
    ) {
        $this->repository = $repository;
        $this->marker_factory = $marker_factory;
        $this->navigator_factory = $navigator_factory;
        $this->path_factory = $path_factory;
        $this->path_utilities_factory = $path_utilities_factory;
    }

    /**
     * @throws ilMDPathException
     */
    public function prepareDelete(
        SetInterface $set,
        PathInterface $path
    ): SetInterface {
        /**
         * @var ElementInterface[] $elements
         */
        $my_set = clone $set;
        $elements = $this->navigator_factory->navigator($path, $my_set->getRoot())->elementsAtFinalStep();
        $target_elements = [];
        foreach ($elements as $element) {
            if ($element instanceof MarkableInterface) {
                $target_elements[] = $element;
            }
        }
        $this->markElementsDelete($target_elements);
        return $my_set;
    }

    public function execute(SetInterface $set): void
    {
        $this->repository->manipulateMD($set);
    }

    /**
     * @throws ilMDPathException
     */
    public function prepareCreateOrUpdate(
        SetInterface $set,
        PathInterface $path,
        string ...$values
    ): SetInterface {
        $my_set = clone $set;
        $elements_to_update = $this->getElementsToUpdate($my_set->getRoot(), $path, ...$values);
        $remaining_values = array_slice($values, 0, count($values) - count($elements_to_update));
        $elements_to_create = $this->getElementsToCreate($my_set->getRoot(), $path, ...$remaining_values);
        $target_elements = array_merge($elements_to_update, $elements_to_create);
        $this->markElementsCreateOrUpdate($target_elements, $values);
        return $my_set;
    }

    /**
     * @throws ilMDPathException
     */
    public function prepareForceCreate(
        SetInterface $set,
        PathInterface $path,
        string ...$values
    ): SetInterface {
        $my_set = clone $set;
        $target_elements = $this->getElementsToCreate($my_set->getRoot(), $path, ...$values);
        $this->markElementsCreateOrUpdate($target_elements, $values);
        return $my_set;
    }

    protected function getElementsToUpdate(
        ElementInterface $set_root,
        PathInterface $path,
        string ...$values
    ): array {
        /**
         * @var ElementInterface[] $target_elements
         * @var NavigatorInterface[] $navigators
         */
        $path_conditions = $this->path_utilities_factory->pathConditionsCollection($path);
        $path_checker = $this->path_utilities_factory->pathConditionChecker($path_conditions);
        $target_elements = [];
        $navigators = [$this->navigator_factory->navigator($path_conditions->getPathWithoutConditions(), $set_root)];

        if (count($values) <= 0) {
            return [];
        }

        // Search for existing elements to update
        while (count($navigators) > 0) {
            $curr_navi = array_shift($navigators)->nextStep();
            $at_least_one_path_condition_met = $path_checker->atLeastOnePathConditionIsMet(
                $curr_navi->currentStep(),
                ...$curr_navi->elements()
            );

            // Complete Path: Target elements at end of path found
            if ($at_least_one_path_condition_met && $curr_navi->hasElements() && !$curr_navi->hasNextStep()) {
                $roots = iterator_to_array($path_checker->getRootsThatMeetPathCondition(
                    $curr_navi->currentStep(),
                    ...$curr_navi->elements()
                ));
                // Slice array to select only as many elements as needed
                $missing_target_count = max(0, count($values) - count($target_elements));
                $targets = array_slice($roots, 0, $missing_target_count);
                array_push($target_elements, ...$targets);
                continue;
            }

            // Path Continues: Add navigators with the elements that meet the path conditions as roots
            if ($at_least_one_path_condition_met && $curr_navi->hasElements() && $curr_navi->hasNextStep()) {
                $roots = iterator_to_array($path_checker->getRootsThatMeetPathCondition(
                    $curr_navi->currentStep(),
                    ...$curr_navi->elements()
                ));
                foreach ($roots as $root) {
                    $navigators[] = $this->navigator_factory->navigator(
                        $this->remainingPathOfNavigator($curr_navi->nextStep()),
                        $root
                    );
                }
                continue;
            }
        }

        return $target_elements;
    }

    protected function getElementsToCreate(
        ElementInterface $set_root,
        PathInterface $path,
        string ...$values
    ): array {
        /**
         * @var ElementInterface[] $target_elements
         * @var ElementInterface[] $loose_end_roots
         * @var PathInterface[] $loose_end_paths
         * @var NavigatorInterface[] $navigators
         */
        $path_conditions = $this->path_utilities_factory->pathConditionsCollection($path);
        $path_checker = $this->path_utilities_factory->pathConditionChecker($path_conditions);
        $target_elements = [];
        $loose_end_roots = [];
        $loose_end_paths = [];
        $navigators = [$this->navigator_factory->navigator($path_conditions->getPathWithoutConditions(), $set_root)];

        if (count($values) <= 0) {
            return [];
        }

        // Elements to create
        while (count($navigators) > 0) {
            $orig_navi = array_shift($navigators);
            $curr_navi = $orig_navi->nextStep();
            $at_least_one_path_condition_met = $path_checker->atLeastOnePathConditionIsMet(
                $curr_navi->currentStep(),
                ...$curr_navi->elements()
            );

            // Path Conditions not met
            if (!$at_least_one_path_condition_met) {
                $roots = iterator_to_array($orig_navi->elements());
                $paths = array_fill(0, count($roots), $this->remainingPathOfNavigator($orig_navi));
                array_push($loose_end_roots, ...$roots);
                array_push($loose_end_paths, ...$paths);
                continue;
            }

            // Incomplete Path: At end of path but target elements are missing
            if ($at_least_one_path_condition_met && !$curr_navi->hasElements() && !$curr_navi->hasNextStep()) {
                $roots = iterator_to_array($orig_navi->elements());
                $paths = array_fill(0, count($roots), $this->remainingPathOfNavigator($orig_navi->nextStep()));
                array_push($loose_end_roots, ...$roots);
                array_push($loose_end_paths, ...$paths);
                continue;
            }

            // Incomplete Path: Complete the paths as needed
            if ($at_least_one_path_condition_met && !$curr_navi->hasElements() && $curr_navi->hasNextStep()) {
                $roots = iterator_to_array($path_checker->getRootsThatMeetPathCondition(
                    $curr_navi->currentStep(),
                    ...$curr_navi->elements()
                ));
                $paths = array_fill(0, count($roots), $this->remainingPathOfNavigator($curr_navi->nextStep()));
                array_push($loose_end_roots, ...$roots);
                array_push($loose_end_paths, ...$paths);
                continue;
            }

            // Path Continues: Add navigators with the elements that meet the path conditions as roots
            if ($at_least_one_path_condition_met && $curr_navi->hasElements() && $curr_navi->hasNextStep()) {
                $roots = iterator_to_array($path_checker->getRootsThatMeetPathCondition(
                    $curr_navi->currentStep(),
                    ...$curr_navi->elements()
                ));
                foreach ($roots as $root) {
                    $navigators[] = $this->navigator_factory->navigator(
                        $this->remainingPathOfNavigator($curr_navi->nextStep()),
                        $root
                    );
                }
                continue;
            }
        }

        // Create the path if an incomplete path was found
        $missing_target_count = max(0, count($values) - count($target_elements));
        if (count($loose_end_roots) > 0 && $missing_target_count > 0) {
            $target_elements[] = $this->insertPathElementsAsScaffolds(
                array_shift($loose_end_paths),
                array_shift($loose_end_roots),
                $path_conditions
            );
        }

        // Move Navigator to path end
        $navigator = $this->getLastNavigatorWithElements(
            $path_conditions->getPathWithoutConditions(),
            $set_root
        );

        // Move Navigator backwards to insert point: parent of first non unique element
        $navigator = $this->moveNavigatorBackwardsToFirstNonUnique($navigator);
        if ($navigator->hasPreviousStep()) {
            $navigator = $navigator->previousStep();
        }

        // Add targets of loose ends
        $missing_target_count = max(0, count($values) - count($target_elements));
        $loose_end_paths = array_slice($loose_end_paths, 0, $missing_target_count);
        $loose_end_roots = array_slice($loose_end_roots, 0, $missing_target_count);
        array_push($target_elements, ...$this->createTargetElements($loose_end_roots, $loose_end_paths, $path_conditions));

        // Add new targets
        $missing_target_count = max(0, count($values) - count($target_elements));
        $remaining_path = $this->remainingPathOfNavigator($navigator->nextStep());
        $root = $navigator->lastElement();
        $target_paths = array_fill(0, $missing_target_count, $remaining_path);
        $target_roots = array_fill(0, $missing_target_count, $root);
        array_push($target_elements, ...$this->createTargetElements($target_roots, $target_paths, $path_conditions));

        return $target_elements;
    }

    /**
     * @throws ilMDPathException
     */
    protected function moveNavigatorBackwardsToFirstNonUnique(NavigatorInterface $navigator): NavigatorInterface
    {
        while ($navigator->hasPreviousStep() && $navigator->lastElement()->getDefinition()->unique()) {
            $navigator = $navigator->previousStep();
        }
        return $navigator;
    }

    protected function remainingPathOfNavigator(NavigatorInterface $navigator): PathInterface
    {
        $builder = $this->path_factory->custom()
            ->withRelative(true)
            ->withLeadsToExactlyOneElement(false);
        while (!is_null($navigator)) {
            if (is_null($navigator->currentStep())) {
                $navigator = $navigator->nextStep();
                continue;
            }
            $builder = $builder->withNextStepFromStep($navigator->currentStep(), false);
            $navigator = $navigator->nextStep();
        }
        return $builder->get();
    }

    protected function getLastNavigatorWithElements(
        PathInterface $path,
        ElementInterface $root
    ): NavigatorInterface {
        $navigator = $this->navigator_factory->navigator($path, $root);
        while ($navigator->hasNextStep() && $navigator->nextStep()->hasElements()) {
            $navigator = $navigator->nextStep();
        }
        return $navigator;
    }

    /**
     * @throws ilMDPathException
     */
    protected function insertPathElementsAsScaffolds(
        PathInterface $path,
        ElementInterface $root,
        PathConditionsCollectionInterface $path_conditions_collection
    ): ElementInterface {
        $navigator = $this->navigator_factory->navigator($path, $root);
        $navigator = $navigator->nextStep();
        $scaffold = $root;
        while (!is_null($navigator)) {
            $scaffold = $this->addAndMarkScaffoldByStep($scaffold, $navigator->currentStep());
            if (is_null($scaffold)) {
                throw new ilMDPathException(
                    'Cannot create scaffold at step: ' . $navigator->currentStep()->name() . ', invalid path.'
                );
            }
            $condition_path = $path_conditions_collection->getConditionPathByStepName($navigator->currentStep()->name());
            if ($condition_path->steps()->valid()) {
                $this->insertConditionPathElementsAsScaffolds($condition_path, $scaffold);
            }
            $navigator = $navigator->nextStep();
        }
        return $scaffold;
    }

    /**
     * @param ElementInterface[] $roots
     * @param PathInterface[] $paths
     * @return ElementInterface[]
     * @throws ilMDPathException
     */
    protected function createTargetElements(
        array $roots,
        array $paths,
        PathConditionsCollectionInterface $path_conditions
    ): array {
        $target_elements = [];
        while (0 < count($paths)) {
            $target_elements[] = $this->insertPathElementsAsScaffolds(
                array_shift($paths),
                array_shift($roots),
                $path_conditions
            );
        }
        return $target_elements;
    }

    /**
     * @throws ilMDPathException
     */
    protected function insertConditionPathElementsAsScaffolds(
        PathInterface $condition_path,
        ElementInterface $root
    ): void {
        $navigator = $this->navigator_factory->navigator($condition_path, $root);
        $navigator = $navigator->nextStep();
        $scaffold = $root;
        while (!is_null($navigator)) {
            $scaffold = $this->addAndMarkScaffoldByStep($scaffold, $navigator->currentStep());
            if (is_null($scaffold)) {
                throw new ilMDPathException('Cannot create scaffold, invalid path.');
            }
            $navigator = $navigator->nextStep();
        }
    }

    /**
     * also returns the added scaffold, if valid
     */
    protected function addAndMarkScaffoldByStep(
        ElementInterface $element,
        StepInterface $step
    ): ?ElementInterface {
        if ($step->name() === StepToken::SUPER) {
            return $element->getSuperElement();
        }
        $scaffold = $element->addScaffoldToSubElements(
            $this->repository->scaffolds(),
            $step->name()
        );
        if (!isset($scaffold)) {
            return null;
        }
        $data = '';
        foreach ($step->filters() as $filter) {
            if ($filter->type() === FilterType::DATA) {
                $data = $filter->values()->current();
                break;
            }
        }
        $scaffold->mark(
            $this->marker_factory,
            Action::CREATE_OR_UPDATE,
            $data
        );
        return $scaffold;
    }

    /**
     * @param ElementInterface[] $target_elements
     * @param array $values
     */
    protected function markElementsCreateOrUpdate(array $target_elements, array $values): void
    {
        array_map(function (ElementInterface $element, ?string $value) {
            $element->mark($this->marker_factory, Action::CREATE_OR_UPDATE, $value);
        }, $target_elements, $values);
    }

    protected function markElementsDelete(array $target_elements): void
    {
        array_map(function (ElementInterface $element) {
            $element->mark($this->marker_factory, Action::DELETE);
        }, $target_elements);
    }
}
