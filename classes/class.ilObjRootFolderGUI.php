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
* Class ilObjRootFolderGUI
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$Id: class.ilObjRootFolderGUI.php,v 1.6 2005/05/12 17:10:45 akill Exp $
*
* @ilCtrl_Calls ilObjRootFolderGUI:
*
* @extends ilObjectGUI
* @package ilias-core
*/

require_once "class.ilContainerGUI.php";
require_once "class.ilObjCategoryGUI.php";

class ilObjRootFolderGUI extends ilContainerGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjRootFolderGUI($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
	{
		$this->type = "root";
		$this->ilContainerGUI($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
	}

	/**
	* import categories form
	*/
	function importCategoriesFormObject ()
	{
		ilObjCategoryGUI::_importCategoriesForm($this->ref_id, $this->tpl);
	}

	/**
	* import cancelled
	*
	* @access private
	*/
	function importCancelledObject()
	{
		sendInfo($this->lng->txt("action_aborted"),true);
		ilUtil::redirect("adm_object.php?ref_id=".$this->ref_id);
	}

	/**
	* import categories
	*/
	function importCategoriesObject()
	{
		ilObjCategoryGUI::_importCategories($this->ref_id);
	}

	function getTabs(&$tabs_gui)
	{
		global $rbacsystem;

		$this->ctrl->setParameter($this,"ref_id",$this->ref_id);

		if ($rbacsystem->checkAccess('read',$this->ref_id))
		{
			$tabs_gui->addTarget("view_content",
				$this->ctrl->getLinkTarget($this, ""),
				"");
		}
		
		// parent tabs (all container: edit_permission, clipboard, trash
		parent::getTabs($tabs_gui);

	}


}
?>
