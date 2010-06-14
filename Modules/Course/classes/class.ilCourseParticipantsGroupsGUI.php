<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Modules/Course/classes/class.ilCourseParticipantsGroupsTableGUI.php";

/**
* Class ilCourseParticipantsGroupsGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* $Id: class.ilObjCourseGUI.php 24234 2010-06-14 12:35:45Z smeyer $
*
* @ilCtrl_Calls ilCourseParticipantsGroupsGUI:
*
*/
class ilCourseParticipantsGroupsGUI
{
	function __construct($a_ref_id)
	{
	  $this->ref_id = $a_ref_id;
	}

	function executeCommand()
	{
		$this->show();
	}

	function show()
	{
		global $tpl;
		
		$tbl_gui = new ilCourseParticipantsGroupsTableGUI($this, "show", $this->ref_id);
		$tpl->setContent($tbl_gui->getHTML());
	}
}

?>