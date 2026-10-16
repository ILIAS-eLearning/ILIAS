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

use ILIAS\Questions\AnswerForm\Capabilities\TextFeedback\HtmlPurifier;
use ILIAS\Questions\AnswerForm\Capabilities\TextFeedback\TextFeedback;
use ILIAS\Questions\AnswerForm\Capabilities\TextFeedback\OverviewTable;
use ILIAS\Questions\AnswerForm\Capabilities\TextFeedback\SpecificTextFeedback;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\Gap;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Questions\Presentation\Layout\Async;
use ILIAS\Questions\Presentation\Layout\Tools\InputsBuilderSession;
use ILIAS\Data\Text\Factory as TextFactory;
use ILIAS\Data\Text\Markdown;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Language\Language;
use ILIAS\Refinery\Custom\Transformation as CustomTransformation;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Input\Field\Section;
use ILIAS\UI\Component\Modal\RoundTrip as RoundTripModal;
use ILIAS\UI\Component\Table\Column\Factory as ColumnFactory;
use ILIAS\UI\Component\Table\Data as DataTable;
use ILIAS\UI\Factory as UIFactory;

class TextFeedbackOverviewTable implements OverviewTable
{
    private const string SUB_ACTION_ENTER_FEEDBACK = 'ef';
    private const string SUB_ACTION_EDIT_FEEDBACK = 'edf';
    private const string SUB_ACTION_CONFIRM_DELETE_FEEDBACK = 'cdf';
    private const string SUB_ACTION_DELETE_FEEDBACK = 'df';
    private const string SUB_ACTION_SAVE_FEEDBACK = 'sf';
    private const string SUB_ACTION_ADD_LEGACY_TEXT = 'alt';

    public function __construct(
        private readonly UuidFactory $uuid_factory,
        private readonly TextFactory $text_factory,
        private readonly TextFeedbackOverviewDataRetrieval $data_retrieval
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
            $environment->withSubActionParameter(
                self::SUB_ACTION_ENTER_FEEDBACK
            )->getUrlBuilder()->buildURI()->__toString()
        )->withSubmitLabel($lng->txt('next'));
    }

    #[\Override]
    public function getTable(
        Environment $environment,
        TextFeedback $feedback
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
        TextFeedback $feedback,
        string $action
    ): Async|RoundTripModal|TextFeedback {
        return match($action) {
            self::SUB_ACTION_ENTER_FEEDBACK => $this->processSelectGapModal(
                $environment,
                $feedback
            ),
            self::SUB_ACTION_EDIT_FEEDBACK => $this->editFeedback(
                $environment->withPreservedTableRowIdsParameter(),
                $feedback
            ),
            self::SUB_ACTION_SAVE_FEEDBACK => $this->processEnterFeedbackModal(
                $environment,
                $feedback
            ),
            self::SUB_ACTION_CONFIRM_DELETE_FEEDBACK => $this->confirmDeleteFeedback(
                $environment
            ),
            self::SUB_ACTION_DELETE_FEEDBACK => $this->deleteFeedback(
                $environment,
                $feedback
            ),
            self::SUB_ACTION_ADD_LEGACY_TEXT => $this->addLegacyFeedback(
                $environment->withPreservedTableRowIdsParameter(),
                $feedback
            )
        };
    }

    private function processSelectGapModal(
        Environment $environment,
        TextFeedback $feedback
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
            $inputs_builder,
            false
        );

        $inputs_builder
            ->withCarry(
                $data['gap']->toString()
            )->persistCarry();

        return $enter_feedback_modal;
    }

    private function buildEnterFeedbackModal(
        Environment $environment,
        InputsBuilderSession $inputs_builder,
        bool $needs_legacy_text_info
    ): RoundTripModal {
        return $environment->getUIFactory()->modal()->roundtrip(
            $environment->getLanguage()->txt('edit_feedback'),
            $needs_legacy_text_info
                ? [
                    $environment->getUIFactory()->messageBox()->info(
                        $environment->getLanguage()->txt('insert_legacy_texts_info')
                    )->withButtons([
                        $environment->getUIFactory()->button()->standard(
                            $environment->getLanguage()->txt('insert_legacy_texts'),
                            $environment->withSubActionParameter(
                                self::SUB_ACTION_ADD_LEGACY_TEXT
                            )->getUrlBuilder()->buildURI()->__toString()
                        )
                    ])
                ]
                : null,
            [
                'feedback' => $inputs_builder->getInputs()
            ],
            $environment->withSubActionParameter(
                self::SUB_ACTION_SAVE_FEEDBACK
            )->getUrlBuilder()->buildURI()->__toString()
        );
    }

    private function processEnterFeedbackModal(
        Environment $environment,
        TextFeedback $feedback
    ): RoundTripModal|TextFeedback {
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
                fn(SpecificTextFeedback $v): string => $v->getCondition(),
                $specific_feedbacks
            )
        );

        $modal = $this->buildEnterFeedbackModal(
            $environment,
            $inputs_builder,
            isset($specific_feedbacks[0])
                ? $specific_feedbacks[0]->displaysLegacyText()
                : false
        )->withRequest($environment->getHttpServices()->request());

        $data = $modal->getData();
        if ($data === null) {
            $inputs_builder->persistCarry();
            return $modal;
        }

        return $data['feedback'];
    }

    private function addLegacyFeedback(
        Environment $environment,
        TextFeedback $feedback
    ): RoundTripModal {
        $specific_feedbacks = $this->getSpecifcFeedbacksFromQuery(
            $environment
        );

        return $this->buildEnterFeedbackModal(
            $environment,
            $this->buildEnterFeedbackInputBuilder(
                $environment,
                $feedback,
                $specific_feedbacks[0]->getParentId(),
                array_map(
                    fn(SpecificTextFeedback $v): string => $v->getCondition(),
                    $specific_feedbacks
                ),
                (new HtmlPurifier())->prepareAndPurify(
                    $specific_feedbacks[0]->getLegacyFeedbackText()
                )
            ),
            false
        );
    }

    private function editFeedback(
        Environment $environment,
        TextFeedback $feedback
    ): Async {
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
                        fn(SpecificTextFeedback $v): string => $v->getCondition(),
                        $specific_feedbacks
                    ),
                    $specific_feedbacks[0]->getFeedbackText()?->getRawRepresentation() ?? ''
                ),
                $specific_feedbacks[0]->displaysLegacyText()
            )
        );
    }

    private function confirmDeleteFeedback(
        Environment $environment
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
                    ->withSubActionParameter(self::SUB_ACTION_DELETE_FEEDBACK)
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
        TextFeedback $feedback
    ): RoundTripModal|TextFeedback {
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
            fn(TextFeedback $c, SpecificTextFeedback $v): TextFeedback => $c->withoutSpecificFeedback($v),
            $feedback
        );
    }

    private function buildEnterFeedbackInputBuilder(
        Environment $environment,
        TextFeedback $feedback,
        ?Uuid $selected_gap = null,
        array $selected_conditions = [],
        string $feedback_text = ''
    ): InputsBuilderSession {
        return $environment->getPresentationFactory()->getSessionBasedInputsBuilder(
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
                        $environment->isMarkingRequired(),
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
            $lng->txt('feedback')
        );
    }

    private function buildSpecificFeedbackTransformation(
        Refinery $refinery,
        TextFeedback $feedback,
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
            ): TextFeedback {
                $feedback_with_removed_specific_feedbacks = array_reduce(
                    array_diff($selected_conditions, $vs['answer_options']),
                    fn(TextFeedback $c, string $v): TextFeedback => $c->withoutSpecificFeedback(
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
                    fn(TextFeedback $c, string $v): TextFeedback => $c->withSpecificFeedback(
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
            'answer_options' => $column_factory->text(
                $lng->txt('answer_options')
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
                $environment->withSubActionParameter(
                    self::SUB_ACTION_EDIT_FEEDBACK
                )->getUrlBuilder(),
                $environment->getTableRowIdToken()
            )->withAsync(true),
            'delete' => $af->single(
                $lng->txt('delete'),
                $environment->withSubActionParameter(
                    self::SUB_ACTION_CONFIRM_DELETE_FEEDBACK
                )->getUrlBuilder(),
                $environment->getTableRowIdToken()
            )->withAsync(true),
        ];
    }
}
