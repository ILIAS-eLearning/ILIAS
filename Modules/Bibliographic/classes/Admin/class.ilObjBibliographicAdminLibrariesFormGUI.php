<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('./Modules/Bibliographic/classes/class.ilObjBibliographicAdminGUI.php');
/**
 * Bibliographic Settings Form.
 *
 * @author Theodor Truffer
 * @author Martin Studer <ms@studer-raimann.ch>
 *
 * @ilCtrl_Calls ilObjBibliographicAdminLibrariesFormGUI: ilObjBibliographicAdminGUI
 *
 * @ingroup      ModulesBibliographic
 */
class ilObjBibliographicAdminLibrariesFormGUI extends ilPropertyFormGUI {

	/**
	 * @var ilBibliographicSetting
	 */
	protected $bibl_setting;
	/**
	 * @var ilObjBibliographicAdminLibrariesGUI
	 */
	protected $parent_gui;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilLanguage
	 */
	protected $lng;
	/**
	 * @var string
	 */
	protected $action;


	/**
	 * Constructor
	 *
	 */
	public function __construct($parent_gui, $bibl_setting) {
		global $ilCtrl, $lng;
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->parent_gui = $parent_gui;
		$this->bibl_setting = $bibl_setting;
		if ($bibl_setting->getId() > 0) {
			$this->action = 'update';
		} else {
			$this->action = 'create';
		}
		$this->ctrl->saveParameter($parent_gui, 'lib_id');
		$this->initForm();
	}


	/**
	 * Init settings property form
	 *
	 * @access private
	 */
	private function initForm() {
		$this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
		$name = new ilTextInputGUI($this->lng->txt("bibl_library_name"), 'name');
		$name->setRequired(true);
		$name->setValue('');
		$this->addItem($name);
		$url = new ilTextInputGUI($this->lng->txt("bibl_library_url"), 'url');
		$url->setRequired(true);
		$url->setValue('');
		$this->addItem($url);
		$img = new ilTextInputGUI($this->lng->txt("bibl_library_img"), 'img');
		$img->setValue('');
		$this->addItem($img);
		$show_in_list = new ilCheckboxInputGUI($this->lng->txt("bibl_library_show_in_list"), 'show_in_list');
		$show_in_list->setValue(1);
		$this->addItem($show_in_list);
		switch ($this->action) {
			case 'create':
				$this->setTitle($this->lng->txt("bibl_settings_new"));
				$this->addCommandButton('create', $this->lng->txt('save'));
				break;
			case 'update':
				$this->addCommandButton('update', $this->lng->txt('save'));
				$this->fillForm();
				$this->setTitle($this->lng->txt("bibl_settings_edit"));
				break;
		}
		$this->addCommandButton('cancel', $this->lng->txt("cancel"));
	}


	private function fillForm() {
		$this->setValuesByArray(array(
			'name' => $this->bibl_setting->getName(),
			'url' => $this->bibl_setting->getUrl(),
			'img' => $this->bibl_setting->getImg(),
			'show_in_list' => $this->bibl_setting->getShowInList()
		));
	}


	public function saveObject() {
		if (! $this->checkInput()) {
			return false;
		}
		$this->bibl_setting->setName($this->getInput("name"));
		$this->bibl_setting->setUrl($this->getInput("url"));
		$this->bibl_setting->setImg($this->getInput("img"));
		$this->bibl_setting->setShowInList($this->getInput("show_in_list"));
		switch ($this->action) {
			case 'create':
				$this->bibl_setting->create();
				break;
			case 'update':
				$this->bibl_setting->update();
				break;
		}

		return true;
	}
}