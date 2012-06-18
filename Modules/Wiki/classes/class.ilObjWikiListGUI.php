<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once "Services/Object/classes/class.ilObjectListGUI.php";

/**
* ListGUI class for wiki objects.
*
* @author 	Alex Killing <alex.killing@gmx.de>
* @version	$Id$
*
* @ingroup ModulesWiki
*/
class ilObjWikiListGUI extends ilObjectListGUI
{
	/**
	* constructor
	*/
	function ilObjWikiListGUI()
	{
		$this->ilObjectListGUI();
	}

	/**
	* initialisation
	*/
	function init()
	{
		$this->copy_enabled = true;
		$this->delete_enabled = true;
		$this->cut_enabled = true;
		$this->subscribe_enabled = true;
		$this->link_enabled = true;
		$this->payment_enabled = false;
		$this->info_screen_enabled = true;
		$this->type = "wiki";
		$this->gui_class_name = "ilobjwikigui";
		
		// general commands array
		include_once('./Modules/Wiki/classes/class.ilObjWikiAccess.php');
		$this->commands = ilObjWikiAccess::_getCommands();
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

		include_once("./Modules/Wiki/classes/class.ilObjWikiAccess.php");

		if (!ilObjWikiAccess::_lookupOnline($this->obj_id))
		{
			$props[] = array("alert" => true, "property" => $lng->txt("status"),
				"value" => $lng->txt("offline"));
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
		switch($a_cmd)
		{
			case 'downloadFile':
				$cmd_link = "ilias.php?baseClass=ilWikiHandlerGUI".
					"&amp;cmdClass=ilwikipagegui&amp;ref_id=".$this->ref_id.
					"&amp;cmd=downloadFile&amp;file_id=".$this->getChildId();
				break;
			
			default:
				// separate method for this line
				$cmd_link = "ilias.php?baseClass=ilWikiHandlerGUI&ref_id=".$this->ref_id."&cmd=$a_cmd";
				break;

		}
		

		return $cmd_link;
	}

	function setChildId($a_child_id)
	{
		$this->child_id = $a_child_id;
	}
	function getChildId()
	{
		return $this->child_id;
	}


} // END class.ilObjWikiListGUI
?>
