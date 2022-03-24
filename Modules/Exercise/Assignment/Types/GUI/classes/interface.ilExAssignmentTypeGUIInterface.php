<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface for assignment types
 *
 * Currently this interface contains GUI functions for different scenarios
 * (editing screen, assignment overview, ...)
 *
 * @author Alex Killing <killing@leifos.de>
 */
interface ilExAssignmentTypeGUIInterface
{
    /**
     * Add custom form properties to edit form
     */
    public function addEditFormCustomProperties(ilPropertyFormGUI $form);

    /**
     * Get values from form and put them into assignment
     */
    public function importFormToAssignment(ilExAssignment $ass, ilPropertyFormGUI $form);

    /**
     * Get form values array from assignment
     * @return array
     */
    public function getFormValuesArray(ilExAssignment $ass);

    /**
     * Add overview content of submission to info screen object
     */
    public function getOverviewContent(ilInfoScreenGUI $a_info, ilExSubmission $a_submission) : void;

    /**
     * Set submission
     */
    public function setSubmission(ilExSubmission $a_submission);

    /**
     * Set exercise
     */
    public function setExercise(ilObjExercise $a_exercise);
}
