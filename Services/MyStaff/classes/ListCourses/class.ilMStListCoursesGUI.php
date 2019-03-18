<?php

/**
 * GUI-Class Table ilMStListCoursesGUI
 *
 * @author            Martin Studer <ms@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilMStListCoursesGUI: ilMyStaffGUI
 * @ilCtrl_Calls      ilMStListCoursesGUI:ilFormPropertyDispatchGUI
 */
class ilMStListCoursesGUI {

	use \ILIAS\Modules\OrgUnit\ARHelper\DIC;
	const CMD_APPLY_FILTER = 'applyFilter';
	const CMD_INDEX = 'index';
	const CMD_GET_ACTIONS = "getActions";
	const CMD_RESET_FILTER = 'resetFilter';
	/**
	 * @var \ilMStListCoursesTableGUI
	 */
	protected $table;


	protected function checkAccessOrFail() {
		if (ilMyStaffAccess::getInstance()->hasCurrentUserAccessToMyStaff()) {
			return true;
		} else {
			ilUtil::sendFailure($this->lng()->txt("permission_denied"), true);
			$this->ctrl()->redirectByClass('ilPersonalDesktopGUI', "");
		}
	}


	public function executeCommand() {

		$cmd = $this->ctrl()->getCmd();
		$next_class = $this->ctrl()->getNextClass();

		switch ($next_class) {
			case strtolower(ilFormPropertyDispatchGUI::class):
				$this->checkAccessOrFail();

				$this->ctrl()->setReturn($this, self::CMD_INDEX);
				$table = new ilMStListCoursesTableGUI($this, self::CMD_INDEX);
				$table->executeCommand();
				break;
			default:
				switch ($cmd) {

					case self::CMD_RESET_FILTER:
					case self::CMD_APPLY_FILTER:
					case self::CMD_INDEX:
					case self::CMD_GET_ACTIONS:
						$this->$cmd();
					default:
						$this->index();
						break;
				}
				break;
		}
	}


	public function index() {
		$this->listUsers();
	}


	public function listUsers() {
		$this->checkAccessOrFail();

		$this->table = new ilMStListCoursesTableGUI($this, self::CMD_INDEX);
		$this->table->setTitle($this->lng()->txt('mst_list_courses'));
		$this->tpl()->setContent($this->table->getHTML());
	}


	public function applyFilter() {
		$this->table = new ilMStListCoursesTableGUI($this, self::CMD_APPLY_FILTER);
		$this->table->writeFilterToSession();
		$this->table->resetOffset();
		$this->index();
	}


	public function resetFilter() {
		$this->table = new ilMStListCoursesTableGUI($this, self::CMD_RESET_FILTER);
		$this->table->resetOffset();
		$this->table->resetFilter();
		$this->index();
	}


	public function getId() {
		$this->table = new ilMStListCoursesTableGUI($this, self::CMD_INDEX);

		return $this->table->getId();
	}


	public function cancel() {
		$this->ctrl()->redirect($this);
	}


	public function getActions() {
		$ilAccess = $this->dic()->access();

		$mst_co_usr_id = $this->dic()->http()->request()->getQueryParams()['mst_lco_usr_id'];
		$mst_lco_crs_ref_id = $this->dic()->http()->request()->getQueryParams()['mst_lco_crs_ref_id'];

		if ($mst_co_usr_id > 0 && $mst_lco_crs_ref_id > 0) {
			$selection = new ilAdvancedSelectionListGUI();

			if ($ilAccess->checkAccess("visible", "", $mst_lco_crs_ref_id)) {
				$link = ilLink::_getStaticLink($mst_lco_crs_ref_id, 'crs');
				$selection->addItem(ilObject2::_lookupTitle(ilObject2::_lookupObjectId($mst_lco_crs_ref_id)), '', $link);
			};

			$org_units = ilOrgUnitPathStorage::getTextRepresentationOfOrgUnits('ref_id');
			foreach (ilOrgUnitUserAssignment::innerjoin('object_reference', 'orgu_id', 'ref_id')->where(array(
				'user_id' => $mst_co_usr_id,
				'object_reference.deleted' => NULL
			), array( 'user_id' => '=', 'object_reference.deleted' => '!=' ))->get() as $org_unit_assignment) {
				if ($ilAccess->checkAccess("read", "", $org_unit_assignment->getOrguId())) {
					$link = ilLink::_getStaticLink($org_unit_assignment->getOrguId(), 'orgu');
					$selection->addItem($org_units[$org_unit_assignment->getOrguId()], '', $link);
				}
			}

			$selection = ilMyStaffGUI::extendActionMenuWithUserActions($selection, $mst_co_usr_id, rawurlencode($this->ctrl()
				->getLinkTarget($this, self::CMD_INDEX)));

			echo $selection->getHTML(true);
		}
		exit;
	}
}
