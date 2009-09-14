<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "classes/class.ilObjectListGUI.php";

/**
* ListGUI class for exercise objects.
*
* @author 	Alex Killing <alex.killing@gmx.de>
* @version	$Id$
*
* @ingroup ModulesExercise
*/
class ilObjExerciseListGUI extends ilObjectListGUI
{
	/**
	* constructor
	*/
	function ilObjExerciseListGUI()
	{
		$this->ilObjectListGUI();
	}

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
		$this->payment_enabled = false;
		$this->info_screen_enabled = true;
		$this->type = "exc";
		$this->gui_class_name = "ilobjexercisegui";
		
		// general commands array
		include_once('./Modules/Exercise/classes/class.ilObjExerciseAccess.php');
		$this->commands = ilObjExerciseAccess::_getCommands();
	}


	/**
	* inititialize new item
	*
	* @param	int			$a_ref_id		reference id
	* @param	int			$a_obj_id		object id
	* @param	string		$a_title		title
	* @param	string		$a_description	description
	*/
	function initItem($a_ref_id, $a_obj_id, $a_title = "", $a_description = "")
	{
		parent::initItem($a_ref_id, $a_obj_id, $a_title, $a_description);
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
		global $lng, $ilUser;

		$props = array();
		$props[] = array(
			"property" => $this->lng->txt("exc_time_to_send"),
			"value" => ilObjExerciseAccess::_lookupRemainingWorkingTimeString($this->obj_id)
		);

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
