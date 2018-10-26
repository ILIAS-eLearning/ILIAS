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
	 * @param ilPropertyFormGUI $form
	 */
	function addEditFormCustomProperties(ilPropertyFormGUI $form);

	/**
	 * Get values from form and put them into assignment
	 * @param ilExAssignment $ass
	 * @param ilPropertyFormGUI $form
	 */
	function importFormToAssignment(ilExAssignment $ass , ilPropertyFormGUI $form);

	/**
	 * Get form values array from assignment
	 * @param ilExAssignment $ass
	 * @return array
	 */
	function getFormValuesArray(ilExAssignment $ass);

	/**
	 * Add overview content of submission to info screen object
	 * @param ilInfoScreenGUI $a_info
	 * @param ilExSubmission $a_submission
	 */
	function getOverviewContent(ilInfoScreenGUI $a_info, ilExSubmission $a_submission);

	/**
	 * Set submission
	 * @param ilExSubmission $a_submission
	 */
	function setSubmission(ilExSubmission $a_submission);

	/**
	 * Set exercise
	 * @param ilObjExercise $a_exercise
	 */
	function setExercise(ilObjExercise $a_exercise);

}