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

namespace ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Combinations;

use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Combinations\Combination;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Combinations\Factory as CombinationsFactory;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Properties;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Questions\Presentation\Layout\Async;
use ILIAS\Questions\Presentation\Layout\Tools\InputsBuilder;
use ILIAS\Questions\Presentation\Layout\Tools\InputsBuilderSession;
use ILIAS\Questions\Presentation\Layout\Viewable;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\UI\Component\Input\Field\Section;
use ILIAS\UI\Component\Modal\RoundTrip as RoundTripModal;
use ILIAS\UI\Component\Modal\Interruptive as InterruptiveModal;
use ILIAS\UI\Component\Table\Data as DataTable;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;

class Overview implements Viewable, DataRetrieval
{
    private const string SUB_ACTION_SAVE = 's';
    private const string SUB_ACTION_SET_COMBINATION_VALUES = 'scv';
    private const string SUB_ACTION_JUMP_TO_SET_COMBINATION_VALUES = 'jscv';
    private const string SUB_ACTION_DELETE_COMBINATION = 'dc';
    private const string SUB_ACTION_CONFIRM_DELETE_COMBINATION = 'cdc';

    private ?RoundTripModal $modal = null;

    public function __construct(
        private readonly Environment $environment,
        private readonly CombinationsFactory $combinations_factory
    ) {
    }

    #[\Override]
    public function getUI(): array
    {
        $modal = $this->buildSetCombinationGapsModal();
        $content = [
            $this->environment->getUIFactory()->button()->standard(
                $this->environment->getLanguage()->txt('add_combination'),
                $modal->getShowSignal()
            ),
            $modal,
            $this->buildTable()
        ];
        if ($this->modal !== null) {
            $content[] = $this->modal;
        }
        return $content;
    }

    #[\Override]
    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): \Generator {
        yield from $this->environment->getAnswerFormProperties()
            ->getCombinations()->toTableRows(
                $this->environment->getLanguage(),
                $row_builder
            );
    }

    #[\Override]
    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return $this->environment->getAnswerFormProperties()
            ->getCombinations()->getNumberOfCombinations();
    }

    public function doAction(): Async|self|Properties
    {
        return match ($this->environment->getSubAction()) {
            self::SUB_ACTION_SET_COMBINATION_VALUES => $this->processSetCombinationGapsModal(),
            self::SUB_ACTION_DELETE_COMBINATION => $this->deleteCombination(),
            self::SUB_ACTION_SAVE => $this->processSetCombinationValues(),
            default => $this->buildAction()
        };
    }

    private function buildTable(): DataTable
    {
        return $this->environment->getUIFactory()->table()->data(
            $this,
            $this->environment->getLanguage()->txt('combinations'),
            $this->getColumns()
        )->withActions($this->getActions())
        ->withRequest($this->environment->getHttpServices()->request());
    }

    private function getColumns(): array
    {
        $cf = $this->environment->getUIFactory()->table()->column();
        return [
            'gaps' => $cf->text($this->environment->getLanguage()->txt('gaps')),
            'values' => $cf->text($this->environment->getLanguage()->txt('values')),
            'available_points' => $cf->number($this->environment->getLanguage()->txt('points'))->withDecimals(2)
        ];
    }

    private function getActions(): array
    {
        $af = $this->environment->getUIFactory()->table()->action();
        return [
            $af->single(
                $this->environment->getLanguage()->txt('edit'),
                $this->environment
                    ->withSubActionParameter(self::SUB_ACTION_JUMP_TO_SET_COMBINATION_VALUES)
                    ->getUrlBuilder(),
                $this->environment->getTableRowIdToken()
            )->withAsync(true),
            $af->single(
                $this->environment->getLanguage()->txt('delete'),
                $this->environment
                ->withSubActionParameter(self::SUB_ACTION_CONFIRM_DELETE_COMBINATION)
                ->getUrlBuilder(),
                $this->environment->getTableRowIdToken()
            )->withAsync(true)
        ];
    }

    private function buildAction(): Async
    {
        $affected_item = $this->environment->getAnswerFormProperties()
            ->getCombinations()->getCombinationById(
                $this->environment->getTableRowIds()[0]
            );

        if ($affected_item === null) {
            return $this->buildNoItemsSelectedAsync();
        }

        return $this->environment->getPresentationFactory()->getAsync(
            match ($this->environment->getSubAction()) {
                self::SUB_ACTION_JUMP_TO_SET_COMBINATION_VALUES =>
                    $this->buildSetCombinationValuesModal(
                        $this->buildInputsBuilder($affected_item)
                    ),
                self::SUB_ACTION_CONFIRM_DELETE_COMBINATION =>
                    $this->confirmDeleteCombination($affected_item)
            }
        );
    }

    private function buildNoItemsSelectedAsync(): Async
    {
        return new Async(
            $this->environment->getHttpServices(),
            $this->environment->getUIFactory()->messageBox()
                ->failure('no_combination_selected')
        );
    }

    private function buildSetCombinationGapsModal(): RoundTripModal
    {
        $properties = $this->environment->getAnswerFormProperties();
        $gaps = $properties->getGaps();
        return $this->environment->getUIFactory()->modal()->roundtrip(
            $this->environment->getLanguage()->txt('add_combination'),
            $properties->getClozeText()->buildPanelForEditing(
                $this->environment->getUIFactory(),
                $this->environment->getLanguage(),
                $gaps,
                $properties->getLegacyClozeText()
            ),
            [
                'combination' => $gaps->buildGapsMultiSelect(
                    $this->environment->getLanguage()->txt('select_gaps_for_combinations'),
                    $this->environment->getUIFactory()->input()->field()
                )->withRequired(true)
                ->withAdditionalTransformation(
                    $this->environment->getRefinery()->custom()->constraint(
                        fn(array $v): bool => count($v) > 1,
                        $this->environment->getLanguage()->txt('combination_needs_more_than_one')
                    )
                )->withAdditionalTransformation(
                    $this->environment->getRefinery()->custom()->transformation(
                        fn(array $v): Combination => $this->combinations_factory
                        ->buildNewCombination($gaps, $v)
                    )
                )
            ],
            $this->environment
                ->withSubActionParameter(self::SUB_ACTION_SET_COMBINATION_VALUES)
                ->getUrlBuilder()
                ->buildURI()
                ->__toString()
        )->withSubmitLabel($this->environment->getLanguage()->txt('next'));
    }

    private function processSetCombinationGapsModal(): self
    {
        $clone = clone $this;

        $set_gaps_modal = $clone->buildSetCombinationGapsModal()
            ->withRequest($clone->environment->getHttpServices()->request());
        $data = $set_gaps_modal->getData();

        if ($data === null) {
            $clone->modal = $set_gaps_modal->withOnLoad($set_gaps_modal->getShowSignal());
            return $clone;
        }

        $set_values_modal = $clone->buildSetCombinationValuesModal(
            $this->buildInputsBuilder($data['combination'])
        );
        $clone->modal = $set_values_modal->withOnLoad($set_values_modal->getShowSignal());
        return $clone;
    }

    private function buildSetCombinationValuesModal(
        InputsBuilder $inputs_builder
    ): RoundTripModal {
        $properties = $this->environment->getAnswerFormProperties();
        $gaps = $properties->getGaps();

        return $this->environment->getUIFactory()->modal()->roundtrip(
            $this->environment->getLanguage()->txt('edit'),
            $properties->getClozeText()->buildPanelForEditing(
                $this->environment->getUIFactory(),
                $this->environment->getLanguage(),
                $gaps,
                $properties->getLegacyClozeText()
            ),
            [
                'values_awarding_points' => $inputs_builder->getInputs()
            ],
            $this->environment->withSubActionParameter(self::SUB_ACTION_SAVE)
            ->getUrlBuilder()
            ->buildURI()
            ->__toString()
        );
    }

    private function processSetCombinationValues(): self|Properties
    {
        $inputs_builder = $this->buildInputsBuilder(null);
        $set_values_modal = $this->buildSetCombinationValuesModal($inputs_builder)
            ->withRequest($this->environment->getHttpServices()->request());
        $data = $set_values_modal->getData();
        if ($data === null) {
            $this->modal = $this->initializeModal($set_values_modal)
                ->withOnLoad($set_values_modal->getShowSignal());
            $inputs_builder->persistCarry();
            return $this;
        }

        return $data['values_awarding_points'];
    }

    private function confirmDeleteCombination(
        Combination $affected_item
    ): InterruptiveModal {
        return $this->environment->getUIFactory()->modal()->interruptive(
            $this->environment->getLanguage()->txt('confirm'),
            $this->environment->getLanguage()->txt('delete_combination'),
            $this->environment->withSubActionParameter(
                self::SUB_ACTION_DELETE_COMBINATION
            )->getUrlBuilder()
            ->withParameter(
                $this->environment->getTableRowIdToken(),
                [$affected_item->getId()->toString()]
            )->buildURI()->__toString()
        );
    }

    private function deleteCombination(): Properties
    {
        $combination_identifier = $this->environment->getTableRowIds();
        if ($combination_identifier === []) {
            return $this->environment->getAnswerFormProperties();
        }

        /** @var \ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Properties $answer_form_properties */
        $answer_form_properties = $this->environment->getAnswerFormProperties();

        return $answer_form_properties->withCombinations(
            $answer_form_properties->getCombinations()->withoutCombination(
                $combination_identifier[0]
            )
        );
    }

    private function buildInputsBuilder(
        ?Combination $combination,
    ): InputsBuilderSession {
        $builder = $this->environment->getPresentationFactory()->getSessionBasedInputsBuilder(
            $this->environment->getAnswerFormProperties()->getAnswerFormId()->toString(),
            $this->environment->getRefinery()->custom()->transformation(
                function (?string $v) use ($combination): ?Section {
                    $properties = $this->environment->getAnswerFormProperties();
                    if ($combination === null) {
                        $combination = $this->combinations_factory
                            ->buildCombinationFromCarryValue(
                                $v,
                                $properties
                            );
                    }

                    return $combination?->buildPointsInputs(
                        $this->environment->getUIFactory()->input()->field(),
                        $this->environment->getRefinery(),
                        $this->environment->getLanguage(),
                        $this->combinations_factory,
                        $properties
                    );
                }
            )
        );

        if ($combination === null) {
            return $builder;
        }

        $builder_with_string = $builder->withCarry($combination->buildCarryString());
        $builder_with_string->persistCarry();
        return $builder_with_string;
    }
}
