<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Base trait for ilExAssignmetnTypeGUI implementations
 *
 * @author killing@leifos.de
 * @ingroup ModulesExercise
 */
trait ilExAssignmentTypeGUIBase
{
    /**
     * @var ilExSubmission
     */
    protected $submission;

    /**
     * @var ilObjExercise
     */
    protected $exercise;

    /**
     * Set submission
     *
     * @param ilExSubmission $a_val submission
     */
    public function setSubmission(ilExSubmission $a_val)
    {
        $this->submission = $a_val;
    }

    /**
     * Get submission
     *
     * @return ilExSubmission submission
     */
    public function getSubmission()
    {
        return $this->submission;
    }

    /**
     * Set exercise
     *
     * @param ilObjExercise $a_val exercise
     */
    public function setExercise(ilObjExercise $a_val)
    {
        $this->exercise = $a_val;
    }

    /**
     * Get exercise
     *
     * @return ilObjExercise exercise
     */
    public function getExercise()
    {
        return $this->exercise;
    }
}
