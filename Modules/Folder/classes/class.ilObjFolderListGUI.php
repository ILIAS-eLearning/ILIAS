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


/**
* Class ilObjFolderListGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @extends ilObjectListGUI
*/


include_once "./classes/class.ilObjectListGUI.php";

class ilObjFolderListGUI extends ilObjectListGUI
{
	/**
	* constructor
	*
	*/
	function ilObjFolderListGUI()
	{
		$this->ilObjectListGUI();
	}

	/**
	* initialisation
	*/
	function init()
	{
		$this->delete_enabled = true;
		$this->cut_enabled = true;
		$this->subscribe_enabled = true;
		$this->link_enabled = false;
		$this->payment_enabled = false;
		$this->type = "fold";
		$this->gui_class_name = "ilobjfoldergui";

		// general commands array
		include_once('./Modules/Folder/classes/class.ilObjFolderAccess.php');
		$this->commands = ilObjFolderAccess::_getCommands();
	}

	// BEGIN WebDAV: Get parent properties
	// BEGIN ChangeEvent: Get parent properties
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
		global $lng, $ilUser, $ilias;

		//$props = array();
		$props = parent::getProperties();

		return $props;
	}
	// END ChangeEvent: Get parent properties
	// END WebDAV: Get parent properties

	/**
	* Get command link url.
	*
	* @param	int			$a_ref_id		reference id
	* @param	string		$a_cmd			command
	*
	*/
	function getCommandLink($a_cmd)
	{
		// BEGIN WebDAV: Mount webfolder.
		switch ($a_cmd) 
		{
			case 'mount_webfolder' :
				require_once('Services/WebDAV/classes/class.ilDAVServer.php');
				if (ilDAVServer::_isActive())
				{
					$davServer = new ilDAVServer();
					
					// XXX: The following is a very dirty, ugly trick. 
					//        To mount URI needs to be put into two attributes:
					//        href and folder. This hack returns both attributes
					//        like this:  http://...mount_uri..." folder="http://...folder_uri...
					$cmd_link = $davServer->getMountURI($this->ref_id, $this->title, $this->parent).
								'" folder="'.$davServer->getFolderURI($this->ref_id, $this->title, $this->parent);
					break;
				} // Fall through, when plugin is inactive.
			default :
				// separate method for this line
				$cmd_link = "repository.php?ref_id=".$this->ref_id."&cmd=$a_cmd";
				break;
		}
		
		return $cmd_link;
		// END WebDAV: Mount Webfolder
	}

	// BEGIN WebDAV: mount_webfolder in _blank frame
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
		require_once('Services/WebDAV/classes/class.ilDAVServer.php');
		if (ilDAVServer::_isActive())
		{
			return ($a_cmd == 'mount_webfolder') ? '_blank' : '';
		}
		else
		{
			return '';
		}
	}
	// END WebDAV: mount_webfolder in _blank frame


} // END class.ilObjFolderListGUI
?>
