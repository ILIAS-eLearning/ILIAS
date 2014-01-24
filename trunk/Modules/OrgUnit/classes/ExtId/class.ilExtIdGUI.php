<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilExtIdGUI
 *
 * @author            Oskar Truffer <ot@studer-raimann.ch>
 * @author            Martin Studer <ms@studer-raimann.ch>
 */
class ilExtIdGUI {

	/**
	 * @var ilTabsGUI
	 */
	protected $tabs_gui;
	/**
	 * @var ilPropertyFormGUI
	 */
	protected $form;
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
	protected $object;
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
			case 'edit':
				$this->edit();
				break;
			case 'update':
				$this->update();
				break;
		}
		return true;
	}

	public function edit() {
		$form = $this->initForm();
		$this->tpl->setContent($form->getHTML());
	}

	public function initForm() {
		$form = new ilPropertyFormGUI();
		$input = new ilTextInputGUI($this->lng->txt("ext_id"), "ext_id");
		$input->setValue($this->parent_object->getImportId());
		$form->addItem($input);
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->addCommandButton("update", $this->lng->txt("save"));

		return $form;
	}

	public function update() {
		$form = $this->initForm();
		$form->setValuesByPost();
		if ($form->checkInput()) {
			$this->parent_object->setImportId($form->getItemByPostVar("ext_id")->getValue());
			$this->parent_object->update();
			ilUtil::sendSuccess($this->lng->txt("ext_id_updated"), true);
			$this->ctrl->redirect($this,"edit");
		} else {
			$this->tpl->setContent($form->getHTML());
		}
	}
}
?>