<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Object/classes/class.ilObjectListGUI.php";

/**
* Class ilObjPollListGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* $Id: class.ilObjRootFolderListGUI.php 23764 2010-05-06 15:11:30Z smeyer $
*
* @extends ilObjectListGUI
*/
class ilObjPollListGUI extends ilObjectListGUI
{
	/**
	* initialisation
	*/
	function init()
	{
		$this->copy_enabled = false;
		$this->delete_enabled = true;
		$this->cut_enabled = false;
		$this->subscribe_enabled = false;
		$this->link_enabled = false;
		$this->payment_enabled = false;
		$this->info_screen_enabled = true;
		$this->type = "poll";
		$this->gui_class_name = "ilobjpollgui";

		// general commands array
		include_once('./Modules/Poll/classes/class.ilObjPollAccess.php');
		$this->commands = ilObjPollAccess::_getCommands();
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
		global $lng;

		// BEGIN WebDAV: Get parent properties
		// BEGIN ChangeEvent: Get parent properties
		$props = parent::getProperties();
		// END ChangeEvent: Get parent properties
		// END WebDAV: Get parent properties

		// offline
		include_once 'Modules/Poll/classes/class.ilObjPollAccess.php';
		if(!ilObjPollAccess::_lookupOnline($this->obj_id))
		{
			$props[] = array("alert" => true, "property" => $lng->txt("status"),
				"value" => $lng->txt("offline"));
		}

		return $props;
	}
}

?>