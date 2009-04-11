<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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

require_once "./classes/class.ilObjectGUI.php";

/**
* Class ilObjExternalFeedGUI
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
* 
* @ilCtrl_Calls ilObjExternalFeedGUI: ilExternalFeedBlockGUI, ilPermissionGUI
* @ilCtrl_IsCalledBy ilObjExternalFeedGUI: ilRepositoryGUI, ilAdministrationGUI
*/
class ilObjExternalFeedGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjExternalFeedGUI($a_data, $a_id, $a_call_by_reference, $a_prepare_output = true)
	{
		$this->type = "feed";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output);
	}
	
	
	function &executeCommand()
	{
		global $rbacsystem, $tpl;

		$next_class = $this->ctrl->getNextClass($this);
		
		switch($next_class)
		{
			case 'ilpermissiongui':
				$this->prepareOutput();
				include_once("./classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;
				
			case "ilexternalfeedblockgui":
				$this->prepareOutput();
				include_once("./Services/Block/classes/class.ilExternalFeedBlockGUI.php");
				$fb_gui =& new ilExternalFeedBlockGUI();
				$fb_gui->setGuiObject($this);
				if (is_object($this->object))
				{
					$fb_gui->setRefId($this->object->getRefId());
				}
				$ret =& $this->ctrl->forwardCommand($fb_gui);
				$tpl->setContent($ret);
				break;

			default:
				$cmd = $this->ctrl->getCmd("view");
				if ($cmd != "create")
				{
					$this->prepareOutput();
				}
				$cmd .= "Object";
				$this->$cmd();
				break;
		}
		return true;
	}

	function createObject()
	{
		global $ilCtrl;
		$ilCtrl->setCmdClass("ilexternalfeedblockgui");
		$ilCtrl->setCmd("create");
		return $this->executeCommand();
	}
	
	/**
	* save object
	* @access	public
	*/
	function save($a_feed_block)
	{
		global $rbacadmin, $ilUser;

		// create and insert forum in objecttree
		$_GET["new_type"] = "feed";
		$_POST["Fobject"]["title"] = $a_feed_block->getTitle();
		$_POST["Fobject"]["desc"] = $a_feed_block->getFeedUrl();
		$newObj = parent::saveObject();
		$newObj->setOwner($ilUser->getId());
		$newObj->updateOwner();
		$a_feed_block->setContextObjId($newObj->getId());
		$a_feed_block->setContextObjType("feed");

		// setup rolefolder & default local roles
		//$roles = $newObj->initDefaultRoles();

		// ...finally assign role to creator of object
		//$rbacadmin->assignUser($roles[0], $newObj->getOwner(), "y");

	}
	
	/**
	* Exit save.
	*
	*/
	public function exitSave()
	{
		global $ilCtrl;

		// always send a message
		ilUtil::sendSuccess($this->lng->txt("object_added"), true);
		$this->ctrl->returnToParent($this);
	}
	
	/**
	* update object
	* @access	public
	*/
	function update($a_feed_block)
	{
		global $rbacadmin;

		// update object
		$_POST["Fobject"]["title"] = $a_feed_block->getTitle();
		$_POST["Fobject"]["desc"] = $a_feed_block->getFeedUrl();
		$newObj = parent::updateObject();
	}

	/**
	* Cancel update.
	*
	*/
	public function cancelUpdate()
	{
		global $ilCtrl, $tree;


		$par = $tree->getParentId($_GET["ref_id"]);
		$_GET["ref_id"] = $par;
		$this->redirectToRefId($par);
		
		//$this->ctrl->returnToParent($this);
	}

	/**
	* After update
	*
	*/
	public function afterUpdate()
	{
		global $ilCtrl, $tree;

		// always send a message
		$par = $tree->getParentId($_GET["ref_id"]);
		$_GET["ref_id"] = $par;
		$this->redirectToRefId($par);

	}

	/**
	* get tabs
	* @access	public
	* @param	object	tabs gui object
	*/
	function getTabs(&$tabs_gui)
	{
		global $rbacsystem, $ilCtrl;
		
		if (in_array($ilCtrl->getCmd(), array("create", "saveFeedBlock")))
		{
			return;
		}
		
		$ilCtrl->setParameterByClass("ilexternalfeedblockgui", "external_feed_block_id",
			$_GET["external_feed_block_id"]);
		$ilCtrl->saveParameter($this, "external_feed_block_id");

		if ($rbacsystem->checkAccess('write', $this->object->getRefId()))
		{
			$force_active = ($_GET["cmd"] == "edit" ||
				$this->ctrl->getNextClass() == "ilexternalfeedblockgui")
				? true
				: false;
			$tabs_gui->addTarget("edit_properties",
				$this->ctrl->getLinkTargetByClass("ilexternalfeedblockgui", "editFeedBlock"),
				"edit", get_class($this),
				"", $force_active);
		}

		if($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass("ilpermissiongui", "perm"), "", "ilpermissiongui");
		}

	}
} // END class.ilObjExternalFeed
?>
