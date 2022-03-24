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
