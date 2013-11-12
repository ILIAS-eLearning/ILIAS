<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once("./Services/Table/classes/class.ilTable2GUI.php");
require_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
require_once("./Services/Tracking/classes/class.ilObjUserTracking.php");
/**
 * Class ilOrgUnitStaffTableGUI
 *
 * @author            Oskar Truffer <ot@studer-raimann.ch>
 * @author            Martin Studer <ms@studer-raimann.ch>
 *
 */
class ilOrgUnitStaffTableGUI extends ilTable2GUI{

	/** @var bool  */
	private $recursive = false;

	/** @var string "employee" | "superior" */
	private $staff = "employee";

	public function __construct($parent_obj, $parent_cmd, $staff = "employee", $recursive = false, $template_context = ""){
		parent::__construct($parent_obj, $parent_cmd, $template_context);

		global $lng, $ilCtrl, $ilTabs;
		/**
		 * @var $ilCtrl ilCtrl
		 * @var $ilTabs ilTabsGUI
		 */
		$this->ctrl = $ilCtrl;
		$this->tabs = $ilTabs;
		$this->lng = $lng;

		$this->setPrefix("sr_orgu_".$staff);
		$this->setFormName('sr_orgu_'.$staff);
		$this->setId("sr_orgu_".$staff);
		$this->setStaff($staff);
        $this->recursive = $recursive;
		$this->setTableHeaders();
		$this->setTopCommands(true);
		$this->setEnableHeader(true);
		$this->setShowRowsSelector(true);
		$this->setShowTemplates(false);
		$this->setEnableHeader(true);
		$this->setDefaultOrderField("role");
		$this->setEnableTitle(true);
		$this->setTitle($this->lng->txt("Staff"));
		$this->setRowTemplate("tpl.staff_row.html", "Modules/OrgUnit");
	}


	protected function setTableHeaders(){
		$this->addColumn($this->lng->txt("firstname"), "first_name");
		$this->addColumn($this->lng->txt("lastname"), "last_name");
        if ($this->recursive) {
            $this->addColumn($this->lng->txt('obj_orgu'), 'org_units');
        }
		$this->addColumn($this->lng->txt("action"));
	}

	public function parseData(){
		if($this->staff == "employee")
			$data = $this->parseRows(ilObjOrgUnitTree::_getInstance()->getEmployees($_GET["ref_id"], $this->recursive));
		elseif($this->staff == "superior")
			$data = $this->parseRows(ilObjOrgUnitTree::_getInstance()->getSuperiors($_GET["ref_id"], $this->recursive));
		else
			throw new Exception("The ilOrgUnitStaffTableGUI's staff variable has to be either 'employee' or 'superior'");

		$this->setData($data);
	}

	protected function parseRows($user_ids){
		$data = array();
		foreach($user_ids as $user_id){
			$set = array();
			$this->setRowForUser($set, $user_id);
			$data[] = $set;
		}
		return $data;
	}

	/**
	 * @param string $staff Set this variable either to "employee" or "superior". It's employee by default.
	 */
	public function setStaff($staff)
	{
		$this->staff = $staff;
	}

	/**
	 * @return string
	 */
	public function getStaff()
	{
		return $this->staff;
	}

	protected function setRowForUser(&$set, $user_id){
		$user = new ilObjUser($user_id);
		$set["first_name"] = $user->getFirstname();
		$set["last_name"] = $user->getLastname();
		$set["user_object"] = $user;
		$set["user_id"] = $user_id;
        if ($this->recursive) $set["org_units"] = ilObjOrgUnitTree::_getInstance()->getOrgUnitOfUser($user_id, (int)$_GET['ref_id']);
	}

	function fillRow($set){
		global $ilUser, $Access, $lng, $ilAccess;
		$this->tpl->setVariable("FIRST_NAME", $set["first_name"]);
		$this->tpl->setVariable("LAST_NAME", $set["last_name"]);
        if ($this->recursive) {
            $orgUnitsTitles = array_values(ilObjOrgUnitTree::_getInstance()->getTitles($set['org_units']));
            $this->tpl->setVariable("ORG_UNITS", implode(', ', $orgUnitsTitles));
        }
		$this->ctrl->setParameterByClass("illearningprogressgui", "obj_id", $set["user_id"]);
		$this->ctrl->setParameterByClass("ilobjorgunitgui", "obj_id", $set["user_id"]);
		$selection = new ilAdvancedSelectionListGUI();
		$selection->setListTitle($lng->txt("Actions"));
		$selection->setId("selection_list_user_lp_".$set["user_id"]);

		if($ilAccess->checkAccess("view_learning_progress", "", $_GET["ref_id"]) AND ilObjUserTracking::_enabledLearningProgress() and
			ilObjUserTracking::_enabledUserRelatedData()){
			$selection->addItem($lng->txt("show_learning_progress"), "show_learning_progress", $this->ctrl->getLinkTargetByClass(array("ilAdministrationGUI", "ilObjOrgUnitGUI", "ilLearningProgressGUI"), ""));
		}
		if($ilAccess->checkAccess("write", "", $_GET["ref_id"]) && !$this->recursive){
			if($this->staff == "employee")
				$this->addEmployeeActions($selection);
			if($this->staff == "superior")
				$this->addSuperiorActions($selection);
		}
		$this->tpl->setVariable("ACTIONS", $selection->getHTML());

	}

	/**
	 * @param $selection ilAdvancedSelectionListGUI
	 */
	protected function addEmployeeActions(&$selection){
		$selection->addItem($this->lng->txt("remove"), "delete_from_employees", $this->ctrl->getLinkTargetByClass("ilOrgUnitStaffGUI", "confirmRemoveFromEmployees"));
		$selection->addItem($this->lng->txt("change_to_superior"), "change_to_superior", $this->ctrl->getLinkTargetByClass("ilOrgUnitStaffGUI", "fromEmployeeToSuperior"));
	}

	/**
	 * @param $selection ilAdvancedSelectionListGUI
	 */
	protected function addSuperiorActions(&$selection){
		$selection->addItem($this->lng->txt("remove"), "delete_from_superiors", $this->ctrl->getLinkTargetByClass("ilOrgUnitStaffGUI", "confirmRemoveFromSuperiors"));
		$selection->addItem($this->lng->txt("change_to_employee"), "change_to_employee", $this->ctrl->getLinkTargetByClass("ilOrgUnitStaffGUI", "fromSuperiorToEmployee"));
	}

	/**
	 * @param $recursive bool show direct members of this org unit or the sub-units as well?
	 */
	public function setRecursive($recursive){
		$this->recursive = $recursive;
	}


}
?>