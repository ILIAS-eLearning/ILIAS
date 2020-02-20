<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types = 1);

use \ILIAS\UI\Component\Input\Field;
use \ILIAS\Refinery\Factory as Refinery;

class ilStudyProgrammeAutoMailSettings
{
    /**
     * @var bool
     */
    protected $send_re_assigned_mail;

    /**
     * @var int | null
     */
    protected $reminder_not_restarted_by_user_days;

    /**
     * @var int | null
     */
    protected $processing_ends_not_successful_days;

    public function __construct(
        bool $send_re_assigned_mail,
        ?int $reminder_not_restarted_by_user_days,
        ?int $processing_ends_not_successful_days
    ) {
        if (
            !is_null($reminder_not_restarted_by_user_days) &&
            1 > $reminder_not_restarted_by_user_days
        ) {
            throw new InvalidArgumentException(
                'Numbers less than 1 are not allowed'
            );
        }

        if (
            !is_null($processing_ends_not_successful_days) &&
            1 > $processing_ends_not_successful_days
        ) {
            throw new InvalidArgumentException(
                'Numbers less than 1 are not allowed'
            );
        }

        $this->send_re_assigned_mail = $send_re_assigned_mail;
        $this->reminder_not_restarted_by_user_days = $reminder_not_restarted_by_user_days;
        $this->processing_ends_not_successful_days = $processing_ends_not_successful_days;
    }

    public function getSendReAssignedMail() : bool
    {
        return $this->send_re_assigned_mail;
    }

    public function getReminderNotRestartedByUserDays() : ?int
    {
        return $this->reminder_not_restarted_by_user_days;
    }

    public function getProcessingEndsNotSuccessfulDays() : ?int
    {
        return $this->processing_ends_not_successful_days;
    }

    public function withSendReAssignedMail(bool $do_it) : \ilStudyProgrammeAutoMailSettings
    {
        $clone = clone $this;
        $clone->send_re_assigned_mail = $do_it;
        return $clone;
    }

    public function withReminderNotRestartedByUserDays(
        ?int $days
    ) : \ilStudyProgrammeAutoMailSettings {
        if (!is_null($days) && 1 > $days) {
            throw new InvalidArgumentException(
                'Numbers less than 1 are not allowed'
            );
        }

        $clone = clone $this;
        $clone->reminder_not_restarted_by_user_days = $days;
        return $clone;
    }

    public function withProcessingEndsNotSuccessfulDays(
        ?int $days
    ) : \ilStudyProgrammeAutoMailSettings {
        if (!is_null($days) && 1 > $days) {
            throw new InvalidArgumentException(
                'Numbers less than 1 are not allowed'
            );
        }
        $clone = clone $this;
        $clone->processing_ends_not_successful_days = $days;
        return $clone;
    }

    public function toFormInput(Field\Factory $input, \ilLanguage $ilLng, Refinery $refinery) : Field\Input
    {
        return $input->section(
            [
                "send_re_assigned_mail" => $input->checkbox(
                    $ilLng->txt("send_re_assigned_mail"),
                    $ilLng->txt('send_re_assigned_mail_info')
                )
                ->withValue($this->getSendReAssignedMail()),

                "prg_user_not_restarted_time_input" => $input->optionalGroup(
                    [$input->numeric(
                        $ilLng->txt('prg_user_not_restarted_time_input'),
                        $ilLng->txt('prg_user_not_restarted_time_input_info')
                    )->withAdditionalTransformation($refinery->int()->isGreaterThan(0))],
                    $ilLng->txt("send_info_to_re_assign_mail"),
                    $ilLng->txt("send_info_to_re_assign_mail_info")
                )
                ->withValue($this->getReminderNotRestartedByUserDays() !== null ? [$this->getReminderNotRestartedByUserDays()] : null),

                "processing_ends_not_success" => $input->optionalGroup(
                    [$input->numeric(
                        $ilLng->txt('prg_processing_ends_no_success'),
                        $ilLng->txt('prg_processing_ends_no_success_info')
                    )->withAdditionalTransformation($refinery->int()->isGreaterThan(0))],
                    $ilLng->txt("send_risky_to_fail_mail"),
                    $ilLng->txt("send_risky_to_fail_mail_info")
                )
                ->withValue($this->getProcessingEndsNotSuccessfulDays() !== null ? [$this->getProcessingEndsNotSuccessfulDays()] : null)
            ],
            $ilLng->txt("prg_cron_job_configuration")
        )
        ->withAdditionalTransformation($refinery->custom()->transformation(function ($vals) {
            return new \ilStudyProgrammeAutoMailSettings(
                $vals["send_re_assigned_mail"],
                isset($vals["prg_user_not_restarted_time_input"]) ? (int) $vals["prg_user_not_restarted_time_input"][0] : null,
                isset($vals["processing_ends_not_success"]) ? (int) $vals["processing_ends_not_success"][0] : null
            );
        }));
    }
}
