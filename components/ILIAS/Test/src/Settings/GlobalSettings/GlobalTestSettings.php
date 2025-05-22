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

namespace ILIAS\Test\Settings\GlobalSettings;

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Field\Section;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;

class GlobalTestSettings
{
    public function __construct(
        private ProcessLockModes $process_lock_mode = ProcessLockModes::ASS_PROC_LOCK_MODE_NONE,
        private string $image_map_line_color = 'FF0000',
        private UserIdentifiers $user_identifier = UserIdentifiers::USER_ID,
        private int $skill_triggering_number_of_answers = 1,
        private bool $export_essay_questions_as_html = false,
        private array $disabled_question_types = [],
        private bool $manual_scoring_enabled = false,
        private bool $adjusting_questions_with_results_allowed = false,
        private bool $page_editor_enabled = false
    ) {
    }

    /**
     *
     * @return array<ILIAS\UI\Component\Input\Input>
     */
    public function toForm(
        UIFactory $ui_factory,
        Refinery $refinery,
        \ilLanguage $lng
    ): array {
        $ff = $ui_factory->input()->field();
        $all_question_types = \ilObjQuestionPool::_getQuestionTypes(true);
        $enabled_question_types = array_map(
            static fn(array $v): int => $v['question_type_id'],
            array_filter(
                $all_question_types,
                fn(array $v): bool => !in_array($v['question_type_id'], $this->disabled_question_types)
            )
        );
        $trafo = $this->buildGlobalSettingsBuilderTrafo($refinery, $all_question_types);

        return [
            'global_settings' => $ff->group([
                'general_settings' => $this->buildGeneralSettingsInputs($ff, $refinery, $lng),
                'question_settings' => $this->buildQuestionSettingsInputs(
                    $ff,
                    $lng,
                    $all_question_types,
                    $enabled_question_types
                )
            ])->withAdditionalTransformation($trafo)
        ];
    }

    public function getProcessLockMode(): ProcessLockModes
    {
        return $this->process_lock_mode;
    }

    public function withProcessLockMode(ProcessLockModes $process_lock_mode): self
    {
        $clone = clone $this;
        $clone->process_lock_mode = $process_lock_mode;
        return $clone;
    }

    public function getImageMapLineColor(): string
    {
        return $this->image_map_line_color;
    }

    public function withImageMapLineColor(string $image_map_line_color): self
    {
        $clone = clone $this;
        $clone->image_map_line_color = $image_map_line_color;
        return $clone;
    }

    public function getUserIdentifier(): UserIdentifiers
    {
        return $this->user_identifier;
    }

    public function withUserIdentifier(UserIdentifiers $user_identifier): self
    {
        $clone = clone $this;
        $clone->user_identifier = $user_identifier;
        return $clone;
    }

    public function getSkillTriggeringNumberOfAnswers(): int
    {
        return $this->skill_triggering_number_of_answers;
    }

    public function withSkillTriggeringNumberOfAnswers(int $skill_triggering_number_of_answers): self
    {
        $clone = clone $this;
        $clone->skill_triggering_number_of_answers = $skill_triggering_number_of_answers;
        return $clone;
    }

    public function getExportEssayQuestionsAsHtml(): bool
    {
        return $this->export_essay_questions_as_html;
    }

    public function withExportEssayQuestionsAsHtml(bool $export_essay_questions_as_html): self
    {
        $clone = clone $this;
        $clone->export_essay_questions_as_html = $export_essay_questions_as_html;
        return $clone;
    }

    /**
     * @return array<int>
     */
    public function getDisabledQuestionTypes(): array
    {
        return $this->disabled_question_types;
    }

    /**
     * @param array<int> $disabled_question_types
     */
    public function withDisabledQuestionTypes(array $disabled_question_types): self
    {
        $clone = clone $this;
        $clone->disabled_question_types = $disabled_question_types;
        return $clone;
    }

    public function isManualScoringEnabled(): bool
    {
        return $this->manual_scoring_enabled;
    }

    public function withManualScoringEnabled(bool $manual_scoring_enabled): self
    {
        $clone = clone $this;
        $clone->manual_scoring_enabled = $manual_scoring_enabled;
        return $clone;
    }

    public function isAdjustingQuestionsWithResultsAllowed(): bool
    {
        return $this->adjusting_questions_with_results_allowed;
    }

    public function withAdjustingQuestionsWithResultsAllowed(bool $adjusting_questions_with_results_allowed): self
    {
        $clone = clone $this;
        $clone->adjusting_questions_with_results_allowed = $adjusting_questions_with_results_allowed;
        return $clone;
    }

    public function isPageEditorEnabled(): bool
    {
        return $this->page_editor_enabled;
    }

    public function withPageEditorEnabled(bool $page_editor_enabled): self
    {
        $clone = clone $this;
        $clone->page_editor_enabled = $page_editor_enabled;
        return $clone;
    }

    private function buildGlobalSettingsBuilderTrafo(
        Refinery $refinery,
        array $all_question_types
    ): Transformation {
        return $refinery->custom()->transformation(
            static function ($vs) use ($all_question_types): self {
                $process_lock_mode = ProcessLockModes::ASS_PROC_LOCK_MODE_NONE;
                if ($vs['general_settings']['process_lock_mode'] !== null) {
                    $process_lock_mode = ProcessLockModes::from($vs['general_settings']['process_lock_mode'][0]);
                }
                return new self(
                    $process_lock_mode,
                    substr($vs['general_settings']['image_map_line_color']->asHex(), 1),
                    UserIdentifiers::from($vs['general_settings']['user_identifier']),
                    $vs['general_settings']['skill_triggering_number_of_answers'],
                    $vs['general_settings']['export_essay_questions_as_html'],
                    array_reduce(
                        $all_question_types,
                        static function (array $c, array $v) use ($vs): array {
                            if (!in_array($v['question_type_id'], $vs['question_settings']['enabled_question_types'])) {
                                $c[] = $v['question_type_id'];
                            }
                            return $c;
                        },
                        []
                    ),
                    $vs['question_settings']['manual_scoring_enabled'],
                    $vs['question_settings']['adjusting_questions_with_results_allowed']
                );
            }
        );
    }

    private function buildGeneralSettingsInputs(
        FieldFactory $ff,
        Refinery $refinery,
        \ilLanguage $lng
    ): Section {
        return $ff->section(
            [
                'process_lock_mode' => $ff->optionalGroup(
                    [
                        $ff->radio($lng->txt('ass_process_lock_mode'))
                        ->withOption(
                            ProcessLockModes::ASS_PROC_LOCK_MODE_FILE->value,
                            $lng->txt('ass_process_lock_mode_file'),
                            $lng->txt('ass_process_lock_mode_file_desc')
                        )->withOption(
                            ProcessLockModes::ASS_PROC_LOCK_MODE_DB->value,
                            $lng->txt('ass_process_lock_mode_db'),
                            $lng->txt('ass_process_lock_mode_db_desc')
                        )
                    ],
                    $lng->txt('ass_process_lock')
                )->withByline($lng->txt('ass_process_lock_desc'))
                ->withValue($this->process_lock_mode === ProcessLockModes::ASS_PROC_LOCK_MODE_NONE ? null : [$this->process_lock_mode->value]),
                'image_map_line_color' => $ff->colorSelect($lng->txt('imap_line_color'))
                    ->withValue('#' . $this->image_map_line_color),
                'user_identifier' => $ff->select(
                    $lng->txt('user_criteria'),
                    array_reduce(
                        UserIdentifiers::cases(),
                        function (array $c, UserIdentifiers $v): array {
                            $c[$v->value] = $v->value;
                            return $c;
                        },
                        []
                    )
                )->withRequired(true)
                ->withByline($lng->txt('user_criteria_desc'))
                ->withValue($this->user_identifier->value),
                'skill_triggering_number_of_answers' => $ff->numeric($lng->txt('tst_skill_triggerings_num_req_answers'))
                    ->withAdditionalTransformation($refinery->int()->isGreaterThan(0))
                    ->withByline($lng->txt('tst_skill_triggerings_num_req_answers_desc'))
                    ->withValue($this->skill_triggering_number_of_answers),
                'export_essay_questions_as_html' => $ff->checkbox($lng->txt('export_essay_qst_with_html'))
                    ->withByline($lng->txt('export_essay_qst_with_html_desc'))
                    ->withValue($this->export_essay_questions_as_html)
            ],
            $lng->txt('settings')
        );
    }

    private function buildQuestionSettingsInputs(
        FieldFactory $ff,
        \ilLanguage $lng,
        array $all_question_types,
        array $enabled_question_types
    ): Section {
        return $ff->section(
            [
                'enabled_question_types' => $ff->multiSelect(
                    $lng->txt('assf_allowed_questiontypes'),
                    array_reduce(
                        array_keys($all_question_types),
                        function (array $c, string $v) use ($all_question_types): array {
                            $c[$all_question_types[$v]['question_type_id']] = $v;
                            return $c;
                        },
                        []
                    )
                )->withByline($lng->txt('assf_allowed_questiontypes_desc'))
                ->withValue($enabled_question_types),
                'manual_scoring_enabled' => $ff->checkbox($lng->txt('activate_manual_scoring'))
                    ->withByline($lng->txt('activate_manual_scoring_desc'))
                    ->withValue($this->isManualScoringEnabled()),
                'adjusting_questions_with_results_allowed' => $ff->checkbox($lng->txt('assessment_scoring_adjust'))
                    ->withByline($lng->txt('assessment_scoring_adjust_desc'))
                    ->withValue($this->isAdjustingQuestionsWithResultsAllowed())
            ],
            $lng->txt('assf_questiontypes')
        );
    }
}
