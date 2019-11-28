<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* ListGUI class for exercise objects.
*
* @author 	Alex Killing <alex.killing@gmx.de>
*
* @ingroup ModulesExercise
*/
class ilObjExerciseListGUI extends ilObjectListGUI
{
	/**
	* initialisation
	*/
	function init()
	{
		$this->static_link_enabled = true;
		$this->delete_enabled = true;
		$this->cut_enabled = true;
		$this->copy_enabled = true;
		$this->subscribe_enabled = true;
		$this->link_enabled = true;
		$this->info_screen_enabled = true;
		$this->type = "exc";
		$this->gui_class_name = "ilobjexercisegui";

		$this->substitutions = ilAdvancedMDSubstitution::_getInstanceByObjectType($this->type);
		if($this->substitutions->isActive())
		{
			$this->substitutions_enabled = true;
		}
		
		// general commands array
		$this->commands = ilObjExerciseAccess::_getCommands();
	}



	/**
	* Get command target frame
	*
	* @param	string		$a_cmd			command
	*
	* @return	string		command target frame
	*/
	function getCommandFrame($a_cmd)
	{
		switch($a_cmd)
		{
			default:
				$frame = ilFrameTargetInfo::_getFrame("MainContent");
				break;
		}

		return $frame;
	}



	/**
	* Get item properties
	*
	* @return	array		array of property arrays:
	*						"alert" (boolean) => display as an alert property (usually in red)
	*						"property" (string) => property name
	*						"value" (string) => property value
	*/
	function getProperties()
	{
		$lng = $this->lng;
		$ilUser = $this->user;

		$props = array();
		$rem = ilObjExerciseAccess::_lookupRemainingWorkingTimeString($this->obj_id);		
		if ($rem["mtime"] != "")
		{
			$props[] = array(
				"property" => ($rem["cnt"] > 1)
					? $this->lng->txt("exc_next_deadline")
					: $this->lng->txt("exc_next_deadline_single"),
				"value" => $rem["mtime"]
			);
		}

		return $props;
	}


	/**
	* Get command link url.
	*
	* @param	int			$a_ref_id		reference id
	* @param	string		$a_cmd			command
	*
	*/
	function getCommandLink($a_cmd)
	{
		// separate method for this line
		$cmd_link = "ilias.php?baseClass=ilExerciseHandlerGUI&ref_id=".$this->ref_id."&cmd=$a_cmd";

		return $cmd_link;
	}



} // END class.ilObjTestListGUI
?>
