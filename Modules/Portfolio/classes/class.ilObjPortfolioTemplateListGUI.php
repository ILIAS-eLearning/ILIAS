<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Object/classes/class.ilObjectListGUI.php";

/**
* Class ilObjPortfolioTemplateListGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* $Id: class.ilObjRootFolderListGUI.php 23764 2010-05-06 15:11:30Z smeyer $
*
* @extends ilObjectListGUI
*/
class ilObjPortfolioTemplateListGUI extends ilObjectListGUI
{
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
		$this->type = "prtt";
		$this->gui_class_name = "ilobjportfoliotemplategui";

		// general commands array
		include_once('./Modules/Portfolio/classes/class.ilObjPortfolioTemplateAccess.php');
		$this->commands = ilObjPortfolioTemplateAccess::_getCommands();
	}
	
	public function getProperties()
	{
		global $lng;

		$props = array();

		include_once("./Modules/Portfolio/classes/class.ilObjPortfolioTemplateAccess.php");
		if(!ilObjPortfolioTemplateAccess::_lookupOnline($this->obj_id))
		{
			$props[] = array(
				"alert" => true, 
				"property" => $lng->txt("status"),
				"value" => $lng->txt("offline")
			);
		}

		return $props;
	}
}

?>