<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Object/classes/class.ilObjectListGUI.php";

/**
* Class ilObjBlogListGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* $Id: class.ilObjRootFolderListGUI.php 23764 2010-05-06 15:11:30Z smeyer $
*
* @extends ilObjectListGUI
*/
class ilObjBlogListGUI extends ilObjectListGUI
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
		$this->link_enabled = true; // #10498
		$this->payment_enabled = false;
		$this->info_screen_enabled = true;
		$this->type = "blog";
		$this->gui_class_name = "ilobjbloggui";

		// general commands array
		include_once('./Modules/Blog/classes/class.ilObjBlogAccess.php');
		$this->commands = ilObjBlogAccess::_getCommands();
	}
	
	public function getCommands()
	{
		$commands = parent::getCommands();
		
		// #10182 - handle edit and contribute
		$permissions = array();
		foreach($commands as $idx => $item)
		{
			if($item["lang_var"] == "edit" && $item["granted"])
			{
				$permissions[$item["permission"]] = $idx;						
			}
		}		
		if(sizeof($permissions) == 2)
		{
			unset($commands[$permissions["contribute"]]);
		}
		
		return $commands;
	}
}

?>