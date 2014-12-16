<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObjectGUI.php";

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
		// create and insert forum in objecttree
		$_REQUEST["new_type"] = "feed";
		$_POST["title"] = $a_feed_block->getTitle();
		$_POST["desc"] = $a_feed_block->getFeedUrl();
		parent::saveObject($a_feed_block);
	}

	function afterSave(ilObject $a_new_object, $a_feed_block)
	{
	    // saveObject() parameters are sent as array
		$a_feed_block = $a_feed_block[0];

		$a_feed_block->setContextObjId($a_new_object->getId());
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
		$_POST["title"] = $a_feed_block->getTitle();
		$_POST["desc"] = $a_feed_block->getFeedUrl();
		parent::updateObject();
	}

	/**
	* Cancel update.
	*
	*/
	public function cancelUpdate()
	{
		global $tree;

		$par = $tree->getParentId($_GET["ref_id"]);
		$_GET["ref_id"] = $par;
		$this->redirectToRefId($par);
	}

	/**
	* After update
	*
	*/
	public function afterUpdate()
	{
		global $tree;

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
		global $ilAccess, $ilCtrl, $ilTabs, $lng, $ilHelp;
		
		if (in_array($ilCtrl->getCmd(), array("create", "saveFeedBlock")))
		{
			return;
		}
		$ilHelp->setScreenIdComponent("feed");
		
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
	
	public static function _goto($a_target)
	{						
		global $tree;
		
		$id = explode("_", $a_target);		
		$ref_id = $id[0];
		
		// is sideblock: so show parent instead
		$container_id = $tree->getParentId($ref_id);

		// #14870
		include_once "Services/Link/classes/class.ilLink.php";
		ilUtil::redirect(ilLink::_getLink($container_id));		
	}
} // END class.ilObjExternalFeed
?>