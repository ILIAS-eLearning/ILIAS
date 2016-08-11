<?php
require_once 'Services/Table/classes/class.ilTable2GUI.php';
require_once 'Modules/ManualAssessment/classes/Members/class.ilManualAssessmentMembersStorageDB.php';
require_once 'Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';
class ilManualAssessmentMembersTableGUI extends ilTable2GUI {
	public function __construct($a_parent_obj, $a_parent_cmd="", $a_template_context="") {
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);
		global $DIC;
		$this->ctrl = $DIC['ilCtrl'];
		$this->lng = $DIC['lng'];
		$this->setEnableTitle(true);
		$this->setTopCommands(true);
		$this->setEnableHeader(true);
		$this->setExternalSorting(false);
		$this->setExternalSegmentation(true);
		$this->setRowTemplate("tpl.members_table_row.html", "Modules/ManualAssessment");
		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, "view"));
		$this->parent_obj = $a_parent_obj;
		$columns = array( "name" 				=> array("name")
						, "login" 				=> array("login")
						, "grade" 				=> array("grade")
						, "graded_by"			=> array("graded_by")
						, "actions"				=> array(null)
						);
		foreach ($columns as $lng_var => $params) {
			$this->addColumn($this->lng->txt($lng_var), $params[0]);
		}
		$this->setData(iterator_to_array($a_parent_obj->object->loadMembers()));
	}

	protected function fillRow($a_set) {
		$this->tpl->setVariable("FIRSTNAME", $a_set["firstname"]);
		$this->tpl->setVariable("LASTNAME", $a_set["lastname"]);
		$this->tpl->setVariable("LOGIN", $a_set["login"]);
		$this->tpl->setVariable("GRADE", $a_set["grade"]);
		$this->tpl->setVariable("GRADED_BY", $a_set["graded_by"]);
		$this->tpl->setVariable("ACTIONS",$this->buildActionDropDown($a_set));
	}

	protected function buildActionDropDown($a_set) {
		$l = new ilAdvancedSelectionListGUI();
		$lng_var_edit = "mass_usr_view";
		if(!$a_set['finalized']) {
			$this->ctrl->setParameter($this->parent_obj, 'usr_id', $a_set['usr_id']);
			$target = $this->ctrl->getLinkTarget($this->parent_obj,'removeUser');
			$this->ctrl->setParameter($this->parent_obj, 'usr_id', null);
			$l->addItem($this->lng->txt("mass_usr_remove"), 'removeUser', $target);
			$lng_var_edit = "mass_usr_edit";
		}
		$this->ctrl->setParameterByClass('ilManualAssessmentMemberGUI', 'usr_id', $a_set['usr_id']);
		$target = $this->ctrl->getLinkTargetByClass('ilManualAssessmentMemberGUI','edit');
		$this->ctrl->setParameterByClass('ilManualAssessmentMemberGUI', 'usr_id', null);
		$l->addItem($this->lng->txt($lng_var_edit), 'edit', $target);
		$this->ctrl->setParameter($this->parent_obj,'usr_id',null);

		return $l->getHTML();
	}

}