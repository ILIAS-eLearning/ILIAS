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

namespace ILIAS\Exercise\Settings;

class Settings
{
    public function __construct(
        protected int $obj_id,
        protected string $instruction,
        protected int $time_stamp,
        protected string $pass_mode,
        protected int $nr_mandatory_random,
        protected int $pass_nr,
        protected bool $show_submissions,
        protected bool $compl_by_submission,
        protected int $certificate_visibility,
        protected int $tfeedback
    ) {
    }

    // Getter methods
    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function withObjId(int $id): self
    {
        $clone = clone $this;
        $clone->obj_id = $id;
        return $clone;
    }


    public function getInstruction(): string
    {
        return $this->instruction;
    }

    public function getTimeStamp(): int
    {
        return $this->time_stamp;
    }

    public function getPassMode(): string
    {
        return $this->pass_mode;
    }

    public function getNrMandatoryRandom(): int
    {
        return $this->nr_mandatory_random;
    }

    public function getPassNr(): int
    {
        return $this->pass_nr;
    }

    public function getShowSubmissions(): bool
    {
        return $this->show_submissions;
    }

    public function getCompletionBySubmission(): bool
    {
        return $this->compl_by_submission;
    }

    public function getCertificateVisibility(): int
    {
        return $this->certificate_visibility;
    }

    public function getTutorFeedback(): int
    {
        return $this->tfeedback;
    }

    public function hasTutorFeedbackText(): bool
    {
        return (bool) ($this->tfeedback & \ilObjExercise::TUTOR_FEEDBACK_TEXT);
    }

    public function hasTutorFeedbackMail(): bool
    {
        return (bool) ($this->tfeedback & \ilObjExercise::TUTOR_FEEDBACK_MAIL);
    }

    public function hasTutorFeedbackFile(): bool
    {
        return (bool) ($this->tfeedback & \ilObjExercise::TUTOR_FEEDBACK_FILE);
    }

}
