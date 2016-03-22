<?php
require_once("Services/CaTUIComponents/classes/class.catTableGUI.php");
include_once("./Services/Table/classes/class.ilTable2GUI.php");
require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");

class gevUserRoleHistoryTableGUI extends ilTable2GUI {

	public function __construct($a_parent_obj, $user_id) {
		assert(!is_null($user_id));
		assert(is_integer($user_id));
		parent::__construct($a_parent_obj);

		global $lng, $ilCtrl;

		$this->gLng = $lng;
		$this->parent_obj = $a_parent_obj;
		$this->user_id = $user_id;
		$this->user_utils = gevUserUtils::getInstance($user_id);

		$this->setTitle($this->gLng->txt("gev_user_role_history"));
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, "showContent"));
		$this->setRowTemplate("tpl.gev_user_role_history_row.html", "Services/GEV/Administration");
		$this->addColumn($this->gLng->txt("gev_user_role_history_title"));
		$this->addColumn($this->gLng->txt("gev_user_role_history_date"));
		$this->addColumn($this->gLng->txt("gev_user_role_history_user"));
		$this->addColumn($this->gLng->txt("gev_user_role_history_action"));

		$data = $this->user_utils->getRoleHistory();
		$this->setMaxCount(count($data));
		$this->setData($data);
	}

	protected function fillRow($a_set) {
		$this->tpl->setVariable("ROLE_TITLE", $a_set["rol_title"]);
		
		$date = new ilDateTime($a_set["created_ts"], IL_CAL_UNIX);
		$this->tpl->setVariable("DATE", $date->get(IL_CAL_DATETIME));

		$name = $a_set["firstname"]." ".$a_set["lastname"];
		$this->tpl->setVariable("USER", $name);

		switch ($a_set["action"]) {
			case 1:
				$action = $this->gLng->txt("gev_user_role_history_add");
				break;
			case -1:
				$action = $this->gLng->txt("gev_user_role_history_removed");
				break;
			default:
				$action = $this->gLng->txt("gev_user_role_history_unknown");
				break;
		}
		$this->tpl->setVariable("ACTION", $action);
	}
}