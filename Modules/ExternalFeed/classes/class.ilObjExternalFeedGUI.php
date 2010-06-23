<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./classes/class.ilObjectGUI.php";

/**
* Class ilObjExternalFeedGUI
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
* 
* @ilCtrl_Calls ilObjExternalFeedGUI: ilExternalFeedBlockGUI, ilPermissionGUI, ilExportGUI
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
	
	
	function executeCommand()
	{
		global $tpl, $ilTabs;

		$next_class = $this->ctrl->getNextClass($this);
		
		switch($next_class)
		{
			case 'ilpermissiongui':
				$this->prepareOutput();
				$ilTabs->activateTab("id_permissions");
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;
				
			case "ilexternalfeedblockgui":
				$this->prepareOutput();
				$ilTabs->activateTab("id_settings");
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

			case "ilexportgui":
				$this->prepareOutput();
				$ilTabs->activateTab("export");
				include_once("./Services/Export/classes/class.ilExportGUI.php");
				$exp_gui = new ilExportGUI($this);
				$exp_gui->addFormat("xml");
				$ret = $this->ctrl->forwardCommand($exp_gui);
//				$this->tpl->show();
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
	*/
	function setTabs()
	{
		global $ilAccess, $ilCtrl, $ilTabs, $lng;
		
		if (in_array($ilCtrl->getCmd(), array("create", "saveFeedBlock")))
		{
			return;
		}
		
		$ilCtrl->setParameterByClass("ilexternalfeedblockgui", "external_feed_block_id",
			$_GET["external_feed_block_id"]);
		$ilCtrl->saveParameter($this, "external_feed_block_id");

		if ($ilAccess->checkAccess('write', '', $this->object->getRefId()))
		{
			$ilTabs->addTab("id_settings",
				$lng->txt("settings"),
				$this->ctrl->getLinkTargetByClass("ilexternalfeedblockgui", "editFeedBlock"));
		}

		// export
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()) && DEVMODE == 1)
		{
			$ilTabs->addTab("export",
				$lng->txt("export"),
				$this->ctrl->getLinkTargetByClass("ilexportgui", ""));
		}


		if($ilAccess->checkAccess('edit_permission', '', $this->object->getRefId()))
		{
			$ilTabs->addTab("id_permissions",
				$lng->txt("perm_settings"),
				$this->ctrl->getLinkTargetByClass("ilpermissiongui", "perm"));
		}

	}
} // END class.ilObjExternalFeed
?>
