<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilObjExerciseVerificationListGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* $Id: class.ilObjFolderListGUI.php 26089 2010-10-20 08:08:05Z smeyer $
*
* @extends ilObjectListGUI
*/

include_once "Services/Object/classes/class.ilObjectListGUI.php";

class ilObjExerciseVerificationListGUI extends ilObjectListGUI
{
	/**
	* initialisation
	*/
	function init()
	{
		$this->delete_enabled = true;
		$this->cut_enabled = true;
		$this->copy_enabled = true;
		$this->subscribe_enabled = false;
		$this->link_enabled = false;
		$this->payment_enabled = false;
		$this->info_screen_enabled = false;
		$this->type = "excv";
		$this->gui_class_name = "ilobjexerciseverificationgui";

		// general commands array
		include_once('./Modules/Exercise/classes/class.ilObjExerciseVerificationAccess.php');
		$this->commands = ilObjExerciseVerificationAccess::_getCommands();
	}
	
	function getProperties()
	{
		global $lng;
		
		return array(
			array("alert" => false, "property" => $lng->txt("type"),
				"value" => $lng->txt("wsp_list_excv"))			
		);
	}
	
} 

?>