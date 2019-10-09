<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilObjBookingPoolListGUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * $Id: class.ilObjCategoryListGUI.php 23764 2010-05-06 15:11:30Z smeyer $
 *
 * @ingroup ModulesBookingManager
 */
class ilObjBookingPoolListGUI extends ilObjectListGUI
{
	/**
	* constructor
	*/
	function __construct()
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->lng = $DIC->language();
		parent::__construct();
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
		$this->info_screen_enabled = true;
		$this->type = "book";
		$this->gui_class_name = "ilobjbookingpoolgui";

		// general commands array
		$this->commands = ilObjBookingPoolAccess::_getCommands();
	}

	/**
	* Get command target frame.
	*
	* Overwrite this method if link frame is not current frame
	*
	* @param	string		$a_cmd			command
	* @return	string		command target frame
	*/
	function getCommandFrame($a_cmd)
	{
		return parent::getCommandFrame($a_cmd);
	}

	/**
	* Get command link url.
	*
	* @param	int			$a_ref_id		reference id
	* @param	string		$a_cmd			command
	*/
	function getCommandLink($a_cmd)
	{
		$ilCtrl = $this->ctrl;
		
		switch ($a_cmd) 
		{
			default :
				$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->ref_id);
				$cmd_link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", $a_cmd);
				$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", (int) $_GET["ref_id"]);
				break;
		}

		return $cmd_link;
	}
	
	function getProperties()
	{
		$lng = $this->lng;
		
		// #11193

		$props = array();

		if (!ilObjBookingPool::_lookupOnline($this->obj_id))
		{
			$props[] = array("alert" => true, "property" => $lng->txt("status"),
				"value" => $lng->txt("offline"));
		}
		return $props;
	}
}

?>