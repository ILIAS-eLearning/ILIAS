<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Object/classes/class.ilObjectListGUI.php";

/**
* ListGUI implementation for Example object plugin. This one
* handles the presentation in container items (categories, courses, ...)
* together with the ...Access class.
*
* @author 		Alex Killing <alex.killing@gmx.de>
*/
abstract class ilObjectPluginListGUI extends ilObjectListGUI
{
	/**
	* initialisation
	*/
	final function init()
	{
		$this->delete_enabled = true;
		$this->cut_enabled = true;
		$this->subscribe_enabled = true;
		$this->link_enabled = true;
		$this->payment_enabled = false;
		$this->info_screen_enabled = true;
		$this->initType();
		$this->plugin =
			ilPlugin::getPluginObject(IL_COMP_SERVICE, "Repository", "robj",
				ilPlugin::lookupNameForId(IL_COMP_SERVICE, "Repository", "robj", $this->getType()));
		$this->gui_class_name = $this->getGuiClass();
		$this->commands = $this->initCommands();
	}
	
	abstract function getGuiClass();
	abstract function initCommands();
	
	/**
	* Set 
	*
	* @param	
	*/
	function setType($a_val)
	{
		$this->type = $a_val;
	}
	
	/**
	* Get type
	*
	* @return	type
	*/
	function getType()
	{
		return $this->type;
	}
	
	abstract function initType();

	/**
	* txt
	*/
	function txt($a_str)
	{
		return $this->plugin->txt($a_str);
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
		return ilFrameTargetInfo::_getFrame("MainContent");
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
		$cmd_link = "ilias.php?baseClass=ilObjPluginDispatchGUI&amp;".
			"cmd=forward&amp;ref_id=".$this->ref_id."&amp;forwardCmd=".$a_cmd;

		return $cmd_link;
	}
}
?>
