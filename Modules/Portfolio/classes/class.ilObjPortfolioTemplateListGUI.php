<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjPortfolioTemplateListGUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
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
		$this->info_screen_enabled = true;
		$this->type = "prtt";
		$this->gui_class_name = "ilobjportfoliotemplategui";

		// general commands array
		$this->commands = ilObjPortfolioTemplateAccess::_getCommands();
	}
	
	public function getProperties()
	{
		$lng = $this->lng;

		$props = array();

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