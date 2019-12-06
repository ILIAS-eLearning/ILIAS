<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjBlogListGUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
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
		$this->info_screen_enabled = true;
		$this->type = "blog";
		$this->gui_class_name = "ilobjbloggui";

		// general commands array
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