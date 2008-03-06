<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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

include_once "classes/class.ilObjectListGUI.php";

/**
* Class ilObjFileListGUI
*
* @author 		Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @ingroup ModulesFile
*/
class ilObjFileListGUI extends ilObjectListGUI
{
	/**
	* constructor
	*
	*/
	function ilObjFileListGUI()
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
		$this->link_enabled = true;
		$this->payment_enabled = true;
		$this->info_screen_enabled = true;
		$this->type = "file";
		$this->gui_class_name = "ilobjfilegui";

		// general commands array
		include_once('./Modules/File/classes/class.ilObjFileAccess.php');
		$this->commands = ilObjFileAccess::_getCommands();
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
			// BEGIN WebDAV: View inline objects in a blank window
			case 'sendfile' :
				require_once('class.ilObjFileAccess.php');
				if (ilObjFileAccess::_isFileInline($this->title))
				{
					$frame = '_blank';
				}
				break;
			// END WebDAV View inline objects in a blank window

			case "":
				$frame = ilFrameTargetInfo::_getFrame("RepositoryContent");
				break;

			default:
		}

		return $frame;
	}

	// BEGIN WebDAV: getIconImageType.
	/**
	* Returns the icon image type.
	* For most objects, this is same as the object type, e.g. 'cat','fold'.
	* We can return here other values, to express a specific state of an object,
	* e.g. 'crs_offline", and/or to express a specific kind of object, e.g.
	* 'file_inline'.
	*/
	function getIconImageType() 
	{
		include_once('class.ilObjFileAccess.php');
		
		return ilObjFileAccess::_isFileInline($this->title) ? $this->type.'_inline' : $this->type;
	}
	// END WebDAV: getIconImageType.

	// BEGIN WebDAV: Suppress filename extension from title.
	/**
	 * getTitle overwritten in class.ilObjLinkResourceList.php 
	 *
	 * @return string title
	 */
	function getTitle()
	{
		// Remove filename extension from title
		return preg_replace('/\\.[a-z0-9]+\\z/i','', $this->title);
	}
	// END WebDAV: Suppress filename extension from title.


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

		// BEGIN WebDAV: Get parent properties
		$props = parent::getProperties();
		// END WebDAV: Get parent properties

		// to do: implement extra smaller file info object
		include_once("./Modules/File/classes/class.ilObjFileAccess.php");

		// BEGIN WebDAV: Only display relevant information.
		$props[] = array("alert" => false, "property" => $lng->txt("type"),
			"value" => ilObjFileAccess::_lookupSuffix($this->obj_id),
			'propertyNameVisible' => false
			);
		$props[] = array("alert" => false, "property" => $lng->txt("size"),
			"value" => ilObjFileAccess::_lookupFileSize($this->obj_id, true),
			'propertyNameVisible' => false);
		$version = ilObjFileAccess::_lookupVersion($this->obj_id);
		if ($version > 1)
		{
			$props[] = array("alert" => false, "property" => $lng->txt("version"),
				"value" => $version);
		}
		$props[] = array("alert" => false, "property" => $lng->txt("last_update"),
			"value" => ilObject::_lookupLastUpdate($this->obj_id, true),
			'propertyNameVisible' => false);
		// END WebDAV: Only display relevant information.

		return $props;
	}


	/**
	* Get command link url.
	*
	* @param	int			$a_ref_id		reference id
	* @param	string		$a_cmd			command
	*
	*/
	/*
	function getCommandLink($a_cmd)
	{
		// separate method for this line
		$cmd_link = "repo.php?ref_id=".$this->ref_id."&cmd=$a_cmd";

		return $cmd_link;
	}*/



} // END class.ilObjFileListGUI
?>
