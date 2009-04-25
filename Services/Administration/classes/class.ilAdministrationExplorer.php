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

/*
* Administration Explorer
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
*/

require_once("classes/class.ilExplorer.php");

class ilAdministrationExplorer extends ilExplorer
{

	/**
	 * id of root folder
	 * @var int root folder id
	 * @access private
	 */
	var $root_id;
	var $output;
	var $ctrl;
	/**
	* Constructor
	* @access	public
	* @param	string	scriptname
	* @param    int user_id
	*/
	function ilAdministrationExplorer($a_target)
	{
		global $tree,$ilCtrl,$lng;

		$this->ctrl = $ilCtrl;

		parent::ilExplorer($a_target);
		$this->tree = $tree;
		$this->root_id = $this->tree->readRootId();
		$this->order_column = "title";
		$this->setSessionExpandVariable("expand");
		$this->setTitle($lng->txt("overview"));
	}

	/**
	* overwritten method from base class
	* @access	public
	* @param	integer obj_id
	* @param	integer array options
	* @return	string
	*/
	function formatHeader(&$tpl, $a_obj_id,$a_option)
	{
		global $lng, $ilias, $objDefinition;
		
		if ($_GET["admin_mode"] == "settings")
		{
			return;
		}

		$tpl->setCurrentBlock("icon");
		$tpl->setVariable("ICON_IMAGE" , ilUtil::getImagePath("icon_root.gif"));
		$tpl->setVariable("TXT_ALT_IMG", $lng->txt("repository"));
		$tpl->parseCurrentBlock();

		$class_name = $objDefinition->getClassName("root");
		$class = strtolower("ilObj".$class_name."GUI");
		$this->ctrl->setParameterByClass($class, "ref_id", ROOT_FOLDER_ID);
		$link = $this->ctrl->getLinkTargetByClass($class, "view");

		$tpl->setCurrentBlock("link");
		$tpl->setVariable("TITLE", $lng->txt("repository"));
		$tpl->setVariable("LINK_TARGET", $link);
		$tpl->setVariable("TARGET", " target=\"content\"");
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("element");
		$tpl->parseCurrentBlock();

	}

	/**
	* build link target
	*/
	function buildLinkTarget($a_node_id, $a_type)
	{
		global $ilCtrl, $objDefinition;

		if ($a_type == "" || $a_type == "xxx")
		{
			return;
		}
		if ($_GET["admin_mode"] == "settings" && $a_node_id == ROOT_FOLDER_ID)
		{
			$this->ctrl->setParameterByClass("iladministrationgui", "ref_id", ROOT_FOLDER_ID);
			$this->ctrl->setParameterByClass("iladministrationgui", "admin_mode", "repository");
			$link = $this->ctrl->getLinkTargetByClass("iladministrationgui", "frameset");
			$this->ctrl->setParameterByClass("iladministrationgui", "admin_mode", "settings");
		}
		else
		{
			$class_name = $objDefinition->getClassName($a_type);
			$class = strtolower("ilObj".$class_name."GUI");
			$this->ctrl->setParameterByClass($class, "ref_id", $a_node_id);
			$link = $this->ctrl->getLinkTargetByClass($class, "view");
		}
		return $link;
	}
	
	/**
	* get frame target (may be overwritten by derived classes)
	*/
	function buildFrameTarget($a_type, $a_child = 0, $a_obj_id = 0)
	{
		if ($_GET["admin_mode"] == "settings" && $a_child == ROOT_FOLDER_ID)
		{
			return ilFrameTargetInfo::_getFrame("MainContent");
		}
		else
		{
			return $this->frame_target;
		}
	}

	
	/**
	* get image path
	*/
	function getImage($a_name, $a_type = "", $a_obj_id = "")
	{
		if ($a_type != "")
		{
			// custom icons
			if ($this->ilias->getSetting("custom_icons") &&
				in_array($a_type, array("cat","grp","crs")))
			{
				require_once("./Services/Container/classes/class.ilContainer.php");
				if (($path = ilContainer::_lookupIconPath($a_obj_id, "small")) != "")
				{
					return $path;
				}
			}
		}
		
		return parent::getImage($a_name);
	}
	
	function isVisible($a_ref_id,$a_type)
	{
		global $rbacsystem, $ilBench;

		// these objects may exist due to test cases that didnt clear
		// data properly
		if ($a_type == "" || $a_type == "xxx")
		{
			return false;
		}
		
		$ilBench->start("Explorer", "setOutput_isVisible");
		$visible = $rbacsystem->checkAccess('visible',$a_ref_id);
		if ($a_type == "rolf" && $a_ref_id != ROLE_FOLDER_ID)
		{
			return false;
		}
		$ilBench->stop("Explorer", "setOutput_isVisible");

		return $visible;
	}
	
	/**
	* modify children of parent ()
	*/
	function modifyChilds($a_parent_id, $a_objects)
	{
		global $lng;
		
		if ($a_parent_id == SYSTEM_FOLDER_ID)
		{
			$new_objects = array();
			foreach($a_objects as $object)
			{
				$new_objects[$object["title"].":".$object["child"]]
					= $object;
			}

			// add entry for switching to repository admin
			// note: please see showChilds methods which prevents infinite look
			$new_objects[$lng->txt("repository_admin").":".ROOT_FOLDER_ID] =
				array(
				"tree" => 1,
				"child" => ROOT_FOLDER_ID,
				"ref_id" => ROOT_FOLDER_ID,
				"depth" => 3,
				"type" => "root",
				"title" => $lng->txt("repository_admin"),
				"description" => $lng->txt("repository_admin_desc"),
				"desc" => $lng->txt("repository_admin_desc"),
				);
			ksort($new_objects);
			
			return $new_objects;
		}

		return $a_objects;
	}

	function showChilds($a_parent_id, $a_obj_id)
	{
		
		// prevent infinite loop due to (root folder tree) node
		// that is inserted under system admin folder
		if ($a_parent_id == ROOT_FOLDER_ID)
		{
			if ($this->rootfolder_shown == true)
			{
				return false;
			}
			$this->rootfolder_shown = true;
		}
		
		return true;
	}
	
	/**
	* force expansion of node
	*/
	function forceExpanded($a_obj_id)
	{
		if ($a_obj_id == SYSTEM_FOLDER_ID)
		{
			return true;
		}
		else
		{
			return false;
		}
	}


} // END class ilAdministrationExplorer
?>
