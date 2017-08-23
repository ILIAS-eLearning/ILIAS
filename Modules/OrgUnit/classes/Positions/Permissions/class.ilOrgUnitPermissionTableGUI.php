<?php

/**
 * Class ilOrgUnitPermissionTableGUI
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitPermissionTableGUI extends ilTable2GUI {

	/**
	 * @var null|int
	 */
	private $ref_id = null;


	/**
	 * ilOrgUnitPermissionTableGUI constructor.
	 *
	 * @param \ILIAS\Modules\OrgUnit\ARHelper\BaseCommands $a_parent_obj
	 * @param string                                       $a_parent_cmd
	 * @param string                                       $a_ref_id
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $a_ref_id) {
		global $ilCtrl, $tpl;

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->lng->loadLanguageModule('rbac');

		$this->ref_id = $a_ref_id;

		$this->setId('objpositionperm_' . $this->ref_id);

		$tpl->addJavaScript('./Services/AccessControl/js/ilPermSelect.js');

		$this->setTitle($this->lng->txt('permission_settings'));
		$this->setEnableHeader(true);
		$this->disable('sort');
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->disable('numinfo');
		$this->setRowTemplate("tpl.obj_position_perm_row.html", "Services/AccessControl");
		//$this->setLimit(100);
		$this->setShowRowsSelector(false);
		$this->setDisableFilterHiding(true);
		$this->setNoEntriesText($this->lng->txt('msg_no_roles_of_type'));

		$this->addCommandButton('savePermissions', $this->lng->txt('save'));
	}


	/**
	 * Get ref id of current object
	 *
	 * @return
	 */
	public function getRefId() {
		return $this->ref_id;
	}


	/**
	 * Get obj id
	 *
	 * @return
	 */
	public function getObjId() {
		return ilObject::_lookupObjId($this->getRefId());
	}


	/**
	 * get obj type
	 *
	 * @return
	 */
	public function getObjType() {
		return ilObject::_lookupType($this->getObjId());
	}


	/**
	 * Fill one permission row
	 *
	 * @param object $row
	 *
	 * @return
	 */
	public function fillRow($row) {
		global $objDefinition;

		// Select all
		if (isset($row['show_select_all'])) {
			foreach ($row["positions"] as $position) {
				$this->tpl->setCurrentBlock('position_select_all');
				$this->tpl->setVariable('JS_ROLE_ID', $position->getId());
				$this->tpl->setVariable('JS_SUBID', 0);
				$this->tpl->setVariable('JS_ALL_PERMS', "[]");//'".implode("','",$row['ops'])."']");
				$this->tpl->setVariable('JS_FORM_NAME', $this->getFormName());
				$this->tpl->setVariable('TXT_SEL_ALL', $this->lng->txt('select_all'));
				$this->tpl->parseCurrentBlock();
			}

			return true;
		}

		foreach ($row as $permission) {
			$position = $permission["position"];
			$op_id = $permission["op_id"];
			$this->tpl->setCurrentBlock('position_td');
			$this->tpl->setVariable('POSITION_ID', $position->getId());
			$this->tpl->setVariable('PERM_ID', $op_id);

			$this->tpl->setVariable('TXT_PERM', $op_id);
			$this->tpl->setVariable('PERM_LONG', $op_id);

			if ($role_info['permission_set']) {
				$this->tpl->setVariable('PERM_CHECKED', 'checked="checked"');
			}

			$this->tpl->parseCurrentBlock();
		}
	}


	public function collectData() {
		$positions = ilOrgUnitPosition::getActive();

		$this->initColumns($positions);

		$perms = [];
		$operations = $this->dic()->access()->getAvailablePositionRelatedPermissions();

		foreach ($operations as $op) {
			$ops = [];
			foreach ($positions as $position) {
				$ops[] = [ "op_id" => $op, "position" => $position, "permission_set" => false ];
			}
			$perms[] = $ops;
		}

		$perms[] = [ "show_select_all" => true, "positions" => $positions ];

		$this->setData($perms);

		return;
	}


	/**
	 * Init Columns
	 *
	 * @param    ilOrgUnitPosition[] $positions
	 *
	 * @return
	 */
	protected function initColumns(array $positions) {
		foreach ($positions as $position) {
			$this->addColumn($position->getTitle(), '', '', '', false, $position->getDescription());
		}

		return true;
	}


	/**
	 * @return \ILIAS\DI\Container
	 */
	private function dic() {
		return $GLOBALS['DIC'];
	}
}
