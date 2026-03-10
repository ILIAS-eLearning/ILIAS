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

namespace ILIAS\Questions\AnswerFormTypes\Cloze\Capabilities;

use ILIAS\Questions\AnswerForm\Capabilities\Feedback\Feedback;
use ILIAS\Questions\AnswerForm\Capabilities\Feedback\OverviewTable;
use ILIAS\Questions\AnswerForm\Capabilities\Feedback\SpecificFeedback;
use ILIAS\Questions\AnswerForm\Capabilities\Marking\Marking;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\Gap;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Questions\Presentation\Layout\Async;
use ILIAS\Questions\Presentation\Layout\InputsBuilderSession;
use ILIAS\Data\Text\Factory as TextFactory;
use ILIAS\Data\Text\Markdown;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Language\Language;
use ILIAS\Refinery\Custom\Transformation as CustomTransformation;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Input\Field\Section;
use ILIAS\UI\Component\Modal\InterruptiveItem\InterruptiveItem;
use ILIAS\UI\Component\Modal\RoundTrip as RoundTripModal;
use ILIAS\UI\Component\Table\Column\Factory as ColumnFactory;
use ILIAS\UI\Component\Table\Data as DataTable;
use ILIAS\UI\Factory as UIFactory;

class FeedbackOverviewTable implements OverviewTable
{
    private const string STEP_ENTER_FEEDBACK = 'ef';
    private const string STEP_EDIT_FEEDBACK = 'edf';
    private const string STEP_CONFIRM_DELETE_FEEDBACK = 'cdf';
    private const string STEP_DELETE_FEEDBACK = 'df';
    private const string STEP_SAVE_FEEDBACK = 'sf';

    private const string KEY_GAP_ID = 'cap_id';

    public function __construct(
        private readonly UuidFactory $uuid_factory,
        private readonly TextFactory $text_factory,
        private readonly FeedbackOverviewDataRetrieval $data_retrieval
    ) {

    }

    #[\Override]
    public function getCreateModal(
        Environment $environment
    ): RoundTripModal {
        $ui_factory = $environment->getUIFactory();
        $lng = $environment->getLanguage();

        return $ui_factory->modal()->roundtrip(
            $lng->txt('create_feedback'),
            null,
            [
                'gap' => $environment->getAnswerFormProperties()->getGaps()
                    ->buildGapsSelect(
                        $lng->txt('select_gap'),
                        $ui_factory->input()->field()
                    )->withAdditionalTransformation(
                        $environment->getRefinery()->custom()->transformation(
                            fn(string $v): Uuid => $this->uuid_factory->fromString($v)
                        )
                    )->withRequired(true)
            ],
            $environment->withStepParameter(
                self::STEP_ENTER_FEEDBACK
            )->getUrlBuilder()->buildURI()->__toString()
        )->withSubmitLabel($lng->txt('next'));
    }

    #[\Override]
    public function getTable(
        Environment $environment,
        Feedback $feedback
    ): DataTable {
        return $environment->getUIFactory()->table()->data(
            $this->data_retrieval,
            '',
            $this->getColumns(
                $environment->getUIFactory()->table()->column(),
                $environment->getLanguage()
            )
        )->withActions(
            $this->getActions(
                $environment
            )
        )->withRequest($environment->getHttpServices()->request());
    }

    #[\Override]
    public function doAction(
        Environment $environment,
        Feedback $feedback,
        string $action
    ): Async|RoundTripModal|Feedback {
        return match($action) {
            self::STEP_ENTER_FEEDBACK => $this->processSelectGapModal(
                $environment,
                $feedback
            ),
            self::STEP_EDIT_FEEDBACK => $this->editFeedback(
                $environment->withPreservedTableRowIdsParameter(),
                $feedback
            ),
            self::STEP_SAVE_FEEDBACK => $this->processEnterFeedbackModal(
                $environment,
                $feedback
            ),
            self::STEP_CONFIRM_DELETE_FEEDBACK => $this->confirmDeleteFeedback(
                $environment,
                $feedback
            ),
            self::STEP_DELETE_FEEDBACK => $this->deleteFeedback(
                $environment,
                $feedback
            )
        };
    }

    private function processSelectGapModal(
        Environment $environment,
        Feedback $feedback
    ): RoundTripModal {
        $create_modal = $this->getCreateModal($environment)
            ->withRequest($environment->getHttpServices()->request());
        $data = $create_modal->getData();
        if ($data === null) {
            return $create_modal;
        }

        $inputs_builder = $this->buildEnterFeedbackInputBuilder(
            $environment,
            $feedback,
            $data['gap']
        );

        $enter_feedback_modal = $this->buildEnterFeedbackModal(
            $environment,
            $inputs_builder
        );

        $inputs_builder
            ->withCarry(
                $data['gap']->toString()
            )->persistCarry();

        return $enter_feedback_modal;
    }

    private function buildEnterFeedbackModal(
        Environment $environment,
        InputsBuilderSession $inputs_builder
    ): RoundTripModal {
        return $environment->getUIFactory()->modal()->roundtrip(
            $environment->getLanguage()->txt('create_feedback'),
            null,
            [
                'feedback' => $inputs_builder->getInputs()
            ],
            $environment->withStepParameter(
                self::STEP_SAVE_FEEDBACK
            )->getUrlBuilder()->buildURI()->__toString()
        );
    }

    private function processEnterFeedbackModal(
        Environment $environment,
        Feedback $feedback
    ): RoundTripModal|Feedback {
        $specific_feedbacks = $this->getSpecifcFeedbacksFromQuery(
            $environment
        );

        $gap_id = null;
        if (isset($specific_feedbacks[0])) {
            $gap_id = $specific_feedbacks[0]->getParentId();
            $environment = $environment->withPreservedTableRowIdsParameter();
        }

        $inputs_builder = $this->buildEnterFeedbackInputBuilder(
            $environment,
            $feedback,
            $gap_id,
            array_map(
                fn(SpecificFeedback $v): string => $v->getCondition(),
                $specific_feedbacks
            )
        );

        $modal = $this->buildEnterFeedbackModal(
            $environment,
            $inputs_builder
        )->withRequest($environment->getHttpServices()->request());

        $data = $modal->getData();
        if ($data === null) {
            $inputs_builder->persistCarry();
            return $modal;
        }

        return $data['feedback'];
    }

    private function editFeedback(
        Environment $environment,
        Feedback $feedback
    ): Async|Feedback {
        $specific_feedbacks = $this->getSpecifcFeedbacksFromQuery(
            $environment
        );

        return $environment->getPresentationFactory()->getAsync(
            $this->buildEnterFeedbackModal(
                $environment,
                $this->buildEnterFeedbackInputBuilder(
                    $environment,
                    $feedback,
                    $specific_feedbacks[0]->getParentId(),
                    array_map(
                        fn(SpecificFeedback $v): string => $v->getCondition(),
                        $specific_feedbacks
                    ),
                    $specific_feedbacks[0]->getFeedbackText()->getRawRepresentation()
                )
            )
        );
    }

    private function confirmDeleteFeedback(
        Environment $environment,
        Feedback $feedback
    ): Async {
        $pf = $environment->getPresentationFactory();
        $uf = $environment->getUIFactory();
        $lng = $environment->getLanguage();
        $selected_feedbacks = $this->data_retrieval->getSpecificFeedbacksForRowId(
            $environment->getTableRowIds()[0]
        );

        if ($selected_feedbacks === []) {
            return $pf->getAsync(
                $uf->messageBox()->failure(
                    $lng->txt('ui_field_option_filter_no_selection')
                )
            );
        }

        return $pf->getAsync(
            $uf->modal()->interruptive(
                $lng->txt('confirm'),
                $lng->txt('confirm_delete_feedback'),
                $environment
                    ->withStepParameter(self::STEP_DELETE_FEEDBACK)
                    ->getUrlBuilder()
                    ->buildURI()
                    ->__toString()
            )->withAffectedItems([
                $uf->modal()->interruptiveItem()->standard(
                    $environment->getTableRowIds()[0],
                    $selected_feedbacks[0]->getFeedbackText()
                        ?->getRawRepresentation() ?? ''
                )
            ])
        );
    }

    private function deleteFeedback(
        Environment $environment,
        Feedback $feedback
    ): RoundTripModal|Feedback {
        $uf = $environment->getUIFactory();
        $lng = $environment->getLanguage();
        $selected_feedbacks = $environment->getHttpServices()->wrapper()->post()
            ->retrieve(
                'interruptive_items',
                $environment->getRefinery()->custom()->transformation(
                    fn(array $vs): array => $this->data_retrieval
                        ->getSpecificFeedbacksForRowId($vs[0])
                )
            );

        if ($selected_feedbacks === []) {
            return $uf->modal()->roundtrip(
                $lng->txt('error'),
                $uf->messageBox()->failure(
                    $lng->txt('ui_field_option_filter_no_selection')
                )
            );
        }

        return array_reduce(
            $selected_feedbacks,
            fn(Feedback $c, SpecificFeedback $v): Feedback => $c->withoutSpecificFeedback($v),
            $feedback
        );
    }

    private function buildEnterFeedbackInputBuilder(
        Environment $environment,
        Feedback $feedback,
        ?Uuid $selected_gap = null,
        array $selected_conditions = [],
        string $feedback_text = ''
    ): InputsBuilderSession {
        return $environment->getPresentationFactory()->getSessionBasedInputsBuilder(
            self::KEY_GAP_ID,
            $environment->getRefinery()->custom()->transformation(
                function (
                    ?string $v
                ) use (
                    $environment,
                    $feedback,
                    $selected_gap,
                    $selected_conditions,
                    $feedback_text
                ): Section {
                    $gap_id = $selected_gap
                        ?? $this->uuid_factory->fromString($v);

                    return $this->buildSpecificFeedbackSection(
                        $environment->getUIFactory(),
                        $environment->getRefinery(),
                        $environment->getLanguage(),
                        $environment->getAnswerFormProperties()
                            ->getGaps()
                            ->getGapById($gap_id),
                        $environment->isCapabilityRequired(Marking::class),
                        $feedback->getSpecificFeedbacks(),
                        $selected_conditions,
                        $feedback_text
                    )->withAdditionalTransformation(
                        $this->buildSpecificFeedbackTransformation(
                            $environment->getRefinery(),
                            $feedback,
                            $environment->getAnswerFormId(),
                            $gap_id,
                            $selected_conditions
                        )
                    );
                }
            )
        );
    }

    private function buildSpecificFeedbackSection(
        UIFactory $ui_factory,
        Refinery $refinery,
        Language $lng,
        Gap $gap,
        bool $is_marking_required,
        array $existing_specific_feedbacks,
        array $selected_conditions,
        string $feedback_text
    ): Section {
        $answer_options = $gap->getType()->getFeedbackSelectValues(
            $gap,
            $is_marking_required
        );

        foreach ($existing_specific_feedbacks as $feedback) {
            if (!in_array($feedback->getCondition(), $selected_conditions)) {
                unset($answer_options[$feedback->getCondition()]);
            }
        }

        return $ui_factory->input()->field()->section(
            [
                'answer_options' => $ui_factory->input()->field()->multiSelect(
                    $lng->txt('answer_options'),
                    $answer_options
                )->withHasOptionFilter(true)
                ->withRequired(true)
                ->withValue($selected_conditions),
                'feedback' => $ui_factory->input()->field()->markdown(
                    new \ilUIMarkdownPreviewGUI(),
                    $lng->txt('feedback')
                )->withAdditionalTransformation(
                    $refinery->custom()->transformation(
                        fn(string $v): Markdown => $this->text_factory->markdown($v)
                    )
                )->withRequired(true)
                ->withValue($feedback_text)
            ],
            $lng->txt('enter_feedback')
        );
    }

    private function buildSpecificFeedbackTransformation(
        Refinery $refinery,
        Feedback $feedback,
        Uuid $answer_form_id,
        Uuid $gap_id,
        array $selected_conditions
    ): CustomTransformation {
        return $refinery->custom()->transformation(
            function (
                array $vs
            ) use (
                $feedback,
                $answer_form_id,
                $gap_id,
                $selected_conditions
            ): Feedback {
                $feedback_with_removed_specific_feedbacks = array_reduce(
                    array_diff($selected_conditions, $vs['answer_options']),
                    fn(Feedback $c, string $v): Feedback => $c->withoutSpecificFeedback(
                        $c->getSpecificFeedbackForConditionOrNew(
                            $this->uuid_factory,
                            $answer_form_id,
                            $gap_id,
                            $v
                        )
                    ),
                    $feedback
                );

                return array_reduce(
                    $vs['answer_options'],
                    fn(Feedback $c, string $v): Feedback => $c->withSpecificFeedback(
                        $c->getSpecificFeedbackForConditionOrNew(
                            $this->uuid_factory,
                            $answer_form_id,
                            $gap_id,
                            $v
                        )->withFeedbackText($vs['feedback'])
                    ),
                    $feedback_with_removed_specific_feedbacks
                );
            }
        );
    }

    private function getSpecifcFeedbacksFromQuery(
        Environment $environment
    ): array {
        $selected_table_row_ids = $environment->getTableRowIds();

        if ($selected_table_row_ids === []) {
            return [];
        }

        return $this->data_retrieval->getSpecificFeedbacksForRowId(
            $selected_table_row_ids[0]
        );
    }

    private function getColumns(
        ColumnFactory $column_factory,
        Language $lng
    ): array {
        return [
            'gap' => $column_factory->text(
                $lng->txt('gap')
            )->withIsSortable(false),
            'answer_option' => $column_factory->text(
                $lng->txt('answer_option')
            )->withIsSortable(false),
            'feedback' => $column_factory->text(
                $lng->txt('feedback')
            )->withIsSortable(false)
        ];
    }

    private function getActions(
        Environment $environment
    ): array {
        $af = $environment->getUIFactory()->table()->action();
        $lng = $environment->getLanguage();

        return [
            'edit' => $af->single(
                $lng->txt('edit'),
                $environment->withStepParameter(
                    self::STEP_EDIT_FEEDBACK
                )->getUrlBuilder(),
                $environment->getTableRowIdToken()
            )->withAsync(true),
            'delete' => $af->single(
                $lng->txt('delete'),
                $environment->withStepParameter(
                    self::STEP_CONFIRM_DELETE_FEEDBACK
                )->getUrlBuilder(),
                $environment->getTableRowIdToken()
            )->withAsync(true),
        ];
    }
}
