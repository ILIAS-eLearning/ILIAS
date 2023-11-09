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

use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Component\Input\Field\Radio;
use ILIAS\UI\Component\Input\Field\OptionalGroup;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\Transformation;

class ilObjTestSettingsQuestionBehaviour extends TestSettings
{
    private const DEFAULT_AUTOSAVE_INTERVAL = 30000;

    public const ANSWER_FIXATION_NONE = 'none';
    public const ANSWER_FIXATION_ON_INSTANT_FEEDBACK = 'instant_feedback';
    public const ANSWER_FIXATION_ON_FOLLOWUP_QUESTION = 'followup_question';
    public const ANSWER_FIXATION_ON_IFB_OR_FUQST = 'ifb_or_fuqst';

    public function __construct(
        int $test_id,
        protected int $question_title_output_mode,
        protected bool $autosave_enabled,
        protected int $autosave_interval,
        protected bool $shuffle_questions,
        protected bool $question_hints_enabled,
        protected bool $instant_feedback_points_enabled,
        protected bool $instant_feedback_generic_enabled,
        protected bool $instant_feedback_specific_enabled,
        protected bool $instant_feedback_solution_enabled,
        protected bool $force_instant_feedback_on_next_question,
        protected bool $lock_answer_on_instant_feedback,
        protected bool $lock_answer_on_next_question,
        protected bool $compulsory_questions_enabled
    ) {
        parent::__construct($test_id);
    }

    /**
     *
     * @return array<ILIAS\UI\Component\Input\Field\Input>
     */
    public function toForm(
        \ilLanguage $lng,
        FieldFactory $f,
        Refinery $refinery,
        array $environment = null
    ): FormInput {
        $inputs['title_output'] = $f->radio($lng->txt('tst_title_output'))
            ->withOption('0', $lng->txt('tst_title_output_full'))
            ->withOption('1', $lng->txt('tst_title_output_hide_points'))
            ->withOption('3', $lng->txt('tst_title_output_only_points'))
            ->withOption('2', $lng->txt('tst_title_output_no_title'))
            ->withValue($this->getQuestionTitleOutputMode())
            ->withAdditionalTransformation($refinery->kindlyTo()->int());

        $inputs['autosave'] = $this->getInputAutosave($lng, $f, $refinery);

        $inputs['shuffle_questions'] = $f->checkbox(
            $lng->txt('tst_shuffle_questions'),
            $lng->txt('tst_shuffle_questions_description')
        )->withValue($this->getShuffleQuestions());

        $inputs['offer_hints'] = $f->checkbox(
            $lng->txt('tst_setting_offer_hints_label'),
            $lng->txt('tst_setting_offer_hints_info')
        )->withValue($this->getQuestionHintsEnabled());

        if ($environment['participant_data_exists']) {
            $inputs['shuffle_questions'] = $inputs['shuffle_questions']->withDisabled(true);
        }

        $inputs['instant_feedback'] = $this->getInputInstantFeedback($lng, $f, $refinery, $environment);
        $inputs['lock_answers'] = $this->getInputLockAnswers($lng, $f, $refinery, $environment);

        $inputs['enable_compulsory_questions'] = $f->checkbox(
            $lng->txt('tst_setting_enable_obligations_label'),
            $lng->txt('tst_setting_enable_obligations_info')
        )->withValue($this->getCompulsoryQuestionsEnabled());

        if ($environment['participant_data_exists']) {
            $inputs['enable_compulsory_questions'] = $inputs['enable_compulsory_questions']->withDisabled(true);
        }

        $section = $f->section($inputs, $lng->txt('tst_presentation_properties'));
        foreach ($this->getConstraintsSectionQuestionBehaviour($lng, $refinery) as $constraint) {
            $section = $section->withAdditionalTransformation($constraint);
        }

        return $section;
    }

    private function getInputAutosave(
        \ilLanguage $lng,
        FieldFactory $f,
        Refinery $refinery
    ): OptionalGroup {
        $trafo = $refinery->custom()->transformation(
            static function (?array $vs): array {
                if ($vs === null) {
                    return [
                        'autosave_enabled' => false,
                        'autosave_interval' => self::DEFAULT_AUTOSAVE_INTERVAL
                    ];
                }

                return [
                    'autosave_enabled' => true,
                    'autosave_interval' => $vs['autosave_interval'] * 1000
                ];
            }
        );
        $sub_inputs_autosave['autosave_interval'] = $f->numeric($lng->txt('autosave_ival'))
            ->withRequired(true)
            ->withAdditionalTransformation($refinery->int()->isGreaterThan(0))
            ->withValue($this->getAutosaveInterval() / 1000);

        $autosave_input = $f->optionalGroup(
            $sub_inputs_autosave,
            $lng->txt('autosave'),
            $lng->txt('autosave_info')
        )->withValue(null)
            ->withAdditionalTransformation($trafo);

        if (!$this->getAutosaveEnabled()) {
            return $autosave_input;
        }

        return $autosave_input->withValue(
            [
                'autosave_interval' => $this->getAutosaveInterval() / 1000
            ]
        );
    }

    private function getInputInstantFeedback(
        \ilLanguage $lng,
        FieldFactory $f,
        Refinery $refinery,
        array $environment
    ): OptionalGroup {
        $trafo = $refinery->custom()->transformation(
            static function (?array $vs): array {
                if ($vs === null) {
                    return [
                        'enabled_feedback_types' => [
                            'instant_feedback_specific' => false,
                            'instant_feedback_generic' => false,
                            'instant_feedback_points' => false,
                            'instant_feedback_solution' => false
                        ],
                        'feedback_on_next_question' => false
                    ];
                }

                $vs['feedback_on_next_question'] = $vs['feedback_trigger'] === '1' ? true : false;
                return $vs;
            }
        );

        $instant_feedback = $f->optionalGroup(
            $this->getSubInputInstantFeedback($lng, $f),
            $lng->txt('tst_instant_feedback'),
            $lng->txt('tst_instant_feedback_desc')
        )->withValue(null)
            ->withAdditionalTransformation($trafo);

        if ($this->isAnyInstantFeedbackOptionEnabled()) {
            $instant_feedback = $instant_feedback->withValue(
                [
                    'enabled_feedback_types' => [
                        'instant_feedback_specific' => (bool) $this->getInstantFeedbackSpecificEnabled(),
                        'instant_feedback_generic' => (bool) $this->getInstantFeedbackGenericEnabled(),
                        'instant_feedback_points' => (bool) $this->getInstantFeedbackPointsEnabled(),
                        'instant_feedback_solution' => (bool) $this->getInstantFeedbackSolutionEnabled()
                    ],
                    'feedback_trigger' => (string) ($this->getForceInstantFeedbackOnNextQuestion() ?
                        '1' : '0')
                ]
            );
        }

        if (!$environment['participant_data_exists']) {
            return $instant_feedback;
        }

        return $instant_feedback->withDisabled(true);
    }

    private function getSubInputInstantFeedback(
        \ilLanguage $lng,
        FieldFactory $f
    ): array {
        $feedback_options = [
            'instant_feedback_points' => $f->checkbox(
                $lng->txt('tst_instant_feedback_results'),
                $lng->txt('tst_instant_feedback_results_desc')
            ),
            'instant_feedback_generic' => $f->checkbox(
                $lng->txt('tst_instant_feedback_answer_generic'),
                $lng->txt('tst_instant_feedback_answer_specific_desc')
            ),
            'instant_feedback_specific' => $f->checkbox(
                $lng->txt('tst_instant_feedback_answer_specific'),
                $lng->txt('tst_instant_feedback_answer_specific_desc')
            ),
            'instant_feedback_solution' => $f->checkbox(
                $lng->txt('tst_instant_feedback_solution'),
                $lng->txt('tst_instant_feedback_solution_desc')
            )
        ];

        $sub_inputs_feedback['enabled_feedback_types'] = $f->group(
            $feedback_options,
            $lng->txt('tst_instant_feedback_contents')
        )->withRequired(true);

        $sub_inputs_feedback['feedback_trigger'] = $f->radio(
            $lng->txt('tst_instant_feedback_trigger')
        )->withOption(
            '0',
            $lng->txt('tst_instant_feedback_trigger_manual'),
            $lng->txt('tst_instant_feedback_trigger_manual_desc')
        )->withOption(
            '1',
            $lng->txt('tst_instant_feedback_trigger_forced'),
            $lng->txt('tst_instant_feedback_trigger_forced_desc')
        );

        return $sub_inputs_feedback;
    }

    private function getInputLockAnswers(
        \ilLanguage $lng,
        FieldFactory $f,
        Refinery $refinery,
        array $environment
    ): Radio {
        $lock_answers = $f->radio(
            $lng->txt('tst_answer_fixation_handling')
        )->withOption(
            self::ANSWER_FIXATION_NONE,
            $lng->txt('tst_answer_fixation_none'),
            $lng->txt('tst_answer_fixation_none_desc')
        )->withOption(
            self::ANSWER_FIXATION_ON_INSTANT_FEEDBACK,
            $lng->txt('tst_answer_fixation_on_instant_feedback'),
            $lng->txt('tst_answer_fixation_on_instant_feedback_desc')
        )->withOption(
            self::ANSWER_FIXATION_ON_FOLLOWUP_QUESTION,
            $lng->txt('tst_answer_fixation_on_followup_question'),
            $lng->txt('tst_answer_fixation_on_followup_question_desc')
        )->withOption(
            self::ANSWER_FIXATION_ON_IFB_OR_FUQST,
            $lng->txt('tst_answer_fixation_on_instantfb_or_followupqst'),
            $lng->txt('tst_answer_fixation_on_instantfb_or_followupqst_desc')
        )->withValue(
            $this->getAnswerFixationSettingsAsFormValue()
        )->withAdditionalTransformation($this->getTransformationLockAnswers($refinery));

        if (!$environment['participant_data_exists']) {
            return $lock_answers;
        }

        return $lock_answers->withDisabled(true);
    }

    private function getTransformationLockAnswers(Refinery $refinery): Transformation
    {
        return $refinery->custom()->transformation(
            static function (?string $v): array {
                if ($v === null || $v === self::ANSWER_FIXATION_NONE) {
                    return [
                        'lock_answer_on_instant_feedback' => false,
                        'lock_answer_on_next_question' => false
                    ];
                }

                if ($v === self::ANSWER_FIXATION_ON_INSTANT_FEEDBACK) {
                    return [
                        'lock_answer_on_instant_feedback' => true,
                        'lock_answer_on_next_question' => false
                    ];
                }
                if ($v === self::ANSWER_FIXATION_ON_FOLLOWUP_QUESTION) {
                    return [
                        'lock_answer_on_instant_feedback' => false,
                        'lock_answer_on_next_question' => true
                    ];
                }

                return [
                    'lock_answer_on_instant_feedback' => true,
                    'lock_answer_on_next_question' => true
                ];
            }
        );
    }

    private function getConstraintsSectionQuestionBehaviour(
        \ilLanguage $lng,
        Refinery $refinery
    ): Generator {
        yield from [
            $refinery->custom()->constraint(
                function ($vs): bool {
                    if ($vs['enable_compulsory_questions'] === true
                        && (
                            $vs['lock_answers']['lock_answer_on_instant_feedback']
                            || $vs['lock_answers']['lock_answer_on_next_question']
                        )
                    ) {
                        return false;
                    }
                    return true;
                },
                $lng->txt('tst_settings_conflict_compulsory_and_lock')
            ),
             $refinery->custom()->constraint(
                 function ($vs): bool {
                     if ($vs['shuffle_questions'] === true
                         && $vs['lock_answers']['lock_answer_on_next_question']) {
                         return false;
                     }
                     return true;
                 },
                 $lng->txt('tst_settings_conflict_shuffle_and_lock')
             )
        ];
    }

    public function toStorage(): array
    {
        return [
            'title_output' => ['integer', $this->getQuestionTitleOutputMode()],
            'autosave' => ['integer', (int) $this->getAutosaveEnabled()],
            'autosave_ival' => ['integer', $this->getAutosaveInterval()],
            'shuffle_questions' => ['integer', (int) $this->getShuffleQuestions()],
            'offer_question_hints' => ['integer', (int) $this->getQuestionHintsEnabled()],
            'answer_feedback_points' => ['integer', (int) $this->getInstantFeedbackPointsEnabled()],
            'answer_feedback' => ['integer', (int) $this->getInstantFeedbackGenericEnabled()],
            'specific_feedback' => ['integer', (int) $this->getInstantFeedbackSpecificEnabled()],
            'instant_verification' => ['integer', (int) $this->getInstantFeedbackSolutionEnabled()],
            'force_inst_fb' => ['integer', (int) $this->getForceInstantFeedbackOnNextQuestion()],
            'inst_fb_answer_fixation' => ['integer', (int) $this->getLockAnswerOnInstantFeedbackEnabled()],
            'follow_qst_answer_fixation' => ['integer', (int) $this->getLockAnswerOnNextQuestionEnabled()],
            'obligations_enabled' => ['integer', (int) $this->getCompulsoryQuestionsEnabled()]
        ];
    }

    public function getQuestionTitleOutputMode(): int
    {
        return $this->question_title_output_mode;
    }
    public function withQuestionTitleOutputMode(int $question_title_output_mode): self
    {
        $clone = clone $this;
        $clone->question_title_output_mode = $question_title_output_mode;
        return $clone;
    }

    public function getAutosaveEnabled(): bool
    {
        return $this->autosave_enabled;
    }
    public function withAutosaveEnabled(bool $autosave_enabled): self
    {
        $clone = clone $this;
        $clone->autosave_enabled = $autosave_enabled;
        return $clone;
    }

    public function getAutosaveInterval(): int
    {
        return $this->autosave_interval;
    }
    public function withAutosaveInterval(int $autosave_interval): self
    {
        $clone = clone $this;
        $clone->autosave_interval = $autosave_interval;
        return $clone;
    }

    public function getShuffleQuestions(): bool
    {
        return $this->shuffle_questions;
    }
    public function withShuffleQuestions(bool $shuffle_questions): self
    {
        $clone = clone $this;
        $clone->shuffle_questions = $shuffle_questions;
        return $clone;
    }

    public function getQuestionHintsEnabled(): bool
    {
        return $this->question_hints_enabled;
    }
    public function withQuestionHintsEnabled(bool $question_hints_enabled): self
    {
        $clone = clone $this;
        $clone->question_hints_enabled = $question_hints_enabled;
        return $clone;
    }

    public function getInstantFeedbackPointsEnabled(): bool
    {
        return $this->instant_feedback_points_enabled;
    }
    public function withInstantFeedbackPointsEnabled(bool $instant_feedback_points_enabled): self
    {
        $clone = clone $this;
        $clone->instant_feedback_points_enabled = $instant_feedback_points_enabled;
        return $clone;
    }
    public function getInstantFeedbackGenericEnabled(): bool
    {
        return $this->instant_feedback_generic_enabled;
    }
    public function withInstantFeedbackGenericEnabled(bool $instant_feedback_generic_enabled): self
    {
        $clone = clone $this;
        $clone->instant_feedback_generic_enabled = $instant_feedback_generic_enabled;
        return $clone;
    }
    public function getInstantFeedbackSpecificEnabled(): bool
    {
        return $this->instant_feedback_specific_enabled;
    }
    public function withInstantFeedbackSpecificEnabled(bool $instant_feedback_specific_enabled): self
    {
        $clone = clone $this;
        $clone->instant_feedback_specific_enabled = $instant_feedback_specific_enabled;
        return $clone;
    }
    public function getInstantFeedbackSolutionEnabled(): bool
    {
        return $this->instant_feedback_solution_enabled;
    }
    public function withInstantFeedbackSolutionEnabled(bool $instant_feedback_solution_enabled): self
    {
        $clone = clone $this;
        $clone->instant_feedback_solution_enabled = $instant_feedback_solution_enabled;
        return $clone;
    }
    private function isAnyInstantFeedbackOptionEnabled(): bool
    {
        return $this->getInstantFeedbackPointsEnabled()
            || $this->getInstantFeedbackGenericEnabled()
            || $this->getInstantFeedbackSpecificEnabled()
            || $this->getInstantFeedbackSolutionEnabled();
    }

    public function getForceInstantFeedbackOnNextQuestion(): bool
    {
        return $this->force_instant_feedback_on_next_question;
    }
    public function withForceInstantFeedbackOnNextQuestion(bool $force_instant_feedback_on_next_question): self
    {
        $clone = clone $this;
        $clone->force_instant_feedback_on_next_question = $force_instant_feedback_on_next_question;
        return $clone;
    }

    public function getLockAnswerOnInstantFeedbackEnabled(): bool
    {
        return $this->lock_answer_on_instant_feedback;
    }
    public function getLockAnswerOnNextQuestionEnabled(): bool
    {
        return $this->lock_answer_on_next_question;
    }
    private function getAnswerFixationSettingsAsFormValue(): string
    {
        if ($this->getLockAnswerOnInstantFeedbackEnabled()
            && $this->getLockAnswerOnNextQuestionEnabled()) {
            return self::ANSWER_FIXATION_ON_IFB_OR_FUQST;
        }

        if ($this->getLockAnswerOnInstantFeedbackEnabled()) {
            return self::ANSWER_FIXATION_ON_INSTANT_FEEDBACK;
        }

        if ($this->getLockAnswerOnNextQuestionEnabled()) {
            return self::ANSWER_FIXATION_ON_FOLLOWUP_QUESTION;
        }

        return self::ANSWER_FIXATION_NONE;
    }
    public function withLockAnswerOnInstantFeedbackEnabled(bool $lock_answer_on_instant_feedback): self
    {
        $clone = clone $this;
        $clone->lock_answer_on_instant_feedback = $lock_answer_on_instant_feedback;
        return $clone;
    }
    public function withLockAnswerOnNextQuestionEnabled(bool $lock_answer_on_next_question): self
    {
        $clone = clone $this;
        $clone->lock_answer_on_next_question = $lock_answer_on_next_question;
        return $clone;
    }

    public function getCompulsoryQuestionsEnabled(): bool
    {
        return $this->compulsory_questions_enabled;
    }
    public function withCompulsoryQuestionsEnabled(bool $compulsory_questions_enabled): self
    {
        $clone = clone $this;
        $clone->compulsory_questions_enabled = $compulsory_questions_enabled;
        return $clone;
    }
}
