<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Object/classes/class.ilObjectListGUI.php";

/**
* Class ilObjWorkspaceRootFolderListGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id: class.ilObjRootFolderListGUI.php 23764 2010-05-06 15:11:30Z smeyer $
*
* @extends ilObjectListGUI
*/
class ilObjWorkspaceRootFolderListGUI extends ilObjectListGUI
{
	/**
	* initialisation
	*/
	function init()
	{
		$this->copy_enabled = false;
		$this->delete_enabled = true;
		$this->cut_enabled = true;
		$this->subscribe_enabled = true;
		$this->link_enabled = false;
		$this->payment_enabled = false;
		$this->type = "root";
		$this->gui_class_name = "ilobjworkspacerootfoldergui";

		// general commands array
		include_once('./Modules/WorkspaceRootFolder/classes/class.ilObjWorkspaceRootFolderAccess.php');
		$this->commands = ilObjWorkspaceRootFolderAccess::_getCommands();
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
		// :TODO: ?!

		// separate method for this line
		$cmd_link = "repository.php?ref_id=".$this->ref_id."&cmd=$a_cmd";

		return $cmd_link;
	}


} // END class.ilObjWorkspaceRootFolderGUI
?>
