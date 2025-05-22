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

namespace ILIAS\Test\Settings\MainSettings;

use ILIAS\Test\Settings\TestSettings;
use ILIAS\Test\Logging\AdditionalInformationGenerator;

use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Component\Input\Field\OptionalGroup;
use ILIAS\Refinery\Factory as Refinery;

class SettingsParticipantFunctionality extends TestSettings
{
    public function __construct(
        int $test_id,
        protected bool $use_previous_answers_allowed = false,
        protected bool $suspend_test_allowed = false,
        protected bool $postponed_questions_move_to_end = false,
        protected int $usrpass_overview_mode = 0,
        protected bool $question_marking_enabled = false,
        protected bool $question_list_enabled = false
    ) {
        parent::__construct($test_id);
    }

    public function toForm(
        \ilLanguage $lng,
        FieldFactory $f,
        Refinery $refinery,
        ?array $environment = null
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
            $lng->txt('tst_postpone_off_desc')
        )->withOption(
            '1',
            $lng->txt('tst_postpone_on'),
            $lng->txt('tst_postpone_on_desc')
        )->withValue($this->getPostponedQuestionsMoveToEnd() ? '1' : '0')
            ->withAdditionalTransformation($refinery->kindlyTo()->bool());

        $inputs['enable_question_list'] = $f->checkbox(
            $lng->txt('tst_enable_questionlist'),
            $lng->txt('tst_enable_questionlist_description')
        )->withValue($this->getQuestionListEnabled());

        $inputs['usr_pass_overview'] = $this->getInputUsrPassOverview($lng, $f, $refinery);

        $inputs['enable_question_marking'] = $f->checkbox(
            $lng->txt('question_marking'),
            $lng->txt('question_marking_description')
        )->withValue($this->getQuestionMarkingEnabled());

        return $f->section($inputs, $lng->txt('tst_sequence_properties'));
    }

    private function getInputUsrPassOverview(
        \ilLanguage $lng,
        FieldFactory $f,
        Refinery $refinery
    ): OptionalGroup {
        $trafo = $refinery->custom()->transformation(
            static function (?array $vs): int {
                if ($vs === null) {
                    return 0;
                }

                $usrpass_overview_mode = 1;

                if ($vs['show_at_beginning'] === true) {
                    $usrpass_overview_mode += 2;
                }

                if ($vs['show_at_end'] === true) {
                    $usrpass_overview_mode += 4;
                }

                if ($vs['show_description'] === true) {
                    $usrpass_overview_mode += 8;
                }

                return $usrpass_overview_mode;
            }
        );

        $sub_inputs_usrpass_questionlist['show_at_beginning'] = $f->checkbox($lng->txt('tst_list_of_questions_start'));
        $sub_inputs_usrpass_questionlist['show_at_end'] = $f->checkbox($lng->txt('tst_list_of_questions_end'));
        $sub_inputs_usrpass_questionlist['show_description'] = $f->checkbox($lng->txt('tst_list_of_questions_with_description'));

        $enable_usrpass_questionlist = $f->optionalGroup(
            $sub_inputs_usrpass_questionlist,
            $lng->txt('tst_show_summary'),
            $lng->txt('tst_show_summary_description')
        )->withValue(null)
            ->withAdditionalTransformation($trafo);

        if ($this->getUsrPassOverviewEnabled() === false) {
            return $enable_usrpass_questionlist;
        }

        return $enable_usrpass_questionlist->withValue(
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
            'suspend_test_allowed' => ['integer', (int) $this->getSuspendTestAllowed()],
            'sequence_settings' => ['integer', (int) $this->getPostponedQuestionsMoveToEnd()],
            'usr_pass_overview_mode' => ['integer', $this->getUsrPassOverviewMode()],
            'show_marker' => ['integer', (int) $this->getQuestionMarkingEnabled()],
            'show_questionlist' => ['integer', $this->getQuestionListEnabled()]
        ];
    }

    public function toLog(AdditionalInformationGenerator $additional_info): array
    {
        $log_array = [
            AdditionalInformationGenerator::KEY_TEST_USE_PREVIOUS_ANSWERS_ENABELD => $additional_info
                ->getEnabledDisabledTagForBool($this->getUsePreviousAnswerAllowed()),
            AdditionalInformationGenerator::KEY_TEST_SUSPEND_ALLOWED => $additional_info
                ->getEnabledDisabledTagForBool($this->getSuspendTestAllowed()),
            AdditionalInformationGenerator::KEY_TEST_POSTPONED_MOVE_TO_END => $additional_info
                ->getEnabledDisabledTagForBool($this->getPostponedQuestionsMoveToEnd())
        ];

        $log_array[AdditionalInformationGenerator::KEY_TEST_OVERVIEW_ENABLED] = $additional_info
                ->getEnabledDisabledTagForBool($this->getUsrPassOverviewEnabled());
        if ($this->getUsrPassOverviewEnabled()) {
            $log_array[AdditionalInformationGenerator::KEY_TEST_OVERVIEW_SHOW_START] = $additional_info
                ->getEnabledDisabledTagForBool($this->getShownQuestionListAtBeginning());
            $log_array[AdditionalInformationGenerator::KEY_TEST_OVERVIEW_SHOW_END] = $additional_info
                ->getEnabledDisabledTagForBool($this->getShownQuestionListAtEnd());
            $log_array[AdditionalInformationGenerator::KEY_TEST_OVERVIEW_SHOW_DESCRIPTION] = $additional_info
                ->getEnabledDisabledTagForBool($this->getShowDescriptionInQuestionList());
        }

        $log_array[AdditionalInformationGenerator::KEY_TEST_QUESTION_MARKING_ENABLED] = $additional_info
                ->getEnabledDisabledTagForBool($this->getQuestionMarkingEnabled());
        $log_array[AdditionalInformationGenerator::KEY_TEST_QUESTION_LIST_ENABLED] = $additional_info
                ->getEnabledDisabledTagForBool($this->getQuestionListEnabled());
        return $log_array;
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

    public function getQuestionListEnabled(): bool
    {
        return $this->question_list_enabled;
    }
    public function withQuestionListEnabled(bool $question_list_enabled): self
    {
        $clone = clone $this;
        $clone->question_list_enabled = $question_list_enabled;
        return $clone;
    }

    public function getUsrPassOverviewMode(): int
    {
        return $this->usrpass_overview_mode;
    }

    public function withUsrPassOverviewMode(int $usrpass_overview_mode): self
    {
        $clone = clone $this;
        $clone->usrpass_overview_mode = $usrpass_overview_mode;
        return $clone;
    }

    public function getUsrPassOverviewEnabled(): bool
    {
        return ($this->usrpass_overview_mode & 1) > 0;
    }
    public function getShownQuestionListAtBeginning(): bool
    {
        return ($this->usrpass_overview_mode & 2) > 0;
    }
    public function getShownQuestionListAtEnd(): bool
    {
        return ($this->usrpass_overview_mode & 4) > 0;
    }

    public function getShowDescriptionInQuestionList(): bool
    {
        return ($this->usrpass_overview_mode & 8) > 0;
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
