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

trait ilExAssignmentTypeGUIBase
{
    protected ilExSubmission $submission;
    protected ilObjExercise $exercise;

    protected int $ass_id = 0;
    protected int $user_id = 0;

    /**
     * Set submission
     *
     * @param ilExSubmission $a_val submission
     */
    public function setSubmission(ilExSubmission $a_val): void
    {
        $this->submission = $a_val;
        $this->ass_id = $a_val->getAssignment()->getId();
        $this->user_id = $a_val->getUserId();
    }

    public function getSubmission(): ilExSubmission
    {
        return $this->submission;
    }

    public function setExercise(ilObjExercise $a_val): void
    {
        $this->exercise = $a_val;
    }

    public function getExercise(): ilObjExercise
    {
        return $this->exercise;
    }
}
