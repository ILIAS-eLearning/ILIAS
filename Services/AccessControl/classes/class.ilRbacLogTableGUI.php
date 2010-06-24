<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
* Class ilRbacLogTableGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
*
* @version $Id: class.ilObjRoleGUI.php 24339 2010-06-23 15:06:55Z jluetzen $
*
* @ilCtrl_Calls ilRbacLogTableGUI:
*
* @ingroup	ServicesAccessControl
*/
class ilRbacLogTableGUI extends ilTable2GUI
{
	protected $operations = array();
	
	function __construct($a_parent_obj, $a_parent_cmd, $a_ref_id)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;

		$this->setId("rbaclog");
		$this->ref_id = $a_ref_id;

		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->setTitle($lng->txt("rbac_log"));
		$this->setLimit(9999);
		// $this->setShowTemplates(true);

		$this->addColumn($this->lng->txt("date"), "date", "15%");
		$this->addColumn($this->lng->txt("user"), "user", "15%");
		$this->addColumn($this->lng->txt("action"), "action", "20%");
		$this->addColumn($this->lng->txt("rbac_changes"), "", "50%");

		// $this->setExternalSorting(true);
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getLinkTarget($a_parent_obj, $a_parent_cmd));
		$this->setRowTemplate("tpl.rbac_log_row.html", "Services/AccessControl");
		$this->initFilter($this->ref_id);

		$this->getItems($this->ref_id, $this->getCurrentFilter());
	}

	public function initFilter($a_ref_id)
	{


	}

	protected function getCurrentFilter()
	{
		
	}
	
	protected function getItems($a_ref_id, array $current_filter = NULL)
	{
		global $rbacreview;

		foreach($rbacreview->getOperations() as $op)
		{
			$this->operations[$op["ops_id"]] = $op["operation"];
		}

		include_once "Services/AccessControl/classes/class.ilRbacLog.php";
		$this->setData(ilRbacLog::getLogItems($a_ref_id));
	}

	protected function fillRow($a_set)
	{
		$this->tpl->setVariable("DATE", ilDatePresentation::formatDate(new ilDateTime($a_set["created"], IL_CAL_UNIX)));
		$this->tpl->setVariable("USER", ilObjUser::_lookupFullname($a_set["user_id"]));

		switch($a_set["action"])
		{
			case ilRbacLog::EDIT_PERMISSIONS:
				$action = $this->lng->txt("rbac_log_edit_permissions");
				break;

			case ilRbacLog::MOVE_OBJECT:
				$action = $this->lng->txt("rbac_log_move_object");
				break;

			case ilRbacLog::LINK_OBJECT:
				$action = $this->lng->txt("rbac_log_link_object");
				break;

			case ilRbacLog::COPY_OBJECT:
				$action = $this->lng->txt("rbac_log_copy_object");
				break;

			case ilRbacLog::CREATE_OBJECT:
				$action = $this->lng->txt("rbac_log_create_object");
				break;

			case ilRbacLog::EDIT_TEMPLATE:
				$action = $this->lng->txt("rbac_log_edit_template");			
				break;

			case ilRbacLog::EDIT_TEMPLATE_EXISTING:
				$action = $this->lng->txt("rbac_log_edit_template_existing");
				break;
		}
		$this->tpl->setVariable("ACTION", $action);

		if($a_set["action"] == ilRbacLog::EDIT_TEMPLATE)
		{
			$changes = $this->parseChangesTemplate($a_set["data"]);
		}
		else
		{
			$changes = $this->parseChangesFaPa($a_set["data"]);
		}

		$this->tpl->setCurrentBlock("changes");
		foreach($changes as $change)
		{
			$this->tpl->setVariable("CHANGE_ACTION", $change["action"]);
			$this->tpl->setVariable("CHANGE_OPERATION", $change["operation"]);
			$this->tpl->parseCurrentBlock();
		}
	}

	protected function parseChangesFaPa(array $raw)
	{
		$result = array();

		$type = ilObject::_lookupType($this->ref_id, true);
		
		if(isset($raw["src"]))
		{
			$result[] = array("action"=>$this->lng->txt("rbac_log_source_object"),
						"role"=>ilObject::_lookupTitle(ilObject::_lookupObjectId($raw["src"])));

			// added only
			foreach($raw["ops"] as $role_id => $ops)
			{
				foreach($ops as $op)
				{
					$result[] = array("action"=>sprintf($this->lng->txt("rbac_log_operation_add"), ilObject::_lookupTitle($role_id)),
						"operation"=>$this->lng->txt($type."_".$this->operations[$op]));
				}
			}
		}
		else if(isset($raw["ops"]))
		{
			foreach($raw["ops"] as $role_id => $actions)
			{
				foreach($actions as $action => $ops)
				{
					foreach($ops as $op)
					{
						$result[] = array("action"=>sprintf($this->lng->txt("rbac_log_operation_".$action), ilObject::_lookupTitle($role_id)),
							"operation"=>$this->lng->txt($type."_".$this->operations[$op]));
					}
				}
			}
		}

		if(isset($raw["inht"]))
		{
			foreach($raw["inht"] as $action => $role_ids)
			{
				foreach($role_ids as $role_id)
				{
					$result[] = array("action"=>sprintf($this->lng->txt("rbac_log_inheritance_".$action), ilObject::_lookupTitle($role_id)));
				}
			}
		}

        return $result;
	}

	protected function parseChangesTemplate(array $raw)
	{
		$result = array();
		foreach($raw as $type => $actions)
		{
			if($type != "src")
			{
				foreach($actions as $action => $ops)
				{
					foreach($ops as $op)
					{
						$result[] = array("action"=>sprintf($this->lng->txt("rbac_log_operation_add"), $this->lng->txt("obj_".$type)),
							"operation"=>$this->lng->txt($type."_".$this->operations[$op]));
					}
				}
			}
		}
		return $result;
	}
}

?>