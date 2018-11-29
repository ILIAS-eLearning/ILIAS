<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Modules\OrgUnit\ARHelper\BaseCommands;

/**
 * Class ilOrgUnitUserAssignmentTableGUI
 */
class ilOrgUnitRecursiveUserAssignmentTableGUI extends ilTable2GUI {

	/**
	 * @var ilOrgUnitPosition
	 */
	protected $ilOrgUnitPosition;
	/**
	 * @var \ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var \ILIAS\Modules\OrgUnit\ARHelper\BaseCommands
	 */
	protected $parent_obj;


	/**
	 * @var bool
	 */
	protected $recursive;
	/**
	 * ilOrgUnitUserAssignmentTableGUI constructor.
	 *
	 * @param \ILIAS\Modules\OrgUnit\ARHelper\BaseCommands $parent_obj
	 * @param string                                       $parent_cmd
	 * @param \ilOrgUnitPosition                           $position
	 */
	public function __construct(BaseCommands $parent_obj, $parent_cmd, ilOrgUnitPosition $position) {
		$this->parent_obj = $parent_obj;
		$this->ilOrgUnitPosition = $position;
		$this->ctrl = $GLOBALS["DIC"]->ctrl();
		$this->setPrefix("il_orgu_" . $position->getId());
		$this->setFormName('il_orgu_' . $position->getId());
		$this->setId("il_orgu_" . $position->getId());
		$this->orgu_ref_id = $_GET["ref_id"];
		parent::__construct($parent_obj, $parent_cmd);

		$this->setFormAction($this->ctrl->getFormAction($parent_obj));
		$this->setTableHeaders();
		$this->setTopCommands(true);
		$this->setEnableHeader(true);
		$this->setShowRowsSelector(true);
		$this->setShowTemplates(false);
		$this->setEnableHeader(true);
		$this->setEnableTitle(true);
		$this->setTitle($position->getTitle());
		$this->setRowTemplate("tpl.staff_row.html", "Modules/OrgUnit");
		$this->setData($this->loadData());
	}


	protected function setTableHeaders() {
		$this->addColumn($this->lng->txt("firstname"), "first_name");
		$this->addColumn($this->lng->txt("lastname"), "last_name");
		$this->addColumn($this->lng->txt("orgu_assignments"), "orgus");
		$this->addColumn($this->lng->txt("action"));
	}


	public function loadData()
	{
		global $DIC;
		$ilAccess = $DIC['ilAccess'];
		$orgu_tree = ilObjOrgUnitTree::_getInstance();
		$data = [];
		foreach ($orgu_tree->getAllChildren($this->orgu_ref_id) as $ref_id) {
			$permission_view_lp = $ilAccess->checkAccess("view_learning_progress", "", $ref);
			foreach($orgu_tree->getAssignements($ref_id,$this->ilOrgUnitPosition) as $usr_id) {
				$usr_id = (int)$usr_id;
				if(!array_key_exists($usr_id, $data)) {
					$user = new ilObjUser($usr_id);
					$set["first_name"] = $user->getFirstname();
					$set["last_name"] = $user->getLastname();
					$set["user_object"] = $user;
					$set["user_id"] = $user_id;
					$set["orgu_assignments"] = [];
					$set['view_lp'] = false;
					$data[$usr_id] = $set;
				}
				$data[$usr_id]['orgu_assignments'][] = ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id));
				$data[$usr_id]['view_lp'] = $permission_view_lp || $data[$usr_id]['view_lp'];
			}
		}
		return $data;
	}


	/**
	 * @param array $set
	 */
	public function fillRow($set) {
		global $DIC;

		$lng = $DIC['lng'];
		$ilAccess = $DIC['ilAccess'];
		$this->tpl->setVariable("FIRST_NAME", $set["first_name"]);
		$this->tpl->setVariable("LAST_NAME", $set["last_name"]);
		$orgus = $set['orgu_assignments'];
		sort($orgus);
		$this->tpl->setVariable("ORG_UNITS", implode(',',$orgus));
//		$this->ctrl->setParameterByClass(ilLearningProgressGUI::class, "obj_id", $set["user_id"]);
//		$this->ctrl->setParameterByClass(ilObjOrgUnitGUI::class, "obj_id", $set["user_id"]);
		$this->ctrl->setParameterByClass(ilOrgUnitUserAssignmentGUI::class, 'usr_id', $set["user_id"]);
		$this->ctrl->setParameterByClass(ilOrgUnitUserAssignmentGUI::class, 'position_id', $this->ilOrgUnitPosition->getId());
		$selection = new ilAdvancedSelectionListGUI();
		$selection->setListTitle($lng->txt("Actions"));
		$selection->setId("selection_list_user_lp_" . $set["user_id"]);

		if ($set['view_lp']) {
			$selection->addItem($lng->txt("show_learning_progress"), "show_learning_progress", $this->ctrl->getLinkTargetByClass(array(
				ilAdministrationGUI::class,
				ilObjOrgUnitGUI::class,
				ilLearningProgressGUI::class,
			), ""));
		}
		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"]) && !$this->recursive) {
			$this->addActions($selection);
		}
		$this->tpl->setVariable("ACTIONS", $selection->getHTML());
	}


	/**
	 * @param $selection ilAdvancedSelectionListGUI
	 */
	protected function addActions(&$selection) {
		$selection->addItem($this->lng->txt("remove"), "delete_from_employees", $this->ctrl->getLinkTargetByClass(ilOrgUnitUserAssignmentGUI::class, ilOrgUnitUserAssignmentGUI::CMD_CONFIRM));
	}
}
