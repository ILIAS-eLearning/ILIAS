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
use ILIAS\UI\Component\Input\Field\OptionalGroup;
use ILIAS\Refinery\Factory as Refinery;

class ilObjTestSettingsParticipantFunctionality extends TestSettings
{
    public function __construct(
        int $test_id,
        protected bool $use_previous_answers_allowed = false,
        protected bool $suspend_test_allowed = false,
        protected bool $postponed_questions_move_to_end = false,
        protected int $question_list_mode = 0,
        protected bool $question_marking_enabled = false
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
        $inputs['use_previous_answers'] = $f->checkbox(
            $lng->txt('tst_use_previous_answers'),
            $lng->txt('tst_use_previous_answers_description')
        )->withValue($this->getUsePreviousAnswerAllowed());

        $inputs['allow_suspend_test'] = $f->checkbox(
            $lng->txt('tst_show_cancel'),
            $lng->txt('tst_show_cancel_description')
        )->withValue($this->getSuspendTestAllowed());

        $inputs['postponed_questions_behaviour'] = $f->radio(
            $lng->txt('tst_postpone')
        )->withOption(
            '0',
            $lng->txt('tst_postpone_off'),
            $lng->txt('tst_postpone_off_desc'),
        )->withOption(
            '1',
            $lng->txt('tst_postpone_on'),
            $lng->txt('tst_postpone_on_desc'),
        )->withValue($this->getPostponedQuestionsMoveToEnd() ? '1' : '0')
            ->withAdditionalTransformation($refinery->kindlyTo()->bool());

        $inputs['enable_question_list'] = $this->getInputEnableQuestionList(
            $lng,
            $f,
            $refinery
        );

        $inputs['enable_question_marking'] = $f->checkbox(
            $lng->txt('question_marking'),
            $lng->txt('question_marking_description')
        )->withValue($this->getQuestionMarkingEnabled());

        return $f->section($inputs, $lng->txt('tst_sequence_properties'));
    }

    private function getInputEnableQuestionList(
        \ilLanguage $lng,
        FieldFactory $f,
        Refinery $refinery
    ): OptionalGroup {
        $trafo = $refinery->custom()->transformation(
            static function (?array $vs): int {
                if ($vs === null) {
                    return 0;
                }

                $question_list_mode = 1;

                if ($vs['show_at_beginning'] === true) {
                    $question_list_mode += 2;
                }

                if ($vs['show_at_end'] === true) {
                    $question_list_mode += 4;
                }

                if ($vs['show_description'] === true) {
                    $question_list_mode += 8;
                }

                return $question_list_mode;
            }
        );

        $sub_inputs_question_list['show_at_beginning'] = $f->checkbox(
            $lng->txt('tst_list_of_questions_start')
        );
        $sub_inputs_question_list['show_at_end'] = $f->checkbox(
            $lng->txt('tst_list_of_questions_end')
        );
        $sub_inputs_question_list['show_description'] = $f->checkbox(
            $lng->txt('tst_list_of_questions_with_description')
        );

        $enable_question_list = $f->optionalGroup(
            $sub_inputs_question_list,
            $lng->txt('tst_show_summary'),
            $lng->txt('tst_show_summary_description')
        )->withValue(null)
            ->withAdditionalTransformation($trafo);

        if ($this->getQuestionListEnabled() === false) {
            return $enable_question_list;
        }

        return $enable_question_list->withValue(
            [
                'show_at_beginning' => $this->getShownQuestionListAtBeginning(),
                'show_at_end' => $this->getShownQuestionListAtEnd(),
                'show_description' => $this->getShowDescriptionInQuestionList()
            ]
        );
    }

    public function toStorage(): array
    {
        return [
            'use_previous_answers' => ['integer', (int) $this->getUsePreviousAnswerAllowed()],
            'show_cancel' => ['integer', (int) $this->getSuspendTestAllowed()],
            'sequence_settings' => ['integer', (int) $this->getPostponedQuestionsMoveToEnd()],
            'show_summary' => ['integer', $this->getQuestionListMode()],
            'show_marker' => ['integer', (int) $this->getQuestionMarkingEnabled()]
        ];
    }

    public function getUsePreviousAnswerAllowed(): bool
    {
        return $this->use_previous_answers_allowed;
    }
    public function withUsePreviousAnswerAllowed(bool $use_previous_answers_allowed): self
    {
        $clone = clone $this;
        $clone->use_previous_answers_allowed = $use_previous_answers_allowed;
        return $clone;
    }

    public function getSuspendTestAllowed(): bool
    {
        return $this->suspend_test_allowed;
    }
    public function withSuspendTestAllowed(bool $suspend_test_allowed): self
    {
        $clone = clone $this;
        $clone->suspend_test_allowed = $suspend_test_allowed;
        return $clone;
    }

    public function getPostponedQuestionsMoveToEnd(): bool
    {
        return $this->postponed_questions_move_to_end;
    }
    public function withPostponedQuestionsMoveToEnd(bool $postponed_questions_move_to_end): self
    {
        $clone = clone $this;
        $clone->postponed_questions_move_to_end = $postponed_questions_move_to_end;
        return $clone;
    }

    public function getQuestionListMode(): int
    {
        return $this->question_list_mode;
    }
    public function getQuestionListEnabled(): bool
    {
        if (($this->question_list_mode & 1) > 0) {
            return true;
        }

        return false;
    }
    public function getShownQuestionListAtBeginning(): bool
    {
        if (($this->question_list_mode & 2) > 0) {
            return true;
        }

        return false;
    }
    public function getShownQuestionListAtEnd(): bool
    {
        if (($this->question_list_mode & 4) > 0) {
            return true;
        }

        return false;
    }
    public function getShowDescriptionInQuestionList(): bool
    {
        if (($this->question_list_mode & 8) > 0) {
            return true;
        }

        return false;
    }
    public function withQuestionListMode(int $question_list_mode): self
    {
        $clone = clone $this;
        $clone->question_list_mode = $question_list_mode;
        return $clone;
    }

    public function getQuestionMarkingEnabled(): bool
    {
        return $this->question_marking_enabled;
    }
    public function withQuestionMarkingEnabled(bool $question_marking_enabled): self
    {
        $clone = clone $this;
        $clone->question_marking_enabled = $question_marking_enabled;
        return $clone;
    }
}
