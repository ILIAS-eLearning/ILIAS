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

namespace ILIAS\Questions\AnswerFormTypes\Cloze\Layout;

use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Combinations\Combination;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Combinations\Factory as CombinationsFactory;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Properties;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Questions\Presentation\Layout\Async;
use ILIAS\Questions\Presentation\Layout\InputsBuilder;
use ILIAS\Questions\Presentation\Layout\Renderable;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\HTTP\Services as Http;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Modal\RoundTrip as RoundTripModal;
use ILIAS\UI\Component\Modal\Interruptive as InterruptiveModal;
use ILIAS\UI\Component\Table\Data as DataTable;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Renderer as UIRenderer;

class CombinationsOverview implements DataRetrieval, Renderable
{
    private const string STEP_SAVE = 's';
    private const string STEP_SET_COMBINATION_VALUES = 'scv';
    private const string STEP_JUMP_TO_SET_COMBINATION_VALUES = 'jscv';
    private const string STEP_DELETE_COMBINATION = 'dc';
    private const string STEP_CONFIRM_DELETE_COMBINATION = 'cdc';

    private ?RoundTripModal $modal = null;

    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly \ilToolbarGUI $toolbar,
        private readonly Refinery $refinery,
        private readonly Language $lng,
        private readonly Http $http,
        private readonly Environment $environment,
        private readonly CombinationsFactory $combinations_factory
    ) {
    }

    #[\Override]
    public function render(
        UIRenderer $ui_renderer
    ): string {
        $content = [
            $this->initializeModal($this->buildSetCombinationGapsModal()),
            $this->buildTable()
        ];
        if ($this->modal !== null) {
            $content[] = $this->modal;
        }
        return $ui_renderer->render($content);
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
            ->getCombinations()->toTableRows($this->lng, $row_builder);
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
        return match ($this->environment->getStep()) {
            self::STEP_SET_COMBINATION_VALUES => $this->processSetCombinationGapsModal(),
            self::STEP_DELETE_COMBINATION => $this->deleteCombination(),
            self::STEP_SAVE => $this->processSetCombinationValues(),
            default => $this->buildAction()
        };
    }

    private function buildTable(): DataTable
    {
        return $this->ui_factory->table()->data(
            $this,
            $this->lng->txt('combinations'),
            $this->getColumns()
        )->withActions($this->getActions())
        ->withRequest($this->http->request());
    }

    private function initializeModal(
        RoundTripModal $modal
    ): RoundTripModal {
        $this->toolbar->addComponent(
            $this->ui_factory->button()->standard(
                $this->lng->txt('add_combination'),
                $modal->getShowSignal()
            )
        );
        return $modal;
    }

    private function getColumns(): array
    {
        $cf = $this->ui_factory->table()->column();
        return [
            'gaps' => $cf->text($this->lng->txt('gaps')),
            'values' => $cf->text($this->lng->txt('values')),
            'available_points' => $cf->number($this->lng->txt('points'))->withDecimals(2)
        ];
    }

    private function getActions(): array
    {
        $af = $this->ui_factory->table()->action();
        return [
            $af->single(
                $this->lng->txt('edit'),
                $this->environment
                    ->withStepParameter(self::STEP_JUMP_TO_SET_COMBINATION_VALUES)
                    ->getUrlBuilder(),
                $this->environment->getTableRowIdToken()
            )->withAsync(true),
            $af->single(
                $this->lng->txt('delete'),
                $this->environment
                ->withStepParameter(self::STEP_CONFIRM_DELETE_COMBINATION)
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
            match ($this->environment->getStep()) {
                self::STEP_JUMP_TO_SET_COMBINATION_VALUES =>
                    $this->buildSetCombinationValuesModal($affected_item),
                self::STEP_CONFIRM_DELETE_COMBINATION =>
                    $this->confirmDeleteCombination($affected_item)
            }
        );
    }

    private function buildNoItemsSelectedAsync(): Async
    {
        return new Async(
            $this->http,
            $this->ui_factory->messageBox()->failure('no_combination_selected')
        );
    }

    private function buildSetCombinationGapsModal(): RoundTripModal
    {
        $properties = $this->environment->getAnswerFormProperties();
        $gaps = $properties->getGaps();
        return $this->ui_factory->modal()->roundtrip(
            $this->lng->txt('add_combination'),
            $properties->getClozeText()->buildPanelForEditing(
                $this->ui_factory,
                $this->lng,
                $gaps,
                $properties->getLegacyClozeText()
            ),
            [
                'combination' => $gaps->buildGapsMultiSelect(
                    $this->lng->txt('select_gaps_for_combinations'),
                    $this->ui_factory->input()->field()
                )->withRequired(true)
                ->withAdditionalTransformation(
                    $this->refinery->custom()->constraint(
                        fn(array $v): bool => count($v) > 1,
                        $this->lng->txt('combination_needs_more_than_one')
                    )
                )->withAdditionalTransformation(
                    $this->refinery->custom()->transformation(
                        fn(array $v): Combination => $this->combinations_factory
                        ->buildNewCombination($gaps, $v)
                    )
                )
            ],
            $this->environment
                ->withStepParameter(self::STEP_SET_COMBINATION_VALUES)
                ->getUrlBuilder()
                ->buildURI()
                ->__toString()
        )->withSubmitLabel($this->lng->txt('next'));
    }

    private function processSetCombinationGapsModal(): self
    {
        $clone = clone $this;

        $set_gaps_modal = $clone->buildSetCombinationGapsModal()
            ->withRequest($clone->http->request());
        $data = $set_gaps_modal->getData();

        if ($data === null) {
            $clone->modal = $set_gaps_modal->withOnLoad($set_gaps_modal->getShowSignal());
            return $clone;
        }

        $set_values_modal = $clone->buildSetCombinationValuesModal($data['combination']);
        $clone->modal = $set_values_modal->withOnLoad($set_values_modal->getShowSignal());
        return $clone;
    }

    private function buildSetCombinationValuesModal(
        ?Combination $combination = null
    ): RoundTripModal {
        $properties = $this->environment->getAnswerFormProperties();
        $gaps = $properties->getGaps();

        $inputs_builder = $this->buildInputsBuilder($combination);
        $inputs = $inputs_builder->getInputs(
            $this->environment
        );

        return $this->ui_factory->modal()->roundtrip(
            $this->lng->txt('edit'),
            $properties->getClozeText()->buildPanelForEditing(
                $this->ui_factory,
                $this->lng,
                $gaps,
                $properties->getLegacyClozeText()
            ),
            [
                'values_awarding_points' => $inputs
            ],
            $inputs_builder->addCarryToEnvironment(
                $this->environment
            )->withStepParameter(self::STEP_SAVE)
            ->getUrlBuilder()
            ->buildURI()
            ->__toString()
        );
    }

    private function processSetCombinationValues(): self|Properties
    {
        $set_values_modal = $this->buildSetCombinationValuesModal()
            ->withRequest($this->http->request());
        $data = $set_values_modal->getData();
        if ($data === null) {
            $this->modal = $this->initializeModal($set_values_modal)
                ->withOnLoad($set_values_modal->getShowSignal());
            return $this;
        }

        return $data['values_awarding_points'];
    }

    private function confirmDeleteCombination(
        Combination $affected_item
    ): InterruptiveModal {
        return $this->ui_factory->modal()->interruptive(
            $this->lng->txt('confirm'),
            $this->lng->txt('delete_combination'),
            $this->environment->withStepParameter(
                self::STEP_DELETE_COMBINATION
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
    ): InputsBuilder {
        $inputs_builder = $this->environment->getPresentationFactory()->getInputsBuilder(
            $this->refinery->custom()->transformation(
                function (?string $v) use ($combination): ?Group {
                    $properties = $this->environment->getAnswerFormProperties();
                    if ($combination === null) {
                        $combination = $this->combinations_factory
                            ->buildCombinationFromCarryValue(
                                $v,
                                $properties
                            );
                    }

                    return $combination?->buildPointsInputs(
                        $this->ui_factory->input()->field(),
                        $this->refinery,
                        $this->lng,
                        $properties
                    );
                }
            )
        );

        if ($combination === null) {
            return $inputs_builder;
        }

        return $inputs_builder->withCarry($combination->buildCarryString());
    }
}
