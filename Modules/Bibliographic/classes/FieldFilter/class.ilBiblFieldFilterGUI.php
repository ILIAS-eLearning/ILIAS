<?php

/**
 * Class ilBiblFieldFilterGUI
 *
 * @author Benjamin Seglias   <bs@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */

class ilBiblFieldFilterGUI {

	use \ILIAS\Modules\OrgUnit\ARHelper\DIC;
	const FILTER_ID = 'filter_id';
	const CMD_STANDARD = 'index';
	const CMD_ADD = 'add';
	const CMD_CREATE = 'create';
	const CMD_CANCEL = 'cancel';
	const CMD_EDIT = 'edit';
	const CMD_UPDATE = 'update';
	const CMD_DELETE = 'delete';
	const CMD_APPLY_FILTER = 'applyFilter';
	const CMD_RESET_FILTER = 'resetFilter';
	const CMD_TRANSLATE = 'translate';


	/**
	 * @var \ilBiblFactoryFacade
	 */
	protected $facade;


	/**
	 * ilBiblFieldFilterGUI constructor.
	 *
	 * @param \ilBiblFactoryFacade $facade
	 */
	public function __construct(ilBiblFactoryFacade $facade) {
		$this->facade = $facade;
	}


	public function executeCommand() {
		$nextClass = $this->ctrl()->getNextClass();
		switch ($nextClass) {
			default:
				$this->tabs()->activateTab(ilObjBibliographicGUI::TAB_SETTINGS);
				$this->performCommand();
		}
	}


	protected function performCommand() {
		$cmd = $this->ctrl()->getCmd(self::CMD_STANDARD);
		switch ($cmd) {
			case self::CMD_STANDARD:
			case self::CMD_ADD:
			case self::CMD_EDIT:
			case self::CMD_UPDATE:
			case self::CMD_CREATE:
			case self::CMD_DELETE:
			case self::CMD_CANCEL:
			case self::CMD_APPLY_FILTER:
			case self::CMD_RESET_FILTER:
				if ($this->access()->checkAccess('write', "", $this->facade->iliasObject()
				                                                           ->getRefId())) {
					$this->{$cmd}();
					break;
				} else {
					ilUtil::sendFailure($this->lng()->txt("no_permission"), true);
					break;
				}
		}
	}


	public function index() {
		$table = new ilBiblFieldFilterTableGUI($this, $this->facade);
		$this->tpl()->setContent($table->getHTML());
	}


	protected function add() {
		$ilBiblSettingsFilterFormGUI = new ilBiblFieldFilterFormGUI($this, new ilBiblFieldFilter(), $this->facade);
		$this->tpl()->setContent($ilBiblSettingsFilterFormGUI->getHTML());
	}


	protected function create() {
		$this->tabs()->activateTab(self::CMD_STANDARD);
		$il_bibl_field = new ilBiblFieldFilter();
		$il_bibl_field->setObjectId($this->facade->iliasObject()->getId());
		$form = new ilBiblFieldFilterFormGUI($this, $il_bibl_field, $this->facade);
		if ($form->saveObject()) {
			ilUtil::sendSuccess($this->lng()->txt('changes_saved_success'), true);
			$this->ctrl()->redirect($this, self::CMD_STANDARD);
		}
		$form->setValuesByPost();
		$this->tpl()->setContent($form->getHTML());
	}


	public function edit() {
		$ilBiblSettingsFilterFormGUI = new ilBiblFieldFilterFormGUI($this, $this->getFieldFilterFromRequest(), $this->facade);
		$ilBiblSettingsFilterFormGUI->fillForm();
		$this->tpl()->setContent($ilBiblSettingsFilterFormGUI->getHTML());
	}


	public function update() {
		$il_bibl_field = $this->getFieldFilterFromRequest();
		$this->tabs()->activateTab(self::CMD_STANDARD);

		$form = new ilBiblFieldFilterFormGUI($this, $il_bibl_field, $this->facade);
		if ($form->saveObject()) {
			ilUtil::sendSuccess($this->lng()->txt('changes_saved_success'), true);
			$this->ctrl()->redirect($this, self::CMD_STANDARD);
		}
		$form->setValuesByPost();
		$this->tpl()->setContent($form->getHTML());
	}


	public function delete() {
		$il_bibl_field = $this->getFieldFilterFromRequest();
		$this->tabs()->activateTab(self::CMD_STANDARD);
		$il_bibl_field->delete();
		ilUtil::sendSuccess($this->dic->language()->txt('filter_deleted'), true);
		$this->ctrl()->redirect($this, self::CMD_STANDARD);
	}


	/**
	 * cancel
	 */
	public function cancel() {
		$this->ctrl()->redirect($this, self::CMD_STANDARD);
	}


	/**
	 * @return ilBiblFieldFilterInterface
	 */
	private function getFieldFilterFromRequest() {
		$field = $this->http()->request()->getQueryParams()[self::FILTER_ID];
		$il_bibl_field = $this->facade->filterFactory()->findById($field);

		return $il_bibl_field;
	}
}