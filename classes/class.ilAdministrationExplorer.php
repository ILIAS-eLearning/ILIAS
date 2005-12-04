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
* @package core
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
		global $tree,$ilCtrl;

		$this->ctrl = $ilCtrl;

		parent::ilExplorer($a_target);
		$this->tree = $tree;
		$this->root_id = $this->tree->readRootId();
		$this->order_column = "title";
		$this->setSessionExpandVariable("expand");

	}

	/**
	* note: most of this stuff is used by ilCourseContentInterface too
	*/
	function buildLinkTarget($a_node_id, $a_type)
	{
		global $ilCtrl, $objDefinition;

		$class_name = $objDefinition->getClassName($a_type);
		$class = strtolower("ilObj".$class_name."GUI");
		$this->ctrl->setParameterByClass($class, "ref_id", $a_node_id);
		$link = $this->ctrl->getLinkTargetByClass($class, "view");
		return $link;
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
				require_once("classes/class.ilContainer.php");
				if (($path = ilContainer::_lookupIconPath($a_obj_id, "small")) != "")
				{
					return $path;
				}
			}
		}
		
		return parent::getImage($a_name);
	}

} // END class ilAdministrationExplorer
?>
