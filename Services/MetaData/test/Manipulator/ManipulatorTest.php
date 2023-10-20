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

use ILIAS\MetaData\Elements\Data\DataInterface;
use ILIAS\MetaData\Elements\Data\NullData;
use ILIAS\MetaData\Elements\Data\Type;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Elements\Markers\Action;
use ILIAS\MetaData\Elements\Markers\MarkerFactoryInterface;
use ILIAS\MetaData\Elements\Markers\MarkerInterface;
use ILIAS\MetaData\Elements\Markers\NullMarker;
use ILIAS\MetaData\Elements\Markers\NullMarkerFactory;
use ILIAS\MetaData\Elements\NoID;
use ILIAS\MetaData\Elements\NullElement;
use ILIAS\MetaData\Elements\NullSet;
use ILIAS\MetaData\Elements\RessourceID\NullRessourceID;
use ILIAS\MetaData\Elements\RessourceID\RessourceIDInterface;
use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\Manipulator\Path\NullPathConditionsChecker;
use ILIAS\MetaData\Manipulator\Path\NullPathConditionsCollection;
use ILIAS\MetaData\Manipulator\Path\NullPathUtilitiesFactory;
use ILIAS\MetaData\Manipulator\Path\PathConditionsCheckerInterface;
use ILIAS\MetaData\Manipulator\Path\PathConditionsCollectionInterface;
use ILIAS\MetaData\Manipulator\Path\PathUtilitiesFactoryInterface;
use ILIAS\MetaData\Paths\BuilderInterface;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactoryInterface;
use ILIAS\MetaData\Paths\Filters\FilterInterface;
use ILIAS\MetaData\Paths\Filters\FilterType;
use ILIAS\MetaData\Paths\Filters\NullFilter;
use ILIAS\MetaData\Paths\Navigator\NavigatorFactoryInterface;
use ILIAS\MetaData\Paths\Navigator\NavigatorInterface;
use ILIAS\MetaData\Paths\Navigator\NullNavigator;
use ILIAS\MetaData\Paths\Navigator\NullNavigatorFactory;
use ILIAS\MetaData\Paths\NullBuilder;
use ILIAS\MetaData\Paths\NullPath;
use ILIAS\MetaData\Paths\NullFactory;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Paths\Steps\NullStep;
use ILIAS\MetaData\Paths\Steps\StepInterface;
use ILIAS\MetaData\Repository\NullRepository;
use ILIAS\MetaData\Repository\RepositoryInterface;
use ILIAS\MetaData\Repository\Utilities\NullScaffoldProvider;
use ILIAS\MetaData\Repository\Utilities\ScaffoldProviderInterface;
use ILIAS\MetaData\Paths\Steps\StepToken;
use ILIAS\MetaData\Structure\Definitions\DefinitionInterface;
use ILIAS\MetaData\Structure\Definitions\NullDefinition;
use ilMDPathException;
use PHPUnit\Framework\TestCase;

class ManipulatorTest extends TestCase
{
    protected function getSetMock(ElementInterface $set_root): SetInterface
    {
        return new class ($set_root) extends NullSet {
            public function __construct(protected ElementInterface $set_root)
            {
            }

            public function getRessourceID(): RessourceIDInterface
            {
                return new NullRessourceID();
            }

            public function getRoot(): ElementInterface
            {
                return $this->set_root;
            }
        };
    }

    public function getScaffoldProviderMock(): ScaffoldProviderInterface
    {
        return new class () extends NullScaffoldProvider {
            public function getScaffoldsForElement(ElementInterface $element): \Generator
            {
                yield from [];
            }
        };
    }

    protected function getRepositoryMock(): RepositoryInterface
    {
        return new class ($this) extends NullRepository {
            public function __construct(protected ManipulatorTest $test)
            {
            }

            public function scaffolds(): ScaffoldProviderInterface
            {
                return $this->test->getScaffoldProviderMock();
            }
        };
    }

    public function getMarkerMock(Action $action, string $data_value = ''): MarkerInterface
    {
        return new class ($action, $data_value) extends NullMarker {
            public function __construct(protected Action $action, protected string $data_value)
            {
            }

            public function action(): Action
            {
                return $this->action;
            }

            public function dataValue(): string
            {
                return $this->data_value;
            }
        };
    }

    protected function getMarkerFactoryMock(): MarkerFactoryInterface
    {
        return new class ($this) extends NullMarkerFactory {
            public function __construct(protected ManipulatorTest $test)
            {
            }

            public function marker(Action $action, string $data_value = ''): MarkerInterface
            {
                return $this->test->getMarkerMock($action, $data_value);
            }
        };
    }

    public function getNavigatorMock(PathInterface $path, ElementInterface $start_element): NavigatorInterface
    {
        return new class ($path, $start_element) extends NullNavigator {
            /**
             * @var ElementInterface[]
             */
            protected array $my_elements;
            /**
             * @var StepInterface[];
             */
            protected array $steps;
            protected int $step_index;

            public function __construct(PathInterface $path, protected ElementInterface $start_element)
            {
                $this->my_elements = [$this->start_element];
                $this->steps = iterator_to_array($path->steps());
                $this->step_index = -1;
            }

            protected function filterElements(): void
            {
                foreach ($this->currentStep()->filters() as $filter) {
                    if ($filter->type() === FilterType::NULL) {
                        continue;
                    }
                    if ($filter->type() === FilterType::MDID) {
                        $elements = [];
                        foreach ($this->my_elements as $element) {
                            foreach ($filter->values() as $value) {
                                if ($element->getMDID() === $value) {
                                    $elements[] = $element;
                                    break;
                                }
                            }
                        }
                        $this->my_elements = $elements;
                        continue;
                    }
                    if ($filter->type() === FilterType::DATA) {
                        $elements = [];
                        foreach ($this->my_elements as $element) {
                            foreach ($filter->values() as $value) {
                                if ($element->getData()->value() === $value) {
                                    $elements[] = $element;
                                    break;
                                }
                            }
                        }
                        $this->my_elements = $elements;
                        continue;
                    }
                    if ($filter->type() === FilterType::INDEX) {
                        $elements = [];
                        foreach ($filter->values() as $value) {
                            $value = (int) $value;
                            if (0 <= $value && $value < count($this->my_elements)) {
                                $elements[] = $this->my_elements[$value];
                            }
                        }
                        $this->my_elements = $elements;
                        continue;
                    }
                }
            }

            public function elementsAtFinalStep(): \Generator
            {
                $current = clone $this;
                while ($current->hasNextStep()) {
                    $current = $current->nextStep();
                }
                yield from $current->my_elements;
            }

            public function lastElement(): ?ElementInterface
            {
                $element_count = count($this->my_elements);
                return ($element_count === 0) ? null : $this->my_elements[$element_count - 1];
            }

            public function nextStep(): ?NavigatorInterface
            {
                if (!$this->hasNextStep()) {
                    return null;
                }
                $clone = clone $this;
                $clone->my_elements = [];
                $clone->step_index = $clone->step_index + 1;
                foreach ($this->my_elements as $element) {
                    array_push($clone->my_elements, ...$element->getSubElements());
                }
                $clone->filterElements();
                return $clone;
            }

            public function currentStep(): ?StepInterface
            {
                return (0 <= $this->step_index && $this->step_index < count($this->steps)) ? $this->steps[$this->step_index] : null;
            }

            public function lastElementAtFinalStep(): ?ElementInterface
            {
                $final_elements = iterator_to_array($this->elementsAtFinalStep());
                $final_elements_count = count($final_elements);
                return $final_elements_count > 0 ? $final_elements[$final_elements_count - 1] : null;
            }

            public function previousStep(): ?NavigatorInterface
            {
                if (!$this->hasPreviousStep()) {
                    return null;
                }
                $clone = clone $this;
                $clone->my_elements = [];
                $clone->step_index = $clone->step_index - 1;
                foreach ($this->my_elements as $element) {
                    $clone->my_elements[] = $element->getSuperElement();
                }
                return $clone;
            }

            public function hasNextStep(): bool
            {
                return ($this->step_index + 1) < count($this->steps);
            }

            public function hasPreviousStep(): bool
            {
                return ($this->step_index - 1) >= -1;
            }

            public function hasElements(): bool
            {
                return count($this->my_elements) > 0;
            }

            public function elements(): \Generator
            {
                yield from $this->my_elements;
            }
        };
    }

    public function getPathConditionCollectionMock(PathInterface $path): PathConditionsCollectionInterface
    {
        /**
         * @var StepInterface[] $collected_steps
         * @var PathInterface[] $path_conditions
         */
        $path_conditions = [];
        $collected_steps = [];
        $name = '';
        foreach ($path->steps() as $step) {
            if ($step->name() === StepToken::SUPER) {
                $super = $step;
                $condition = array_pop($collected_steps);
                $target = $collected_steps[count($collected_steps) - 1];
                $condition_path_steps = [$condition];

                if (key_exists($condition->name(), $path_conditions)) {
                    // array_pop($condition_path_steps);
                    foreach ($path_conditions[$condition->name()]->steps() as $cond_step) {
                        $condition_path_steps[] = $cond_step;
                    }
                    // $condition_path_steps[] = $super;
                    unset($path_conditions[$condition->name()]);
                }

                if (key_exists($target->name(), $path_conditions)) {
                    $target_steps = [];
                    foreach ($path_conditions[$target->name()]->steps() as $cond_step) {
                        $target_steps[] = $cond_step;
                    }
                    array_unshift($condition_path_steps, ...$target_steps);
                    unset($path_conditions[$target->name()]);
                }

                $path_conditions[$target->name()] = $this->getPathMock(
                    $condition_path_steps,
                    true,
                    false
                );
                continue;
            }
            $collected_steps[] = $step;
        }

        return new class (
            $this,
            $this->getPathMock($collected_steps, false, false),
            $path_conditions
        ) extends NullPathConditionsCollection {
            public function __construct(
                protected ManipulatorTest $test,
                protected PathInterface $clear_path,
                protected array $path_conditons
            ) {
            }

            public function getPathWithoutConditions(): PathInterface
            {
                return $this->clear_path;
            }

            public function getConditionPathByStepName(string $name): PathInterface
            {
                if (key_exists($name, $this->path_conditons)) {
                    return $this->path_conditons[$name];
                }
                return $this->test->getPathMock([], true, false);
            }
        };
    }

    public function getPathConditionCheckerMock(
        PathConditionsCollectionInterface $path_conditions_collection
    ): PathConditionsCheckerInterface {
        return new class (
            $path_conditions_collection,
            $this->getNavigatorFactoryMock()
        ) extends NullPathConditionsChecker {
            public function __construct(
                protected PathConditionsCollectionInterface $path_conditions_collection,
                protected NavigatorFactoryInterface $navigator_factory
            ) {
            }

            public function isPathConditionMet(StepInterface $step, ElementInterface $root): bool
            {
                $navigator = $this->navigator_factory->navigator(
                    $this->path_conditions_collection->getConditionPathByStepName($step->name()),
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

            public function getRootsThatMeetPathCondition(StepInterface $step, ElementInterface ...$roots): \Generator
            {
                $elements = [];
                foreach ($roots as $root) {
                    if ($this->isPathConditionMet($step, $root)) {
                        $elements[] = $root;
                    }
                }
                yield from $elements;
            }
        };
    }

    public function getPathFactoryMock(): PathFactoryInterface
    {
        return new class ($this) extends NullFactory {
            public function __construct(protected ManipulatorTest $test)
            {
            }

            public function custom(): BuilderInterface
            {
                return $this->test->getPathBuilderMock();
            }
        };
    }

    public function getPathBuilderMock(): BuilderInterface
    {
        return new class ($this) extends NullBuilder {
            protected bool $is_relative;
            protected bool $leads_to_exactly_one_element;
            /**
             * @var StepInterface[]
             */
            protected array $steps;

            public function __construct(protected ManipulatorTest $test)
            {
                $this->is_relative = false;
                $this->leads_to_exactly_one_element = false;
            }

            public function withLeadsToExactlyOneElement(bool $leads_to_one): BuilderInterface
            {
                $builder = clone $this;
                $builder->leads_to_exactly_one_element = $leads_to_one;
                return $builder;
            }

            public function withRelative(bool $is_relative): BuilderInterface
            {
                $builder = clone $this;
                $builder->is_relative = $is_relative;
                return $builder;
            }

            public function withNextStepFromStep(StepInterface $next_step, bool $add_as_first = false): BuilderInterface
            {
                $builder = clone $this;
                $builder->steps[] = $next_step;
                return $builder;
            }

            public function get(): PathInterface
            {
                return $this->test->getPathMock($this->steps, $this->is_relative, $this->leads_to_exactly_one_element);
            }
        };
    }

    public function getNavigatorFactoryMock(): NavigatorFactoryInterface
    {
        return new class ($this) extends NullNavigatorFactory {
            public function __construct(protected ManipulatorTest $test)
            {
            }

            public function navigator(PathInterface $path, ElementInterface $start_element): NavigatorInterface
            {
                return $this->test->getNavigatorMock($path, $start_element);
            }
        };
    }

    protected function getPathUtilitiesFactoryMock(): PathUtilitiesFactoryInterface
    {
        return new class (
            $this
        ) extends NullPathUtilitiesFactory {
            public function __construct(
                protected ManipulatorTest $test
            ) {
            }

            public function pathConditionChecker(
                PathConditionsCollectionInterface $path_conditions_collection
            ): PathConditionsCheckerInterface {
                return $this->test->getPathConditionCheckerMock($path_conditions_collection);
            }

            public function pathConditionsCollection(PathInterface $path): PathConditionsCollectionInterface
            {
                return $this->test->getPathConditionCollectionMock($path);
            }
        };
    }

    public function getDataMock(string $value, Type $type): DataInterface
    {
        return new class ($value, $type) extends NullData {
            public function __construct(protected string $my_value, protected Type $my_type)
            {
            }

            public function value(): string
            {
                return $this->my_value;
            }

            public function type(): Type
            {
                return $this->my_type;
            }
        };
    }

    public function getDefinitionMock(
        string $name,
        Type $data_type,
        bool $is_unique
    ): DefinitionInterface {
        return new class ($name, $data_type, $is_unique) extends NullDefinition {
            public function __construct(
                protected string $my_name,
                protected Type $data_type,
                protected bool $is_unique
            ) {
            }

            public function name(): string
            {
                return $this->my_name;
            }

            public function dataType(): Type
            {
                return $this->data_type;
            }

            public function unique(): bool
            {
                return $this->is_unique;
            }
        };
    }

    public function getElementMock(
        int|NoID $mdid,
        string $value,
        Type $type,
        bool $is_unique = false
    ): ElementInterface {
        return new class ($this, $mdid, $value, $type, $is_unique) extends NullElement {
            protected ?ElementInterface $parent;
            protected ?MarkerInterface $marker;
            protected DataInterface $data;
            protected DefinitionInterface $definition;
            /**
             * @var ElementInterface[]
             */
            protected array $children;

            public function __construct(
                protected ManipulatorTest $test,
                protected int|NoID $mdid,
                string $value,
                Type $type,
                bool $is_unique
            ) {
                $this->marker = null;
                $this->parent = null;
                $this->children = [];
                $this->data = $this->test->getDataMock($value, $type);
                $this->definition = $this->test->getDefinitionMock('', $this->data->type(), $is_unique);
            }

            public function getMDID(): int|NoID
            {
                return $this->mdid;
            }

            public function addChild(ElementInterface $child): void
            {
                $child->parent = $this;
                $this->children[] = $child;
            }

            public function addChildren(ElementInterface ...$children): void
            {
                foreach ($children as $child) {
                    $this->addChild($child);
                }
            }

            public function getSubElements(): \Generator
            {
                yield from $this->children;
            }

            public function getSuperElement(): ?ElementInterface
            {
                return $this->parent;
            }

            public function addScaffoldToSubElements(ScaffoldProviderInterface $scaffold_provider, string $name): ?ElementInterface
            {
                $element = $this->test->getElementMock(NoID::SCAFFOLD, $name, Type::NULL);
                $this->addChild($element);
                return $element;
            }

            public function getData(): DataInterface
            {
                return $this->data;
            }

            public function mark(MarkerFactoryInterface $factory, Action $action, string $data_value = ''): void
            {
                $this->marker = $factory->marker($action, $data_value);
                $element = $this->parent;
                while (!is_null($element) && !$element->isMarked()) {
                    $current_action = ($element->isScaffold() && $action === Action::CREATE_OR_UPDATE) ? Action::CREATE_OR_UPDATE : Action::NEUTRAL;
                    $element->marker = $factory->marker($current_action);
                    $element = $element->parent;
                }
            }

            public function getDefinition(): DefinitionInterface
            {
                return $this->definition;
            }


            public function getMarker(): ?MarkerInterface
            {
                return $this->marker;
            }

            public function isMarked(): bool
            {
                return !is_null($this->marker);
            }
        };
    }

    /**
     * @param StepInterface[] $steps
     */
    public function getPathMock(array $steps, bool $is_relative, bool $leads_to_one): PathInterface
    {
        return new class ($steps, $is_relative, $leads_to_one) extends NullPath {
            /**
             * @param StepInterface[] $my_steps
             */
            public function __construct(
                protected array $my_steps,
                protected bool $is_relative,
                protected bool $leads_to_one
            ) {
            }

            public function leadsToExactlyOneElement(): bool
            {
                return $this->leads_to_one;
            }

            public function isRelative(): bool
            {
                return $this->is_relative;
            }

            public function steps(): \Generator
            {
                yield from $this->my_steps;
            }
        };
    }

    /**
     * @param FilterInterface[] $filter
     */
    protected function getStepMock(string|StepToken $step_name, array $filter): StepInterface
    {
        return new class ($step_name, $filter) extends NullStep {
            public function __construct(protected string|StepToken $step_name, protected array $my_filter)
            {
            }

            public function name(): string|StepToken
            {
                return $this->step_name;
            }

            public function filters(): \Generator
            {
                yield from $this->my_filter;
            }
        };
    }

    protected function getFilterMock(FilterType $filter_type, array $values): FilterInterface
    {
        return new class ($filter_type, $values) extends NullFilter {
            public function __construct(protected FilterType $filter_type, protected array $my_values)
            {
            }

            public function type(): FilterType
            {
                return $this->filter_type;
            }

            public function values(): \Generator
            {
                yield from $this->my_values;
            }
        };
    }

    protected function createExpectedValuesArray(
        int $expected_child_count,
        int|NoID $expected_id,
        string $expected_element_data_value,
        Type $expected_data_type = Type::NULL,
        ?Action $expected_marker_action = null,
        string $exptected_marker_value = '',
        array $expected_child_values = [],
    ): array {
        return [
            'expected_child_count' => $expected_child_count,
            'expected_id' => $expected_id,
            'expected_element_data_value' => $expected_element_data_value,
            'expected_data_type' => $expected_data_type,
            'expected_marker_action' => $expected_marker_action,
            'expected_marker_value' => $exptected_marker_value,
            'children' => $expected_child_values
        ];
    }

    protected function myAssertElement(
        ElementInterface $element_to_check,
        array $expected_values
    ): void {
        /**
         * @var int $expected_child_count
         * @var int|NoID $expected_id
         * @var string $expected_element_data_value
         * @var Type $expected_data_type
         * @var ?Action $expected_marker_action
         * @var string $expected_marker_value
         */
        $expected_child_count = $expected_values['expected_child_count'];
        $expected_id = $expected_values['expected_id'];
        $expected_element_data_value = $expected_values['expected_element_data_value'];
        $expected_data_type = $expected_values['expected_data_type'];
        $expected_marker_action = $expected_values['expected_marker_action'];
        $expected_marker_value = $expected_values['expected_marker_value'];
        $msg = 'Failed during check of element with data value: ' . $element_to_check->getData()->value()
            . ', and with NoID: ' . ($element_to_check->getMDID() instanceof NoID ? $element_to_check->getMDID()->value : $element_to_check->getMDID());
        if (is_null($expected_marker_action)) {
            $this->assertSame(null, $element_to_check->getMarker(), $msg);
        }
        if (!is_null($expected_marker_action)) {
            $this->assertNotSame(null, $element_to_check->getMarker(), $msg);
            $this->assertSame($expected_marker_action, $element_to_check->getMarker()->action(), $msg);
            $this->assertSame($expected_marker_value, $element_to_check->getMarker()->dataValue(), $msg);
        }
        $this->assertSame($expected_child_count, count(iterator_to_array($element_to_check->getSubElements())), $msg);
        $this->assertSame($expected_id, $element_to_check->getMDID(), $msg);
        $this->assertSame($expected_element_data_value, $element_to_check->getData()->value(), $msg);
        $this->assertSame($expected_data_type, $element_to_check->getData()->type(), $msg);
    }

    protected function myAssertTree(ElementInterface $root, array $expected_root_values)
    {
        /**
         * @var ElementInterface[] $elements
         * @var array[] $expected_values
         */
        $elements = [$root];
        $expected_values = [$expected_root_values];
        while (count($elements) > 0) {
            $this->assertSame(count($elements), count($expected_values), 'Bad test value initializion');
            $current_element = array_pop($elements);
            $current_expected_values = array_pop($expected_values);
            $this->myAssertElement($current_element, $current_expected_values);
            array_push($elements, ...$current_element->getSubElements());
            array_push($expected_values, ...$current_expected_values['children']);
        }
    }

    public function testPrepareDelete_001(): void
    {
        $manipulator = new Manipulator(
            new NullRepository(),
            $this->getMarkerFactoryMock(),
            $this->getNavigatorFactoryMock(),
            $this->getPathFactoryMock(),
            new NullPathUtilitiesFactory()
        );

        $element_root = $this->getElementMock(NoID::ROOT, 'root', Type::NULL);
        $element_general = $this->getElementMock(0, 'general', Type::NULL);
        $element_subsection_1_0 = $this->getElementMock(1, 'subsection_1', Type::NULL);
        $element_subsection_1_1 = $this->getElementMock(2, 'subsection_1', Type::NULL);
        $element_target_0 = $this->getElementMock(3, 'target', Type::STRING);
        $element_target_1 = $this->getElementMock(4, 'target', Type::STRING);
        $element_target_2 = $this->getElementMock(5, 'target', Type::STRING);

        $element_root->addChildren($element_general);
        $element_general->addChildren($element_subsection_1_0, $element_subsection_1_1);
        $element_subsection_1_0->addChildren($element_target_0);
        $element_subsection_1_1->addChildren($element_target_1, $element_target_2);

        $set = $this->getSetMock($element_root);
        $delete_path = $this->getPathMock(
            [
                $this->getStepMock('general', [
                    $this->getFilterMock(FilterType::DATA, ['general'])
                ]),
                $this->getStepMock('subsection_1', [
                    $this->getFilterMock(FilterType::DATA, ['subsection_1']),
                    $this->getFilterMock(FilterType::MDID, [2])
                ]),
                $this->getStepMock('target', [
                    $this->getFilterMock(FilterType::DATA, ['target'])
                ])
            ],
            false,
            false
        );

        try {
            $manipulator->prepareDelete($set, $delete_path);
        } catch (\ilMDPathException $e) {
            $this->fail($e->getMessage());
        }

        $expected_element_target_2 = $this->createExpectedValuesArray(
            0,
            5,
            'target',
            Type::STRING,
            Action::DELETE
        );

        $expected_element_target_1 = $this->createExpectedValuesArray(
            0,
            4,
            'target',
            Type::STRING,
            Action::DELETE
        );

        $expected_element_target_0 = $this->createExpectedValuesArray(
            0,
            3,
            'target',
            Type::STRING
        );

        $expected_element_subsection_1_1 = $this->createExpectedValuesArray(
            2,
            2,
            'subsection_1',
            Type::NULL,
            Action::NEUTRAL,
            '',
            [
                $expected_element_target_1,
                $expected_element_target_2
            ]
        );

        $expected_element_subsection_1_0 = $this->createExpectedValuesArray(
            1,
            1,
            'subsection_1',
            Type::NULL,
            null,
            '',
            [
                $expected_element_target_0
            ]
        );

        $expected_element_general = $this->createExpectedValuesArray(
            2,
            0,
            'general',
            Type::NULL,
            Action::NEUTRAL,
            '',
            [
                $expected_element_subsection_1_0,
                $expected_element_subsection_1_1
            ]
        );

        $expected_element_root = $this->createExpectedValuesArray(
            1,
            NoID::ROOT,
            'root',
            Type::NULL,
            Action::NEUTRAL,
            '',
            [
                $expected_element_general
            ]
        );

        $this->myAssertTree($element_root, $expected_element_root);
    }

    public function testPrepareDelete_002(): void
    {
        $manipulator = new Manipulator(
            new NullRepository(),
            $this->getMarkerFactoryMock(),
            $this->getNavigatorFactoryMock(),
            $this->getPathFactoryMock(),
            new NullPathUtilitiesFactory()
        );

        $element_root = $this->getElementMock(NoID::ROOT, 'root', Type::NULL);
        $element_general = $this->getElementMock(0, 'general', Type::NULL);
        $element_subsection_1_0 = $this->getElementMock(1, 'subsection_1', Type::NULL);
        $element_subsection_1_1 = $this->getElementMock(2, 'subsection_1', Type::NULL);
        $element_target = $this->getElementMock(3, 'target', Type::STRING);

        $element_root->addChildren($element_general);
        $element_general->addChildren($element_subsection_1_0, $element_subsection_1_1);
        $element_subsection_1_0->addChildren($element_target);

        $set = $this->getSetMock($element_root);
        $delete_path = $this->getPathMock(
            [
                $this->getStepMock('general', [
                    $this->getFilterMock(FilterType::DATA, ['general'])
                ]),
                $this->getStepMock('subsection_1', [
                    $this->getFilterMock(FilterType::DATA, ['subsection_1'])
                ]),
                $this->getStepMock('i_do_not_exist_one', [
                    $this->getFilterMock(FilterType::DATA, ['i_do_not_exist_one'])
                ]),
                $this->getStepMock('i_do_not_exist_two', [
                    $this->getFilterMock(FilterType::DATA, ['i_do_not_exist_two'])
                ])
            ],
            false,
            false
        );

        try {
            $manipulator->prepareDelete($set, $delete_path);
        } catch (\ilMDPathException $e) {
            $this->fail($e->getMessage());
        }

        $expected_element_target = $this->createExpectedValuesArray(
            0,
            3,
            'target',
            Type::STRING
        );

        $expected_element_subsection_1_1 = $this->createExpectedValuesArray(
            0,
            2,
            'subsection_1'
        );

        $expected_element_subsection_1_0 = $this->createExpectedValuesArray(
            1,
            1,
            'subsection_1',
            Type::NULL,
            null,
            '',
            [
                $expected_element_target
            ]
        );

        $expected_element_general = $this->createExpectedValuesArray(
            2,
            0,
            'general',
            Type::NULL,
            null,
            '',
            [
                $expected_element_subsection_1_0,
                $expected_element_subsection_1_1
            ]
        );

        $expected_element_root = $this->createExpectedValuesArray(
            1,
            NoID::ROOT,
            'root',
            Type::NULL,
            null,
            '',
            [
                $expected_element_general
            ]
        );

        $this->myAssertTree($element_root, $expected_element_root);
    }

    public function testPrepareCreateOrUpdate_001(): void
    {
        $manipulator = new Manipulator(
            $this->getRepositoryMock(),
            $this->getMarkerFactoryMock(),
            $this->getNavigatorFactoryMock(),
            $this->getPathFactoryMock(),
            $this->getPathUtilitiesFactoryMock()
        );

        $path = $this->getPathMock([
            $this->getStepMock('general', [
                $this->getFilterMock(FilterType::NULL, [])
            ])
        ], false, false);

        $element_root = $this->getElementMock(NoID::ROOT, 'root', Type::NULL, true);

        try {
            $manipulator->prepareCreateOrUpdate($this->getSetMock($element_root), $path, 'test');
        } catch (\ilMDPathException $e) {
            $this->fail($e->getMessage());
        }

        $expected_element_general = $this->createExpectedValuesArray(
            0,
            NoID::SCAFFOLD,
            'general',
            Type::NULL,
            Action::CREATE_OR_UPDATE,
            'test',
        );

        $expected_element_root = $this->createExpectedValuesArray(
            1,
            NoID::ROOT,
            'root',
            Type::NULL,
            Action::NEUTRAL,
            '',
            [
                $expected_element_general
            ]
        );

        $this->myAssertTree($element_root, $expected_element_root);
    }

    public function testPrepareCreateOrUpdate_002(): void
    {
        $manipulator = new Manipulator(
            $this->getRepositoryMock(),
            $this->getMarkerFactoryMock(),
            $this->getNavigatorFactoryMock(),
            $this->getPathFactoryMock(),
            $this->getPathUtilitiesFactoryMock()
        );

        $add_path = $this->getPathMock([
            $this->getStepMock('general', [
                $this->getFilterMock(FilterType::DATA, ['general'])
            ]),
            $this->getStepMock('tags', [
                $this->getFilterMock(FilterType::DATA, ['tags'])
            ]),
            $this->getStepMock('tag', [
                $this->getFilterMock(FilterType::DATA, ['tag']),
                $this->getFilterMock(FilterType::INDEX, [0, 1])
            ]),
        ], false, false);

        $element_root = $this->getElementMock(NoID::ROOT, 'root', Type::NULL, true);
        $element_general = $this->getElementMock(0, 'general', Type::NULL, true);
        $element_special = $this->getElementMock(1, 'special', Type::NULL, true);

        $element_root->addChildren($element_general, $element_special);

        try {
            $manipulator->prepareCreateOrUpdate($this->getSetMock($element_root), $add_path, 'test1', 'test2');
        } catch (\ilMDPathException $e) {
            $this->fail($e->getMessage());
        }

        $expected_element_tag_1 = $this->createExpectedValuesArray(
            0,
            NoID::SCAFFOLD,
            'tag',
            Type::NULL,
            Action::CREATE_OR_UPDATE,
            'test2',
        );

        $expected_element_tag_0 = $this->createExpectedValuesArray(
            0,
            NoID::SCAFFOLD,
            'tag',
            Type::NULL,
            Action::CREATE_OR_UPDATE,
            'test1',
        );

        $expected_element_tags = $this->createExpectedValuesArray(
            2,
            NoID::SCAFFOLD,
            'tags',
            Type::NULL,
            Action::CREATE_OR_UPDATE,
            'tags',
            [
                $expected_element_tag_0,
                $expected_element_tag_1
            ]
        );

        $expected_element_special = $this->createExpectedValuesArray(
            0,
            1,
            'special',
        );

        $expected_element_general = $this->createExpectedValuesArray(
            1,
            0,
            'general',
            Type::NULL,
            Action::NEUTRAL,
            '',
            [
                $expected_element_tags
            ]
        );

        $expected_element_root = $this->createExpectedValuesArray(
            2,
            NoID::ROOT,
            'root',
            Type::NULL,
            Action::NEUTRAL,
            '',
            [
                $expected_element_general,
                $expected_element_special
            ]
        );

        $this->myAssertTree($element_root, $expected_element_root);
    }

    public function testPrepareCreateOrUpdate_003(): void
    {
        $manipulator = new Manipulator(
            $this->getRepositoryMock(),
            $this->getMarkerFactoryMock(),
            $this->getNavigatorFactoryMock(),
            $this->getPathFactoryMock(),
            $this->getPathUtilitiesFactoryMock()
        );

        $add_path = $this->getPathMock([
            $this->getStepMock('general', [
                $this->getFilterMock(FilterType::DATA, ['general'])
            ]),
            $this->getStepMock('tags', [
                $this->getFilterMock(FilterType::DATA, ['tags'])
            ]),
            $this->getStepMock('tag', [
                $this->getFilterMock(FilterType::DATA, ['tag']),
                $this->getFilterMock(FilterType::INDEX, [0, 2])
            ]),
        ], false, false);

        $element_root = $this->getElementMock(NoID::ROOT, 'root', Type::NULL, true);
        $element_general = $this->getElementMock(0, 'general', Type::NULL, true);
        $element_special = $this->getElementMock(1, 'special', Type::NULL, true);
        $element_tags = $this->getElementMock(2, 'tags', Type::NULL, true);
        $element_tag_0 = $this->getElementMock(3, 'tag', Type::STRING);
        $element_tag_1 = $this->getElementMock(4, 'tag', Type::STRING);
        $element_tag_2 = $this->getElementMock(5, 'tag', Type::STRING);
        $element_tag_3 = $this->getElementMock(6, 'tag', Type::STRING);

        $element_root->addChildren($element_general, $element_special);
        $element_general->addChildren($element_tags);
        $element_tags->addChildren($element_tag_0, $element_tag_1, $element_tag_2, $element_tag_3);

        try {
            $manipulator->prepareCreateOrUpdate($this->getSetMock($element_root), $add_path, 'test1', 'test2');
        } catch (\ilMDPathException $e) {
            $this->fail($e->getMessage());
        }

        $expected_element_tag_3 = $this->createExpectedValuesArray(
            0,
            6,
            'tag',
            Type::STRING
        );

        $expected_element_tag_2 = $this->createExpectedValuesArray(
            0,
            5,
            'tag',
            Type::STRING,
            Action::CREATE_OR_UPDATE,
            'test2'
        );

        $expected_element_tag_1 = $this->createExpectedValuesArray(
            0,
            4,
            'tag',
            Type::STRING
        );

        $expected_element_tag_0 = $this->createExpectedValuesArray(
            0,
            3,
            'tag',
            Type::STRING,
            Action::CREATE_OR_UPDATE,
            'test1'
        );

        $expected_element_tags = $this->createExpectedValuesArray(
            4,
            2,
            'tags',
            Type::NULL,
            Action::NEUTRAL,
            '',
            [
                $expected_element_tag_0,
                $expected_element_tag_1,
                $expected_element_tag_2,
                $expected_element_tag_3
            ]
        );

        $expected_element_special = $this->createExpectedValuesArray(
            0,
            1,
            'special'
        );

        $expected_element_general = $this->createExpectedValuesArray(
            1,
            0,
            'general',
            Type::NULL,
            Action::NEUTRAL,
            '',
            [
                $expected_element_tags
            ]
        );

        $expected_element_root = $this->createExpectedValuesArray(
            2,
            NoID::ROOT,
            'root',
            Type::NULL,
            Action::NEUTRAL,
            '',
            [
                $expected_element_general,
                $expected_element_special
            ]
        );

        $this->myAssertTree($element_root, $expected_element_root);
    }

    public function testPrepareCreateOrUpdate_004(): void
    {
        $manipulator = new Manipulator(
            $this->getRepositoryMock(),
            $this->getMarkerFactoryMock(),
            $this->getNavigatorFactoryMock(),
            $this->getPathFactoryMock(),
            $this->getPathUtilitiesFactoryMock()
        );

        $add_path = $this->getPathMock([
            $this->getStepMock('general', [
                $this->getFilterMock(FilterType::DATA, ['general'])
            ]),
            $this->getStepMock('general_condition', [
                $this->getFilterMock(FilterType::DATA, ['general_condition'])
            ]),
            $this->getStepMock(StepToken::SUPER, []),
            $this->getStepMock('tags', [
                $this->getFilterMock(FilterType::DATA, ['tags'])
            ]),
            $this->getStepMock('tag', [
                $this->getFilterMock(FilterType::DATA, ['tag']),
                $this->getFilterMock(FilterType::INDEX, [0, 2])
            ]),
        ], false, false);

        $element_root = $this->getElementMock(NoID::ROOT, 'root', Type::NULL, true);
        $element_general = $this->getElementMock(0, 'general', Type::NULL, true);
        $element_special = $this->getElementMock(1, 'special', Type::NULL, true);
        $element_general_condition = $this->getElementMock(2, 'general_condition', Type::NULL);
        $element_tags = $this->getElementMock(3, 'tags', Type::NULL, true);
        $element_tag_0 = $this->getElementMock(4, 'tag', Type::STRING);
        $element_tag_1 = $this->getElementMock(5, 'tag', Type::STRING);
        $element_tag_2 = $this->getElementMock(6, 'tag', Type::STRING);
        $element_tag_3 = $this->getElementMock(7, 'tag', Type::STRING);

        $element_root->addChildren($element_general, $element_special);
        $element_general->addChildren($element_general_condition, $element_tags);
        $element_tags->addChildren($element_tag_0, $element_tag_1, $element_tag_2, $element_tag_3);

        try {
            $manipulator->prepareCreateOrUpdate($this->getSetMock($element_root), $add_path, 'test1', 'test2');
        } catch (\ilMDPathException $e) {
            $this->fail($e->getMessage());
        }

        $expected_element_tag_3 = $this->createExpectedValuesArray(
            0,
            7,
            'tag',
            Type::STRING
        );

        $expected_element_tag_2 = $this->createExpectedValuesArray(
            0,
            6,
            'tag',
            Type::STRING,
            Action::CREATE_OR_UPDATE,
            'test2'
        );

        $expected_element_tag_1 = $this->createExpectedValuesArray(
            0,
            5,
            'tag',
            Type::STRING
        );

        $expected_element_tag_0 = $this->createExpectedValuesArray(
            0,
            4,
            'tag',
            Type::STRING,
            Action::CREATE_OR_UPDATE,
            'test1'
        );

        $expected_element_tags = $this->createExpectedValuesArray(
            4,
            3,
            'tags',
            Type::NULL,
            Action::NEUTRAL,
            '',
            [
                $expected_element_tag_0,
                $expected_element_tag_1,
                $expected_element_tag_2,
                $expected_element_tag_3
            ]
        );

        $expected_element_general_condition = $this->createExpectedValuesArray(
            0,
            2,
            'general_condition'
        );

        $expected_element_special = $this->createExpectedValuesArray(
            0,
            1,
            'special'
        );

        $expected_element_general = $this->createExpectedValuesArray(
            2,
            0,
            'general',
            Type::NULL,
            Action::NEUTRAL,
            '',
            [
                $expected_element_general_condition,
                $expected_element_tags
            ]
        );

        $expected_element_root = $this->createExpectedValuesArray(
            2,
            NoID::ROOT,
            'root',
            Type::NULL,
            Action::NEUTRAL,
            '',
            [
                $expected_element_general,
                $expected_element_special
            ]
        );

        $this->myAssertTree($element_root, $expected_element_root);
    }

    public function testPrepareCreateOrUpdate_005(): void
    {
        $manipulator = new Manipulator(
            $this->getRepositoryMock(),
            $this->getMarkerFactoryMock(),
            $this->getNavigatorFactoryMock(),
            $this->getPathFactoryMock(),
            $this->getPathUtilitiesFactoryMock()
        );

        $add_path = $this->getPathMock([
            $this->getStepMock('general', [
                $this->getFilterMock(FilterType::DATA, ['general'])
            ]),
            $this->getStepMock('tags', [
                $this->getFilterMock(FilterType::DATA, ['tags'])
            ]),
            $this->getStepMock('tags_condition', [
                $this->getFilterMock(FilterType::DATA, ['tags_condition'])
            ]),
            $this->getStepMock(StepToken::SUPER, []),
            $this->getStepMock('tag', [
                $this->getFilterMock(FilterType::DATA, ['tag'])
            ]),
        ], false, false);

        $element_root = $this->getElementMock(NoID::ROOT, 'root', Type::NULL, true);
        $element_general = $this->getElementMock(0, 'general', Type::NULL, true);
        $element_special = $this->getElementMock(1, 'special', Type::NULL, true);
        $element_general_condition = $this->getElementMock(2, 'general_condition', Type::NULL);
        $element_tags = $this->getElementMock(3, 'tags', Type::NULL, true);
        $element_tag_0 = $this->getElementMock(4, 'tag', Type::STRING);

        $element_root->addChildren($element_general, $element_special);
        $element_general->addChildren($element_general_condition, $element_tags);
        $element_tags->addChildren($element_tag_0);

        try {
            $manipulator->prepareCreateOrUpdate(
                $this->getSetMock($element_root),
                $add_path,
                'test1',
                'test2',
                'test3'
            );
        } catch (\ilMDPathException $e) {
            $this->fail($e->getMessage());
        }

        $expected_element_tag_3 = $this->createExpectedValuesArray(
            0,
            NoID::SCAFFOLD,
            'tag',
            Type::NULL,
            Action::CREATE_OR_UPDATE,
            'test3'
        );

        $expected_element_tag_2 = $this->createExpectedValuesArray(
            0,
            NoID::SCAFFOLD,
            'tag',
            Type::NULL,
            Action::CREATE_OR_UPDATE,
            'test2'
        );

        $expected_element_tag_1 = $this->createExpectedValuesArray(
            0,
            NoID::SCAFFOLD,
            'tag',
            Type::NULL,
            Action::CREATE_OR_UPDATE,
            'test1'
        );

        $expected_element_tag_0 = $this->createExpectedValuesArray(
            0,
            4,
            'tag',
            Type::STRING
        );

        $expected_element_tags_condition = $this->createExpectedValuesArray(
            0,
            NoID::SCAFFOLD,
            'tags_condition',
            Type::NULL,
            Action::CREATE_OR_UPDATE,
            'tags_condition'
        );

        $expected_element_tags_created = $this->createExpectedValuesArray(
            4,
            NoID::SCAFFOLD,
            'tags',
            Type::NULL,
            Action::CREATE_OR_UPDATE,
            'tags',
            [
                $expected_element_tags_condition,
                $expected_element_tag_1,
                $expected_element_tag_2,
                $expected_element_tag_3
            ]
        );

        $expected_element_tags = $this->createExpectedValuesArray(
            1,
            3,
            'tags',
            Type::NULL,
            null,
            '',
            [
                $expected_element_tag_0
            ]
        );

        $expected_element_general_condition = $this->createExpectedValuesArray(
            0,
            2,
            'general_condition'
        );

        $expected_element_special = $this->createExpectedValuesArray(
            0,
            1,
            'special'
        );

        $expected_element_general = $this->createExpectedValuesArray(
            3,
            0,
            'general',
            Type::NULL,
            Action::NEUTRAL,
            '',
            [
                $expected_element_general_condition,
                $expected_element_tags,
                $expected_element_tags_created
            ]
        );

        $expected_element_root = $this->createExpectedValuesArray(
            2,
            NoID::ROOT,
            'root',
            Type::NULL,
            Action::NEUTRAL,
            '',
            [
                $expected_element_general,
                $expected_element_special
            ]
        );

        $this->myAssertTree($element_root, $expected_element_root);
    }

    public function testPrepareForceCreate01(): void
    {
        $manipulator = new Manipulator(
            $this->getRepositoryMock(),
            $this->getMarkerFactoryMock(),
            $this->getNavigatorFactoryMock(),
            $this->getPathFactoryMock(),
            $this->getPathUtilitiesFactoryMock()
        );

        $add_path = $this->getPathMock([
            $this->getStepMock('general', [
                $this->getFilterMock(FilterType::DATA, ['general'])
            ]),
            $this->getStepMock('tags', [
                $this->getFilterMock(FilterType::DATA, ['tags'])
            ]),
            $this->getStepMock('tags_condition', [
                $this->getFilterMock(FilterType::DATA, ['tags_condition'])
            ]),
            $this->getStepMock(StepToken::SUPER, []),
            $this->getStepMock('tag', [
                $this->getFilterMock(FilterType::DATA, ['tag'])
            ]),
        ], false, false);

        $element_root = $this->getElementMock(NoID::ROOT, 'root', Type::NULL, true);
        $element_general = $this->getElementMock(0, 'general', Type::NULL, true);
        $element_special = $this->getElementMock(1, 'special', Type::NULL, true);
        $element_general_condition = $this->getElementMock(2, 'general_condition', Type::NULL);
        $element_tags = $this->getElementMock(3, 'tags', Type::NULL, true);
        $element_tag_0 = $this->getElementMock(4, 'tag', Type::STRING);
        $element_tags_condition = $this->getElementMock(5, 'tags_condition', Type::NULL, true);

        $element_root->addChildren($element_general, $element_special);
        $element_general->addChildren($element_general_condition, $element_tags);
        $element_tags->addChildren($element_tag_0, $element_tags_condition);

        try {
            $manipulator->prepareForceCreate(
                $this->getSetMock($element_root),
                $add_path,
                'test1',
                'test2',
                'test3'
            );
        } catch (\ilMDPathException $e) {
            $this->fail($e->getMessage());
        }

        $expected_element_tag_3 = $this->createExpectedValuesArray(
            0,
            NoID::SCAFFOLD,
            'tag',
            Type::NULL,
            Action::CREATE_OR_UPDATE,
            'test3'
        );

        $expected_element_tag_2 = $this->createExpectedValuesArray(
            0,
            NoID::SCAFFOLD,
            'tag',
            Type::NULL,
            Action::CREATE_OR_UPDATE,
            'test2'
        );

        $expected_element_tag_1 = $this->createExpectedValuesArray(
            0,
            NoID::SCAFFOLD,
            'tag',
            Type::NULL,
            Action::CREATE_OR_UPDATE,
            'test1'
        );

        $expected_element_tag_0 = $this->createExpectedValuesArray(
            0,
            4,
            'tag',
            Type::STRING
        );

        $expected_element_tags_condition = $this->createExpectedValuesArray(
            0,
            5,
            'tags_condition',
            Type::NULL
        );

        $expected_element_tags = $this->createExpectedValuesArray(
            5,
            3,
            'tags',
            Type::NULL,
            Action::NEUTRAL,
            '',
            [
                $expected_element_tag_0,
                $expected_element_tags_condition,
                $expected_element_tag_1,
                $expected_element_tag_2,
                $expected_element_tag_3
            ]
        );

        $expected_element_general_condition = $this->createExpectedValuesArray(
            0,
            2,
            'general_condition'
        );

        $expected_element_special = $this->createExpectedValuesArray(
            0,
            1,
            'special'
        );

        $expected_element_general = $this->createExpectedValuesArray(
            2,
            0,
            'general',
            Type::NULL,
            Action::NEUTRAL,
            '',
            [
                $expected_element_general_condition,
                $expected_element_tags
            ]
        );

        $expected_element_root = $this->createExpectedValuesArray(
            2,
            NoID::ROOT,
            'root',
            Type::NULL,
            Action::NEUTRAL,
            '',
            [
                $expected_element_general,
                $expected_element_special
            ]
        );

        $this->myAssertTree($element_root, $expected_element_root);
    }

    public function testPrepareForceCreate02(): void
    {
        $manipulator = new Manipulator(
            $this->getRepositoryMock(),
            $this->getMarkerFactoryMock(),
            $this->getNavigatorFactoryMock(),
            $this->getPathFactoryMock(),
            $this->getPathUtilitiesFactoryMock()
        );

        $add_path = $this->getPathMock([
            $this->getStepMock('general', [
                $this->getFilterMock(FilterType::DATA, ['general'])
            ]),
            $this->getStepMock('tags', [
                $this->getFilterMock(FilterType::DATA, ['tags'])
            ]),
            $this->getStepMock('tag', [
                $this->getFilterMock(FilterType::DATA, ['tag'])
            ]),
        ], false, false);

        $element_root = $this->getElementMock(NoID::ROOT, 'root', Type::NULL, true);
        $element_general = $this->getElementMock(0, 'general', Type::NULL, true);
        $element_tags = $this->getElementMock(1, 'tags', Type::NULL, true);
        $element_tag_0 = $this->getElementMock(2, 'tag', Type::STRING);

        $element_root->addChildren($element_general);
        $element_general->addChildren($element_tags);
        $element_tags->addChildren($element_tag_0);

        try {
            $manipulator->prepareForceCreate(
                $this->getSetMock($element_root),
                $add_path,
                'test1',
                'test2',
                'test3'
            );
        } catch (\ilMDPathException $e) {
            $this->fail($e->getMessage());
        }

        $expected_element_tag_3 = $this->createExpectedValuesArray(
            0,
            NoID::SCAFFOLD,
            'tag',
            Type::NULL,
            Action::CREATE_OR_UPDATE,
            'test3'
        );

        $expected_element_tag_2 = $this->createExpectedValuesArray(
            0,
            NoID::SCAFFOLD,
            'tag',
            Type::NULL,
            Action::CREATE_OR_UPDATE,
            'test2'
        );

        $expected_element_tag_1 = $this->createExpectedValuesArray(
            0,
            NoID::SCAFFOLD,
            'tag',
            Type::NULL,
            Action::CREATE_OR_UPDATE,
            'test1'
        );

        $expected_element_tag_0 = $this->createExpectedValuesArray(
            0,
            2,
            'tag',
            Type::STRING
        );

        $expected_element_tags = $this->createExpectedValuesArray(
            4,
            1,
            'tags',
            Type::NULL,
            Action::NEUTRAL,
            '',
            [
                $expected_element_tag_0,
                $expected_element_tag_1,
                $expected_element_tag_2,
                $expected_element_tag_3
            ]
        );

        $expected_element_general = $this->createExpectedValuesArray(
            1,
            0,
            'general',
            Type::NULL,
            Action::NEUTRAL,
            '',
            [
                $expected_element_tags
            ]
        );

        $expected_element_root = $this->createExpectedValuesArray(
            1,
            NoID::ROOT,
            'root',
            Type::NULL,
            Action::NEUTRAL,
            '',
            [
                $expected_element_general
            ]
        );

        $this->myAssertTree($element_root, $expected_element_root);
    }
}
