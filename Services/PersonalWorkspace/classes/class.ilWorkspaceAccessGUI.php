<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ACL access handler GUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * 
 * @ilCtrl_Calls ilWorkspaceAccessGUI: ilRepositorySearchGUI
 *
 * @ingroup ServicesPersonalWorkspace
 */
class ilWorkspaceAccessGUI
{
	protected $ctrl;
	protected $lng;
	protected $node_id;
	protected $access_handler;	
	
	const PERMISSION_REGISTERED = -1;
	const PERMISSION_ALL = -5;
	
	
	function __construct($a_node_id, $a_access_handler)
	{
		global $ilCtrl, $lng;
		
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->node_id = $a_node_id;
		$this->access_handler = $a_access_handler;		
	}
	
	function executeCommand()
	{
		global $rbacsystem;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			case "ilrepositorysearchgui";				
				include_once('./Services/Search/classes/class.ilRepositorySearchGUI.php');
				$rep_search =& new ilRepositorySearchGUI();
				$rep_search->setCallback($this, 'addPermission');
				$rep_search->allowObjectSelection(true);

				$this->ctrl->setReturn($this, 'editpermissions');
				$this->ctrl->forwardCommand($rep_search);
				break;

			default:
				// $this->prepareOutput(); 
				if(!$cmd)
				{
					$cmd = "editPermissions";
				}
				return $this->$cmd();
		}

		return true;
	}
	
	protected function getAccessHandler()
	{
		return $this->access_handler;
	}
	
	protected function editPermissions()
	{
		global $ilToolbar, $lng, $tpl;

		$reg = $this->getAccessHandler()->hasRegisteredPermission($this->node_id);
		$all = $this->getAccessHandler()->hasGlobalPermission($this->node_id);

		if(!$all && !$reg)
		{
			$ilToolbar->addButton($this->lng->txt("wsp_permission_add_users"),
				$this->ctrl->getLinkTargetByClass("ilRepositorySearchGUI", "start"));
			
			$ilToolbar->addButton($this->lng->txt("wsp_set_permission_registered"),
				$this->ctrl->getLinkTarget($this, "addPermissionRegistered"));
		}
		if(!$all)
		{
			$ilToolbar->addButton($this->lng->txt("wsp_set_permission_all"),
				$this->ctrl->getLinkTarget($this, "addPermissionAll"));
		}

		if(!$all && !$reg)
		{
			include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessTableGUI.php";
			$table = new ilWorkspaceAccessTableGUI($this, "editPermissions", $this->node_id, $this->getAccessHandler());
			$tpl->setContent($table->getHTML());
		}
		else 
		{
			$ilToolbar->addButton($this->lng->txt("wsp_remove_permission"),
				$this->ctrl->getLinkTarget($this, "removeAllPermissions"));
			
			if($reg)
			{
				ilUtil::sendInfo($this->lng->txt("wsp_permission_registered_info"));
			}
			else
			{
				ilUtil::sendInfo($this->lng->txt("wsp_permission_all_info"));
			}
		}
	}
	
	public function addPermission($a_users = null)
	{
		global $lng;

		$object_ids = array();
		if($this->ctrl->getCmd() == "addUser")
		{
			if($a_users)
			{
				$object_ids = $a_users;
			}
			else
			{
				// return to repository search gui
				ilUtil::sendFailure($lng->txt('select_one'));
				return;
			}
		}
		else
		{
			if($_REQUEST["obj"])
			{
				$object_ids = explode(";", $_REQUEST["obj"]);
			}
		}

		if($object_ids)
		{
			foreach($object_ids as $object_id)
			{
				$this->getAccessHandler()->addPermission($this->node_id, $object_id);
			}
		}

		$this->ctrl->redirect($this, "editPermissions");
	}
	
	protected function addPermissionRegistered()
	{
		$this->getAccessHandler()->removePermission($this->node_id);	
		$this->getAccessHandler()->addPermission($this->node_id, self::PERMISSION_REGISTERED);	
		$this->ctrl->redirect($this, "editPermissions");
	}

	protected function addPermissionAll()
	{
		$this->getAccessHandler()->removePermission($this->node_id);	
		$this->getAccessHandler()->addPermission($this->node_id, self::PERMISSION_ALL);		
		$this->ctrl->redirect($this, "editPermissions");
	}

	public function removePermission()
	{
		global $lng;

		if($_REQUEST["obj_id"])
		{
			$this->getAccessHandler()->removePermission($this->node_id, (int)$_REQUEST["obj_id"]);
		    ilUtil::sendSuccess($lng->txt("permission_removed"), true);
		}

		$this->ctrl->redirect($this, "editPermissions");
	}
	
	protected function removeAllPermissions()
	{
		$this->getAccessHandler()->removePermission($this->node_id);	
		$this->ctrl->redirect($this, "editPermissions");
	}
}

?>