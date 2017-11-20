<?php

/**
 * Class ilBiblFieldFilterGUI
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class ilBiblFieldFilterGUI {

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
	 * @var ilObjBibliographic
	 */
	protected $object;
	/**
	 * @var \ILIAS\DI\Container
	 */
	protected $dic;
	/**
	 * @var ilTemplate
	 */
	protected $tpl;
	/**
	 * @var ilTabsGUI
	 */
	protected $tabs;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;


	public function __construct() {
		global $DIC;
		$this->dic = $DIC;
		$this->tpl = $this->dic['tpl'];
		$this->tabs = $DIC->tabs();
		$this->ctrl = $this->dic->ctrl();
		$this->tpl = $this->dic['tpl'];
		$this->object = ilObjectFactory::getInstanceByRefId($_GET['ref_id']);
	}


	public function executeCommand() {
		$nextClass = $this->ctrl->getNextClass();
		switch ($nextClass) {
			default:
				$this->tabs->activateTab(ilObjBibliographicGUI::TAB_SETTINGS);
				$this->performCommand();
		}
	}


	protected function performCommand() {
		$cmd = $this->ctrl->getCmd(self::CMD_STANDARD);
		switch ($cmd) {
			case self::CMD_STANDARD:
			case self::CMD_ADD:
			case self::CMD_EDIT:
			case self::CMD_UPDATE:
			case self::CMD_CREATE:
			case self::CMD_DELETE:
			case self::CMD_APPLY_FILTER:
			case self::CMD_RESET_FILTER:
				if ($this->dic->access()->checkAccess('write', "", $this->object->getRefId())) {
					$this->{$cmd}();
					break;
				} else {
					ilUtil::sendFailure($this->dic->language()->txt("no_permission"), true);
					break;
				}
		}
	}


	public function index() {
		$table = new ilBiblFieldFilterTableGUI($this, self::CMD_STANDARD, $this->object);
		$this->tpl->setContent($table->getHTML());
	}


	protected function add() {
		$ilBiblSettingsFilterFormGUI = new ilBiblFieldFilterFormGUI($this, new ilBiblFieldFilter());
		$this->tpl->setContent($ilBiblSettingsFilterFormGUI->getHTML());
	}


	protected function create() {
		$this->tabs->activateTab(self::CMD_STANDARD);
		$il_bibl_field = new ilBiblFieldFilter();
		$il_bibl_field->setObjectId($this->object->getId());
		$form = new ilBiblFieldFilterFormGUI($this, $il_bibl_field);
		if ($form->saveObject()) {
			ilUtil::sendSuccess($this->dic->language()->txt('changes_saved_success'), true);
//			$this->ctrl->redirect($this, self::CMD_STANDARD);
		}
		$form->setValuesByPost();
		$this->tpl->setContent($form->getHTML());
	}


	public function edit() {
		$ilBiblSettingsFilterFormGUI = new ilBiblFieldFilterFormGUI($this, $this->getFieldFilterFromRequest());
		$ilBiblSettingsFilterFormGUI->fillForm();
		$this->tpl->setContent($ilBiblSettingsFilterFormGUI->getHTML());
	}


	public function update() {
		$il_bibl_field = $this->getFieldFilterFromRequest();
		$this->tabs->activateTab(self::CMD_STANDARD);

		$form = new ilBiblFieldFilterFormGUI($this, $il_bibl_field);
		if ($form->saveObject()) {
			ilUtil::sendSuccess($this->dic->language()->txt('changes_saved_success'), true);
			$this->ctrl->redirect($this, self::CMD_STANDARD);
		}
		$form->setValuesByPost();
		$this->tpl->setContent($form->getHTML());
	}


	/**
	 * @return \ilBiblFieldFilter
	 */
	private function getFieldFilterFromRequest() {
		$field = $this->dic->http()->request()->getQueryParams()[self::FILTER_ID];
		$il_bibl_field = ilBiblFieldFilter::findOrFail($field);

		return $il_bibl_field;
	}
}