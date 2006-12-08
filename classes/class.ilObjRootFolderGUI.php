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
* @version $Id$Id: class.ilObjRootFolderGUI.php,v 1.13 2006/03/10 09:22:58 akill Exp $
*
* @ilCtrl_Calls ilObjRootFolderGUI: ilPermissionGUI, ilPageObjectGUI, ilContainerLinkListGUI
* 
* @extends ilObjectGUI
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
		$this->ctrl->returnToParent($this);
	}

	/**
	* import categories
	*/
	function importCategoriesObject()
	{
	  ilObjCategoryGUI::_importCategories($this->ref_id,0);
	}


	/**
	 * import categories
	 */
	function importCategoriesWithRolObject()
	{
	  ilObjCategoryGUI::_importCategories($this->ref_id,1);
	}


	function getTabs(&$tabs_gui)
	{
		global $rbacsystem;

		$this->ctrl->setParameter($this,"ref_id",$this->ref_id);

		if ($rbacsystem->checkAccess('read',$this->ref_id))
		{
			$tabs_gui->addTarget("view_content",
				$this->ctrl->getLinkTarget($this, ""),
				array("", "view", "render"));
		}
		
		// parent tabs (all container: edit_permission, clipboard, trash
		parent::getTabs($tabs_gui);

	}

	function &executeCommand()
	{
		global $rbacsystem;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		
		switch($next_class)
		{
			case 'ilcontainerlinklistgui':
				include_once("./classes/class.ilContainerLinkListGUI.php");
				$link_list_gui =& new ilContainerLinkListGUI();
				$ret =& $this->ctrl->forwardCommand($link_list_gui);
				break;

				// container page editing
			case "ilpageobjectgui":
				$this->tpl->getStandardTemplate();
				$this->setLocator();
				sendInfo();
				infoPanel();
				//$this->prepareOutput(false);
				$ret = $this->forwardToPageObject();
				$this->setTitleAndDescription();
				$this->setPageEditorTabs();
				return $ret;
				break;

			case 'ilpermissiongui':
				$this->prepareOutput();
				include_once("./classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;
			
			default:
				$this->prepareOutput();
				include_once("classes/class.ilObjStyleSheet.php");
				$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
					ilObjStyleSheet::getContentStylePath(0));

				if(!$cmd)
				{
					$cmd = "render";
				}

				$cmd .= "Object";
				$this->$cmd();

				break;
		}
		return true;
	}
	
	
	/**
	* called by prepare output 
	*/
	function setTitleAndDescription()
	{
		global $lng;

		$this->tpl->setTitle($lng->txt("repository"));
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_".$this->object->getType()."_b.gif"));
	}

}
?>
