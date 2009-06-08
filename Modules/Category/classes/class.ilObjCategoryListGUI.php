<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

include_once "./classes/class.ilObjectListGUI.php";

/**
* Class ilObjCategoryListGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @ingroup ModulesCategory
*/
class ilObjCategoryListGUI extends ilObjectListGUI
{
	/**
	* constructor
	*
	*/
	function ilObjCategoryListGUI()
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
		$this->subscribe_enabled = true;
		$this->link_enabled = false;
		$this->payment_enabled = false;
		$this->info_screen_enabled = true;
		$this->type = "cat";
		$this->gui_class_name = "ilobjcategorygui";

		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDSubstitution.php');
		$this->substitutions = ilAdvancedMDSubstitution::_getInstanceByObjectType($this->type);
		if($this->substitutions->isActive())
		{
			$this->substitutions_enabled = true;
		}

		// general commands array
		include_once('./Modules/Category/classes/class.ilObjCategoryAccess.php');
		$this->commands = ilObjCategoryAccess::_getCommands();
	}

	/**
	* Get command target frame.
	*
	* Overwrite this method if link frame is not current frame
	*
	* @param	string		$a_cmd			command
	*
	* @return	string		command target frame
	*/
	function getCommandFrame($a_cmd)
	{
		// BEGIN WebDAV
		return ($a_cmd == 'mount_webfolder') ? '_blank' : parent::getCommandFrame($a_cmd);
		// END WebDAV
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
		// BEGIN WebDAV
		switch ($a_cmd) 
		{
			case 'mount_webfolder' :
				require_once ('Services/WebDAV/classes/class.ilDAVActivationChecker.php');
				if (ilDAVActivationChecker::_isActive())
				{
					require_once ('Services/WebDAV/classes/class.ilDAVServer.php');
					$davServer = new ilDAVServer();
					
					// FIXME: The following is a very dirty, ugly trick. 
					//        To mount URI needs to be put into two attributes:
					//        href and folder. This hack returns both attributes
					//        like this:  http://...mount_uri..." folder="http://...folder_uri...
					$cmd_link = $davServer->getMountURI($this->ref_id).
								'" folder="'.$davServer->getFolderURI($this->ref_id);
				}
				break;
			default :
				// separate method for this line
				$cmd_link = "repository.php?ref_id=".$this->ref_id."&cmd=$a_cmd";
				break;
		}
		// END WebDAV

		return $cmd_link;
	}
	
} // END class.ilObjCategoryGUI
?>
