<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Modules/Exercise/AssignmentTypes/GUI/classes/interface.ilExAssignmentTypeGUIInterface.php");
include_once("./Modules/Exercise/AssignmentTypes/GUI/traits/trait.ilExAssignmentTypeGUIBase.php");

/**
 * Team upload type gui implementations
 *
 * @author Alex Killing <killing@leifos.de>
 */
class ilExAssTypeUploadTeamGUI implements ilExAssignmentTypeGUIInterface
{
	use ilExAssignmentTypeGUIBase;

	/**
	 * @inheritdoc
	 */
	function addEditFormCustomProperties(ilPropertyFormGUI $form)
	{

	}

	/**
	 * @inheritdoc
	 */
	function importFormToAssignment(ilExAssignment $ass , ilPropertyFormGUI $form)
	{

	}

	/**
	 * @inheritdoc
	 */
	function getFormValuesArray(ilExAssignment $ass)
	{
		return [];
	}

	/**
	 * @inheritdoc
	 */
	function getOverviewContent(ilInfoScreenGUI $a_info, ilExSubmission $a_submission)
	{

	}

}