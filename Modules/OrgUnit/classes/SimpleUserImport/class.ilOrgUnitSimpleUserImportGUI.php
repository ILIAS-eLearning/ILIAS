<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilOrgUnitSimpleUserImportGUI
 *
 * @author            Oskar Truffer <ot@studer-raimann.ch>
 * @author            Martin Studer <ms@studer-raimann.ch>
 */
class ilOrgUnitSimpleUserImportGUI {

	/**
	 * @var ilTabsGUI
	 */
	protected $tabs_gui;
	/**
	 * @var ilToolbarGUI
	 */
	protected $toolbar;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilTemplate
	 */
	protected $tpl;
	/**
	 * @var ilObjOrgUnit|ilObjCategory
	 */
	protected $parent_object;
	/**
	 * @var ilLanguage
	 */
	protected $lng;
	/**
	 * @var ilAccessHandler
	 */
	protected $ilAccess;


	/**
	 * @param $parent_gui
	 */
	function __construct($parent_gui) {
		global $tpl, $ilCtrl, $ilTabs, $ilToolbar, $lng, $ilAccess;
		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->parent_gui = $parent_gui;
		$this->parent_object = $parent_gui->object;
		$this->tabs_gui = $this->parent_gui->tabs_gui;
		$this->toolbar = $ilToolbar;
		$this->lng = $lng;
		$this->ilAccess = $ilAccess;
		$this->lng->loadLanguageModule('user');
		if (! $this->ilAccess->checkaccess("write", "", $this->parent_gui->object->getRefId())) {
			ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
		}
	}

	/**
	 * @return bool
	 */
	public function executeCommand()
	{
		$cmd = $this->ctrl->getCmd();

		switch ($cmd) {
			case 'userImportScreen':
				$this->userImportScreen();
				break;
			case 'startImport':
				$this->startImport();
				break;
		}
		return true;
	}

	public function userImportScreen() {
		$form = $this->initForm("startUserImport");
		$this->tpl->setContent($form->getHTML());
	}


	protected function initForm($submit_action) {
		$form = new ilPropertyFormGUI();
		$input = new ilFileInputGUI($this->lng->txt("import_xml_file"), "import_file");
		$input->setRequired(true);
		$form->addItem($input);
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->addCommandButton($submit_action, $this->lng->txt("import"));

		return $form;
	}

	public function startUserImport() {
		$form = $this->initForm("startUserImport");
		if (! $form->checkInput()) {
			$this->tpl->setContent($form->getHTML());
		} else {
			$file = $form->getInput("import_file");
			$importer = new ilOrgUnitImporter();
			try {
				$importer->simpleUserImport($file["tmp_name"]);
			} catch (Exception $e) {
				$this->ilLog->wirte($e->getMessage() . "\\n" . $e->getTraceAsString());
				ilUtil::sendFailure($lng->txt("import_failed"), true);
				$this->ctrl->redirect($this, "render");
			}
			$this->displayImportResults($importer);
		}
	}
}
?>