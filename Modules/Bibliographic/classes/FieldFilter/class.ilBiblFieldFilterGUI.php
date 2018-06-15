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
	const CMD_ASYNC_EDIT_FORM = 'renderEditFormAsync';
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
			case self::CMD_ASYNC_EDIT_FORM:
				if ($this->access()->checkAccess('write', "", $this->facade->iliasRefId())) {
					$this->{$cmd}();
					break;
				} else {
					ilUtil::sendFailure($this->lng()->txt("no_permission"), true);
					break;
				}
		}
	}


	public function index() {
		if ($this->access()->checkAccess('write', "", $this->facade->iliasRefId())) {
			$ilBiblSettingsFilterFormGUI = new ilBiblFieldFilterFormGUI($this, new ilBiblFieldFilter(), $this->facade);

			$f = $this->dic()->ui()->factory();
			$r = $this->dic()->ui()->renderer();
			$modal = $f->modal()->roundtrip($this->lng()->txt("add_filter"), $f->legacy($ilBiblSettingsFilterFormGUI->getHTML()));
			$button = $f->button()->standard($this->lng()->txt("add_filter"), "#")->withOnClick($modal->getShowSignal());

			$add_filter_html = $r->render([$modal, $button]);

			$this->toolbar()->addText($add_filter_html);
		}

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
		$il_bibl_field->setObjectId($this->facade->iliasObjId());
		$form = new ilBiblFieldFilterFormGUI($this, $il_bibl_field, $this->facade);
		if ($form->saveObject()) {
			ilUtil::sendSuccess($this->lng()->txt('changes_saved'), true);
			$this->ctrl()->redirect($this, self::CMD_STANDARD);
		}
		$form->setValuesByPost();
		$this->tpl()->setContent($form->getHTML());
	}


	public function edit() {
		$ilBiblSettingsFilterFormGUI = $this->initEditForm();
		$this->tpl()->setContent($ilBiblSettingsFilterFormGUI->getHTML());
	}


	public function renderEditFormAsync() {
		$ilBiblSettingsFilterFormGUI = $this->initEditForm();
		echo $ilBiblSettingsFilterFormGUI->getHTML();
		exit;
	}


	public function update() {
		$il_bibl_field = $this->getFieldFilterFromRequest();
		$this->tabs()->activateTab(self::CMD_STANDARD);

		$form = new ilBiblFieldFilterFormGUI($this, $il_bibl_field, $this->facade);
		if ($form->saveObject()) {
			ilUtil::sendSuccess($this->lng()->txt('changes_saved'), true);
			$this->ctrl()->redirect($this, self::CMD_STANDARD);
		}
		$form->setValuesByPost();
		$this->tpl()->setContent($form->getHTML());
	}


	public function delete() {
		global $DIC;
		$il_bibl_field = $this->getFieldFilterFromRequest();
		$this->tabs()->activateTab(self::CMD_STANDARD);
		$il_bibl_field->delete();
		ilUtil::sendSuccess($DIC->language()->txt('filter_deleted'), true);
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


	/**
	 * @return ilBiblFieldFilterFormGUI
	 */
	protected function initEditForm(): ilBiblFieldFilterFormGUI {
		$this->tabs()->clearTargets();
		$this->tabs()->setBackTarget(
			$this->lng()->txt("back"), $this->ctrl()->getLinkTargetByClass(ilBiblFieldFilterGUI::class, ilBiblFieldFilterGUI::CMD_STANDARD)
		);

		$ilBiblSettingsFilterFormGUI = new ilBiblFieldFilterFormGUI($this, $this->getFieldFilterFromRequest(), $this->facade);
		$ilBiblSettingsFilterFormGUI->fillForm();

		return $ilBiblSettingsFilterFormGUI;
	}
}